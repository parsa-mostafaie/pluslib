<?php
defined('ABSPATH') || exit;

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
  return e($_POST[$name] ?? '');
}

function urlParam($name)
{
  return e($_GET[$name] ?? '');
}

function get_val($name)
{
  return urlParam_Sended($name) ? urlParam($name) : get_request_field($name);
}

function get_request_field($field)
{
  return e(decoded_request_body()[$field] ?? '');
}

function request_field_sent($field)
{
  return isset(decoded_request_body()[$field]);
}


// Method Managing
function is_post()
{
  return request_method('post');
}

function request_method($method = 'post')
{
  if (!is_array($method)) {
    $method = [$method];
  }
  $method = array_map('strtoupper', $method);
  return in_array(strtoupper($_SERVER['REQUEST_METHOD']), $method);
}

/* customs */
function request_body()
{
  return file_get_contents('php://input');
}

/**
 * exploded trimed content type
 * @return string
 */
function et_content_type()
{
  $content_type_parts = explode(';', $_SERVER['CONTENT_TYPE'] ?? '');
  $content_type_parts = array_trim($content_type_parts);

  return $content_type_parts[0];
}

function parse_formdata_request()
{
  $a_data = [];
  // read incoming data
  $input = request_body();

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
  return $a_data;
}

function decoded_request_body()
{
  if (et_content_type() == 'application/json') {
    return json_decode(request_body(), true, flags: JSON_BIGINT_AS_STRING);
  }

  if (is_post()) {
    return $_POST;
  } elseif (request_method('GET')) {
    return $_GET;
  }

  if (et_content_type() == 'application/x-www-form-urlencoded') {
    $query = [];
    parse_str(request_body(), $query);
    return $query;
  }

  if (et_content_type() == 'multipart/form-data')
    return parse_formdata_request();
}