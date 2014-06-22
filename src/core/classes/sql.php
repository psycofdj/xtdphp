<?php

require_once(dirname(__FILE__) . "/../../local.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/RedBeanPHP/loader.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/log.php");

/**
 * Simple redbean request logger
 */
class SqlLogger implements RedBeanPHP\Logger
{
  public function log()
  {
    foreach (func_get_args() as $c_arg)
    {
      if (is_array($c_arg))
      {
        if (0 == count($c_arg))
          continue;
        $c_arg = print_r($c_arg, true);
      }
      $c_arg = str_replace("\n", " ",  $c_arg);
      $c_arg = preg_replace('/( +)/s', ' ', $c_arg);
      log::debug("core.sql", "executing sql query : %s", $c_arg);
    }
  }
}


class SqlImporter
{
  public function load($p_file)
  {
    if (false == ($l_data = file_get_contents($p_file)))
    {
      log::crit("core.sql", "enable to open file '%s'", $p_file);
      return false;
    }

    R::begin();
    R::exec($l_data);
    R::commit();

    return true;
  }
}

?>