# Phase 04 — Performance

Front-end weight, default WordPress output and loading strategy.

See [README.md](README.md) for how entries are written.

## 2026-10-16 — Default WordPress output cleanup

Measured first (WordPress 7.1.3, logged out) on the home page, `/design-system`
and a test post with 12 core blocks. The cleanup lives in
`theme/inc/cleanup.php`, one commented removal per item.

- **Block editor for posts only.** Pages are edited with the classic editor
  and SCF fields (`use_block_editor_for_post_type`), so no blocks end up on
  pages.
- **Block CSS only on single posts** (`is_singular( 'post' )`): elsewhere
  `wp-block-library`, `classic-theme-styles`, `global-styles` and
  `core-block-supports` are dequeued. The blog index and archives will show
  excerpts, so they do not need them. Per-block styles (`wp-block-image`…)
  are left alone: core already loads them only when a block renders.
- **Removed everywhere:** emoji detection script and styles (browsers render
  emoji natively), generator meta, RSD link, shortlink (head and header),
  REST API discovery links (head and header) and oEmbed discovery links.
- **Kept on purpose:**
  - the REST API itself (the contact form needs it);
  - Rank Math's output;
  - feed links (a technical blog should offer RSS);
  - speculation rules (they prefetch on hover);
  - the 135-byte `img` auto-sizes rule (prevents layout shift with lazy images);
  - the oEmbed consumer (YouTube embeds in posts still work).
- **Not needed:** jQuery, Dashicons, admin-bar assets and duotone SVGs do not
  load for logged-out visitors in WordPress 7.1, so nothing was added for them.

Result on `/design-system`: `<head>` from 455 to 38 lines (23.7 KB to 4.2 KB),
HTML from 46.9 KB to 13.5 KB. Single posts lose about 15 KB of emoji and head
output. The number of requests does not change: everything removed was inline.

## 2026-10-17 — Theme stylesheet, editor presets and closed endpoints

- **`style.css` is no longer enqueued.** It only holds the theme header
  WordPress needs to recognise the theme; loading it was an empty
  render-blocking request.
- **Editor presets from the tokens** (`theme/inc/editor.php`, through the
  `wp_theme_json_data_theme` and `wp_theme_json_data_default` filters, since a
  classic theme has no `theme.json`):
  - colour controls off: the system is one paper, one ink and one grey that
    swap in dark mode, so a colour fixed in a post would break the dark theme;
  - gradients, duotone, shadows and spacing presets off;
  - font sizes limited to the four token sizes, as `var(--font-size-*)`;
  - core's default presets emptied at the source. The `default*` flags only
    hide them in the editor, and core keeps printing them for old content,
    which this blog does not have. Aspect ratios stay for the image block;
  - `tokens.css` loaded as an editor style so the size presets preview
    correctly.

  `global-styles` on single posts: 9.4 KB to 1.9 KB.
- **Closed endpoints** (`theme/inc/security.php`, moving to `portfolio-core`
  when the plugin exists):
  - XML-RPC: all methods removed, plus the `X-Pingback` header. Only the
    built-in `system.*` introspection methods remain; blocking the file
    entirely belongs to the server configuration;
  - REST `/wp/v2/users`: 401 for anyone who cannot edit posts, so author
    login slugs are not listed publicly. The block editor still reads it, and
    the rest of the REST API is untouched.

## 2026-10-17 — Font weight axis trimmed to 400–600

The design uses three weights (tokens `font.weight`: 400, 500, 600), but the
variable fonts shipped the full axis (Inter 100–900, Newsreader 200–800). All
six files are now instanced to `wght` 400–600, and the `@font-face` ranges in
`fonts.css` match. Both families keep the same range so any combination (a
`strong` inside a heading, for example) renders with real glyphs.

- 27–34 % smaller per file. The two preloaded latin files go from 106.3 KB
  to 74.9 KB, so every page loads about 31 KB less.
- The OFL licences declare no Reserved Font Name, so modified files keep
  their names.
- To redo it from the original Fontsource files (see
  `02-design-system.md`), with fontTools 4.60 and brotli in a throwaway
  virtualenv:
  `fonttools varLib.instancer <file>.woff2 wght=400:600 -o <file>.woff2`
