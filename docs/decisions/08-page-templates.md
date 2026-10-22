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

## 2026-10-18 — Resume without a PDF download

The Resume page stays (name, `/resume/`, links from the Home and Contact,
indexable) as a transcription of the CV, with no downloadable PDF. The
`resume_pdf` field and the download button are removed; the hero keeps a
quiet link to Contact.

## 2026-10-20 — Work rows say the role; cases get their own title

- **Project rows show the role after "what I did"** (`portfolio_theme_project_line()`),
  in the Work hub, in service proof and in Selected work. Agency work has to
  say through whom it came and who did the design, and the row is the only
  place most projects appear.
- **"Case title" field** (`project_case_title`): the H1 of a case page, e.g.
  "Ramón Esteve: moving an architecture studio's site from a custom theme to
  Elementor". Lists keep the short name (the post title); empty falls back to
  it.

## 2026-10-20 — Maintenance: in the Services hub and the footer, not the Home

- **"Show on the Home" field** on services (`service_home`, on by default).
  WordPress maintenance switches it off: it is listed in the Services hub and
  linked from the footer, not from the Home or the main menu. A field, like
  the projects' Home placement, so the choice is editorial and visible in the
  editor. A service saved before the field existed has no value and stays on.
- **Footer links menu** (`footer` location, Appearance › Menus), rendered with
  `link-row-a` next to the copyright. It holds pages kept out of the main menu.

## 2026-10-20 — Polylang installed; English and Spanish locally

Polylang (free) is active locally: English is the default language with no
prefix, Spanish lives under `/es/`, browser detection off. Projects and
services are translatable; custom fields are **not** synced between
translations, since each language has its own text. Menus are assigned per
language (main menu in English; footer links in both).

Content is entered with a WP-CLI script kept outside the repo, so the
database stays the source of content. Until the i18n phase:

- Polylang free cannot share slugs, so Spanish projects carry `-es` and post
  type bases stay in English (`/es/work/ramon-esteve-es/`). The rewrite filter
  planned above translates the bases; the slugs are settled with it.
- The Spanish Home, hubs and pages are not created yet, and theme strings are
  not translated.
- The Playground blueprint does not install Polylang yet.

## 2026-10-20 — Breadcrumbs: Rank Math's trail, rendered by breadcrumb-a

- **Rank Math builds the trail and the `BreadcrumbList` schema**, turned on
  from code (`add_theme_support( 'rank-math-breadcrumbs' )`), so every
  environment has it and all schema still comes from one graph.
- **`breadcrumb-a` renders the same items** above the H1: a `nav` named
  "Breadcrumb" with an ordered list, the current page as
  `aria-current="page"`, muted at `sm`, a slash between items that screen
  readers skip. What people see and what search engines and agents read is
  the same list.
