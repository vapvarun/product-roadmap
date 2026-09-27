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

## Shortcode Parameters

Customize the roadmap display with these parameters:

### product (Required)

Which product roadmap to show:
```
[roadmap product="main"]
```

Free version: Only `"main"` available
Pro version: Any product slug

### template

Display style:
```
[roadmap product="main" template="minimal"]
```

Available templates:
- `minimal` - Clean single-column layout (default, free)
- `board` - Kanban board (Pro)
- `list` - Simple list (Pro)
- `cards` - Grid of cards (Pro)
- `timeline` - Gantt chart (Pro)
- `ideabox` - Idea collection with voting (Pro)

The free version ships the `minimal` layout. The other layouts require the Pro addon; if you use them without Pro, the roadmap falls back to `minimal`.

**Idea Box Template (Pro)**
```
[roadmap product="main" template="ideabox"]
```
Perfect for collecting user ideas with prominent voting. Features:
- Large upvote buttons
- Sort by votes, newest, or activity
- Clean, focused design for idea collection

### status

Filter by status:
```
[roadmap product="main" status="planned"]
```

**Single status:**
```
status="in-progress"
```

**Multiple statuses:**
```
status="planned,in-progress,testing"
```

Use status slugs, separated by commas.

### category

Filter by category:
```
[roadmap product="main" category="feature"]
```

**Single category:**
```
category="enhancement"
```

**Multiple categories:**
```
category="feature,enhancement,bug-fix"
```

### limit

How many items to show:
```
[roadmap product="main" limit="20"]
```

Default: 100
Set lower for faster loading.

### orderby

Sort items by:
```
[roadmap product="main" orderby="votes"]
```

Options:
- `date` - Publish date (default)
- `votes` - Vote count
- `title` - Alphabetical
- `modified` - Last updated
- `priority` - Priority level (Pro)

### order

Sort direction:
```
[roadmap product="main" orderby="votes" order="desc"]
```

- `DESC` - Descending (default)
- `ASC` - Ascending

## Common Examples

### Show All Items
```
[roadmap product="main"]
```
Simple board with all items.

### Most Voted Features
```
[roadmap product="main" orderby="votes" order="desc" limit="10"]
```
Top 10 voted items.

### In-Progress Items
```
[roadmap product="main" status="in-progress"]
```
Only items being worked on.

### Planned Features
```
[roadmap product="main" status="planned" category="feature"]
```
Upcoming features only.

### Recent Updates
```
[roadmap product="main" orderby="modified" order="desc" limit="5"]
```
Last 5 updated items.

### Multiple Statuses
```
[roadmap product="main" status="in-progress,testing,completed"]
```
Active and done items.

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
[roadmap product="main" orderby="votes" limit="10"]
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
[roadmap product="main" status="planned,in-progress"]
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