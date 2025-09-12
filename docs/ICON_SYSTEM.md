# Icon System

Tracking integration of a unified icon system.

## Current state
- Legacy icon helpers removed.
- Source SVGs consolidated under `assets/icons/src`.
- No unified sprite.

## Goal
Migrate to structured `assets/icons/src` with a generated `sprite.svg` and ACF integration.

## Admin Integration — kickoff

### Current state
- Sprite build pipeline is in place (`assets/icons/src` → `assets/icons/sprite.svg`).
- ACF fields exist for terms, posts, and menus but need polish (preview, labels, priority).

### Goal
- Enable preview in WP admin for icon selects.
- Support dual source for terms: library (sprite) + custom upload (SVG/PNG) with clear priority.
- Inject menu icons on frontend.

### Acceptance criteria
- [x] Sprite inlined on admin pages.
- [x] Live preview beside ACF selects (`menu_icon`, `post_icon_name`, `term_icon_name`).
- [x] Terms: Select (library) overrides Upload (fallback image).
- [x] Category/Tag list shows "Icon" column.
- [x] Menu items render chosen icon before label on frontend.
- [x] Docs updated for editors (how to use).

Admin list "Icon" columns are fixed at 28px wide (24px glyph + 4px left padding), and the preview assets scope themselves to ACF icon fields via their `data-name` attributes.

### Helpers

Render the current taxonomy term's icon at the default size (24px); sprite selection overrides upload:

```php
echo ld_term_icon_html();
```

Output a larger post or page icon (32px) and fall back to an uploaded image when no library icon is chosen:

```php
ld_content_icon( null, [ 'class' => 'icon--32' ] );
```

### How to use in WP Admin

- **Categories/Tags:** pick **Icon (library)** or upload **Custom Icon**; the library choice overrides the upload.
- **Menus:** open the menu item and set the **Menu Icon** field.
- **Preview:** a small icon appears next to the select; if you don't see it, reload the page.

### Examples for Pages

In a page header:

```php
echo ld_content_icon(null, ['class' => 'icon']);
```

Inside loops (e.g., page list):

```php
echo ld_content_icon(get_the_ID(), ['class' => 'icon icon--24']);
```

