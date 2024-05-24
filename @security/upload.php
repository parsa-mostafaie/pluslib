<?php
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
        return;
    }

    $filepath = $_FILES[$name]['tmp_name'];
    $fileSize = filesize($filepath);
    $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
    $filetype = finfo_file($fileinfo, $filepath);

    if ($fileSize === 0) {
        throw new Exception("The file is empty.");
    }

    if ($fileSize > $max_size) {
        throw new Exception("The file is too large");
    }

    if (!in_array($filetype, array_keys($allowedTypes))) {
        throw new Exception("File not allowed.");
    }

    $filename = uniqid('UPLOAD_' . $prefix, true);
    // $filename = basename($filepath);

    $extension = $allowedTypes[$filetype];
    $targetDirectory = $_SERVER['DOCUMENT_ROOT'] . web_url(c_url('/uploads'));

    $newFilepath = $targetDirectory . "/" . $filename . "." . $extension;

    if (!copy($filepath, $newFilepath)) { // Copy the file, returns false if failed
        throw new Exception("Can't move file.");
    }
    unlink($filepath); // Delete the temp file

    return 'uploads/' . $filename . '.' . $extension;
}
function unlinkUpload($fname)
{
    if (!urlOfUpload($fname))
        return;
    unlink($_SERVER['DOCUMENT_ROOT'] . urlOfUpload($fname));
}

function urlOfUpload($fname)
{
    return $fname ? c_url('/' . $fname) : null;
}