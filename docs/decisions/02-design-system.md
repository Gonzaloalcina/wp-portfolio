# Phase 02 — Design system

Styling approach, tokens, typography, colour and the tokens build.

See [README.md](README.md) for how entries are written.

## 2026-10-16 — Styles in plain CSS custom properties, no SCSS

Design tokens (JSON) generate a `tokens.css` with custom properties; there is
no Sass. The system stays inspectable in DevTools (a visitor can read `--unit`
and the scale), there is one source of truth instead of `$unit` plus `--unit`,
and no extra build step. Native nesting, `calc()` and `clamp()` cover what Sass
would have added.

## 2026-10-16 — Everything derives from an 8px unit

`--unit: 8px`, with 0.5 steps (4px) allowed. Font sizes and line heights follow
it too: 12/16, 16/24, 24/32, 40/48. Exceptions: 1px hairlines and heading
tracking in `em`. See `docs/design-brief.md` and `tokens/tokens.json`.

## 2026-10-16 — Typography: Newsreader (headings) + Inter (text), self-hosted

Serif headings and sans text give a clear hierarchy. Both fonts are open
licence and served from this site, with no third-party requests (GDPR).

## 2026-10-16 — Colour: cream paper, light and dark modes

Palette: cream paper, near-black ink, one grey. The site follows the system
colour scheme by default, with a manual toggle. Light is the reference theme
and the fallback.

## 2026-10-16 — Tokens build: own Node script, not Style Dictionary

The project only outputs CSS, and its distinctive rules (`calc(var(--unit) * n)`,
the light/dark block, `color-mix` lines) would need custom transforms and
formats in Style Dictionary anyway. A short script with no dependencies is
easier to read and review. Tokens stay in W3C format, so moving to Style
Dictionary later is straightforward if more platforms are needed.

JavaScript reads token values from the CSS custom properties at runtime
(`getComputedStyle`), so CSS remains the single source of truth. A JS output is
added only if a build step needs it.

## 2026-10-16 — Generated `tokens.css` is committed

`theme/assets/css/tokens.css` is generated from `tokens/tokens.json` and
committed. The theme works without a build step (WordPress Playground, a
downloaded zip). A test fails if the committed file is out of date with the
tokens, so the JSON stays the source of truth.

## 2026-10-16 — Font files: Fontsource, weight axis only, Inter italic

- Source: Fontsource packages (`@fontsource-variable/inter` and
  `@fontsource-variable/newsreader`, v5.3.0, OFL-1.1), fetched with `npm pack`
  and copied into `theme/assets/fonts/` with their licences. They are already
  WOFF2, variable and split into latin and latin-ext, so no subsetting step.
- **Weight axis only.** Newsreader's optical size axis (better detail at large
  sizes) more than doubles the file (57 KB → 129 KB for latin). Deferred until
  it can be judged on the real render.
- **Inter italic included** so the blog does not show a synthetic slant. It
  downloads only on pages that use italics. Newsreader has no italic.
- The two latin normal files are preloaded; latin-ext and italic load on
  demand through `unicode-range`. A typical page downloads about 104 KB of
  fonts.

## 2026-10-16 — `/design-system` page rendered from generated token data

- The build script also writes `theme/assets/data/tokens.json` (flat list:
  path, CSS variable, value, multiplier; colours paired light/dark). Generated
  and committed like `tokens.css`, and covered by the same up-to-date test.
- The page uses the `page-design-system.php` template and renders every value
  from that file, so it cannot drift from the tokens. Contrast ratios are
  computed in PHP (WCAG 2), not typed by hand.
- `design-system.css` loads only on that page.
- **noindex is set in Rank Math** on the page (`rank_math_robots`), the way an
  editor would. It lives in the database, so the Playground blueprint must set
  it too.

## 2026-10-16 — Small text is 14/24; font sizes allow quarter units

12px read too small on the real render. `font.size.sm` is now 14px (1.75
units) with a 24px line height (3 units). Font sizes may use steps of 0.25
unit (2px); everything else keeps steps of 0.5, and line heights stay whole
units so all text sits on the 8px grid. Supersedes the 12/16 step in "Everything
derives from an 8px unit". The tokens test enforces both step sizes.

## 2026-10-16 — Dimensions in rem: `--unit: 0.5rem`

