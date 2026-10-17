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
