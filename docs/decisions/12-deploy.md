# Phase 12 — Deploy

Staging and production hosting, and how code reaches them.

See [README.md](README.md) for how entries are written.

## 2026-10-17 — Hosting: cdmon shared hosting (Junior plan), staging as a subdomain

Production (gonzaloalcina.dev) and staging run on the existing cdmon Junior
shared hosting plan, chosen on budget. The domain's DNS and email already live
on this plan and stay there.

What the plan gives and lacks shapes the deploy:

- **Has:** PHP 8.2–8.5, unlimited MySQL databases (1 GB each) and subdomains,
  password-protected folders, cron jobs, daily automatic backups, Let's Encrypt.
- **Lacks:** SSH/SFTP, WP-CLI, Git deploy and the host's own staging tool
  (Senior plan and above). File access is FTP only, and the FTP server
  (ProFTPD) rejects `AUTH TLS`: no FTPS, so FTP credentials would travel in
  plain text.

Consequences:

- **Staging** is a subdomain with its own WordPress install and database on
  the same plan. Disk usage is checked before creating it (shared 20 GB).
- **No WP-CLI on the server.** Database moves, URL search-replace and
  migrations are done locally with WP-CLI and imported with phpMyAdmin, or
  with a plugin when that is simpler.
- **How code is uploaded** is decided separately, given the lack of FTPS.

Upgrade path when budget allows: cdmon Senior (SSH with keys, rsync, WP-CLI,
Git, staging tool) or another host, with DNS kept at cdmon; the domain's email
must be kept or moved first.

## 2026-10-23 — Redirects from the previous site, in the plugin

The previous gonzaloalcina.dev (Elementor, Spanish only, no `/es/`) had 28
URLs in its sitemaps. Each one answers with a 301 on the new site (Gonzalo):

- **Projects still listed** go to their page (`/proyectos/ramon-esteve-estudio/`
  → `/es/proyectos/ramon-esteve/`); **projects that are not, and the old
  portfolio categories**, go to `/es/proyectos/` (a 301, not a 410: a visitor
  lands on the projects that are there); **blog categories** go to
  `/es/notas/`. `/contacto/` and `/proyectos/` need no rule: WordPress finds
  the Spanish page by its slug and redirects to its `/es/` URL.
- **In the plugin, not in Rank Math:** `plugin/inc/redirects.php`, a
  filterable map (`portfolio_core_legacy_redirects`), versioned and covered by
  `tests/e2e/redirects.spec.mjs`. Rejected: Rank Math's Redirections module,
  editable in wp-admin but outside the repo and its tests.
- **Only on a 404**, before core guesses a permalink, so a real page always
  wins; the path is matched decoded (Relyon's old slug ends in U+FE0F).
- **The Home stays at `/`, now in English** (Gonzalo): no browser-language
  redirect; Spanish visitors switch with EN / ES.
