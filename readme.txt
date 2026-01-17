=== Product Roadmap ===
Contributors: wbcomdesigns, vapvarun
Tags: roadmap, product roadmap, feature voting, user feedback, product management
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 1.3.1
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

* **Public Roadmap Page** - Beautiful, responsive display of your plans
* **Voting System** - Let users upvote features they want most
* **Feature Suggestions** - Customers can submit their own ideas
* **Progress Tracking** - Show what's planned, in-progress, or completed
* **Categories** - Organize features for easy browsing
* **Comments** - Enable discussions on each item
* **Simple Shortcode** - Add to any page with [roadmap product="main"]

**Pro Version - For Growing Teams:**

* **Multiple Products** - Separate roadmaps for each product line
* **Board View** - Kanban-style board layout
* **Timeline View** - Visual representation of your plans
* **Priority Levels** - Mark features as critical, high, or nice-to-have
* **Advanced Analytics** - Understand voting patterns
* **Email Notifications** - Stay informed of user engagement
* **Custom Styling** - Match your brand perfectly
* **Data Export** - Analyze feedback in your favorite tools
* **Priority Support** - Get help when you need it

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

= 1.3.1 =
* Security: Fixed IP spoofing vulnerability in guest voting - now uses only REMOTE_ADDR with proper validation
* Performance: Fixed N+1 query issue in REST API - batch fetches votes and primes caches for all items
* Performance: Dashboard stats now use efficient count queries instead of loading all posts
* Performance: Role capabilities only added on activation/version upgrade (not every page load)
* Performance: Default taxonomy terms only inserted on activation/version upgrade (not every page load)
* Enhancement: Added filter hooks for Pro addon to enhance changelog shortcode and API

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