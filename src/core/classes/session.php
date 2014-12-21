<?php

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


  public function close()
  {
    return true;
  }

  public function destroy($p_sid)
  {
    if (false != ($l_session = $this->get($p_sid)))
      R::trash($l_session);
    return true;
  }

  public function gc($p_maxtime)
  {
    $l_sessions = R::find("sessions", "timestamp < :time", array("time" => time() - intval($p_maxtime)));
    R::trashAll($l_sessions);
    return true;
  }

  public function open($p_savepath , $p_name)
  {
    $l_limit    = time() - (3600 * 24);
    $l_sessions = R::find("session", "timestamp < :limit", array("limit" => $l_limit));
    R::trashAll($l_sessions);
    return true;
  }

  public function read($p_sid)
  {
    if (false == ($l_session = $this->__getSession($p_sid)))
      return "";
    return $l_session->data;
  }

  public function write($p_sid , $p_data)
  {
    if (false == ($l_session = $this->__getSession($p_sid)))
      $l_session = $this->__createSession($p_sid);
    $l_session->data      = $p_data;
    $l_session->timestamp = time();
    R::store($l_session);
    return true;
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
}

?>