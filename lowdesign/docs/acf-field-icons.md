# ACF Field Icons

The theme exposes an "Icon" field group through Advanced Custom Fields (ACF) that lets editors pick icons for navigation, posts, and taxonomy terms. This document summarizes the field layout, UI copy, and expected behaviors so the experience stays consistent as we iterate.

## Field group locations

- **Site Settings:** Provides global icon overrides for navigation and footer components.
- **Page and Post edit screens:** Allows per-entry icon selection that surfaces in list tables and cards.
- **Taxonomy terms:** Supplies icon choices for categories, tags, or custom terms where iconography is part of the design language.

## Field structure

1. **Icon Source (Radio):** Options include "No icon," "Theme icon," and "Media Library."
2. **Theme Icon (Select):** Populated from the sprite manifest; displays a live preview via JavaScript when an option is chosen.
3. **Media Icon (File/Image):** Accepts SVG or PNG uploads. The field appears only when "Media Library" is selected.
4. **Preview (Message field):** Mirrors the selected source and helps the editor confirm their choice.

## Conditional logic

- Choosing **No icon** hides both the sprite selector and media upload fields.
- Choosing **Theme icon** reveals the sprite selector and preview while keeping the media upload hidden.
- Choosing **Media Library** reveals the media upload and preview while hiding the sprite selector.

## Data storage

- Sprite selections store the icon slug (e.g., `ui/arrow-right`) for use with `ld_icon()` or inline SVG rendering.
- Media uploads save the attachment ID so templates can fetch the corresponding image markup.
- The icon source radio value should always be checked in templates before outputting markup.

## Maintenance checklist

- Confirm that new icons added to `assets/icons/src/` are registered in the ACF select choices.
- Ensure translation strings for field labels and instructions live alongside other ACF field translations.
- When duplicating field groups, keep the field keys stable to avoid data loss.

For usage examples, see the [recipes collection](recipes.md) or the [icon source reference](icon-sources.md).
