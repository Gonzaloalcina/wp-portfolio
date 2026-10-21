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
