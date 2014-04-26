<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");
require_once(__WAPPCORE_DIR__  . "/core/app.php");
require_once(__WAPPCORE_DIR__  . "/core/menu.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");

class authModule extends Module
{
  private $m_perms = Array();

  public function __construct($p_baseDir, $p_name)
  {
    parent::__construct($p_baseDir, $p_name);

    /* $this */
    /*   ->addPerm("auth.perm.user.view") */
    /*   ->addPerm("auth.perm.user.create") */
    /*   ->addPerm("auth.perm.user.update") */
    /*   ->addPerm("auth.perm.user.terminate") */
    /*   ->addPerm("auth.perm.role.view") */
    /*   ->addPerm("auth.perm.role.create") */
    /*   ->addPerm("auth.perm.role.update") */
    /*   ->addPerm("auth.perm.role.terminate"); */

    /* App::get()->getMenu() */
    /*   ->addTab(new MenuTab("auth.menu.title"), 80) */
    /*   ->addSubTab("auth.menu.roles", "/wappcore/auth/?action=rolelist", "auth.perm.role.*") */
    /*   ->addSubTab("auth.menu.users", "/wappcore/auth/?action=userlist", "auth.perm.user.*"); */

    App::get()->getMenu()
      ->addWidget("file:[auth]menu_widget.tpl", array($this, "createWidget"));
  }


