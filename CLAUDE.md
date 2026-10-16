# CLAUDE.md — wp-portfolio

Project instructions for Claude Code. Read this before working in the repo.

## Context

Personal portfolio of Gonzalo Alcina (gonzaloalcina.dev), built on WordPress.
The public GitHub repo is itself part of the portfolio: it shows how Gonzalo
works, not just the final site.

- **Audience:** companies outside Spain. English first, Spanish second.
- **What it must show:** a senior WordPress developer working with stakeholders
  on a live site — campaigns, CRO, dataLayer and an editorial team.

## Working rules

- Talk to Gonzalo in **Spanish**: concise and didactic. Code, commits and repo
  documentation in **English**.
- **Do not invent.** If something is unclear or a fact is missing, ask.
- Before installing or running anything big, explain what it will do and why.
- **Confidentiality:** no names of employer-specific plugins and no internal
  figures from Gonzalo's employer anywhere in the repo.
- **Never push or publish without Gonzalo's explicit permission.** Local commits
  only when he approves them.
- When a decision is closed, record it in `docs/decisions.md` (and here if it
  changes these rules or the stack).

## Git identity

- GitHub account: **Gonzaloalcina** (personal). Never the work account.
- Remote: `origin = git@github-personal:Gonzaloalcina/wp-portfolio.git`
  (`github-personal` is an SSH host alias for the personal key).
- Commit email: the GitHub **noreply** address, set for this folder via
  `includeIf` in the global git config.
- Before committing, check `git config user.email`. If it shows the work
  address, stop and tell Gonzalo.

## Stack

- **Theme:** custom PHP theme (`portfolio-theme`) with SCF for dynamic sections
  and components. No page builders.
- **Blog:** Gutenberg with 2–3 custom blocks (React in the editor, PHP render).
- **i18n:** Polylang (free), EN/ES.
- **Plugin:** custom plugin (`portfolio-core`): REST form with Cloudflare
  Turnstile validated server-side, honeypot and rate limiting; plus automation.
- **Design system as code:** W3C design tokens (JSON) → CSS custom properties;
  a `/design-system` page (noindex); documented in the README. No paid Figma.
- **Quality:** PHPCS, PHPUnit (separate MySQL test database, never the site
  database) and Playwright. CI on GitHub Actions with MySQL as a service.
- **Public demo:** WordPress Playground via `blueprint.json` (verify that SCF
  and Polylang boot).
- **Local:** XAMPP on macOS, no Docker.

## Repository layout

```
theme/               → symlinked to wp-content/themes/portfolio-theme
plugin/              → symlinked to wp-content/plugins/portfolio-core
tokens/              # design tokens (W3C JSON)
tests/php/           # PHPUnit
tests/e2e/           # Playwright
docs/decisions.md    # decision log
.github/workflows/   # CI
blueprint.json       # WordPress Playground
```

WordPress core, uploads and the database live outside the repo.

## Local environment

- WordPress install: `/Applications/XAMPP/xamppfiles/htdocs/portfolio`
  → http://localhost/portfolio
- Database `wp_portfolio`, dedicated MySQL user, table prefix `pf_`.
  `WP_ENVIRONMENT_TYPE` is `local`, debug log on.
- Apache serves PHP 8.0 (XAMPP). Homebrew's `wp` runs on a different PHP; run
  WP-CLI with XAMPP's PHP so both match:
  `/Applications/XAMPP/xamppfiles/bin/php -d memory_limit=512M "$(brew --prefix wp-cli)/bin/wp" …`
- Local admin credentials are kept outside the repo. Never commit them.
