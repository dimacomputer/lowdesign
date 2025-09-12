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

### Helper

Render the current taxonomy term's icon (sprite selection overrides upload):

```php
echo ld_term_icon_html( null, 'icon--lg' );
```

### How to use in WP Admin

- **Categories/Tags:** pick **Icon (library)** or upload **Custom Icon**; the library choice overrides the upload.
- **Menus:** open the menu item and set the **Menu Icon** field.
- **Preview:** a small icon appears next to the select; if you don't see it, reload the page.

