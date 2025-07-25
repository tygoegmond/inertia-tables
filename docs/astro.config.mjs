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
				{ label: 'Installation', slug: '01-installation' },
				{ label: 'Getting Started', slug: '02-getting-started' },
				{
					label: 'Columns',
					items: [
						{ label: 'Getting Started', slug: '03-columns/01-getting-started' },
						{ label: 'Text Column', slug: '03-columns/02-text-column' },
						{ label: 'Custom Columns', slug: '03-columns/03-custom-columns' },
					],
				},
				{
					label: 'Actions',
					items: [
						{ label: 'Getting Started', slug: '04-actions/01-getting-started' },
						{ label: 'Row Actions', slug: '04-actions/02-row-actions' },
						{ label: 'Bulk Actions', slug: '04-actions/03-bulk-actions' },
						{ label: 'Header Actions', slug: '04-actions/04-header-actions' },
						{ label: 'Custom Actions', slug: '04-actions/05-custom-actions' },
					],
				},
				{ label: 'Search & Filtering', slug: '05-search-and-filtering' },
				{ label: 'Sorting & Pagination', slug: '06-sorting-pagination' },
				{ label: 'React Integration', slug: '07-react-integration' },
				{ label: 'Advanced Usage', slug: '08-advanced-usage' },
				{ label: 'API Reference', slug: '09-api-reference' },
				{ label: 'Examples', slug: '10-examples' },
			],
		}),
	],
});
