<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");


class Setup extends Handler
{
  public function __construct()
  {
    parent::__construct(new BinaryGenerator());
  }

  public function h_default()
  {
    R::freeze(false);
    foreach (App::get()->getModules() as $c_module)
    {
      log::crit("installing module '%s'", $c_module->getName());
      $c_module->setup();
    }
    return $this->redirect("/");
  }
}


$l_page = new Setup();
$l_page->process();

?>