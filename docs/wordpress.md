# WordPress guide

**Owner:** Code agent (`code`); the "Deploy" and "Operations" sections: WordPress ops agent (`wordpress-ops`) · **Status:** v0.1 · **Last updated:** 22/11/2026

How the WordPress version of the site works, day to day. Why it's built this way: [ADR 0002](decisions/0002-wordpress-with-custom-blocks.md). Rules: `AGENTS.md` §4.7. This guide describes the build as of 22/11/2026, while it is still being written; parts marked **(not built yet)** don't exist yet.

---

## 1. Run it locally

Needs Node.js only (no PHP, MySQL or Docker).

```sh
npm install
npm start          # WordPress Playground on http://127.0.0.1:9400, logged in as admin automatically
npm run migrate    # one-time import of the Astro content (pages, media, menus, Customizer, SEO fields)
npm run build      # build the block editor UI (plugin src/ → build/)
npm run watch      # rebuild the editor UI on every change
npm run reset      # delete ./wordpress (the local site); then npm start and npm run migrate again
```

- `npm start` runs WordPress Playground (PHP 8.3, latest WordPress) with the settings in `blueprint.json` (debug on, environment `local`). The first run downloads WordPress into `./wordpress/`.
- `./wordpress/` is the local site: WordPress core, uploads and the SQLite database (SQLite Database Integration plugin). It's git-ignored and **disposable**. Our code isn't copied into it: `wp-content/themes/novidea-israel/` and `wp-content/plugins/novidea-blocks/` are mounted into the site, so edits show up on reload.
- **Local content is throwaway.** Until the site is on staging, content comes from the migration. Anything typed into the local wp-admin is lost on `npm run reset`.
- PHP changes show on reload. Editor UI changes (`src/`) need `npm run build` or `npm run watch`.

## 2. Where things are

| What | Where |
|---|---|
| Templates (header, footer, page, 404) | `wp-content/themes/novidea-israel/*.php` |
| Theme supports, menus (`primary`, `footer`), lean `<head>` | theme `inc/setup.php` |
| Bootstrap 5.3.9 from jsDelivr (SRI, RTL build when `is_rtl()`), site CSS and JS | theme `inc/assets.php` |
| Tokens, brand layer, layout CSS, fonts | theme `assets/css/tokens.css`, `brand.css`, `layout.css`, `fonts.css` (Rubik Variable, self-hosted in `assets/fonts/`) |
| Scroll animations | theme `assets/js/animate-on-scroll.js` (IntersectionObserver + Web Animations; no library) |
| Shared content (header button, lead banner, footer sentence) | theme `inc/customizer.php` → Appearance → Customize → "Novidea: shared content" |
| Logo, menus, lead banner output | theme `inc/template-tags.php` (logo inlined from `assets/images/logo-shapes.svg`) |
| Blocks | `wp-content/plugins/novidea-blocks/blocks/<name>/` (`block.json`, `render.php`, `style.css`); registered in `includes/blocks.php` |
| Shared block helpers (pictures, video, icons, UI icons) | plugin `includes/helpers.php` |
| Block editor UI (preview + form, SEO panel) | plugin `src/` (`index.js`, `edit.js`, `form.js`, `schemas.js`, `seo-panel.js`) → `build/` |
| Page meta (search title, description, noindex, hide lead banner) | plugin `includes/meta.php` |
| Technical SEO (titles, robots, Open Graph, JSON-LD, sitemap, robots.txt) | plugin `includes/seo.php` |
| Cookie consent (banner, dialog, `window.nvConsent`) | plugin `includes/consent.php`, `assets/js/consent.js` |
| HubSpot form loader | plugin `assets/js/hubspot-form.js` |
| WebP image sizes | plugin `includes/media.php` |
| Brand icons | plugin `assets/icons/` (`<id>.png`, `<id>.on-dark.png`, names in `icons.json`) |
| Translations | theme `languages/` (text domain `novidea-israel`), plugin `languages/` (text domain `novidea-blocks`) |
| Migration from Astro | `migration/` (`npm run migrate`) |
| Development settings | `blueprint.json` |

## 3. How a page is built

A page is a WordPress page made of Novidea blocks (inserter category "Novidea sections"): first a **Hero** (home, landing pages) or **Page header** (internal pages), then sections such as **Text section**, **Feature cards**, **Customer logos**, **Page link cards**, **Customer quotes**, **Office location**, **Legal text** and **Lead form (HubSpot)**. The theme adds the header, footer and the lead banner around it.

Every block is **dynamic**: WordPress saves only the block's attributes (JSON in the page content), and `render.php` builds the HTML on every request. The markup is the same as the Astro component it replaces, so the design system and tests carry over. In the editor, each block shows a live server-rendered preview and a form generated from its schema in `src/schemas.js`.

Fields, defaults and rules for every block: `docs/content-model.md`.

## 4. Change the code

