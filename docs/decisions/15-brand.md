# 15 — Brand

The mark, the logo lockup, the favicon and where they live.

## 2026-10-22 — Mark, logo and favicon

Decided:

- **Mark:** a cell with its marker: a square frame (4/64 of the mark) with the
  4px-marker square inside, bottom left. It is the grid detail of the design
  system, drawn once as a logo. Three options were compared: a solid square
  with a serif G, this frame, and a "G." with a square full stop. The solid G
  held up best at 16px; the frame was chosen because it needs no initial and
  is literally the site's grid. The "G." read as a plain letter and its stop
  disappeared at small sizes.
- **Name:** "Gonzalo Alcina" in Newsreader 500, tracking -0.02em, next to the
  mark. In the header it is live text (heading font from the tokens), not an
  image.
- **Colour:** ink and paper only, light and dark pairs from the tokens. No
  accent colour. No token values change.
- **Header:** the `logo-a` component (mark as inline SVG with `currentColor`,
  so it follows the link colour and the theme; name as text). The mark is
  `aria-hidden`; the name is the link's accessible name. Below 36rem the mark
  and name step down one size (mark 2 units, same as the favicon) so the
  header still fits one row at 360px. At 320px the name wraps to two lines.
- **Favicon:** `assets/brand/favicon.svg` (paper tile with the mark, so it is
  visible on any tab colour), `favicon.ico` (16, 32, 48) and
  `apple-touch-icon.png` (180). Printed by `inc/brand.php`; a Site Icon set in
  the Customizer takes over. A transparent SVG that switches with
  `prefers-color-scheme` was rejected as the default: it can vanish when the
  browser and system themes differ.
- **Sizes:** at 64 units the frame is 4 and the marker 16, so at 16px the
  frame is exactly 1px and the marker 4px, and the ICO stays pixel-sharp.

Not in the repo (kept in the git-ignored `.private/brand/`): avatars, the
default social image, the email signature and the PNG/SVG logo set. Per-page
social images stay with Rank Math.

Open: `/favicon.ico` at the web root. WordPress answers it with its own icon
when no Site Icon is set; place a copy of `favicon.ico` at the web root when
deploying (see 12 — Deploy).

## 2026-10-22 — A social image per page, rendered from the brand template

Every published page, note, project and service has its own 1200×630 social
image: the mark on the left and the page title, on the layout of the default
image (Gonzalo). The front pages keep the default image, and noindex or
redirected pages get none.

- **Rendered locally, not on the server.** `scripts/og-images.mjs` draws an
  HTML template in Playwright's Chromium (already a dev dependency) with the
  site's fonts and the light-theme tokens; `scripts/og-images.php` exports
  the list of titles and imports the PNGs with WP-CLI. Rejected: generating
  them on the fly in PHP, which needs GD with font support on the host, costs
  CPU on each request and would not match the site's typography.
- **Rank Math holds them** as each page's Facebook image; Twitter reuses it.
  A rerun replaces the file of the same attachment, so there are no
  duplicates in the media library.
- **Cost:** rerun the two steps when a title changes or a page is added. The
  images are content (media library), not committed.
