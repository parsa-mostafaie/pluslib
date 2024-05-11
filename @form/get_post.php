<?php
//NOTE: THIS FILE IS ALWAYS PUBLIC
//? Check Both Of POST and GET Methods

function get_val($name)
{
  $res = '';
  if (isset ($_POST[$name])) {
    $res = $_POST[$name];
  } elseif (isset ($_GET[$name])) {
    $res = $_GET[$name];
  }
  return htmlspecialchars($res);
}

function setted($name)
{
  return isset ($_POST[$name]) || isset ($_GET[$name]);
}
