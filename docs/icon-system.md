# Content Icon (Posts/Pages/Terms)

## UI
- **Icon source:** No icon / Theme icon / Media Library.
- Theme icon → dropdown with sprite slugs (takes priority over upload).
- Media Library → SVG only. Inline preview; can **Change** or **Remove** anytime.

## Frontend access
Stored meta:
- Posts: `_ld_content_icon` → `{ source: 'none'|'theme'|'media', theme: string, media_id: number }`
- Terms: `_ld_term_icon`   → same shape

Example:
```php
$meta = get_post_meta(get_the_ID(), '_ld_content_icon', true);

if (($meta['source'] ?? '') === 'theme' && !empty($meta['theme'])) {
  echo '<svg class="icon"><use href="#'.esc_attr($meta['theme']).'"></use></svg>';
} elseif (($meta['source'] ?? '') === 'media' && !empty($meta['media_id'])) {
  echo wp_get_attachment_image((int) $meta['media_id'], 'full');
}
```
