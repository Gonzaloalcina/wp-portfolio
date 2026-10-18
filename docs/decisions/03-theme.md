# Phase 03 — Theme

Theme structure, requirements and templates.

See [README.md](README.md) for how entries are written.

## 2026-10-16 — Theme header: PHP 8.0, WordPress 7.1, GPL-2.0-or-later

- `Requires PHP: 8.0` (the local PHP). **Provisional** until the hosting PHP
  version is known.
- `Requires at least: 7.1` (the version the theme is built on).
- License `GPL-2.0-or-later`, as WordPress themes require.

## 2026-10-16 — Theme toggle: light/dark, system by default, in the header

- **Two states.** The site follows the system until the visitor clicks; each
  click switches to the other scheme. A three-state cycle (System, Light,
  Dark) was rejected: two of its states always look the same.
- **Back to system without a third state:** a choice that matches the system
  scheme is not stored, so the site follows the system again.
- **Placement and style:** right side of the header, small muted text
  (`Theme: Light` / `Theme: Dark`, the scheme on screen), underlined on hover
  and focus like a link. No icons. *Changed 2026-10-18: see below.*
- **No flash:** a short inline script at the top of `<head>` applies the stored
  choice (`localStorage` key `portfolio-theme`) before first paint.
- **Script:** native ES module (`wp_enqueue_script_module`), no build. The
  button is rendered `hidden` and shown by the module, so without JavaScript
  there is no dead button and the site follows the system.
- `localStorage` is functional storage: list it in the privacy and cookie
  notice when it is written.

## 2026-10-18 — Agnostic components, BEM, numeric modifiers

- **Components are named by what they are plus a design letter**: `hero-a`,
  `section-a`, `list-a`, `row-a`, `actions-a`, `button-a`, `link-row-a`.
  Never by the page that uses them (`home-hero` would be duplicated the day
  About needs the same hero). A page template only chooses and combines
  components.
- **Letter = a different design** (other markup or layout): `hero-b` is a new
  component. **Numeric modifier = a small variation of the same design**:
  `--second`, `--third`, never descriptive names.
- **BEM without element chains.** When an element has parts of its own, it
  becomes a block nested inside the parent's element: `list-a` >
  `list-a__item` > `row-a` > `row-a__title`, never
  `list-a__item__title`. A parent places a child with a BEM mix
  (`class="actions-a hero-a__actions"`): the child's block gives the look, the
  parent's element gives the position.
- **One template and one stylesheet per component**:
  `template-parts/components/<name>.php` (data in through `$args`, rendered
  with `portfolio_theme_component()`) and
  `assets/css/components/<name>.css`, compiled into the site stylesheet
  (see `01-foundation.md`, 2026-10-18).
- **Page width belongs to the layout block, not to the page CSS.** `site`
  (`site__header`, `site__main`, `site__footer`) uses the text measure;
  `site--second` uses the wide one. The template sets it with
  `get_header( null, array( 'variant' => 'second' ) )`. There is no page
  stylesheet for the Home.
- **Values that are data, not variants**, go in `data-*` attributes:
  `<svg class="icon" data-icon="gauge">`. JavaScript hooks use `data-*` too,
  never classes.
- **Kept as they are:** WordPress's own classes (`alignwide`,
  `screen-reader-text`, body classes, `wp-block-*`), `acf-*` in the admin,
  and element selectors for content written in the editor.

## 2026-10-18 — Header navigation (nav-a)

- **Items:** About · Work · Services · Notes · Contact. Contact is a plain
  link like the rest: the only primary buttons are the page's own (Home brief
  Q8, closed).
- **A WordPress menu** (`primary` location, Appearance › Menus), so it is
  edited without a deploy and Polylang assigns one per language. Rendered by
  the `nav-a` component; filters swap core's `menu-item…` classes and ids for
  BEM classes on that menu only. The current page is styled from the
  `aria-current="page"` core adds. No menu assigned: no nav.
- **Narrow screens (< 48rem): a "Menu" disclosure.** A text button like the
  theme toggle (`aria-expanded`, `aria-controls`) opens the list as a
  dropdown under the header, one 48px row per link. Escape closes it and
  returns focus to the button; a click outside or widening the screen closes
  it too. Native ES module (`assets/js/nav-a.js`), no build.
- **Fade layer while open:** paper at 85% over the page (`color-mix`, no new
  token), under the header. It fades the page instead of darkening it, so it
  works the same in light and dark; tapping it closes the menu. No scroll lock
  or focus trap: it is a disclosure, not a modal.
- **Not sticky** (2026-10-18): the Home brief rules out sticky bars, a fixed
  header costs ~14% of a 640px screen, and pages are short. To revisit for
  long notes, as a header that reappears on scroll up.
- **Without JavaScript** the button stays hidden and the links wrap on their
  own row under the title: nothing is unreachable and there is no dead
  button.
- **From 48rem:** inline links between the title and the theme toggle. The
  header is a grid (title | nav | toggle) with its own stacking context, so
  the dropdown stays above the page.

## 2026-10-18 — Theme toggle: sun and moon icons

- The text toggle becomes an icon button: **sun** while the light scheme is
  on screen, **moon** while the dark one is (it shows the current state, as
  the text did). Lucide `sun` and `moon`, added to the icon library.
- Muted, full ink on hover and focus; 24px (`icon.size`), within the 24px
  minimum target.
- The accessible name stays "Theme: Light" / "Theme: Dark" in visually
  hidden text, still announced through `aria-live` when it changes.
- The module sets `data-scheme` on the button and CSS shows the matching
  icon; the button stays hidden without JavaScript, as before.

## 2026-10-18 — Home: Also built and Testimonials

- **Also built:** sites built for agencies and small clients, as an H3 list
  inside Selected work (lighter work than the featured cases, so not a
  section of its own). Name, year · type, linked to the live site. A client
  is named only with consent. The SEO value is proof, not authority: links
  out do not pass authority to this site; a credit link from the client's
  site would.
- **Testimonials:** a section between Services and Latest notes, so the
  social proof sits right before the notes and the contact call. Up to three,
  real and with permission, the name linked to proof (a LinkedIn
  recommendation). New components `quote-list-a` and `quote-a`: the quote in
  the heading serif, `base` when narrow and `lg` from 48rem (at `lg` on a
  phone each quote ran to seven lines), who said it in the title column, on the same
  three columns as `list-a`.
- **No review schema:** Google does not show stars for reviews a site
  publishes about itself. **No Google Business Profile:** it requires
  in-person service and serves local search, which is not this audience.
- **section-a takes content components** (`content`: name, args, optional
  H3 title) instead of a fixed `list-a`, so one section can hold a list, a
  second titled list or quotes. An entry with no items is skipped.
