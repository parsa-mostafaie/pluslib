<?php require_once 'init.php';

class ajaxAPI
{
  private $header = [];
  private $body = [];
  public function generateObj()
  {
    $obj = ['header' => $this->header, 'body' => $this->body];
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
  public function err($err)
  {
    $this->body['error'] = $err;
    $this->send();
  }
}