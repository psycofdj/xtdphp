<?php

require_once __DIR__ . '/Loader/ClassMapAutoloader.php';

$loader = new Zend\Loader\ClassMapAutoloader();
$loader->registerAutoloadMap(__DIR__ . '/classmap.php');
$loader->register();

?>
