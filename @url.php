<?php include_once 'init.php';
function c_url($url, $regularIt = true)
{
  return $regularIt ? regular_url(c_url($url, false)) : web_url(HOME_URL() . $url);
}
function web_url($url)
{
  return str_replace('\\', '/', $url);
}

function regular_url($_purl)
{
  return str_replace('/', DIRECTORY_SEPARATOR, web_url($_purl));
}

function i_protocol()
{
  $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://";
  return $protocol;
}

function http_baseurl()
{
  $baseurl = i_protocol() . $_SERVER["HTTP_HOST"];
  return $baseurl;
}

function etc_url($url)
{
  return $_SERVER['DOCUMENT_ROOT'] . $url;
}

function www_url($url)
{
  return http_baseurl() . web_url($url);
}

function form_processor_url($path, $dir = '/libs/custom/@form', $base = '')
{
  $base = $base ? $base : $_SERVER['DOCUMENT_ROOT'];
  $full = $base . $dir . $path;
  return $full;
}

// function slugify($text, string $divider = '-')
// {
//   // replace non letter or digits by divider
//   $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

//   // transliterate
//   $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

//   // remove unwanted characters
//   $text = preg_replace('~[^-\w]+~', '', $text);

//   // trim
//   $text = trim($text, $divider);

//   // remove duplicate divider
//   $text = preg_replace('~-+~', $divider, $text);

//   // lowercase
//   $text = strtolower($text);

//   if (empty($text)) {
//     return 'n-a';
//   }

//   return $text;
// }

function slugify($string, $separator = '-')
{
  $zwng = html_entity_decode('&zwnj;');


  $string = trim($string);
  $string = str_replace($zwng, ' ', $string);
  $string = mb_strtolower($string, 'UTF-8');
  $string = preg_replace("/[^a-z0-9_\-\sءاآؤئبپتثجچحخدذرزژسشصضطظعغفقكکگلمنوهی]/u", '', $string);
  $string = preg_replace("/[\s\-_]+/", ' ', $string);
  $string = preg_replace("/[\s_]/", $separator, $string);

  return $string;

}