Px sizes ignore the font size a visitor sets in the browser (zoom still
works, but the setting does not), which hurts accessibility. The unit is now
`0.5rem` (8px at the default 16px root) and every dimension token is in rem,
so the whole system scales with that setting. Hairlines stay at `1px`. The
8px grid and all multipliers are unchanged. The tokens test checks that only
hairlines use px. Supersedes the `--unit: 8px` value in "Everything derives
from an 8px unit".

## 2026-10-16 — Strong weight 600; H3 at base size and strong weight

- New token `font.weight.strong: 600`, used by `strong`/`b`.
- H3 uses the base size (16/24) with the strong weight, in the heading font.
  H4–H6 use the base size with the heading weight for now.

## 2026-10-16 — Base style details: links, rhythm, focus

- **Link underline offset: 4px** (`--space-0-5`) instead of the brief's 3px,
  which would break the unit rule.
- **Vertical rhythm:** 24px (`--space-3`) between blocks, 48px (`--space-6`)
  before a heading and 16px (`--space-2`) after it.
- **Focus:** 1px outline in the current colour, offset 4px.

## 2026-10-17 — Icon library: Lucide icons, picked from a reusable SCF field

- **Source:** Lucide (`lucide-static` 1.59.0, ISC), SVGs copied into
  `theme/assets/icons/` with the licence. Chosen for its 24-unit grid (3 of our
  units) and a stroke that CSS can thin down. Only the icons in use are
  shipped, not the 2,000+ set.
- **Icons:** accessibility, blocks, code-xml, flask-conical, gauge, languages,
  layout-template, megaphone, pen-line, search, shield-check, tag, trending-up,
  workflow; sun and moon added 2026-10-18 for the theme toggle (same version).
  Adding an SVG to the folder adds it everywhere.
- **Rendering:** `portfolio_theme_icon( $name )` inlines the SVG (only known
  names, filtered with `wp_kses`), `currentColor`, hidden from assistive
  technology because icons are decorative.
- **Tokens:** `icon.size` (3 units) and `icon.stroke-width` (1.5, thinner than
  Lucide's 2, to match the hairline style).
- **Editor:** one inactive SCF field group ("Icon") holds a radio field whose
  choices are generated from the folder. Other groups (cards, sections) reuse
  it through a Clone field, so the picker is defined once, in code. In the
  admin it shows as a grid of icon tiles: SCF strips SVG from choice labels,
  so each label carries `data-icon` and admin CSS draws the icon with a mask.
- `/design-system` shows the library and a demo card whose icon is picked on
  that page (demo field group, not the final card component).

## 2026-10-21 — H3 in the serif at its own size; one style for H4–H6

On the real render, H3 at base size read like a bold paragraph and did not
separate from the H2, and the serif at base size and weight 600 looked heavy.
New token step `md`: 20px (2.5 units) with a 24px line height (3 units). H3
uses it in Newsreader, with the heading weight and tracking, like H1 and H2.
H4–H6 share one style: Inter at base size, weight 600 (`font.weight.strong`),
no heading tracking. Supersedes the H3 part of "Strong weight 600; H3 at base
size and strong weight".

## 2026-10-22 — Standalone links end in an arrow instead of an underline

The underline of standalone links (row titles, link rows, "All …", facts,
testimonial names, pagination) clashed with the 1px dividers next to them.
They now drop it and end in a small ↗ arrow, muted at rest and full ink on
hover and focus, the same wherever the link leads (a first version used →
for this site and ↗ for other sites; Gonzalo preferred ↗ everywhere). The
underline returns on hover and focus. The pagination keeps ← and →, where
the arrow gives the direction.

- Links in running text keep the underline: they are the colour of the text,
  so without it they would be told apart by nothing (WCAG 1.4.1).
- Menus keep their own style: nav-a, the language switch, the site title and
  the footer menu (no arrow, underline on hover and focus).
- CSS only (`theme/assets/css/links.css`): no component changed its args or
  markup. Arrows are Lucide `arrow-up-right`, `arrow-right` and `arrow-left`
  as masks, so they take the text colour; they flip in right-to-left text.
- Rejected: an arrow on every link, including running text (noise inside
  sentences); no icon at all with underline on hover only (no hint on touch
  screens, which have no hover).

