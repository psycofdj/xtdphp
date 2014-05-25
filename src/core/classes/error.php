<?php

class WappError extends Exception
{
  private $m_data = array();
  private $m_code = 200;

  public function __construct($p_message, $p_code = 200)
  {
    parent::__construct($p_message);

    $this->setData("error_message", $p_message);
    $this->setStatusCode($p_code);
  }

  public function setStatusCode($p_code)
  {
    $this->m_code = $p_code;
  }

  public function getStatusCode()
  {
    return $this->m_code;
  }

  public function setData($p_key, $p_value)
  {
    $this->m_data[$p_key] = $p_value;
  }

  public function getData()
  {
    return $this->m_data;
  }
}

?>