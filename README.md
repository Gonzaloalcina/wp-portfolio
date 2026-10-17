# wp-portfolio

> **Work in progress.** In place: repository base, design system (tokens,
> fonts, base styles, icons, light/dark), theme skeleton, CI and the
> Playground blueprint. Not built yet: page templates, blog blocks, the
> contact form plugin and i18n.

Source code for [gonzaloalcina.dev](https://gonzaloalcina.dev), the portfolio of
Gonzalo Alcina, a WordPress developer. The repo is part of the portfolio: it
shows how the site is built, tested and documented, not just the result.

## Stack

- Custom PHP theme with SCF for dynamic sections. No page builders.
- Gutenberg for blog posts only, with a few custom blocks (React in the
  editor, PHP render). Pages use the classic editor and SCF fields.
- English and Spanish with Polylang.
- SEO with Rank Math.
- Custom plugin with a REST contact form (server-side Cloudflare Turnstile,
  honeypot, rate limiting).
- Design system as code: W3C design tokens compiled to CSS custom properties,
  with a living `/design-system` page.
- No bundler: plain CSS and native ES modules; `@wordpress/scripts` only for
  blocks.
- PHPCS and token tests in GitHub Actions; PHPUnit and Playwright to come.
- Public demo through WordPress Playground.

## Demo

[Open in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Gonzaloalcina/wp-portfolio/main/blueprint.json)
(available once the repository is public). It runs the theme from `main` with
its plugins in your browser, logged in as admin, on the `/design-system/`
page. Nothing is shared: every visitor gets a throwaway copy.

## Design system

The visual rules live in code, not in a design tool. The intent behind them is
in [docs/design-brief.md](docs/design-brief.md); the values are in
[tokens/tokens.json](tokens/tokens.json). If the two disagree, the tokens win.

### Principles

- **Minimal:** one paper, one ink, one grey. A serif for headings, a sans for
  text, thin lines and small square details. Hierarchy comes from tone,
  weight and spacing, never from decoration.
- **One unit:** everything derives from `--unit` (0.5rem, 8px at the default
  root size). Sizes are in rem, so the whole system follows the font size a
  visitor sets in the browser.
- **Inspectable:** plain CSS custom properties, no Sass and no build step for
  styles. What you read in DevTools is the system.

### Tokens

```
tokens/tokens.json  ──  npm run build:tokens  ──▶  theme/assets/css/tokens.css    (custom properties)
   (W3C format)                                ──▶  theme/assets/data/tokens.json  (data for /design-system)
```

- **Source:** `tokens/tokens.json`, in the W3C Design Tokens format. Each
  dimension stores its unit multiplier in
  `$extensions["com.gonzaloalcina.unit"]`, so the build emits
  `calc(var(--unit) * n)` instead of a fixed value.
- **Build:** `scripts/build-tokens.mjs`, a dependency-free Node script. Both
  outputs are generated and committed, so the theme works without a build
  step. Never edit them by hand.
- **Naming:** the token path joined with hyphens: `font.size.sm` →
  `--font-size-sm`, `space.3` → `--space-3`. Colours drop the mode:
  `color.light.ink` → `--color-ink`.

| Group | Tokens | Values (at the default root size) |
|---|---|---|
| Unit | `--unit` | 0.5rem (8px) |
| Space | `--space-0-5` … `--space-12` | 4, 8, 12, 16, 24, 32, 48, 64, 96px |
| Font size / line height | `sm`, `base`, `lg`, `xl` | 14/24, 16/24, 24/32, 40/48px |
| Font family | `heading`, `body` | Newsreader, Inter |
| Font weight | `regular`, `heading`, `strong` | 400, 500, 600 |
| Layout | `text`, `wide`, `gutter` | 640, 1024, 24px |
| Colour | `paper`, `ink`, `muted`, `line` | light and dark values |
| Details | `line-width`, `marker-size`, `icon-size`, `icon-stroke-width` | 1px, 4px, 24px, 1.5 |

**Rules enforced by `npm run test:tokens`** (also in CI):

- `unit` is 0.5rem, and every dimension is in rem except declared hairlines.
- Every dimension equals its multiplier × unit.
- Multipliers are steps of 0.5 unit; font sizes may use steps of 0.25.
- Line heights are whole units, so all text sits on the 8px grid.
- Light and dark define the same colours.
- The committed `tokens.css` and `tokens.json` match the source.

To change a value: edit `tokens/tokens.json`, run `npm run build:tokens`, and
commit the source and both generated files together.

### Colour and dark mode

Light is the reference theme. Contrast against paper is 16.4:1 (ink) and
5.0:1 (muted) in light, 14.9:1 and 6.2:1 in dark: both pass WCAG AA. Lines are
not a second grey: `--color-line` is muted at 30% opacity.

The site follows the system colour scheme. The toggle in the header switches
to the other scheme; a choice that differs from the system is stored in
`localStorage` (`portfolio-theme`) and set as `data-theme` on `<html>` by an
inline script before first paint, so there is no flash. Choosing the system's
own scheme clears the stored value.

### Typography

Newsreader (headings, weight 500, tracking -0.02em) and Inter (text), both
self-hosted as variable WOFF2 files split into latin and latin-ext. No
third-party font requests. The latin files are preloaded; latin-ext and the
Inter italic download only when a page needs them.

### Icons

Fourteen [Lucide](https://lucide.dev) icons (ISC) in `theme/assets/icons/`,
drawn at `--icon-size` with a thinner stroke (`--icon-stroke-width`). They
take the text colour and are decorative, so the text next to them carries the
meaning.

```php
<?php portfolio_theme_icon( 'gauge' ); ?>
```

Editors pick icons in a visual grid. The picker is one reusable SCF field
(group "Icon", field key `field_portfolio_icon`), registered in code; other
field groups include it with a Clone field. To add an icon, drop its SVG into
`theme/assets/icons/`: it appears in templates, in the picker and on
`/design-system`.

### Block editor

Blog posts use Gutenberg, limited to the system: the four token font sizes,
no colour, gradient or duotone controls, and no default spacing or shadow
presets. A colour fixed inside a post would break dark mode.

### Living reference: `/design-system`

A page (noindex) that renders every token, the type scale, the palette with
computed contrast ratios, lines and details, layout widths, the icon library
and the light/dark state. It reads `theme/assets/data/tokens.json`, so it
cannot drift from the tokens.

## Structure

```
theme/               # WordPress theme (portfolio-theme)
  assets/css/        # fonts, generated tokens, base styles, page styles
  assets/fonts/      # self-hosted Newsreader and Inter
  assets/icons/      # Lucide SVGs
  assets/js/         # native ES modules (theme toggle)
  inc/               # theme features, one file each
tokens/              # design tokens (source)
scripts/             # build scripts (tokens)
tests/               # token tests; PHPUnit and Playwright to come
docs/                # design brief and decision log (by phase)
.github/             # CI workflow and Dependabot
blueprint.json       # WordPress Playground demo
```

Planned: `plugin/` (contact form plugin, `portfolio-core`).

WordPress core, uploads and the database are not part of this repo. `theme/`
is symlinked into a local WordPress install.

## Development

Requirements: Node 20+, PHP 8.0+, Composer.

```bash
composer install          # coding standards (dev only)
npm run build:tokens      # regenerate tokens.css and tokens.json
npm run test:tokens       # token rules and generated files up to date
composer run lint         # PHPCS: WordPress Coding Standards, PHP 8.0+
nvm exec 24 npm run playground   # run blueprint.json locally with ./theme mounted (Node 24+)
```

## Environments

### No build step for the theme

There is no `npm run dev` / `npm run build` split for the theme, on purpose.
CSS is plain (custom properties, native nesting) and JavaScript uses native ES
modules, so the files in the repository are the files the browser loads, in
every environment. What you inspect in production is what you wrote.

The exceptions are explicit and checked:

- **Design tokens:** `npm run build:tokens` generates `tokens.css` and
  `tokens.json`. Both are committed and CI fails if they are out of date.
- **Gutenberg blocks** (planned): built with `@wordpress/scripts`, which brings
  its own `start` (development) and `build` (production) commands.

Minifying CSS can be added as a deploy step if measurements justify it; with
server compression the gain is usually small.

### Environments

| Environment | Where | Status |
|---|---|---|
| Local | XAMPP on macOS, `WP_ENVIRONMENT_TYPE=local` | In use |
| Demo | WordPress Playground (`blueprint.json`) | Ready, public once the repo is |
| Staging | Private copy for review: noindex, password-protected, test keys | Planned |
| Production | gonzaloalcina.dev | Planned (hosting not chosen yet) |

Planned deployment: GitHub Actions deploys `main` to staging and version tags
to production, only after CI passes. Environment-specific behaviour (debug,
indexing, API keys) is driven by `wp_get_environment_type()` and
configuration, never by editing code per environment.

## Decisions

Every closed decision, with its reasons, is in
[docs/decisions/](docs/decisions/README.md), one file per project phase.
