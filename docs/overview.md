# Lowdesign — Admin Icons & ACF Field Icons (Overview)

**Goal:** единая икон-система в админке + возможность привязывать иконку к каждому ACF-полю. Минимум кода, предсказуемый UX.

## What you get
- Consistent admin previews (24×24 box, 18×18 glyph).
- Content Icon control for Posts/Pages/Terms (Theme icon / Media Library).
- ACF “Field Icon” — choose an icon per field (multi-source: sprite, cpt, ui, brand).
- Frontend helpers to render the chosen icon.

## Files (key)
- `assets/admin/icon-preview.css` — общий стиль превью/бейджей.
- `assets/admin/icon-preview.js` — sprite + media inline preview, не ломает ACF-кнопки.
- `assets/admin/acf-field-icons.js` — рисует маленькие иконки рядом с label полей (admin).
- `assets/admin/acf-field-icon-setting-preview.js` — превью иконки справа от select в настройке ACF.
- `inc/extensions/icon-system.php` — контрол Content Icon (источник/превью/сохранение).
- `inc/extensions/acf-field-icons.php` — настройка для ACF-полей + multi-source каталог.
- `assets/icons/src/{cpt,ui,brand}/*.svg` — inline наборы.

## Conventions
- Preview box: **24×24**, glyph: **18×18**, `fill: currentColor`.
- Radio focus: доступность через `:focus-visible`.
- We never hide `.image-wrap` → ACF buttons (Remove/Edit/Replace) always visible.
