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
