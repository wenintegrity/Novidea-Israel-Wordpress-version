# Content model

**Owner:** Structure agent (`structure-architect`) · **Status:** v0.2 · **Last updated:** 19/11/2026

> **Ported from the Astro project on 22/11/2026.** The model (page parts, blocks, fields, rules) is unchanged; only where it lives changed. Mapping: page YAML files (`src/content/pages/<locale>/<slug>.yaml`) → WordPress pages built from Novidea blocks; `type: <name>` → the block `novidea/<name>` (`leadForm` → `novidea/lead-form`, the `hero` and `header` keys → the blocks `novidea/hero` and `novidea/page-header`); the Zod schemas in `src/content.config.ts` → each block's `blocks/<name>/block.json` (attribute names and defaults) plus the editor form in `src/schemas.js` of the `novidea-blocks` plugin; `seo.*` and `leadCta` → page meta in the "Novidea: search and page" panel; `src/content/shared/<locale>.yaml` → the Customizer; `src/i18n/ui.ts` → translation files. The Astro build failed on a missing or invalid field; WordPress doesn't, so the rules below are checked by the editor form where it can and by the Testing agent otherwise.

**Verification rule:** any claim, name or wording that hasn't been confirmed is marked `TODO(verify)`. Content in the WordPress database can't carry comments, so each one is listed in `Open tasks.md` with the page, block and wording; legal pages keep their highlighted `[לעדכון: …]` placeholders in the text. The list must be empty, and no placeholder may remain on any page, before launch (`AGENTS.md` §3.5). The YAML `# TODO(verify)` comments in the Astro project weren't migrated (comments aren't data); they are carried over in `Open tasks.md`.

---

## Pages: WordPress pages

Each page is a WordPress page whose slug is its URL (`/<slug>`, permalinks `/%postname%`). The home page is the static front page (Settings → Reading). Adding a page means creating it in wp-admin (Pages → Add New) with Novidea blocks, then adding it to the `primary` or `footer` menu (Appearance → Menus) if it belongs there. A new page that isn't in `docs/sitemap.md` needs the Structure agent first.

