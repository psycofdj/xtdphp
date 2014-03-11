<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/RedBeanPHP/loader.php");
require_once(__WAPPCORE_DIR__  . "/core/log.php");

/* require_once("phar:///". __WAPPCORE_DIR__  . "/core/libs/rb.phar/Logger.php"); */
/* $p = new Phar('rb.phar', 0); */
/* // Phar étend la classe DirectoryIterator de SPL */
/* foreach (new RecursiveIteratorIterator($p) as $file) { */
/*   // $file est une classe PharFileInfo et hérité de SplFileInfo */
/*   echo $file->getFileName() . "\n"; */
/* } */

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
        $c_arg = str_replace("\n", " ", print_r($c_arg, TRUE));
      }
      log::debug("executing sql query : %s", $c_arg);
    }
  }
}


?>