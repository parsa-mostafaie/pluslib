<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/pluslib/init.php';

//! Not recommended to use [always not never]; start
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