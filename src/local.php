<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

ini_set("error_reporting", E_ALL | E_NOTICE);
ini_set("display_errors",  1);

if (false == defined("__WAPPCORE_DIR__"))
{
  if (false == ($l_value = getenv("__WAPPCORE_DIR__")))
    $l_value = dirname(__FILE__);
  define("__WAPPCORE_DIR__", $l_value);
}

if (false == defined("__APP_DIR__"))
{
  if (false == ($l_value = getenv("__APP_DIR__")))
    die("unknown __APP_DIR__ env variable");
  define("__APP_DIR__", $l_value);
}

require_once(__WAPPCORE_DIR__ . "/core/classes/log.php");

/* -------------------------------------------------------------------------- */

$g_conf                       = array();
$g_conf["env"]                = "prod";
$g_conf["version"]            = "0";
$g_conf["session"]            = array();
$g_conf["session"]["handler"] = "default";

$g_conf["style"]              = array();
$g_conf["style"]["name"]      = "Wappcore";
$g_conf["style"]["favicon"]   = "/wappcore/core/images/wappcore_logo.png";
$g_conf["style"]["brand"]     = "/wappcore/core/images/wappcore.png";

$g_conf["web"]                = array();
$g_conf["web"]["cleanurl"]    = false;

$g_conf["log"]                = array();
$g_conf["log"]["level"]       = log::mc_levelCrit;

$g_conf["mysql"]              = array();
$g_conf["mysql"]["host"]      = "localhost";
$g_conf["mysql"]["port"]      = 3306;
$g_conf["mysql"]["username"]  = "default";
$g_conf["mysql"]["password"]  = "default";
$g_conf["mysql"]["database"]  = "default";

$g_conf["mail"]               = array();
$g_conf["mail"]["from"]       = "no-reply@wapp.pro";
$g_conf["mail"]["name"]       = "no-reply";
$g_conf["mail"]["host"]       = "localhost";
$g_conf["mail"]["port"]       = 25;
$g_conf["mail"]["charset"]    = "utf-8";

require_once(__APP_DIR__      . "/local.php");

/* -------------------------------------------------------------------------- */

log::setDefaultLevel($g_conf["log"]["level"]);

if ($g_conf["env"] == "dev")
  ini_set( "display_errors",  1 );

require_once(__WAPPCORE_DIR__ . "/core/classes/app.php");

App::get();

/* -------------------------------------------------------------------------- */

?>