<?php
defined('ABSPATH') || exit;

// NOTE THAT's ALWAYS PUBLIC
session_start();

if ((!isset($_SESSION['PATHS'])) || !$_SESSION['PATHS']) {
    $_SESSION['PATHS'] = array();
}

function get_session($name, $public = false)
{
    return session_arr($public)[$name] ?? '';
}

function set_session($name, $value, $public = false)
{
    session_arr($public)[$name] = $value;
}

function session__unset($public = false, ...$val)
{
    foreach ($val as $n) {
        unset(session_arr($public)[$n]);
    }
}

function &session_arr($public = false): array
{
    $PATHS =& $_SESSION['PATHS'];
    if (!isset($PATHS[HOME_URL()]) || !$PATHS[HOME_URL()]) {
        $PATHS[HOME_URL()] = array();
    }
    if (!$public) {
        $ref =& $PATHS[HOME_URL()];
    } else {
        $ref =& $_SESSION;
    }
    return $ref;
}