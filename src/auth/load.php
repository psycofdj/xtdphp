<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/module.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/app.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/menu.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/config.php");
require_once(__WAPPCORE_DIR__  . "/auth/classes/resource.php");

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class authModule extends Module
{
  private $m_perms     = array();
  private $m_actions   = array();
  private $m_resources = array();

  public function __construct($p_baseDir, $p_name)
  {
    parent::__construct($p_baseDir, $p_name, 100);

    $this
      ->registerAction("auth/user/view",        "auth.action.user.view",      null)
      ->registerAction("auth/user/modify",      "auth.action.user.modify",    null)
      ->registerAction("auth/user/terminate",   "auth.action.user.terminate", null)
      ->registerAction("auth/role/view",        "auth.action.role.view",      null)
      ->registerAction("auth/role/modify",      "auth.action.role.modify",    null)
      ->registerAction("auth/role/terminate",   "auth.action.role.terminate", null)
      ;

    App::get()->getMenu()
      ->addWidget("[auth]menu_widget.tpl", array($this, "createWidget"));

    App::get()->getMenu()
      ->addTab(new MenuTab("auth.menu.title"), 80)
      ->addSubTab("auth.menu.users", "/wappcore/auth/user.php", $this->allower("auth/user/view"))
      ->addSubTab("auth.menu.roles", "/wappcore/auth/role.php", $this->allower("auth/role/view"));

    App::get()->connect("Handler", "process",  array($this, "updatePerm"));
    App::get()->connect("Handler", "process",  array($this, "checkPerm"));

    $l_this = $this;
    App::get()->connect("TemplateGenerator", "initialize", function(TemplateGenerator $p_gen) use (&$l_this) {
        $p_gen->addPlugin("block",    "perm",          array($l_this, "pluginPerm"));
        $p_gen->addPlugin("function", "permelse",      array($l_this, "pluginPermElse"));
        $p_gen->addPlugin("function", "perm_if",       array($l_this, "pluginPermIf"));
      });
  }


  public function pluginPermIf($p_params, &$p_smarty)
  {
    $l_content = "disabled='disabled'";
    $l_action  = $p_params["action"];
    $l_handler = App::get()->getHandler();
    $l_data    = $this->getResourceOfAction($l_action);

    if (true == array_key_exists("content", $p_params))
      $l_content = $p_params["content"];

    list($l_isAllowed, $l_errorKey) = $this->isAllowed($l_handler, $l_action, $l_data);
    return ($l_isAllowed) ? "" : $l_content;
  }

  public function pluginPermElse($params, &$smarty)
  {
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
    $l_handler  = App::get()->getHandler();
    $l_data     = $this->getResourceOfAction($l_action);

    list($l_isAllowed, $l_errorKey) = $this->isAllowed($l_handler, $l_action, $l_data);

    if ($l_inverse)
      $l_isAllowed = !$l_isAllowed;
    return ($l_isAllowed) ? $l_hasPerm : $l_elsePerm;
  }

  public function updatePerm(Handler $p_handler, $p_action)
  {
    if ((false != ($l_user       = $p_handler->getSession("auth_user"))) &&
        (false != ($l_systemLast = ConfigModel::get("flush")))           &&
        (false != ($l_userLast   = $p_handler->getSession("flush"))))
    {
      if ($l_systemLast > $l_userLast)
      {
        $l_user = UserModel::getByID($l_user->id);
        $this->loadPrivileges($p_handler, $l_user);
      }
    }
    return true;
  }

  /**
   * Handler plugin to check permission to execute the queried action
   *
   * 1. reteive Acl session object
   * 2. get registered resource
   * 3. use resource to extract current value
   * 4. verify that resource is also registered to acl object
   * 5. query acl for permission
   *
   * @param p_handler Main process handler object
   * @param p_action  handler queried action
   * @return true if permission is ok, false otherwise
   */
  public function checkPerm(Handler $p_handler, $p_action)
  {
    $l_checks = array_filter($this->m_perms, function($p_el) use (&$p_action) {
        return ($p_el["action"] == $p_action);
      });

    if (0 == count($l_checks))
      return true;

    foreach ($l_checks as $c_check)
    {
      list($l_isAllowed, $l_errorKey) = $this->isAllowed($p_handler, $c_check["role"], $c_check["data"]);
      if (false == $l_isAllowed)
        throw new WappError(Locale::resolve($l_errorKey));
    }
    return true;
  }

  public function registerPerm($p_action, $p_roleTag, $p_dataName = null)
  {
    array_push($this->m_perms, array("action" => $p_action, "role" => $p_roleTag, "data" => $p_dataName));
    return $this;
  }

  public function getResources()
  {
    return $this->m_resources;
  }

  public function getResource($p_name)
  {
    foreach ($this->m_resources as $c_res)
      if ($c_res->getName() == $p_name)
        return $c_res;
    return false;
  }

  public function allower($p_action, $p_dataName = null)
  {
    $l_this    = $this;
    $l_functor = function () use ($l_this, $p_action, $p_dataName) {
      $l_handler = App::get()->getHandler();
      list($l_isAllowed, $l_errorKey) = $l_this->isAllowed($l_handler, $p_action, $p_dataName);
      return $l_isAllowed;
    };

    return $l_functor;
  }

  public function isAllowed($p_handler, $p_action, $p_dataName = null)
  {
    if (false == ($l_acl = $p_handler->getSession("auth_acl")))
      return array(false, "auth.error.loginrequiered");

    if (null != $p_dataName)
    {
      if (false == ($l_resource = $this->getResource($p_dataName)))
        throw Exception(sprintf("resource %s is unknown", $p_dataName));
      if (false == ($l_value = $l_resource->getValue($p_handler, $this)))
        throw Exception(sprintf("resource %s has no value", $p_dataName));
      $p_dataName = sprintf("%s:%s", $p_dataName, $l_value);
      if (false == $l_acl->hasResource($p_dataName))
        return array(false, "auth.error.unauthorized");
    }

    if (false == $l_acl->isAllowed("user", $p_dataName, $p_action))
      return array(false, "auth.error.unauthorized");
    return array(true, "");
  }

  public function getResourceOfAction($p_action)
  {
    $l_actions = array_filter($this->m_actions, function($p_el) use ($p_action) {
          return ($p_el["action"] == $p_action);
        });

    if (0 != count($l_actions))
    {
      $l_action = array_shift($l_actions);
      return $l_action["data"];
    }
    return false;
  }

  public function getActions()
  {
    return $this->m_actions;
  }

  public function registerResource(IResource $p_res)
  {
    $this->m_resources[$p_res->getName()] = $p_res;
    return $this;
  }

  public function registerAction($p_action, $p_tag, $p_dataName = null)
  {
    if (($p_dataName != null) && (false == array_key_exists($p_dataName, $this->m_resources)))
      throw new Exception(sprintf("action '%s' requires unknown resource '%s'", $p_action, $p_dataName));

    array_push($this->m_actions,
               array(
                 "action" => $p_action,
                 "tag"    => $p_tag,
                 "data"   => $p_dataName));
    return $this;
  }

  public function createWidget(Handler $p_handler)
  {
    $l_lastFlush = ConfigModel::get("flush");

    $p_handler->setData("auth_user", null);
    if (false != ($l_user = $p_handler->getSession("auth_user")))
      $p_handler->setData("auth_user", $l_user);
  }


  public function loadPrivileges(Handler $p_handler, $p_user)
  {
    $l_perms   = $p_user->ownAuthuserAuthpermList;
    $l_acl     = new Acl();

    $l_acl->addRole(new Role("user"));
    foreach ($l_perms as $c_perm)
    {
      $l_role = $c_perm->authrole;
      $l_data = $c_perm->data;

      if (null != $l_data)
      {
        $l_datatype = $l_role->datatype;
        $l_data     = sprintf("%s:%s", $l_datatype, $l_data);
        if (false == $l_acl->hasResource($l_data))
          $l_acl->addResource(new Resource($l_data));
      }
      foreach ($l_role->sharedAuthactionList as $c_action) {
        $l_acl->allow("user", $l_data, $c_action->tag);
      }
    }

    foreach (App::get()->getModule("auth")->getResources() as $c_res)
    {
      foreach ($p_user->ownAuthuserAuthresourceList as $c_setres)
      {
        if ($c_res->getName() == $c_setres->name)
        {
          if (false == $c_res->setValue($p_handler, $c_setres->value))
          {
            log::crit("auth.login", "value %d is invalid for resource %s", $c_setres->value, $c_setres->name);
            return false;
          }
        }
      }
    }

    $p_handler->setSession("auth_user", $p_user);
    $p_handler->setSession("auth_acl",  $l_acl);
    $p_handler->setSession("flush",     sprintf("%s", time()));
    return true;
  }


  public function setup()
  {
    R::exec("SET FOREIGN_KEY_CHECKS=0");
    R::exec("DROP TABLE IF EXISTS `authuser_authperm`");
    R::exec("DROP TABLE IF EXISTS `authuser_authresource`");
    R::exec("DROP TABLE IF EXISTS `authaction_authrole`");
    R::exec("DROP TABLE IF EXISTS `authaction`");
    R::exec("DROP TABLE IF EXISTS `authrole`");
    R::exec("DROP TABLE IF EXISTS `authuser`");
    R::exec("DROP TABLE IF EXISTS `authconfig`");
    R::exec("SET FOREIGN_KEY_CHECKS=1");

    R::exec(<<<'EOT'
            CREATE TABLE IF NOT EXISTS `authuser`
            (
              `id`       int(11) unsigned                                        NOT NULL AUTO_INCREMENT,
              `mail`     varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `name`     varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,

              PRIMARY KEY (`id`),
              UNIQUE(`mail`)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;
EOT
    );

    R::exec(<<<'EOT'
            CREATE TABLE IF NOT EXISTS `authconfig`
            (
              `id`       int(11) unsigned                                        NOT NULL AUTO_INCREMENT,
              `name`     varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `value`    varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY(`name`)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;
EOT
    );

    R::exec(<<<'EOT'
            CREATE TABLE `authrole` (
              `id`       int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name`     varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `datatype` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `UQ_name` (`name`)
            ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
EOT
    );

    R::exec(<<<'EOT'
            CREATE TABLE `authuser_authresource` (
              `id`          int(11) unsigned NOT NULL AUTO_INCREMENT,
              `authuser_id` int(11) unsigned NOT NULL,
              `name`        varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `value`       varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `index_foreignkey_authuser_authresource_authuser` (`authuser_id`),
              CONSTRAINT `c_fk_authuser_authresource_authuser_id` FOREIGN KEY (`authuser_id`) REFERENCES `authuser` (`id`)  ON DELETE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
EOT
    );

    ConfigModel::create("flush", sprintf("%s", time()));

    $l_actions = array();
    foreach ($this->m_actions as $c_action)
    {
      $l_action            = R::dispense('authaction');
      $l_action->tag       = $c_action["action"];
      $l_action->localetag = $c_action["tag"];
      $l_action->datatype  = $c_action["data"];
      R::store($l_action);
      array_push($l_actions, $l_action);
    }

    $l_roleAdmin                       = R::dispense("authrole");
    $l_roleAdmin->name                 = "Super Admin";
    $l_roleAdmin->sharedAuthactionList = array_filter($l_actions, function($p_el) {
        return $p_el->datatype == null;
      });
    R::store($l_roleAdmin);

    $l_roleOther                       = R::dispense("authrole");
    $l_roleOther->name                 = "Super Garages";
    $l_roleOther->datatype             = "garages";
    $l_roleOther->sharedAuthactionList = array_filter($l_actions, function($p_el) {
        return $p_el->datatype == "garages";
      });
    R::store($l_roleOther);

    $l_user                 = R::dispense("authuser", 1);
    $l_user->mail           = "sa@sa.com";
    $l_user->name           = "Super Admin";
    $l_user->password       = md5("sasasasa");
    $l_user->link("authuser_authperm",     array('data' => null))->authrole = $l_roleAdmin;
    $l_user->link("authuser_authperm",     array('data' => "84"))->authrole = $l_roleOther;
    $l_user->link("authuser_authresource", array('name' => "garages", "value" => "84"));
    R::store($l_user);

  }

}

?>
