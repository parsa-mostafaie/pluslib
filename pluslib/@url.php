<?php

function basepath($path='')
{
  return app()->basepath($path);
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

if (!function_exists('build_url')) {
  /**
   * Inverse of parse_url
   * @param array $url_components
   * @return string Url
   */
  function build_url($url_components = [])
  {
    // Initialize an empty URL
    $url = '';

    // Add the scheme (http, https, etc.)
    if (!empty($url_components['scheme'])) {
      $url .= $url_components['scheme'] . '://';
    }

    // Add the user info (username:password)
    if (!empty($url_components['user'])) {
      $url .= $url_components['user'];
      if (!empty($url_components['pass'])) {
        $url .= ':' . $url_components['pass'];
      }
      $url .= '@';
    }

    // Add the host
    if (!empty($url_components['host'])) {
      $url .= $url_components['host'];
    }

    // Add the port if it is set
    if (!empty($url_components['port'])) {
      $url .= ':' . $url_components['port'];
    }

    // Add the path
    if (!empty($url_components['path'])) {
      $url .= web_url($url_components['path']);
    }

    // Add the query string if it is set
    if (!empty($url_components['query'])) {
      $url .= '?' . $url_components['query'];
    }

    // Add the fragment if it is set
    if (!empty($url_components['fragment'])) {
      $url .= '#' . $url_components['fragment'];
    }

    return $url;
  }
}

if (!function_exists('url')) {
  function url($url, $query = [])
  {
    /**
     * relative url: file.ext, ../file.ext, ...
     * absolute url: /file.ext, ...
     */
    $auto_host = str_starts_with(web_url($url), '/'); // handle relative url

    $parsed = parse_url($url);

    $q = $parsed['query'] ?? '';
    parse_str($q, $base_query);

    foreach ($query as $i => $q) {
      if (is_int($i)) {
        unset($query[$i]);
        $query[$q] = '';
      }
    }

    $query = http_build_query(array_merge($base_query, $query));

    $scheme = $parsed['scheme'] ?? ($auto_host ? substr(i_protocol(), 0, -3) : '');

    $host = $parsed['host'] ?? ($auto_host ? $_SERVER['HTTP_HOST'] : '');

    return build_url(array_merge($parsed, [
      'query' => $query,
      'scheme' => $scheme,
      'host' => $host
    ]));
  }
}

// PATH
if (!function_exists('storage_path')) {
  function storage_path($path = '')
  {
    return app()->storage_path($path);
  }
}

if (!function_exists('public_path')) {
  function public_path($path = '')
  {
    return app()->public_path($path);
  }
}

if (!function_exists('config_path')) {
  function config_path($path = '')
  {
    return app()->config_path($path);
  }
}

if (!function_exists('resources_path')) {
  function resources_path($path = '')
  {
    return app()->resources_path($path);
  }
}