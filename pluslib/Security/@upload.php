<?php

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

  $extension = $allowedTypes[$filetype];
  $filename = join_paths($prefix, uniqid(more_entropy: true)) . ".$extension";

  $newFilepath = upload_path($filename);

  if (!move_uploaded_file($filepath, $newFilepath)) { // Copy the file, returns false if failed
    throw new Exception("Can't move file.", 500);
  }

  return $filename;
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
  if (!upload_path($fname))
    return;
  unlink(upload_path($fname));
}

function upload_path($fname = '')
{
  if (parse_url($fname, component: PHP_URL_HOST)) {
    return null;
  }

  return $fname ? regular_url(join_paths(config('storage.driver.path'), $fname)) : null;
}

function upload_url($fname = '')
{
  if (parse_url($fname, component: PHP_URL_HOST)) {
    return $fname;
  }

  return config('storage.driver.url') . join_paths('', $fname);
}