<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/mail.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/role.php");

class Page extends Handler
{
  public function __construct()
  {
    parent::__construct();

    $l_auth = App::get()->getModule("auth");
    $l_auth
      ->registerPerm("default", "auth/user/view")
      ->registerPerm("list",    "auth/user/view")
      ->registerPerm("edit",    "auth/user/modify")
      ->registerPerm("delete",  "auth/user/terminate");
  }


  public function h_default()
  {
    return $this->h_list();
  }


  public function h_list()
  {
    $this->setContent("file:[auth]user_list.tpl");
    $this->setData("users", UserModel::getAll());
    return true;
  }


  private function notifyUser($p_user, $p_password)
  {
    global $g_conf;

    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off'))
      $l_url = sprintf("https://%s/", $_SERVER['HTTP_HOST']);
    else
      $l_url = sprintf("http://%s/", $_SERVER['HTTP_HOST']);

    $l_mailer = new MailTemplate("userinfo", $p_user->mail);
    $l_mailer
      ->setData("user",     $p_user)
      ->setData("password", $p_password)
      ->setData("url",      $l_url)
      ->setData("name",     $g_conf["style"]["name"]);
    $l_mailer->addImage($g_conf["style"]["brand"], "brand", true);

    if (false == $l_mailer->send())
      log::warn("error while sending mail userinfo to '%s'", $p_user->mail);
  }


  public function h_save($pi_uid = 0, $pm_email, $p_name, $p_password, $pau_perm = array())
  {

    if ((0 == $pi_uid) && (0 == strlen($p_password)))
    {
      log::crit("new users must have non-empty passwords");
      return false;
    }

    if (0 != $pi_uid)
    {
      $l_isUpdated = false;
      $l_user      = UserModel::update($pi_uid, $pm_email, $p_name, $p_password, $l_isUpdated);
    }
    else
    {
      $l_isUpdated = true;
      $l_user      = UserModel::create($pm_email, $p_name, $p_password);
    }

    if (false == $l_user)
    {
      log::crit("error while accessing/creating user");
      return false;
    }

    if ($l_isUpdated)
      $this->notifyUser($l_user, $p_password);

    $l_perms = array();
    foreach ($pau_perm as $c_permIdx)
    {
      $l_roleName = sprintf("perm_%d_role", $c_permIdx);
      $l_dataName = sprintf("perm_%d_data", $c_permIdx);

      if ((false === ($l_roleID = $this->getParam($l_roleName))) ||
          (false === ($l_dataID = $this->getParam($l_dataName))))
      {
        log::crit("could not find role '%s' and data '%s' id form permission index '%d'", $l_roleName, $l_dataName, $c_permIdx);
        return false;
      }

      if ($l_dataID == "")
        $l_dataID = null;

      if (false == RoleModel::getByID($l_roleID))
      {
        log::crit("unable to find roleID '%d'", $l_roleID);
        return false;
      }

      array_push($l_perms, array("role" => $l_roleID, "data" => $l_dataID));
    }

    UserModel::setPermissions($l_user, $l_perms);
    return $this->redirect("/wappcore/auth/user.php");
  }

  public function h_edit($pu_uid)
  {
    $this->setContent("file:[auth]user_add.tpl");
    $this->setData("user",      UserModel::getByID($pu_uid));
    $this->setData("roles",     RoleModel::getAll());
    $this->setData("resources", App::get()->getModule("auth")->getResources());
    return true;
  }

  public function h_delete($pu_uid)
  {
    if (false == UserModel::delete($pu_uid))
    {
      log::crit("unable to delete user of id '%d'", $pu_uid);
      return false;
    }

    return $this->redirect("/wappcore/auth/user.php");
  }

  public function h_add()
  {
    $this->setContent("file:[auth]user_add.tpl");
    $this->setData("roles", RoleModel::getAll());
    return true;
  }
}

$l_page = new Page();
$l_page->process();

?>