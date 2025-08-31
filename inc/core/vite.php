<?php
if (!defined('ABSPATH')) exit;

/**
 * Vite helpers (build time, no dev server)
 * - Ищем manifest.json в build/ и build/.vite/
 * - Кешируем манифест, чтобы не читать файл каждый раз
 * - Универсально достаём asset URI по entry-ключу (ключ = исходный путь)
 */

if (!function_exists('ld_vite_manifest_path')) {
  function ld_vite_manifest_path(): ?string {
    $base = LD_THEME_DIR . '/build';
    foreach ([$base . '/manifest.json', $base . '/.vite/manifest.json'] as $p) {
      if (is_file($p)) return $p;
    }
    return null;
  }
}

if (!function_exists('ld_vite_manifest')) {
  /** @return array<string,mixed>|null */
  function ld_vite_manifest(): ?array {
    static $cache = null;
    if ($cache !== null) return $cache;

    $path = ld_vite_manifest_path();
    if (!$path) return $cache = null;

    $json = file_get_contents($path);
    if ($json === false) return $cache = null;

    $data = json_decode($json, true);
    return $cache = (is_array($data) ? $data : null);
  }
}

if (!function_exists('ld_vite_asset_uri')) {
  /**
   * Получить публичный URI ассета по entry-ключу из манифеста.
   * Пример ключей: 'assets/src/scss/main.scss', 'assets/src/scss/editor.scss', 'assets/src/scss/admin-dark.scss', 'assets/src/js/main.js'
   */
  function ld_vite_asset_uri(string $entry): ?string {
    $manifest = ld_vite_manifest();
    if (!$manifest) return null;

    // 1) точное совпадение
    if (isset($manifest[$entry]['file'])) {
      $rel = ltrim($manifest[$entry]['file'], '/');
      return LD_THEME_URI . '/build/' . $rel;
    }

    // 2) иногда ключи могут начинаться/не начинаться со слеша
    $alts = [];
    if ($entry && $entry[0] === '/') { $alts[] = ltrim($entry, '/'); }
    else { $alts[] = '/' . $entry; }

    foreach ($alts as $alt) {
      if (isset($manifest[$alt]['file'])) {
        $rel = ltrim($manifest[$alt]['file'], '/');
        return LD_THEME_URI . '/build/' . $rel;
      }
    }

    // 3) не нашли
    return null;
  }
}

if (!function_exists('ld_vite_has_entry')) {
  function ld_vite_has_entry(string $entry): bool {
    $manifest = ld_vite_manifest();
    if (!$manifest) return false;
    return isset($manifest[$entry]) || isset($manifest['/'.$entry]);
  }
}

/** Админ-уведомление: нет сборки или нет ключевых entry */
add_action('admin_notices', function () {
  if (!current_user_can('manage_options')) return;

  $path = ld_vite_manifest_path();
  if (!$path) {
    echo '<div class="notice notice-warning"><p><strong>Lowdesign:</strong> не найден <code>build/manifest.json</code> (или <code>build/.vite/manifest.json</code>). Собери ассеты и закоммить в тему.</p></div>';
    return;
  }

  // Подсказка, если забыли добавить важные entry
  $must = [
    'assets/src/scss/main.scss',
    'assets/src/js/main.js',
    'assets/src/scss/editor.scss',
    'assets/src/scss/admin-dark.scss', // новый ключ для тёмной админки
  ];
  $missing = array_filter($must, fn($k) => !ld_vite_has_entry($k));
  if ($missing) {
    $list = implode('<br>', array_map(fn($k)=>"<code>{$k}</code>", $missing));
    echo '<div class="notice notice-info"><p><strong>Lowdesign:</strong> в манифесте не найдены некоторые entry:<br>'.$list.'</p></div>';
  }
});