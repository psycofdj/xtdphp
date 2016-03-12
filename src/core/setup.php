<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");


class Setup extends HTTPHandler
{
  public function __construct()
  {
    parent::__construct(new BinaryGenerator());
  }

  public function initialize()
  {
    if (false == parent::initialize())
    {
      log::crit("setup", "unable to initialize handler");
      return false;
    }
    return true;
  }

  public function h_update($p_version)
  {
    $l_version = str_replace(".", "_", $p_version);

    try {
      foreach (App::get()->getModules() as $c_module)
      {
        $l_methodName = sprintf("update_%s", $l_version);
        $l_reflex  = new ReflectionClass($c_module);

        try {
          $l_method  = $l_reflex->getMethod($l_methodName);
        }
        catch (ReflectionException $l_error)
        {
          continue;
        }
        log::crit("core.setup", "updating module  module '%s' with function %s", $c_module->getName(), $l_methodName);
        $l_method->invokeArgs($c_module, array());
      }
    }
    catch (Exception $l_error) {
      log::crit("core.setup", "caught exception");
      log::doLogFile(log::mc_levelCrit, "core.setup", "    %s", $l_error->getMessage(), $l_error->getFile(), $l_error->getLine());
      log::crit("core.setup", "exception backtrace");
      log::logStack(log::mc_levelCrit, "core.setup", $l_error->getTrace());
      return false;
    }
    return $this->redirect("/");
  }

  public function h_default()
  {
    global $g_conf;

    R::freeze(false);

    R::exec(<<<'EOF'
       SET FOREIGN_KEY_CHECKS = 0;
       SET GROUP_CONCAT_MAX_LEN=32768;
       SET @tables = NULL;
       SELECT GROUP_CONCAT('`', table_name, '`') INTO @tables
         FROM information_schema.tables
         WHERE table_schema = (SELECT DATABASE()) and table_name != 'inouts';
       SELECT IFNULL(@tables,'dummy') INTO @tables;
       SET @tables = CONCAT('DROP TABLE IF EXISTS ', @tables);
       PREPARE stmt FROM @tables;
       EXECUTE stmt;
       DEALLOCATE PREPARE stmt;
       SET FOREIGN_KEY_CHECKS = 1;
EOF
            , array($g_conf["mysql"]["database"]));

    try {
      foreach (App::get()->getModules() as $c_module)
      {
        log::crit("core.setup", "installing module '%s'", $c_module->getName());
        $c_module->setup();
      }
    }
    catch (Exception $l_error) {
      log::crit("core.setup", "caught exception");
      log::doLogFile(log::mc_levelCrit, "core.setup", "    %s", $l_error->getMessage(), $l_error->getFile(), $l_error->getLine());
      log::crit("core.setup", "exception backtrace");
      log::logStack(log::mc_levelCrit, "core.setup", $l_error->getTrace());
      return false;
    }

    return $this->redirect("/");
  }
}



$l_page = new Setup();
$l_page->process();

?>