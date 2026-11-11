# Displaying Your Roadmap

Product Roadmap displays through shortcodes, so you can drop a roadmap onto any page, post, or widget area without touching code.

## The basic shortcode

```
[roadmap]
```

With no attributes, this shows your default product's board using the Minimal layout (the only layout the free version ships), with every published item and a Load more button. Leave `product` off entirely to use your default product - on Free that's always the "main" product, since Free supports one product roadmap.

## Common examples

```
[roadmap status="planned"]              Starts on the Planned filter
[roadmap sort="votes" limit="10"]       Top 10 most-voted items
[roadmap category="integrations"]       Only items in one category
[roadmap show_filters="false"]          Hide the filter bar
[roadmap show_voting="hide"]            Hide voting on this board only
```

`status` and `category` each take exactly one slug, and they set the starting filter (visitors can change it unless you hide the filter bar). To show more than one status or category on the same page, add a second shortcode, or leave the attribute off and let visitors use the filter bar instead.

## Recipes

**"What we're building" page**
```
[roadmap status="in-progress" sort="priority"]
```

**"Recently shipped" page**
```
[roadmap status="completed" sort="recent" limit="10"]
```

**Read-only board (no voting, no comments)**
```
[roadmap show_voting="hide" show_comments="hide"]
```

`show_voting="hide"` and `show_comments="hide"` can only hide voting or comments on this particular board - they can't turn either on if it's disabled in **Roadmap > Settings**.

## Adding the shortcode to a page

- **Block editor:** add a Shortcode block and paste `[roadmap]`.
- **Classic editor:** switch to Text mode, paste the shortcode, then switch back to Visual to see a placeholder.
- **Page builders:** use your builder's shortcode/raw HTML element (Elementor's Shortcode widget, WPBakery's Raw HTML, Beaver Builder's HTML module, Divi's Code module).
- **Widget areas:** add a Custom HTML (or Shortcode) widget under **Appearance > Widgets** and paste the shortcode there.

## The changelog shortcode

`[roadmap_changelog]` displays your changelog on its own page. See [Changelog](04-changelog.md).

Feature suggestions don't use a separate shortcode - a "Suggest a feature" button appears directly on the `[roadmap]` board whenever suggestions are enabled in Settings. See [Voting, Comments, and Suggestions](03-voting-comments-and-suggestions.md#feature-suggestions).

## Every shortcode attribute

For the complete, current attribute list (including the Pro-only filters), see the [shortcode reference](../developer-guide/04-shortcode-reference.md).

## Page width

A board is a wide layout, so give its page room. If your theme shows a sidebar or a narrow content column on ordinary pages, choose the theme's full-width or no-sidebar page template for the page that holds the shortcode or block (on Reign, set the page layout to Full Width). The plugin's own item and archive pages already use the theme's content area with no sidebar.

## Theming and dark mode

The board and every template automatically pick up your active theme's accent color and follow your theme's own light/dark toggle (themes like BuddyX Pro and Reign). There's no separate dark-mode switch to configure - if your theme has a light/dark toggle, the roadmap follows it. See [Theming](../developer-guide/01-architecture-overview.md#colours-and-theming) if you want to customize colors further.

## Troubleshooting

**Shortcode shows as literal text**
- Make sure it's in a text/code block, not formatted by the visual editor.
- Check for curly quotes instead of straight quotes if you copy-pasted it.

**Items aren't appearing**
- Confirm items are published, not drafts.
- Remember `status` and `category` each accept only one value - a comma-separated list returns an empty board.

**Not all items are showing**
- `limit="0"` (the default) shows everything behind a Load more button - visitors need to click it to see more.
- Clear any caching plugin.

## Next steps

| Goal | Guide |
|---|---|
| Turn on voting and comments | [Voting, Comments, and Suggestions](03-voting-comments-and-suggestions.md) |
| Document a release | [Changelog](04-changelog.md) |
| See what Pro adds | [Multi-Product and Pro Features](05-multi-product-and-pro-features.md) |
