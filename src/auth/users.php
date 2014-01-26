<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/handler.php");

class UserPage extends HtmlHandler
{
  public function __construct()
  {
    parent::__construct();
    $this->setContent("file:[app]test.tpl");
  }

  public function h_default($pu_test = 5)
  {
    $this->setData("val", $pu_test);
    return true;
  }
}

$l_page = new UserPage();
$l_page->process();

?>