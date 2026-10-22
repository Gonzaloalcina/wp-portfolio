# Phase 07 — Information architecture

Sitemap, URLs, page purposes and linking rules. The current sitemap is
[`../sitemap.md`](../sitemap.md).

See [README.md](README.md) for how entries are written.

## 2026-10-17 — Sitemap v1: three services, Work as a hub

Three service pages (WordPress development, Website ownership, AI and
automation in phase 2) instead of a long service silo: they match the work
Gonzalo does and avoid competing with agencies. Work is a hub with two full
cases and a row list of projects fed by a `project` post type.

## 2026-10-17 — Section named "Services"

"Services" rather than "Expertise", because the section covers B2B and
freelance work.

## 2026-10-17 — Resume page

`/resume/` is a printable page.

## 2026-10-17 — Legal pages

Privacy, cookies and legal notice. Gonzalo reviews them; the legal notice
probably applies because of his freelance activity.

## 2026-10-19 — Sitemap v2: maintenance service and two more cases

A fourth service page, WordPress maintenance, because it is in steady demand
and it is the page Gonzalo can send when someone asks about it. It is indexed
and linked from the Services hub and the footer, not from the main navigation.
It has two public plans (80 and 140 euros a month, without VAT, per site) and
quotes anything beyond them. Ramón Esteve and Escuela Victoria become full
cases next to Founderz, because they show decisions and not only a build.

## 2026-10-19 — Work hub: other sites, honest by build type

The hub lists the sites built for agencies (Xufa, Doowebs, Nokeon), for a
design studio and directly for clients. Custom-theme work comes first, then
page-builder work, each with the role stated: design and content came from the
agency or the client. Agencies and clients are named and linked with their
permission. Sites with a server error stay out through their status, and client
code is not linked because the repos are private.

## 2026-10-21 — Sitemap v3: AI and automation at launch

AI and automation becomes a phase-1 service, the fourth on the Services hub.
It describes the work (automations, Claude set up for a team, a site an
assistant can operate, translation and QA pipelines), not a title, and its
proof is this site and the Founderz work in the wording already approved on
About. Invoicing automation stays out until there is a real case.
Maintenance stays off the Home.

## 2026-10-22 — Public prices on every service that has a floor

A visible "from" price is the best filter for requests and what search
engines and assistants quote when asked what the work costs. It must be on
the page: structured data that marks up a price nobody can see is ignored,
or worse. Prices exclude VAT, and the hub says so when a row shows a figure.

- **WordPress development: from 1,500 €.** The floor is a basic site: Home,
  services, about, blog, projects, contact and legal pages, in two
  languages, built from the client's design. Design is a separate item,
  quoted on its own. Larger builds are quoted.
- **WordPress maintenance: 150 €/month** (Care) and **250 €/month** (Care +
  2 hours of development), up from 80 and 140, so the price reads as a
  service and the extra hours sit at a senior rate. Updates the 2026-10-19
  entry.
- **AI and automation: from 1,000 €** for a setup. Smaller one-off
  automations can still be quoted privately.
- **Website ownership: quoted.** A monthly fee only makes sense tied to a
  capacity (days per month), which is not fixed yet.
- **No public hourly or agency rate.** Agencies get quotes privately: an
  hourly figure would anchor every other price and read as a mid-level
  profile.
- The `Service` structured data (with `offers` from separate price fields)
  comes with the technical phase, after the layout of every page.


## 2026-10-22 — Footer: a reduced main menu; maintenance leaves it

The footer links are now the main menu (About, Work, Services, Notes,
Contact) plus Resume and How this site is built, then "Cookie settings" and
a LinkedIn icon. A footer that repeats the main pages is what visitors
expect at the end of a long page, and it gives Resume and How this site is
built a link from every page without crowding the header. Updates the
2026-10-19 entry: WordPress maintenance is no longer a footer link.

- **Maintenance stays one click away.** The Services hub lists it, and every
  service page ends with an "Other services" section that lists the rest,
  so maintenance gets internal links from each service, in context, instead
  of a sitewide footer link. Still indexed, still off the Home and the main
  menu.
- **Seeded in code.** `scripts/seed-footer-menu.php` (WP-CLI) builds a
  "Footer" and a "Footer ES" menu from pages, not custom links, so each
  follows its language's permalinks, and assigns them through Polylang.
  `scripts/playground-seed.php` builds the same menus in Playground.
- **LinkedIn icon.** The URL is the LinkedIn row in Site settings › Profiles,
  the same value the Person schema uses as `sameAs`: one place to change it.
  An inline SVG: a solid square in ink with "in" cut out, square corners like
  the buttons (it inverts with the theme, so it stays visible in dark mode).
  Kept out of `assets/icons/` so it does not appear in the editors' icon
  picker. It
  opens in a new tab with `rel="me noopener"`, `aria-label="LinkedIn"` and
  the footer's "Opens in a new tab" note.

## 2026-10-22 — Legal links on their own row at the bottom of the footer

Privacy, Legal notice and Cookies now have their own menu (`legal` location,
"Legal" and "Legal ES"), shown as a second row at the bottom of the footer,
aligned to the end edge (start on narrow screens), with "Cookie settings"
after them (Gonzalo). The first row keeps the site's pages and the LinkedIn
icon. Legal links are expected at the very bottom, and "Cookie settings"
belongs with the cookie policy.

- **No new line:** the row sits under the first one with a small space, so
  the footer still has a single hairline above it.
- **Seeded in code** like the footer menu: `scripts/seed-footer-menu.php`
  builds both menus per language, and the Playground seed does the same.

## 2026-10-22 — Work becomes Projects, with new addresses

"Work" and "Selected work" read as vague template labels on the finished
site (Gonzalo). The hub is now **Projects / Proyectos** and the Home section
**Sites I've built / Webs que he construido**: concrete, and in the first
person like the rest of the Home. Rejected: "Case studies" (only Founderz has
a full case) and "Sites" alone as a menu label (too bare in English).

- **Addresses move too** (Gonzalo, knowing the cost): `/projects/` and
  `/es/proyectos/` for the hub, and the same bases for every project
  (`/projects/founderz/`, `/es/proyectos/founderz/`). The page template is
  `templates/projects.php`.
- **Old addresses redirect with a 301**, hub and singles, query kept:
  `/work/…` and `/es/trabajo/…` (and Polylang's `/es/work/…`) go to the same
  path under the new base (`portfolio_core_moved_bases()`, filterable). Tested
  in `tests/e2e/i18n.spec.mjs`.
- **Before the site is public**, so no search result points to the old
  addresses yet; the redirects are there for links already shared and for
  the decision entries that still say `/work/`.

## 2026-10-22 — "How this site is built" leaves the footer

The footer links are the main menu plus Resume; "How this site is built" is
no longer one of them (Gonzalo). Its label was by far the longest and broke
the row, in Spanish most of all, and the page is already reached from the
"Gonzalo Alcina" row in Projects and from About. The page stays as it is.

## 2026-10-22 — Footer layout: two columns, menus on the end edge

From 48rem the footer is a two-column grid: the copyright with the LinkedIn
icon under it on the start edge, and the footer links with the legal links
under them on the end edge, so both menus line up on the right (Gonzalo).
Below 48rem the copyright and the icon share the first row, the icon on the
end edge, and each menu takes its own row. The icon is now its own
`link-row-a` (`site__footer-social`), out of the footer links list.
