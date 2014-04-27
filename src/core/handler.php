<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/log.php");
require_once(__WAPPCORE_DIR__  . "/core/locale.php");
require_once(__WAPPCORE_DIR__  . "/core/types.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/smarty/Smarty.class.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/RedBeanPHP/loader.php");
/* require_once(__WAPPCORE_DIR__  . "/core/libs/redbean.rebean.php"); */
require_once(__WAPPCORE_DIR__  . "/core/sql.php");
require_once(__WAPPCORE_DIR__  . "/core/app.php");
require_once(__WAPPCORE_DIR__  . "/core/generator.php");


/**
 * Output generator
 */
class Handler
{
  /** List of headers to inlude in HTTP header response */
  private $m_headers;
  /** List of headers to inlude in HTTP header response */
  private $m_statusCode;
  /** Content-Type HTTP value */
  private $m_contentType;
  /** Content of HTTP response */
  private $m_content;

  protected function __construct(Generator $p_gen)
  {
    $this->m_gen         = $p_gen;
    $this->m_headers     = Array();
    $this->m_statusCode  = 200;
    $this->m_contentType = "text/plain";
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
    case 302:
      return "302 Moved Temporarily";
    case 401:
      return "401 Unauthorized";
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

  private function generateOutput($p_isValid)
  {
    global $g_conf;

    if (false == $p_isValid)
      $this->setStatusCode(500);


    if (200 == $this->m_statusCode)
    {
      if (false != ($l_content = $this->m_gen->resolve($this)))
      {
        $this->m_content     = $l_content;
        $this->m_contentType = $this->m_gen->getContentType();
      }
      else
      {
        $this->setStatusCode(500);
      }
    }

    if (500 == $this->m_statusCode)
    {
      $this->setStatusCode(500);
      if ($g_conf["env"] == "dev")
      {
        $this->m_content = join("\n", log::getLines());
        $this->m_content .= join("\n", error_get_last());
      }
    }
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
    $this->generateOutput($p_isValid);

    log::debug("replying with status %d", $this->m_statusCode);

    // 1.
    array_push($this->m_headers, sprintf('HTTP/1.1 %s',         $this->translateStatus()));
    array_push($this->m_headers, sprintf('Status: HTTP/1.1 %s', $this->translateStatus()));
    array_push($this->m_headers, sprintf('Content-type: %s',    $this->m_contentType));
    foreach($this->m_headers as $c_header)
      header($c_header);

    echo $this->m_content;

    return $p_isValid;
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

    R::getDatabaseAdapter()->getDatabase()->setDebugMode(true, new SqlLogger());

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
          log::error("requested param '%s' not available", $l_paramName);
          return $this->reply(false);
        }
        $l_paramValue = $c_param->getDefaultValue();
      }
      else
      {
        if (false == $this->validateParam($l_paramName, $l_paramAttr, $l_paramValue))
          return $this->reply(false);
      }

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


?>
