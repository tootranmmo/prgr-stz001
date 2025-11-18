=== Programmatic SEO ===
Contributors: tootranmmo
Donate link: https://github.com/tootranmmo
Tags: seo, meta-tags, sitemap, structured-data, schema, page-generator, template, automation
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.4
Stable tag: 2.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced SEO plugin with template management, automatic page generation, meta automation, schema markup, internal linking, and analytics.

== Description ==

Programmatic SEO is a comprehensive SEO plugin that combines traditional SEO optimization with advanced automation features. Generate thousands of SEO-optimized pages from templates and data sources with automatic meta tag generation, schema markup, internal linking, and performance analytics.

### Key Features

* **Template Management** - Create dynamic templates with variable substitution for bulk page generation
* **Data Source Integration** - Connect CSV files, JSON data, or API endpoints as data sources
* **Auto Page Generation** - Automatically create hundreds or thousands of pages from templates and data
* **SEO Meta Automation** - Automatic title, description, and keyword generation
* **Schema Markup Generator** - Support for Product, Event, Recipe, Article, and BlogPosting schemas
* **Internal Linking System** - Intelligent internal linking based on content analysis
* **Meta Tags Management** - Automatically generate and output SEO meta tags including description, keywords, and canonical URLs
* **XML Sitemap Generation** - Create XML sitemaps for posts, pages, categories, tags, and generated pages
* **Structured Data (JSON-LD)** - Output structured data markup for better search engine understanding
* **Social Media Meta Tags** - Support for Open Graph, Twitter Cards, LinkedIn, and Pinterest meta tags
* **Performance Analytics** - Track page views, engagement, and calculate SEO scores
* **Admin Dashboard** - Easy-to-use settings panel to enable/disable features and configure options

### Plugin Features

**Template Management:**
- Create dynamic templates with variable substitution
- Support for title, description, keywords, content, and schema templates
- Template duplication and cloning
- Flexible variable system {{variable}}

**Data Sources:**
- CSV file parsing and import
- JSON data support
- API integration (GET/POST)
- Custom field mapping
- Data validation and error handling

**Auto Page Generation:**
- Bulk page creation from templates + data
- Duplicate detection and prevention
- Automatic featured image handling
- Source data tracking
- Progress tracking and reporting

**SEO Meta Automation:**
- Auto-generate SEO titles (optimized 50-60 chars)
- Auto-generate descriptions (optimized 120-160 chars)
- Automatic keyword extraction from content
- Batch processing for existing posts
- Meta quality checking and scoring

**Schema Markup:**
- BlogPosting schema for posts
- WebPage schema for pages
- Product schema with pricing and ratings
- Event schema with location and organizer
- Recipe schema with ingredients and instructions
- Article schema
- Organization schema
- BreadcrumbList schema
- JSON-LD format for search engines

**Internal Linking:**
- Automatic internal link creation based on keywords
- Related post discovery
- Link management and editing
- Link statistics and reporting
- Batch link generation

**Dynamic Sitemap:**
- Posts sitemap
- Pages sitemap
- Categories sitemap
- Tags sitemap
- Generated pages sitemap (auto-updated)
- Automatic robots.txt integration

**Analytics:**
- Page view tracking
- Engagement rate calculation
- SEO score calculation (0-100)
- Top performing posts ranking
- Dashboard statistics
- Chart data for analysis

**Meta Tags:**
- Automatic meta description generation
- Custom meta keywords support
- Canonical URL management
- Robots meta tags

**Social Media Integration:**
- Open Graph meta tags (Facebook, Pinterest)
- Twitter Card support
- LinkedIn meta tags
- Pinterest optimization
- Custom Twitter handle support

== Installation ==

1. Upload the `programmatic-seo` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Programmatic SEO > Settings to enable/disable features
4. Configure your Twitter handle and other options
5. Create templates in Programmatic SEO > Templates
6. Add data sources in Programmatic SEO > Data Sources
7. Generate pages using Programmatic SEO > Page Generator
8. Monitor performance in Programmatic SEO > Analytics
9. Check the Tools page to verify sitemap generation

== Frequently Asked Questions ==

= How do I create and use templates? =
1. Go to Programmatic SEO > Templates
2. Click "Create New Template"
3. Fill in template fields with {{variable}} placeholders
4. Save the template
5. Use the template with a data source to generate pages

= How do I add a data source? =
1. Go to Programmatic SEO > Data Sources
2. Click "Add New Data Source"
3. Choose type (CSV, JSON, or API)
4. Configure the source details
5. Set up field mapping to match your data structure

= How do I generate pages automatically? =
1. Ensure you have at least one template and one data source
2. Go to Programmatic SEO > Page Generator
3. Select a template and data source
4. Click "Generate Pages"
5. Pages will be created and tracked in the Generated Pages table

= How do I access the XML sitemap? =
Once the plugin is activated and the sitemap feature is enabled, you can access it at:
- Main sitemap: yoursite.com/sitemap.xml
- Posts: yoursite.com/sitemap-1.xml
- Pages: yoursite.com/sitemap-pages.xml
- Categories: yoursite.com/sitemap-categories.xml
- Tags: yoursite.com/sitemap-tags.xml
- Generated Pages: yoursite.com/sitemap-generated.xml

= Can I customize meta descriptions? =
Yes, you can set custom meta descriptions for each post through the post edit screen using the plugin's meta boxes or set them during page generation via templates.

= What data formats are supported? =
The plugin supports:
- CSV files (local or remote)
- JSON data (inline or from API)
- API endpoints (GET/POST with custom headers)

= Does this plugin conflict with other SEO plugins? =
It's recommended to use only one SEO plugin at a time to avoid conflicts. Disable features from other plugins if you're using this one.

= Can I generate thousands of pages? =
Yes, the page generator is designed to handle large-scale page generation. It includes duplicate detection and can process large data sets.

= How do I track page performance? =
Go to Programmatic SEO > Analytics to see:
- Total page views
- Top performing pages
- SEO scores for each page
- Engagement rates

== Screenshots ==

1. Admin Dashboard showing enabled features
2. Settings page with feature toggles
3. Tools page for testing sitemaps
4. Frontend meta tags output (visible in page source)

== Changelog ==

= 2.0.0 =
* Template Management system with dynamic variable substitution
* Data Source Integration (CSV, JSON, API support)
* Auto Page Generation for bulk page creation
* SEO Meta Automation (auto-generate titles, descriptions, keywords)
* Schema Markup Generator (Product, Event, Recipe, Article schemas)
* Internal Linking System with automatic link creation
* Performance Analytics dashboard
* Database tables for templates, data sources, generated pages, and analytics
* Enhanced Admin interface with multiple management pages
* Dynamic sitemap including generated pages
* Full backwards compatibility with v1.0.0 features

= 1.0.0 =
* Initial release
* Meta Tags management
* XML Sitemap generation
* Structured Data (JSON-LD) support
* Social Media meta tags
* Admin dashboard and settings
* Robots.txt integration

== Upgrade Notice ==

= 2.0.0 =
Major update with template management and automatic page generation features. Database tables will be created automatically on activation. All previous settings and features are preserved.

== Support ==

For support, bug reports, and feature requests, visit:
https://github.com/tootranmmo/prgr-stz001

== License ==

This plugin is licensed under the GPL v2 or later. You can read the full license here:
https://www.gnu.org/licenses/gpl-2.0.html

== Credits ==

Programmatic SEO is developed and maintained by the Programmatic SEO Team.
