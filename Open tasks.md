# Open tasks

Everything still open on the Novidea Israel site, to be handled later. Last updated 22/11/2026.

**WordPress port (22/11/2026):** the site is moving from Astro to WordPress (ADR 0002). Sections 1 to 6 are carried over from the Astro project's list (20/11/2026) with their wording kept; file references were changed to where things live in WordPress (Astro paths are kept in brackets where useful). Items that only made sense for Astro or Vercel are marked **Superseded** with the reason. Section 7 is new: what the WordPress site needs to reach launch. Each item names its owner role (`AGENTS.md` §3).

Astro site (reference, read-only): https://novidea-israel.vercel.app/ · repo `wenintegrity/novidea-israel`. WordPress site: local only for now (`npm start`, http://127.0.0.1:9400); no staging or production yet.
To find every content placeholder: the `TODO(verify)` register below (content in the database can't carry comments), `grep -rn "TODO(verify)" wp-content`, and look for highlighted `[לעדכון: …]` text on the legal pages.

---

## 0. `TODO(verify)` register

Every unverified item on the site. In the Astro project these were YAML comments; they don't migrate, so they're tracked here. Nothing here may remain at launch (`AGENTS.md` §3.5). Owner: Content, with Novidea marketing.

| Page · block | What to verify | Details |
|---|---|---|
| `/capabilities` · features, "טיפול בפניות שירות" | "מנגנון מוצרי מלא" as written in the source; confirm the intended wording | §2 |
| `/capabilities` · features, "דשבורדים ודוחות עסקיים בזמן אמת" | The source reads "בזמן אמת על בסיס, שניתן לבנות..." and seems to be missing a word after "על בסיס" | §2 |
| `/` · text block with the video | If the video has narration, captions are required (IS 5568 / WCAG 1.2.2) | §3a |
| `/why-novidea` and `/platform` · customer quotes | Hebrew translations approved by Novidea marketing / each customer (quotes must stay faithful to the original); Mivtach Simon quote cleared for the website; Hebrew job titles confirmed if translated | §2a |
| `/privacy` · legal text | Every "[לעדכון: …]" placeholder (highlighted on the page) must be filled in; legal review of the whole text; retention period; transfer-abroad wording | §1 |
| Cookie banner and settings dialog (plugin strings) | Legal review of all consent wording (Privacy Protection Law, Amendment 13) | §1 |
| Accessibility statement draft (`docs/drafts/accessibility.he.yaml`) | Every "[לעדכון: …]" placeholder (coordinator details are mandatory); a manual screen-reader and keyboard check | §1 |

---

## 1. Legal and compliance (before launch)

- [ ] **Privacy policy: fill in the placeholders** (page `/privacy`, legal text block; Astro: `privacy.yaml`): registered company name and company number (ח.פ.), privacy contact email, mailing address, data retention period. *(Content, Novidea legal)*
- [ ] **Privacy policy: legal review** of the whole draft, especially the transfer-abroad wording (HubSpot stores data outside Israel) and marketing consent. Now also: the WordPress host (server logs with IP addresses, where the server is) and jsDelivr (ADR 0001) as processors. *(Novidea legal)*
- [ ] **Accessibility statement: put it back on the site.** Removed on 20/11/2026 at the owner's request, but Israeli law (regulation 35, IS 5568) requires it, and AGENTS.md §4.3 too. The draft is in [docs/drafts/accessibility.he.yaml](docs/drafts/accessibility.he.yaml). In WordPress: create the page `/accessibility` (Page header + Legal text blocks) from the draft, add it to the footer menu (Appearance → Menus), and add it to the e2e tests. (Astro: move the YAML back to `src/content/pages/he/accessibility.yaml`, re-add the footer link in `Footer.astro` / `ui.ts`.) *(Structure, Content, Testing)*
- [ ] **Accessibility statement: accessibility coordinator details** (in the draft above): name, phone, email, mailing address. Required by regulation 35. *(Novidea)*
- [ ] **Accessibility statement: manual check** with a screen reader (NVDA on Windows, VoiceOver on Mac/iPhone) and keyboard only; record the results and date in the statement's "איך בדקנו" section. Do it on the WordPress site, not the Astro one. *(Testing)*
- [ ] **Cookie banner wording: legal review** (consent strings in the plugin's translation files and `includes/consent.php`; Astro: `ui.ts`, `consent.*` strings). *(Novidea legal, Content)*
- [ ] **Picture licences:** confirm the brand pictures (Astro: `src/assets/brand/`; now in the media library) may be used on the Israeli site. *(Novidea marketing)*

## 2. Content questions (Novidea marketing)

- [ ] **/capabilities, "דשבורדים ודוחות עסקיים בזמן אמת":** the source text reads "בזמן אמת על בסיס, שניתן לבנות…" and seems to be missing a word after "על בסיס". Temporary wording is on the site. *(Content)*
- [ ] **/capabilities, "טיפול בפניות שירות":** confirm "מנגנון מוצרי מלא של Mail to Case" is the intended wording. *(Content)*
- [x] **Customer logos** on the home page (supplied 20/11/2026).
- [ ] **Customer logos: permission and quality.** Confirm written permission to show the מבטח סימון, קלי and GUR logos (design-system.md §10.10). The מבטח סימון and kali files are low resolution and look soft on high-density screens; ask for SVG or larger PNGs. *(Content, Novidea marketing)*
- [ ] **Brand details:** confirm the brand red (`#ED4443` vs RGB 234/68/67), approve Rubik as the Hebrew typeface, and confirm the 2022 brand book is still current. *(Design, Novidea marketing)*

## 2a. Customer quotes (/why-novidea)

- [ ] **Approve the Hebrew translations** of the four quotes (Howden Israel, Marsh Israel, Leaderim, Mivtach Simon) with Novidea marketing or each customer. Translated quotes must stay faithful to the original (customer quotes block on `/why-novidea`, also on `/platform`; source: [docs/sources/customer-quotes.md](docs/sources/customer-quotes.md)). *(Content, Novidea marketing)*
- [ ] **Mivtach Simon quote:** not marked "on website" in the source document; confirm it may be published. *(Novidea marketing)*
- [ ] **Job titles in Hebrew:** they're in English as in the source. Hebrew titles are gendered, so confirm each person's Hebrew title if you want them translated. *(Content, Novidea marketing)*
- [ ] **Hebrew spelling of names** (שי סימקין, גבי גרנט, יהודה שביט, אילנית חזן) and whether Howden and Marsh should appear in Hebrew. *(Content, Novidea marketing)*

## 3. HubSpot form (in HubSpot, not in the site code)

The site embeds form `05171e84-3123-41fc-81e0-3f8626cb3574` (portal 2799426) on `/contact`, after marketing-cookie consent (lead form block on the Contact page).

- [ ] **Fields to match the content document:** שם מלא · שם הסוכנות / בית הסוכן · תפקיד (מנכ"ל / סמנכ"ל תפעול / מנהל מערכות מידע / אחר) · דוא"ל עסקי · טלפון · גודל הסוכנות (10-30 / 30-100 / 100+ משתמשים). The form currently has first name, last name, company, email and phone, without role or agency size. *(Novidea marketing, in HubSpot)*
- [x] **Right-to-left layout and brand styling:** done by the site (Astro `hubspot-form.ts`, ported to the plugin's `assets/js/hubspot-form.js`; styles the form inside HubSpot's iframe), including the navy "השאירו פרטים" button. CSS pasted into the site wouldn't reach the iframe. Re-check on the WordPress site (§7).
- [ ] **Form language: Hebrew** in HubSpot's form settings, so error messages are in Hebrew (they're English now, e.g. "Please complete this required field."). *(Novidea marketing, in HubSpot)*
- [ ] **Keyboard and screen-reader check** of the form. *(Testing)*

## 3a. Home page video

The video is on the home page (media library; Astro: `src/assets/videos/Novidea-Hebrew-Israel.mp4`, 78.5 s, 720p, 39 MB).

- [x] **Size:** the new version (39 MB) is under GitHub's 100 MB limit. A 15-25 MB version would load faster on mobile. (In WordPress the limit that matters is the host's upload size; a smaller encode is still worth it.)
- [x] **Broken burned-in captions:** the new version has none.
- [ ] **Captions:** removed on 20/11/2026 at the owner's request (the supplied file was timed to an earlier edit). If the video has narration, Israeli accessibility rules (IS 5568 / WCAG 1.2.2) require captions before launch: a Hebrew .vtt timed to this version goes in the media library, then set it as the captions of the video in the home page's text block (Astro: `src/assets/videos/` and `captions` in home.yaml). *(Content, Novidea marketing)*
- [ ] **Confirm the video may be used on the Israeli site** (the original file name says it was made for Gallagher). *(Novidea marketing)*
- [ ] **If it moves to YouTube:** load it only after marketing consent, like the HubSpot form (design-system.md §10.24). *(Code)*

