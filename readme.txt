=== Product Roadmap ===
Contributors: wbcomdesigns, vapvarun
Tags: roadmap, feature requests, user feedback, changelog, feature voting
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 1.7.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create public product roadmaps to share your vision, collect user feedback, and build products your customers actually want.

== Description ==

**Build Products Your Customers Love**

Stop guessing what features to build next. Product Roadmap helps you create transparency with your customers by sharing your development plans and letting them vote on features that matter most to them.

**Turn Customer Feedback Into Your Competitive Advantage**

Every day, your customers have ideas about how to improve your product. Most businesses lose these valuable insights in support tickets, emails, or casual conversations. Product Roadmap captures this feedback in one organized place where customers can vote, comment, and see that you're listening.

**Why Product Roadmap?**

* **Reduce Development Waste**: Stop building features nobody uses. Let voting data guide your decisions.
* **Increase Customer Loyalty**: When customers see their ideas implemented, they become your biggest advocates.
* **Save Support Time**: Reduce "when will you add X?" tickets by showing what's already planned.
* **Build Community**: Turn feature requests into conversations that engage your entire user base.
* **Make Better Decisions**: Real data from real users beats guesswork every time.

**Perfect For:**

* SaaS companies wanting to be more customer-driven
* WordPress plugin and theme developers
* E-commerce stores planning new features
* Digital agencies managing client expectations
* Any business that values customer feedback

**Free Version Includes Everything You Need:**

* **Public Roadmap Board** - Beautiful, responsive, translation-ready display of your product plans
* **Feature Voting** - Logged-in users or guests (by IP) upvote the features they want most, with a clear "Voted" state
* **Feature Requests & Suggestions** - Customers submit their own ideas, with a moderation queue and built-in spam protection (rate limiting plus a honeypot, no captcha)
* **Comments & Discussion** - Enable conversations on every roadmap item
* **Custom Statuses & Categories** - Organize features (Planned, In Progress, Completed, or your own) and let visitors filter and sort
* **Changelog & Release Notes** - Publish per-release changelogs with a shortcode plus dedicated single and archive pages
* **Status-Change Emails** - Automatically notify the people who voted or commented when an item moves forward, with a one-click unsubscribe link
* **Automatic Dark Mode & Theme Colours** - The roadmap adopts your theme's accent colour and follows its light/dark toggle, with zero custom CSS
* **Simple Shortcodes** - Drop [roadmap] and [roadmap_changelog] onto any page
* **Developer Friendly** - REST API, 25+ action and filter hooks, and clean uninstall

**Pro Version - For Growing Teams:**

* **Multiple Product Roadmaps** - A separate roadmap for each product or service
* **4 Premium Layouts** - Board (Kanban), Timeline, Cards, and List views (Minimal is included free)
* **Analytics Dashboard** - Track votes, comments, views, and engagement with charts, popular items, and CSV export
* **Advanced Filters** - Filter by priority, progress, assignee, and date
* **Guest Suggestions & Feedback Widget** - Collect ideas from logged-out visitors and a floating feedback button
* **Email Digest & Custom Templates** - Scheduled activity digests and branded notification emails
* **Webhooks & Custom Fields** - Connect to your stack and store your own item data
* **Gutenberg Block** - Build roadmaps visually in the block editor
* **Priority Support** - Direct help from our team

== Part of the Wbcom Designs Ecosystem ==

Product Roadmap is built by Wbcom Designs, makers of a large family of WordPress and community products - including the BuddyX and Reign themes and dozens of BuddyPress, bbPress, and WooCommerce extensions. Pair Product Roadmap with your community or membership site to close the loop between what your members ask for and what you build next.

