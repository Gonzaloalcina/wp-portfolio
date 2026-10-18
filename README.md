# wp-portfolio

> **Work in progress.** In place: repository base, design system (tokens,
> fonts, base styles, icons, light/dark), theme skeleton, CI and the
> Playground blueprint, blog blocks. Not built yet: page templates, the
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
- No bundler: CSS split per component and compiled by a zero-dependency
  script into one site stylesheet; native ES modules; `@wordpress/scripts`
  only for blocks.
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
- **Inspectable:** plain CSS custom properties, no Sass. The build only joins
  files, so what you read in DevTools is the system.

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

Three custom blocks live in the `portfolio-core` plugin, so posts keep them
if the theme changes (`plugin/blocks/`):

- **Related:** links the note to one service and, optionally, one project.
  Rendered in PHP from the current data, so a renamed service or a project
  whose client withdraws consent updates every note. A pre-publish check
  warns when a note links to no service (the sitemap rule).
- **Code with file name:** code with an optional file name and caption. No
  syntax highlighting and no JavaScript on the front end.
- **Aside:** a label (Note, Decision) and a few blocks between two 1px lines.

Each block's CSS loads only on pages that render it.

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

`plugin/` (`portfolio-core`) holds what must survive a theme change: the
project and service types and the blog blocks (`plugin/blocks/src`, built
into `plugin/blocks/build`).

WordPress core, uploads and the database are not part of this repo. `theme/`
is symlinked into a local WordPress install.

## Development

Requirements: Node 20+, PHP 8.0+, Composer.

```bash
composer install          # coding standards (dev only)
npm run build:tokens      # regenerate tokens.css and tokens.json
npm run test:tokens       # token rules and generated files up to date
npm run build:css         # compile theme/assets/css into theme/assets/build
npm run test:css          # build up to date, every component imported, no stray px
npm ci                    # @wordpress/scripts, for the blocks only
npm run build:blocks      # build plugin/blocks/src into plugin/blocks/build
npm run start:blocks      # same, rebuilding on every change
npm run check:blocks      # block build up to date, no stray px in block CSS
composer run lint         # PHPCS: WordPress Coding Standards, PHP 8.0+
nvm exec 24 npm run playground   # run blueprint.json locally with ./theme mounted (Node 24+)
```

## Environments

### Builds: small, committed and checked

There is no `npm run dev` / `npm run build` split for the theme, on purpose.
Every build output is committed, so the theme runs as it is in every
environment (Playground, a downloaded zip), and CI fails if an output is out
of date:

- **CSS:** one file per component in `theme/assets/css/`, compiled by
  `npm run build:css` (a zero-dependency script) into one site stylesheet in
  `theme/assets/build/`. It only joins files and drops comments, so the output
  reads like the source.
- **Design tokens:** `npm run build:tokens` generates `tokens.css` and
  `tokens.json`, then rebuilds the CSS.
- **Gutenberg blocks:** built with `@wordpress/scripts` (`npm run build:blocks`)
  into `plugin/blocks/build/`, committed like the CSS. `npm run start:blocks`
  rebuilds while editing.

JavaScript uses native ES modules without a build.

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
