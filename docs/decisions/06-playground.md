# Phase 06 — Playground demo

Public demo of the theme through WordPress Playground.

See [README.md](README.md) for how entries are written.

## 2026-10-17 — Blueprint: theme from `main`, admin login, design system first

`blueprint.json` (Playground blueprint v1):

- **Versions:** PHP 8.0 (the theme's minimum), latest WordPress.
- **Plugins:** Secure Custom Fields and Rank Math from wordpress.org. Polylang
  is added with the i18n phase.
- **Theme:** installed from this repository with `git:directory`, branch
  `main`, folder `theme/`, so the demo always shows the latest merged work.
  Pinning a commit was rejected: it would need a blueprint update per release.
- **Content** (one `runPHP` step): pretty permalinks, Rank Math registration
  skipped (it prints no tags otherwise), the "Design system" page with
  `noindex` and the demo card icon.
- **Entry:** logged in as admin, landing on `/design-system/`. Each visitor
  gets a private, throwaway copy, so they can open the editor and try the
  icon picker without risk.

`git:directory` only works for public repositories: the demo link works once
the repo is public.

## 2026-10-17 — Local runs with Playground CLI: `npm run playground`

`scripts/playground.mjs` runs the same blueprint with the official Playground
CLI, but swaps the GitHub install for a mount of `./theme`, so unpushed
changes show up live. It passes the PHP version explicitly because the CLI
ignores `preferredVersions` (it defaults to PHP 8.3).

The CLI needs Node 24+ (Node 26 fails: a native dependency has no prebuilt
binary for it). The project's tools stay on Node 20; run it with
`nvm exec 24 npm run playground`. The CLI version is pinned in the script.

## 2026-10-18 — Main menu in the blueprint

The `runPHP` step creates the "Main" menu (About, Work, Services, Notes,
Contact) and assigns it to the `primary` location, so the header nav shows in
the demo. The URLs are built with `home_url()` at boot, because every
Playground instance has its own address. The pages behind them do not exist
yet, so they 404 in the demo too.

The Home is not seeded: with empty Home fields it shows the site name as H1
and Latest notes, and every other section hides itself. Checked with
`npm run playground` (this run found and fixed a Contact heading that
rendered with nothing under it).
