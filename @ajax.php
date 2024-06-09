<?php
defined('ABSPATH') || exit;

class ajaxAPI
{
  private $header = [];
  private $body = [];
  private $customs = [];
  public function generateObj()
  {
    $obj = ['header' => $this->header, 'body' => $this->body, ...$this->customs];
    return $obj;
  }
  public function generateStr()
  {
    return json_encode($this->generateObj());
  }
  public function send($live = false)
  {
    echo $this->generateStr();
    if (!$live)
      die;
  }
  public function custom($n, $v)
  {
    $customs[$n] = $v;
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