  public function setup()
  {
    R::exec("SET FOREIGN_KEY_CHECKS=0");
    R::exec("DROP TABLE IF EXISTS `user`");
    R::exec("SET FOREIGN_KEY_CHECKS=1");

    R::exec(<<<'EOT'
            CREATE TABLE IF NOT EXISTS `user`
            (
             `id`       int(11)                                                 NOT NULL AUTO_INCREMENT,
             `mail`     varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
             `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,

             PRIMARY KEY (`id`),
             UNIQUE(`mail`)
             ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;
EOT
            );

    $l_user           = R::dispense('user');
    $l_user->mail     = "xavier@marcelet.com";
    $l_user->password = md5("ipark");
    R::store($l_user);

    $l_user           = R::dispense('user');
    $l_user->mail     = "pb@wapp.pro";
    $l_user->password = md5("ipark");
    R::store($l_user);


    /* R::nuke(); */

    /* $l_actionUserWrite              = R::dispense('authaction', 1); */
    /* $l_actionUserWrite->description = "Create or modify user"; */
    /* $l_actionUserWrite->tag         = "auth/user/write"; */
    /* R::store($l_actionUserWrite); */
    /* $l_actionUserRead              = R::dispense('authaction', 1); */
    /* $l_actionUserRead->description = "Consult user list"; */
    /* $l_actionUserRead->tag         = "auth/user/view"; */
    /* R::store($l_actionUserRead); */
    /* $l_actionUserDelete              = R::dispense('authaction', 1); */
    /* $l_actionUserDelete->description = "Delete user"; */
    /* $l_actionUserDelete->tag         = "auth/user/delete"; */
    /* R::store($l_actionUserDelete); */

    /* $l_actionRoleWrite              = R::dispense('authaction', 1); */
    /* $l_actionRoleWrite->description = "Create or modify role"; */
    /* $l_actionRoleWrite->tag         = "auth/role/write"; */
    /* R::store($l_actionRoleWrite); */
    /* $l_actionRoleRead              = R::dispense('authaction', 1); */
    /* $l_actionRoleRead->description = "Consult role list"; */
    /* $l_actionRoleRead->tag         = "auth/role/view"; */
    /* R::store($l_actionRoleRead); */
    /* $l_actionRoleDelete              = R::dispense('authaction', 1); */
    /* $l_actionRoleDelete->description = "Delete role"; */
    /* $l_actionRoleDelete->tag         = "auth/role/delete"; */
    /* R::store($l_actionRoleDelete); */

    /* list($l_roleRead, $l_roleWrite, $l_roleAdmin) = R::dispense("authrole", 3); */
    /* $l_roleRead->description    = "Read user and roles"; */
    /* $l_roleRead->sharedAction[] = $l_actionUserRead; */
    /* /\* $l_roleRead->sharedAction[] = $l_actionRoleRead; *\/ */
    /* R::store($l_roleRead); */
    /* $l_roleWrite->description    = "Read/Write user and roles"; */
    /* $l_roleWrite->sharedAction[] = $l_actionUserWrite; */
    /* /\* $l_roleWrite->sharedAction[] = $l_actionRoleWrite; *\/ */
    /* $l_roleWrite->parent         = $l_roleRead; */
    /* R::store($l_roleWrite); */
    /* $l_roleAdmin->description    = "All permissions"; */
    /* $l_roleAdmin->sharedAction[] = $l_actionUserDelete; */
    /* /\* $l_roleAdmin->sharedAction[] = $l_actionRoleDelete; *\/ */
    /* $l_roleAdmin->parent         = $l_roleWrite; */
    /* R::store($l_roleAdmin); */

    /* list($l_dataAll, $l_dataGarage1, $l_dataGarage2) = R::dispense("authdata", 3); */
    /* $l_dataAll->description     = "All"; */
    /* $l_dataGarage1->description = "Garage 1"; */
    /* $l_dataGarage2->description = "Garage 2"; */
    /* R::store($l_dataAll); */
    /* R::store($l_dataGarage1); */
    /* R::store($l_dataGarage2); */

    /* $l_user                 = R::dispense("authuser", 1); */
    /* $l_user->mail           = "xavier@marcelet.com"; */
    /* $l_user->password       = md5("dduyg8kn"); */
    /* $l_user->link("authuser_perms", array('authdata' => $l_dataGarage1))->authrole = $l_roleRead; */
    /* $l_user->link("authuser_perms", array('authdata' => $l_dataGarage2))->authrole = $l_roleWrite; */
    /* $l_user->link("authuser_perms", array('authdata' => $l_dataAll))->authrole = $l_roleAdmin; */
    /* R::store($l_user); */


    /* $l_user = R::load("authuser", 1); */
    /* echo $l_user->mail . "<br/>"; */
    /* echo $l_user->password . "<br/>"; */
    /* var_dump($l_user); */
    /* var_dump($l_user->via("authuser_perms")->authrole); */



    /* $l_perm         = R::dispense("authperm", 1); */
    /* $l_perm->user   = $l_user; */
    /* $l_perm->role   = $l_role1; */
    /* $l_perm->access = $l_access; */
    /* R::store($l_perm); */

    /* R::createRevisionSupport($l_action); */
    /* R::createRevisionSupport($l_perm); */
    /* R::createRevisionSupport($l_role1); */
    /* R::createRevisionSupport($l_user); */
    /* R::exec("SET FOREIGN_KEY_CHECKS=0;"); */
    /* R::clearRelations($l_role1, "authaction"); */
    /* R::clearRelations($l_role2, "authaction"); */
    /* R::wipe("authperm"); */
    /* R::wipe("authaccess"); */
    /* R::wipe("authuser"); */
    /* R::wipe("authrole"); */
    /* R::wipe("authaction"); */
    /* R::exec("SET FOREIGN_KEY_CHECKS=1;"); */
  }

  public function createWidget(Handler $p_handler)
  {
    $p_handler->setData("auth_user", null);
    $p_handler->setData("auth_perm", null);

    /* $p_handler->setData("auth",      App::get()->getModule("auth")); */
    if (false != ($l_user = $p_handler->getSession("auth_user")))
      $p_handler->setData("auth_user",   $l_user);
  }

  public function addPerm($p_tag)
  {
    array_push($this->m_perms, $p_tag);
    return $this;
  }

  public function getPerms()
  {
    return $this->m_perms;
  }


}

?>