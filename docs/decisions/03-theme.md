# Phase 03 — Theme

Theme structure, requirements and templates.

See [README.md](README.md) for how entries are written.

## 2026-10-16 — Theme header: PHP 8.0, WordPress 7.1, GPL-2.0-or-later

- `Requires PHP: 8.0` (the local PHP). **Provisional** until the hosting PHP
  version is known.
- `Requires at least: 7.1` (the version the theme is built on).
- License `GPL-2.0-or-later`, as WordPress themes require.

## 2026-10-16 — Theme toggle: light/dark, system by default, in the header

- **Two states.** The site follows the system until the visitor clicks; each
  click switches to the other scheme. A three-state cycle (System, Light,
  Dark) was rejected: two of its states always look the same.
- **Back to system without a third state:** a choice that matches the system
  scheme is not stored, so the site follows the system again.
- **Placement and style:** right side of the header, small muted text
  (`Theme: Light` / `Theme: Dark`, the scheme on screen), underlined on hover
  and focus like a link. No icons.
- **No flash:** a short inline script at the top of `<head>` applies the stored
  choice (`localStorage` key `portfolio-theme`) before first paint.
- **Script:** native ES module (`wp_enqueue_script_module`), no build. The
  button is rendered `hidden` and shown by the module, so without JavaScript
  there is no dead button and the site follows the system.
- `localStorage` is functional storage: list it in the privacy and cookie
  notice when it is written.

## 2026-10-18 — Agnostic components, BEM, numeric modifiers

- **Components are named by what they are plus a design letter**: `hero-a`,
  `section-a`, `list-a`, `row-a`, `actions-a`, `button-a`, `link-row-a`.
  Never by the page that uses them (`home-hero` would be duplicated the day
  About needs the same hero). A page template only chooses and combines
  components.
- **Letter = a different design** (other markup or layout): `hero-b` is a new
  component. **Numeric modifier = a small variation of the same design**:
  `--second`, `--third`, never descriptive names.
- **BEM without element chains.** When an element has parts of its own, it
  becomes a block nested inside the parent's element: `list-a` >
  `list-a__item` > `row-a` > `row-a__title`, never
  `list-a__item__title`. A parent places a child with a BEM mix
  (`class="actions-a hero-a__actions"`): the child's block gives the look, the
  parent's element gives the position.
- **One template and one stylesheet per component**:
  `template-parts/components/<name>.php` (data in through `$args`, rendered
  with `portfolio_theme_component()`) and
  `assets/css/components/<name>.css`, compiled into the site stylesheet
  (see `01-foundation.md`, 2026-10-18).
- **Page width belongs to the layout block, not to the page CSS.** `site`
  (`site__header`, `site__main`, `site__footer`) uses the text measure;
  `site--second` uses the wide one. The template sets it with
  `get_header( null, array( 'variant' => 'second' ) )`. There is no page
  stylesheet for the Home.
- **Values that are data, not variants**, go in `data-*` attributes:
  `<svg class="icon" data-icon="gauge">`. JavaScript hooks use `data-*` too,
  never classes.
- **Kept as they are:** WordPress's own classes (`alignwide`,
  `screen-reader-text`, body classes, `wp-block-*`), `acf-*` in the admin,
  and element selectors for content written in the editor.
