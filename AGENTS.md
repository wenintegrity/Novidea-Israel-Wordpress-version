# Novidea Israel — Agents & Best Practices

This file is the operating manual for everyone (human or AI agent) working on the **Novidea Israel** website: a local, Hebrew-first version of [novidea.com](https://novidea.com) for the Israeli insurance market.

Read it before you change anything. If it conflicts with a request, raise the conflict. Don't work around it quietly.

> **Stack change (22/11/2026):** the site moved from Astro to **WordPress** with a custom theme and a custom blocks plugin, so Novidea marketing can edit content themselves. See [ADR 0002](docs/decisions/0002-wordpress-with-custom-blocks.md). The Astro project (`C:\Users\eytanr\Novidea-Israel`, repo `wenintegrity/novidea-israel`) is the read-only reference and the migration source. Never change it from here.

---

## 1. Project at a glance

| Item | Decision |
|---|---|
| Audience | Israeli insurers, insurance agencies (סוכנויות ביטוח), brokers, and MGAs |
| Primary language | Hebrew (`he_IL` site language, RTL set by WordPress), served at `/` |
| Secondary language | English (`en`, LTR), planned under `/en/` through a multilingual plugin (e.g. Polylang), decided in an ADR when the work starts. Hebrew only for now |
| Source of truth for product messaging | novidea.com, adapted for Israel, not machine-translated |
| Platform | **WordPress** (latest, PHP 8.3) with a classic PHP theme (`novidea-israel`) and a custom plugin of dynamic Gutenberg blocks (`novidea-blocks`). See ADR 0002 |
| Local development | [WordPress Playground](https://wordpress.github.io/wordpress-playground/) CLI: `npm start` → http://127.0.0.1:9400 (admin logged in automatically). Node.js only, no PHP, MySQL or Docker. Local database: SQLite |
| Styling | **Bootstrap 5.3.9 from the jsDelivr CDN** with SRI: `bootstrap.rtl.min.css` when `is_rtl()`, `bootstrap.min.css` otherwise, plus the Novidea brand layer (theme `assets/css/tokens.css`, `brand.css`, `layout.css`, and each block's `style.css`). Our own CSS uses **logical properties only**; in markup use Bootstrap's start/end utilities (`ms-*`, `pe-*`, `text-start`). See ADR 0001. Check Bootstrap markup and utilities against the official docs for LLMs, [getbootstrap.com/llms-full.txt](https://getbootstrap.com/llms-full.txt), and the Bootstrap skills in `.claude/skills/`. The site stays on **5.3.9**: skills and docs about Bootstrap 6 or bundlers are reference only, not a migration plan |
| Content | In the WordPress database: pages built from Novidea blocks, menus, Customizer values, SEO fields, media library. Edited in wp-admin. Imported once from the Astro YAML by `npm run migrate` |
| Testing | Playwright (E2E and visual) and axe-core (accessibility) against the running site, Lighthouse (performance and SEO), PHPCS with the WordPress Coding Standards, `@wordpress/scripts` lint. Being ported from the Astro project |
| Hosting | To be decided: a WordPress host with MySQL or MariaDB, PHP 8.3, HTTPS, backups and a staging site. Record the choice in `docs/decisions/` |

Stack changes go through an Architecture Decision Record (ADR) in `docs/decisions/NNNN-title.md`. Adding a third-party plugin counts as a stack change if it affects the front end, data or security.

---

## 2. Repository layout

```
.
├── AGENTS.md                 ← this file
├── CLAUDE.md                 ← points Claude Code to this file
├── Open tasks.md             ← everything still open, by owner and priority
├── README.md                 ← how to run it
├── package.json              ← npm start / build / watch / migrate / reset
├── blueprint.json            ← Playground development settings, applied on every start
├── scripts/                  ← Playground start script
├── migration/                ← one-time import from the Astro YAML (npm run migrate)
├── .claude/
│   ├── agents/               ← subagent definitions (one per role below)
│   └── skills/               ← Bootstrap skills (reference; the site stays on 5.3.9)
├── docs/
│   ├── wordpress.md          ← practical guide: run, where things are, add a block, editing, deploy
│   ├── sitemap.md            ← owned by Structure agent
│   ├── content-model.md      ← owned by Structure agent
│   ├── design-system.md      ← owned by Design agent
│   ├── glossary-he.md        ← owned by Content agent (Hebrew terminology)
│   ├── seo.md                ← owned by SEO agent (keywords, technical SEO, domain move)
│   ├── marketing/            ← owned by Marketing agent
│   ├── sources/              ← source documents (content document, press releases, quotes)
│   ├── drafts/               ← texts not on the site yet (accessibility statement)
│   └── decisions/            ← ADRs
├── wp-content/
│   ├── themes/novidea-israel/        ← presentation
│   │   ├── header.php, footer.php, page.php, 404.php, index.php, functions.php, style.css
│   │   ├── inc/              ← setup.php, assets.php, customizer.php, template-tags.php
│   │   ├── assets/css/       ← fonts.css, tokens.css, brand.css, layout.css
│   │   ├── assets/js/        ← animate-on-scroll.js
│   │   ├── assets/fonts/, assets/images/
│   │   └── languages/        ← Hebrew translations (text domain novidea-israel)
│   └── plugins/novidea-blocks/       ← content structure and functionality
│       ├── blocks/<name>/    ← block.json + render.php + style.css (10 blocks)
│       ├── src/ → build/     ← editor UI (npm run build / watch)
│       ├── includes/         ← blocks, helpers, meta, seo, consent, media
│       ├── assets/           ← css, js (consent, HubSpot), icons (+ icons.json)
│       └── languages/        ← Hebrew translations (text domain novidea-blocks, PHP + JS)
├── tests/                    ← Playwright + axe (to be ported)
└── wordpress/                ← local WordPress install: core, uploads, SQLite DB. Git-ignored, disposable
```

Only `wp-content/themes/novidea-israel/` and `wp-content/plugins/novidea-blocks/` are WordPress code we own. Everything in `./wordpress/` is regenerated by `npm start` and deleted by `npm run reset`.

---

## 3. The agents

Each role below has a matching subagent in `.claude/agents/`. A role **owns** certain files or settings: other agents may propose changes to them, but the owner makes or approves the change.

### Workflow

```
 Structure ──► Content ──┐
     │                   ├──► Code ──► Testing ──► PR review ──► merge ──► WordPress ops (staging → production)
     └──────► Design ────┘
        ▲        ▲          ▲
        └── SEO ─┴──────────┘   (advises Structure, Content and Code; owns the SEO plan)
```

1. **Structure** defines what pages exist, their URLs, what each page contains, and the fields of each block.
2. **Content** and **Design** work in parallel from that spec.
3. **Code** implements the theme and blocks from the structure spec, content model and design tokens.
4. **Testing** verifies the result against the running site and blocks the merge if quality gates fail.
5. **SEO** audits the site and feeds search requirements into Structure, Content, Code and Testing through `docs/seo.md`.
6. **Marketing** drafts campaigns, landing pages, social posts, emails and case studies in `docs/marketing/`; site copy goes through Content.
7. **WordPress ops** deploys code, keeps the WordPress sites (staging, production) healthy, backed up, updated and secure.

Each handoff is a file in the repo, not a chat message. If it isn't written down, the next agent can't rely on it. **Content is the exception:** it lives in the WordPress database, so content changes are recorded by WordPress revisions, and anything still open about them goes in `Open tasks.md`.

---

### 3.1 Structure agent: `structure-architect`

**Purpose:** information architecture, sitemap, URLs, navigation, content model, block fields, SEO skeleton.

**Owns:** `docs/sitemap.md`, `docs/content-model.md`, the attribute schema of each block (`wp-content/plugins/novidea-blocks/blocks/<name>/block.json`: attribute names, types, defaults) and the matching field list in `src/schemas.js` (Code implements the controls), the menu plan (which items are in `primary` and `footer`), the permalink rule, and redirect rules.

**Responsibilities**
- Map novidea.com's sections to the Israeli site. Decide what to keep, adapt, drop, or add for Israel (for example local customers, local events, a Hebrew contact flow).
- Define URL slugs. Hebrew pages use **English (Latin) slugs** (`/solutions/brokers`, not Hebrew-encoded URLs), so links stay readable when shared. In WordPress the slug is the page's permalink; permalinks are `/%postname%` with no trailing slash.
- Define each page's blocks and each block's fields in `content-model.md`. Content and Code both build from this. A field change in `block.json` changes saved content: plan it with Code (§4.7).
- Plan navigation (header, footer, breadcrumbs) and, when English comes, the language switcher, mapping each Hebrew page to its English counterpart.
- Define SEO metadata per page: title, description, `hreflang` pairs, canonical URL, and structured data (Organization, BreadcrumbList).

**Done when:** every page in the sitemap has a URL, its blocks, an `hreflang` counterpart (or an explicit "he only"), and a content-model entry.

---

### 3.2 Design agent: `design`

**Purpose:** visual language, design tokens, component specs, and RTL correctness.

**Owns:** `docs/design-system.md`, the theme's `assets/css/tokens.css` and `assets/css/brand.css` (the brand layer over Bootstrap), and the visual values (colors, sizes, spacing, which tokens) in the theme's `layout.css` and each block's `style.css`. Code writes those two kinds of files; Design approves any visual change in them.

**Responsibilities**
- Inherit Novidea's global brand (logo, colors, imagery style) from novidea.com and the brand guidelines. The Israeli site should feel like Novidea, not a separate brand.
- Hebrew typography: Rubik (self-hosted variable font), pending Novidea's approval. Slightly larger size and line-height for Hebrew text.
- Define tokens for color, spacing, type scale, radii, shadows and breakpoints. Components use tokens only, never raw hex values or pixel sizes.
- Write an RTL spec for each component:
  - Directional icons (arrows, chevrons, "next") are mirrored. Logos, media controls, charts, and phone numbers are **not**.
  - Mixed Hebrew/English text (product names like *Novidea*, *Salesforce*) must render correctly. Use `<bdi>` or `dir="auto"` where needed.
- Meet contrast and focus-state requirements (see Accessibility, §4.3).
- Check Bootstrap 5.3 behaviour against [getbootstrap.com/llms-full.txt](https://getbootstrap.com/llms-full.txt) and `.claude/skills/` (reference only; we stay on 5.3.9).

**Done when:** every component used in the sitemap has a spec with RTL and LTR states, mobile and desktop layouts, and hover, focus and disabled states.

---

### 3.3 Code agent: `code`

**Purpose:** implement the site in WordPress.

**Owns:** `wp-content/themes/novidea-israel/` and `wp-content/plugins/novidea-blocks/` (except the files and values owned by Design and Structure, and the Hebrew translation values owned by Content), `scripts/`, `migration/`, `package.json`, `blueprint.json`, build, lint and CI configuration.

**Responsibilities**
- Build templates and blocks from the structure spec, content model, and design tokens. Don't invent copy or styles. If something is missing, ask the owning agent.
- **Theme = presentation, plugin = content and functionality.** Templates, menus, header, footer, styles and Customizer settings go in the theme. Blocks, page meta, SEO, consent, HubSpot and media handling go in the plugin, so content keeps working if the theme changes.
- Every block is **dynamic** (`block.json` + `render.php`) with the same markup as the Astro component it replaces. The editor UI lives in `src/` and is built to `build/` with `@wordpress/scripts` (`npm run build`).
- `lang` and `dir` come from WordPress (`language_attributes()`), never hard-coded.
- Use CSS logical properties everywhere. A physical direction (`left`, `margin-right`, `text-align: left`) needs a comment explaining why.
- No hard-coded user-facing strings: English source strings through WordPress i18n functions with the right text domain (§4.7); Hebrew values come from the translation files.
- Format dates, numbers and currency for `he_IL` (`wp_date()`, `number_format_i18n()`, or `Intl` in JavaScript): dates as DD/MM/YYYY, ₪ for currency, `+972` phone numbers.
- Ship minimal JavaScript: vanilla scripts enqueued only where needed, with `defer`.
- Images through the media library and `wp_get_attachment_image()` (srcset, explicit width and height, WebP sizes). Self-host fonts.
- Follow the WordPress security and coding rules in §4.7.
- Check Bootstrap 5.3 markup and utilities against [getbootstrap.com/llms-full.txt](https://getbootstrap.com/llms-full.txt) and `.claude/skills/` (reference only; we stay on 5.3.9).

**Done when:** `npm run build` succeeds, PHP lint and PHPCS pass, the change works on `npm start` and matches the design spec, and the tests the Testing agent asked for exist.

---

### 3.4 Testing agent: `testing`

**Purpose:** quality gates. This agent can block a merge or a deploy.

**Owns:** `tests/`, Playwright configuration, Lighthouse budgets, PHPCS configuration (with Code).

**Responsibilities**
- **E2E (Playwright)** against the Playground site (`npm start`) or staging: navigation, menus, forms (consent gate, HubSpot loading), the lead banner, 404 pages, and the language switcher when English exists.
- **Visual regression:** screenshots of key pages in `he` (and `en` later) at mobile (375px), tablet (768px) and desktop (1440px). Until launch, also compare each migrated page with the Astro site at the same widths.
- **Accessibility:** run axe-core on every page with zero serious or critical violations, plus a manual keyboard-only pass on new components.
- **RTL checks:** no horizontal scroll, directional icons mirrored correctly, no physical CSS properties leaking in.
- **SEO checks:** each page has a unique title and description within the limits, a canonical URL, `noindex` only where intended, valid `hreflang` pairs (when English exists), and appears in `/wp-sitemap.xml` unless `noindex`.
- **Content checks:** no empty alt on meaningful pictures, no `[לעדכון: …]` placeholder left before launch, titles ≤ 60 and descriptions ≤ 155 characters (WordPress doesn't enforce them).
- **Code checks:** PHP lint and PHPCS (WordPress Coding Standards), `@wordpress/scripts` lint for JavaScript and CSS.
- **Performance budget (Lighthouse, mobile):** Performance ≥ 90, Accessibility = 100, SEO = 100, Best Practices ≥ 95. LCP < 2.5s, CLS < 0.1.

**Done when:** all gates pass. A failure is reported with the failing page, locale, viewport and a reproduction step. "Tests pass" is never claimed without running them.

---

### 3.5 Content agent: `content`

**Purpose:** Hebrew (and later English) copy, localized for the Israeli insurance market.

**Owns:** the page content (block fields, page SEO fields), menu labels and Customizer text in WordPress; the Hebrew values in the theme's and plugin's `languages/`; `docs/glossary-he.md`.

**Where content is edited:** in wp-admin (Pages, Appearance → Menus, Appearance → Customize, the "Novidea: search and page" panel), by Novidea marketing or by the Content agent. Never in theme or plugin files. Until the site is on staging, the local site is disposable (`npm run reset` deletes it): fixes before the cut-over go into the migration input so a re-run keeps them; after the cut-over to staging, WordPress is the only place content is edited (`docs/wordpress.md`).

**Responsibilities**
- **Localize, don't translate.** Rewrite novidea.com messaging for Israeli readers: shorter sentences, a direct tone, and local examples.
- Keep a **Hebrew insurance glossary** (`docs/glossary-he.md`) and use it consistently, for example: insurer → מבטח / חברת ביטוח, agency → סוכנות ביטוח, underwriting → חיתום, claims → תביעות, policy → פוליסה, renewal → חידוש. Product and third-party brand names stay in English (Salesforce, Open API). **Exception: our own brand is written "נובידאה" in Hebrew running text** (owner's decision, 20/11/2026); "Novidea" stays in the logo, in English text and inside Latin-script names.
- Use gender-inclusive or neutral Hebrew where possible (for example plural forms or infinitives in calls to action: "לתיאום הדגמה" rather than "תאם הדגמה").
- Make **no unverified claims.** Every customer name, statistic, certification or regulatory statement needs a source (novidea.com, a published case study, or written approval from Novidea marketing). Mark anything unverified `TODO(verify)`: content in the database can't hold comments, so list it in `Open tasks.md` with the page, block and wording (legal pages keep their highlighted `[לעדכון: …]` placeholders). It must not ship.
- Write SEO copy (title ≤ 60 characters, description ≤ 155 characters) for each page, following the Structure agent's metadata plan.
- Write alt text for every meaningful image in the page's language (in the block's picture field).
- Check the Hebrew of the editor UI and site strings in the translation files.

**Done when:** every field in the content model is filled for each page (or marked "he only"), the glossary is followed, and no `TODO(verify)` remains.

---

### 3.6 SEO agent: `seo`

**Purpose:** search visibility in Israel: Hebrew keywords, on-page SEO, technical SEO, and the move from temporary URLs to the final domain.

**Owns:** `docs/seo.md` (keyword map, technical SEO checklist, domain-move plan, measurement). Uses the `marketing:seo-audit` skill.

**Responsibilities**
- Audit the site for search: indexing, titles and descriptions, headings, internal links, structured data, link previews, Core Web Vitals.
- Keep a Hebrew keyword map per page and hand titles, descriptions and copy changes to Content (no unverified claims, §3.5).
- Define technical SEO for Code and Structure: `robots.txt`, the sitemap, canonical and `hreflang` tags built from the WordPress site address (Settings → General, never a hard-coded domain), JSON-LD, Open Graph, 404. Implemented in the plugin's `includes/seo.php`.
- **Every address before launch is temporary** (the Astro site at `novidea-israel.vercel.app`, the local site, any staging host). Keep them out of search indexes: Settings → Reading → "Discourage search engines" stays on until launch, plus a host-level `noindex` header on temporary hosts. Own the launch checklist for the final domain (301 redirects, Search Console, sitemap submission).
- Hand SEO checks to Testing.

**Done when:** every page has a keyword target and metadata in `docs/seo.md`, the technical checklist is implemented by its owners, and the domain-move plan is ready.

---

### 3.7 Marketing agent: `marketing`

**Purpose:** marketing for the Israeli market: positioning and messaging, campaign and landing pages, LinkedIn and social posts, email campaigns, case studies, competitive briefs and campaign reporting.

**Owns:** `docs/marketing/`. Uses the marketing skills (`marketing:draft-content`, `content-creation`, `brand-review`, `campaign-plan`, `competitive-brief`, `email-sequence`, `performance-report`).

**Responsibilities**
- Draft campaign material in the site voice and glossary terms, with every claim traced to `docs/sources/`, novidea.com or Novidea marketing's written approval (§3.5).
- Hand site copy to Content, new pages and new blocks to Structure, visuals to Design, campaign-page indexing to SEO, and builds to Code. A campaign page built from existing Novidea blocks needs no code: Structure plans it and Content builds it in wp-admin.
- Respect consent and privacy: tracking only after cookie consent, opt-in and unsubscribe for email (§4.4).

**Done when:** each draft lists its sources and open ⚠ items, and has been handed to the owning agent for publishing.

---

### 3.8 WordPress ops agent: `wordpress-ops`

**Purpose:** get the site to launch and keep it running: hosting, deployment, backups, updates, security and the WordPress settings of staging and production. (New with the WordPress stack, ADR 0002: a static Astro site needed none of this.)

**Owns:** the hosting ADR and environments (staging, production), the deployment process (the "Deploy" and "Operations" sections of `docs/wordpress.md`), WordPress settings, users and roles, plugin installs and updates on staging and production, backups and restores, host-level configuration (security headers, redirects, caching, HTTPS, PHP version), and the DNS cut-over at launch together with SEO.

**Responsibilities**
- Recommend a host (MySQL or MariaDB, PHP 8.3, HTTPS, daily backups with tested restore, a staging site, SSH or WP-CLI access, servers in or near Israel or a CDN) and record the decision in an ADR.
- Write and run the deployment process: theme and plugin from git (built `build/` included) to staging, then production; content (database and uploads) moved once from the migrated site to the host, then never overwritten by a code deploy.
- Keep core, plugins and PHP updated: security releases quickly, major versions tested on staging first. No plugin without a reason, a review and an owner (§4.7).
- Harden the sites: least-privilege accounts (marketing as Editor, few Administrators), 2FA for administrators, login rate-limiting, file editing in wp-admin off, debug output off, security headers at the host (§4.6).
- Keep staging and every temporary host out of search (`noindex` header, "Discourage search engines" on, password protection on staging).

**Done when:** a staging site runs the current `main`, deployments and restores are documented and tested, and the launch checklist (`docs/seo.md` §6, `Open tasks.md`) is ready to run.

---

## 4. Cross-cutting best practices

### 4.1 Git and pull requests
- `main` is protected and always deployable. Work on short-lived branches: `feat/…`, `fix/…`, `content/…`, `design/…`, `test/…`, `ops/…`.
- Use [Conventional Commits](https://www.conventionalcommits.org) (`feat:`, `fix:`, `content:`, `docs:`, `test:`, `chore:`).
- One concern per PR. A PR description says what changed, why, which agent roles were involved, and includes screenshots in `he` (and `en` once it exists) for any visual change.
- Git holds code and docs only. WordPress core, `./wordpress/`, uploads, the database and secrets (`wp-config.php`) never go in git. Content changes are made in WordPress, not in PRs.
- CI (build, lint, PHPCS, tests, Lighthouse) must be green before merging.

### 4.2 Localization and RTL
- Hebrew is the default and is designed first. English is not the base that Hebrew gets "flipped" from.
- Logical CSS properties only. Check every layout in both directions.
- Mixed-direction strings (Hebrew containing English words or numbers) must be checked visually. Punctuation at the end of mixed strings is a common bug.
- Israeli conventions: the work week runs Sunday to Thursday, dates use DD/MM/YYYY, currency is ₪ (ILS), and phone numbers use `+972` / `0X-XXXXXXX`.
- Code strings are English; Hebrew comes from translation files (§4.7). The site language is `he_IL` (Settings → General); WordPress sets `dir="rtl"` from it.

### 4.3 Accessibility (legal requirement in Israel)
- Israeli law (the Equal Rights for Persons with Disabilities regulations, 2013, and Israeli Standard **IS 5568**, based on WCAG) requires accessible websites. Target **WCAG 2.1 AA** as the minimum.
- The site must publish an **accessibility statement** (הצהרת נגישות) with a named accessibility contact.
- Don't use third-party "accessibility overlay" widgets or plugins as a substitute for accessible code.
- Every interactive element is keyboard reachable, has a visible focus state, and has an accessible name in the current language.
- Editors can't break this: blocks control headings, contrast and markup; editors fill fields (including alt text and video captions).

### 4.4 Privacy and legal
- Comply with the Israeli Privacy Protection Law (including Amendment 13) and GDPR where EU data is involved. Have legal review the privacy policy and cookie notice before launch.
- Load analytics and marketing scripts only after consent (`window.nvConsent` in the plugin). This includes any plugin that adds tracking.
- Collect only the form fields that are needed. Lead forms go to HubSpot; WordPress stores no form submissions.
- The privacy policy page is set as WordPress's Privacy Policy page (Settings → Privacy).

### 4.5 Performance and SEO
- Pages are rendered by PHP per request: use page caching at the host, and keep the front end lean (no jQuery, no plugin CSS or JavaScript on pages that don't need it). Every byte of client JavaScript needs a reason.
- When English exists: `hreflang="he-IL"` and `hreflang="en"` plus `x-default` on every page pair. Always: a generated sitemap (`/wp-sitemap.xml`) and `robots.txt`.
- Set up Google Search Console for the Israeli domain at launch.

### 4.6 Security
- No secrets in the repo. Database credentials, salts and API keys live in `wp-config.php` or host environment variables on the server only.
- Set security headers (CSP, HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`) at the host. The CSP must allow jsDelivr and HubSpot's domains.
- WordPress accounts: unique strong passwords, 2FA for administrators, least privilege (Editor for content work), login rate-limiting, no shared accounts. Remove accounts that are no longer needed.
- Keep WordPress core, PHP and plugins updated; test major updates on staging first; don't auto-update major versions on production. Keep npm dev dependencies updated with Dependabot or Renovate.

### 4.7 WordPress rules
- **Never edit WordPress core, `./wordpress/`, or third-party plugins.** Our code lives only in the theme and the plugin.
- **Theme = presentation, plugin = content and functionality** (§3.3). Content must survive a theme change.
- **Content is edited in wp-admin, not in files.** No page copy in PHP templates. The only strings in code are UI strings and fallbacks, through i18n functions.
- **Escape output, late:** `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` for allowed HTML, `wp_json_encode()` for JSON-LD and data attributes. Every attribute value from a block is untrusted.
- **Sanitize and validate input, early:** `sanitize_text_field()`, `sanitize_textarea_field()`, `absint()`, `rest_sanitize_boolean()`, allow-lists for enumerations (e.g. `surface`). Register post meta with `sanitize_callback`, `auth_callback` and `show_in_rest`.
- **Nonces and capabilities** on every form, admin action, AJAX call and REST route: `check_admin_referer()` / `wp_verify_nonce()` plus `current_user_can()`. No REST route without a `permission_callback`.
- **i18n:** every user-facing string goes through `__()`, `esc_html__()`, `esc_attr__()`, `_x()`, `_n()` (PHP) or `@wordpress/i18n` (JavaScript), with a literal text domain: `novidea-israel` in the theme, `novidea-blocks` in the plugin. Add `/* translators: */` comments for placeholders. Never build sentences by concatenation.
- **Use WordPress APIs:** `wp_enqueue_style()` / `wp_enqueue_script()` (no hard-coded `<link>` or `<script>` in templates), `home_url()` and `get_permalink()` for URLs, `wp_get_attachment_image()` for pictures, `register_block_type()` from `block.json`, `WP_Query` and post meta APIs (no direct SQL; `$wpdb->prepare()` if SQL is unavoidable).
- **Prefix everything** (`novidea_` for PHP functions and options, `nv-` for front-end handles and classes where used, `_novidea_` for meta). Every PHP file starts with `defined( 'ABSPATH' ) || exit;`.
- **Block attributes are a contract with saved content.** Adding an attribute with a default is safe. Renaming, removing or changing the type of one needs backward-compatible reading in `render.php`, a block `deprecated` entry, or a migration of existing pages, decided with Structure and tested on a copy of the real content.
- **Keep plugin dependencies minimal and vetted.** Before adding a plugin: is it needed (could 20 lines in our plugin do it)? Is it maintained (recent releases, tested with the current WordPress), widely used, with a clean security record and a GPL-compatible licence? What does it add to the front end? Record the decision and its owner (ADR for anything significant). Never install nulled or unofficial copies.
- **Updates:** WordPress minor and security releases apply automatically. Major core releases, plugin updates and PHP upgrades go to staging first, with the Testing agent's checks, then production (WordPress ops).
- **Coding standards:** PHP follows the WordPress Coding Standards (PHPCS with `WordPress` rules), JavaScript and CSS the `@wordpress/scripts` lint rules. A `phpcs:ignore` needs a reason in the comment.
- **Environment:** `WP_DEBUG` on locally (from `blueprint.json`), off on production with no error display. `DISALLOW_FILE_EDIT` on staging and production.
- **Database changes** on staging or production go through WordPress (wp-admin, WP-CLI, the REST API), never raw SQL edits. Domain changes use a serialization-safe search-replace (`wp search-replace`).

---

## 5. Working with the agents in Claude Code

The subagents live in `.claude/agents/`. Ask for them by role, for example:

- "Use the **structure-architect** agent to plan the fields of a new FAQ block."
- "Use the **content** agent to review the Hebrew on the `/solution` page."
- "Use the **testing** agent to run the a11y and RTL checks against `npm start`."
- "Use the **seo** agent to audit the site and update the SEO plan."
- "Use the **marketing** agent to draft a LinkedIn post about the Colmobil announcement."
- "Use the **wordpress-ops** agent to compare hosting options and draft the hosting ADR."

Rules for agents:
1. Stay within your owned files and settings. Propose changes to other areas rather than making them.
2. Read the relevant `docs/` spec (and `docs/wordpress.md`) before starting. If it's missing, ask the owner instead of guessing.
3. Report exactly what you changed and what you verified. If something was skipped or failed, say so.
4. Never modify the Astro project. Read it for reference only.
