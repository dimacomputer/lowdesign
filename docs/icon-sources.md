# Icon Sources

Editors can choose from three icon sources. Understanding how each option behaves ensures consistent rendering and prevents broken icons.

## No icon

- **Use when:** An item should intentionally display without accompanying iconography.
- **Front-end result:** Templates skip icon markup entirely.
- **Admin cues:** The preview panel shows "No icon selected" so editors understand nothing will render.
- **Implementation note:** Always guard template icon output with a check for the source value.

## Theme icon

- **Use when:** A sprite icon from `assets/icons/src/` already covers the needed artwork.
- **Front-end result:** Templates call `ld_icon( 'slug' )` or output inline SVG markup fetched from the compiled sprite.
- **Admin cues:** Selecting a sprite reveals a live preview fed by the sprite manifest.
- **Implementation note:** Slugs should follow the `set/name` pattern (e.g., `ui/check`) so they remain unique.

## Media Library

- **Use when:** A bespoke icon needs to be uploaded or the sprite is not yet updated.
- **Front-end result:** Templates render the uploaded SVG/PNG using WordPress attachment helpers.
- **Admin cues:** The file picker appears with the standard Media Library modal and the preview displays the uploaded asset.
- **Implementation note:** Encourage SVG uploads for crisp rendering; PNG should be limited to photographic or gradient assets.

## Fallback order

Templates should respect the selection made in ACF. If legacy data only stored attachment IDs, the code should still prefer explicit choices in this order:

1. Theme icon slug (sprite)
2. Media Library attachment
3. No icon (renders nothing)

Refer to the [ACF field documentation](acf-field-icons.md) for field keys and JSON exports.
