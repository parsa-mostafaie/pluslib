<?php
defined('ABSPATH') || exit;

use pluslib\Config;

function uploadTemp_secure(
  $filepath,
  $max_size = 3145728,
  $allowedTypes = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg'
  ],
  $prefix = ''
) {
  $fileSize = filesize($filepath);
  $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
  $filetype = finfo_file($fileinfo, $filepath);

  if ($fileSize === 0) {
    throw new Exception("The file is empty.", 400);
  }

  if ($fileSize > $max_size) {
    throw new Exception("The file is too large.", 400);
  }

  if (!in_array($filetype, array_keys($allowedTypes))) {
    throw new Exception("File not allowed.", 400);
  }

  $filename = uniqid($prefix, true) . '_' . basename($filepath);

  $extension = $allowedTypes[$filetype];
  $targetDirectory = etc_urlOfUpload('/' . Config::$uploadDirectory); // loc

  $newFilepath = "$targetDirectory$filename.$extension";

  if (!move_uploaded_file($filepath, $newFilepath)) { // Copy the file, returns false if failed
    throw new Exception("Can't move file.", 500);
  }

  return Config::$uploadDirectory . $filename . '.' . $extension;
}

function uploadFile_secure(
  $name,
  $max_size = 3145728,
  $allowedTypes = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg'
  ],
  $prefix = ''
) {
  if (!isset($_FILES))
    return;

  if (empty($_FILES[$name]))
    return null;

  if ($_FILES[$name]["error"] != 0) {
    //stands for any kind of errors happen during the uploading
    return null;
  }

  if (is_array($_FILES[$name]['name'])) {
    $arr_result = [];
    foreach ($_FILES[$name]['tmp_name'] as $filepath) {
      $arr_result[] = uploadTemp_secure($filepath);
    }
    return $arr_result;
  }

  $filepath = $_FILES[$name]['tmp_name'];

  return uploadTemp_secure($filepath, $max_size, $allowedTypes, $prefix);
}
function unlinkUpload($fname)
{
  if (!urlOfUpload($fname))
    return;
  unlink(etc_urlOfUpload($fname));
}

function urlOfUpload($fname, $no_www = false)
{
  if (parse_url($fname, component: PHP_URL_HOST)) {
    return $fname;
  }
  return $fname ? ($no_www ? c_url("/$fname") : url(c_url("/$fname", false))) : null;
}

function etc_urlOfUpload($fname)
{
  return etc_url(urlOfUpload($fname, true));
}