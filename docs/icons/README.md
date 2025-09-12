# Icon Guidelines

Source SVGs live in `assets/icons/src` and are grouped into `brand`, `social`, `ui`, and `glyph`.

## Rules
- `viewBox` must be `0 0 24 24`
- Only `currentColor` values are allowed for `fill` and `stroke`
- Name files `icon-{area}-{name}[-variant].svg`

## Build
Run the sprite build after adding or updating icons:

```
npm run icons
```
