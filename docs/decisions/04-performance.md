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
