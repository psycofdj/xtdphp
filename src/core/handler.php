<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/log.php");
require_once(__WAPPCORE_DIR__  . "/core/locale.php");
require_once(__WAPPCORE_DIR__  . "/core/types.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/smarty/Smarty.class.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/redbean.php");
require_once(__WAPPCORE_DIR__  . "/core/sql.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");

/**
 * Output generator
 */
class Handler
{
  /** Variables used to generate HTTP body response */
  protected $m_data;
  /** List of headers to inlude in HTTP header response */
  protected $m_headers;
  /** List of headers to inlude in HTTP header response */
  private $m_statusCode;
  /** Content-Type HTTP value */
  private $m_contentType;

  protected function __construct()
  {
    $this->m_data        = Array();
    $this->m_headers     = Array();
    $this->m_statusCode  = 200;
    $this->m_contentType = "text/plain";
  }

  /* ---------------------------------------------- */

  /**
   * @return Handler
   */
  private function setStatusCode($p_statusCode)
  {
    $this->m_statusCode = $p_statusCode;
    return $this;
  }

  /**
   * Translate http status to readable text
   *
   * @return string
   */
  private function translateStatus()
  {
    switch ($this->m_statusCode)
    {
    case 200:
      return "200 OK";
    case 302:
      return "302 Moved Temporarily";
    case 500:
      return "500 Internal Server Error";
    default:
      $this-setStatusCode(500);
      return $this->translateStatus();
    }
  }

  /**
   * Return given script parameter
   *
   * The first value found in $_GET then $_POST then $_FILES
   * is returned. If no value is available, value is false
   *
   * @param $p_name parameter name
   * @return string|false parameter value or false if not found
   */
  private function getParam($p_name)
  {
    if (true == isset($_GET[$p_name]))
      $l_value = $_GET[$p_name];
    else if (true == isset($_POST[$p_name]))
      $l_value = $_POST[$p_name];
    else if (true == isset($_FILES[$p_name]))
      $l_value = $_FILES[$p_name];
    else
      return false;
    return $l_value;
  }

  /**
   * Checks if given value validates given list of constraints
   *
   * List of constraints is given as a string, each letter codes
   * for a certain type. For numerical checks, value is cast in the given type.
   *
   * List of checks:
   * - p :  ignored
   * - i :  integer check and cast (only digits with zero or more leading dash)
   * - u :  positive integer check and cast (only digits with no leading dash)
   * - f :  float check and cast (only digits with zero or more leading dash and at most one period)
   * - b : boot check and cast, see @ref types for valid values (1|yes|true|on|0|no|false|off)
   * - s :  string cast
   *
   * @param $p_paramName name of the script parameter
   * @param $p_paramAttr list of parameter modifiers
   * @param $p_paramValue string parameter value
   * @return bool true if value is valid, false otherwise
   */
  private function validateParam($p_paramName, $p_paramAttr, &$p_paramValue)
  {
    $p_paramAttr = str_split($p_paramAttr);

    foreach ($p_paramAttr as $c_attr)
    {
      $l_status = true;
      switch ($c_attr)
      {
      case 'p' : break;
      case 'i' :
        $l_name = "int";
        $l_status = types::to_int($p_paramValue);
        break;
      case 'u' :
        $l_name = "uint";
        $l_status = types::to_uint($p_paramValue);
        break;
      case 'f' :
        $l_name = "float";
        $l_status = types::to_float($p_paramValue);
        break;
      case 'b' :
        $l_name = "bool";
        $l_status = types::to_bool($p_paramValue);
        break;
      case 's' :
        $p_paramValue = (string)$p_paramValue;
        break;
      }

      if ($l_status == false)
      {
        log::error("unable to convert param '%s' of value '%s' to %s", $p_paramName, $p_paramValue, $l_name);
        return false;
      }
    }
    return true;
  }


