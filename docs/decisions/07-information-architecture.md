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

