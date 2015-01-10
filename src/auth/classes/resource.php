<?php

interface IResource
{
  public function getName();
  public function getTag();
  public function generate();
  public function getValue(HTTPHandler $p_handler);
  public function setValue(HTTPHandler $p_handler, $p_value);
}

?>