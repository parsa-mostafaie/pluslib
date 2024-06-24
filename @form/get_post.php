<?php
defined('ABSPATH') || exit;

//NOTE: THIS FILE IS ALWAYS PUBLIC
//? Check Both Of POST and GET Methods


// Check Sended
function posted($name)
{
  return isset($_POST[$name]);
}

function urlParam_Sended($name)
{
  return isset($_GET[$name]);
}

function setted($name)
{
  return posted($name) || urlParam_Sended($name) || request_field_sent($name);
}

// Get Value
function posted_val($name)
{
  return htmlspecialchars($_POST[$name] ?? '');
}

function urlParam($name)
{
  return htmlspecialchars($_GET[$name] ?? '');
}

function get_val($name)
{
  return urlParam_Sended($name) ? urlParam($name) : (is_post() ? posted_val($name) : get_request_field($name));
}

// Method Managing
function is_post()
{
  return request_method('post');
}

function request_method($method = 'post')
{
  return strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($method);
}

function request_body()
{
  return file_get_contents('php://input');
}

function get_request_field($field)
{
  return decoded_request_body()[$field] ?? '';
}

function request_field_sent($field)
{
  return isset(decoded_request_body()[$field]);
}

function parse_raw_http_request(array &$a_data)
{
  // read incoming data
  $input = file_get_contents('php://input');

  // grab multipart boundary from content type header
  preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
  $boundary = $matches[1];

  // split content by boundary and get rid of last -- element
  $a_blocks = preg_split("/-+$boundary/", $input);
  array_pop($a_blocks);

  // loop data blocks
  foreach ($a_blocks as $id => $block) {
    if (empty($block))
      continue;

    // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

    // parse uploaded files
    if (strpos($block, 'application/octet-stream') !== FALSE) {
      // match "name", then everything after "stream" (optional) except for prepending newlines 
      preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
    }
    // parse all other fields
    else {
      // match "name" and optional value in between newline sequences
      preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
    }
    $a_data[$matches[1]] = $matches[2];
  }
}

function decoded_request_body()
{
  if (is_post()) {
    return $_POST;
  } elseif (request_method('GET')) {
    return $_GET;
  }
  $v = [];
  parse_raw_http_request($v);
  return $v;
}