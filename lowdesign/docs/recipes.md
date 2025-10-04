# Recipes

Common workflows for maintaining the icon system and related theme features.

## Add a new icon to the sprite

1. Save the SVG into `assets/icons/src/<set>/<name>.svg`.
2. Ensure the SVG includes a `<title>` element for accessibility.
3. Run `npm run build` (or `npm run dev` while developing) to regenerate the sprite.
4. Update the ACF select field choices if editors need to pick the new icon.
5. Reference the icon in templates using `ld_icon( '<set>/<name>' )`.

## Swap an icon in an existing component

1. Confirm the target component uses `ld_icon()` or inline sprite references.
2. Update the component markup to point to the new icon slug.
3. Test the component in Storybook or the block editor preview to verify sizing and alignment.
4. Communicate the change to editors if the new icon alters meaning.

## Provide a fallback media icon

1. Open the relevant post, page, or term in the WordPress admin.
2. In the Icon field group, choose **Media Library**.
3. Upload the fallback SVG/PNG or select an existing attachment.
4. Publish or update the entry to save the attachment reference.
5. Confirm the front end renders the uploaded icon instead of the sprite entry.

## Clean up unused icons

1. Audit icon usage across templates and ACF configurations.
2. Remove unused SVG files from `assets/icons/src/`.
3. Update the sprite by running `npm run build`.
4. Delete orphaned choices in ACF field definitions.
5. Verify no templates reference the removed icon slugs before committing.

More edge-case handling lives in [Troubleshooting](troubleshooting.md). For the underlying architecture, refer to the [icon system guide](icon-system.md).