### 4.1 Add a field to an existing block
1. Structure agrees the field in `docs/content-model.md`.
2. Add the attribute to `blocks/<name>/block.json` **with a default**. Never rename or remove an attribute without a plan (§4.4).
3. Add the field to the block's entry in `src/schemas.js` (types: text, textarea, select, toggle, link, strings, image, video, icon, repeater). Labels through `__( '…', 'novidea-blocks' )`.
4. Use it in `render.php`, escaped (`esc_html()`, `esc_attr()`, `esc_url()`).
5. `npm run build`, check the editor and the front end on `npm start`, add the Hebrew label to the translations (§6), ask Testing for checks.

### 4.2 Add a new block
1. Structure specs it in `docs/content-model.md`, Design in `docs/design-system.md`.
2. Create `blocks/<name>/block.json` modelled on an existing one: `"apiVersion": 3`, `"name": "novidea/<name>"`, `"category": "novidea"`, `"textdomain": "novidea-blocks"`, `"supports": { "html": false, "className": false, "customClassName": false }`, the attributes with defaults (an enum for fixed choices like `surface`), `"render": "file:./render.php"`, `"style": [ "novidea-blocks-common", "file:./style.css" ]`, `"editorStyle": "novidea-blocks-editor-ui"`.
3. Write `render.php` (it receives `$attributes`; escape everything; use the helpers in `includes/helpers.php` for pictures, video and icons) and `style.css` (tokens and logical properties only).
4. Add the name to `novidea_block_names()` in `includes/blocks.php` (that list is the registration and inserter order).
5. Add its schema to `src/schemas.js` (and a `required` field so an empty block opens in the form), then `npm run build`.
6. Translations, tests, and the docs (`content-model.md`, `design-system.md`).

### 4.3 Add a page setting or a shared setting
- **Per page** (like "Keep out of search results"): register the meta in `includes/meta.php` (type, `sanitize_callback`, `auth_callback`, `show_in_rest`), add the control to `src/seo-panel.js`, read it with `get_post_meta()`.
- **Site-wide text** (like the lead banner): add a setting and control in the theme's `inc/customizer.php` with a `sanitize_callback`, read it with `get_theme_mod()`.

### 4.4 Changing or removing a block attribute
Saved pages hold the old attributes. Before renaming, removing or changing a type: read the old value as a fallback in `render.php`, or add a `deprecated` entry for the block in the editor script, or write a one-off migration of existing pages (WP-CLI or a script) and test it on a copy of the real content. Agree it with Structure (`AGENTS.md` §4.7).

### 4.5 Code checks
- `npm run build` must succeed.
- PHP: `php -l` on changed files, PHPCS with the WordPress Coding Standards **(not set up yet)**.
- JavaScript and CSS: `@wordpress/scripts` lint (`wp-scripts lint-js`, `lint-style`) **(no npm script yet)**.
- E2E and accessibility: Playwright + axe-core against `npm start` **(not ported yet; the Astro specs are the starting point)**.

## 5. How editors work (wp-admin)

| Task | Where |
|---|---|
| Edit a page's text, pictures, video | Pages → the page → click a block, edit its form in the editor |
| Add a section to a page | "+" → "Novidea sections" → pick a block |
| Search title, description, noindex, hide the lead banner | Page editor → settings sidebar → "Novidea: search and page" (counts characters; title ≤ 60, description ≤ 155) |
| Header and footer menus | Appearance → Menus (locations `primary` and `footer`) |
| Header button, lead banner, footer sentence | Appearance → Customize → "Novidea: shared content" |
| Pictures, logos, video, captions (.vtt) | Media → Library (alt text is set in the block, because it depends on the context) |
| Privacy policy page | Settings → Privacy (set to the `/privacy` page) |
| Keep the whole site out of search | Settings → Reading → "Discourage search engines" (on until launch) |
| Site address | Settings → General (all canonical, sitemap and link-preview URLs come from it) |

Rules for editors (Novidea marketing): glossary and style in `docs/glossary-he.md`; no claim without a source (`AGENTS.md` §3.5); alt text for meaningful pictures; captions for videos with speech; quotes without quotation marks (the site adds them). Use Preview before Update; WordPress keeps revisions (page editor → Revisions) for undoing mistakes. **(Not built yet:** a short Hebrew editor guide for Novidea marketing.)

## 6. Translations

