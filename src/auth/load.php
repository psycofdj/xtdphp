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
require_once(__WAPPCORE_DIR__  . "/core/classes/module.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/app.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/menu.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/config.php");
require_once(__WAPPCORE_DIR__  . "/auth/classes/resource.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/generator.php");

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
  }

  public function initialize($p_app)
  {
    $this
      ->registerAction("auth/user/view",        "auth.action.user.view",      null)
      ->registerAction("auth/user/modify",      "auth.action.user.modify",    null)
      ->registerAction("auth/user/terminate",   "auth.action.user.terminate", null)
      ->registerAction("auth/role/view",        "auth.action.role.view",      null)
      ->registerAction("auth/role/modify",      "auth.action.role.modify",    null)
      ->registerAction("auth/role/terminate",   "auth.action.role.terminate", null)
      ;

    $p_app->getMenu()
      ->addWidget("[auth]menu_widget.tpl", array($this, "createWidget"));

    $p_app->getMenu()
      ->addTab(new MenuTab("auth.menu.title"), 80)
      ->addSubTab("auth.menu.users", "/wappcore/auth/user.php", $this->allower("auth/user/view"))
      ->addSubTab("auth.menu.roles", "/wappcore/auth/role.php", $this->allower("auth/role/view"));

    $p_app->connect("HTTPHandler", "process",  array($this, "updatePerm"));
    $p_app->connect("HTTPHandler", "process",  array($this, "checkPerm"));

    TemplateGenerator::addStaticPlugin("block",    "perm",     array($this, "pluginPerm"));
    TemplateGenerator::addStaticPlugin("function", "permelse", array($this, "pluginPermElse"));
    TemplateGenerator::addStaticPlugin("function", "perm_if",  array($this, "pluginPermIf"));
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

  public function updatePerm(HTTPHandler $p_handler, $p_action)
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
  public function checkPerm(HTTPHandler $p_handler, $p_action)
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
        throw new WappError(Locale::resolve($l_errorKey), 401);
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

  public function allowerFor($p_action, $p_resource)
  {
    $l_this    = $this;
    $l_functor = function ($p_value) use ($l_this, $p_action, $p_resource) {
      $l_handler = App::get()->getHandler();
      list($l_isAllowed, $l_errorKey) = $l_this->isAllowedFor($l_handler, $p_action, $p_resource, $p_value);
      return $l_isAllowed;
    };
    return $l_functor;
  }

  public function isAllowedFor($p_handler, $p_action, $p_resource, $p_value)
  {
    if (false == ($l_acl = $p_handler->getSession("auth_acl")))
      return array(false, "auth.error.loginrequiered");
    $l_label = sprintf("%s:%s", $p_resource->getName(), $p_value);
    if (false == $l_acl->hasResource($l_label))
      return array(false, "auth.error.unauthorized");
    if (false == $l_acl->isAllowed("user", $l_label, $p_action))
      return array(false, "auth.error.unauthorized");
    return array(true, "");
  }


  public function isAllowed($p_handler, $p_action, $p_dataName = null)
  {
    if (false == ($l_acl = $p_handler->getSession("auth_acl")))
      return array(false, "auth.error.loginrequiered");

    if (null != $p_dataName)
    {
      if (false == ($l_resource = $this->getResource($p_dataName)))
        throw new Exception(sprintf("resource %s is unknown", $p_dataName));
      if (false == ($l_value = $l_resource->getValue($p_handler)))
        throw new Exception(sprintf("resource %s has no value", $p_dataName));
      return $this->isAllowedFor($p_handler, $p_action, $l_resource, $l_value);
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

  public function createWidget(HTTPHandler $p_handler)
  {
    $l_lastFlush = ConfigModel::get("flush");

    $p_handler->setData("auth_user", null);
    if (false != ($l_user = $p_handler->getSession("auth_user")))
      $p_handler->setData("auth_user", $l_user);
  }


  public function loadPrivileges(HTTPHandler $p_handler, $p_user)
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
    R::exec(<<<'EOT'
            CREATE TABLE `authuser`
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
            CREATE TABLE `authconfig`
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

    $l_types = array();
    foreach ($l_actions as $c_action)
    {
      $l_key = $c_action->datatype;
      if (null == $l_key)
        $l_key = "all";
      if (false == array_key_exists($l_key, $l_types))
        $l_types[$l_key] = array();
      array_push($l_types[$l_key], $c_action);
    }


    $l_user                 = R::dispense("authuser", 1);
    $l_user->mail           = "sa@sa.com";
    $l_user->name           = "Super Admin";
    $l_user->password       = md5("sasasasa");

    foreach ($l_types as $c_name => $c_actions)
    {
      $l_role                       = R::dispense("authrole");
      $l_role->name                 = sprintf("Super admin for '%s'", $c_name);
      $l_role->sharedAuthactionList = $c_actions;
      if ($c_name == "all")
        $c_name = null;
      $l_role->datatype = $c_name;
      R::store($l_role);

      if ($c_name == null)
      {
        $l_user->link("authuser_authperm", array('data' => $c_name))->authrole = $l_role;
      }
      else
      {
        $l_first     = true;
        $l_resources = $this->getResource($c_name)->generate();
        foreach ($l_resources as $c_item)
        {
          $l_user->link("authuser_authperm", array('data' => $c_item["id"]))->authrole = $l_role;
          if ($l_first == true)
          {
            $l_user->link("authuser_authresource", array('name' => $c_name, "value" => $c_item["id"]));
            $l_first = false;
          }
        }
      }
    }
    R::store($l_user);
  }

}

?>