- **Only below the first level** (sitemap rule): Home › Work › case,
  Home › Services › service, Home › Notes › note. The hub is the page with the
  Work or Services template, or the posts page, in the current language
  (`inc/breadcrumbs.php`, filter on Rank Math's items). First-level pages,
  and a language whose hub does not exist yet, get no trail and no schema.

## 2026-10-20 — External links open in a new tab

Gonzalo's call: links to another host open in a new tab, with
`rel="noopener"` and `aria-describedby` pointing to one hidden "Opens in a
new tab" note in the footer, so screen readers announce it.
`portfolio_theme_link_attrs()` applies it in every component that prints a
link, and a `the_content` filter does the same for editor content. Internal
links, `mailto:` and `tel:` are left alone.

## 2026-10-20 — Every project has a page; featured marks the case studies

Replaces "Work: a `project` post type with a featured flag" on one point.
With only the featured projects linked inside the site, the Work hub mixed
internal links (case studies) and links out (every other row) in the same
kind of list, so a visitor could not tell where a row would take them.

- **Every project whose client may be named has a page** at `/work/<slug>/`,
  on the same template (`single-project.php`). A project without consent is
  still a 404.
- **Featured now means case study:** the Case studies list of the hub, the
  long text, "Case study" above the title (other pages say "Project").
- **Rows always link inside the site** (hub, Home, service proof, blocks).
  The live site is a fact on the project page ("Site").
- **A Link that is a page on this site replaces the project page:** this
  site's entry links to `/how-this-site-is-built/`, and `/work/this-website/`
  redirects there (301), so the same work has one URL.

## 2026-10-20 — Screen recordings on project pages

Some work is interaction (scroll effects, hovers) and reads better as a few
seconds of video than as code.

- **Short clips, no audio**, 5–20 s, recorded on the live site, trimmed and
  converted with ffmpeg: 1280 px wide, 30 fps, WebM (VP9) with an MP4
  (H.264) fallback, and the first frame as a JPEG poster. Under 3 MB each;
  no GIFs (heavier and worse).
- **In the content**, in a wide figure (`alignwide`) with a one-line caption
  that says what the clip shows, at the end of "What I built" on each
  project page. Media live in the library, not the repo.
- **Where they fit:** short pieces (5–10 s are the most effective) next to
  the plain layout: interactions, a walk through a site, and later code or a
  process done with Claude.
- **`video-autoplay.js`**, loaded only on pages with such a video: a
  `[data-autoplay]` video plays while it is on screen and pauses when it
  leaves. With "reduce motion" nothing plays by itself; the controls stay
  either way, so anyone can pause (WCAG 2.2.2). `preload="none"`, so nothing
  downloads before the visitor gets there.
- Post content may now hold `<source>` (src, type): core's `wp_kses_post`
  allows `<video>` but not its sources.

## 2026-10-20 — About: the entity page, built from existing components

About answers, in order: who this is, how he works with the people around a
live website, where, with what, who says so, other things (the person), contact. No new
component and no new token.

- **H1 is the name** (Site settings), not "About": it is the page about the
  person (`ProfilePage`/Person, SEO session). The nav label stays "About".
  Without a name the page title is the H1, so the page always has one.
- **One source per fact.** Short bio, location and the new **languages**
  field (`site_languages_en`/`_es`) live in Site settings; languages also
  feed `knowsLanguage` in the Person schema, without their level.
  "Where I have done it" is the **Resume page's first section**
  (experience). Testimonials are the **Home's**, at most two
  (`portfolio_theme_home_quotes()` now reads the front page and takes a
  limit). Only what no other page has is an About field (`inc/about.php`):
  `about_currently`, `about_how` (area | what I do | with whom),
  `about_tools`, `about_outside`.
- **Sections:** `hero-a` with a larger portrait, `facts-a`, `prose-a` (page
  text), then `section-a` with `list-a`, text or `quote-list-a`. A section
  without content does not render: `section-a` and the lists already drop
  out when empty, and facts drop row by row.
- **`hero-a--second`** (`variant => 'second'`): the portrait at 20 units
  narrow and 24 from 48rem (12 and 16 on other pages), a larger image size
  for sharp rendering. Still a square with a 1px line, eager (it is the
  LCP), `alt` = the name.
- **`section-a` text can hold paragraphs:** a blank line starts a new one.
  Tools and AI needs two; other sections are unchanged.
- **Not in About:** no availability or job-seeking wording, no employer
  plugin names or internal figures, no client counts. The copy is reviewed
  by Gonzalo before it leaves the local database.
- An end-to-end check (one H1, empty sections absent) waits for Playwright,
  which the repo does not have yet.

## 2026-10-20 — The closing Contact is set apart by a line

Contact closes every page that ends with it, but it looked like one more
section of the page. Gonzalo chose the most minimal of three options (line
and space; line and a larger title; line and the square marker):
`section-a--second` adds a full-width 1px line and more space above it, like
the footer's. Used by `portfolio_theme_contact_section()` and the Home's
Contact. No new token.

Rule: **never two lines with nothing between them.** When the block before
already ends on a line (`list-a`, `quote-list-a`, `facts-a`), Contact draws
no line of its own and that one is the divider (`:has()` in `section-a.css`).

## 2026-10-20 — Theme strings translated in `theme/languages/`; About in Spanish

The first Spanish page is About (`/es/sobre-mi/`), so the theme now ships
its translations. Updates "Polylang installed" on theme strings.

- **`portfolio-theme.pot`** is generated with `wp i18n make-pot theme
  theme/languages/portfolio-theme.pot --domain=portfolio-theme
  --exclude=assets`. **`es_ES.po`** is the source to edit; `es_ES.mo` and
  `es_ES.l10n.php` are built from it with `wp i18n make-mo` and
  `wp i18n make-php`, and committed together (the PHP file is the faster
  format WordPress reads first; it is left out of PHPCS as generated).
- Inside a theme the files are named by locale only (`es_ES.*`), not
  `portfolio-theme-es_ES.*`; `load_theme_textdomain()` points WordPress at
  the folder.
- **Translated so far:** the strings a Spanish About shows (header, footer,
  theme toggle, About sections, Contact). The rest follow page by page.
- **Location is translatable** (`site_location_es`), like bio and languages.
- **"Full resume" links to the Resume page of the current language.** Other
  fixed paths (Contact, How this site is built) stay English until those
  pages exist in Spanish.
- Section IDs stay English (`#how-i-work`) in both languages: they are
  anchors, not copy.
- Spanish content (About, a CV page with Experience, a minimal Spanish Home
  for testimonials and Contact) lives in the local database only, like the
  English.

## 2026-10-21 — Services: choose by situation, request form in the hub

The hub lists each service with the situation it fits and, where it exists,
its price, then explains how it works and ends in a request form instead of
the generic Contact section. Every service page ends the same way, with its
service preselected, so a lead arrives classified. The reply promise is two
working days. `field-a` gains a `select` type. AI and automation joins the
first launch as a fourth service. Replaces "Services: a `service` post type"
on AI and automation, which no longer waits as a draft.

- **Rows** (`portfolio_theme_services_hub_rows()`): name linked, the new
  `service_situation` line as text (the problem line when empty, so no row
  is blank) and the new `service_price` as meta ("From 80 €/month",
  "Quoted"; empty: no meta). The Home keeps its problem-line rows.
- **How it works**: `services_steps` (title, line), a field group on the
  Services template in `theme/inc/services.php`. No rows: no section.
- **One function for hub and singles**, `portfolio_theme_request_section()`:
  name, email, "What do you need?" (the published services in the current
  language, then "Not sure", after an empty "Choose one" so nothing is sent
  that nobody chose), site URL (optional) and message. The single passes its
  own slug; the hub reads `?service=<slug>`, and a slug that is not a
  published service preselects nothing. The hero button of both points to
  `#request`. Under the button: the reply promise and the "not connected"
  note; then a link to the privacy page (wording to review with the legal
  pages). Still a placeholder: no endpoint, Turnstile, honeypot or rate
  limiting yet.
- **`?service=` needed the post type's query var off** (`query_var` false
  on `service`): WordPress read `?service=<slug>` as "load that service" and
  rendered it in the hub template. Pretty permalinks are unchanged; core
  rewrites them to `post_type=service&name=`.
