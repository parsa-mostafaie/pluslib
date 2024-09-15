<?php

defined('ABSPATH') || exit;

function usePassword()
{
  $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
  $pass = array(); //remember to declare $pass as an array
  $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
  for ($i = 0; $i < 8; $i++) {
    $n = rand(0, $alphaLength);
    $pass[] = $alphabet[$n];
  }
  return implode($pass); //turn the array into a string
}

function hash_pass(string $str)
{
  return password_hash($str, PASSWORD_DEFAULT);
}

function rand_hex($len = 4)
{
  return bin2hex(random_bytes($len));
}

function pass_verify($input, $hash)
{
  return password_verify($input, $hash);
}

enum secure_form_enum
{
  case gen;
  case get;
  case expire;
}

function secure_form(secure_form_enum $st = secure_form_enum::gen)
{
  if ($st != secure_form_enum::gen && request_method('get')) {
    return false;
  }
  if ($st == secure_form_enum::gen) {
    $n = uniqid('sec_form_sess_');
    $v = rand_hex();
    set_session($n, md5($v));
    return ['n' => $n, 'v' => $v];
  } else if ($st == secure_form_enum::get) {
    $n = get_val('sec_form_sess_n');
    $v = get_val('sec_form_sess_v');

    if (md5($v) == get_session($n)) {
      return true;
    }
    return false;
  } else {
    $n = get_val('sec_form_sess_n');
    $v = get_val('sec_form_sess_v');

    if (md5($v) == get_session($n)) {
      session__unset(false, $n, $v);
      return true;
    }
    return false;
  }
}

function anti_xss($html)
{
  if (empty($html)) {
    return $html;
  }
  $dom = new DOMDocument();

  $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

  $script = $dom->getElementsByTagName('script');

  $remove = [];
  foreach ($script as $item) {
    $remove[] = $item;
  }

  foreach ($remove as $item) {
    $item->parentNode->removeChild($item);
  }

  return $dom->saveHTML();
}

function secretFile($addr = null)
{
  static $address = null;
  $default = etc_url(web_url(c_url('/secret.json')));
  if (!is_null($addr)) {
    $address = $addr;
  }
  return importJSON($address ?? $default, true);
}

if (!function_exists('e')) {
  /**
   * Alias for htmlspecialchars
   * @param string $html
   * @param bool $doubleEncode
   * 
   * @return string
   */
  function e(?string $html = null, $doubleEncode = true)
  {
    return htmlspecialchars($html ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
  }
}