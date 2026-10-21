/**
 * Playwright: end-to-end checks against a running site. Locally that is the
 * XAMPP install; set BASE_URL for another one (e.g. Playground on
 * http://127.0.0.1:9400/). Chromium only for now.
 */

import { defineConfig, devices } from '@playwright/test';

export default defineConfig( {
	testDir: 'tests/e2e',
	forbidOnly: !! process.env.CI,
	reporter: process.env.CI ? 'github' : 'list',
	use: {
		baseURL: process.env.BASE_URL || 'http://localhost/portfolio/',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
	],
} );
