# Troubleshooting

Guidance for diagnosing common issues related to the icon workflow and theme build process.

## Sprite icon not appearing on the front end

- Confirm the icon slug stored in ACF matches an SVG file in `assets/icons/src/`.
- Re-run `npm run build` to regenerate the sprite after adding new icons.
- Ensure the deployed environment received the updated `assets/icons/src/` files; the sprite itself is generated at build time.
- Inspect the rendered markup to verify that `use` tags point to an existing symbol ID.

## Media Library icon renders as a large image

- Check that the attachment markup includes explicit `width` and `height` attributes that match the icon utility classes.
- Add or adjust CSS in `assets/src/scss/utilities/_icons.scss` to constrain image-based icons if they must remain raster formats.
- Consider converting the asset to SVG if crispness or scaling remains problematic.

## Editors do not see icon preview updates

- Verify that the admin JavaScript responsible for preview updates is enqueued via the relevant ACF hooks.
- Confirm that `acf-json/` exports are in sync with the active site; re-export if field keys changed.
- Clear the browser cache or disable aggressive caching plugins that may block the admin script.

## Build command fails after adding icons

- Lint the SVG with `npm run lint:icons` (if available) to catch malformed markup.
- Look for XML declarations or embedded raster images that the sprite generator cannot parse.
- Validate that filenames use lowercase letters, numbers, and hyphens to avoid path resolution issues.

For process guidance see the [recipes](recipes.md), and review [icon sources](icon-sources.md) to confirm the selected fallback behavior.
