<?php
require_once __DIR__ . '/../init.php';

//NOTE: THIS LIB IS PUBLIC ONLY IF getUserInfo BE PUBLIC

//? Check Logged In User is admin
function isAdmin()
{
    return (getCurrentUserInfo_prop('admin')) == 1;
}

//? 404 ERROR If Logged in user isn't admin
function authAdmin()
{
    if (!isAdmin()) {
        _404_();
    }
}