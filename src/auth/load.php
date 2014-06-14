<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/module.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/app.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/menu.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");


interface IResource
{
  public function getName();
  public function generate();
}

class GarageResource implements IResource
{
  public function getName()
  {
    return "garages";
  }

  public function generate()
  {
    return array(
      array("id" => 1,    "label" => "Garage 1"),
      array("id" => 2,    "label" => "Garage 2"),
      array("id" => null, "label" => "All garages"),
    );
  }
}

class authModule extends Module
{
  private $m_perms     = array();
  private $m_actions   = array();
  private $m_resources = array();

  public function __construct($p_baseDir, $p_name)
  {
    parent::__construct($p_baseDir, $p_name, 100);

    $l_garage = new GarageResource();
    $this
      ->registerResource($l_garage);

    $this
      ->registerAction("auth/user/view",        "auth.action.user.view",      null)
      ->registerAction("auth/user/modify",      "auth.action.user.modify",    null)
      ->registerAction("auth/user/terminate",   "auth.action.user.terminate", null)
      ->registerAction("auth/role/view",        "auth.action.role.view",      null)
      ->registerAction("auth/role/modify",      "auth.action.role.modify",    null)
      ->registerAction("auth/role/terminate",   "auth.action.role.terminate", null)
      ->registerAction("activity/event/view",   "a.b.c.",                     $l_garage->getName())
      ->registerAction("activity/event/modify", "a.b.c.",                     $l_garage->getName())
      ;

    App::get()->getMenu()
      ->addWidget("file:[auth]menu_widget.tpl", array($this, "createWidget"));

    App::get()->getMenu()
      ->addTab(new MenuTab("auth.menu.title"), 80)
      ->addSubTab("auth.menu.users", "/wappcore/auth/user.php", "auth/user/view")
      ->addSubTab("auth.menu.roles", "/wappcore/auth/role.php", "auth/role/view");

    App::get()->connect("Handler", "process",  array($this, "checkPerm"));

    $l_this = $this;
    App::get()->connect("TemplateGenerator", "initialize", function(TemplateGenerator $p_gen) use (&$l_this) {
          $p_gen->addPlugin("block",    "perm",     array($l_this, "pluginPerm"));
          $p_gen->addPlugin("function", "permelse", array($l_this, "pluginPermElse"));
        });
  }


  public function pluginPermElse($params, &$smarty) {
    return $smarty->left_delimiter . 'pelse' . $smarty->right_delimiter;
  }

  public function pluginPerm($p_params, $p_content, &$p_smarty, &$p_repeat)
  {
    if (!$p_content)
      return;

    $l_pelse    = $p_smarty->left_delimiter . 'pelse' . $p_smarty->right_delimiter;
    $l_parts    = explode($l_pelse, $p_content, 2);
    $l_hasPerm  = (isset($l_parts[0]) ? $l_parts[0] : null);
    $l_elsePerm = (isset($l_parts[1]) ? $l_parts[1] : null);
    $l_inverse  = isset($p_params["inverse"]) && ($p_params["inverse"] == "true");
    $l_action   = $p_params["action"];
    $l_acl      = $p_smarty->getTemplateVars("auth_acl");
    $l_valid    = $l_acl->isAllowed("user", null, $l_action);

    if ($l_inverse)
      $l_valid = !$l_valid;

    return ($l_valid) ? $l_hasPerm : $l_elsePerm;
  }

  public function checkPerm(Handler $p_handler, $p_action)
  {
    $l_checks = array_filter($this->m_perms, function($p_el) use (&$p_action) {
          return ($p_el["action"] == $p_action);
        });

    if (0 == count($l_checks))
      return;

    if (false == ($l_acl = $p_handler->getSession("auth_acl")))
      throw new WappError(Locale::resolve("auth.error.loginrequiered"));

    $l_data = $p_handler->getSession("auth_data");

    foreach ($l_checks as $c_check)
    {
      if (false == $l_acl->isAllowed("user", $c_check["data"], $c_check["role"]))
        throw new WappError(Locale::resolve("auth.error.unauthorized"));
    }
    return true;
  }

