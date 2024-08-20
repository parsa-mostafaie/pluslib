<?php
namespace pluslib\HTTP;

defined('ABSPATH') || exit;

class RestAPI
{
  protected $status = 'ok';
  public function send($data, $json_flags = JSON_BIGINT_AS_STRING)
  {
    pls_content_type();
    echo json_encode(['status' => $this->status, 'data' => $data], $json_flags);
    die;
  }
  public function setStatus($status = 'ok')
  {
    $this->status = $status;
    return $this;
  }
  public function err($err)
  {
    $this->setStatus('nok')->send($err);
  }
}