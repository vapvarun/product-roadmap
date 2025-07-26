# Product Roadmap

Create public product roadmaps to share your vision, collect user feedback, and build products your customers actually want.

## Short Description

Product Roadmap helps you build transparency with your customers by sharing your development plans and letting them vote on features that matter most to them.

## Long Description

**Build Products Your Customers Love**

Product Roadmap transforms the way you communicate with your customers about upcoming features and improvements. Instead of developing in isolation, create a transparent development process where customers can see what's coming, vote on priorities, and suggest new ideas.

**Why Product Roadmap?**

- **Increase Customer Engagement**: Let customers feel heard by allowing them to vote and comment on upcoming features
- **Reduce Development Waste**: Build features that customers actually want, not what you think they need
- **Build Trust Through Transparency**: Show customers you're actively working on improvements they care about
- **Gather Valuable Insights**: Understand which features resonate most with your user base
- **Reduce Support Tickets**: Customers can see what's already planned, reducing "when will you add X?" questions
- **Boost Customer Retention**: Users stay loyal when they see their feedback shapes your product

### Free Version - What You Get

- **Public Roadmap Display**: Share your development plans openly with customers
- **User Voting**: Let customers vote on features they want most
- **Progress Tracking**: Show what's planned, in progress, or completed
- **Organized Categories**: Group similar features together for easy browsing
- **User Suggestions**: Allow customers to submit their own feature ideas
- **Comment System**: Enable discussions on each roadmap item
- **Quick Setup**: Add to any page with a simple shortcode
- **Mobile Friendly**: Your roadmap looks great on all devices

### Pro Version - Scale Your Feedback

- **Multiple Products**: Manage separate roadmaps for different products or services
- **Individual Product Settings**: Customize each roadmap to match your brand
- **Visual Timeline**: Show customers when features are expected
- **Smart Filters**: Help users find relevant features quickly
- **Priority Management**: Highlight critical vs nice-to-have features
- **Data Export**: Analyze voting patterns and user feedback
- **Admin Notifications**: Get alerted when users engage with your roadmap
- **Custom Branding**: Match your roadmap perfectly to your site design

## Installation

1. Upload the `product-roadmap` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Roadmap → Dashboard to get started

## Usage

### Basic Usage

Add the roadmap to any page or post using the shortcode:

```
[roadmap]
```

### Frontend Editing

Enable frontend editing for authorized users:

```
[roadmap allow_frontend_edit="true"]
```

### Pro Version - Multiple Products

```
[roadmap product="mobile-app"]
[roadmap product="web-platform"]
```

## Configuration

1. Go to **Roadmap → Settings** to configure global settings
2. Set default status for new items
3. Enable/disable voting, comments, and suggestions
4. Configure items per page

## Who Should Use Product Roadmap?

- **SaaS Companies**: Show customers your development priorities
- **WordPress Plugin/Theme Developers**: Let users vote on next features
- **Digital Agencies**: Share project timelines with clients
- **E-commerce Stores**: Get feedback on new features or products
- **Community Projects**: Let members decide project direction
- **Any Business**: That wants to build products customers actually want

## Documentation

- **[User Guide](docs/USER-GUIDE.md)** - Complete guide for using the plugin
- **[Developer Guide](docs/DEVELOPER-GUIDE.md)** - Hooks, filters, and API reference  
- **[Product System](docs/PRODUCT-SYSTEM.md)** - Multi-product roadmap documentation
- **[Settings Guide](docs/SETTINGS-GUIDE.md)** - Configuration options
- **[Build Instructions](BUILD.md)** - Development setup

## Development

### Building the Plugin

To build the production assets, run:

```bash
./build.sh
```

This will:
1. Install npm dependencies
2. Build the Vue.js application
3. Copy assets to WordPress directories
4. Create production-ready files

### Development Mode

For development with hot reload:

```bash
cd vue-app
npm install
npm run dev
```

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Modern browser with JavaScript enabled
- Node.js 16+ (for building from source)

## Author

**Wbcom Designs**  
Website: [https://wbcomdesigns.com/](https://wbcomdesigns.com/)

## License

GPL v2 or later

## Support

For support, please visit [https://wbcomdesigns.com/support/](https://wbcomdesigns.com/support/)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.