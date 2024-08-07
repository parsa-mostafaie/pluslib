<?php
namespace pluslib;

defined('ABSPATH') || exit;

class ajaxAPI
{
  private $header = [];
  private $body = ['logs' => ''];
  private $customs = [];
  public function __construct()
  {
    ob_start();
  }
  public function generateObj()
  {
    $obj = array_merge(
      ['header' => $this->header, 'body' => $this->body, 'ob' => ob_get_clean()],
      $this->customs
    );
    return $obj;
  }
  public function generateStr()
  {
    return json_encode($this->generateObj());
  }
  public function send()
  {
    pls_content_type();
    echo $this->generateStr();
    die;
  }
  public function custom($n, $v)
  {
    $this->customs[$n] = $v;
  }
  public function err($err)
  {
    $this->body['error'] = $err;
    $this->send();
  }
  public function redirect($url)
  {
    $this->header['redirect'] = $url;
    $this->send();
  }
}