<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/handler.php");

class AuthPage extends HtmlHandler
{
  public function __construct()
  {
    parent::__construct();
  }

  /* public function h_list($p_mail, $p_pass, $p_role) */
  /* { */
  /* } */

  /* public function h_add($p_mail, $p_pass, $p_role,  = "/") */
  /* { */
  /* } */

  public function h_login($ps_mail, $ps_password)
  {
    log::debug("pass : %s", $ps_password);
    if (null != ($l_user = UserModel::getByMailPass($ps_mail, $ps_password)))
    {
      $this->setSession("auth_user",   $l_user);
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

}

$l_page = new AuthPage();
$l_page->process();

?>