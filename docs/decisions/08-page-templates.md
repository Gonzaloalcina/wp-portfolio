# Phase 08 — Page templates

Templates for every phase-1 page except the Home, at template level:
structure, components and layout, with Lorem where content is missing.

See [README.md](README.md) for how entries are written.

## 2026-10-18 — portfolio-core starts with the content types

Projects and services are content: they must survive a theme change, so
their post types live in the `portfolio-core` plugin (`plugin/`), not in the
theme. The plugin starts with that and nothing else; the contact form
endpoint will join it later.

- `inc/content-types.php`: `project` and `service`, classic editor
  (`show_in_rest` off, like pages), no archives.
- `inc/fields.php`: SCF field groups in code, named after the data hub
  columns. A project's status is its post status, not a field.
- PHPCS covers `plugin/` with its own text domain (`portfolio-core`) and
  prefix (`portfolio_core`).
- The theme works without the plugin: the helpers return nothing and the
  sections that use them hide.
- Locally, `plugin/` must be symlinked to
  `wp-content/plugins/portfolio-core` and activated after the merge.

## 2026-10-18 — Work: a `project` post type with a featured flag

- Single URL `/work/<slug>/`. Only a **featured** project whose client may be
  named has a page (the case template, `single-project.php`); any other
  project URL answers 404. Those projects are rows in the hub, linked to the
  live site.
- Shown only when **Client OK to name** is Yes or Not needed, in the hub, in
  service proof and anywhere else that lists projects.
- **Link** field: the live site, or a page on this site. This site's entry
  links to `/how-this-site-is-built/`, so there is no `/work/wp-portfolio/`.
- Order: menu order (custom-theme work above page-builder work), then date.
- The Home still reads Selected work from its own fields; the Home session
  switches it to the post type later.

## 2026-10-18 — Services: a `service` post type

Chosen over child pages with fields. Single URL `/services/<slug>/`.
Fields: the problem it solves (one line, used in lists and as the intro),
what it includes (rows) and proof (a relationship to projects and notes:
every service links to its proof). AI and automation stays a draft until it
has evidence, and drafts are not listed.

## 2026-10-18 — Hubs are pages; post type URL bases translated in the i18n phase

`/work/` and `/services/` are pages with a template that query the post
types, not post type archives. Polylang free translates page slugs
(`/es/trabajo/`) but not the base of a post type's URL (that is Polylang
Pro), so singles would be `/es/work/founderz/` instead of
`/es/trabajo/founderz/`.

The plan, for the i18n phase: a small filter in `portfolio-core` (rewrite
rules per language, `post_type_link` and `pll_translation_url`), covered by an
e2e test of the Spanish URLs, since it depends on how Polylang builds its
rules. Switching plugin was not needed: as far as known, TranslatePress and
WPML translate slugs only in their paid versions (not verified).

## 2026-10-18 — Templates chosen per page, not by slug

Custom page templates (`Template Name:`) in `theme/templates/`, assigned in
the page editor: About, Work, Services, Resume, Contact. Polylang gives the
Spanish pages other slugs (`/es/sobre-mi/`), so `page-{slug}.php` would not
match them.

Templates WordPress picks by itself:

| File | Page |
|---|---|
| `page.php` | Text pages: How this site is built, privacy, cookies, legal notice. H1 with the update date |
| `home.php` | Notes, the posts page (`/notes/` set in Settings › Reading). Not `inc/home.php`, which holds the Home fields |
| `single.php` | A note |
| `single-project.php` | A case study |
| `single-service.php` | A service |
| `404.php` | A helpful dead end: one line and the main sections |

Layout: every template uses the wide layout (`site--second`), like the Home,
so the header and the menu keep the same width on every page. Text (H1,
intro, prose, facts, forms) keeps the text measure, aligned to the start;
lists use the full width. *Changed 2026-10-18: see below.*

## 2026-10-18 — One source for contact and profile links

