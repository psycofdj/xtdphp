<?php

require_once(dirname(__FILE__) . "/../../local.php");
require_once(__WAPPCORE_DIR__ . "/core/classes/handler.php");

class BulkSMS
{
  public function __construct()
  {
    global $g_conf;

    $this->m_url        = $g_conf["sms"]["url"];
    $this->m_username   = $g_conf["sms"]["username"];
    $this->m_password   = $g_conf["sms"]["password"];
    $this->m_defaultDst = $g_conf["sms"]["destination"];
  }

  public function send($p_message, $p_dst = null)
  {
    $l_dst = $p_dst;
    if (null == $l_dst)
      $l_dst = $this->m_defaultDst;

    $l_data =
      array("username"         => $this->m_username,
            "password"         => $this->m_password,
            "message"          => $p_message,
            "msisdn"           => join(",     ", $l_dst),
            "routing_group"    => 2);

    $l_query = http_build_query($l_data);
    $l_curl  = curl_init();
    curl_setopt($l_curl, CURLOPT_URL,            $this->m_url);
    curl_setopt($l_curl, CURLOPT_POST,           count($l_data));
    curl_setopt($l_curl, CURLOPT_POSTFIELDS,     $l_query);
    curl_setopt($l_curl, CURLOPT_RETURNTRANSFER, 1);
    if (false == ($l_output = curl_exec($l_curl)))
    {
      log::crit("sms", "send failed, coulnd't send request to '%s?%s'", $this->m_url, $l_query);
      curl_close($l_curl);
      return false;
    }

    $l_parts = explode("|", $l_output);
    if (2 > count($l_parts))
    {
      log::crit("sms", "send failed, invalid server response : '%s'", $l_output);
      return false;
    }

    if ((int)$l_parts[0] > 1)
    {
      log::crit("sms", "send failed, server anwsered code %d : %s", $l_parts[0], $l_parts[1]);
      return false;
    }

    return true;
  }
}


class Tester extends Handler
{
  public function run()
  {
    $l_sms = new BulkSMS();
    return $l_sms->send("hello", array("33608852123"));
  }
}


if (php_sapi_name() == 'cli')
{
  $l_test = new Tester();
  $l_test->process();
}

?>
