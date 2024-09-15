<?php

// NOTE THAT's ALWAYS PUBLIC
session_start();

function get_session($name)
{
  return $_SESSION[$name] ?? '';
}

function set_session($name, $value)
{
  $_SESSION[$name] = $value;
}

function session__unset(...$val)
{
  foreach ($val as $n) {
    unset($_SESSION[$n]);
  }
}

function session_new_id()
{
  session_regenerate_id(true);
}

function destroy_session()
{
  session_unset();
  session_destroy();
}

function &session($name){
  return $_SESSION[$name];
}