Pages end with the same Contact section as the Home, built from the Home's
fields (contact text, button label, profile links), so each lives in one
place and the button label is identical everywhere. With a Home field empty,
that part hides. A site-wide profile setting may replace this when the
footer gets its links.

## 2026-10-18 — New components

- `title-a`: the H1 of a text page, with an optional meta line (a date) and
  an intro. `hero-a` stays the H1 for pages that lead to an action.
- `prose-a`: long text from the editor on the text measure. Element
  selectors inside, since the editor adds no classes. Quotes in the heading
  serif at `lg` on every screen (short, unlike testimonials; at `base`
  the serif reads smaller than the text), set in a little, with no side line.
- `facts-a`: label and value pairs on 1px lines (client, year, role…), on
  the first two columns of a `list-a`.
- `form-a` and `field-a`: fields, a submit `button-a` (BEM mix
  `form-a__submit`) and a note. Control borders use the muted grey, not the
  hairline colour: form borders need 3:1 contrast (WCAG 1.4.11).
- `pagination-a`: newer | page x of y | older.

No existing component changed its args or markup. Print styles
(`assets/css/print.css`) hide the site chrome and force dark ink on white
through system colours, so the Resume prints correctly even in dark mode.

## 2026-10-18 — Contact form: static placeholder

The Contact page renders `form-a` with name, email, company (optional) and
message, no action and a disabled submit, plus the note "The form is not
connected yet." The endpoint, Turnstile, honeypot and rate limiting come with
the plugin's form.

## 2026-10-18 — Playground: plugin in the blueprint, optional local seed

- The blueprint installs `portfolio-core` from the repository
  (`git:directory`, `plugin/`), like the theme. `scripts/playground.mjs`
  swaps it for a mount of `./plugin`.
- `SEED=1` adds `scripts/playground-seed.php` as a last step: a Home with
  its contact fields, every phase-1 page with its template, Lorem projects
  (one featured, one without consent to check it stays hidden), services
  (one draft) and twelve notes, so the index paginates. Lorem only; the
  public blueprint does not include it.
- Checked with `SEED=1 PORT=9401 nvm exec 24 npm run playground`: every
  page answers with the expected status and one H1, no PHP notices; light
  and dark; no horizontal scroll at 360px.

## 2026-10-18 — One width for every page

Text pages started on the text measure, and the header and menu narrowed
with them from page to page. Every template, `/design-system/` and the
`index.php` fallback now use `site--second`. Reading width does not change:
`title-a`, `prose-a`, `facts-a` and `form-a` keep the text measure. A wide
block inside `prose-a` (`alignwide`) starts with the text column and runs to
the site's full width.

## 2026-10-18 — Comments off site-wide

`plugin/inc/comments.php` turns comments off everywhere: no support on any
post type, closed and empty on the front end, no comment REST endpoints, no
Comments or Discussion screens (direct visits go to the dashboard), no admin
bar bubble, no comment feed links. Comment queries return nothing and counts
are zero, so the dashboard hides its comment sections on its own. A portfolio
gains little from comments and pays in spam, moderation and personal data.

## 2026-10-18 — Site settings: identity and profile links in one place

An SCF options page in portfolio-core ("Site settings") holds data the whole
site uses: name, role and short bio (English and Spanish fields, because
Polylang free does not translate options pages), location, and the profile
links (label, URL, rel="me"). This replaces the Home's profile links field
from "One source for contact and profile links": `portfolio_theme_profile_links()`
keeps its name and shape and now reads the settings, and the Home's Contact
section uses it too. The Home keeps its contact text and button label.

The settings complete Rank Math's site Person (Titles & Meta › Local SEO set
to Person; the blueprint sets it) with `jobTitle`, `description` and the
rel="me" profiles as `sameAs`, through the `rank_math/json_ld` filter; post
authors are left alone.

Kept discreet on purpose: no availability, no "hire me" fields, and no CV
here (the Resume page is a transcription of the CV, without a downloadable
PDF). Secrets and per-environment IDs (Turnstile, GTM) stay out of the
database.
