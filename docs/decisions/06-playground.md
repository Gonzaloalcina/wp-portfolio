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

## 2026-10-21 — Polylang in the blueprint: languages, post types and menu

Polylang (free) joins Secure Custom Fields and Rank Math in `plugins`, and a
new `runPHP` step, before the content one, sets up the languages. Reading
Polylang 3.9.1 and running the steps showed four things that are not obvious:

- **The languages are created through Polylang's model.** Polylang builds its
  `$polylang` object, and with it the `pll_*()` functions, only once a language
  exists, so the first step cannot call them. It creates a `PLL_Model` with its
  own `Options` object, calls `add_language()` and saves the options (the first
  language becomes the default one in them). Later steps run with Polylang
  fully loaded.
- **English first, then Spanish.** The first language becomes the default.
  Polylang's defaults give what the site needs: no prefix for the default
  language (`hide_default`) and `/es/` for Spanish (`force_lang` = directory),
  so they are not set.
- **Projects and services are switched on for translation from code.**
  Polylang only translates the custom post types it is told about, and by
  default that is a database setting (`post_types`). portfolio-core adds
  `project` and `service` with the `pll_get_post_types` filter, so a fresh
  install needs no setting and the blueprint stores none; Languages › Settings
  shows them checked and locked.
- **The menu of each language is in Polylang's option.** Once a language
  exists the front end ignores the `nav_menu_locations` theme mod and reads
  `polylang['nav_menus'][theme][location][lang]`, so the `Main` menu would
  vanish unless it is registered there. The content step does it for English.
  Polylang's options are written through `PLL()->options`: a plain
  `update_option( 'polylang' )` is discarded.

The page the blueprint seeds, Design system, is given the English language.
The public blueprint creates Spanish but no Spanish content: Design system is
English only (see 11), so the `ES` link of the switcher leads to the Spanish
home. The Spanish pairs live in the optional seed, which now builds both
languages (see 11).

## 2026-10-21 — The public blueprint stays without Spanish content

Considered and left as it is: the public blueprint creates the two languages
and nothing in Spanish.

- It seeds one page, Design system, which is English only by design (see 11),
  and the pages behind its menu do not exist yet, so a Spanish half would be as
  empty as the English one.
- The complete content in both languages is the optional seed (`SEED=1`), and
  it is placeholder text. The e2e spec for i18n runs against it.
- What a visitor sees today: the `ES` link of the switcher goes to `/es/`, the
  Spanish home with the site name as heading and the footer in Spanish, and no
  menu (there is no `Main ES` without pages to point to).

When the real content replaces the placeholder, both languages go into the
blueprint together.
