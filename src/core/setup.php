<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");


class Setup extends Handler
{
  public function __construct()
  {
    parent::__construct(new BinaryGenerator());
  }

  public function initialize()
  {
    global $g_conf;

    if (false == parent::initialize())
    {
      log::crit("setup", "unable to initialize handler");
      return false;
    }

    R::freeze(false);

    R::exec(<<<'EOF'
       SET FOREIGN_KEY_CHECKS = 0;
       SET GROUP_CONCAT_MAX_LEN=32768;
       SET @tables = NULL;
       SELECT GROUP_CONCAT('`', table_name, '`') INTO @tables
         FROM information_schema.tables
         WHERE table_schema = (SELECT DATABASE());
       SELECT IFNULL(@tables,'dummy') INTO @tables;
       SET @tables = CONCAT('DROP TABLE IF EXISTS ', @tables);
       PREPARE stmt FROM @tables;
       EXECUTE stmt;
       DEALLOCATE PREPARE stmt;
       SET FOREIGN_KEY_CHECKS = 1;
EOF
            , array($g_conf["mysql"]["database"]));

    foreach (App::get()->getModules() as $c_module)
    {
      log::crit("core.setup", "installing module '%s'", $c_module->getName());
      $c_module->setup();
    }
    return true;
  }

  public function h_default()
  {
    return $this->redirect("/");
  }
}



$l_page = new Setup();
$l_page->process();

?>