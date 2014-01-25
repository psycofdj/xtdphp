<?php

define("__APP_DIR__",      dirname(__FILE__));
define("__WAPPCORE_DIR__", sprintf("%s/src/", __APP_DIR__))

require_once(__WAPPCORE_DIR__ . "/core/log.php");

global $g_conf;
$g_conf["mysql"]["username"] = "clienttest";
$g_conf["mysql"]["password"] = "clienttest";
$g_conf["mysql"]["database"] = "clienttest";

?>