- **Analytics hooks only** (the push is the dataLayer piece, after consent):
  `data-cta-location` and `data-service` on the hero button
  (`services_hero`; `service_hero` on a service page) and on each row
  (`services_row`); on the form `data-form-id="services_request"`,
  `data-cta-location="services_form"` and `data-page-type` (`services` or
  `service`). The `generate_lead` service is the select's value at submit.
  Slugs differ per language (`mantenimiento-wordpress`): the dataLayer piece
  decides whether to map them to one value.
- **Component args, no new markup by default** (Gonzalo, 2026-10-21): a
  generic `data` arg (`portfolio_theme_data_attrs()`) on `row-a`'s link,
  `actions-a`'s button and `form-a`; `section-a` takes a `form-a` as content
  (an entry with `fields`).
- **`field-a` select**: `type => 'select'` with `options` (value => label)
  and `selected`. The native look is dropped (some browsers ignore borders
  on it) and the chevron is two 1px lines on a `field-a__select` wrapper, in
  the text colour; same muted border and 48px height as the other controls.
  Shown on `/design-system/`.
- **Maintenance stays off the Home** (`service_home` off, Gonzalo
  2026-10-21); the hub lists it.
- Playground seed: four published services, only maintenance with a price
  and off the Home, one with a situation line; steps and the privacy page
  set. The draft service it had is gone, since AI is now published.

## 2026-10-22 — Resume: the whole CV, a one-line summary for About

The Resume carries the full CV in four `resume_sections`, in this order:
Experience, Before web development, Skills, Education and languages. Same
template and components (`hero-a`, `prose-a`, one `section-a` + `list-a` per
section); no new component and no new token.

- **Experience stays the first section:** About's "Where I have done it"
  reads `sections[0]`. That is why the years before web development are a
  section of their own: About shows three rows, not four.
- **Rows carry a paragraph:** the row `text` field is now a textarea with no
  length limit (it was a 140-character line). A role reads as one paragraph
  of about 85 words; it holds at 360, 768 and 1280 without layout changes.
- **Optional `summary` per row** (140 characters): About shows it when
  present, the Resume's `text` otherwise. The experience is still entered
  once; only its length differs per page.
- **Skills are plain lists:** title = category, text = the tools, no years
  and no self-assessed levels.
- **The hero has no Contact link;** the page ends with the closing Contact
  like the other pages, so its list's bottom line is the divider.
- **No phone, email address or work mode** on the page; Contact is the way
  in. No employer-specific plugin names or internal figures.
- **Print: two A4 pages.** The print styles now hide the site header and the
  closing Contact, set the root size to 87.5% (everything is in rem, so it
  all scales), and win over the dark scheme: the colour override also
  matches `:root:not([data-theme="light"])`, which before left light ink on
  white paper when the system was dark.
- Playground seed: the four sections, with a paragraph and a summary on the
  experience rows and no years on the skills.

## 2026-10-22 — Home: no portrait

The Home hero is text only: headline, intro, the Contact button and the Work
and About links. The `home_portrait` field and the empty preview frame are
removed; the portrait lives on About.
