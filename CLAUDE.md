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
- When a decision is closed, record it in `docs/decisions/` (and here if it
  changes these rules or the stack).

## Git identity

- GitHub account: **Gonzaloalcina** (personal). Never the work account.
- Remote: `origin = git@github-personal:Gonzaloalcina/wp-portfolio.git`
  (`github-personal` is an SSH host alias for the personal key).
- Commit email: the GitHub **noreply** address, set for this folder via
  `includeIf` in the global git config.
- Before committing, check `git config user.email`. If it shows the work
  address, stop and tell Gonzalo.
- The `gh` CLI on this machine is logged in to the **work** account. Do not
  use `gh` for this repo unless Gonzalo switches it to the personal account.

## Parallel sessions and worktrees

localhost serves only `/Users/Shared/wp-portfolio` (the theme is symlinked
from there), so:

- **One browser session at a time in the main folder.** Work that needs
  localhost (styles, templates, performance) runs in `/Users/Shared/wp-portfolio`
  on `main`.
- **Every other parallel session uses a worktree** on its own branch, in
  `/Users/Shared/wp-portfolio-worktrees/<name>` (outside the home folder, so
  Apache could read it): CI, docs, scripts, tests.
- To test a worktree in the browser, repoint the theme symlink to it and point
  it back afterwards; the main-folder session loses its view meanwhile.
- Branches merge into `main` locally, with Gonzalo's approval.
- **Never `git add .`, `git add -A` or `git commit -a`.** Stage files by name:
  another session may have uncommitted work in the same folder.
- In a new worktree, check `git config user.email` before the first commit.

## Stack

- **Theme:** custom PHP theme (`portfolio-theme`) with SCF for dynamic sections
  and components. No page builders.
- **Blog:** Gutenberg with 2–3 custom blocks (React in the editor, PHP render).
- **i18n:** Polylang (free), EN/ES.
- **SEO:** Rank Math (free).
- **Plugin:** custom plugin (`portfolio-core`): REST form with Cloudflare
  Turnstile validated server-side, honeypot and rate limiting; plus automation.
- **Design system as code:** W3C design tokens (JSON) → CSS custom properties;
  a `/design-system` page (noindex); documented in the README. No paid Figma.
- **Quality:** PHPCS, PHPUnit (separate MySQL test database, never the site
  database) and Playwright. CI on GitHub Actions with MySQL as a service.
- **Public demo:** WordPress Playground via `blueprint.json` (verify that SCF
  and Polylang boot).
- **Build tooling:** no bundler. CSS is split per component and compiled
  by `scripts/build-css.mjs` (no dependencies) into one site stylesheet in
  `theme/assets/build/`, committed. JavaScript stays native ES modules;
  `@wordpress/scripts` (v35, Node 20) only for Gutenberg blocks, which live
  in `plugin/blocks/src/` and build into `plugin/blocks/build/`, committed.
- **Local:** XAMPP on macOS, no Docker.

## Repository layout

```
theme/               → symlinked to wp-content/themes/portfolio-theme
plugin/              → symlinked to wp-content/plugins/portfolio-core
tokens/              # design tokens (W3C JSON)
tests/php/           # PHPUnit
tests/e2e/           # Playwright
docs/decisions/      # decision log, one file per phase
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
- Repo path: `/Users/Shared/wp-portfolio` (Apache cannot read the home
  folder). `theme/` is symlinked to `wp-content/themes/portfolio-theme`.
- Permalinks: `/%postname%/`. The local `.htaccess` (outside the repo) holds
  the WordPress rewrite rules and the WOFF2 media type.
- Rank Math registration is skipped locally (no account connected).
- `wp-content/uploads` is writable by Apache (`daemon`) through an ACL. Files
  it creates belong to `daemon`; delete them from WordPress, not the shell.

## Quality checks

Run before proposing a commit; CI runs the same:

- `npm run test:tokens`: design token rules and generated files up to date.
- `npm run test:css`: compiled CSS up to date, every component imported, no
  stray px. After any CSS change run `npm run build:css` and commit the
  sources and `theme/assets/build/` together.
- `npm run check:blocks`: block build up to date, no stray px in block CSS.
  After any change in `plugin/blocks/src/` run `npm run build:blocks` and
  commit the sources and `plugin/blocks/build/` together. Install with
  `npm ci`; adding or upgrading packages needs npm 11
  (`npx npm@11.6.2 install …`), see `docs/decisions/09-blog-blocks.md`.
- `composer run lint`: PHPCS (WordPress Coding Standards, PHP 8.0+
  compatibility). `composer run lint:fix` fixes what it can.
- `nvm exec 24 npm run playground`: runs `blueprint.json` in Playground CLI
  with `./theme` mounted, on http://127.0.0.1:9400 (needs Node 24; the rest
  of the tooling stays on Node 20). Use it to check blueprint changes.

## Design system

- Before writing or changing any CSS, read `docs/design-brief.md` and
  `tokens/tokens.json`.
- Everything derives from `--unit` (0.5rem = 8px at the default root size).
  Dimensions are in rem. No hard-coded pixel values, except
  1px hairlines.
- Plain CSS custom properties, no Sass.
- Components are agnostic: named by what they are plus a design letter
  (`hero-a`, `row-a`), never by page. New letter = different design; numeric
  modifier (`--second`) = small variation. BEM without element chains (a
  nested part becomes its own block). One template in
  `theme/template-parts/components/` and one CSS file in
  `theme/assets/css/components/` per component, imported in
  `theme/assets/css/site.css`. JavaScript hooks use `data-*`. See
  `docs/decisions/03-theme.md`.
- `theme/assets/css/tokens.css` is generated: edit `tokens/tokens.json`, then
  run `npm run build:tokens` and commit both files. Never edit the CSS by hand.
  `npm run test:tokens` checks the unit rules and that the CSS is up to date.
- Do not change token values without Gonzalo's approval. Record any change in
  `docs/decisions/02-design-system.md`.