  public function registerPerm($p_action, $p_roleTag, $p_dataName = null)
  {
    if (($p_dataName != null) && (false == array_key_exists($p_dataName, $this->m_data)))
      throw new Exception("data not found : " . $p_dataName);
    array_push($this->m_perms, array("action" => $p_action, "role" => $p_roleTag, "data" => $p_dataName));
    return $this;
  }

  public function getResources()
  {
    return $this->m_resources;
  }

  public function registerResource(IResource $p_res)
  {
    $this->m_resources[$p_res->getName()] = $p_res;
    return $this;
  }

  public function registerAction($p_tag, $p_localetag, $p_dataName = null)
  {
    if (($p_dataName != null) && (false == array_key_exists($p_dataName, $this->m_resources)))
      throw new Exception(sprintf("action '%s' requires unknown resource '%s'", $p_tag, $p_dataName));

    array_push($this->m_actions,
        array(
          "tag"       => $p_tag,
          "localetag" => $p_localetag,
          "data"      => $p_dataName));
    return $this;
  }

  public function setup()
  {
    R::exec("SET FOREIGN_KEY_CHECKS=0");
    R::exec("DROP TABLE IF EXISTS `authuser`");
    R::exec("DROP TABLE IF EXISTS `authaction`");
    R::exec("DROP TABLE IF EXISTS `authrole`");
    R::exec("DROP TABLE IF EXISTS `authaction_authrole`");
    R::exec("DROP TABLE IF EXISTS `authuser_authperm`");
    R::exec("SET FOREIGN_KEY_CHECKS=1");

    R::exec(<<<'EOT'
            CREATE TABLE IF NOT EXISTS `authuser`
            (
             `id`       int(11)                                                 NOT NULL AUTO_INCREMENT,
             `mail`     varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
             `name`     varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
             `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,

             PRIMARY KEY (`id`),
             UNIQUE(`mail`)
             ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;
EOT
    );

    $l_actions = array();
    foreach ($this->m_actions as $c_action)
    {
      $l_action            = R::dispense('authaction');
      $l_action->tag       = $c_action["tag"];
      $l_action->localetag = $c_action["localetag"];
      $l_action->datatype  = $c_action["data"];
      R::store($l_action);
      array_push($l_actions, $l_action);
    }

    $l_roleAdmin                       = R::dispense("authrole");
    $l_roleAdmin->name                 = "superadmin";
    $l_roleOther->datatype             = null;
    $l_roleAdmin->sharedAuthactionList = array_filter($l_actions, function($p_el) {
          return $p_el->datatype == null;
        });
    R::store($l_roleAdmin);

    $l_roleOther                       = R::dispense("authrole");
    $l_roleOther->name                 = "other";
    $l_roleOther->datatype             = "garages";
    $l_roleOther->sharedAuthactionList = array_filter($l_actions, function($p_el) {
          return $p_el->datatype == "garages";
        });
    R::store($l_roleOther);

    $l_user                 = R::dispense("authuser", 1);
    $l_user->mail           = "sa@sa.com";
    $l_user->name           = "Super Admin";
    $l_user->password       = md5("sasasasa");
    $l_user->link("authuser_authperm", array('data' => null))->authrole = $l_roleAdmin;
    R::store($l_user);

  }

  public function createWidget(Handler $p_handler)
  {
    $p_handler->setData("auth_user", null);
    $p_handler->setData("auth_perm", null);

    if (false != ($l_user = $p_handler->getSession("auth_user")))
      $p_handler->setData("auth_user",   $l_user);
    if (false != ($l_acl = $p_handler->getSession("auth_acl")))
      $p_handler->setData("auth_acl", $l_acl);
  }

}

?>