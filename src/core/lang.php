<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/locale.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/log.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");

class LangPage extends HTTPHandler
{
  public function __construct()
  {
    parent::__construct(new BinaryGenerator());
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
      log::warning("core.language", "unknown requested lang '%s'", $p_lang);
    }
    return $this->redirect("/");
  }
}


$l_page = new LangPage();
$l_page->process();

?>