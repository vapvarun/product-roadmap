# Displaying Your Roadmap

**Time to read: 7 minutes**

Learn how to add and customize the roadmap display on your website.

## What You'll Learn

- Using the roadmap shortcode
- All shortcode parameters
- Adding to pages and posts
- Using with page builders
- Customizing the display

## The Roadmap Shortcode

### Basic Usage

The simplest way to display your roadmap:

```
[roadmap product="main"]
```

The `product` parameter is optional. Without it (or if it cannot be read, for example a curly quote pasted from an email) the roadmap shows your default product, so visitors always see a working board. Any setup message is shown only to people who can edit the page.

### Where to Use It

Add the shortcode to:
- **Pages** - Dedicated roadmap page
- **Posts** - In blog posts
- **Widgets** - Sidebar or footer
- **Page Builders** - Elementor, Divi, etc.

## Shortcode and Block Options

The `[roadmap]` shortcode and the **Roadmap** block (Pro) share one list of options, so anything you can set in the block sidebar you can also write in the shortcode, and the block's **Shortcode** panel shows the exact shortcode for your block. Every option is optional: a bare `[roadmap]` shows your default product with your default layout.

| Option | What it does | Default | Values | Notes |
|---|---|---|---|---|
| `product` | Product | (empty) | a product slug | Leave empty to show your default product. |
| `template` | Layout | your default layout (Settings) | `board`, `timeline`, `list`, `cards`, `minimal`, `ideabox` (Pro; Free has `minimal`) |  |
| `status` | Show only this status | (empty) | one status slug | Visitors can still change it from the filter bar unless you hide the filters. |
| `category` | Show only this category | (empty) | one category slug |  |
| `priority` | Show only this priority | (empty) | (empty) All priorities, `low` Low, `medium` Medium, `high` High, `critical` Critical | **Pro.** |
| `date_from` | Target date from | (empty) | `YYYY-MM-DD` | **Pro.** Only items with a target date on or after this day. |
| `date_to` | Target date to | (empty) | `YYYY-MM-DD` | **Pro.** Only items with a target date on or before this day. |
| `sort` | Sort by | (empty) | (empty) Default, `votes` Most votes, `recent` Newest, `comments` Most comments, `priority` Priority, `title` Title |  |
| `limit` | Number of items | 0 | 0-100 | 0 shows every item, with a Load more button. |
| `show_title` | Show the title | true | `true` / `false` |  |
| `show_filters` | Show the filter bar | true | `true` / `false` |  |
| `show_search` | Show the search box | true | `true` / `false` |  |
| `show_voting` | Voting on this board | (empty) | (empty) Follow the global setting, `hide` Hide on this board | Can hide voting here; it can only be on where voting is enabled in Settings. |
| `show_comments` | Comments on this board | (empty) | (empty) Follow the global setting, `hide` Hide on this board | Can hide comments here; they can only be on where comments are enabled in Settings. |
| `mode` | Mode | view | `view` View, `manage` Manage | Manage lets editors add and edit items on the page; visitors always see View. |
| `allow_frontend_edit` | Allow editing items on the page | false | `true` / `false` |  |
| `class` | Extra CSS class | (empty) | text |  |

This table is generated from `Roadmap_Shortcode::options()` in `includes/class-roadmap-shortcode.php`, the single source for the shortcode and the block. After adding or changing an option there, regenerate it and run `wp eval-file wp-content/plugins/product-roadmap/bin/check-board-options.php` to prove the option reaches the board from both.

**Filters vs the filter bar.** `status` and `category` set what the board starts on. Visitors can still change them from the filter bar; add `show_filters="false"` to lock the board to that view. One status and one category per board.

## Common Examples

### Show all items
```
[roadmap]
```

### Most voted, top 10
```
[roadmap sort="votes" limit="10"]
```

### Recently shipped (locked)
```
[roadmap status="completed" sort="recent" show_filters="false"]
```

### Planned features only
```
[roadmap status="planned" category="features"]
```

### A read-only board (no voting, no comments)
```
[roadmap show_voting="hide" show_comments="hide"]
```
Hides voting and comments on this board only; other boards follow your Settings.

### Pro: a product in the board layout
```
[roadmap product="mobile-app" template="board"]
```

## Adding to Pages

### Gutenberg Editor

1. Edit or create a page
2. Click **+** to add block
3. Search for "Shortcode"
4. Add **Shortcode** block
5. Paste shortcode:
   ```
   [roadmap product="main"]
   ```
6. Click **Update** or **Publish**

### Classic Editor

1. Edit or create a page
2. Switch to **Text** mode (tab at top right)
3. Paste shortcode where you want roadmap
4. Switch back to **Visual** mode
5. You'll see a placeholder
6. Click **Update** or **Publish**

### Quick Setup

The setup wizard already created a page for you:

1. Go to **Pages**
2. Find "{Your Product} Roadmap"
3. Edit if you want to customize
4. The shortcode is already there

## Adding to Posts

Same as pages:

1. Create or edit a post
2. Add Shortcode block (Gutenberg)
3. Or paste in Text mode (Classic)
4. Include roadmap inline with post content

Example use case:
"Check out our Q1 2024 roadmap below!"

## Using with Page Builders

