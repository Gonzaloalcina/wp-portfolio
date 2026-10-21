# Phase 13 — Contact form

The endpoint behind the Contact page and the Services request form, in
`portfolio-core`, and how the theme connects to it.

See [README.md](README.md) for how entries are written.

## 2026-10-21 — One REST endpoint, stored and emailed

Replaces the placeholders of `08-page-templates.md` ("Contact form: static
placeholder" and the request form of "Services: choose by situation").

- **One endpoint for every form**, `POST /wp-json/portfolio/v1/contact`
  (`plugin/inc/contact-form.php`), told apart by `form_id`: `contact` (name,
  email, company, message) and `services_request` (name, email, service,
  site, message). Each form's fields and limits live in one function; a field
  a form does not define is dropped. The site URL accepts `example.com` and
  gets `https://`.
- **Order of checks:** honeypot, rate limit, fields, Turnstile, then store
  and email. The cheap checks come first, so a bot never costs a call to
  Cloudflare.
- **Honeypot:** a `website` field kept off screen. A filled one gets the
  same answer as a success, so a bot learns nothing.
- **Rate limit:** 5 attempts an hour per IP (filter
  `portfolio_core_contact_rate_limit`), counted in a transient whose key is
  a salted hash: the IP is never stored. `REMOTE_ADDR` only, because
  forwarded headers can be forged; revisit if a CDN is put in front.
- **Turnstile verified server-side** (siteverify). With real keys the token
  must also come from this host and from this form (the widget's `action` is
  the form ID). Keys go in `wp-config.php` (`PORTFOLIO_TURNSTILE_SITE_KEY`,
  `PORTFOLIO_TURNSTILE_SECRET_KEY`), never in the database or the repo.
  Without them, local and development installs use Cloudflare's test keys;
  any other environment has no form (fail closed).
- **Stored, then emailed** (Gonzalo, 2026-10-21): each accepted message is a
  private `portfolio_message` post (`plugin/inc/contact-messages.php`), read
  only in the admin, then emailed to the admin address with Reply-To the
  sender. Shared hosting mail can fail; the message stays in the admin
  marked "not emailed". Rejected: email only (a failed email loses the lead)
  and storage only (nobody would see it in time).
- **Deleted after 12 months** by a daily cron job (filter
  `portfolio_core_contact_retention_months`). The privacy page must say so.
- **No reply to the sender** (Gonzalo, 2026-10-21): the on-screen message
  is enough, and an automatic reply could be used to send mail to third
  parties from this domain.
- **Codes, not copy:** the endpoint answers `invalid_fields` (with a code per
  field: required, invalid, too_short, too_long), `challenge_failed`,
  `rate_limited` or `not_configured`; the theme owns the wording in both
  languages (`portfolio_theme_form_messages()`). The plugin's admin strings
  stay English, as decided in `11-i18n.md`.

## 2026-10-21 — The theme connects forms, JavaScript sends them

- `portfolio_theme_form_connection( $form_id )` (`theme/inc/forms.php`)
  returns the `form-a` args that connect a form: action, Turnstile site key,
  hidden `form_id` and `lang`, the honeypot name and the messages. Without
  the plugin or the keys it returns nothing and the form stays a placeholder
  (no action, disabled button, "not connected" note).
- **`form-a.js`** (native ES module, loaded only where a connected form is
  rendered) sends the form as JSON and says how it went in a
  `role="status"` line. The button is disabled until the module runs: the
  spam check needs JavaScript, so a `<noscript>` note says so instead of a
  submit that cannot pass.
- **Turnstile loads on the first interaction** with the form, not with the
  page, and uses `interaction-only`: most visitors never see it and pages
  without a visitor typing pay nothing. It is the only third-party request
  on the site and is strictly necessary (security); the cookies page must
  list it.
- **Errors:** the browser checks first (`required`, `type`, `maxlength`
  matching the endpoint's limits, a new `field-a` arg, like `inputmode`);
  the endpoint's field errors appear under each field, linked with
  `aria-describedby`, `aria-invalid` set and focus on the first one. One ink,
  no red: the line turns to the text colour and the message is strong. No new
  token.
- **Privacy link on both forms:** "How the data you send is used", to the
  privacy page. Services has it as the section's `more` link; Contact gets it
  through a new `more` arg of `form-a`, under the note, in the same small
  size.
- **Analytics hook:** on success the form fires a bubbling `form-a:sent`
  event with the form ID and the service, for the dataLayer piece to push
  `generate_lead` after consent.
- **Test:** `tests/e2e/contact-form.spec.mjs` sends one real message
  (Turnstile test keys) and checks the error display with the endpoint
  mocked, so it costs one attempt of the rate limit per run. The server-side
  rules are covered by PHPUnit (`tests/php/ContactFormTest.php`,
  `ContactMessagesTest.php`).
