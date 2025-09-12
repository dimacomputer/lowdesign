# ARCHITECTURE.md

## Directory map
```
acf-json/               # Exported ACF field groups
assets/
├─ functions.php
├─ icons/               # SVG library (brand/glyph/ui)
├─ src/
│  ├─ js/
│  └─ scss/             # Bootstrap/WP token bridge, themes
build/                  # Vite build output
inc/
├─ bootstrap.php        # Theme bootstrap
├─ core/                # loaders, Vite helpers, i18n, ACF JSON
├─ helpers/             # Utility functions (ld_component, icons, etc.)
├─ assets/              # Enqueue scripts/styles
├─ cpt/, taxonomies/, extensions/, setup/, ui/
templates/
├─ components/
├─ front-page.php
├─ page.php
└─ hero/hero.php
styles/                 # theme.json variants (dark.json)
.github/workflows/      # CI/CD definitions
```

## Main entry points
- `functions.php` defines theme constants and loads the bootstrap script
- `inc/bootstrap.php` orchestrates core modules, helpers, CPTs, taxonomies, extensions, theme setup, asset enqueues, and navigation/widgets
- `inc/core/loader.php` provides safe `require` helpers and directory autoloading
- `inc/assets/enqueue-frontend.php` and `inc/assets/editor.php` enqueue Bootstrap overrides and Vite-built bundles for the front-end and editor respectively
- Templates: `templates/front-page.php` demonstrates component inclusion via `ld_component()`

## Assets & configuration
- SCSS/Bootstrap tokens live in `assets/src/scss`, with WordPress preset mapping in `_wp-tokens.scss`
- Icons/SVG: source files stored under `assets/icons/src/`
- ACF JSON exports reside in `acf-json/` (e.g., `site-settings.json`, `term-icon.json`)
- Polylang configurations are absent; multilingual handling is likely external.
- Theme style variants are stored in `styles/dark.json` for block theme tokens.

## Deployment
- GitHub Actions workflow `deploy-to-nas.yml` pushes commits on `main` to a NAS, writing SSH credentials, triggering remote `pull.sh`, and verifying HEAD alignment

## Architectural approach
- **Standardized:** safe `require` utilities, Vite integration, directory-based autoloading, bootstrap sequencing, and block-theme token mapping.
- **Custom:** `ld_component` for component rendering, SVG icon loader with NAS fallback, NAS-based deployment workflow, dark theme JSON preset.

## Risk hotspots

- Asset enqueue relies on the Vite manifest; in debug mode missing or unreadable files are logged but styles/scripts are still absent.
:::task-stub{title="Provide basic fallback assets"}
- Bundle minimal CSS/JS so the site remains usable without a Vite build.
:::

## Next steps
1. Document or add Polylang integration if multilingual support is planned.
2. Expand tests or linters to cover PHP helper files and deployment scripts.
3. Add unit tests for deterministic loader behavior.
4. Bundle minimal CSS/JS as a fallback when the Vite build is missing.
5. Create an `npm test` script to unify PHP linting and build checks.
