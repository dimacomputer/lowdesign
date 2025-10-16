<?php
if (!function_exists('ld_opt')) {
  function ld_opt(string $name, $default = null) {
    if (!function_exists('get_field')) return $default;
    $v = get_field($name, 'option');
    return ($v !== null && $v !== '') ? $v : $default;
  }
}