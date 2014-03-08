<?php

require_once(__WAPPCORE_DIR__  . "/core/log.php");

class Module
{
  private $m_name;
  private $m_widgets;

  public function getName()
  {
    return $this->m_name;
  }

  protected function __construct($p_name)
  {
    log::debug("initializing module '%s'", $p_name);
    $this->m_name = $p_name;
  }

  public function setup()
  {
  }
}