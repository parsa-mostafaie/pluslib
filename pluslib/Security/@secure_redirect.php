<?php
function redirectBack($default = null, $gen_only = false)
{
  $url = '';
  if (!empty(get_val('back'))) {
    $back = htmlspecialchars_decode(get_val('back'));

    $url = $back;
  } else if ($default) {
    $url = $default;
  }
  if ($gen_only)
    return $url;
  redirect($url);
}