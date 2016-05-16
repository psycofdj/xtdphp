<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(dirname(__FILE__) . "/../../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/log.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/locale.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/types.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/error.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/app.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/smarty/Smarty.class.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/Mobile_Detect.php");

/* -------------------------------------------------------------------------- */

interface OutputGenerator
{
  public function getContentType();
  public function resolve();
  public function resolveError(WappError $p_error);
  public function initialize(HTTPHandler $p_handler);
}

interface RawGenerator extends OutputGenerator
{
}


/* -------------------------------------------------------------------------- */

class BinaryGenerator implements RawGenerator
{
  private $m_contentType = "application/octet-stream";
  private $m_binData     = false;

  public function initialize(HTTPHandler $p_handler)
  {
  }

  public function getContentType()
  {
    return $this->m_contentType;
  }

  public function loadFile($p_filePath, $p_contentType = null)
  {
    if (null == $p_contentType)
    {
      if (false == ($l_handle = finfo_open()))
      {
        log::error("core.generator", "unable to initialize finfo");
        return false;
      }

      if (false == ($p_contentType = finfo_file($l_handle, $p_filePath)))
      {
        log::error("core.generator", "unable to detect content-type from '%s' file", $p_filePath);
        finfo_close($l_handle);
        return false;
      }
      finfo_close($l_handle);
    }

    $this->m_contentType = $p_contentType;
    if (false == ($this->m_binData = file_get_contents($p_filePath)))
    {
      log::error("core.generator", "unable to read input file '%s'", $p_filePath);
      return false;
    }

    return true;
  }

  public function loadBase64($p_data, $p_contentType)
  {
    if (false == ($this->m_binData = base64_decode($p_data, true)))
    {
      log::error("core.generator", "error while decoding bases64 data");
      return false;
    }

    $this->m_contentType = $p_contentType;
    return true;
  }

  public function loadBinary($p_data, $p_contentType)
  {
    $this->m_contentType = $p_contentType;
    $this->m_binData     = $p_data;
    return true;
  }

  public function resolve()
  {
    return $this->m_binData;
  }

  public function resolveError(WappError $p_error)
  {
    return "";
  }
}


/* -------------------------------------------------------------------------- */

/**
 * Json generator handler
 *
 * Generate a json answer from current data and set content-type header to
 * application/json
 */
class JsonGenerator implements OutputGenerator
{
  private $m_contentType = "application/json";
  private $m_data        = array();
  private $m_forceObject = false;

  public function __construct($p_forceObject = true)
  {
    $this->m_forceObject = $p_forceObject;
  }

  public function initialize(HTTPHandler $p_handler)
  {
  }

  public function getContentType()
  {
    return $this->m_contentType;
  }

  /**
   * @return Handler $this
   */
  public function setData($p_key, $p_value)
  {
    $this->m_data[$p_key] = $p_value;
    return $this;
  }

  public function setArrayData($p_data)
  {
    $this->m_data = $p_data;
  }

  public function resolve()
  {
    if ($this->m_forceObject)
      $l_json = json_encode($this->m_data, JSON_FORCE_OBJECT);
    else
      $l_json = json_encode($this->m_data);
    log::info("core.generator", "answering 200 ok with data : '%s'", $l_json);
    return $l_json;
  }

  public function resolveError(WappError $p_error)
  {
    return json_encode($p_error->getData(), JSON_FORCE_OBJECT);
  }
}

/* -------------------------------------------------------------------------- */

/**
 * Generate answer from given data and smarty template
 *
 * Renders given $m_targetTmpl smarty template with Handler::$m_data.
 * Smarty engine will be initialized with the custum function plugin named
 * <b>{t}</b> which allow to generate localized output
 */
class TemplateGenerator implements OutputGenerator
{
  private   $m_contentType;
  protected $m_data;
  /** Smarty engine instance */
  protected $m_smarty;
  /** Smarty template relative file path */
  protected   $m_targetTmpl;
  protected   $m_targetErrorTmpl;
  private   $m_signals;
  private static $ms_plugins = array();

