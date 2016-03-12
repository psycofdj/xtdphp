<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(__WAPPCORE_DIR__  . "/core/classes/tools.php");

class log
{
  public static  $ms_defaultLevel = 2;
  public static  $ms_levels       = array();
  public static  $ms_lines        = array();
  const           mc_levelDebug   = 7;
  const           mc_levelInfo    = 6;
  const           mc_levelWarn    = 4;
  const           mc_levelError   = 3;
  const           mc_levelCrit    = 2;

  public static function setDefaultLevel($p_level) {
    self::$ms_defaultLevel = $p_level;
  }

  public static function getDefaultLevel() {
    return self::$ms_defaultLevel;
  }

  public static function setLevel($p_module, $p_level) {
    return self::$ms_levels[$p_module] = $p_level;
  }

  public static function getLevel($p_module = null)
  {
    $l_parts = explode(".", $p_module);
    while (0 != count($l_parts))
    {
      $l_key = implode(".", $l_parts);
      if (true == array_key_exists($l_key, self::$ms_levels))
        return self::$ms_levels[$l_key];
      array_pop($l_parts);
    }
    return self::$ms_defaultLevel;
  }

  public static function getLevels() {
    return self::$ms_levels;
  }

  public static function getLines() {
    return self::$ms_lines;
  }

  public static function debug() {
    $l_args = func_get_args();
    array_unshift($l_args, self::mc_levelDebug);
    forward_static_call_array("self::doLog", $l_args);
  }

  public static function info() {
    $l_args = func_get_args();
    array_unshift($l_args, self::mc_levelInfo);
    forward_static_call_array("self::doLog", $l_args);
  }

  public static function warn() {
    $l_args = func_get_args();
    array_unshift($l_args, self::mc_levelWarn);
    forward_static_call_array("self::doLog", $l_args);
  }

  public static function error() {
    $l_args = func_get_args();
    array_unshift($l_args, self::mc_levelError);
    forward_static_call_array("self::doLog", $l_args);
  }

  public static function crit() {
    $l_args = func_get_args();
    array_unshift($l_args, self::mc_levelCrit);
    forward_static_call_array("self::doLog", $l_args);
  }

  private static function levelToString($p_level)
  {
    if ($p_level == self::mc_levelCrit)
      return "crit";
    if ($p_level == self::mc_levelError)
      return "error";
    if ($p_level == self::mc_levelWarn)
      return "warning";
    if ($p_level == self::mc_levelInfo)
      return "info";
    if ($p_level == self::mc_levelDebug)
      return "debug";
    return "unknown";
  }

  public static function logBuild()
  {
    $l_args      = func_get_args();
    $l_callArgs  = array();
    $l_level     = $l_args[0];
    $l_module    = $l_args[1];
    $l_srcFmt    = $l_args[2];
    $l_dstFmt    = sprintf("%%9s (%%s) : %s at %%s:%%d", $l_srcFmt);

    if (self::getLevel($l_module) < $l_level)
      return false;

    array_push($l_callArgs, $l_dstFmt);
    array_push($l_callArgs, sprintf("[%s]", self::levelToString($l_level)));
    array_push($l_callArgs, $l_module);

    for ($c_argIdx = 3; $c_argIdx < func_num_args(); $c_argIdx++)
      array_push($l_callArgs, $l_args[$c_argIdx]);

    $l_stackInfo = debug_backtrace();
    $l_stackInfo = $l_stackInfo[4];
    $l_file      = "(unknown file)";
    $l_line      = 0;

    if (true == array_key_exists("file", $l_stackInfo))
      $l_file = $l_stackInfo["file"];
    if (true == array_key_exists("line", $l_stackInfo))
      $l_line = $l_stackInfo["line"];
    array_push($l_callArgs, $l_file);
    array_push($l_callArgs, $l_line);

    return call_user_func_array("sprintf", $l_callArgs);
  }

