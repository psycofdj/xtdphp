<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(dirname(__FILE__) . "/../../local.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/phpmailer/class.phpmailer.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/generator.php");


class MailTemplate extends TemplateGenerator
{
  private $m_htmlTpl = null;
  private $m_txtTpl  = null;
  private $m_images  = array();
  private $m_files   = array();

  function __construct($p_mailName, $p_dest, $p_brandLogo = true, $p_handler)
  {
    global $g_conf;

    parent::__construct("dummy");
    parent::initialize($p_handler);

    $this->m_mail             = new PHPMailer();
    $this->m_to               = $p_dest;
    $this->m_mail->From       = $g_conf["mail"]["from"];
    $this->m_mail->Host       = $g_conf["mail"]["host"];
    $this->m_mail->Hostname   = $g_conf["mail"]["host"];
    $this->m_mail->Sender     = $g_conf["mail"]["from"];
    $this->m_mail->CharSet    = $g_conf["mail"]["charset"];
    $this->m_mail->FromName   = $g_conf["mail"]["name"];

    $this->m_smarty->setCompileDir(sprintf("%s/mails_c", __APP_DIR__));
    $this->m_smarty->setTemplateDir(array());
    foreach (App::get()->getModules() as $c_module) {
      $l_name = $c_module->getName();
      $l_path = sprintf("%s/%s/mails", $c_module->getBaseDir(), $l_name);
      $this->m_smarty->addTemplateDir($l_path, $l_name);
    }

    $this->setTemplate($p_mailName);
    $this->m_mail->addReplyTo($g_conf["mail"]["from"], $g_conf["mail"]["name"]);


    $this
      ->setData("__base_url",   tools::getBaseUrl())
      ->setData("__brand_name", $g_conf["style"]["name"]);
    if ($p_brandLogo)
      $this->addImage($g_conf["style"]["brand"], "__brand_logo", true);
  }

  public function setTemplate($p_name)
  {
    $this->m_htmlTpl    = sprintf("%s.%s.html.tpl",    $p_name, Locale::getName());
    $this->m_txtTpl     = sprintf("%s.%s.txt.tpl",     $p_name, Locale::getName());
    $this->m_subjectTpl = sprintf("%s.%s.subject.tpl", $p_name, Locale::getName());
    return $this;
  }

  public function addImage($p_path, $p_id, $p_fromBase = true)
  {
    if (true == $p_fromBase)
      $p_path = sprintf("%s/%s", __APP_DIR__, $p_path);
    $this->m_images[$p_path] = $p_id;
    return $this;
  }

  public function addFile($p_path, $p_type = "application/octet-stream", $p_fromBase = true)
  {
    if (true == $p_fromBase)
      $p_path = sprintf("%s/%s", __APP_DIR__, $p_path);
    $this->m_files[$p_path] = $p_type;
    return $this;
  }

  public function setDest($p_to)
  {
    $this->m_to = $p_to;
    return $this;
  }

  private function _send()
  {
    foreach ($this->m_data as $c_key => $c_value)
      $this->m_smarty->assign($c_key, $c_value);

    $l_txtText     = $this->m_smarty->fetch($this->m_txtTpl);
    $l_htmlText    = $this->m_smarty->fetch($this->m_htmlTpl);
    $l_subjectText = $this->m_smarty->fetch($this->m_subjectTpl);

    foreach ($this->m_images as $c_path => $c_id)
      $this->m_mail->AddEmbeddedImage($c_path, $c_id, basename($c_path));
    foreach ($this->m_files as $c_path => $c_type)
      $this->m_mail->AddAttachment($c_path, basename($c_path), "base64", $c_type);

    $this->m_mail->Subject = $l_subjectText;
    $this->m_mail->Body    = $l_htmlText;
    $this->m_mail->AltBody = $l_txtText;
    $this->m_mail->AddAddress($this->m_to);

    if (false == ($l_status = $this->m_mail->Send()))
      log::warn("core.mail", "error while sending mail to '%s'", $this->m_to);

    return $l_status;
  }

  public function send()
  {
    if (false == $this->_send())
    {
      log::crit("core.mail", "unable to send mail to %s (txtTpl=%s, htmlTpl=%s)", $this->m_to, $this->m_txtTpl, $this->m_htmlTpl);
      return false;
    }
    return true;
  }
}

/* Local Variables: */
/* ispell-local-dictionary: "american" */
/* End: */

?>
