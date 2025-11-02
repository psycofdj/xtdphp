<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/tools.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/config.php");

class Page extends HTTPHandler
{
  public function __construct()
  {
    parent::__construct();

    $this->m_auth = App::get()->getModule("auth");
  }

  public function h_default()
  {
    return $this->redirect("/wappcore/auth/user.php");
  }

  public function h_login($ps_mail, $ps_password)
  {
    if (null == ($l_user = UserModel::getByMailPass($ps_mail, $ps_password)))
    {
      $this->setStatusCode(401);
      return true;
    }

    if (false == $this->m_auth->loadPrivileges($this, $l_user))
      return false;

    $this->setStatusCode(204);
    return true;
  }

  public function h_logout($p_dest = "/")
  {
    session_destroy();
    return $this->redirect($p_dest);
  }

}

$l_page = new Page();
$l_page->process();

?>