- Code strings are English; Hebrew comes from translation files. The site language is `he_IL` (Settings → General), which also makes WordPress set `dir="rtl"`.
- PHP strings: `__()`, `esc_html__()`, `_x()`, `_n()` with text domain `novidea-israel` (theme) or `novidea-blocks` (plugin). Editor JavaScript strings: `@wordpress/i18n` with `novidea-blocks`; the plugin loads them with `wp_set_script_translations()` from `languages/`.
- Files (edited by hand; no `.po`/`.pot` files and no WP-CLI step):
  - Theme PHP strings: `wp-content/themes/novidea-israel/languages/he_IL.l10n.php`, a PHP array, English source text → Hebrew (WordPress's PHP translation format, 6.5+).
  - Plugin PHP strings, including block titles and descriptions from `block.json` (keys `"block title\4<English>"` and `"block description\4<English>"`): `wp-content/plugins/novidea-blocks/languages/novidea-blocks-he_IL.l10n.php`.
  - Editor JavaScript strings: edit `wp-content/plugins/novidea-blocks/languages/editor-he_IL.mjs`, then run `node wp-content/plugins/novidea-blocks/languages/editor-he_IL.mjs` to regenerate `novidea-blocks-he_IL-novidea-blocks-editor.json`.
  - A string with no entry shows in English, so a missing translation is easy to spot on the page.
- When code adds or changes strings, Code lists them for Content, who writes the Hebrew.
- **English later:** a multilingual plugin (e.g. Polylang) with its own ADR. English page content will be separate WordPress pages linked as translations; code strings already have English sources.

## 7. Migration from Astro

`npm run migrate` (`migration/`) reads the Astro project's content (page YAML, shared YAML, images and video) and creates: pages built from Novidea blocks, media library items, the `primary` and `footer` menus, the Customizer values and each page's SEO fields. Run it on a fresh site (`npm run reset`, `npm start`, `npm run migrate`).

- After the cut-over to staging, **WordPress is the source of truth** for content. The Astro YAML is no longer edited, and the migration isn't run against a site with real edits.
- YAML comments (including `# TODO(verify)`) aren't data and don't migrate. Their open items are in `Open tasks.md`.
- Each migrated page must be checked against the Astro site (`Open tasks.md`, WordPress section).

## 8. Deploy

Owner: WordPress ops. The site currently runs on a temporary Cloudways app (temporary address, not indexed). The hosting ADR is still to be written.

### 8.1 Code: push to `main` → `deploy` branch → Cloudways

```
git push (main) ──► GitHub Action "Deploy to Cloudways" (.github/workflows/deploy-branch.yml)
                      1. PHP syntax check (a broken file stops the deploy)
                      2. updates the `deploy` branch: only the theme and the plugin (no src/, docs, migration)
                      3. optional: asks Cloudways to pull `deploy` (Cloudways API)
                  ──► Cloudways → Deployment via Git → branch `deploy` → public_html/
```

- The Action runs only when something under `wp-content/` changed. Run it by hand from GitHub → Actions → "Deploy to Cloudways" → Run workflow.
- `DEPLOYED.txt` on the `deploy` branch says which `main` commit it holds.
- **Never commit to `deploy` by hand**: every run replaces its content with `main`'s.

**One-time setup in Cloudways** (Applications → the app → Deployment via Git):
1. Generate SSH Key, copy it, and add it on GitHub: repo → Settings → Deploy keys → Add deploy key (read-only).
2. Git Remote Address `git@github.com:wenintegrity/Novidea-Israel-Wordpress-version.git` → Authenticate.
3. Branch `deploy`, Deployment Path `public_html/` → Start Deployment.

**Each deploy without auto-pull:** after the Action finishes (GitHub → Actions, green tick), click **Pull** in Cloudways → Deployment via Git. Then purge the Varnish cache if a change doesn't show.

**Auto-pull (optional):** in GitHub → Settings → Secrets and variables → Actions, add the repository secrets `CLOUDWAYS_EMAIL` (the Cloudways login email), `CLOUDWAYS_API_KEY` (Cloudways → Account → API Integration), `CLOUDWAYS_SERVER_ID` and `CLOUDWAYS_APP_ID` (the numbers in the app's Cloudways URL: `…/server/<server id>/…/<app id>`). If the Deployment Path isn't `public_html/`, add the variable `CLOUDWAYS_DEPLOY_PATH`. From then on a push to `main` reaches the site with no click.

**Fallback without git:** `npm run migrate:bundle:no-video` writes `migration/out/novidea-israel.zip` and `novidea-blocks.zip`; upload them in wp-admin (Themes / Plugins → Upload → "Replace active with uploaded").

Only code travels this way. Content (pages, menus, Customizer text, media) is in the database and is edited in wp-admin.

### 8.2 Outline for production


1. **Host:** MySQL or MariaDB, PHP 8.3, HTTPS, daily off-site backups with tested restore, a staging site, SSH and WP-CLI, page caching. Production doesn't use SQLite.
2. **Code:** the theme and the plugin (with its built `build/` folder) go from git `main` to staging, then production, by a repeatable step (CI deploy job, SSH/rsync, or the host's git deploy). Never edit code on the server.
3. **Content, once:** export the migrated site (database and uploads) and import it on the host (or run the migration on staging directly), then replace the local address with the staging address (`wp search-replace`). From then on content is edited only there, and code deploys never overwrite the database or uploads.
4. **Settings on the host:** permalinks `/%postname%`, site language `he_IL`, Privacy Policy page, "Discourage search engines" on (until launch), staging password-protected with `X-Robots-Tag: noindex`, `DISALLOW_FILE_EDIT`, debug display off.
5. **Launch:** `docs/seo.md` §6 (site address, indexing on, 301s from the Vercel address and any temporary host, Search Console).

## 9. Operations (not built yet)

Owner: WordPress ops. To fill in when hosting exists: backup schedule and restore steps, update policy (minor and security automatic; major and plugin updates on staging first), accounts and roles (marketing as Editor, few Administrators, 2FA), security headers, monitoring (uptime, PHP errors), and who to call.
