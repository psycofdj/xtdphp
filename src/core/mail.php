<?php

require_once(dirname(__FILE__) . "/../conf/local.php");
require_once(__BASE_DIR__      . "/lib/locale.php");
require_once(__BASE_DIR__      . "/lib/Smarty/Smarty.class.php");
require_once(__BASE_DIR__      . "/lib/phpmailer/class.phpmailer.php");


class TemplateMail
{
  private $m_smarty;
  private $m_htmlTpl;
	private $m_txtTpl;
	private $m_images = Array();
	private $m_files = Array();

  function __construct()
  {
    global $g_conf;

		$this->m_htmlTpl = "";
		$this->m_txtTpl  = "";
    $this->m_images  = Array();
    $this->m_files   = Array();
    $this->m_smarty  = new Smarty();
    $this->m_smarty
      ->setCompileDir(sprintf("%s/mails_c", __BASE_DIR__))
      ->setTemplateDir(sprintf("%s/mails",  __BASE_DIR__))
      ->setCacheDir(sprintf("%s/cache",    __BASE_DIR__))
      ->registerPlugin("block", "t", array($this, 'translate'));

		$this->m_mail             = new PHPMailer();
		$this->m_mail->From       = $g_conf["mail"]["from"];
		$this->m_mail->Host       = $g_conf["mail"]["host"];
		$this->m_mail->Hostname   = $g_conf["mail"]["host"];
		$this->m_mail->Sender     = $g_conf["mail"]["from"];
		$this->m_mail->CharSet    = $g_conf["mail"]["charset"];
		$this->m_mail->FromName   = $g_conf["mail"]["name"];
		$this->m_mail->addReplyTo($g_conf["mail"]["from"], $g_conf["mail"]["name"]);
  }

  public function translate($p_params, $p_content, &$p_smarty, &$p_repeat)
  {
    if (!$p_content)
      return;
    $p_content = trim($p_content);
    return ø($p_content);
  }

  protected function setTemplates($p_htmlTpl, $p_txtTpl)
  {
    $this->m_htmlTpl = sprintf("%s/%s", Locale::getName(), $p_htmlTpl);
    $this->m_txtTpl  = sprintf("%s/%s", Locale::getName(), $p_txtTpl);
  }

	protected function addImage($p_path, $p_id, $p_fromBase = true)
	{
    if (true == $p_fromBase)
      $p_path = sprintf("%s/%s", __BASE_DIR__, $p_path);
		$this->m_images[$p_path] = $p_id;
	}

	protected function addFile($p_path, $p_type = "application/octet-stream", $p_fromBase = true)
	{
    if (true == $p_fromBase)
      $p_path = sprintf("%s/%s", __BASE_DIR__, $p_path);
		$this->m_files[$p_path] = $p_type;
	}

	protected function assign($p_prop, $p_value)
  {
    $this->m_smarty->assign($p_prop, $p_value);
	}

  protected function setDest($p_to)
  {
    $this->m_to      = $p_to;
  }
  protected function setSubject($p_subject)
  {
    $this->m_subject = $p_subject;
    $this->assign("title", $p_subject);
  }

	private function _send()
	{
		$l_txtText  = $this->m_smarty->fetch($this->m_txtTpl);
		$l_htmlText = $this->m_smarty->fetch($this->m_htmlTpl);

		foreach ($this->m_images as $c_path => $c_id)
			$this->m_mail->AddEmbeddedImage($c_path, $c_id, basename($c_path));
		foreach ($this->m_files as $c_path => $c_type)
			$this->m_mail->AddAttachment($c_path, basename($c_path), "base64", $c_type);

		$this->m_mail->Subject = $this->m_subject;
		$this->m_mail->Body    = $l_htmlText;
		$this->m_mail->AltBody = $l_txtText;
		$this->m_mail->AddAddress($this->m_to);
    return $this->m_mail->Send();
  }


  public function send()
  {
    try {
      if (false == $this->_send())
      {
        log::crit("unable to send mail to %s (txtTpl=%s, htmlTpl=%s)", $this->m_to, $this->m_txtTpl, $this->m_htmlTpl);
        return false;
      }
    }
    catch (Exception $l_error)
    {
      log::crit("error while sending mail to %s (txtTpl=%s, htmlTpl=%s) : %s", $this->m_to, $this->m_txtTpl, $this->m_htmlTpl, $l_error->getMessage());
      return false;
    }
    return true;
  }
}


/* -------------------------------------------------------------------------- */

class ViewMail extends TemplateMail
{
  public function __construct($p_dest, $p_subject, $p_data)
  {
    parent::__construct();
    $this->setDest($p_dest);
    $this->setSubject($p_subject);
    $this->setTemplates("view.html.tpl", "view.txt.tpl");
    $this->addImage("./images/logo-fond-transparent-small.png", "safebe-logo");
    foreach ($p_data as $c_key => $c_value)
      $this->assign($c_key, $c_value);
  }
}

class ImageUrlMail extends TemplateMail
{
  public function __construct($p_dest, $p_subject, $p_data)
  {
    parent::__construct();
    $this->setDest($p_dest);
    $this->setSubject($p_subject);
    $this->setTemplates("imageUrl.html.tpl", "imageUrl.txt.tpl");
    $this->addImage("./images/logo-fond-transparent-small.png", "safebe-logo");
    foreach ($p_data as $c_key => $c_value)
      $this->assign($c_key, $c_value);
  }
}

class NewUserMail extends TemplateMail
{
  public function __construct($p_dest, $p_subject, $p_data)
  {
    parent::__construct();
    $this->setDest($p_dest);
    $this->setSubject($p_subject);
    $this->setTemplates("newUser.html.tpl", "newUser.txt.tpl");
    $this->addImage("./img/logo-medium.jpg../img", "safebe-logo");
    foreach ($p_data as $c_key => $c_value)
      $this->assign($c_key, $c_value);
  }
}

