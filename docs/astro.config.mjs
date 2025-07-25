// @ts-check
import { defineConfig } from 'astro/config';
import starlight from '@astrojs/starlight';
import starlightThemeRapide from 'starlight-theme-rapide'

// https://astro.build/config
export default defineConfig({
	integrations: [
		starlight({
            plugins: [starlightThemeRapide()],
			title: 'Inertia Tables',
			social: [
				{ icon: 'github', label: 'GitHub', href: 'https://github.com/tygoegmond/inertia-tables' }
			],
			sidebar: [
				{ label: 'Installation', slug: 'installation' },
				{ label: 'Getting Started', slug: 'getting-started' },
				{
					label: 'Columns',
					items: [
						{ label: 'Getting Started', slug: 'columns/getting-started' },
						{ label: 'Text Column', slug: 'columns/text-column' },
					],
				},
                { label: 'Actions', slug: 'actions' },
				// {
				// 	label: 'Actions',
				// 	items: [
				// 		{ label: 'Getting Started', slug: 'actions/getting-started' },
				// 		{ label: 'Row Actions', slug: 'actions/row-actions' },
				// 		{ label: 'Bulk Actions', slug: 'actions/bulk-actions' },
				// 		// { label: 'Header Actions', slug: 'actions/header-actions' },
				// 	],
				// },
			],
		}),
	],
});
