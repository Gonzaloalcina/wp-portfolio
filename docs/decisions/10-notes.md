# Phase 10 — Notes

What the Notes section is, what goes in it and how often. The templates and
blocks are in [08](08-page-templates.md) and [09](09-blog-blocks.md).

See [README.md](README.md) for how entries are written.

## 2026-10-21 — Notes: decisions and patterns from real work

Notes are not general tutorials and not a news feed. Each note comes from
something done on a live site or on this one: a decision with its trade-offs,
a reusable pattern, or something that broke and what changed. A "how to
register a post type" tutorial competes with thousands of pages and says
nothing about seniority; a decision explained with its cost does.

- **Three formats:** *Decision* (context, options, choice, cost), *Pattern*
  (a reusable implementation with short code) and *Incident* (what failed and
  what changed).
- **Evidence rule:** a note is only published about something that exists.
  Same rule as the AI and automation service. A note about a feature that is
  not built yet waits for the feature.
- **Clusters and links:** one cluster per service (WordPress engineering,
  Website ownership, AI and automation); every note links to one service and,
  when it fits, to one project (Related block, sitemap rule).
- **Confidentiality:** notes draw on this repository first. Nothing about
  employer-specific plugins or internal figures, as everywhere else.

## 2026-10-21 — Fortnightly, English first, every note translated

- **Cadence:** fortnightly, as the sitemap says.
- **Languages:** every note exists in English and Spanish. It is written in
  English, translated in the same cycle and reviewed together, so the two
  versions go out at the same time.

## 2026-10-21 — Six notes to launch with

Two per cluster, each about work that is already done:

1. WordPress engineering: component CSS without a bundler.
2. WordPress engineering: Polylang free does not translate a post type's URL
   base, and how it is solved (decision 08).
3. WordPress engineering: from W3C tokens (JSON) to CSS custom properties.
4. AI and automation: how this site is built with Claude Code (CLAUDE.md,
   worktrees, rules).
5. Website ownership: giving editors fields instead of free layout (SCF).
   Written as a decision, without naming any employer's setup.
6. Website ownership: hreflang and canonical on a bilingual site. Written once
   the i18n phase is built.

Waiting for the feature, so not in the launch set: the dataLayer event plan,
the consent banner, the contact form with Turnstile (the request form is not
connected yet) and testing with PHPUnit (`tests/php` does not exist yet).

## 2026-10-21 — Notes URLs, feed, Blog schema and canonical

Found by rendering the first note and fixed the same day:

- **Notes live under `/notes/<slug>/`**, as the sitemap says. The permalink
  structure is `/notes/%postname%/` (the blueprint sets it; Settings ›
  Permalinks does it on a site). Pages are not affected, and `project` and
  `service` already have `with_front` off, so `/work/` and `/services/` keep
  their URLs. The Spanish base (`/es/notas/<slug>/`) belongs to the i18n phase
  with the other post type bases (decision 08).
- **Feed links:** decision 04 says they are kept on purpose, but the theme did
  not declare `automatic-feed-links`, so no `<link rel="alternate">` was
  printed. Declared now.
- **Notes is a `Blog` as well as a `CollectionPage`** in the JSON-LD, as the
  sitemap says (Rank Math only outputs `CollectionPage`).
- **Canonical of the Notes page:** Rank Math's Polylang support
  (`fix_home_canonical`) runs wherever `is_home()` is true, which includes the
  posts page, and appends the path of Polylang's home URL. In a subfolder
  install `/notes/` became `/notes/portfolio/`; on the Spanish page it would
  become `/es/notas/es/`. A filter at priority 20 in `portfolio-core` removes
  what was appended. Pagination is untouched because the filter only strips
  that trailing path.

