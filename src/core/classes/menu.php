<?php

class Menu
{
  private $m_tabs    = Array();
  private $m_widgets = Array();
  private $m_brand   = Array("title" => "Wappcore Corp",
      "url"   => "/");

  public function addTab($p_tab, $p_priority = null)
  {
    if (null == $p_priority) {
      $p_priority = array_reduce($this->m_tabs, function ($p_s1, $p_s2) {
            $p_s1 = max($p_s1, $p_s2);
            return $p_s1;
          }, 0) + 10;
    }
    array_push($this->m_tabs, $p_tab->setPriority($p_priority));
    return $p_tab;
  }

  public function getTabs()
  {
    usort($this->m_tabs, function($p_tab1, $p_tab2) {
          return $p_tab1->getPriority() > $p_tab2->getPriority();
        });
    return $this->m_tabs;
  }

  public function addWidget($p_template, $p_callback = null)
  {
    array_push($this->m_widgets,
        array("tpl"      => $p_template,
            "callback" => $p_callback));
    return $this;
  }

  public function getWidgets()
  {
    return $this->m_widgets;
  }

  public function getBrand()
  {
    return $this->m_brand;
  }

  public function setBrand($p_title, $p_url)
  {
    $this->m_brand = Array("title" => $p_title, "url" => $p_url);
    return $this;
  }

  public function initialize($p_handler)
  {
    foreach ($this->getWidgets() as $c_widget)
    {
      if (null != $c_widget["callback"]) {
        call_user_func($c_widget["callback"], $p_handler);
      }
    }
  }
}

class MenuTab
{
  public $m_title;
  public $m_link;
  public $m_subTabs;
  public $m_role;
  public $m_priority;

  function __construct($p_title, $p_link = "/", $p_action = null)
  {
    $this->m_title    = $p_title;
    $this->m_link     = $p_link;
    $this->m_subTabs  = array();
    $this->m_action   = $p_action;
    $this->m_priority = 0;
  }

  function addSubTab($p_title, $p_link = "/", $p_action = null)
  {
    array_push($this->m_subTabs, new MenuTab($p_title, $p_link, $p_action));
    return $this;
  }

  function setPriority($p_priority)
  {
    $this->m_priority = $p_priority;
    return $this;
  }

  function getPriority()
  {
    return $this->m_priority;
  }

  function hasTabs()
  {
    return 0 != count($this->m_subTabs);
  }

  function getTabs()
  {
    return $this->m_subTabs;
  }

  function isAllowed($p_acl)
  {
    if (true == $this->hasTabs())
      return array_reduce($this->m_subTabs, function ($p_res, $p_obj) use (&$p_acl) {
            $p_res = $p_res || $p_obj->isAllowed($p_acl);
            return $p_res;
          }, false);
    if (false == $this->m_action)
      return true;
    return $p_acl->isAllowed("user", null, $this->m_action);
  }

  function isActiveUrl()
  {
    if (true == $this->hasTabs())
      return array_reduce($this->m_subTabs, function ($p_res, $p_obj) {
            $p_res = $p_res || $p_obj->isActiveUrl();
            return $p_res;
          }, false);

    $l_needle       = "index.php";
    $l_needleSize   = strlen($l_needle);
    $l_current      = $_SERVER['PHP_SELF'];
    $l_currentClean = $l_current;

    if ($l_needle == substr($l_current, - $l_needleSize, $l_needleSize)) // ends_with($l_current, $l_needle)
      $l_currentClean = substr($l_current, 0, strlen($l_current) - $l_needleSize);

    return ($l_currentClean == $this->m_link);
  }
}

?>