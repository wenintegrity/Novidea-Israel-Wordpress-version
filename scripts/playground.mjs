// Shared by start.mjs and the migration: runs the WordPress Playground CLI against the local site.
//
//   ./wordpress                         WordPress core, uploads and the SQLite database (disposable,
//                                       not in git). The first run downloads WordPress into it.
//   ./wp-content/themes/novidea-israel  our theme   } mounted into the site, so edits here show up
//   ./wp-content/plugins/novidea-blocks our plugin  } immediately (this is the code in git)
import { spawn } from 'node:child_process';
import { existsSync, mkdirSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
export const wpDir = path.join(root, 'wordpress');

const cli = path.join(root, 'node_modules', '@wp-playground', 'cli', 'wp-playground.js');

/** Our code, mounted over the matching folders of the site. */
const codeMounts = [
	['wp-content/themes/novidea-israel', '/wordpress/wp-content/themes/novidea-israel'],
	['wp-content/plugins/novidea-blocks', '/wordpress/wp-content/plugins/novidea-blocks'],
];

export function isInstalled() {
	return existsSync(path.join(wpDir, 'wp-load.php'));
}

/**
 * Runs a Playground CLI command ('server', 'run-blueprint', …) with the site and our code mounted.
 * @param {string} command
 * @param {string[]} extra Additional CLI arguments.
 * @returns {Promise<number>} Exit code.
 */
export function runPlayground(command, extra = []) {
	if (!existsSync(cli)) {
		console.error('Playground CLI not found. Run `npm install` first.');
		process.exit(1);
	}
	const installed = isInstalled();
	mkdirSync(wpDir, { recursive: true });

	const args = [
		cli,
		command,
		'--mount-dir-before-install', wpDir, '/wordpress',
		// "install-from-existing-files-if-needed" fails on an empty folder, hence the check.
		'--wordpress-install-mode', installed ? 'install-from-existing-files-if-needed' : 'download-and-install',
		...codeMounts.flatMap(([host, vfs]) => ['--mount-dir', path.join(root, host), vfs]),
		...extra,
	];

	if (!installed) console.log('First run: downloading WordPress into ./wordpress');
	return new Promise((resolve) => {
		const child = spawn(process.execPath, args, { stdio: 'inherit', cwd: root });
		child.on('exit', (code) => resolve(code ?? 0));
	});
}
