<?php
//NOTE THIS FILE REQUIRES:
// A users TABLE WITH {id, last_activity_time}

//NOTE THIS PART IS PUBLIC ONLY WHEN: users TABLE HAS id and admin
function growUpUser($id)
{
  return update_q('users', 'id=?', 'admin = 1', [$id]);
}

function shrinkDownUser($id)
{
  return update_q('users', 'id=?', 'admin = 0', [$id]);
}
//ENDPART

//NOTE THIS PART IS PUBLIC
function validateUsername($userName)
{
  if (preg_match('/^[A-Za-z][0-9A-Za-z_-]{2,24}$/', $userName)) {
    return true;
  }
  return false;
}
function is_username(array $data, string $field): bool
{
  if (empty($data[$field])) {
    return true;
  }

  return validateUsername($data[$field]);
}
//ENDPART

//NOTE THIS PART IS CUSTOMIZED
function add_user($fname, $lname, $uname, $pword)
{
  $hashed = hash_pass($pword);
  $insert = insert_q('users', 'firstname, lastname, username, password', '?, ?, ?, ?', [$fname, $lname, $uname, $hashed]);
  return $insert;
}
//ENDPART

// THIS PART IS PUBLIC
function get_users(...$params)
{
  return select_q("users", ...$params);
}

function update_users(...$args)
{
  return update_q("users", ...$args);
}

function delete_users($id)
{
  $id = intval($id);
  // rem_prof_img($id);
  if ($id) {
    return delete_q("users", "id = ?", [$id]);
  }
  return false;
}
// ENDPART

// REQUIRES: users table with: last_activity_time, lu_browser
function user_actived($id)
{
  try {
    update_q('users', 'id = ?', 'lu_browser = ?', [$id, getBrowser()['name']]);
    update_q('users', 'id = ?', 'last_activity_time = NOW()', [$id]);
  } catch (Exception) {
  }
  ;
  return true;
}
function last_activity_time($id)
{
  return get_users(cols: 'last_activity_time', condition: 'id = ?', p: [$id])->fetchColumn();
}
function last_activity_time__ago__($id)
{
  return get_users(cols: '(TIMESTAMPDIFF(MINUTE, last_activity_time, NOW()))', condition: 'id = ?', p: [$id])->fetchColumn();
}
function isOnline($id)
{
  return last_activity_time__ago__($id) <= 2;
}
// ENDPART