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
require_once(__WAPPCORE_DIR__  . "/core/classes/tools.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/log.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/locale.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/types.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/error.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/sql.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/app.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/generator.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/session.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/RedBeanPHP/loader.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/Zend/load.php");



class Handler
{
  protected function initialize()
  {
    sql::initialize();
    return true;
  }

  protected function finalize()
  {
    return true;
  }

  public function process()
  {
    try
    {
      // 1.
      if (true != $this->initialize())
      {
        log::crit("core.handler", "unable to initialize handler");
        return $this->replyInternalError();
      }

      // 2.
      if (false === $this->run())
      {
        log::crit("core.handler", "error while running process");
        return $this->replyInternalError();
      }

      // 3.
      if (true != $this->finalize())
      {
        log::crit("core.handler", "unable to finalize handler");
        return $this->replyInternalError();
      }

      // 4.
      return $this->replySuccess();
    }
    catch (WappError $l_error) {
      return $this->replyError($l_error);
    }
    catch (Exception $l_error) {
      log::crit("core.handler", "caught exception");
      log::doLogFile(log::mc_levelCrit, "core.handler", "    %s", $l_error->getMessage(), $l_error->getFile(), $l_error->getLine());
      log::crit("core.handler", "exception backtrace");
      log::logStack(log::mc_levelCrit, "core.handler", $l_error->getTrace());
      return $this->replyException($l_error);
    }
  }

  protected function run()
  {
    throw new Exception("you must define a 'public function run()' method");
  }

  protected function replyInternalError()
  {
    global $g_conf;
    if ($g_conf["env"] == "dev")
    {
      echo join("\n", log::getLines());
      if (null != ($l_error = error_get_last()))
        echo join("\n", $l_error);
    }
    exit(1);
  }

  protected function replyError(WappError $p_error)
  {
    return $this->replyException($p_error);
  }

  protected function replyException(Exception $p_error)
  {
    echo $p_error->getMessage() . "\n";
    return $this->replyInternalError();
  }

  protected function replySuccess()
  {
    exit(0);
  }
}



class HTTPHandler extends Handler
{
  private static $ms_defaultGenerator = "WappHtmlGenerator";

  /** List of headers to include in HTTP header response */
  protected $m_headers;
  /** List of headers to include in HTTP header response */
  private $m_statusCode;
  /** Content-Type HTTP value */
  private $m_contentType;
  /** Content of HTTP response */
  private $m_content;

  private $m_signals = null;

  protected function __construct(OutputGenerator $p_gen = null)
  {
    $this->m_gen         = $p_gen;
    $this->m_headers     = Array();
    $this->m_statusCode  = 200;
    $this->m_contentType = "text/plain";
    $this->m_signals     = new ezcSignalCollection(__CLASS__);

    if ($this->m_gen == null)
      $this->m_gen = new self::$ms_defaultGenerator;
  }

  /* ---------------------------------------------- */

  static public function setDefaultGenerator($p_gen)
  {
    self::$ms_defaultGenerator = $p_gen;
  }

  public function setGenerator($p_gen)
  {
    $this->m_gen = $p_gen;
  }

  /* ---------------------------------------------- */

  /**
   * @return Handler
   */
  protected function setStatusCode($p_statusCode)
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
    case 204:
      return "204 No Content";
    case 302:
      return "302 Moved Temporarily";
    case 401:
      return "401 Unauthorized";
    case 500:
      return "500 Internal Server Error";
    default:
      $this->setStatusCode(500);
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
  protected function getParam($p_name)
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

