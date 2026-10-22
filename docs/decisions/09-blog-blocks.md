# Phase 09 — Blog blocks

Custom Gutenberg blocks for notes: what they are, where they live, how they
are built and loaded.

See [README.md](README.md) for how entries are written.

## 2026-10-18 — Three blocks: Related, Code with file name, Aside

- **Related** (`portfolio-core/related`, dynamic): one service and,
  optionally, one project. It only stores their IDs; titles, links and the
  one-line text are read at render time, so a renamed service or a project
  whose client withdraws consent updates every note. A service that is not
  published, or a project that is not published or whose client may not be
  named, is skipped. One per note (`multiple: false`).
- **Code with file name** (`portfolio-core/code`, static): an optional file
  name above the code and an optional caption below. The code is stored as
  plain text. It converts to and from core's Code block. No syntax
  highlighting: it would need JavaScript or a PHP highlighter, for little
  gain on short snippets.
- **Aside** (`portfolio-core/aside`, static): a label and up to a few
  paragraphs, lists or code blocks between two 1px lines. Note and Decision
  are variations of the label, which stays editable. No box and no side line:
  the brief rules them out.

Component classes follow the theme's convention (`related-a`, `code-a`,
`aside-a`, BEM). The generated `wp-block-*` class and the Additional CSS
class field are off (`className`, `customClassName`), so the markup only
carries the design system's classes.

Changing a static block's saved markup later needs a `deprecated` entry, or
posts saved with the old markup turn invalid in the editor.

## 2026-10-18 — The blocks live in portfolio-core

Blocks are content: a post written with them must keep working if the theme
changes, so they live in the plugin (`plugin/blocks/`), not in the theme.

- The block CSS uses the token custom properties the theme defines. With
  another theme the properties are undefined and the blocks fall back to
  plain, readable markup.
- **Related reuses the theme's list-a and row-a** through
  `portfolio_theme_component()` when that function exists, so the rows look
  exactly like the rest of the site and there is one row design. Otherwise it
  prints a plain list of links. The plugin owns the data
  (`portfolio_core_related_items()`), the theme owns the look.

## 2026-10-18 — Sitemap rule: a pre-publish warning, not a lock

The sitemap says every note links to one service. The Related block makes
that the easy path, and a pre-publish panel warns when a note has no Related
block or no service chosen. It does not block publishing: the editor stays
in charge, and a note without a fitting service is rare but possible.

## 2026-10-18 — A REST route for the editor instead of exposing the post types

`project` and `service` are not in the REST API (`show_in_rest` off, classic
editor). Enabling it would expose every field and switch them to the block
editor unless filtered back. Instead the plugin adds
`GET /portfolio-core/v1/related-options`: IDs and titles of what a note may
link to (published services; published projects whose client may be named),
for users who can edit posts.

## 2026-10-18 — Build: @wordpress/scripts 35, output committed and checked in CI

- `npm run build:blocks` runs `wp-scripts build` over `plugin/blocks/src/`
  into `plugin/blocks/build/`, then writes `blocks-manifest.php`. The plugin
  registers every block from that manifest
  (`wp_register_block_types_from_metadata_collection`), so WordPress does not
  read each `block.json` on every request.
- **Output committed**, like the CSS and the tokens: Playground installs the
  plugin from the repository (`git:directory`) and a zip works without
  building. `npm run check:blocks` builds into a temporary folder and fails
  if any committed file differs, is missing or is extra, and if block CSS
  hard-codes px. CI runs it in its own job (`npm ci`).
- **Version 35** at first (now 37, see the entry of 2026-10-22), not 36: 36 requires Node 22, and the tooling stays on
  Node 20 (Node 24 only for Playground CLI).
- **Lockfile generated with npm 11** (`npx npm@11.6.2 install`): npm 10.8.2,
  the one Node 20.19 ships, crashes resolving this tree from scratch
  (`Cannot read properties of null (reading 'edgesOut')`, an Arborist bug
  with an optional peer dependency). Since the Playwright packages, `npm ci`
  needs npm 11 too (see the entry of 2026-10-21 below).
- The root `package.json` is `"type": "module"`, so block sources import
  relative files with their extension (`./edit.js`), as native modules do.
- PHPCS skips `plugin/blocks/build/` (generated); `render.php` is linted in
  `plugin/blocks/src/`.

## 2026-10-18 — Block CSS only where a block renders

Each block's front-end CSS is its `block.json` `style`. Core prints it only
when the block renders, and inlines it because it is small (under 1 KB each),
so it adds no request. Notes are the only content with blocks (decision 04),
so the CSS never loads on the Home, hubs, the notes index (excerpts) or a
note that does not use the block. Editor-only rules (the plain-text fields)
are in `editorStyle` and never reach the front end.

Checked in Playground: the three styles inline on the test note; none on
`/`, `/notes/`, another note or a service page.

## 2026-10-18 — Editor styles: editor-styles support declared

`theme/inc/editor.php` called `add_editor_style( 'tokens.css' )`, but the
block editor only reads editor styles when the theme declares
`add_theme_support( 'editor-styles' )`, so the tokens never reached the
editor canvas (contrary to decision 04's note). The support is now declared:
the font size presets and the blocks' token-based CSS preview correctly.
The canvas still uses the editor's own fonts and base styles; loading the
site's fonts and base CSS in the editor is a separate decision.

## 2026-10-21 — `npm ci` with npm 11 as well

After the Playwright packages entered the lockfile, npm 10.8.2 (Node 20) stopped
accepting it: `npm ci` fails with `Missing: @emnapi/core … from lock file`. It
reads an optional peer dependency of `@napi-rs/wasm-runtime` as missing, though
the lockfile is complete: npm 11 installs from it and regenerating it changes
nothing. CI and local installs run `npx npm@11.6.2 ci`. Node stays on 20; moving
the tooling to Node 22 or 24 would also bring npm 11, but is a separate decision.

## 2026-10-22 — Plain `npm ci` again, on Node 24

The tooling moved to Node 24 (decision 05). Its npm (11.19) rejected the lockfile
as well; regenerating it with that npm added the two `@emnapi` entries the
older npm left out, so the entry above was wrong to call the lockfile complete.
With the regenerated lockfile, `npm ci` works without pinning npm. Version 36+
of `@wordpress/scripts` is now possible; upgrading is a separate change.

## 2026-10-22 — `@wordpress/scripts` 37

Upgraded from 35 to 37, pinned as before. The breaking changes of 36 and 37
(Node 22+, Jest replaced by consumer-installed Vitest, webpack-dev-server 6,
markdownlint and stylelint config lookup) do not touch this project: it only
uses `build`, `start` and `build-blocks-manifest`, with no JS unit tests and no
custom dev server. The block build came out byte for byte the same, the blocks
of an existing note stay valid in the editor, and the install drops about 200
packages (no bundled Jest).