- A new weight outside 400–600 means re-instancing from the originals.

## 2026-10-17 — Image sizes for a narrow layout

Images take the width of their container (`base.css`); the containers are
the text column (`layout.text`, 640px) and wide images (`layout.wide`,
1024px). WordPress generates four sizes, each a little larger than the case
it serves (`theme/inc/media.php`):

| Size | Width | Serves |
|---|---|---|
| `medium` | 640 | text column at 1x, phones at 1x |
| `medium_large` | 960 | phones at 2x (about 680–780px needed) |
| `large` | 1280 | text column on retina, phones at 3x, wide at 1x |
| `full` | ≤ 2048 | wide on retina (`big_image_size_threshold`, default 2560) |

Plus `thumbnail` (150×150 crop) for the admin media grid. Heights are not
limited, so tall screenshots are not shrunk. Core's `1536x1536` and
`2048x2048` are not generated: the scaled original covers them.

- **Sizes are forced in code** (`pre_option_*` filters) so local, production
  and Playground generate the same files. Settings › Media has no effect.
- **`sizes` attribute from the tokens.** Core's default is the file's own
  width, so retina screens fetched the 2048px original for a 640px column.
  The default is now `(min-width: 43rem) 40rem, calc(100vw - 3rem)` (the same
  rule as the CSS), and wide image blocks get the 64rem version. Lazy-loaded
  images still get `auto` prepended by core.
- **`$content_width` is 640, from `layout.text`.** It sizes embeds (YouTube
  was 500px). It does not affect front-end image `sizes`, which is why the
  filter above is needed.
- **WebP/AVIF output postponed:** the local PHP (XAMPP GD, no Imagick)
  supports neither, so it depends on the hosting.

Checked on the test post: a 1024px 2x screen loads the 1280 file for the
text column (2048 before); a 375px 2x phone loads the 960 file.

## 2026-10-23 — Page cache: WP Super Cache, Simple mode

Measured from outside with curl right after launch (three requests per page,
logged out): TTFB 0.93–1.09 s on `/`, 0.86–0.88 s on `/es/inicio/`,
0.78–0.82 s on `/projects/`. HTML is under 30 KB and gzipped by Apache, so
almost all of that time is PHP and the database building the page.

- **WP Super Cache** (Automattic), installed in wp-admin (Gonzalo). It
  stores each page as a file on disk and serves it on the next visit without
  running the theme or the database queries.
- **Simple delivery mode, not Expert.** Expert serves the files straight from
  `mod_rewrite` rules in `.htaccess`, but `/web` on cdmon also holds other,
  unrelated sites, and its root `.htaccess` is shared. Simple mode only needs
  `WP_CACHE` in `wp-config.php` and `wp-content/advanced-cache.php`.
- **Why not cdmon's own cache tool.** It is incompatible with cdmon's CDN
  (one or the other), and cdmon warns that its WordPress mode can conflict
  when several sites share one hosting in subfolders.
- **Safe to cache because the HTML is the same for every anonymous
  visitor:** the consent choice is read in the browser from the
  `portfolio_consent` cookie (`consent-a.js`), the colour scheme lives in
  `localStorage`, the language is in the URL (Polylang, no browser-language
  redirect) and the front end prints no nonces.
- **Not cached:** logged-in users; URLs with query strings; the contact form
  (`POST` to the REST route `portfolio/v1/contact`); Rank Math sitemaps and
  their XSL (`sitemap` and `\.xsl` in Rejected URL Strings), so they are
  always current.
- **Compression off in the plugin:** Apache already gzips HTML.
- Publishing or updating a post or page clears the whole cache.
- Other settings: cache rebuild and extra homepage checks on, Cache Timeout
  1800 s (the plugin's default here).

Result (2026-10-24, same curl method, cached pages): TTFB 0.13–0.16 s on `/`,
0.11–0.13 s on `/es/inicio/`, 0.11–0.15 s on `/projects/`, about seven times
faster. The first visit after a purge still takes ~0.9 s while the file is
written. A URL with a query string (`?utm_source=…`) is generated each time
(~0.96 s), as intended. Sitemaps carry no cache marker, and the contact
route still answers from PHP (`POST` with no body: 400).