## 4. Technical (no input needed)

- [ ] ~~**Security headers** in `vercel.json`~~ **Superseded:** the WordPress site gets them at its host (§7, WordPress ops). Same list: Content-Security-Policy (allowing only jsDelivr and HubSpot's domains), HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` (AGENTS.md §4.6).
- [x] **SEO basics** (20/11/2026, docs/seo.md), on the Astro site: `noindex` on the temporary address, one site-URL setting, canonical URLs, no trailing-slash or `/index.html` duplicates, `robots.txt`, `sitemap.xml`, link previews with a share image, structured data (Organization, WebSite, BreadcrumbList), Hebrew 404 page, new titles and descriptions, favicon. **On WordPress:** ported (plugin `includes/seo.php`, theme `404.php`, share image in the theme's `assets/images/og/`), not tested yet (§7).
- [ ] **SEO at launch:** follow docs/seo.md §6: set the WordPress site address to the final domain, turn off Settings → Reading → "Discourage search engines" on production, redirect `novidea-israel.vercel.app` (and any temporary WordPress host) to the final domain, verify Search Console and Bing, submit the sitemap (`/wp-sitemap.xml`). (Astro: `SITE_URL` and `ALLOW_INDEXING=true` in Vercel Production.) *(SEO, WordPress ops)*
- [ ] **SEO, still open:** "להמשך קריאה" links between pages, Lighthouse run, approval of the share image, and Novidea's answers to docs/seo.md §9 (domain, social profiles, office details, certifications, video). *(SEO, Content, Testing, Novidea)*
- [ ] **Performance audit** with Lighthouse against the AGENTS.md budget (Performance ≥ 90, Accessibility = 100, SEO = 100, Best Practices ≥ 95). On the WordPress site, on staging with caching on. *(Testing)*
- [ ] **CI on GitHub:** build and run the Playwright tests on every push and pull request; protect `main`; add Dependabot or Renovate. Now for the WordPress repo (no remote yet): `npm run build`, PHP lint, PHPCS, JS/CSS lint, Playwright against Playground. *(Code, Testing)*
- [ ] ~~**Build size:** unused icon originals (about 2 MB) are copied into the build.~~ **Superseded:** Astro build output. In WordPress, check instead that the plugin's `assets/icons/` holds only icons in `icons.json` (§7).

## 5. Decisions needed

- [ ] **Custom domain** for the site. Everything is built from one setting (WordPress site address), so launch is: set the site address and turn off "Discourage search engines" on production (docs/seo.md §6). *(Owner, SEO)*
- [ ] **Old repo** `wenintegrity/novidea-israel-minisite` (not the deploy source; still a local remote named `minisite` in the Astro project): archive or delete. *(Owner)*
- [ ] **Duplicate picture files** in the Astro project's `src/assets/brand/`: `Group 1013.png`, `Group 1013 2.png` and `Group 1274.png` are identical; keep one. In WordPress: check the media library after migration and keep one copy. *(Content)*
- [ ] **New:** where the WordPress repo lives (GitHub organisation and name) and who gets access. *(Owner)*
- [ ] **New:** what happens to the Astro site and its Vercel project after launch (keep it only as the redirect to the final domain, docs/seo.md §6). *(Owner, WordPress ops)*

## 6. Later (roadmap)

- [ ] **Blog** section (`/blog`), with the customer-conference and Colmobil stories as first posts. In WordPress: posts (currently left out of the sitemap by `includes/seo.php`, so that changes with the blog). *(Structure, Content, Code)*
- [ ] **English version** (`/en/…`) and the language switcher; Poppins and Campton (Campton needs a web licence). In WordPress: a multilingual plugin (e.g. Polylang), decided in an ADR; code strings are already English. *(Structure, Code, Content, Design)*
- [ ] **Analytics** (GA4 or HubSpot tracking), loaded through the existing cookie-consent gate (`window.nvConsent`). No tracking plugin that loads before consent. *(Marketing, Code, WordPress ops)*
- [ ] **Bootstrap 6** migration once it's stable (ADR 0001; `bootstrap-v5-v6-migration` skill). *(Code, Design)*

---

## 7. WordPress: to reach launch (new, 22/11/2026)

Priority: **P1** before staging (now) · **P2** before launch · **P3** after launch.

### Code
- [ ] **P1 Migration output complete:** `npm run reset`, `npm start`, `npm run migrate` on a clean site creates every page in `docs/sitemap.md` with all blocks and fields, the media (with alt text), both menus, the Customizer values, the SEO fields, `noindex` on `/platform`, the lead banner hidden on `/contact`, `/privacy` as the Privacy Policy page and the static front page. Fix what Testing finds in the page-by-page comparison.
- [ ] **P1 Translations:** Hebrew for every theme and plugin string, PHP and editor JavaScript; document the workflow in `docs/wordpress.md` §6 (template, `.po` or not, `make-php` / `make-json`).
- [ ] **P1 Code checks:** PHPCS with the WordPress Coding Standards (config + npm script), `wp-scripts lint-js` / `lint-style` scripts, PHP lint. Fix or justify every finding.
- [ ] **P2 Lean WordPress:** turn off what the site doesn't use and could leak or be indexed: comments, feeds, author and date archives, attachment pages (redirect to the file or parent), `?p=` / `?page_id=` duplicates (docs/seo.md T-5), the REST users endpoint for visitors, XML-RPC. Check none of them appear in `/wp-sitemap.xml`.
- [ ] **P2 Page-specific share image** (page meta, falls back to the default) (docs/seo.md T-8, with Structure).
- [ ] **P2 Icon preparation:** port the Astro `npm run icons` script (trim, centre, 88% fill) or document the manual steps; check `assets/icons/` holds only catalogued icons.
- [ ] **P3 Inline links in the text block** (docs/seo.md §4.3, optional, with Structure) and an editor helper for LTR isolates (`docs/content-model.md`).

### Testing
- [ ] **P1 Port the Playwright + axe-core tests** from the Astro project (`tests/e2e/site.spec.ts`, `consent.spec.ts`, `hubspot-form.spec.ts`, `animations.spec.ts`) to run against the Playground site (`npm start`), with reduced motion by default.
- [ ] **P1 Verify the migration page by page** against the Astro site: visual comparison in `he` at 375, 768 and 1440 px, plus text, links, alt text, headings, titles and descriptions. Report every difference to Code or Content.
- [ ] **P2 SEO checks** (docs/seo.md T-17), including "Discourage search engines" on → `noindex` everywhere, and robots.txt without `Disallow: /`.
- [ ] **P2 HubSpot form on WordPress:** consent gate, RTL styling inside the iframe, "השאירו פרטים" button, error states (§3).
- [ ] **P2 Lighthouse** on staging with caching on, against the budget (§4).
- [ ] **P2 Block editor checks:** every block can be added, edited and previewed; an empty block opens its form; editing doesn't change other pages.

### Content
- [ ] **P1 Editor UI in Hebrew:** check every label, help text and message in the block forms, the "Novidea: search and page" panel and the Customizer section reads naturally for Novidea marketing.
- [ ] **P1 Migrated text check:** Hebrew punctuation (gershayim, geresh), LTR isolates around numbers (`⁦360°⁩`, `⁦100+⁩`), inline links in legal text, quotes without quotation marks, line breaks.
- [ ] **P2 Short Hebrew editor guide** for Novidea marketing (how to edit a page, menus, banner, SEO fields; the glossary and verification rules), with Code. Base it on `docs/wordpress.md` §5.

### SEO
- [ ] **P2 Re-audit on staging** against the Astro baseline (docs/seo.md §1): nothing worse than the Astro site.
- [ ] **P2 Launch checklist** (docs/seo.md §6), run with WordPress ops on launch day; Search Console and Bing at launch.

### WordPress ops
- [ ] **P1 Choose hosting** and record it in an ADR: MySQL or MariaDB, PHP 8.3, HTTPS, daily off-site backups with tested restore, a staging site, SSH and WP-CLI, page caching, WebP support in the image library (GD or Imagick), location and latency for Israeli visitors, cost.
- [ ] **P1 Deployment process** for the theme and plugin (with `build/`) from git to staging and production, and the one-time move of content (database and uploads) from the migrated site to the host; document it in `docs/wordpress.md` §8.
- [ ] **P1 Staging site:** password-protected, `X-Robots-Tag: noindex`, "Discourage search engines" on.
- [ ] **P2 Security hardening:** headers at the host (CSP allowing jsDelivr and HubSpot, HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`), login rate-limiting, 2FA for administrators, accounts and roles (Novidea marketing as Editor, few Administrators, no shared accounts), `DISALLOW_FILE_EDIT`, debug display off, XML-RPC off. Any plugin needed for this goes through the `AGENTS.md` §4.7 review.
- [ ] **P2 Updates policy:** minor and security releases automatic; major core, plugin and PHP updates on staging first; who checks and how often.
- [ ] **P2 Backups:** schedule, off-site copy, a tested restore before launch.
- [ ] **P2 Caching and CDN:** page caching for visitors (not for logged-in editors), static asset caching headers, compression. Check the consent banner and HubSpot form still work with caching on.
- [ ] **P2 Image optimisation check on the host:** WebP sub-sizes are generated, `srcset` works, the hero picture stays small (§1 audit: 53 KB).
- [ ] **P2 Email:** WordPress sends password resets and admin notices; make sure they're delivered (host mail or SMTP), from a Novidea address.
- [ ] **P2 Launch:** domain and HTTPS, site address, "Discourage search engines" off, 301 redirects from `novidea-israel.vercel.app` and any temporary host, Search Console (with SEO).
- [ ] **P3 Monitoring:** uptime and PHP error alerts, who gets them.

### Structure
- [ ] **P2 Accessibility statement page** in the sitemap and footer menu (§1).
- [ ] **P3 English version plan** (Polylang or another multilingual plugin, ADR), hreflang mapping in `docs/sitemap.md` (§6).

### Marketing
- [ ] **P2 Analytics choice** (GA4 or HubSpot tracking) and what to measure, behind consent (§6, docs/seo.md §7).

## Local setup note

- Node.js was installed portably in `%LOCALAPPDATA%\Programs\node\node-v26.13.1-win-x64` and isn't on the system PATH. Add that folder to PATH (or install Node.js LTS normally) to run `npm` in a new terminal. The WordPress project needs only Node.js (WordPress Playground); no PHP or MySQL install.
