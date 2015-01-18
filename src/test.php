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

require_once(dirname(__FILE__) . "/local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/mapper.php");
require_once(__APP_DIR__       . "/classes/generator.php");

class TestPage extends HTTPHandler
{
  public function __construct()
  {
    parent::__construct();
  }


  public function h_default()
  {
    $this->setContent("[core]test.tpl");
    return true;
  }

  public function s_test($p_params)
  {
    $l_mapper =
      new RawMapper($p_params,
                    array("color", "make", "name", "id"),
                    <<<'EOF'
                       SELECT
                          vehicle.color as ?,
                          vehicle.make  as ?,
                          client.name   as ?,
                          client.id     as ?
                       FROM vehicle
                       LEFT JOIN client on vehicle.client_id = client.id
EOF
      );
    return $l_mapper->process();
  }
}

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
{
  $l_page = new TestPage();
  $l_page->process();
}

?>