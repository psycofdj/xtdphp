<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/handler.php");

class Page extends Handler
{
  public function __construct()
  {
    parent::__construct(new WappHtmlGenerator());
  }

  public function h_login($ps_mail, $ps_password)
  {
    if (null != ($l_user = UserModel::getByMailPass($ps_mail, $ps_password)))
    {
      $this->setSession("auth_user",   $l_user);
      $this->setStatusCode(204);
      return true;
    }
    $this->setStatusCode(401);
    return true;
  }

  public function h_logout($p_dest = "/")
  {
    $this->deleteSession("auth_user");
    return $this->redirect($p_dest);
  }

  public function h_userlist($pu_test = 5)
  {
    $this->setContent("file:[auth]userlist.tpl");
    $this->setData("auth_users", UserModel::getAll());
    return true;
  }

  public function h_rolelist($pu_test = 5)
  {
    $this->setContent("file:[auth]rolelist.tpl");
    return true;
  }

}

$l_page = new Page();
$l_page->process();

?>