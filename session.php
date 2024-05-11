<?php
// NOTE THAT's ALWAYS PUBLIC
session_start();

function get_session($name, $public = false)
{
    return $_SESSION[session_local_name($name, $public)] ?? '';
}

function set_session($name, $value, $public = false)
{
    $_SESSION[session_local_name($name, $public)] = $value;
}

function session__unset($public = false, ...$val)
{
    foreach ($val as $n) {
        unset($_SESSION[session_local_name($n, $public)]);
    }
}

function session_local_name($pureName, $public = false)
{
    return !$public ? c_url('__$' . $pureName) : $pureName;
}