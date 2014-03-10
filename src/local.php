<?php

ini_set( "error_reporting", E_ALL);

if (false == defined("__APP_DIR__"))
  define("__APP_DIR__",      getenv("__APP_DIR__"));
if (false == defined("__WAPPCORE_DIR__"))
  define("__WAPPCORE_DIR__", getenv("__WAPPCORE_DIR__"));

require_once(__WAPPCORE_DIR__ . "/core/log.php");

/* -------------------------------------------------------------------------- */

$g_conf                       = Array();
$g_conf["env"]                = "prod";

$g_conf["web"]                = Array();
$g_conf["web"]["uri"]         = Array();
$g_conf["web"]["uri"]["app"]  = "/";
$g_conf["web"]["uri"]["core"] = "/wappcore/core";
$g_conf["web"]["host"]        = "localhost";

$g_conf["log"]                = Array();
$g_conf["log"]["level"]       = 7;

$g_conf["mysql"]              = Array();
$g_conf["mysql"]["host"]      = "localhost";
$g_conf["mysql"]["port"]      = 3306;
$g_conf["mysql"]["username"]  = "default";
$g_conf["mysql"]["password"]  = "default";
$g_conf["mysql"]["database"]  = "default";

$g_conf["mail"]               = Array();
$g_conf["mail"]["from"]       = "no-reply";
$g_conf["mail"]["name"]       = "no-reply";
$g_conf["mail"]["host"]       = "localhost";
$g_conf["mail"]["port"]       = 25;
$g_conf["mail"]["charset"]    = "utf-8";

require_once(__APP_DIR__      . "/local.php");

/* -------------------------------------------------------------------------- */

log::setLevel($g_conf["log"]["level"]);

/* -------------------------------------------------------------------------- */

?>