Explore more at [wbcomdesigns.com](https://wbcomdesigns.com/).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/product-roadmap` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Roadmap → Dashboard to configure the plugin.
4. Add the shortcode [roadmap] to any page where you want to display your roadmap.

== Frequently Asked Questions ==

= How do I display the roadmap? =

Simply add the shortcode [roadmap] to any page or post where you want the roadmap to appear.

= Can users submit their own feature ideas? =

Yes! When you enable suggestions in the settings, users can submit their own feature requests directly from the roadmap.

= Do users need to register to vote? =

You can configure this in settings. Allow guest voting for maximum participation, or require registration for more controlled feedback.

= Can I moderate feature suggestions? =

Yes, you can set suggestions to require approval before they appear on the public roadmap.

= Is it mobile-friendly? =

Absolutely! The roadmap is fully responsive and works great on all devices.

= Can I customize the appearance? =

The free version includes basic styling options. Pro version offers complete customization to match your brand.

= How do I manage multiple products? =

Multiple product roadmaps are available in the Pro version. Free version supports one roadmap.

== Screenshots ==

1. Public roadmap view where customers can see and vote on upcoming features
2. Admin dashboard with enhanced UI showing roadmap statistics and recent activity
3. Settings page with auto-approval toggle and copy shortcode buttons (v1.1.0)
4. Product listing showing copy shortcode functionality (v1.1.0)
5. Adding a new roadmap item from the WordPress admin
6. Feature suggestion form with auto-approval option enabled
7. Mobile responsive view of the roadmap
8. Individual roadmap item with voting and comments

== Changelog ==

= 1.7.0 - July 2026 =

* New      - Fire roadmap_item_viewed on single-item views so the Pro addon can record views.
* New      - Load more pagination on the roadmap board so products with more than 100 items are fully browsable.
* Improve  - Fire roadmap_api_query_args so the Pro addon's advanced filters (priority, progress, assignee, date) apply.
* Improve  - Add created_at and item_created indexes to the votes table for faster analytics at scale.
* Improve  - Add uninstall cleanup (options, votes table, transients) and load the plugin text domain for translation.
* Improve  - Label the roadmap filter dropdowns for screen readers.
* Improve  - Roadmap board and changelog adopt the active theme's accent colour, falling back to the plugin blue on themes with no colour token.
* Improve  - Vote button shows a clear voted state (checkmark and Voted pill) that stays visible on themes which restyle buttons.
* Improve  - Changelog supports dark mode driven only by the theme's own light/dark toggle, never the operating system.
* Improve  - Rate-limit votes and suggestions (per user, or per IP for guests) and add a suggestion honeypot to block spam without a captcha.
* Improve  - Status-update emails now include an unsubscribe link, and the per-type notification toggles (vote, comment, suggestion) now take effect.
* Improve  - Board bundle is isolated so it renders on themes that define a global script variable named wp (such as Reign), instead of stalling on Loading.
* Improve  - Buttons, badges, chips and the timeline header adapt their text colour to the active theme accent, staying readable on light-accent themes in both light and dark mode.
* Improve  - Status, priority and changelog badges meet WCAG AA contrast in light mode.
* Fix      - Roadmap board rendered empty when the Pro addon was active due to a product filter type mismatch.
* Fix      - Changelog version header was unreadable under a dark theme; it now follows the theme toggle.
* Dev      - Version the built board assets by file modification time so a rebuilt bundle busts the browser cache.
* Dev      - Full frontend translation readiness: every Vue board string (timeline, filters, cards, modals, changelog) runs through the plugin text domain and ships in the POT.
* Compat   - Aligned with Product Roadmap Pro 1.7.0. Install both updates together.

= 1.6.0 =
* New: Email notification to voters when a roadmap item status changes
* New: Admin email notification when a new suggestion is submitted
* Enhancement: Status change detection now uses taxonomy term changes for accurate tracking
* Enhancement: Fires `roadmap_status_changed` action hook for extensibility

= 1.5.1 =
* Fix: Force asset cache refresh for completion notes modal display

= 1.5.0 =
* New: "Done" section in Timeline view displays completed items separately from planned items
* New: Completion Notes field for completed items - explain what was done to customers
* New: Archive pages now use global default template setting from Pro Templates tab
* New: Support for both /roadmap/{product}/ and /roadmap-product/{product}/ URL patterns
* Enhancement: Completed items excluded from "Future - Later" section in Timeline view
* Enhancement: Archive template properly detects taxonomy archives and custom rewrites
* Enhancement: Completion notes exposed in REST API for frontend display
* Fix: Timeline view now correctly groups items by completion status

= 1.4.0 =
* New: All Products archive page for roadmap items (/roadmap/) showing product cards with status counts
* New: Select2 bundled locally - removed CDN dependencies for better performance and privacy
* New: Product filter dropdown on changelog and roadmap archive pages with search functionality
* Enhancement: Consistent Select2 styling across all pages (52px height, proper borders)
* Enhancement: Custom Select2 theme (roadmap-plugin) for unified look and feel
* Fix: Changelog meta data now properly displays version, release date, and release type
* Fix: Template loader now properly handles roadmap_product taxonomy archives

= 1.3.1 =
* Security: Fixed IP spoofing vulnerability in guest voting - now uses only REMOTE_ADDR with proper validation
* Performance: Fixed N+1 query issue in REST API - batch fetches votes and primes caches for all items
* Performance: Dashboard stats now use efficient count queries instead of loading all posts
* Performance: Role capabilities only added on activation/version upgrade (not every page load)
* Performance: Default taxonomy terms only inserted on activation/version upgrade (not every page load)
* Enhancement: Added filter hooks for Pro addon to enhance changelog shortcode and API
* Fix: Admin notice for pending submissions now properly persists dismissal (stays dismissed until new submissions arrive)
* Docs: Added comprehensive web documentation with screenshots

= 1.3.0 =
* New: Changelog feature for publishing release notes and version updates
* New: Changelog shortcode [roadmap_changelog] for displaying changelogs on frontend
* New: Changelog types taxonomy for categorizing updates (Feature, Fix, Security, etc.)
* Enhancement: REST API endpoints for changelog management
* Enhancement: Improved admin interface for changelog entries

= 1.2.1 =
* Fix: Improved array filtering for multiple select fields to properly handle empty values
* Enhancement: Better handling of dependency field data when saving roadmap items
* Compatibility: Ensures clean data structure for Pro addon features

= 1.2.0 =
* Fix: Product filtering now works correctly - items from all products display properly on frontend
* Fix: New items added to any product now appear immediately on frontend
* Fix: Taxonomy query relation now explicitly set for predictable filtering behavior
* Enhancement: Added extensibility filters for Pro plugin settings integration
* Enhancement: Pro settings tabs can now specify custom form groups and save buttons
* Enhancement: Improved plugin architecture for better free/pro integration
* Compatibility: Enhanced coordination between free and Pro versions for seamless functionality
* Note: This update requires both free and Pro versions to be updated together

= 1.1.1 =
* Fix: User submissions now properly appear as pending for admin review instead of being lost
* Fix: Subscribers can now submit suggestions (previously they couldn't due to permissions)
* Fix: Guest voting now works correctly - visitors can vote without logging in
* Fix: Settings page now clearly shows which features require Pro version
* Fix: User suggestions now get the correct default status set in settings
* Fix: Voting system no longer creates duplicate votes when users change their vote
* Fix: Improved security with better input validation and output protection
* Fix: Plugin now automatically refreshes URL structure after updates (prevents 404 errors)
* Enhancement: All voting and suggestion settings are now in one easy-to-find location
* Enhancement: Better support for non-English websites with improved translations
* Enhancement: Guest users can now vote on features without creating an account
* Enhancement: Smoother experience when updating the plugin - no manual steps needed

= 1.1.0 =
* New: Auto-approval option for user suggestions in settings
* New: Copy to clipboard functionality for shortcodes
* New: Shortcode copy buttons in product listings
* Enhancement: Improved admin UI with modern animations and transitions
* Enhancement: Better shortcode display with visual copy buttons
* Enhancement: Enhanced settings page with auto-approve toggle
* Enhancement: Added visual feedback for all copy actions
* Enhancement: Improved product taxonomy display
* Enhancement: Better responsive design for all screen sizes
* Fix: Improved CSS enqueue conditions for better compatibility
* Fix: Settings page JavaScript optimizations

= 1.0.2 =
* Fix: Resolved settings page tab navigation issue preventing clicks
* Fix: Added missing CSS styles for settings page layout
* Enhancement: Improved visual design with modern animations and transitions
* Enhancement: Added better responsive design for mobile and tablet views
* Enhancement: Enhanced Pro version UI with premium visual elements
* Fix: Fixed JavaScript conflicts between free and Pro versions
* Enhancement: Improved Pro CTA section with gradient design
* Enhancement: Added visual indicators for pending roadmap suggestions

= 1.0.1 =
* Fix: Changed default items per page from 20 to 100
* Fix: Made items per page configurable via settings
* Enhancement: Improved display to show all roadmap items without pagination
* Fix: Updated API endpoints to handle more items properly

= 1.0.0 =
* Initial release
* Core roadmap functionality
* Voting system
* Comments and suggestions
* Admin dashboard
* Responsive design
* Shortcode support

== Upgrade Notice ==

= 1.4.0 =
Feature update! New All Products archive page, local Select2 bundling (no more CDN), consistent dropdown styling, and changelog meta fixes.

= 1.3.1 =
Security and performance update. Fixes IP spoofing vulnerability in guest voting and N+1 query issues in REST API. Recommended for all users.

= 1.1.1 =
Important update! Fixes guest voting, subscriber permissions, and pending submissions workflow. Adds full guest voting support with IP tracking. Highly recommended for all users.

= 1.1.0 =
Major update! New auto-approval for suggestions, copy-to-clipboard for shortcodes, Gutenberg block support in Pro version, and improved admin UI. Recommended update for all users.

= 1.0.2 =
Important fixes for settings page navigation and UI improvements. Enhanced visual design with better responsive layout. Resolves tab clicking issues and Pro version compatibility.

= 1.0.1 =
Important fix: Roadmap now displays up to 100 items by default (configurable in settings) instead of being limited to 20.

= 1.0.0 =
Initial release of Product Roadmap. Start building products your customers love!