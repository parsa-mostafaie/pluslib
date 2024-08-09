<?php

use pluslib\SEO\MetaTags;
use pluslib\Support\Action;

if (!function_exists('meta')) {
  function meta($tag_name = null, $default = null)
  {
    static $meta = null;

    if (!$meta) {
      $meta = new MetaTags();
    }

    return is_null($tag_name) ? $meta : $meta->{$tag_name} ?? $default;
  }
}

if (!function_exists('action_head')) {
  function action_head(): Action
  {
    static $action = null;

    if (!$action)
      $action = new Action;

    return $action;
  }
}

if (!function_exists('add_head')) {
  function add_head(callable $callback)
  {
    return action_head()->add($callback);
  }
}

if (!function_exists('do_head')) {
  function do_head()
  {
    return action_head()->do();
  }
}


if (!function_exists('action_footer')) {
  function action_footer(): Action
  {
    static $action = null;

    if (!$action)
      $action = new Action;

    return $action;
  }
}

if (!function_exists('add_footer')) {
  function add_footer(callable $callback)
  {
    return action_footer()->add($callback);
  }
}

if (!function_exists('do_footer')) {
  function do_footer()
  {
    return action_footer()->do();
  }
}