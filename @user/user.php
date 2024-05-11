<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/init.php';

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
function is_username($userName)
{
  if (preg_match('/^[A-Za-z][0-9A-Za-z_-]{2,24}$/', $userName)) {
    return true;
  }
  return false;
}
//ENDPART

//NOTE THIS PART IS CUSTOMIZED
function set_prof_image($tid, $name)
{
  $file = uploadFile_secure($name, prefix: 'user_profile_');
  if ($file) {
    rem_prof_img($tid);
    return update_users(condition: "id='$tid'", set: "profile = ?", params: [$file]);
  }
}

function get_prof_url($tid)
{
  return urlOfUpload(get_users(cols: 'profile', condition: "id = '$tid'")->fetchColumn());
}

function rem_prof_img($tid)
{
  unlinkUpload(get_prof_url($tid));
  return update_users(condition: "id = ?", set: "profile = NULL", params: [$tid]);
}

function get_prof_img($uname, $cattrs = '')
{
  $purl =
    get_prof_url(get_users(cols: 'id', condition: "username = '$uname'")->fetchColumn());
  return imageComponent($purl, 'class="avatar-xxl rounded-circle" ' . $cattrs);
}

function hasprofimg($tid)
{
  $_purl = get_prof_url($tid);
  $purl = $_SERVER['DOCUMENT_ROOT'] . regular_url($_purl);
  return file_exists($purl) && get_prof_url($tid);
}
//ENDPART

//NOTE THIS PART IS CUSTOMIZED
function add_user($fname, $lname, $uname, $pword)
{
  $date = date('Y-m-d H:i:s');
  $hashed = hash_pass($pword);
  $insert = insert_q('users', 'firstname, lastname, date, username, password', '?, ?, ?, ?, ?', [$fname, $lname, $date, $uname, $hashed]);
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
  rem_prof_img($id);
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