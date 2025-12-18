# Product Roadmap - Extended FAQ Content

## Comprehensive FAQ for Website, Support, and Documentation

---

## GENERAL QUESTIONS

### What is Product Roadmap?
Product Roadmap is a WordPress plugin that lets you create public roadmaps where customers can vote on features and submit their own ideas. It helps product teams collect organized feedback and make data-driven decisions about what to build next.

### Is Product Roadmap really free?
Yes! The free version is complete and fully functional. It includes public roadmap display, voting, suggestions, comments, and changelog features. It's not a limited trial or demo - use it forever at no cost.

### What's the difference between Free and Pro?
The free version covers all the basics: public roadmap, voting, suggestions, comments, and two templates. The Pro addon adds visualization (analytics dashboard, timeline view, more templates), management features (multi-product, drag & drop, import/export), and advanced capabilities (guest suggestions, advanced filtering).

### Do I need any technical skills?
Not at all. The setup wizard guides you through everything. Add your roadmap to any page with a simple shortcode. No coding required.

### Does it work with my theme?
Yes. Product Roadmap works with any WordPress theme. The design is responsive and adapts to your site's styling. You can add custom CSS for further customization.

### Can I see a demo?
Yes! Visit [demo URL] to see Product Roadmap in action with sample data.

---

## INSTALLATION & SETUP

### How do I install Product Roadmap?
1. Go to Plugins → Add New in WordPress
2. Search for "Product Roadmap"
3. Click Install Now, then Activate
4. The setup wizard will guide you through configuration

### How long does setup take?
About 5 minutes. The wizard walks you through creating your product, setting up statuses, and optionally importing demo content.

### Can I import demo content?
Yes. The setup wizard offers to import 11 sample roadmap items so you can see how everything works. Delete them when you're ready to add your own items.

### Where does my roadmap appear?
The setup wizard automatically creates a page with your roadmap. You can also add the shortcode `[roadmap product="your-product"]` to any page or post.

### What's the basic shortcode?
```
[roadmap product="main"]
```
Replace "main" with your product slug. The wizard shows you your exact shortcode.

### How do I add the roadmap to my menu?
Go to Appearance → Menus. Find the roadmap page in the Pages list and add it to your menu.

---

## VOTING SYSTEM

### How does voting work?
Customers click the upvote arrow on any roadmap item to vote for it. The vote count updates immediately. They can also downvote or remove their vote.

### Can someone vote multiple times?
No. Each person gets one vote per item. Logged-in users are tracked by user ID, guests by IP address.

### Can visitors vote without an account?
Yes! Guest voting is enabled by default. No registration required - visitors just click to vote.

### How do I see which items have the most votes?
Sort your roadmap by "Most Voted" using the filter options. In the admin, you can see vote counts in the items list.

### Can I disable downvoting?
Not currently. In practice, downvotes are rare - most users either upvote or don't vote at all.

### Can I see who voted?
Voting is anonymous to encourage participation. You see aggregate counts, not individual voters.

### Can I reset votes on an item?
Not through the UI, but votes can be cleared via the database if absolutely necessary. Contact support for guidance.

---

## USER SUGGESTIONS

### How do customers submit suggestions?
They click the "Suggest Feature" button on your roadmap page, fill out a simple form (title and description), and submit.

### Are suggestions moderated?
Yes. All suggestions start in "Pending" status. Review them in your WordPress admin and approve or reject.

### Where do I see pending suggestions?
Go to Roadmap → All Items in WordPress admin. Filter by "Pending" status to see suggestions awaiting review.

### Can I edit a suggestion before approving?
Yes. Click to edit any suggestion. Improve the title, expand the description, change the category, then approve.

### What happens when I approve a suggestion?
Its status changes (typically to "Under Review" or "Planned") and it appears on the public roadmap for others to vote on.

### Can I let suggestions auto-approve?
Yes. Enable auto-approval in Settings if you trust your community. Suggestions go public immediately.

### Can guests submit suggestions?
In the free version, suggestions require logged-in users. The Pro addon adds guest suggestions with name/email collection.

---

## COMMENTS & DISCUSSION

### How do comments work?
Comments use WordPress's native comment system. Customers can discuss roadmap items, ask questions, and provide context.

### Can I moderate comments?
Yes. Standard WordPress comment moderation applies - approval, spam filtering, editing, and deletion.

### Are comments threaded?
Yes. Replies appear nested under parent comments for organized discussions.

### Do I get notified of new comments?
Yes, if you have WordPress comment notifications enabled. You can also configure email notifications in Roadmap settings.

### Can I disable comments?
Yes. Disable comments globally in Settings, or on individual items using WordPress's discussion settings.

---

## CHANGELOG

### What is the changelog feature?
A dedicated place to document your product releases. Show customers what you've shipped and connect it to roadmap items.

