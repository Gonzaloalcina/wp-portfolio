# wp-portfolio

> **Work in progress.** The repository base is in place; theme, plugin and
> tooling are not built yet.

Source code for [gonzaloalcina.dev](https://gonzaloalcina.dev), the portfolio of
Gonzalo Alcina, a WordPress developer. The repo is part of the portfolio: it
shows how the site is built, tested and documented, not just the result.

## Planned stack

- Custom PHP theme with SCF for dynamic sections. No page builders.
- Gutenberg blog with a few custom blocks (React in the editor, PHP render).
- English and Spanish with Polylang.
- Custom plugin with a REST contact form (server-side Cloudflare Turnstile,
  honeypot, rate limiting).
- Design tokens (W3C JSON) compiled to CSS custom properties, with a living
  `/design-system` page.
- PHPCS, PHPUnit and Playwright, run in GitHub Actions.
- Public demo through WordPress Playground.

## Structure

```
theme/               # WordPress theme (portfolio-theme)
plugin/              # WordPress plugin (portfolio-core)
tokens/              # design tokens
tests/               # PHPUnit and Playwright tests
docs/                # project documentation and decision log (by phase)
.github/workflows/   # CI
blueprint.json       # WordPress Playground blueprint
```

WordPress core, uploads and the database are not part of this repo. `theme/`
and `plugin/` are symlinked into a local WordPress install.

## Decisions

See [docs/decisions/](docs/decisions/README.md), one file per project phase.
