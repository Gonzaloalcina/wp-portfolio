# Phase 14 — Consent and analytics

The cookie banner, Google Consent Mode v2, GTM and GA4, and the dataLayer
events.

See [README.md](README.md) for how entries are written.

## 2026-10-21 — A banner of our own, not a plugin

The site loads one tool that needs consent: GA4, through GTM (Gonzalo,
2026-10-21). For that, a consent plugin (Complianz, CookieYes…) brings what
is not needed (cookie scanning, generated legal text, Google's CMP
certification, which only ad serving requires) and costs what the site
avoids (its own JavaScript, CSS and sometimes third-party requests). A
banner of its own is a few KB on the design system, and Consent Mode and a
clean dataLayer are part of what the portfolio shows. Cost: when a tool is
added, the banner, the version and the cookies page are updated by hand.

## 2026-10-21 — Consent Mode v2 in basic mode

Basic mode (Gonzalo, 2026-10-21): nothing from Google loads until the
visitor accepts. Rejected: advanced mode (Google tags run from the start
and send cookieless pings while denied), which models missing data but is
harder to defend under the AEPD's guidance.

- **`plugin/inc/consent.php`** prints `plugin/assets/js/consent.js`
  inline, first thing in `<head>`: every Consent Mode signal denied by
  default (ad signals stay denied for good: no ads), the page context
  pushed (`page_type`, `language`), and the stored answer read. With
  analytics accepted it grants `analytics_storage` and only then loads GTM.
  Inline and synchronous so the defaults come before any tag and the banner
  never flashes.
- **GTM ID in `wp-config.php`** (`PORTFOLIO_GTM_ID`), like the Turnstile
  keys. None yet: without it the dataLayer still fills (check it in the
  console) and nothing leaves the site. GA4 is configured inside GTM, with
  no ID in the code.
- **Without JavaScript** there is no banner and nothing loads.

## 2026-10-21 — The answer: a first-party cookie, 12 months, versioned

- `portfolio_consent` = `<version>.<1|0>.<unix time>`: what was consented
  to, the answer and when, which is the record GDPR asks to be able to
  show. Set from JavaScript, `SameSite=Lax`, `Secure` on https.
- **12 months** for accepts and rejects alike (the AEPD accepts up to 24).
- **`PORTFOLIO_CORE_CONSENT_VERSION`**: raise it when the tools or their
  use change; every stored answer is then void and the banner asks again.
- **Withdrawing** (Reject after Accept) deletes the `_ga*` cookies and, if
  GTM was loaded on the page, reloads it so the container stops.

## 2026-10-21 — consent-a: the banner

- Theme component (`consent-a`, `theme/inc/consent.php`), first in `<body>`
  so keyboard and screen reader users meet it before the page. A `section`
  with an accessible name, not a modal: it does not block the page. Fixed to
  the bottom end corner (right, where visitors expect it; Gonzalo,
  2026-10-21) on the text measure, paper on a 1px ink line (ink, not the
  hairline: it floats over content and needs the edge). No new token.
- **Accept and Reject are the same `button-a`**: rejecting weighs the same
  as accepting. A link to the cookie policy in the text.
- **"Cookie settings"** in the footer links opens it again (focus moves to
  the banner and comes back to the link). Without JavaScript it is a plain
  link to the cookie policy (`link-row-a` links gain a `data` arg).
- Shown in every page, `/design-system/` included (Gonzalo, 2026-10-21).
- Text, to review with the legal pages: "I use Google Analytics to see which
  pages people read. It only runs if you accept." Spanish translated.

## 2026-10-21 — dataLayer events

`plugin/assets/js/datalayer-events.js` reads the theme's data-* hooks
(`08-page-templates.md`, `13-contact-form.md`):

| Event | When | Parameters |
|---|---|---|
| `consent_update` | An answer | `analytics_storage` |
| `cta_click` | A link or button with `data-cta-location` | `cta_location`, `service`, `link_url` |
| `generate_lead` | A form sent (`form-a:sent`) | `form_id`, `cta_location`, `service` |

- **Only with analytics accepted:** before that, not even the dataLayer
  keeps a record of what the visitor does. The page context and the consent
  calls are always there (no personal data).
- **Every event carries all its keys**, empty when unknown, so no value
  leaks from one event into the next in GTM's merged data model.
- **Services by their default-language slug** (`mantenimiento-wordpress` is
  reported as `wordpress-maintenance`), from a map printed with the config:
  settles the question left open in `08-page-templates.md`.
- **Tests:** `tests/php/ConsentTest.php` (GTM ID, page type, head script) and
  `tests/e2e/consent.spec.mjs` (banner, cookie, events, focus, language;
  any request to Google fails the test).

## Pending, outside the code

- **Cookies page:** list `portfolio_consent` (own, 12 months, necessary),
  the GA4 cookies (`_ga`, `_ga_<id>`, 2 years by default, analytics, only
  with consent) and Turnstile (necessary, security, only on pages with a
  form).
- **Privacy page:** GA4 as a processor (Google), and the contact messages
  kept 12 months (`13-contact-form.md`).
- **In GA4:** data retention and IP settings, when the property exists.
