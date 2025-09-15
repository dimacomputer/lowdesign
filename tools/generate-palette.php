<?php
$input = __DIR__ . '/../assets/css/color.css'; // путь к твоему файлу с :root
$output = __DIR__ . '/../docs/theme.palette.json';

if (!file_exists($input)) {
  fwrite(STDERR, "⚠️  Missing CSS file: $input\n");
  exit(1);
}

$css = file_get_contents($input);
$palette = [];

if (preg_match_all('/--bs-([\w-]+):\s*(#[0-9a-fA-F]{3,6})\s*;/', $css, $matches, PREG_SET_ORDER)) {
  foreach ($matches as $m) {
    $slug = strtolower($m[1]);
    $hex = strtoupper($m[2]);
    $palette[] = [
      'slug' => $slug,
      'color' => $hex,
      'name' => ucfirst(str_replace('-', ' ', $slug))
    ];
  }
}

if (!count($palette)) {
  fwrite(STDERR, "⚠️  No matching variables found in $input\n");
  exit(1);
}

$json = json_encode($palette, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($output, $json);
echo "✅ Generated palette with " . count($palette) . " entries → $output\n";
?>
