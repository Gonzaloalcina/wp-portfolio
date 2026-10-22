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
4. AI and automation: how this site is run from Claude (chat in a project,
   Cowork and Code, and what it cost). Replaced the earlier "built with Claude
   Code" idea on 2026-10-22; see the entry below.
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

## 2026-10-22 — Note 4 is "A WordPress site run from Claude"

Replaces the planned "built with Claude Code" note with one that covers the
whole method, since the code side alone was only a third of it.

- **What it says:** WordPress is the CMS for the editors; almost all the work
  went through Claude, split into three kinds of session (chat in a project for
  strategy and texts, Cowork for content and translations, Code for the
  repository). Three doors a model can use without a plugin: admin forms, the
  REST API and WP-CLI. Closed decisions live in `docs/decisions/`, not in the
  chat.
- **Wording:** Gonzalo and Claude both entered wp-admin now and then, so the
  note does not claim "never opened WordPress".
- **Technical detail it keeps:** SCF discards a whole save, with no error,
  when one field is over its `maxlength`; Spanish runs longer, so texts are
  measured before saving.
- **Production:** Novamira (MCP plugin) and ModularDS will be the way in once
  the site is live, for this site and for several. Stated as the plan, not a
  result, until it is done on this site. Nothing about any employer's setup.
- **Evidence rule:** it speaks about this one site. What is not done yet
  (production access, several sites) is worded as a plan. It links to the AI
  and automation service through the Related block. Update the note when the
  production setup exists.

## 2026-10-22 — The Notes page ends with the Contact section

`home.php` now calls `portfolio_theme_contact_section()` after the
pagination, as About, Work and the project page do. The Notes intro is set in
both languages. Single notes end with the link back to all notes, without the
Contact section, as before.
