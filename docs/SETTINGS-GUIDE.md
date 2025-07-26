# Roadmap Plugin Settings Guide

## Overview

The Roadmap Plugin now includes comprehensive settings with product-specific options and a cleaner admin menu structure.

## Admin Menu Structure

The admin menu has been reorganized to avoid duplicates:

1. **Roadmap Items** - Main menu for managing roadmap items
2. **Products** - Manage products and their specific settings
3. **Settings** - Global plugin settings with tabs for:
   - General Settings
   - Statuses Management
   - Categories Management
   - Notifications

## Key Features

### 1. Default Status for New Items

- Set a global default status for new roadmap items
- Each product can have its own default status
- User suggestions can have a separate default status (e.g., "Under Review")

### 2. Product-Specific Settings

Each product can have its own:
- Default status for new items
- Enable/disable voting
- Enable/disable comments
- Enable/disable suggestions
- Custom theme color
- Items per page

### 3. Clean Admin Interface

- No duplicate menu items
- Statuses and Categories are managed under Settings
- Products have their own dedicated page with settings modal

## Usage

### Setting Global Defaults

1. Go to **Roadmap Items → Settings**
2. In the General tab, set:
   - Default Status for New Items
   - Items Per Page
   - Enable/disable features globally

### Configuring Products

1. Go to **Roadmap Items → Products**
2. Click "Settings" on any product card
3. Configure product-specific options
4. Save settings

### Managing Statuses

1. Go to **Roadmap Items → Settings**
2. Click on the "Statuses" tab
3. Click "Manage Statuses" to add/edit/delete statuses

## Default Statuses

The plugin comes with these default statuses:
- **Planned** - Items being considered
- **In Progress** - Items currently being worked on
- **Completed** - Finished items
- **Under Review** - Items being evaluated (good for suggestions)

## API Integration

When creating items via API:
- If no status is provided, the global default status is used
- For suggestions, the suggestion default status is used
- Product-specific defaults override global defaults

## Shortcode Usage

Each product has its own shortcode displayed on the Products page:
```
[roadmap product="main"]
[roadmap product="mobile"]
```

## Notes

- All new roadmap items are created with status "published" by default
- Settings are cached for performance
- Product settings are stored as term meta
- Global settings use WordPress options API