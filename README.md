# Novidea Israel — WordPress

The Israeli website for [Novidea](https://novidea.com), Hebrew-first (RTL), built on WordPress so marketing can edit it in wp-admin. It is a port of the Astro site (`Novidea-Israel`) with the same design, markup and URLs ([ADR 0002](docs/decisions/0002-wordpress-with-custom-blocks.md)).

- **Theme** [wp-content/themes/novidea-israel](wp-content/themes/novidea-israel): page shell (header, footer, lead banner, 404), Bootstrap 5.3.9 from the CDN with the Novidea brand layer, Rubik font.
- **Plugin** [wp-content/plugins/novidea-blocks](wp-content/plugins/novidea-blocks): the page sections as blocks (hero, page header, text, features, customer logos, links, quotes, location, legal text, HubSpot lead form), starter page patterns, page SEO, cookie consent.

## Run it

Requires Node.js 22 or later. No PHP, MySQL or Docker: local development runs on [WordPress Playground](https://wordpress.github.io/wordpress-playground/) (PHP 8.3, SQLite).

```sh
npm install
npm start            # http://127.0.0.1:9400 — logged in as admin / password
npm run migrate      # first time: import the Astro content (pages, media, menus, settings)
```

`npm run migrate` reads the Astro project from `../Novidea-Israel` (or `npm run migrate -- <path>`). Stop `npm start` while it runs.

| Command | What it does |
| --- | --- |
| `npm start` | Local site on port 9400. Extra flags pass through, e.g. `npm start -- --phpmyadmin` |
| `npm run build` | Builds the block editor code (`novidea-blocks/src` → `build`). Commit the build. |
| `npm run watch` | Rebuilds the editor code on every change |
| `npm run migrate` | Imports the Astro content into the local site (safe to repeat) |
| `npm run reset` | Deletes the local WordPress in `./wordpress` (your code in `./wp-content` is untouched) |

`./wordpress` (WordPress core, uploads, the SQLite database) is local and disposable; it is not in git. The code in `./wp-content` is mounted into it, so edits show up on refresh.

## Editing the site

- **Pages:** Pages → a page → each section is a Novidea block. Select it and click "Edit content" (or double-click it) to edit its fields; "Preview" shows it as on the site. New page → pick a starter pattern: home, landing, internal, contact or legal page.
- **Search title, description, noindex, lead banner:** the "Novidea: search and page" panel in the page sidebar.
- **Menus:** Appearance → Menus (Header, Footer).
- **Shared texts** (header button, lead banner, footer blurb): Appearance → Customize → "Novidea: shared content".

More in [docs/wordpress.md](docs/wordpress.md).

## Start here

- [AGENTS.md](AGENTS.md): best practices, stack, repository layout and the agent roles. Claude Code subagents are in [.claude/agents/](.claude/agents/).
- [Open tasks.md](Open%20tasks.md): what's left before launch.
- [docs/](docs/): design system, SEO plan, sitemap, content model, Hebrew glossary, sources, decisions.
