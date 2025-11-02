<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

class WappError extends Exception
{
  private $m_data = array();
  private $m_code = 200;

  public function __construct($p_message, $p_code = 200, $p_redirect = "/")
  {
    parent::__construct($p_message);
    $this
      ->setData("error_message", $p_message)
      ->setStatusCode($p_code)
      ->setRedirect($p_redirect);
  }

  public function setStatusCode($p_code)
  {
    $this->m_code = $p_code;
    return $this;
  }

  public function getStatusCode()
  {
    return $this->m_code;
  }

  public function setData($p_key, $p_value)
  {
    $this->m_data[$p_key] = $p_value;
    return $this;
  }

  public function getData()
  {
    return $this->m_data;
  }

  public function setRedirect($p_dest)
  {
    return $this->setData("__redirect", $p_dest);
  }
}


?>