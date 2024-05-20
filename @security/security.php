<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/init.php';

function redirect_secure($path, $back_addr = null, $back = false, $gen_only = false)
{
  [$u, $p] = useRedirectCode();
  $back = urlencode((empty($back_addr) ? null : $back_addr) ?? $_SERVER['REQUEST_URI']);
  $q = "&u=$u&p=$p&back=$back";
  $g = str_contains($path, '?') ? $path . $q : $path . "?$q";
  if ($gen_only)
    return $g;
  redirect($g);
}

/*
  if(validate_redirect()){
    // Do Something
  }
  redirectBack();
*/
function redirectBack()
{
  return (setted('back') ? (function () {
    $back = get_val('back');
    redirect($back);
  }) : (function () {}))();
}

function validate_redirect()
{
  $u = get_val('u');
  $p = get_val('p');
  $hash = hash_pass($p);

  if (setted('u') && setted('p')) {
    $r = select_q('redirect_codes', '1', condition: 'u = ? AND p = ?', p: [$u, $hash])->fetchColumn();
    if ($r) {
      delete_q('redirect_codes', 'u = ? AND p = ?', [$p, $hash]);
    }
    return $r;
  }
  return false;
}

function useRedirectCode()
{
  $u = uniqid('useRedirectCode_');
  $p = usePassword();
  $hash = hash_pass($p);

  insert_q('redirect_codes', 'u, p', '?, ?', [$u, $hash]);

  return [$u, $p];
}

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

function pass_verify($input, $hash)
{
  return hash_pass($input) == $hash;
}
enum secure_form_enum
{
  case gen;
  case get;
}
function secure_form(secure_form_enum $st = secure_form_enum::gen)
{
  if ($st == secure_form_enum::gen) {
    $n = uniqid('sec_form_sess_');
    $v = bin2hex(random_bytes(4));
    set_session($n, hash_pass($v));
    return ['n' => $n, 'v' => $v];
  } else {
    $n = get_val('sec_form_sess_n');
    $v = get_val('sec_form_sess_v');

    if (pass_verify($v, get_session($n))) {
      session__unset(false, $n, $v);
      return true;
    }
    return false;
  }
}