<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/pluslib/init.php';

//! Not recommended to use [always not never]; start
function redirect_secure($path, $back_addr = null, $gen_only = false)
{
  [$u, $p] = useRedirectCode();
  $q = "&u=$u&p=$p";
  $g = redirect($path, true, $back_addr, true) . $q;
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
function redirectBack($default = null)
{
  return (setted('back') ? (function () {
    $back = get_val('back');
    redirect($back);
  }) : (function ($default) {
    if ($default) {
      redirect($default);
    }
  }))($default);
}

function validate_redirect()
{
  $u = get_val('u');
  $p = get_val('p');
  $hash = md5($p);

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
  $hash = md5($p);

  insert_q('redirect_codes', 'u, p', '?, ?', [$u, $hash]);

  return [$u, $p];
}
//! Not recommended to use; end