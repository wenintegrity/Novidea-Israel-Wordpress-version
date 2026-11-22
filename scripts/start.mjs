// `npm start`: the local site on http://127.0.0.1:9400, logged in as admin.
// Extra arguments pass through to the Playground CLI, e.g. `npm start -- --xdebug`.
import path from 'node:path';
import { root, runPlayground } from './playground.mjs';

const code = await runPlayground('server', [
	'--blueprint', path.join(root, 'blueprint.json'),
	'--login',
	'--port', '9400',
	...process.argv.slice(2),
]);
process.exit(code);
