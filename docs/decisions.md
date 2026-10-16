# Decision log

Closed decisions for this project, newest last. Each entry says what was
decided and why. If a decision is reversed, add a new entry instead of editing
the old one.

## 2026-10-16 — Repository name: `wp-portfolio`

The repo is named `wp-portfolio` so the stack is obvious from the name.

## 2026-10-16 — Theme slug: `portfolio-theme`

The custom theme lives in `theme/` and is installed as `portfolio-theme`.

## 2026-10-16 — Plugin slug: `portfolio-core`

The custom plugin lives in `plugin/` and is installed as `portfolio-core`. It
holds site functionality that must survive a theme change (form endpoint,
automation).

## 2026-10-16 — Private repo first

The GitHub repo starts private and will be made public once the base is solid.

## 2026-10-16 — Personal GitHub identity via SSH alias

The remote uses the SSH host alias `github-personal`
(`git@github-personal:Gonzaloalcina/wp-portfolio.git`), so pushes always use the
personal key and account (`Gonzaloalcina`), never the work one.

## 2026-10-16 — Commit email: GitHub noreply address

Commits use the GitHub noreply address, set for this folder via `includeIf` in
the global git config. It keeps the personal email out of a public history and
separates personal work from work commits.

## 2026-10-16 — Repo layout: code only, symlinked into WordPress

The repo contains only project code (`theme/`, `plugin/`, `tokens/`, `tests/`,
`docs/`, CI and the Playground blueprint). WordPress core, uploads and the
database stay outside the repo; `theme/` and `plugin/` are symlinked into
`wp-content`. Empty folders are not committed; they appear when they get
content.

## 2026-10-16 — Local WordPress install

- WordPress 7.1.3, **English (`en_US`) as the main language**; Spanish will be
  added with Polylang.
- Installed in XAMPP at `htdocs/portfolio` (http://localhost/portfolio).
- Dedicated database `wp_portfolio` with its own MySQL user (access to that
  database only) and table prefix `pf_`.
- `WP_ENVIRONMENT_TYPE` set to `local`; debug log on, display off.
- Core installed from the official zip (SHA-1 checked against wordpress.org)
  and verified with `wp core verify-checksums`. `wp core download` was not used
  because its PHP extractor truncates long file paths in WordPress 7.x.