  public function __construct($p_contentType)
  {
    global $g_conf;

    $this->m_contentType = $p_contentType;
    $this->m_data        = Array();
    $this->m_targetTmpl  = null;
    $this->m_smarty      = new Smarty();
    $this->m_signals     = new ezcSignalCollection(__CLASS__);
    $this->m_smarty
      ->setCompileDir(sprintf("%s/templates_c", __APP_DIR__))
      ->setCacheDir(sprintf("%s/cache",         __APP_DIR__))
      ->registerPlugin("block", "t", array($this, 'translate'));

    $this->m_smarty->caching = 0;
    if ($g_conf["env"] == "dev")
      $this->m_smarty->force_compile = 1;

    foreach (self::$ms_plugins as $c_plugin)
      $this->m_smarty->registerPlugin($c_plugin[0], $c_plugin[1], $c_plugin[2]);

    foreach (App::get()->getModules() as $c_module) {
      $l_name = $c_module->getName();
      $l_path = sprintf("%s/%s/templates", $c_module->getBaseDir(), $l_name);
      $this->m_smarty->addTemplateDir($l_path, $l_name);
    }
  }

  static public function addStaticPlugin($p_type, $p_name, $p_callback)
  {
    array_push(self::$ms_plugins, array($p_type, $p_name, $p_callback));
  }

  public function addPlugin($p_type, $p_name, $p_callback)
  {
    $this->m_smarty->registerPlugin($p_type, $p_name, $p_callback);
  }

  public function initialize(HTTPHandler $p_handler)
  {
    $this->m_signals->emit("initialize", $this);
  }

  public function getContentType()
  {
    return $this->m_contentType;
  }

  /**
   * @return Handler $this
   */
  public function setData($p_key, $p_value)
  {
    $this->m_data[$p_key] = $p_value;
    return $this;
  }

  /**
   * Smarty custom plugin for localized output
   *
   * <br/><b>Smarty plugin signature</b>
   *
   * The $p_content comes from the content of the plugin call, like core.currency in :
   * <code> {t}core.currency{/t} </code>
   *
   * The array $p_params represents from the paramters given to the plugin call, like
   * var1 and var2 in : <code> {t var1=value1 var2=value2}....{/t} </code>
   *
   * <br/><b>Process</b>
   *
   * First, this function will convert the $p_content key to its localized value according
   * to the current language configuration (@see Locale::resolve)
   *
   * Then, final output will be genrated by passing $p_params to the localized string as if it
   * were a sprint format.
   *
   * Exemple @code {t value=400}core.currency{t}
   * For english :
   * - Step 1. Locale::resolve("core.currency") => "Price: $%d"
   * - Step 2. sprint("Price: $%d", 400)        => "Price: $400"
   * For french :
   * - Step 1. Locale::resolve("core.currency") => "Prix : %d€"
   * - Step 2. sprint("Prix : %d€", 400)        => "Prix : 400€"
   *
   * @param string $p_params format arguments
   * @param string $p_content locale key (content of the smarty call)
   * @param mixed $p_smarty (unused)
   * @param mixed $p_repeat (unused)
   * @return string localized value
   */
  public function translate($p_params, $p_content, &$p_smarty, &$p_repeat)
  {
    if (!$p_content)
      return;
    $p_content = trim($p_content);

    $l_params = array_values($p_params);
    $l_format = Locale::resolve($p_content);
    array_unshift($l_params, $l_format);
    return call_user_func_array("sprintf", $l_params);
  }

  /**
   *  Set input template file path
   *
   *  @param  string $p_target template file path relative to smarty template directory
   *  @return TemplateHandler @this
   */
  public function setTarget($p_target)
  {
    $this->m_targetTmpl = $p_target;
    return $this;
  }

  public function setTargetError($p_target)
  {
    $this->m_targetErrorTmpl = $p_target;
    return $this;
  }

  /**
   * Generate template result
   *
   * @return string generated template result
   */
  public function resolve()
  {
    if (null == $this->m_targetTmpl)
    {
      log::crit("core.generator", "cannot render empty template file");
      return false;
    }

    foreach ($this->m_data as $c_key => $c_value)
      $this->m_smarty->assign($c_key, $c_value);

    try {
      return $this->m_smarty->fetch($this->m_targetTmpl);
    }
    catch (SmartyException $l_error) {
      log::crit("core.generator", "exception : %s", $l_error);
      return false;
    }
  }