### How do I create changelog entries?
Go to Roadmap → Changelog → Add New. Add a title (typically version number), description of changes, and publish.

### How do I display the changelog?
Use the shortcode `[roadmap_changelog]` on any page.

### Can I link changelog entries to roadmap items?
Yes. Reference completed items in your changelog description to show the connection between requests and releases.

---

## PRO ADDON

### What is the Pro addon?
The Pro addon is a separate plugin that extends the free Product Roadmap with advanced features: analytics dashboard, timeline view, 6 templates, multi-product support, drag & drop, import/export, and more.

### Do I need the free plugin to use Pro?
Yes. Pro is an addon that requires the free Product Roadmap plugin. Both are included when you purchase Pro.

### Is Pro a subscription?
No. It's a one-time purchase with lifetime updates. Pay once, use forever.

### What happens if my Pro license expires?
You can continue using all Pro features. You just won't receive updates or priority support. Renew anytime to restore them.

### Can I try Pro before buying?
We offer a 30-day money-back guarantee instead of a trial. Try Pro with full functionality and get a refund if it's not right for you.

### How do I activate Pro?
After purchase, you receive a license key. Go to Roadmap → Settings → License, enter your key, and click Activate.

### Can I upgrade from Free to Pro without losing data?
Absolutely. Install Pro alongside Free, activate it, and your existing data stays intact. Pro features simply appear.

---

## PRO: ANALYTICS

### What does the analytics dashboard show?
Key metrics (items, votes, comments, users), status distribution chart, voting trends over time, popular items list, and recent activity feed.

### Can I filter analytics by product?
Yes. The product dropdown filters all analytics to a specific product.

### Can I export analytics data?
Yes. Click Export to download a CSV with metrics, popular items, and activity data.

### How far back does analytics data go?
All historical data from plugin installation. No automatic cleanup or expiration.

---

## PRO: TIMELINE

### How do I display the timeline view?
Use `[roadmap product="your-product" template="timeline"]` shortcode.

### Do all items appear on the timeline?
Only items with target dates. Items without dates show in board view but not timeline.

### What time periods are available?
Day, Week, Month (default), Quarter, and Year views.

### Can I reschedule items on the timeline?
Yes, with drag & drop enabled. Drag an item to move its target date.

---

## PRO: MULTI-PRODUCT

### What is multi-product support?
The ability to create separate roadmaps for different products, projects, or clients, all managed from one WordPress installation.

### How many products can I create?
Unlimited (default max is 10, configurable in settings).

### How do I display a specific product roadmap?
Use `[roadmap product="product-slug"]` shortcode.

### Can one item belong to multiple products?
Yes. Assign multiple products to any item.

---

## TECHNICAL QUESTIONS

### What are the requirements?
WordPress 5.8+, PHP 7.4+

### Does it work with WooCommerce?
Yes. Product Roadmap is compatible with WooCommerce sites.

### Is there a REST API?
Yes. Full REST API for items, votes, comments, and suggestions. Documentation included.

### Can I customize the appearance?
Yes. Custom CSS works. Developers can override templates. Pro adds more template options.

### Does it work with caching plugins?
Yes. The plugin is cache-friendly. Votes use AJAX and work properly with page caching.

### Is it multisite compatible?
Yes. Product Roadmap works on WordPress multisite installations.

### Is it translation ready?
Yes. The plugin is fully translatable. POT file included.

---

## TROUBLESHOOTING

### Votes aren't working
1. Check browser console for JavaScript errors
2. Ensure no plugin conflicts (try disabling others)
3. Verify shortcode has correct product parameter
4. Clear any caching plugins

### Roadmap not showing
1. Verify shortcode is complete: `[roadmap product="your-product"]`
2. Check that the product exists (Roadmap → Products)
3. Ensure items are assigned to that product
4. Check page is published

### Suggestions not appearing
1. Suggestions start in "Pending" status
2. Go to Roadmap → All Items → Filter by Pending
3. Approve suggestions to make them public

### Setup wizard didn't appear
Go to Roadmap menu - you should see "Setup" or "Run Wizard" option. If plugin activated before, wizard may have already run.

### Pro features not showing
1. Verify Pro addon is installed and activated
2. Check license is activated (Roadmap → Settings → License)
3. Clear any caching

---

## SUPPORT & RESOURCES

### Where can I get support?
- **Free users:** WordPress.org support forum
- **Pro users:** Priority email support at support@wbcomdesigns.com

### Where's the documentation?
Full documentation at [docs URL]

### How do I report a bug?
Free users: WordPress.org support forum
Pro users: Email support with details
All users: GitHub issues if repository is public

### How do I request a feature?
Use the feature request form on our website, or email suggestions to feedback@wbcomdesigns.com

### Are there video tutorials?
Yes! Visit our YouTube channel or the Documentation section for video guides.