  /**
   * Generate HTTP response from current object status
   *
   * 1. Generates output headers from current status code, content-type
   * 2. For valid responses (status 200), generates output body by calling
   *    display virtual method
   *
   * @param  bool $p_isValid false means that reply is a server error
   * @return bool $p_isValid's value
   */
  private function reply($p_isValid)
  {
    global $g_conf;

    if (false === $p_isValid)
      $this->setStatusCode(500);

    log::debug("replying with status %d", $this->m_statusCode);

    // 1.
    array_push($this->m_headers, sprintf('HTTP/1.1 %s',         $this->translateStatus()));
    array_push($this->m_headers, sprintf('Status: HTTP/1.1 %s', $this->translateStatus()));
    array_push($this->m_headers, sprintf('Content-type: %s',    $this->m_contentType));
    foreach($this->m_headers as $c_header)
      header($c_header);

    // 2.
    if (200 == $this->m_statusCode)
      echo $this->display();
    else if ((500 == $this->m_statusCode) && ($g_conf["env"] == "dev"))
    {
      echo join("<br/>3", log::getLines());
      echo $php_errormsg;
    }

    return $p_isValid;
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
   * @return Handler $this
   */
  protected function setContentType($p_contentType)
  {
    $this->m_contentType = $p_contentType;
    return $this;
  }

  /**
   * Prepare handler to send a redirect response to given destination
   *
   * This function set headers and HTTP code for a redirect but does not actually
   * answer to client. @ref reply must be call (eventually by @ref process)
   *
   * @param  $p_dest destination url
   * @return Handler $this
   */
  protected function redirect($p_dest)
  {
    log::debug("redirecting with status 302 to %s", $p_dest);
    array_push($this->m_headers, sprintf("Location: %s", $p_dest));
    $this->setStatusCode(302);
    return $this;
  }

  /**
   * Redirect to given destination if given session key dosen't exist
   *
   * @param  $p_sessionKey session key name
   * @param  $p_url destination url
   * @return true if redirect occurs, false otherwhise
   */
  protected function checkSessionOrRedirect($p_sessionKey, $p_url)
  {
    if (false == $this->getSession($p_sessionKey))
    {
      $this->redirect($p_url);
      return true;
    }
    return false;
  }


  /**
   *  Initialize request
   *
   *  Child classe may override this function if it need to perform operations
   *  before action routing or parameter parsing.
   *
   *  @return bool true
   */
  protected function initialize()
  {
    $this->initSession();
    $this->initLocale();
    $this->initSql();
    return true;
  }

  /**
   * Initialize session engine
   *
   * Creates a session id if no session is found
   */
  private function initSession()
  {
    $l_sessionID = session_id();
    if (empty($l_sessionID))
      session_start();
    log::debug("SESSION ID: " . session_id());
  }

  /**
   * Initialize sql engine
   *
   * - Conntect to mysql database according to local configuration
   * - Configure logging handler to log request
   * - Configure redbean "freeze" status according current environement
   */
  private function initSql()
  {
    global $g_conf;

    $l_conf = sprintf("mysql:host=%s;dbname=%s;", $g_conf["mysql"]["host"], $g_conf["mysql"]["database"]);
    R::setup($l_conf, $g_conf["mysql"]["username"], $g_conf["mysql"]["password"]);

    R::debug(true, new SqlLogger());

    if ($g_conf["env"] != "dev")
      R::freeze(true);
    else
    {
      log::warn("initializing redbean with dynamic schemas, transactions will be auto-commited");
      R::freeze(false);
    }
  }


  /**
   * Initialize locale engine
   *
   * Set locale to first language found in accept-language header @see locale::detectLang
   * If session contains "lang" parameter, it become the current locale.
   */
  private function initLocale()
  {
    locale::init();
    if (false != ($l_lang = $this->getSession("lang")))
      locale::setLang($l_lang);
  }

  /**
   * Overridable process finalization callback
   *
   * By default, this function does nothing
   *
   * @return bool true
   */
  protected function finalize()
  {
    return true;
  }

  /**
   *  Append value to session
   *
   *  @param  string $p_key session key
   *  @param  string $p_value session value
   *  @return Handler $this
   */
  protected function setSession($p_key, $p_value)
  {
    $_SESSION[$p_key] = $p_value;
    return $this;
  }

  /**
   * Retreive session value
   *
   * @param  string $p_key session key name
   * @return mixed  session value or false if not found
   */
  public function getSession($p_key)
  {
    if (array_key_exists($p_key, $_SESSION))
      return $_SESSION[$p_key];
    return false;
  }

  /**
   * Delete key from session
   *
   * @param  string $p_key session key
   * @return Handler $this
   */
  protected function deleteSession($p_key)
  {
    unset($_SESSION[$p_key]);
    return $this;
  }


  /**
   * File upload check helper
   *
   * This functions checks if uploaded file is valid.
   * 1. Read php error code, translate error to localized message
   * 2. Check file mime-type
   *
   * @param  array      $p_file php upload file object
   * @param  string     $p_message output error message
   * @param  array|null $p_types authorized mime types (or null)
   * @return bool       true if file is OK, false otherwise
   */
  protected function checkFile($p_file, &$p_message, $p_types = null)
  {
    // 1.
    switch ($p_file["error"])
    {
    case UPLOAD_ERR_OK:
      break;
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      $p_message = ø("core.error.file.toobig");
      return false;
    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
      $p_message = ø("core.error.file.transfert");
      return false;
    case UPLOAD_ERR_NO_TMP_DIR: break;
    case UPLOAD_ERR_CANT_WRITE: break;
    case UPLOAD_ERR_EXTENSION: break;
      $p_message = ø("core.error.file.internal");
      return false;
    }

    // 2.
    if (($p_types != null) && (false == in_array($p_file["type"], $p_types)))
    {
      $p_message = ø("core.error.file.type", $p_file["type"]);
      return false;
    }

    return true;
  }

  /* ---------------------------------------------- */


  /**
   * Handle current request and render server response
   *
   * This function is the main entry point when handling a request. It will :
   * 1. initialize session and language (@see Handler::initialize)
   * 2. deduce the processing method that should be called to compute response
   * 3. check input parameters according to the deduced method
   * 4. call this method
   * 5. finalize answer
   * 6. render server response (headers and body)
   * <br/>
   *
   * <b>Processing method</b>
   *
   * By default, this function will call the method name h_default.
   * If the parameter <action> is given, the method h_<action> will be called. <br/><br/>
   *
   * <b>Parameter checking</b>
   *
   * Once the method found, it will be inspect to retreive its parameter list.
   * For each parameter requiered by the target method, this function checks that
   * a corresponding input parameter has been sent to the script.
   *
   * Given the function below :<br/>
   * <code>
   *  function h_myaction($p_arg1, $p_arg2);
   * </code> <br/>
   *
   * The function checks that "arg1" and "arg2" are available in $_GET, $_POST or $_FILES
   * variables. @see getParam for details on parameter retrieval. If a parameter
   * is missing and no default value is provided in the function signature, the
   * process will log and generate a server error (HTTP 500). <br/><br/>
   *
   * Input parameters will also be type checked and cast according to modifiers found in
   * the left part (before the first underscore) of the function's parameter name.
   *
   * Given the function below :<br/>
   * <code>
   *  function h_myaction($pb_arg1, $pu_arg2, $pf_arg3);
   * </code> <br/>
   *
   * The process will check that arg1, arg2 and arg3 can respectively be interpreted
   * as a boolean, an unsigned integer and a float. @see validateParam for
   * defails on modifiers.
   *
   * @return false in case of server error, true otherwise
   */
  public function process()
  {
    log::debug("handling request...");

    // 1.
    if (true != $this->initialize())
    {
      log::crit("unable to initialize handler");
      return $this->reply(false);
    }

    // 2
    if (false === ($l_action = $this->getParam("action")))
      $l_action = "default";
    $l_reflex = new ReflectionClass($this);
    try {
      $l_method = $l_reflex->getMethod(sprintf("h_%s", $l_action));
    }
    catch (ReflectionException $l_error) {
      log::info("unknown action '%s'", $l_action);
      return $this->reply(false);
    }

    // 3
    $l_params   = $l_method->getParameters();
    $l_callArgs = Array();
    foreach ($l_params as $c_param)
    {
      list($l_paramAttr, $l_paramName) = explode("_", $c_param->getName(), 2);

      if (false === ($l_paramValue = $this->getParam($l_paramName)))
      {
        if (false == $c_param->isDefaultValueAvailable())
        {
          log::info("requested param '%s' not available", $l_paramName);
          return $this->reply(false);
        }
        $l_paramValue = $c_param->getDefaultValue();
      }

      if (false == $this->validateParam($l_paramName, $l_paramAttr, $l_paramValue))
        return $this->reply(false);

      array_push($l_callArgs, $l_paramValue);
    }

    // 4.
    if (false === $l_method->invokeArgs($this, $l_callArgs))
      return $this->reply(false);

    // 5.
    if (true != $this->finalize())
    {
      log::crit("unable to finalize handler");
      return $this->reply(false);
    }

    // 6.
    return $this->reply(true);
  }

}


/* -------------------------------------------------------------------------- */


/**
 * Json generator handler
 *
 * Generate a json answer from current data and set content-type header to
 * application/json
 */
class JsonHandler extends Handler
{
  protected function __construct()
  {
    parent::__construct();
    $this->setContentType("application/json");
  }

