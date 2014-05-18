<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/handler.php");

class Page extends Handler
{
  public function __construct()
  {
    parent::__construct(new WappHtmlGenerator());
    $this->setContent("file:[examples]array.tpl");
  }

  public function h_array($pu_test = 5)
  {
    return true;
  }

  public function h_default($pu_test = 5)
  {
    return true;
  }

}


$l_page = new Page();
$l_page->process();

?>