<?php
defined('ABSPATH') || exit;

use pluslib\Config;

//NOTE: THIS FILE IS ALWAYS PUBLIC
function uploadFile_secure(
  $name,
  $max_size = 3145728,
  $allowedTypes = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg'
  ],
  $prefix = ''
) {

  if ($_FILES[$name]["error"] != 0) {
    //stands for any kind of errors happen during the uploading
    return null;
  }

  $filepath = $_FILES[$name]['tmp_name'];
  $fileSize = filesize($filepath);
  $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
  $filetype = finfo_file($fileinfo, $filepath);

  if ($fileSize === 0) {
    throw new Exception("The file is empty.", 400);
  }

  if ($fileSize > $max_size) {
    throw new Exception("The file is too large", 400);
  }

  if (!in_array($filetype, array_keys($allowedTypes))) {
    throw new Exception("File not allowed.", 400);
  }

  $filename = uniqid('UPLOAD_' . $prefix, true);
  // $filename = basename($filepath);

  $extension = $allowedTypes[$filetype];
  $targetDirectory = etc_urlOfUpload('/' . Config::$uploadDirectory); // loc

  $newFilepath = $targetDirectory . $filename . "." . $extension;

  if (!copy($filepath, $newFilepath)) { // Copy the file, returns false if failed
    throw new Exception("Can't move file.");
  }
  unlink($filepath); // Delete the temp file

  return Config::$uploadDirectory . $filename . '.' . $extension;
}
function unlinkUpload($fname)
{
  if (!urlOfUpload($fname))
    return;
  unlink(etc_urlOfUpload($fname));
}

function urlOfUpload($fname, $no_www = false)
{
  return $fname ? ($no_www ? c_url('/') . $fname : www_url(urlOfUpload($fname, true))) : null;
}

function etc_urlOfUpload($fname)
{
  return $_SERVER['DOCUMENT_ROOT'] . urlOfUpload($fname, true);
}