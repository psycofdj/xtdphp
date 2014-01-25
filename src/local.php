<?php

error_reporting(E_ALL);

require_once(__WAPPCORE_DIR__ . "/core/log.php");

/* -------------------------------------------------------------------------- */

$g_conf                      = Array();
$g_conf["web"]               = Array();
$g_conf["web"]["uri"]        = "";
$g_conf["web"]["host"]       = "{{SAFEBE_URL}}";
$g_conf["log"]               = Array();
$g_conf["log"]["level"]      = 7;
$g_conf["mysql"]             = Array();
$g_conf["mysql"]["host"]     = "localhost";
$g_conf["mysql"]["port"]     = 3306;
$g_conf["mysql"]["username"] = "default";
$g_conf["mysql"]["password"] = "default";
$g_conf["mysql"]["database"] = "default";
$g_conf["mail"]              = Array();
$g_conf["mail"]["from"]      = "no-reply";
$g_conf["mail"]["name"]      = "no-reply";
$g_conf["mail"]["host"]      = "localhost";
$g_conf["mail"]["port"]      = 25;
$g_conf["mail"]["charset"]   = "utf-8";

/* -------------------------------------------------------------------------- */

log::setLevel($g_conf["log"]["level"]);

/* -------------------------------------------------------------------------- */

?>
