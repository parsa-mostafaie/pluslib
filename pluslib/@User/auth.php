<?php
//NOTE: THIS PART/FILE IS PUBLIC ONLY WHEN:
//------ DB HAS:
//------- A users Table With {username, password, mail, lu_browser}

//? return: login successed?
function loginWith($username, $pass)
{
  session_new_id();
  $id = (get_users(cols: 'id', condition: 'username = ? OR mail = ?', p: [$username, $username]));
  if ($id) {
    $id = $id->fetchColumn();
    if (canLoginWith($id, $pass)) {
      set_session('uid', $id);
      set_session('pass', $pass);
      return true;
    }
  }
  return false;
}

function canlogin()
{
  $id = get_session('uid');
  $pass = get_session('pass');

  return canLoginWith($id, $pass) ? user_actived($id) : false;
}

function canLoginWith($id, $pass)
{
  $user = get_users(cols: 'password', condition: 'id = ?', p: [$id]);

  if ($user) {
    $user_pass = $user->fetchColumn();

    if (pass_verify($pass, $user_pass)) {
      return true;
    }
  }

  return false;
}


function getCurrentUserInfo()
{
  if (canLogin()) {
    $id = get_session('uid');

    $user = get_users(cols: '*', condition: 'id = ?', p: [$id])->fetchAll(PDO::FETCH_ASSOC)[0];

    return $user;
  }
  return null;
}

function getCurrentUserInfo_prop($name)
{
  return getCurrentUserInfo()[$name] ?? null;
}

function signout()
{
  session__unset(false, 'pass', 'uid');
  session_new_id();
}
// ENDPART