  public function resolveError(WappError $p_error)
  {
    $this->m_targetTmpl = $this->m_targetErrorTmpl;
    $this->m_data       = $p_error->getData();
    return $this->resolve();
  }

}


/* -------------------------------------------------------------------------- */


/**
 *  Template generator specialized for html output
 *
 *  This object simplifies the generation of HTML page based on a fixed template.
 */
class HtmlGenerator extends TemplateGenerator
{
  /** Body sub-template file path */
  private $m_content;
  /** List of javascript links */
  private $m_jsList;
  /** List of css stylesheets */
  private $m_cssList;
  /** Page's title */
  private $m_title;
  /** Page's meta keywords string */
  private $m_metaKw;
  /** Page's meta description */
  private $m_metaDescr;
  /** List of additional http-equiv directives */
  private $m_metaHttpEquiv;
  /** default included template */
  private $m_header;

  public function __construct()
  {
    global $g_conf;
    parent::__construct("text/html");
    $this->m_content         = null;
    $this->m_jsList          = Array();
    $this->m_cssList         = Array();
    $this->m_title           = null;
    $this->m_metaKw          = null;
    $this->m_metaDescr       = null;
    $this->m_favicon         = null;
    $this->m_base            = null;
    $this->m_header          = null;
    $this->m_metaHttpEquivs  = Array();
    $this->m_version         = $g_conf["version"];
    $this->m_targetErrorTmpl = "[core]error.tpl";

    $this
      ->setData("lang",    locale::getName())
      ->setData("version", $this->m_version);
  }

  public function initialize(HTTPHandler $p_handler)
  {
    parent::initialize($p_handler);
  }

  public function setBase($p_base = null, $p_isHttp = null)
  {
    if (null == $p_isHttp)
      $p_isHttp = in_array('HTTPS', $_SERVER);
    if (null == $p_base)
      $p_base = $_SERVER['SERVER_NAME'];

    $this->m_base = "http://" . $p_base;
    if ($p_isHttp)
      $this->m_base = "https://" . $p_base;
    return $this;
  }

  public function setContent($p_content)
  {
    $this->m_content = $p_content;
    return $this;
  }


  public function setHeader($p_header)
  {
    $this->m_header = $p_header;
    return $this;
  }

  public function setFavicon($p_favicon)
  {
    $this->m_favicon = $p_favicon;
    return $this;
  }

  public function addJs($p_jsPath, $p_module = "app")
  {
    array_push($this->m_jsList, $this->relPathToUrl($p_module, "js/" . $p_jsPath));
    return $this;
  }

  public function addCss($p_cssPath, $p_module = "app")
  {
    array_push($this->m_cssList, $this->relPathToUrl($p_module, "css/" . $p_cssPath));
    return $this;
  }

  private function relPathToUrl($p_module, $p_filePath)
  {
    $l_moduleUri       = App::get()->getModule($p_module)->getUri();
    $l_moduleUriLength = strlen($l_moduleUri);

    if ((0 == $l_moduleUriLength) || ("/" != substr($l_moduleUri, -$l_moduleUriLength)))
      $l_moduleUri = $l_moduleUri . "/";

    return $l_moduleUri . $p_filePath;
  }

  public function setTitle($p_title)
  {
    $this->m_title = $p_title;
    return $this;
  }

  public function setMetaKw($p_metaKw)
  {
    $this->m_metaKw = $p_metaKw;
    return $this;
  }

  public function setMetaDescr($p_metaDescr)
  {
    $this->m_metaDescr = $p_metaDescr;
    return $this;
  }


  public function addMetaHttpEquiv($p_equiv, $p_content)
  {
    $this->m_metaHttpEquivs[$p_equiv] = $p_content;
    return $this;
  }