### Elementor

1. Edit page with Elementor
2. Drag **Shortcode** widget to page
3. Paste shortcode in widget
4. Style the widget container
5. Update page

### WPBakery Page Builder

1. Edit page with WPBakery
2. Add **Raw HTML** element
3. Paste shortcode
4. Save

### Beaver Builder

1. Edit page with Beaver Builder
2. Add **HTML** module
3. Paste shortcode in HTML field
4. Save

### Divi Builder

1. Edit page with Divi
2. Add **Code** module
3. Paste shortcode
4. Save

## Widget Areas

Add roadmap to sidebar or footer:

1. Go to **Appearance > Widgets**
2. Find widget area (e.g., "Sidebar")
3. Add **Custom HTML** widget
4. Widget title: "Product Roadmap"
5. Content: Paste shortcode
6. Click **Save**

Tip: Use `limit` parameter for widgets:
```
[roadmap product="main" limit="5"]
```

## PHP Templates

Add to theme template files:

```php
<?php echo do_shortcode('[roadmap product="main"]'); ?>
```

Or with variables:

```php
<?php
$product = 'main';
$status = 'in-progress';
echo do_shortcode("[roadmap product='$product' status='$status']");
?>
```

## Menu Links

Add roadmap to navigation menu:

1. Create roadmap page (already done by wizard)
2. Go to **Appearance > Menus**
3. Find your menu
4. Under **Pages**, check your roadmap page
5. Click **Add to Menu**
6. Drag to desired position
7. Click **Save Menu**

Now users can access roadmap from navigation.

## Multiple Roadmaps

### Different Views on Different Pages

**Page 1: All items**
```
[roadmap product="main"]
```

**Page 2: Only planned**
```
[roadmap product="main" status="planned"]
```

**Page 3: Top voted**
```
[roadmap sort="votes" limit="10"]
```

### Pro: Multiple Products

With Pro addon:

**Mobile App Roadmap Page:**
```
[roadmap product="mobile-app"]
```

**Web Platform Roadmap Page:**
```
[roadmap product="web-platform"]
```

**All Products Page:**
```
[roadmap product="mobile-app"]
[roadmap product="web-platform"]
```

## Customizing Display

### Page Title

Change the page title:
1. Edit roadmap page
2. Change title field
3. Update page

### Intro Text

Add text before roadmap:

1. Edit page
2. Add paragraph before shortcode
3. Write introduction:
   ```
   Vote for the features you want to see next!

   [roadmap product="main"]
   ```

### Multiple Sections

Organize by status:

```
## What We're Building

[roadmap product="main" status="in-progress"]

## Coming Soon

[roadmap product="main" status="planned"]

## Recently Completed

[roadmap product="main" status="completed" limit="5"]
```

### Custom CSS

Style the roadmap:

1. **Appearance > Customize**
2. **Additional CSS**
3. Add custom styles:

```css
/* Larger cards */
.roadmap-item {
    min-height: 200px;
}

/* Custom colors */
.roadmap-status-planned {
    background: #e3f2fd;
}

/* Hide vote button */
.roadmap-vote-button {
    display: none;
}
```

## Responsive Display

The roadmap is mobile-friendly by default:
- Cards stack on mobile
- Touch-friendly buttons
- Readable text sizes
- Smooth scrolling

Test on mobile:
- Use browser developer tools
- Check on actual device
- Verify all buttons work
- Ensure readability

## Performance Tips

### For Large Roadmaps

**Use limits:**
```
[roadmap product="main" limit="50"]
```

**Filter by status:**
```
[roadmap status="planned"]
```
Shows fewer items, loads faster.

**Enable caching:**
1. **Roadmap > Settings > Advanced**
2. Enable object caching
3. Set cache duration

**Use caching plugin:**
- WP Rocket
- W3 Total Cache
- WP Super Cache

### Optimize Images

If items have images in descriptions:
- Compress images before upload
- Use image optimization plugins
- Enable lazy loading

## Troubleshooting

### Roadmap Not Showing

**Check shortcode syntax:**
- Must include `product` parameter
- Correct: `[roadmap product="main"]`
- Wrong: `[roadmap]`

**Verify items exist:**
- Go to **Roadmap > All Items**
- Ensure items are Published
- Items must be in the product

**Check for errors:**
- View page source
- Look for PHP errors
- Check browser console (F12)

### Showing Wrong Items

**Check filters:**
- Verify status filter
- Check category filter
- Confirm product parameter

**Check item assignment:**
- Edit items
- Verify product assignment
- Verify status assignment

### Styling Issues

**Clear cache:**
- Browser cache (Ctrl+F5)
- WordPress cache
- CDN cache

**Check for conflicts:**
- Disable other plugins
- Try default theme
- Look for CSS conflicts

### Page Builder Issues

**Shortcode not rendering:**
- Use HTML/Code module
- Don't use Text module
- Check shortcode escaping

**Styling problems:**
- Add custom CSS to builder
- Use builder's spacing controls
- Wrap in container

## Next Steps

- **Configure Settings** - Fine-tune display options
- **Customize Styles** - Match your brand
- **Pro Features** - Upgrade for more templates

---

← Voting & Comments | **Settings Overview** →