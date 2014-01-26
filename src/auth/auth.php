require_once(dirname(__FILE__) . "/local.php");
require_once(__WAPPCORE_DIR__  . "/core/handler.php");

<?php

class AuthPage extends HtmlHandler
{
  public function __construct()
  {
    parent::__construct();
  }

  public function h_login($p_mail, $p_pass, $p_dest = "/")
  {
    $l_user = R::findOne("user", "mail = :mail and password :pass",
                         array("mail"     => $p_mail,
                               "password" => md5($p_pass)));

    if (null != $l_user)
    {
      $this
        ->setSession("logged", 1)
        ->setSession("role",   $l_user->role);
    }

    return $this->redirect($p_dest);
  }

  public function h_list($p_mail, $p_pass, $p_role,  = "/")
  {
  }

  public function h_add($p_mail, $p_pass, $p_role,  = "/")
  {
  }

  public function h_logout($p_mail, $p_pass, $p_role, $p_dest = "/")
  {
    $this->deleteSession("logged");
    $this->deleteSession("role");
    $this->redirect($p_dest);
  }

}

?>