| Field | Where in WordPress | Required | Notes |
|---|---|---|---|
| `seo.title` | Page panel → "Search title" (`_novidea_seo_title`) | yes | ≤ 60 characters, unique per page. Empty falls back to the page title, so fill it |
| `seo.description` | Page panel → "Search description" (`_novidea_seo_description`) | yes | ≤ 155 characters |
| `seo.noindex` | Page panel → "Keep out of search results (noindex)" (`_novidea_noindex`) | no | Keeps the page out of search and the sitemap (campaign pages such as `/platform`) |
| `hero` | `novidea/hero` block, first on the page | home and landing pages (instead of `header`) | `titleStrong`, `titleRest`, `lead`, `primaryCta {label, href}`, `secondaryCta` (optional), `picture` (optional), `redLine` (red line on landing-page heroes) |
| `header` | `novidea/page-header` block, first on the page | internal pages | `title` (the page's h1), `intro[]` (paragraphs), `picture` (optional) |
| `sections[]` | the Novidea blocks below the hero or header, in order | no | Blocks (below) |
| `leadCta` | Page panel → "Hide the lead banner at the bottom" (`_novidea_hide_lead_cta`) | no | Default: banner shown. Hidden on `/contact` |

The page's WordPress title is its name in wp-admin and the fallback search title. The visible h1 comes from the hero or page header block.

## Blocks

Every block except the hero and page header has `anchor` (the Astro `id`; becomes the section anchor, English letters, numbers and dashes). `surface` is `light`, `subtle`, `tint` or `navy` (design-system.md §3.3). Each block is `wp-content/plugins/novidea-blocks/blocks/<name>/` (`block.json`, `render.php`, `style.css`), and its markup is identical to the Astro component it replaces.

| Block | Fields | Astro component |
|---|---|---|
| `novidea/text` | `title` (optional), `paragraphs[]`, `picture` **or** `video` (optional), `surface` (default light). `video`: `title` (accessible name), the video file, captions (.vtt) and poster, all from the media library; without a file, the "coming soon" placeholder shows | `TextBlock.astro` |
| `novidea/features` | `title`, `intro` (optional), `columns` 2 or 3, `surface`, `picture` (optional), `items[]`: `{icon, title, body, soon, soonNote}`. An item needs a title or a body. `soon: true` labels the whole item "בקרוב"; `soonNote` adds an upcoming extra to an available item | `FeaturesBlock.astro` |
| `novidea/names` | `title`, `names[]`: each a name, with an optional `logo` from the media library (the name becomes the alt text); `more` (e.g. "ועוד"), `surface` (default subtle) | `NamesBlock.astro` |
| `novidea/links` | `title` (optional), `items[]`: `{icon, title, body, href}`, `surface` | `LinksBlock.astro` |
| `novidea/lead-form` | `title` (optional, default "השאירו פרטים"), `intro` (optional), `portalId`, `formId`, `region` (default `na1`; in Astro these were under `hubspot`), `fields[]` (shown only without a HubSpot form) | `LeadForm.astro` |
| `novidea/location` | `title`, `place` (building), `address`, `directions` (optional), `mapQuery` (search text for Waze / Google Maps), `surface` (default light). Plain map links, no embedded map | `LocationBlock.astro` |
| `novidea/quotes` | `title`, `surface` (default navy), `items[]` (at least 2): `{quote, name, role, company}`. No quotation marks in `quote` (CSS adds them; the editor says so) | `QuotesBlock.astro` |
| `novidea/prose` | `sections[]`: `{title, paragraphs[], bullets[], after[]}`. Long-form text for legal pages. Inline links `[label](href)`; `[label](#cookie-settings)` opens the cookie settings; `[לעדכון: …]` is highlighted as a placeholder | `ProseBlock.astro` |

The hero and page header (above) are blocks too: `novidea/hero` (`home/Hero.astro`) and `novidea/page-header` (`PageHeader.astro`).

**Heading order:** a block's `title` is an `h2` and its items are `h3`. Without a block title, items become `h2`, so headings never skip a level.

**Icons:** `icon` values are IDs from the brand icon library (`wp-content/plugins/novidea-blocks/assets/icons/`, names in `icons.json`). The editor offers only IDs that exist.

**Pictures:** a picture is a media library item chosen in the block's picture field, with the block's own alt text (it describes the picture in this context). Alt text is required; leave it empty only for purely decorative pictures.

**Mixed direction text:** wrap numbers with signs or units in Unicode LTR isolates (U+2066 … U+2069) inside the text: `⁦360°⁩`, `⁦100+⁩`. In YAML these were written as `"⁦360°⁩"`; in the editor they are invisible characters, so copy them from an existing block or ask Code for a helper. Otherwise the sign moves to the wrong side in RTL.

**Changing a block's fields** (adding, renaming or removing an attribute in `block.json`) changes saved content in the database. Plan it with Code: keep old attributes readable, or add a block deprecation or a migration, so existing pages don't break (`AGENTS.md` §4.7).

---

## Shared content: Appearance → Customize → "Novidea: shared content"

Set in the theme's `inc/customizer.php` (replaces `src/content/shared/<locale>.yaml`).

| Field | Notes |
|---|---|
| Header button | Label and target page ("השאירו פרטים" → `/contact` on the Astro site) |
| Lead banner | `title`, `body`, button label: the banner at the bottom of every page except those that hide it |
| Footer about | One sentence under the footer logo |

## UI strings: translation files

Menu button names, skip link, footer headings, "בקרוב" badge, form placeholder text, cookie banner and dialog. Source strings are English in the code (`__()`, `esc_html__()`, `_x()`); Hebrew is in the translation files: the theme's `languages/` (text domain `novidea-israel`) and the plugin's `languages/` (text domain `novidea-blocks`, PHP and JavaScript strings). Owned by Content (Hebrew values) and Code (source strings). Menu labels are menu items in Appearance → Menus, not strings.
