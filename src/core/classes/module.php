<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(__WAPPCORE_DIR__  . "/core/classes/log.php");

class Module
{
  private $m_baseDir;
  private $m_name;
  private $m_widgets;
  private $m_priority;

  protected function __construct($p_baseDir, $p_name, $p_priority)
  {
    log::debug("core.module", "initializing module '%s' in directory '%s'", $p_name, $p_baseDir);
    $this->m_baseDir  = $p_baseDir;
    $this->m_name     = $p_name;
    $this->m_priority = $p_priority;
  }

  public function getPriority()
  {
    return $this->m_priority;
  }

  public function getName()
  {
    return $this->m_name;
  }

  public function getBaseDir()
  {
    return $this->m_baseDir;
  }

  public function getUri()
  {
    $l_wappPath   = realpath(__WAPPCORE_DIR__ . "/");
    $l_appPath    = realpath(__APP_DIR__      . "/");
    $l_modulePath = realPath(sprintf("/%s/%s", $this->m_baseDir, $this->m_name));

    if ($l_wappPath == substr($l_modulePath, 0, strlen($l_wappPath)))
      return sprintf("/wappcore%s", substr($l_modulePath, strlen($l_wappPath)));
    return substr($l_modulePath, strlen($l_appPath));
  }

  public function initialize($p_app)
  {
  }

  public function setup()
  {
  }
}
?>