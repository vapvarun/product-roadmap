=== Product Roadmap ===
Contributors: wbcomdesigns, vapvarun
Tags: roadmap, feature requests, user feedback, changelog, feature voting
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 1.8.0
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

= 1.8.0 - September 2026 =

Security and data-integrity release. Sites upgrading from any earlier version pick up database repairs automatically on the first page view.

* New      - The [roadmap] shortcode's board options all now work: status, category, item limit, search toggle, and per-board voting/comments visibility (Pro adds priority and a target-date range). The block editor's Pro roadmap block shares the same option list, so the two can never drift.
* New      - An admin notice appears when Product Roadmap Pro is older than the paired release, naming both versions with a link to update. Pro keeps running either way.
* Improve  - One shared colour token file now drives every frontend surface (board, item and changelog pages, the changelog shortcode, and Pro's screens), so light/dark mode and a per-product Theme Color reach everywhere consistently, and text/control contrast meets AA everywhere it was measured.
* Improve  - Toast messages, notices, the item detail modal's progress bar and its status chip now use the same colour tokens as the rest of the plugin, fixing contrast failures and a status chip that ignored dark mode.
* Improve  - "Votes" now means the number of people who voted, everywhere. It stored a net score that downvotes reduced, so an item could display fewer votes than it had voters and never matched the analytics. Existing counts are recalculated on upgrade, so boards that have received downvotes will show higher numbers than before.
* Improve  - Status-change emails are queued and sent in the background instead of one blocking send per recipient inside the request that changed the status.
* Improve  - The roadmap archive builds its per-product counts in two grouped queries instead of one unbounded query per product plus one per item.
* Improve  - The public items endpoint caps how many items one request may ask for, and costs far fewer queries per page.
* Improve  - Schema repairs run on the front end, not only in wp-admin, so a site whose owner rarely signs in still receives them.
* Fix      - Members can edit and delete their own submitted items again. A missing WordPress capability check silently blocked every non-admin role from managing their own submissions.
* Fix      - The Approve button on pending items was invisible everywhere due to a naming collision with a WordPress core style rule; the action itself always worked if you knew the URL.
* Fix      - The Timeline view could show one item twice under Now and Next at once; removed a dead dark-mode toggle that had no working control; removed a duplicate page heading.
* Fix      - A configured default product (Settings) was silently ignored by the bare [roadmap] shortcode and by suggestions submitted with no product chosen, both always landing on "main" instead.
* Fix      - The item archive page ignored a theme's changelog-frontend.css override, unlike every other changelog template.
* Fix      - A changelog entry published in the block editor under a non-default product could still get a slug starting with "general-" instead of its real product.
* Fix      - Demo import votes could reset to a much lower number the first time a real visitor voted, and again on every plugin update's recount; Pro analytics showed zero from the start for demo content.
* Fix      - The List and Idea Box templates ignored the board's shared Sort filter and quietly re-sorted only the items already loaded on the page.
* Fix      - Deleting a board item now moves it to the trash (restorable) instead of deleting it permanently.
* Fix      - Item labels saved as blank on Settings showed messages and headings with no noun ("moved to the trash.").
* Fix      - A template not currently offered (Pro inactive, disabled in Pro settings) could still be selected instead of falling back to a working one.
* Fix      - The Setup Wizard showed unrelated admin notices on its own screen, including its own "Run Setup Wizard" button.
* Fix      - The suggestion form cleared a member's title and description immediately on submit, before the server responded, so a rate-limited or failed submission lost their text.
* Fix      - Removed every remaining browser alert()/confirm() dialog (Reject suggestion, Setup Wizard "Start Over", board delete, clear cache); replaced with in-page notices and two-step confirmations.
* Fix      - The single item page's "already voted" state could disagree with the board's, and stopped recognising a guest's earlier vote once guest voting was turned off after they voted.
* Fix      - Sorting by votes dropped every item that had never received a vote from the results.
* Fix      - Notification emails ignored the recipient list configured on the Settings screen and used an older, separate setting instead.
* Fix      - A malformed or missing product on the [roadmap] shortcode showed visitors a setup error instead of a working board; a Free-only board showed a "requires Pro" message to visitors instead of just to editors.
* Fix      - A new item created in the block editor pre-selected the default product before you'd chosen one, and every new item landed on the default product's board regardless of what you picked afterward.
* Fix      - The Setup Wizard's guest-voting choice stopped taking effect once the Settings screen had ever been saved on its own.
* Fix      - The Timeline view's Now/Next/Later date-range header stayed fixed regardless of the selected Timeline Scale.
* Fix      - Removed a duplicate Sort By control on the Cards template that silently overrode the shared filter bar's sort.
* Fix      - Guest voting worked on the single item page but not on the board itself.
* Fix      - Vote and comment counts kept showing on most templates even after voting or comments were turned off in Settings.
* Fix      - The "Suggested" badge for member-submitted items only appeared on the Minimal template; it now appears everywhere an item is shown.
* Fix      - The archive page's dark mode followed the visitor's OS setting instead of the theme's own dark-mode toggle, inconsistent with the rest of the plugin.
* Fix      - Database migrations now reach existing installs. dbDelta was passed "CREATE TABLE IF NOT EXISTS", which it cannot parse, so no column added since 1.0 ever reached an upgraded site.
* Fix      - Guest voting works. It was blocked in four places at once: the board script, the single-item script, a votes table missing the columns the server writes to, and a vote handler that reported success on a failed write.
* Fix      - The single-item page now recognises a guest who has already voted, instead of showing an un-voted button.
* Fix      - Voting now reports an error instead of claiming success when the vote cannot be saved.
* Fix      - Saving one Settings tab no longer resets the other. Saving Notifications previously turned voting, comments, suggestions and guest voting off and reset the items-per-page value, with no warning.
* Fix      - Assignee and Dependencies keep their values. The saved selection did not re-render as selected, and the next unrelated save then destroyed it.
* Fix      - The setup wizard's "Configure advanced settings" link pointed at a page that does not exist.
* Fix      - Filters and sort now apply to the whole board. They ran in the browser over the items already loaded, so on a board larger than one page filtering to a status showed only the matches that happened to be on the first page, and sorting by votes showed the top of that page rather than the top of the board.
* Fix      - Edit and Delete now work on the Timeline, List and Cards views. The buttons rendered and did nothing.
* Fix      - The timeline no longer shows most items twice. Anything without a start date was added to "Later" on top of the section its status had already placed it in, which inflated every count on the view.
* Fix      - The board scrolls sideways instead of wrapping its status columns into rows, which stopped it reading as a board in a narrower content area. Below 640px it stacks.
* Fix      - A Page at /roadmap/ is no longer hidden by the plugin's own rewrite rules.
* Fix      - Changelog entry slugs are no longer stuck as "general-auto-draft". The slug was derived while WordPress was still creating the auto-draft, then never revisited.
* Fix      - Preview on a pending or draft item opens the item instead of the product board.
* Fix      - Turning comments off now closes them everywhere, including the single item page, which asked WordPress directly and still showed a working reply form.
* Fix      - The roadmap archive is readable in dark mode; the heading was dark text on a dark background.
* Fix      - The items endpoint accepts a product id as well as a slug. An id matched nothing and returned an empty board with no error.
* Fix      - Every roadmap item returned 404 when a published Page owned /roadmap/; the single-item URL now always resolves, and the Page keeps /roadmap/.
* Fix      - The product filter on the [roadmap] board was always hidden on Pro; it now shows unless the shortcode pins a product.
* Security - REST routes that edit, delete or create roadmap items now check the capability against the specific post instead of the site-wide edit_posts. A Contributor could previously delete, rewrite or publish any post or page on the site through /roadmap/v1/items.
* Security - Creating an item through the REST API no longer publishes on behalf of a user who lacks publish_posts.
* Security - The pending-submissions notice dismissal now checks a capability alongside its nonce.
* Security - The item-creation REST endpoint rejected a malformed request (an array where a single product was expected) instead of passing it through unsanitized.
* Dev      - Added a show_title attribute to the [roadmap] shortcode.
* Dev      - Added sort and search parameters to the items REST endpoint, and the roadmap_max_per_page filter for the page-size ceiling.
* Dev      - Added Roadmap_Permissions::can_edit_item() and can_delete_item(); every route that mutates an item by id routes through them.
* Dev      - Added the roadmap_tab_setting_keys filter so add-ons can register which settings keys their own tab owns.
* Dev      - Roadmap_API::get_user_ip() is now public, so templates identify a guest the same way the API does.
* Dev      - Removed the stale minified bundles and the build steps that silently never ran. The plugin has always enqueued the unminified files.
* Dev      - Regenerated the translation template. 1.7.1 shipped a 1.7.0 POT, so seven strings from its own features were never available to translators.
* Compat   - Aligned with Product Roadmap Pro 1.8.0. Install both updates together.

= 1.7.1 - July 2026 =

* Fix      - Product theme colour now saves; the settings modal was missing its AJAX submit handler, so the Save button did nothing.
* Fix      - Product settings modal now loads the saved colour and default status when opened, instead of always resetting to the default blue.
* Fix      - Saved product theme colour now recolours the [roadmap] and [roadmap_changelog] frontend; the accent was previously wired only to the site theme colour and ignored the setting.
* Fix      - show_filters="false" on the [roadmap] shortcode and the Show Filters block toggle now hide the filter bar; the attribute was previously ignored.
* Fix      - Admin Vote Count now matches the frontend; the edit screen read a separate key that only demo import wrote, so the two never agreed.
* Fix      - Removing a vote now shows "Your vote has been removed" instead of the "vote submitted" message.
* Fix      - Setup wizard now opens automatically on first activation; the redirect flag was set but never acted on.
* Fix      - Demo content no longer imports twice when the wizard is re-run, which previously duplicated the sample items.
* Fix      - Setup wizard no longer accepts an empty product name submitted by a direct request; it falls back to the site title.
* Fix      - Disabling voting, comments or suggestions now revokes the matching capability so current_user_can() reports it correctly.
* Improve  - Product card action buttons (View Items, View Roadmap, Settings) and the Add New Product button align their icons with the label.
* Improve  - Vote and comment counts are hidden on the board when voting or comments are disabled, so a disabled feature no longer looks actionable.
* Improve  - User-suggested items now carry a Suggested badge on the board and a Source column in the admin list, so they are easy to tell apart from admin-created items.
* Improve  - Changelog frontend styling can be overridden by the theme (drop roadmap/changelog-frontend.css in the theme, or use the roadmap_changelog_enqueue_frontend_css filter).
* Security - Sanitize changelog items saved through the REST meta endpoint.
* Dev      - Remove a duplicate REST POST /comment route registration and an empty legacy demo-import hook.
* Dev      - uninstall.php now also removes the per-user notification opt-out meta.
* Compat   - Aligned with Product Roadmap Pro 1.7.1. Install both updates together.

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