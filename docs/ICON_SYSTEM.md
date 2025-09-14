# Icon System

Unified SVG icon system for the theme.

---

## Overview

- **Source of truth:** `assets/icons/src/**`  
- **Build artifact:** `assets/icons/sprite.svg` (generated)  
- **ACF integration:** select fields for menus, posts/pages, and terms with **live preview**
- **Priority rules:** **Library (sprite)** → **Upload (SVG/PNG)** → none
- **Default size:** **24×24 px** (`.icon`, `.icon--24`)
- **Content icons:** Editors choose a source ("No icon," "Theme icon," or "Media Library") before picking a sprite or uploading media.

---

## Site Config toggles

- **Path:** Dashboard → Site Config → Features → Icons
- **Toggles:**
  - **Content** — enables content icon rendering + admin columns for posts/pages.
  - **Terms** — enables taxonomy icon rendering + admin columns.
  - **Menu** — enables injecting icons into nav menu labels on front-end.
- **Note:** Admin sprite inline + preview assets load if any toggle is on.

---

## Content Icon (Posts & Pages)

1. **Icon source:** Choose "No icon," "Theme icon," or "Media Library."
2. **Theme icon:** Pick from the sprite list with a live preview.  
   **Media Library:** Upload or select an SVG/PNG as a fallback icon.
3. The selected source determines what renders on the front end and in admin list tables.

---

## Directory layout

## Admin QA

- Verified in WordPress admin that selecting "Theme icon" shows a live sprite preview when an icon is chosen.
- Switching the source to "Media Library" allows uploading or selecting an SVG/PNG and shows that media in the preview while hiding the sprite preview.
- Setting the source to "No icon" hides both icon fields and results in no icon rendering on the front end or in admin list tables.
- Confirmed that the "Icon" column in the Pages list displays a 24px icon corresponding to the chosen source.

