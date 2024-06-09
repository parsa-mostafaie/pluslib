<?php
defined('ABSPATH') || exit;

//NOTE: THIS FILE IS ALWAYS PUBLIC
//? Check Both Of POST and GET Methods


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
  return posted($name) || urlParam_Sended($name);
}

// Get Value
function posted_val($name)
{
  return htmlspecialchars($_POST[$name] ?? '');
}

function urlParam($name)
{
  return htmlspecialchars($_GET[$name] ?? '');
}

function get_val($name)
{
  $res = '';
  if (isset($_POST[$name])) {
    $res = $_POST[$name];
  } elseif (isset($_GET[$name])) {
    $res = $_GET[$name];
  }
  return htmlspecialchars($res);
}

// Method Managing
function is_post()
{
  return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
}