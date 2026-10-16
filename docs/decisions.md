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

## 2026-10-16 — Repo lives in `/Users/Shared/wp-portfolio`

Apache (XAMPP) runs as `daemon`, which cannot read the user's home folder or
the macOS-protected Desktop, so a symlink from `wp-content` into the Desktop
would not work. Running Apache as the user was rejected because it would also
affect other local sites. `/Users/Shared` is readable by `daemon` and isolates
this project. The git `includeIf` rule points to the new path.

## 2026-10-16 — Uploads writable through an ACL, not ownership changes

Apache gets write access to `wp-content/uploads` only, through an inherited
macOS ACL for `daemon`. No `sudo`, no global changes. Files Apache creates
there belong to `daemon`; delete them from WordPress, not from the shell.

## 2026-10-16 — Theme header: PHP 8.0, WordPress 7.1, GPL-2.0-or-later

- `Requires PHP: 8.0` (the local PHP). **Provisional** until the hosting PHP
  version is known.
- `Requires at least: 7.1` (the version the theme is built on).
- License `GPL-2.0-or-later`, as WordPress themes require.
