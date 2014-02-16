<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/handler.php");

class Page extends HtmlHandler
{
  public function __construct()
  {
    parent::__construct();
    $this->setContent("file:[examples]array.tpl");
  }

  public function h_array($pu_test = 5)
  {
    return true;
  }

  public function h_default($pu_test = 5)
  {
    R::nuke();

    list($l_permView, $l_permCreate, $l_permUpdate) = R::dispense('authperm', 3);
    $l_permView->description = "User view";
    $l_permCreate->description = "User create";
    $l_permUpdate->description = "User update";
    R::storeAll( array($l_permView, $l_permCreate, $l_permUpdate) );

    list($l_roleAdmin, $l_roleView) = R::dispense("authrole", 2);
    $l_roleAdmin->description = "admin on user";
    $l_roleView->description = "read only on user";
    $l_roleView->sharedPerm[]  = $l_permView;
    $l_roleAdmin->sharedPerm[] = $l_permView;
    $l_roleAdmin->sharedPerm[] = $l_permCreate;
    $l_roleAdmin->sharedPerm[] = $l_permUpdate;
    R::storeAll( array($l_roleAdmin, $l_roleView) );

    $l_user = R::dispense("authuser");
    $l_user->mail = "xavier@marcelet.com";
    $l_user->password = md5("dduyg8kn");
    $l_user->sharedRole = $l_roleAdmin;
    R::store($l_user);

    R::createRevisionSupport($l_permView);
    R::createRevisionSupport($l_roleAdmin);
    R::createRevisionSupport($l_user);
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