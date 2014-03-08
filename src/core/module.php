<?php

require_once(__WAPPCORE_DIR__  . "/core/log.php");

class Module
{
  private $m_baseDir;
  private $m_name;
  private $m_widgets;

  public function getName()
  {
    return $this->m_name;
  }

  public function getBaseDir()
  {
    return $this->m_baseDir;
  }

  protected function __construct($p_baseDir, $p_name)
  {
    log::debug("initializing module '%s' in directory '%s'", $p_name, $p_baseDir);
    $this->m_baseDir = $p_baseDir;
    $this->m_name    = $p_name;
  }

  public function setup()
  {
  }
}