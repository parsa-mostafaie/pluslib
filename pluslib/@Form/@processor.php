<?php
//NOTE: THIS FILE IS ALWAYS PUBIL

// globals: 
//      vars: 
//          _SUCCESS, _SUBMITED, _COND
//      functions: (OPTIONAL, CAN BE SET USING ARGs)
//          __PROCESS__CALLBACK__, __PROCESS__SUCCESS__, __PROCESS__FAILED__

defined('ABSPATH') || exit;

$_SUCCESS = false;

$__DEFAULT__PROCESS_FAILED = function (Exception $ex, $isPDO) {
  $err = $ex instanceof PDOException ? $ex->errorInfo[2] : null;
  $msg = $isPDO ? "<i>An PDO Exception was thrown: $err</i>" : $ex->getMessage();
  echo "<div class='container alert alert-danger'><b>Failed!</b> $msg</div>";
};

function process_form($callback = null, $success = null, $failed = null)
{
  global $_SUBMITED;
  global $_COND;
  global $_SUCCESS;
  global $_BECAUSE;
  global $_WHY;
  global $__PROCESS__CALLBACK__;
  global $__PROCESS__SUCCESS__;
  global $__PROCESS__FAILED__;

  $callback = !$callback ? $__PROCESS__CALLBACK__ : $callback;
  $success = !$success ? $__PROCESS__SUCCESS__ : $success;
  $failed = !$failed ? $__PROCESS__FAILED__ : $failed;

  try {
    if ($_SUBMITED) {
      if (!$_COND) {
        throw new Exception('Invalid Input' . ($_BECAUSE ? " '$_BECAUSE'" : '') . '!' .
          ($_WHY ? ": <p>$_WHY</p>" : ''));
      } else {
        $callback();
        $success();
        $_SUCCESS = true;
      }
    }
  } catch (Exception $ex) {
    $failed($ex, $ex instanceof PDOException);
  }
}
