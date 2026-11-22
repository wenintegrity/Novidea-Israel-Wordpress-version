# 0002: WordPress with a classic theme and custom dynamic blocks, replacing Astro

- **Status:** Accepted
- **Date:** 22/11/2026
- **Decided by:** Project owner
- **Roles affected:** all (Structure, Design, Code, Testing, Content, SEO, Marketing), plus the new WordPress ops role

## Context

The first version of the site was built with Astro + TypeScript (repo `wenintegrity/novidea-israel`, live at the temporary address `novidea-israel.vercel.app`). Content lived in YAML files in git, so every text change needed a developer, a commit and a deploy.

Novidea marketing has to edit the site themselves: page text, pictures, menus, the lead banner, search titles and descriptions. They know WordPress. The site must keep everything the Astro build already does: Hebrew first (RTL), the same URLs, the same markup and design (Bootstrap 5.3.9 from a CDN, ADR 0001), IS 5568 / WCAG 2.1 AA accessibility, cookie consent before HubSpot, and the technical SEO in `docs/seo.md`.

## Decision

Rebuild the site on **WordPress**, with all custom code in this repo:

- **Theme `novidea-israel`** (`wp-content/themes/novidea-israel/`): a classic PHP theme for presentation only: header, footer, page and 404 templates, menus (`primary`, `footer`), Bootstrap 5.3.9 from jsDelivr with SRI (RTL build when `is_rtl()`), the brand CSS (`tokens.css`, `brand.css`, `layout.css`), self-hosted Rubik, the scroll animations (vanilla JavaScript, replacing Motion), and the shared content in the Customizer (header button, lead banner, footer sentence).
- **Plugin `novidea-blocks`** (`wp-content/plugins/novidea-blocks/`): content and functionality. Ten **dynamic** Gutenberg blocks (`block.json` + `render.php` + `style.css`), each a port of an Astro component with identical markup: hero, page header, text (picture or video), features, customer logos, page links, quotes carousel, office location, legal text, HubSpot lead form. The editor shows a live server-side preview and a form generated from a schema (built with `@wordpress/scripts`). Also the SEO module (titles, descriptions, `noindex`, Open Graph, JSON-LD, sitemap, robots.txt), cookie consent, the HubSpot loader, the brand icons and WebP image sizes.
- **Content in the WordPress database**, edited in wp-admin. A one-time migration (`npm run migrate`) imports the Astro YAML: pages, media, menus, Customizer values and SEO fields. After that, WordPress is the source of truth for content and the Astro YAML is no longer edited.
- **Local development on WordPress Playground** (`npm start`, Node only: no PHP, MySQL or Docker install). Locally the database is SQLite (SQLite Database Integration plugin). `./wordpress/` is a disposable local install and isn't in git.
- **Production on a host with MySQL or MariaDB** and PHP 8.3 (MongoDB isn't possible with WordPress). The host isn't chosen yet; that will be its own ADR.
- **URLs unchanged:** permalinks `/%postname%`, no trailing slash.
- **Hebrew only for now** (site language `he_IL`; WordPress sets RTL). Source strings in code are English, Hebrew comes from translation files. English comes later through a multilingual plugin (e.g. Polylang), with its own ADR.
- **Bootstrap 5.3.9 via CDN is kept** (ADR 0001 still applies).

Dynamic blocks (rendered by PHP on every request) were chosen over static blocks (HTML saved in the post): the markup can change in one place without re-saving every page or writing block deprecations for markup changes, and the output stays identical to the Astro components.

## Alternatives considered

| Option | Why not |
|---|---|
| **Keep Astro, add a headless CMS** | Two systems to run and pay for, and editors still can't preview the real page easily. The owner wants WordPress |
| **PHP theme with content in files** (templates or YAML read by PHP) | Same problem as Astro: every text change needs a developer and a deploy |
| **Core blocks + block patterns** (Group, Columns, Heading, Image…) in a block theme | Editors could break the layout, accessibility and design rules (heading order, contrast, RTL, logical properties) with a few clicks. Hard to keep markup identical to the tested Astro components. Bootstrap's grid and components don't map cleanly onto core block markup |
| **Advanced Custom Fields (ACF) blocks** | A paid plugin (ACF Pro) for block registration, a large third-party dependency, and field definitions stored outside `block.json`. The custom block form needs only `@wordpress/scripts` |
| **Page builder** (Elementor and similar) | Heavy front-end CSS and JavaScript (fails the Lighthouse budget), weak control over accessibility and RTL markup, vendor lock-in |

## Consequences

- **Good:** marketing edits text, pictures, menus, the lead banner and SEO fields without a developer. Fields are structured (one form per block), so the design and accessibility rules stay in code. Markup, URLs and design match the Astro site.
- **Content is no longer in git.** Changes to content have no pull request or review. Backups of the database and uploads become essential, and content review happens in WordPress (drafts, preview, revisions) or by agreement with the Content agent.
- **Block attributes are a contract with saved content.** Renaming or removing an attribute in `block.json` can break existing pages. Changes need backward-compatible reading, a deprecation or a migration (`AGENTS.md` §4.7).
- **A server to run and secure:** PHP, a database, WordPress core and plugin updates, login security, backups. A new **WordPress ops** role owns hosting, deployment, backups and updates (`AGENTS.md` §3.8).
- **No build-time checks on content:** the Astro schemas failed the build on a missing alt text or a too-long title. WordPress saves anything. The editor form warns where it can; the rest moves to Playwright tests against the running site.
- **Pages are rendered per request:** speed needs page caching at the host. The Lighthouse budget (`AGENTS.md` §3.4) is unchanged.
- **Indexing switch:** `ALLOW_INDEXING` becomes Settings → Reading → "Discourage search engines" (on until launch); `SITE_URL` becomes Settings → General.
- **Dependencies:** WordPress core and the SQLite integration (local only) are the only third-party runtime pieces. Any further plugin needs a reason, a review and an owner (`AGENTS.md` §4.7).
- **The Astro project** stays read-only as the reference and the migration source until launch, then its Vercel address redirects to the final domain (`docs/seo.md` §6).
