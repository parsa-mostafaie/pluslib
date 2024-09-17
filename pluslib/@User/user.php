<?php
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
function get_users(...$params)
{
  return select_q("users", ...$params);
}

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