  public function resolve()
  {
    if (null == $this->m_content)
    {
      log::crit("core.generator", "missing inner content template");
      return false;
    }

    if (null != $this->m_metaDescr)
      $this->setData("__meta_descr", $this->m_metaDescr);
    if (null != $this->m_metaKw)
      $this->setData("__meta_kw",    $this->m_metaKw);
    if (null != $this->m_title)
      $this->setData("__title", $this->m_title);
    if (null != $this->m_favicon)
      $this->setData("__favicon", $this->m_favicon);

    $this
      ->setData("__content",          $this->m_content)
      ->setData("__header",           $this->m_header)
      ->setData("__base",             $this->m_base)
      ->setData("__js_list",          $this->m_jsList)
      ->setData("__css_list",         $this->m_cssList)
      ->setData("__meta_http_equivs", $this->m_metaHttpEquivs)
      ->setTarget("[core]html.tpl");

    return parent::resolve();
  }

  public function resolveError(WappError $p_error)
  {
    $this->setContent($this->m_targetErrorTmpl);
    foreach ($p_error->getData() as $c_key => $c_value)
      $this->setData($c_key, $c_value);
    return $this->resolve();
  }
}

/* -------------------------------------------------------------------------- */


/**
 *  Template generator specialized for html output
 *
 *  This object simplifies the generation of HTML page based on a fixed template.
 */
class WappHtmlGenerator extends HtmlGenerator
{
  public function __construct()
  {
    parent::__construct();
    $this
      ->addJs("jquery.js",                         "core")
      ->addJs("jquery.sprintf.js",                 "core")
      ->addJs("jquery-ui.js",                      "core")
      ->addJs("jquery.validate.js",                "core")
      ->addJs("jquery.validate.methods.js",        "core")
      ->addJs("jquery.validate.methods-custom.js", "core")
      ->addJs("jquery.dataTables.js",              "core")
      ->addJs("jquery.scrollTo.js",                "core")
      ->addJs("jquery-ui-timepicker-addon.js",     "core")
      ->addJs("jquery-ui-sliderAccess.js",         "core")
      ->addJs("jquery.cookie.js",                  "core")
      ->addJs("bootstrap.js",                      "core")
      ->addJs("bootstrap.multiselect.js",          "core")
      ->addJs("bootstrap.confirmation.js",         "core")
      ->addJs("bootstrap.typeahead.js",            "core")
      ->addJs("wapp.detector.js",                  "core")
      ->addJs("wapp.tools.js",                     "core")
      ->addJs("wapp.form.js",                      "core")
      ->addJs("wapp.table.js",                     "core")
      ->addJs("wapp.confirm.js",                   "core")
      ->addJs("jquery.fileupload.js", "core")
      ;

    //->addJs("wapp.fileupload.js",                "core") a voir après pour wappcoriser
    $this
      ->addCss("jquery-ui.css",                        "core")
      ->addCss("jquery-ui-timepicker-addon.css",       "core")
      ->addCss("jquery.dataTables.css",                "core")
      ->addCss("bootstrap.css",                        "core")
      ->addCss("bootstrap-theme.css",                  "core")
      ->addCss("bootstrap.multiselect.css",            "core")
      ->addCss("jquery.dataTables.bootstrap.css",      "core")
      ->addCss("wapp.css",                             "core")
      ->addCss("jquery.fileupload.css", "core")
      ;
  }

  public function initialize(HTTPHandler $p_handler)
  {
    global $g_conf;

    if (locale::getName() == "fr")
    {
      $this
        ->addJs("jquery.validate.messages-fr.js",  "core")
        ->addJs("jquery.dataTables.locale.fr.js",  "core")
        ->addJs("i18n/jquery-ui-timepicker-fr.js", "core")
        ->addJs("wapp.locale.fr.js",               "core");
    }
    else
    {
      $this
        ->addJs("jquery.dataTables.locale.en.js", "core")
        ->addJs("wapp.locale.en.js",              "core");
    }

    $l_detector = new Mobile_Detect();
    $this->setData("conf",      $g_conf);
    $this->setData("isMobile",  $l_detector->isMobile());
    $this->setData("isTablet",  $l_detector->isTablet());
    $this->setData("isDesktop", !($l_detector->isTablet() || $l_detector->isMobile()));

    parent::initialize($p_handler);
  }

  public function resolve()
  {
    $this->setData("__menu", App::get()->getMenu());
    return parent::resolve();
  }
}


/* -------------------------------------------------------------------------- */


?>