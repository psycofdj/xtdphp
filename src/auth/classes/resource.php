<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 2014
 ** Written by: Pascal BERGER   <pb@wapp.pro>, 2014
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

interface IResource
{
  public function getName();
  public function getTag();
  public function generate();
  public function getValue(HTTPHandler $p_handler);
  public function setValue(HTTPHandler $p_handler, $p_value);
}

?>