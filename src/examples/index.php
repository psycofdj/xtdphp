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
    /* R::exec("SET @uid=2;"); */
    /* $l_roles = R::find('authrole'); */
    /* foreach ($l_roles as $c_role) { */
    /*   /\* $c_role->description = $c_role->description . " plus"; *\/ */
    /*   R::trash($c_role); */
    /* } */


    /* $l_users = R::find('user'); */
    /* foreach ($l_users as $c_user) */
    /* { */
    /*   foreach ($l_roles as $c_role) */
    /*     $c_user->sharedRole[] = $c_role; */
    /*   R::store($c_user); */
    /* } */
  }

}


$l_page = new Page();
$l_page->process();

?>