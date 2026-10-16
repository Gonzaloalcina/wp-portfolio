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
