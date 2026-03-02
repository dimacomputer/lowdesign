# LowDesign → Full Gutenberg / FSE migration (v1)

This package converts the theme to a block theme using `/templates/*.html` and `/parts/*.html`.

## What changed

1. **Block templates added**
- `/templates/*.html`
- `/parts/header.html`, `/parts/footer.html`

2. **Design system exports connected**
- Replaced legacy LD bundle (009–016) with:
  - `assets/css/010-ld-chroma.css`
  - `assets/css/011-ld-bs-bridge.css`
  - `assets/css/012-ld-form.css`
  - `assets/css/013-ld-motion.css`
  - `assets/css/020-ld-fse-block-styles.css`

Legacy CSS files are moved to: `/_legacy_pre_fse/assets_css_pre_fse/`

3. **Runtime selector**
`inc/core/ld-theme-runtime.php` now emits:
- `data-ld-theme="default|dark|..."`
- `data-bs-theme="light|dark"`

4. **Theme JSON**
`theme.json` palette uses exported LD refs (var(--ld-ref-...)).

5. **Patterns**
Starter patterns in `/patterns/` (Hero, Cards, CTA, Posts grid).
Pattern category: `LowDesign`.

## Deployment steps (safe)

1) Backup current theme folder.

2) Copy these folders/files into your theme:
- `/templates/`
- `/parts/`
- `/patterns/`
- `/assets/css/010-ld-chroma.css`
- `/assets/css/011-ld-bs-bridge.css`
- `/assets/css/012-ld-form.css`
- `/assets/css/013-ld-motion.css`
- `/assets/css/020-ld-fse-block-styles.css`
- `/assets/json/ld_motion.json`
- `/theme.json`
- `/inc/core/ld-theme-runtime.php`
- `/inc/setup/fse.php`
- `/assets/src/js/modules/dark-light-theme-detection.js`

3) Ensure `inc/assets/ld-css-enqueue.php` is active (it is in bootstrap).

4) In WP Admin:
- Appearance → Editor:
  - Open **Header** and **Footer** template parts and adjust blocks.
- Create/convert menus:
  - Use Navigation block UI to select/create menu.
- Open a page and insert patterns (Patterns tab → LowDesign).

## Notes

- Any old PHP templates were moved to `/_legacy_pre_fse/templates-php/`.
- You can delete the legacy folder after you verify everything.
- Next step is to migrate custom PHP components into patterns + block styles, and remove unused PHP hooks.
