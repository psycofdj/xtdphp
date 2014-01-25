<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/redbean.php");
require_once(__WAPPCORE_DIR__  . "/core/log.php");


/**
 * Simple redbean request logger
 */
class SqlLogger implements RedBean_Logger
{
  public function log()
  {
    foreach (func_get_args() as $c_arg)
    {
      if (is_array($c_arg))
      {
        if (0 == count($c_arg))
          continue;
        $c_arg = str_replace("\n", " ", print_r($c_arg, TRUE));
      }
      log::debug("executing sql query : %s", $c_arg);
    }
  }
}


?>