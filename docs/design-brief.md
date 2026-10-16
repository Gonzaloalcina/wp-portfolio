# Design brief

Visual rules for wp-portfolio. The values live in `tokens/tokens.json`; this
file explains the intent behind them. If the two disagree, the tokens win and
this file gets fixed.

Status: **v0**. Values are a starting point to be judged against the real
render, not final.

## Direction

Minimal, Notion-like: cream or white paper, black ink and one grey. A serif
for headings, a sans for text, a grid made of thin lines and small details
(little squares). Hierarchy comes from tone, weight and spacing, not from
decoration.

Inspiration only (mood, restraint, typographic care): leerob.com and paco.me.
Their values are not copied.

## One unit rule

Everything derives from a single base unit.

- `--unit: 0.5rem`, which is 8px at the default 16px root size. Steps of 0.5
  are allowed (4px). Sizes are in rem so they follow the visitor's font size
  setting; px values in this file are at the default size.
- Use `calc(var(--unit) * n)` or the derived tokens. Do not hard-code pixel
  values.
- Font sizes are multiples of 0.25 unit (2px). Line heights are whole units,
  so all text sits on the 8px grid.
- Two exceptions: 1px hairlines, and heading tracking (`-0.02em`, relative to
  the font size).

| Token | Multiple | Value |
|---|---|---|
| space 0-5 / 1 / 1-5 / 2 | 0.5 / 1 / 1.5 / 2 | 4 / 8 / 12 / 16 px |
| space 3 / 4 / 6 / 8 / 12 | 3 / 4 / 6 / 8 / 12 | 24 / 32 / 48 / 64 / 96 px |
| layout text / wide / gutter | 80 / 128 / 3 | 640 / 1024 / 24 px |
| marker | 0.5 | 4 px |

## Typography

- **Headings:** Newsreader (serif). Weight 500, tracking -0.02em.
- **Text:** Inter (sans). Weight 400.
- Both self-hosted (variable WOFF2, latin + latin-ext, `font-display: swap`).
  No third-party font requests: GDPR.
- Fallbacks: heading `Iowan Old Style, Georgia, serif`; text
  `system-ui, sans-serif`.

| Step | Size / line height | Use |
|---|---|---|
| sm | 14 / 24 | Meta: dates, labels. Muted colour |
| base | 16 / 24 | Body text |
| lg | 24 / 32 | Section headings (H2) |
| xl | 40 / 48 | Page title (H1) |

Rule of thumb: when something needs less weight, make it `muted` or `sm`, not
a new size.

## Colour

One ink, one grey, one paper.

| Token | Light | Dark |
|---|---|---|
| paper | `#FAF8F3` (cream) | `#171615` |
| ink | `#1A1A1A` | `#ECE9E2` |
| muted | `#6B6B66` | `#9A9791` |
| line | muted at 30% | muted at 30% |

Contrast (WCAG, computed): ink on paper 16.4:1 light, 14.9:1 dark; muted on
paper 5.1:1 light, 6.2:1 dark. Both pass AA for body text.

Lines are not a second grey: `color-mix(in srgb, var(--color-muted) 30%, transparent)`.

### Light and dark modes

- **Default:** follow the visitor's system (`prefers-color-scheme`).
- **Light is the reference theme.** It carries the identity and is the fallback
  without a preference, and the theme for print and social images.
- **Manual toggle:** sets `data-theme="light"` or `"dark"` on `<html>` and
  overrides the system. The choice is stored in `localStorage` (functional
  storage; list it in the privacy and cookie notice).
- An inline script in `<head>` applies the stored theme before first paint, so
  there is no flash.
- Set `color-scheme` so native controls follow the theme.

```css
:root { /* light tokens */ }
@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) { /* dark tokens */ }
}
:root[data-theme="dark"] { /* dark tokens */ }
```

The build generates this block from the tokens, so the dark values exist in one
place.

## Lines and details

- 1px lines, `--color-line`. Used as dividers and as the row structure of lists.
- Lists use a 4px square marker (`--marker-size`), not round bullets.
- Links are underlined (offset 4px, one half unit, underline at ~30% opacity) and change to
  full ink on hover and focus. Keep a visible focus style.

## Not allowed

These read as "made with AI" and are off the table:

- Cards with a coloured line on one side.
- Boxes of stats ("10+ years", "50 projects").
- Two-colour H1s.
- Gradients and shadows.
- Generic copy. Text is concrete and specific to this work.

## Tokens pipeline

`tokens/tokens.json` (W3C format) → generated `tokens.css` with CSS custom
properties. No Sass.

- Dimension tokens carry their multiplier in
  `$extensions["com.gonzaloalcina.unit"]`; the build emits
  `calc(var(--unit) * n)`.
- A test must fail if any dimension value differs from `multiplier × unit`
  (hairline excepted).
- `/design-system` (noindex) renders the tokens and components live.

## Open items

- Verify the dark values and heading weight on the real render.
- Judge Newsreader's optical size axis on the real render (fonts ship with
  the weight axis only; see `docs/decisions/02-design-system.md`).