    for ($c_idx = 0; $c_idx < count($p_paramAttr); $c_idx++)
    {
      $l_attr   = $p_paramAttr[$c_idx];
      $l_status = true;
      switch ($l_attr)
      {
      case 'p' :
        $l_name = "string";
        break;

      case 'd' :
        $l_name   = "date";
        $l_status = types::to_date($p_paramValue);
        break;


      case 'a' :
        $l_name    = "array";
        $l_remains = array_slice($p_paramAttr, $c_idx + 1);
        $c_idx     = count($p_paramAttr);
        if (true == ($l_status = types::is_array($p_paramValue)))
          foreach ($p_paramValue as &$c_val)
            $l_status = $l_status && $this->validateParam($p_paramName . "-item", implode("", $l_remains), $c_val);

        break;

      case 'm' :
        $l_name = "mail";
        $l_status = types::is_mail($p_paramValue);
        break;
      case 'i' :
        $l_name = "int";
        $l_status = types::to_int($p_paramValue);
        break;
      case 'x' :
        $l_name = "xml";
        $l_status = types::xml_to_obj($p_paramValue);
        break;
      case '6' :
        $l_name = "base64";
        $l_status = types::base64_to_bin($p_paramValue);
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
      case 'j' :
        $l_name   = "json";
        $l_status = true;
        if (null === $p_paramValue = json_decode($p_paramValue))
          $l_status = false;
        break;
      case 's' :
        $l_name = "string";
        $p_paramValue = (string)$p_paramValue;
        break;
      }

      if ($l_status == false)
      {
        log::error("core.httphandler", "unable to convert param '%s' of value '%s' to %s", $p_paramName, $p_paramValue, $l_name);
        return false;
      }
    }
    return true;
  }

  protected function replyInternalError()
  {
    global $g_conf;

    $this->setStatusCode(500);

    if ($g_conf["env"] == "dev")
    {
      $this->m_content  = join("\n", log::getLines());
      if (null != ($l_error = error_get_last()))
        $this->m_content  .= join("\n", $l_error);
    }

    $this->reply();
  }

  protected function replyError(WappError $p_error)
  {
    if (false == ($l_content = $this->m_gen->resolveError($p_error)))
      return $this->replyInternalError();

    $this->m_content     = $l_content;
    $this->m_contentType = $this->m_gen->getContentType();
    $this->setStatusCode($p_error->getStatusCode());
    $this->reply();
  }

  protected function replyException(Exception $p_error)
  {
    return $this->replyInternalError();
  }

  protected function replySuccess()
  {
    if ($this->m_statusCode == 200)
    {
      if (false == ($l_content = $this->m_gen->resolve()))
        return $this->replyInternalError();
      $this->m_content     = $l_content;
      $this->m_contentType = $this->m_gen->getContentType();
    }
    $this->reply();
  }

  /**
   * Generate HTTP response from current object status
   *
   * 1. Generates output headers from current status code, content-type
   * 2. Forget about any previous output data
   * 3. Output and send to client handler's data
   * 4. Prevent any futur output
   *
   */
  private function reply()
  {
    log::debug("core.httphandler", "replying with status %d", $this->m_statusCode);

    // 1.
    array_push($this->m_headers, sprintf('HTTP/1.1 %s',         $this->translateStatus()));
    array_push($this->m_headers, sprintf('Status: HTTP/1.1 %s', $this->translateStatus()));
    array_push($this->m_headers, sprintf('Content-type: %s',    $this->m_contentType));
    foreach($this->m_headers as $c_header)
      header($c_header);

    // 2.
    while (ob_get_level())
      ob_end_clean();

    // 3.
    ob_start();
    echo $this->m_content;
    ob_end_flush();

    // 4.
    ob_start(function ($p_buffer, $p_phase) {
        return "";
      });
  }


  public function __call($p_name, $p_arguments)
  {
    return call_user_func_array(array(&$this->m_gen, $p_name), $p_arguments);
  }

  /**
   * @return Handler $this
   */
  private function setContentType($p_contentType)
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
    log::debug("core.httphandler", "redirecting with status 302 to %s", $p_dest);
    array_push($this->m_headers, sprintf("Location: %s", $p_dest));
    $this->setStatusCode(302);
    return $this;
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
    parent::initialize();
    $this->initSession();
    $this->initLocale();
    $this->m_gen->initialize($this);

    if (false == ($this->m_gen instanceof RawGenerator))
      App::get()->initialize($this);

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
      WappSqlSessionHandler::session_start();
    log::debug("core.httphandler", "SESSION ID: " . session_id());
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
  public function setSession($p_key, $p_value)
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

  private function validateArgs($p_params)
  {
    $l_result = array();

    foreach ($p_params as $c_name => $c_attr)
    {
      if ((false === ($l_value = $this->getParam($c_name))) ||
          (false === $this->validateParam($c_name, $c_attr, $l_value)))
      {
        log::crit("core.httphandler", "invalid param %s = %s", $c_name, $l_value);
        return false;
      }
      $l_result[$c_name] = $l_value;
    }
    return $l_result;
  }

  public function serverSide($p_wrapped)
  {
    if (false !== ($l_colIdx = $this->getParam("colIdx")))
      $l_sParams = $this->serverSideInfo($p_wrapped);
    else
      $l_sParams = $this->serverSideData($p_wrapped);

    $l_callArgs = $this->methodGetArgs($p_wrapped, array("params" => $l_sParams));

    if ((false === $l_callArgs) ||
        (false === ($l_data = $p_wrapped->invokeArgs($this, $l_callArgs))))
    {
      log::error("code.handler", "server side error");
      return false;
    }

    $this->m_gen = new JsonGenerator(false);
    foreach ($l_data as $c_key => $c_value)
      $this->m_gen->setData($c_key, $c_value);
    return true;
  }

  public function serverSideInfo($p_method)
  {
    $l_checks = array("colIdx"  => "u", "colName" => "s");

    if (false === ($l_args = $this->validateArgs($l_checks)))
      return false;

    return new MapperInfo($l_args["colIdx"], $l_args["colName"]);
  }


  public function serverSideData($p_method)
  {
    $l_checks = array("sEcho"          => "u", "iColumns"       => "u",
                      "sColumns"       => "s", "iDisplayStart"  => "u",
                      "iDisplayLength" => "u", "sSearch"        => "s",
                      "bRegex"         => "b", "iSortingCols"   => "u");

    if (false === ($l_args = $this->validateArgs($l_checks)))
      return false;

    $l_colNames = explode(",", $l_args["sColumns"]);
    $l_param   = new MapperParams($l_args["sEcho"],          $l_args["iDisplayStart"],
                                  $l_args["iDisplayLength"], $l_args["sSearch"],
                                  $l_args["bRegex"]);

    for ($c_idx = 0; $c_idx < $l_args["iColumns"]; $c_idx++)
    {
      $l_colArgs = array();
      $l_checks  = array("mDataProp"   => "",  "sSearch"     => "s",
                         "bRegex"      => "b", "bSearchable" => "b",
                         "bSortable"   => "b");
      $l_checks = tools::array_key_map($l_checks, function(&$p_result, $p_key, $p_value) use ($c_idx) {
          $l_key            = sprintf("%s_%d", $p_key, $c_idx);
          $p_result[$l_key] = $p_value;
        });
      if (false === ($l_colArgs = $this->validateArgs($l_checks)))
        return false;
      $l_colArgs = array_merge(array($c_idx, $l_colNames[$c_idx]), array_values($l_colArgs));
      call_user_func_array(array($l_param, "addColumns"), $l_colArgs);
    }

    for ($c_idx = 0; $c_idx < $l_args["iSortingCols"]; $c_idx++)
    {
      $l_colArgs = array();
      $l_checks  = array("iSortCol" => "u", "sSortDir" => "s");
      $l_checks  = tools::array_key_map($l_checks, function(&$p_result, $p_key, $p_value) use ($c_idx) {
          $l_key            = sprintf("%s_%d", $p_key, $c_idx);
          $p_result[$l_key] = $p_value;
        });
      if (false === ($l_colArgs = $this->validateArgs($l_checks)))
        return false;
      $l_colArgs = array_merge(array($c_idx, $l_colNames[$c_idx]), array_values($l_colArgs));

      call_user_func_array(array($l_param, "addSort"), $l_colArgs);
    }

    return $l_param;
  }


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
  protected function run()
  {
    // 1.
    if (false == ($l_data = $this->methodGet()))
    {
      log::error("core.httphandler", "unable to find method");
      return false;
    }
    list($l_method, $l_wrapped, $l_action) = $l_data;

    // 2.
    if (false === ($l_callArgs = $this->methodGetArgs($l_method, array("wrapped" => $l_wrapped))))
    {
      log::error("core.httphandlerhandler", "paramters don't fit requested method");
      return false;
    }

    // 3.
    $this->m_signals->emit("process", $this, $l_action);
    return $l_method->invokeArgs($this, $l_callArgs);
  }



  private function methodGet()
  {
    if (false === ($l_action = $this->getParam("action")))
      $l_action = "default";

    $l_reflex = new ReflectionClass($this);
    $l_getter = function($p_name) use ($l_reflex) {
      try {
        return $l_reflex->getMethod($p_name);
      } catch (ReflectionException $l_error) {
        return false;
      }
    };

    $l_wrapped = null;
    if (false !== ($l_method = $l_getter(sprintf("h_%s", $l_action))))
      return array($l_method, $l_wrapped, $l_action);

    if ((false !== ($l_wrapped = $l_getter(sprintf("s_%s", $l_action)))) &&
        (false !== ($l_method  = $l_reflex->getMethod("serverSide"))))
      return array($l_method, $l_wrapped, $l_action);

    if ((false !== ($l_wrapped = $l_getter(sprintf("j_%s", $l_action)))) &&
        (false !== ($l_method  = $l_reflex->getMethod("jsonAnwser"))))
      return array($l_method, $l_wrapped, $l_action);

    log::error("core.httphandler", "unknown action '%s'", $l_action);
    return false;
  }


  public function jsonAnwser($p_wrapped)
  {
    $this->setGenerator(new JsonGenerator(false));
    $l_callArgs = $this->methodGetArgs($p_wrapped);
    if ((false === $l_callArgs) || (false === $p_wrapped->invokeArgs($this, $l_callArgs)))
    {
      log::error("code.handler", "json server side error");
      return false;
    }
    return true;
  }

  private function methodGetArgs($p_method, $p_params = array())
  {
    $l_params   = $p_method->getParameters();
    $l_callArgs = array();

    foreach ($l_params as $c_param)
    {
      list($l_paramAttr, $l_paramName) = explode("_", $c_param->getName(), 2);
      if (true == array_key_exists($l_paramName, $p_params))
      {
        array_push($l_callArgs, $p_params[$l_paramName]);
        continue;
      }

      if (false === ($l_paramValue = $this->getParam($l_paramName)))
      {
        if (false == $c_param->isDefaultValueAvailable())
        {
          log::error("core.httphandler", "requested param '%s' not available", $l_paramName);
          return false;
        }
        $l_paramValue = $c_param->getDefaultValue();
      }
      else
      {
        if (($l_paramValue == "") && ($c_param->isDefaultValueAvailable()))
          $l_paramValue = $c_param->getDefaultValue();
        else
        {
          if (false == $this->validateParam($l_paramName, $l_paramAttr, $l_paramValue))
          {
            log::error("core.httphandler", "couldn't validate param '%s' of value '%s'", $l_paramName, $l_paramValue);
            return false;
          }
        }
      }
      array_push($l_callArgs, $l_paramValue);
    }

    return $l_callArgs;
  }


}


?>