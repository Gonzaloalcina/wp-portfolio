# Phase 01 — Foundation

Repository, git identity, layout and local environment.

See [README.md](README.md) for how entries are written.

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

## 2026-10-16 — Third-party plugins: SCF and Rank Math

- **Secure Custom Fields** (WordPress.org) for dynamic sections and
  components.
- **Rank Math SEO** (free) for titles, meta robots and sitemaps.
- Installed from wordpress.org with WP-CLI and checksum-verified. They live in
  the WordPress install, not in the repo.
- Rank Math only outputs front-end tags after an account is connected or the
  registration is skipped. Locally the registration is skipped (no Rank Math
  account); connecting one is Gonzalo's call.

## 2026-10-16 — No bundler; `@wordpress/scripts` for blocks

CSS is plain (native nesting, `calc()`, `clamp()`) and vanilla JS uses native
ES modules through WordPress script modules (`wp_register_script_module`), so
neither needs a build. Gutenberg blocks will use `@wordpress/scripts`, the
official tool, which bundles React/JSX and generates the `index.asset.php`
dependency file WordPress needs. Vite was rejected: it would be a second build
tool and its main benefit (HMR for large JS apps) does not apply here.
