# Sitemap

Pages, URLs and linking rules for gonzaloalcina.dev. Version 3, 2026-10-21.
The decisions behind it are in
[`decisions/07-information-architecture.md`](decisions/07-information-architecture.md).

## Goals

- Be found, by search engines and by LLMs, as a senior WordPress developer and
  as someone who owns a live website: campaigns, CRO, dataLayer and an editorial
  team, and as someone who applies AI and automation to that work.
- Lead every visitor to one action: a conversation about a project.
- English first. Spanish mirrors the structure through Polylang.

## Structure

```
/                                    Home
/about/                              About
/work/                               Work hub
   /work/founderz/                   Case: Founderz
   /work/ramon-esteve/               Case: Ramón Esteve
   /work/escuela-victoria/           Case: Escuela Victoria
/services/                           Services hub
   /services/wordpress-development/
   /services/website-ownership/
   /services/wordpress-maintenance/  Indexed; footer link, not in the main nav
   /services/ai-automation/
/notes/                              Blog (fortnightly)
   /notes/<post>/
/resume/                             Resume
/contact/                            Contact
/how-this-site-is-built/             How this site is built
/design-system/                      noindex
/privacy/   /cookies/   /legal-notice/
404
```

Spanish lives under `/es/`; English has no prefix.

| English | Spanish |
|---|---|
| `/about/` | `/es/sobre-mi/` |
| `/work/`, `/work/founderz/` | `/es/trabajo/`, `/es/trabajo/founderz/` |
| `/work/ramon-esteve/` | `/es/trabajo/ramon-esteve/` |
| `/work/escuela-victoria/` | `/es/trabajo/escuela-victoria/` |
| `/services/` | `/es/servicios/` |
| `/services/wordpress-development/` | `/es/servicios/desarrollo-wordpress/` |
| `/services/website-ownership/` | `/es/servicios/responsable-de-web/` |
| `/services/wordpress-maintenance/` | `/es/servicios/mantenimiento-wordpress/` |
| `/services/ai-automation/` | `/es/servicios/ia-y-automatizacion/` |
| `/notes/` | `/es/notas/` |
| `/resume/` | `/es/cv/` |
| `/contact/` | `/es/contacto/` |
| `/how-this-site-is-built/` | `/es/como-esta-hecha-esta-web/` |
| `/privacy/`, `/cookies/`, `/legal-notice/` | `/es/privacidad/`, `/es/cookies/`, `/es/aviso-legal/` |

## Pages

| Page | Purpose | Schema | Phase |
|---|---|---|---|
| Home | Hub: who, selected work, services, latest notes, contact | WebSite, Person | 1 |
| About | Entity page: who Gonzalo is, how he works with stakeholders | ProfilePage (Person) | 1 |
| Work | Hub: full cases plus a row list of projects | CollectionPage | 1 |
| Founderz case | Method and decisions, no internal figures or specific plugins | Article | 1 |
| Ramón Esteve case | Migration from a custom theme to Elementor, so the team can edit visually | Article | 1 |
| Escuela Victoria case | Architecture of a large language-course catalog | Article | 1 |
| Services | Hub: choose by situation (each service with when it fits and its price), how it works, request form | CollectionPage | 1 |
| WordPress development | Senior WordPress engineering | Service | 1 |
| Website ownership | Running a live site with stakeholders: dataLayer, CRO, technical SEO, editorial workflow | Service | 1 |
| WordPress maintenance | Updates, backups, monitoring. Two public plans. Page to send to people who ask | Service | 1 |
| AI and automation | Automations (n8n), Claude for teams, a WordPress site an assistant can operate, translation and QA pipelines. Describes the work, not a title. Proof: this site, Founderz (approved wording only) | Service | 1 |
| Notes | Blog hub and posts, three clusters | Blog, BlogPosting | 1 |
| Resume | Printable CV | WebPage | 1 |
| Contact | REST form with Turnstile | ContactPage | 1 |
| How this site is built | The repo, the design system, the decisions | Article | 1 |
| Design system | Tokens and components, live | none (noindex) | 1 |
| Privacy, cookies, legal notice | Legal pages, reviewed by Gonzalo | WebPage | 1 |
| 404 | Helpful dead end | none | 1 |

## Rules

- Two levels deep at most. Lowercase, hyphens, no dates in URLs.
- `hreflang` between each English and Spanish pair; self-referencing canonical;
  an XML sitemap. `/design-system/` is `noindex` and left out of the sitemap.
- Internal links: every post links to one service and, when it fits, to one
  project. Every service links to its proof. Home links to every hub.
- Every service page ends with the same request form as the hub, its service
  preselected; `?service=<slug>` preselects it on the hub.
- Maintenance is linked from the Services hub and the footer, and is indexed.
  It is not in the main navigation or on the Home. No HTML sitemap page is
  needed: the XML sitemap does the indexing and the footer link carries the
  internal weight.
- Maintenance proof: MDF Construcción.
- `BreadcrumbList` on every page below the first level. One H1 per page.
- The same name, headline and profile links on About, in `sameAs`, on LinkedIn
  and on GitHub, so search engines and LLMs treat them as one entity.
- `llms.txt` is optional and cheap, and its effect is not proven. Not a
  priority.

## Work hub data

Projects are a custom post type `project` with SCF fields named after the data
hub columns. Every project whose client may be named has its own page at
`/work/<slug>/`; featured marks the case studies (see
`decisions/08-page-templates.md`, 2026-10-20).

- The hub lists projects as rows (1px lines, like the rest of the design system).
- `featured` marks the case studies (long text, "Case study" above the
  title): Founderz, Ramón Esteve and Escuela Victoria.
- This site's own entry links to `/how-this-site-is-built/`, and
  `/work/this-website/` redirects there (301), so there is no duplicate
  content.
- Only rows with client OK to name equal to Yes or Not needed are shown.
- Custom-theme work first, then page-builder work, listed honestly by type and
  role. "No page builders" is a rule for this site only.
- Agency work: Source is Freelance; the agency (Xufa, Doowebs, Nokeon) and the
  fact that design and content came from them go in My role.
- A broken or dropped site is kept out of the hub through its status.
- No links to client code.
- The source data lives in the private data hub (`.private/data-hub.xlsx`).

## Maintenance plans

- Care: 150 €/month. Care + 2 hours of development: 250 €/month (from
  2026-10-22; see `decisions/07-information-architecture.md`).
- Prices without VAT, per website. Unused hours do not roll over.
- Anything else is quoted, depending on complexity. No public hourly rate.
- Included in both: WordPress, plugin and theme updates; PHP version control;
  backups; uptime monitoring; vulnerability and malware checks; broken-link
  detection; restore if an update breaks something; monthly report.
- Not included: new development, new features, front-end changes.
- No lock-in. Response when a site goes down: the same working day.
- Do not publish how many maintenance clients there are. Mention Modular DS.
- Plan names "Care" and "Care + 2 h" are placeholders.

## Notes clusters

One cluster per service: WordPress engineering, Website ownership, AI and
automation. The AI cluster publishes with the service, starting from how this
site is built.

## Phases

1. **Launch:** every page marked phase 1 above.
2. **After launch:** client rows pending permission, more case pages, more
   notes.

## Open items

- Client sites pending permission: which ones can be named.
- Legal pages: Gonzalo's review.
- Case pages: phase 1 if the text is ready by launch, otherwise phase 2.
