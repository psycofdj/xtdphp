<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class Page extends Handler
{
  public function __construct()
  {
    parent::__construct();

    if (false != ($l_authModule = App::get()->getModule("auth")))
    {
      $l_authModule
        ->registerPerm("userlist", "auth/user/view")
        ->registerPerm("rolelist", "auth/role/view");
    }
  }

  public function h_default()
  {
    return $this->redirect("/wappcore/auth/user.php");
  }

  public function h_login($ps_mail, $ps_password)
  {
    if (null != ($l_user = UserModel::getByMailPass($ps_mail, $ps_password)))
    {
      $l_acl     = new Acl();
      $l_perms   = $l_user->ownAuthuserAuthpermList;

      $l_acl->addRole(new Role("user"));
      foreach ($l_perms as $c_perm)
      {
        $l_role = $c_perm->authrole;
        $l_data = $c_perm->data;
        foreach ($l_role->sharedAuthactionList as $c_action)
          $l_acl->allow("user", $l_data, $c_action->tag);
      }

      $this->setSession("auth_user", $l_user);
      $this->setSession("auth_acl",  $l_acl);
      $this->setStatusCode(204);
      return true;
    }

    $this->setStatusCode(401);
    return true;
  }

  public function h_logout($p_dest = "/")
  {
    $this->deleteSession("auth_user");
    $this->deleteSession("auth_acl");
    return $this->redirect($p_dest);
  }
}

$l_page = new Page();
$l_page->process();

?>