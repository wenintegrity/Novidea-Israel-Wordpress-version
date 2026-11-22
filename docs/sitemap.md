# Sitemap

**Owner:** Structure agent (`structure-architect`) · **Status:** v0.2 · **Last updated:** 19/11/2026

> **Ported from the Astro project on 22/11/2026.** Pages, URLs and navigation are unchanged. Mapping: "Content file" `pages/he/<slug>.yaml` → a WordPress page with that slug, built from Novidea blocks and edited in wp-admin (the YAML was imported once by `npm run migrate`); `src/config/nav.ts` → the WordPress menus `primary` (header) and `footer` (Appearance → Menus); the lead banner text (`src/content/shared/he.yaml`) → Appearance → Customize → "Novidea: shared content"; `noindex` → the "Novidea: search and page" panel in the page editor; `SITE_URL` / `ALLOW_INDEXING` → Settings → General (site address) / Settings → Reading ("Discourage search engines"). "Status" is the Astro site's status; each page still has to be checked on the WordPress site after migration (`Open tasks.md`, WordPress section).

Hebrew pages are served at the root, English pages under `/en/`. Slugs are English (Latin) in both locales (`AGENTS.md` §3.1). Permalink structure: `/%postname%` (no trailing slash), so URLs are identical to the Astro site.

**Content source:** "תוכן לדף אתר NOVIDEA בעברית" (Novidea, 2026). Each section of that document is one page. No other source is used.

## Pages

| Page | Hebrew URL | WordPress page (slug) · Astro source | Built from document section | Status |
|---|---|---|---|---|
| Home | `/` | the static front page (Settings → Reading → "Your homepage displays") · `pages/he/home.yaml` | כותרת ראשית, מסרים נוספים, טקסט מקוצר | **Built** |
| The problem and the solution | `/solution` | `solution` · `pages/he/solution.yaml` | הבעיה והפתרון | **Built** |
| Core capabilities | `/capabilities` | `capabilities` · `pages/he/capabilities.yaml` | יכולות ליבה: מקצה לקצה | **Built** |
| Why Novidea | `/why-novidea` | `why-novidea` · `pages/he/why-novidea.yaml` | למה נובידאה? | **Built** |
| Headquarters in Israel | `/israel` | `israel` · `pages/he/israel.yaml` | המטה כאן בישראל – יחד איתכם בכל שלב | **Built** |
| Contact (lead) | `/contact` | `contact` · `pages/he/contact.yaml` | פתיחת ליד; office location (docs/sources/office-location.he.md) | **Built**, HubSpot form loads after marketing-cookie consent; office address with Waze / Google Maps links. Lead banner hidden ("Hide the lead banner at the bottom" in the page panel) |
| Landing page (campaigns) | `/platform` | `platform` · `pages/he/platform.yaml` | The whole site on one page (docs/marketing/landing-page.he.md) | **Built**, not in the header menu (footer link "הפלטפורמה במבט אחד (דף נחיתה)"), `noindex`, not in `sitemap.xml` |
| Blog | `/blog` | | | Planned |
| Privacy policy | `/privacy` | `privacy` (set as the WordPress Privacy Policy page, Settings → Privacy) · `pages/he/privacy.yaml` | (drafted, not in the content document) | **Built**, draft: placeholders and legal review pending |
| Accessibility statement (הצהרת נגישות) | `/accessibility` | draft in `docs/drafts/accessibility.he.yaml` | (drafted, not in the content document) | **Removed** from the site on 20/11/2026 at the owner's request. **Required by law before launch** (regulation 35) |
| 404 | any unknown URL | the theme's `404.php` (Astro: `src/pages/404.astro`) | | **Built**, Hebrew, `noindex` |

English versions (`/en/…`) are planned; there are no English pages yet. In WordPress they will come from a multilingual plugin (e.g. Polylang), decided in an ADR when the work starts.

## Navigation

**Header** (menu location `primary`): logo → `/` · הפלטפורמה → `/solution` (until 22/11/2026: הבעיה והפתרון) · יכולות ליבה → `/capabilities` · למה נובידאה? → `/why-novidea` · המטה בישראל → `/israel` · button **לתיאום הדגמה** → `/contact` (label and page set in Appearance → Customize → "Novidea: shared content"). The current page is marked with `aria-current="page"`.

**Footer** (menu location `footer`): עמוד הבית plus the same links, and a legal row: מדיניות פרטיות · הגדרות עוגיות (opens the cookie settings). The הצהרת נגישות link returns with the page.

**Lead banner:** every page except `/contact` ends with the "מוכנים להוביל את הסוכנות לעידן הבא?" banner linking to `/contact`. Its text is in the Customizer; a page hides it with the "Hide the lead banner at the bottom" option in the page editor's "Novidea: search and page" panel.

## Language switcher

Hidden until English pages exist, so it never links to a 404.

## SEO per page

Every page has a unique `<title>` (≤ 60 characters) and meta description (≤ 155 characters), set in the page editor's "Novidea: search and page" panel (it counts characters and warns when too long; it doesn't block saving, so the Testing agent checks the limits). The wording follows docs/seo.md §4.1. Every page also has a canonical URL (no trailing slash), link-preview tags with a default share image, and structured data (Organization + WebSite on home, BreadcrumbList on internal pages), from the plugin's `includes/seo.php`. `robots.txt` and the sitemap (`/wp-sitemap.xml`, pages only, without `noindex` pages) are generated by WordPress. `hreflang` pairs come with the English pages. Full plan: docs/seo.md.

**Production:** not set up yet; hosting isn't chosen. The Astro site still runs at https://novidea-israel.vercel.app/ (Vercel, deployed from `wenintegrity/novidea-israel`, branch `main`). **Every temporary address, the Vercel one and any WordPress staging address, is kept out of search engines** (`noindex`). All absolute URLs come from one setting: the WordPress site address (Settings → General); no domain is hard-coded. At launch, set the site address to the final domain and turn off "Discourage search engines" (docs/seo.md §6).
