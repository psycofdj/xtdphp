<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/locale.php");
require_once(__WAPPCORE_DIR__  . "/core/log.php");
require_once(__WAPPCORE_DIR__  . "/core/handler.php");

class LangPage extends Handler
{
  public function __construct()
  {
    parent::__construct();
  }

  public function h_default($p_lang)
  {
    switch ($p_lang)
    {
    case "fr":
    case "en":
      {
        $this->setSession("lang", $p_lang);
        break;
      }
    default:
      log::warning("unknown requested lang '%s'", $p_lang);
    }
    return $this->redirect("/");
  }
}


$l_page = new LangPage();
$l_page->process();

?>