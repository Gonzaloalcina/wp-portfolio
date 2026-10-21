# Decision log

Closed decisions for this project, grouped by phase. Each entry says what was
decided and why.

| Phase | Covers |
|---|---|
| [01 — Foundation](01-foundation.md) | Repository, git identity, layout, local environment |
| [02 — Design system](02-design-system.md) | Styling approach, tokens, typography, colour, tokens build |
| [03 — Theme](03-theme.md) | Theme structure, requirements and templates |
| [04 — Performance](04-performance.md) | Front-end weight, default WordPress output |
| [05 — Quality and CI](05-quality-ci.md) | Coding standards, tests, GitHub Actions |
| [06 — Playground demo](06-playground.md) | Blueprint and local Playground runs |
| [07 — Information architecture](07-information-architecture.md) | Sitemap, URLs, page purposes, linking rules |
| [08 — Page templates](08-page-templates.md) | Page templates, content types, portfolio-core |
| [09 — Blog blocks](09-blog-blocks.md) | Custom Gutenberg blocks: Related, Code with file name, Aside |
| [10 — Notes](10-notes.md) | What Notes is, formats, cadence, launch set |

New phases get their own file when work on them starts, numbered in the order
they start (performance, blog blocks, plugin, i18n, Playground…).

## How to write an entry

- Heading: `## YYYY-MM-DD — Short title`.
- Body: what was decided and why, in a few lines. Mention alternatives that
  were rejected when they explain the choice.
- Newest entries go last in each file.
- If a decision is reversed, add a new entry instead of editing the old one.
