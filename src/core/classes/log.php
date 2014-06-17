<?php

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

  /**
   ** @details
   ** p_level, p_file, p_line, p_fmt...
   */
  private static function doLog()
  {
    $l_args      = func_get_args();
    $l_callArgs  = array();
    $l_level     = $l_args[0];
    $l_module    = $l_args[1];
    $l_srcFmt    = $l_args[2];
    $l_dstFmt    = sprintf("%%9s (%%s) : %s at %%s:%%d", $l_srcFmt);

    if (self::getLevel($l_module) < $l_level)
      return;

    array_push($l_callArgs, $l_dstFmt);
    array_push($l_callArgs, sprintf("[%s]", self::levelToString($l_level)));
    array_push($l_callArgs, $l_module);

    for ($c_argIdx = 3; $c_argIdx < func_num_args(); $c_argIdx++)
      array_push($l_callArgs, $l_args[$c_argIdx]);

    $l_stackInfo = debug_backtrace();
    $l_stackInfo = $l_stackInfo[2];
    array_push($l_callArgs, $l_stackInfo["file"]);
    array_push($l_callArgs, $l_stackInfo["line"]);

    $l_msg = call_user_func_array("sprintf", $l_callArgs);
    array_push(self::$ms_lines, $l_msg);
    error_log($l_msg);
  }

  public static function doLogFile()
  {
    $l_args      = func_get_args();
    $l_callArgs  = Array();
    $l_level     = $l_args[0];
    $l_module    = $l_args[1];
    $l_srcFmt    = $l_args[2];
    $l_dstFmt    = sprintf("%%9s (%%s) : %s [ at %%s:%%d ]", $l_srcFmt);

    if (self::getLevel($l_module) < $l_level)
      return;

    array_push($l_callArgs, $l_dstFmt);
    array_push($l_callArgs, sprintf("[%s]", self::levelToString($l_level)));
    array_push($l_callArgs, $l_module);

    for ($c_argIdx = 3; $c_argIdx < func_num_args(); $c_argIdx++)
      array_push($l_callArgs, $l_args[$c_argIdx]);

    $l_msg = call_user_func_array("sprintf", $l_callArgs);
    array_push(self::$ms_lines, $l_msg);
    error_log($l_msg);
  }
}

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    log::doLogFile(log::mc_levelCrit, "core.php", "%s", $errstr, $errfile, $errline);
  }, E_ALL | E_STRICT);

?>