  protected function display()
  {
    $l_json = json_encode($this->m_data, JSON_FORCE_OBJECT);
    log::info("answering 200 ok with data : '%s'", $l_json);
    return $l_json;
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
class TemplateHandler extends Handler
{
  /** Smarty engine instance */
  private $m_smarty;
  /** Smarty template relative file path */
  private $m_targetTmpl;

  protected function __construct()
  {
    parent::__construct();
    $this->m_targetTmpl = null;
    $this->m_smarty     = new Smarty();
    log::debug("app template dir : %s", sprintf("%s/templates",      __APP_DIR__));
    $this->m_smarty
      ->setCompileDir(sprintf("%s/templates_c", __APP_DIR__))
      ->setCacheDir(sprintf("%s/cache",         __APP_DIR__))
      ->addTemplateDir(sprintf("%s/templates",  __APP_DIR__), "app")
      ->registerPlugin("block", "t",   array($this, 'translate'));
    $this->setContentType("text/plain");
  }

  protected function initialize()
  {
    if (false == parent::initialize())
      return false;

    foreach (Module::getModules() as $c_module) {
      $l_name = $c_module->getName();
      $l_path = sprintf("%s/%s/templates", __WAPPCORE_DIR__, $l_name);
      $this->m_smarty->addTemplateDir($l_path, $l_name);
    }

    return true;
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
  protected function setTarget($p_target)
  {
    $this->m_targetTmpl = $p_target;
    return $this;
  }

  /**
   * Generate template result
   *
   * @return string generated template result
   */
  protected function display()
  {
    foreach ($this->m_data as $c_key => $c_value)
      $this->m_smarty->assign($c_key, $c_value);
    return $this->m_smarty->fetch($this->m_targetTmpl);
  }
}


/* -------------------------------------------------------------------------- */


/**
 *  Template generator specialized for html output
 *
 *  This object simplifies the generation of HTML page based on a fixed template.
 */
class HtmlHandler extends TemplateHandler
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

  protected function __construct()
  {
    parent::__construct();
    $this->m_content        = null;
    $this->m_jsList         = Array();
    $this->m_cssList        = Array();
    $this->m_title          = null;
    $this->m_metaKw         = null;
    $this->m_metaDescr      = null;
    $this->m_onload         = null;
    $this->m_metaHttpEquivs = Array();
    $this->setContentType("text/html");

    $this
      ->addJs("jquery.js",            "core")
      ->addJs("jquery-ui.js",         "core")
      ->addJs("jquery.validate.js",   "core")
      ->addJs("bootstrap.js",         "core")
      ->addCss("jquery-ui.css",       "core")
      ->addCss("bootstrap.css",       "core")
      ->addCss("bootstrap-theme.css", "core");
  }

  protected function initialize()
  {
    if (false == parent::initialize())
      return false;

    $this->setData("lang", locale::getName());
    if (locale::getName() == "fr")
      $this->addJs("jquery.validate.messages-fr.js", "core");

    return true;
  }

  protected function setContent($p_content)
  {
    $this->m_content = $p_content;
    return $this;
  }

  protected function addJs($p_jsPath, $p_module = "app")
  {
    array_push($this->m_jsList, $this->relPathToUrl($p_module, "js/" . $p_jsPath));
    return $this;
  }

  protected function addCss($p_cssPath, $p_module = "app")
  {
    array_push($this->m_cssList, $this->relPathToUrl($p_module, "css/" . $p_cssPath));
    return $this;
  }

  private function relPathToUrl($p_module, $p_filePath)
  {
    global $g_conf;

    $l_moduleUri       = $g_conf["web"]["uri"][$p_module];
    $l_moduleUriLength = strlen($l_moduleUri);

    if ((0 == $l_moduleUriLength) || ("/" != substr($l_moduleUri, -$l_moduleUriLength)))
      $l_moduleUri = $l_moduleUri . "/";

    return $l_moduleUri . $p_filePath;
  }

  protected function setTitle($p_title)
  {
    $this->m_title = $p_title;
    return $this;
  }

  protected function setMetaKw($p_metaKw)
  {
    $this->m_metaKw = $p_metaKw;
    return $this;
  }

  protected function setMetaDescr($p_metaDescr)
  {
    $this->m_metaDescr = $p_metaDescr;
    return $this;
  }

  protected function setOnload($p_onload)
  {
    $this->m_onload = $p_onload;
    return $this;
  }

  protected function addMetaHttpEquiv($p_equiv, $p_content)
  {
    $this->m_metaHttpEquivs[$p_equiv] = $p_content;
    return $this;
  }

  protected function display()
  {
    global $g_conf;

    if (null != $this->m_metaDescr)
      $this->setData("__meta_descr", $this->m_metaDescr);
    if (null != $this->m_metaKw)
      $this->setData("__meta_kw",    $this->m_metaKw);
    if (null != $this->m_onload)
      $this->setData("__onload", $this->m_onload);
    if (null != $this->m_title)
      $this->setData("__title", $this->m_title);

    $l_menu    = Array();
    $l_widgets = Array();

    foreach (Module::getModules() as $c_module)
    {
      $l_menu = array_merge($l_menu, $c_module->getMenu());

      foreach ($c_module->getWidgets() as $c_widget)
      {
        if (null != $c_widget["callback"]) {
          call_user_func($c_widget["callback"], $this);
        }
        array_push($l_widgets, $c_widget["tpl"]);
      }
    }

    $this
      ->setData("__content",          $this->m_content)
      ->setData("__js_list",          $this->m_jsList)
      ->setData("__css_list",         $this->m_cssList)
      ->setData("__meta_http_equivs", $this->m_metaHttpEquivs)
      ->setData("__menu",             $l_menu)
      ->setData("__menu_brand",       $g_conf["brand"])
      ->setData("__menu_widgets",     $l_widgets)
      ->setTarget("html.tpl");

    return parent::display();
  }
}


?>