  public static function logBuildFile()
  {
    $l_args      = func_get_args();
    $l_callArgs  = Array();
    $l_level     = $l_args[0];
    $l_module    = $l_args[1];
    $l_srcFmt    = $l_args[2];
    $l_dstFmt    = sprintf("%%9s (%%s) : %s at %%s:%%d", $l_srcFmt);

    if (self::getLevel($l_module) < $l_level)
      return false;

    array_push($l_callArgs, $l_dstFmt);
    array_push($l_callArgs, sprintf("[%s]", self::levelToString($l_level)));
    array_push($l_callArgs, $l_module);

    for ($c_argIdx = 3; $c_argIdx < func_num_args(); $c_argIdx++)
      array_push($l_callArgs, $l_args[$c_argIdx]);

    return call_user_func_array("sprintf", $l_callArgs);
  }

  /**
   ** @details
   ** p_level, p_file, p_line, p_fmt...
   */
  public static function doLog()
  {
    $l_args = func_get_args();
    if (false != ($l_msg  = forward_static_call_array("self::logBuild", $l_args))) {
      array_push(self::$ms_lines, $l_msg);
      error_log($l_msg);
    }
  }

  public static function doLogFile()
  {
    $l_args = func_get_args();
    if (false != ($l_msg = forward_static_call_array("self::logBuildFile", $l_args))) {
      array_push(self::$ms_lines, $l_msg);
      error_log($l_msg);
    }
  }

  public static function logStack($p_level, $p_module, $p_trace = null)
  {
    $c_idx   = 0;
    $l_stack = $p_trace;
    if (null == $l_stack)
    {
      $l_stack = debug_backtrace();
      $c_idx = 1;
    }

    for ($c_idx; $c_idx < count($l_stack); $c_idx++)
    {
      $l_class = "<unknown class>";
      $l_file  = "<unknown file>";
      $l_line  = 0;
      $l_func  = "<unknown func>";
      if (true == array_key_exists("class", $l_stack[$c_idx]))
        $l_class = sprintf("%s->", $l_stack[$c_idx]["class"]);
      if (true == array_key_exists("file", $l_stack[$c_idx]))
        $l_file = $l_stack[$c_idx]["file"];
      if (true == array_key_exists("line", $l_stack[$c_idx]))
        $l_line = $l_stack[$c_idx]["line"];
      if (true == array_key_exists("function", $l_stack[$c_idx]))
        $l_func = $l_stack[$c_idx]["function"];
      $l_func = sprintf("%s%s(...)", $l_class, $l_func);
      log::doLogFile($p_level, $p_module, "  % 3d. %-35s", $c_idx, $l_func, $l_file, $l_line);
    }
  }
}


function errors_to_str($p_errno)
{
  $l_errorStr = array();
  $l_errors   = array(
    "E_USER_DEPRECATED",
    "E_DEPRECATED",
    "E_RECOVERABLE_ERROR",
    "E_STRICT",
    "E_USER_NOTICE",
    "E_USER_WARNING",
    "E_USER_ERROR",
    "E_COMPILE_WARNING",
    "E_COMPILE_ERROR",
    "E_CORE_WARNING",
    "E_CORE_ERROR",
    "E_NOTICE",
    "E_PARSE",
    "E_WARNING",
    "E_ERROR");

  foreach ($l_errors as $c_error)
  {
    $l_value = constant($c_error);
    if ($p_errno & $l_value)
      array_push($l_errorStr, $c_error);
  }

  return implode("|", $l_errorStr);
}




set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    if (0 == error_reporting())
      return;

    $l_silent =
      array("core/libs/smarty/sysplugins/smarty_resource.php",
            "core/libs/phpmailer/class.phpmailer.php");
    foreach ($l_silent as $c_silent)
      if (true == tools::ends_with($errfile, $c_silent))
        return;

    log::doLogFile(log::mc_levelCrit, "core.php", "[%s] %s", errors_to_str($errno), $errstr, $errfile, $errline);
    log::logStack(log::mc_levelCrit, "core.php");

    throw new Exception();
  }, E_ALL | E_STRICT);

?>