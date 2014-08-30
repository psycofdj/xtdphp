<?php

interface IResource
{
  public function getName();
  public function generate();
  public function getValue(Handler $p_handler);
  public function setValue(Handler $p_handler, $p_value);
}

?>