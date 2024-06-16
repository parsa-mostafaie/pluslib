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
  return posted($name) ? posted_val($name) : urlParam($name);
}

// Method Managing
function is_post()
{
  return request_method('post');
}

function request_method($method = 'post')
{
  return strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($method);
}

function request_body($json = false)
{
  return !$json ? file_get_contents('php://input') : json_decode(request_body(false));
}