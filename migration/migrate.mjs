// `npm run migrate [-- <path to the Astro project>]`
//
// One-time import of the Astro site's content into the local WordPress site. Default source:
// ../Novidea-Israel (next to this project). Safe to run again: pages, media and menus are matched
// and updated, not duplicated. After the migration, WordPress is where content is edited.
//
//   1. Reads src/content/pages/he/*.yaml, src/content/shared/he.yaml and src/assets/icons/icons.yaml.
//   2. Writes the brand icon catalogue (wp-content/plugins/novidea-blocks/assets/icons/icons.json).
//   3. Writes migration/out/content.json: pages as Novidea blocks, menus, shared texts, media list.
//   4. Runs a Playground blueprint: Hebrew language, theme + plugin activation, site settings, then
//      migration/import.php, which creates media library items, pages, menus and Customizer values.
import { existsSync, mkdirSync, readdirSync, readFileSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import YAML from 'yaml';
import { root, runPlayground } from '../scripts/playground.mjs';

const source = path.resolve(process.argv[2] ?? path.join(root, '..', 'Novidea-Israel'));
const assets = path.join(source, 'src', 'assets');
const pagesDir = path.join(source, 'src', 'content', 'pages', 'he');
if (!existsSync(pagesDir)) {
	console.error(`Astro content not found in ${source}. Pass its path: npm run migrate -- <path>`);
	process.exit(1);
}
const outDir = path.join(root, 'migration', 'out');
mkdirSync(outDir, { recursive: true });

const readYaml = (file) => YAML.parse(readFileSync(file, 'utf8'));

// ---------- Brand icon catalogue ----------
const iconDir = path.join(assets, 'icons');
const iconFiles = readdirSync(iconDir).filter((f) => f.toLowerCase().endsWith('.png'));
const iconMeta = readYaml(path.join(iconDir, 'icons.yaml')) ?? {};
const icons = {};
for (const id of [...new Set(iconFiles.map((f) => f.replace(/(\.on-dark)?\.png$/i, '')))].sort()) {
	icons[id] = {
		he: iconMeta[id]?.he ?? '',
		en: iconMeta[id]?.en ?? '',
		category: iconMeta[id]?.category ?? 'misc',
		directional: iconMeta[id]?.directional === true,
		light: iconFiles.includes(`${id}.png`),
		dark: iconFiles.includes(`${id}.on-dark.png`),
	};
}
writeFileSync(
	path.join(root, 'wp-content', 'plugins', 'novidea-blocks', 'assets', 'icons', 'icons.json'),
	JSON.stringify(icons, null, '\t') + '\n'
);
console.log(`Icons: ${Object.keys(icons).length} in icons.json`);

// ---------- Media: YAML paths ("../../../assets/images/x.png") → paths under src/assets ----------
const media = new Map(); // relative path → { file, alt }
function mediaRef(yamlPath, alt = '') {
	const abs = path.resolve(pagesDir, yamlPath);
	const rel = path.relative(assets, abs).split(path.sep).join('/');
	if (rel.startsWith('..') || !existsSync(abs)) throw new Error(`Media not found: ${yamlPath}`);
	if (!media.has(rel)) media.set(rel, { file: rel, alt });
	return { $media: rel };
}
const picture = (p) => (p ? { ...mediaRef(p.src, p.alt), alt: p.alt ?? '' } : {});
const videoFile = (name) => mediaRef(path.relative(pagesDir, path.join(assets, 'videos', name)));

// ---------- Sections → blocks ----------
const pick = (obj, keys) => Object.fromEntries(keys.filter((k) => obj[k] !== undefined).map((k) => [k, obj[k]]));

function block(section) {
	const anchor = section.id ? { anchor: section.id } : {};
	switch (section.type) {
		case 'text': {
			const attrs = { ...anchor, ...pick(section, ['title', 'paragraphs', 'surface']) };
			if (section.picture) attrs.picture = picture(section.picture);
			if (section.video) {
				const v = section.video;
				attrs.video = { title: v.title };
				if (v.file) attrs.video.file = videoFile(v.file);
				if (v.poster) attrs.video.poster = mediaRef(v.poster);
				if (v.captions) attrs.video.captions = videoFile(v.captions);
			}
			return { name: 'novidea/text', attrs };
		}
		case 'features':
			return {
				name: 'novidea/features',
				attrs: {
					...anchor,
					...pick(section, ['title', 'intro', 'columns', 'surface']),
					...(section.picture ? { picture: picture(section.picture) } : {}),
					items: section.items.map((i) => ({
						icon: i.icon ?? '',
						title: i.title ?? '',
						body: i.body ?? '',
						soon: i.soon === true,
						soonNote: i.soonNote ?? '',
					})),
				},
			};
		case 'names':
			return {
				name: 'novidea/names',
				attrs: {
					...anchor,
					...pick(section, ['title', 'more', 'surface']),
					names: section.names.map((n) =>
						typeof n === 'string' ? { name: n, logo: {} } : { name: n.name, logo: { ...mediaRef(n.logo, n.name), alt: n.name } }
					),
				},
			};
		case 'links':
			return {
				name: 'novidea/links',
				attrs: {
					...anchor,
					...pick(section, ['title', 'surface']),
					items: section.items.map((i) => ({ icon: i.icon ?? '', title: i.title, body: i.body, href: i.href })),
				},
			};
		case 'quotes':
			return { name: 'novidea/quotes', attrs: { ...anchor, ...pick(section, ['title', 'surface', 'items']) } };
		case 'location':
			return {
				name: 'novidea/location',
				attrs: { ...anchor, ...pick(section, ['title', 'place', 'address', 'directions', 'mapQuery', 'surface']) },
			};
		case 'prose':
			return {
				name: 'novidea/prose',
				attrs: {
					...anchor,
					sections: section.sections.map((s) => ({
						title: s.title,
						paragraphs: s.paragraphs ?? [],
						bullets: s.bullets ?? [],
						after: s.after ?? [],
					})),
				},
			};
		case 'leadForm':
			return {
				name: 'novidea/lead-form',
				attrs: {
					...anchor,
					...pick(section, ['title', 'intro']),
					...(section.hubspot
						? { portalId: section.hubspot.portalId, formId: section.hubspot.formId, region: section.hubspot.region ?? 'na1' }
						: {}),
					fields: section.fields ?? [],
				},
			};
		default:
			throw new Error(`Unknown section type "${section.type}"`);
	}
}

// ---------- Pages ----------
// WordPress page titles = the menu labels (src/i18n/ui.ts), so menus and breadcrumbs read the same.
const titles = {
	home: 'עמוד הבית',
	solution: 'הבעיה והפתרון',
	capabilities: 'יכולות ליבה',
	'why-novidea': 'למה נובידאה?',
	israel: 'המטה בישראל',
	contact: 'השאירו פרטים',
	platform: 'הפלטפורמה במבט אחד',
	privacy: 'מדיניות פרטיות',
};

const pages = readdirSync(pagesDir)
	.filter((f) => f.endsWith('.yaml'))
	.map((f) => {
		const slug = f.replace(/\.yaml$/, '');
		const data = readYaml(path.join(pagesDir, f));
		const blocks = [];
		if (data.hero) {
			const h = data.hero;
			blocks.push({
				name: 'novidea/hero',
				attrs: {
					titleStrong: h.titleStrong,
					titleRest: h.titleRest,
					lead: h.lead,
					primaryCta: h.primaryCta,
					secondaryCta: h.secondaryCta ?? { label: '', href: '' },
					picture: picture(h.picture),
					// Internal pages with a hero get the red line of the page headers ([slug].astro)
					redLine: slug !== 'home',
				},
			});
		} else if (data.header) {
			blocks.push({
				name: 'novidea/page-header',
				attrs: { title: data.header.title, intro: data.header.intro ?? [], picture: picture(data.header.picture) },
			});
		}
		for (const section of data.sections ?? []) blocks.push(block(section));
		return {
			slug,
			title: titles[slug] ?? data.header?.title ?? `${data.hero?.titleStrong} ${data.hero?.titleRest}`,
			seo: { title: data.seo.title, description: data.seo.description, noindex: data.seo.noindex === true },
			hideLeadCta: data.leadCta === false,
			blocks,
		};
	})
	// Home first, then the menu order, so page IDs (and the Pages list) follow the site's structure
	.sort((a, b) => Object.keys(titles).indexOf(a.slug) - Object.keys(titles).indexOf(b.slug));

const shared = readYaml(path.join(source, 'src', 'content', 'shared', 'he.yaml'));

const content = {
	source,
	site: { name: 'נובידאה', language: 'he_IL' },
	pages,
	media: [...media.values()],
	menus: {
		// Header: src/config/nav.ts primaryNav. Footer: home + primary + platform + contact (Footer.astro).
		primary: [{ page: 'solution' }, { page: 'capabilities' }, { page: 'why-novidea' }, { page: 'israel' }],
		footer: [
			{ page: 'home' },
			{ page: 'solution' },
			{ page: 'capabilities' },
			{ page: 'why-novidea' },
			{ page: 'israel' },
			{ page: 'platform', title: 'הפלטפורמה במבט אחד (דף נחיתה)' },
			{ page: 'contact' },
		],
	},
	themeMods: {
		novidea_header_cta_label: 'השאירו פרטים',
		novidea_header_cta_page: { page: 'contact' },
		novidea_lead_title: shared.leadCta.title,
		novidea_lead_body: shared.leadCta.body,
		novidea_lead_button_label: shared.leadCta.button.label,
		novidea_lead_button_page: { page: shared.leadCta.button.href.replace(/^\//, '') || 'home' },
		novidea_footer_about: shared.footerAbout,
	},
	privacyPage: 'privacy',
	frontPage: 'home',
};
writeFileSync(path.join(outDir, 'content.json'), JSON.stringify(content, null, '\t'));
console.log(`Content: ${pages.length} pages, ${media.size} media files → migration/out/content.json`);

// ---------- Starter page patterns (plugin patterns/*.html) ----------
// Each page type of the Astro site becomes a pattern offered when a new page is created. Real copy
// stays as example text; pictures are left empty (media IDs differ between sites).
function stripMedia(value) {
	if (Array.isArray(value)) return value.map(stripMedia);
	if (!value || typeof value !== 'object') return value;
	if (value.$media) return {};
	const out = {};
	for (const [k, v] of Object.entries(value)) {
		if (v && typeof v === 'object' && v.$media) {
			if (!['file', 'poster', 'captions'].includes(k)) out[k] = {}; // pictures/logos: empty; video files: dropped
			continue;
		}
		out[k] = stripMedia(v);
	}
	return out;
}

/** Same escaping as WordPress's serialize_block_attributes(). */
function serializeAttributes(attrs) {
	return JSON.stringify(attrs)
		.replace(/--/g, '\\u002d\\u002d')
		.replace(/</g, '\\u003c')
		.replace(/>/g, '\\u003e')
		.replace(/&/g, '\\u0026')
		.replace(/\\"/g, '\\u0022');
}

const patternSources = {
	home: 'home',
	landing: 'platform',
	internal: 'solution',
	contact: 'contact',
	legal: 'privacy',
};
const patternDir = path.join(root, 'wp-content', 'plugins', 'novidea-blocks', 'patterns');
mkdirSync(patternDir, { recursive: true });
for (const [pattern, slug] of Object.entries(patternSources)) {
	const page = pages.find((p) => p.slug === slug);
	let blocks = page.blocks.map((b) => ({ ...b, attrs: stripMedia(b.attrs) }));
	if (pattern === 'legal') {
		// Two example sections are enough to show the format
		blocks = blocks.map((b) =>
			b.name === 'novidea/prose' ? { ...b, attrs: { ...b.attrs, anchor: '', sections: b.attrs.sections.slice(0, 2) } } : b
		);
	}
	const markup = blocks.map((b) => `<!-- wp:${b.name} ${serializeAttributes(b.attrs)} /-->`).join('\n\n');
	writeFileSync(path.join(patternDir, `${pattern}.html`), markup + '\n');
}
console.log(`Patterns: ${Object.keys(patternSources).join(', ')} → wp-content/plugins/novidea-blocks/patterns/`);

// ---------- Run the import inside WordPress ----------
const blueprint = {
	$schema: 'https://playground.wordpress.net/blueprint-schema.json',
	features: { networking: true },
	steps: [
		{ step: 'setSiteLanguage', language: 'he_IL' },
		{ step: 'activateTheme', themeFolderName: 'novidea-israel' },
		{ step: 'activatePlugin', pluginPath: 'novidea-blocks/novidea-blocks.php' },
		{
			step: 'runPHP',
			code: "<?php require '/novidea-migration/import.php';",
		},
	],
};
const blueprintFile = path.join(outDir, 'migrate-blueprint.json');
writeFileSync(blueprintFile, JSON.stringify(blueprint, null, '\t'));

const logFile = path.join(outDir, 'import-log.txt');
writeFileSync(logFile, '');
console.log('Importing into WordPress (Hebrew language pack, media, pages, menus)…');
const code = await runPlayground('run-blueprint', [
	'--blueprint', blueprintFile,
	'--mount-dir', path.join(root, 'migration'), '/novidea-migration',
	'--mount-dir', assets, '/novidea-assets',
]);
const log = readFileSync(logFile, 'utf8');
console.log(log || '(the importer wrote no log)');
if (code !== 0 || !log.includes('DONE')) {
	console.error(`Migration failed (exit code ${code}).`);
	process.exit(code || 1);
}
console.log('Migration finished. Start the site with `npm start`.');
