# Import & Export

> **Pro Feature** - This feature requires the [Product Roadmap Pro](https://wbcomdesigns.com/downloads/product-roadmap-pro/) addon.

Bulk manage roadmap items with CSV files. Import items from spreadsheets, export data for backup or analysis.

---

## Why Import/Export?

Use CSV import and export to:
- **Migrate** from other tools (Trello, Jira, spreadsheets)
- **Bulk add** many items at once
- **Backup** your roadmap data
- **Analyze** data in Excel or Google Sheets
- **Share** with stakeholders who prefer spreadsheets

---

## Exporting Data

### Export All Items

1. Go to **Roadmap > Export**
2. Leave filters empty for all items
3. Click **Export to CSV**
4. Download the file

### Export with Filters

Apply filters before exporting:

| Filter | Effect |
|--------|--------|
| Status | Only items with selected status |
| Product | Only items from selected product |
| Category | Only items in selected category |
| Date range | Only items within dates |

### Exported Fields

The CSV includes:

| Column | Description |
|--------|-------------|
| ID | WordPress post ID |
| Title | Item title |
| Description | Full content |
| Status | Current status slug |
| Priority | Priority level |
| Progress | Percentage complete |
| Category | Assigned category |
| Tags | Comma-separated tags |
| Product | Assigned product |
| Votes | Vote count |
| Target Start | Start date |
| Target End | End date |
| Created | Publish date |
| Modified | Last update |

### Export Use Cases

**Stakeholder reports**
Export and share with people who prefer Excel over WordPress.

**Data analysis**
Analyze votes, progress, and status distribution in spreadsheet.

**Backup**
Regular exports create data backups outside WordPress.

**Migration prep**
Export before moving to another system.

---

## Importing Data

### Download Sample CSV

Before importing, get the template:

1. Go to **Roadmap > Import**
2. Click **Download Sample CSV**
3. Open in Excel, Google Sheets, or any CSV editor

### Prepare Your Data

Fill in the template columns:

| Column | Required | Format |
|--------|----------|--------|
| Title | Yes | Text |
| Description | No | Text (HTML OK) |
| Status | No | Status slug (e.g., "planned") |
| Priority | No | low, medium, high, critical |
| Progress | No | Number 0-100 |
| Category | No | Category slug |
| Tags | No | Comma-separated slugs |
| Product | No | Product slug |
| Target Start | No | YYYY-MM-DD |
| Target End | No | YYYY-MM-DD |

### Import Process

1. Go to **Roadmap > Import**
2. Click **Choose File** and select your CSV
3. Click **Upload and Preview**
4. Review the preview to verify data mapping
5. Click **Import Items**
6. View import results

### Import Options

| Option | Effect |
|--------|--------|
| Default status | Status for items without one |
| Default product | Product for items without one |
| Update existing | Match by title, update if exists |
| Skip duplicates | Don't import if title matches |

---

## CSV Format Requirements

### Encoding

- **Required**: UTF-8 encoding
- Excel users: Save As > CSV UTF-8

### Separators

- **Field separator**: Comma (,)
- **Text qualifier**: Double quotes (")

### File Size

- **Maximum**: 5MB
- For larger imports, split into multiple files

### Date Format

- **Format**: YYYY-MM-DD (e.g., 2024-06-15)
- Invalid dates are skipped

### HTML in Descriptions

You can include HTML in the description field:
```
"<p>This is a <strong>feature</strong> description.</p>"
```

Wrap in quotes to preserve formatting.

---

## Updating Existing Items

### How Matching Works

With "Update existing" enabled:
1. Import checks each row's title
2. If a match is found, that item is updated
3. If no match, a new item is created

### What Gets Updated

- Description (if provided)
- Status (if provided)
- Priority (if provided)
- Progress (if provided)
- Categories/Tags (if provided)
- Product (if provided)
- Dates (if provided)

### What Doesn't Change

- WordPress post ID
- Votes (preserved)
- Comments (preserved)
- Created date (preserved)

---

## Migration Workflows

### From Trello

1. Export Trello board to CSV
2. Map columns:
   - Trello "Card Name" → Title
   - Trello "Description" → Description
   - Trello "List" → Status (may need mapping)
3. Add any missing columns
4. Import to Product Roadmap

### From Jira

1. Export Jira issues to CSV
2. Map columns:
   - Jira "Summary" → Title
   - Jira "Description" → Description
   - Jira "Status" → Status (map to your statuses)
   - Jira "Priority" → Priority
3. Import to Product Roadmap

### From Google Sheets

1. Create/edit your roadmap in Google Sheets
2. File > Download > CSV
3. Import to Product Roadmap
4. Keep Sheet synced with periodic exports/imports

### From Excel

1. Prepare data following the template
2. File > Save As > CSV UTF-8
3. Import to Product Roadmap

---

## Error Handling

### Common Import Errors

| Error | Cause | Fix |
|-------|-------|-----|
| "Invalid CSV" | Wrong encoding | Save as UTF-8 |
| "Row skipped" | Missing title | Add title to row |
| "Invalid status" | Unknown status slug | Use valid status |
| "Invalid date" | Wrong date format | Use YYYY-MM-DD |

### Viewing Import Results

After import, you'll see:
- Number of items created
- Number of items updated
- Number of rows skipped
- Error details for failures

### Partial Imports

If some rows fail:
- Successfully imported items are kept
- Failed rows are listed with errors
- Fix errors in CSV and reimport

---

## Best Practices

### Before Importing

- Take a backup (export first)
- Test with a small batch
- Verify column mapping in preview
- Check status/category slugs match

### Data Quality

- Clean data before importing
- Remove duplicate rows
- Standardize status names
- Validate dates

### Regular Sync

If managing in spreadsheets:
- Export weekly for backup
- Import updates after changes
- Use "Update existing" to sync

---

## Troubleshooting

### Import file won't upload

- Check file is under 5MB
- Verify CSV format (not XLS or XLSX)
- Ensure file isn't empty

### All rows skipped

- Check column headers match expected names
- Verify title column has data
- Check encoding is UTF-8

### Status/category not assigned

- Verify slugs match exactly (case-sensitive)
- Check status/category exists in WordPress
- Use default settings for fallback

### Special characters garbled

- Re-save file as UTF-8
- Avoid copying from Word (use plain text)
- Check for hidden characters

---

## Get Import/Export

Import & Export is available with Product Roadmap Pro.

**Included in Pro:**
- CSV export with filters
- Bulk CSV import
- Sample template download
- Update existing items
- UTF-8 support
- 5MB file limit

[Get Product Roadmap Pro →](https://wbcomdesigns.com/downloads/product-roadmap-pro/)

---

## Next Steps

| Goal | Guide |
|------|-------|
| Track engagement | [Analytics](analytics.md) | Pro |
| Visual status management | [Drag & Drop](drag-drop.md) | Pro |
| Manage multiple products | [Multi-Product](multi-product.md) | Pro |
