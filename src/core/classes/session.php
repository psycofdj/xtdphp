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
require_once(__WAPPCORE_DIR__  . "/core/libs/RedBeanPHP/loader.php");

class WappSqlSessionHandler
{
  private static $ms_instance = null;

  static function session_start()
  {
    global $g_conf;
    if ("mysql" == $g_conf["session"]["handler"])
    {
      log::info("core.session", "initializing mysql type session");
      if (self::$ms_instance == null)
        self::$ms_instance = new WappSqlSessionHandler();
      session_set_save_handler(array(self::$ms_instance, 'open'),
                               array(self::$ms_instance, 'close'),
                               array(self::$ms_instance, 'read'),
                               array(self::$ms_instance, 'write'),
                               array(self::$ms_instance, 'destroy'),
                               array(self::$ms_instance, 'gc'));
      register_shutdown_function('session_write_close');
    }

    session_start();
  }

  private function __getSession($p_sid)
  {
    return R::findOne("session", "sid = :sid", array("sid" => $p_sid));
  }

  private function __createSession($p_sid)
  {
    $l_session = R::dispense("session");
    $l_session->sid = $p_sid;
    return $l_session;
  }

  public function open($p_savepath , $p_name)
  {
    log::info("core.session", "initializing : %s", $p_name);
    return true;
  }

  public function close()
  {
    log::info("core.session", "closing session");
    return true;
  }

  public function destroy($p_sid)
  {
    log::info("core.session", "destroying session '%s'", $p_sid);
    if (false != ($l_session = $this->__getSession($p_sid)))
      R::trash($l_session);
    return true;
  }

  public function gc($p_maxtime)
  {
    log::info("core.session", "garbage collecting > '%s'", $p_maxtime);
    R::exec("DELETE FROM session WHERE timestamp < :time", array("time" => time() - intval($p_maxtime)));
    return true;
  }

  public function read($p_sid)
  {
    log::info("core.session", "reading : %s", $p_sid);
    if (false == ($l_session = $this->__getSession($p_sid)))
    {
      log::info("core.session", "session not found : %s", $p_sid);
      return "";
    }
    log::debug("core.session", "session found, data : %s", $p_sid);
    return $l_session->data;
  }

  public function write($p_sid , $p_data)
  {
    log::info("core.session", "writing session : %s", $p_sid);
    log::debug("core.session", "writing session data : %s", $p_data);

    if (false == ($l_session = $this->__getSession($p_sid)))
    {
      log::info("core.session", "creating session %s", $p_sid);
      $l_session = $this->__createSession($p_sid);
    }
    $l_session->data      = $p_data;
    $l_session->timestamp = time();
    R::store($l_session);
    return true;
  }
}

?>