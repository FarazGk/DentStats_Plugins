# DAT Score Converter Plugin

A WordPress plugin that provides a lookup interface for DAT scores and other data from tables created by the CSV-to-SQL plugin.

## Features

- **Admin Interface**: Easy configuration with dropdown selection of available database tables
- **Flexible Lookup**: Support for any table structure created by CSV-to-SQL plugin
- **Multiple Display Formats**: Table and list view options
- **AJAX-Powered**: Fast, responsive lookups without page reloads
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Security**: Proper nonce verification and SQL injection prevention
- **Accessibility**: Keyboard navigation and screen reader support

## Installation

1. Upload the `dat-score-converter` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'DAT Score Converter' in the admin menu to configure

## Configuration

1. Navigate to **DAT Score Converter** in the WordPress admin menu
2. Select a table from the dropdown (tables created by CSV-to-SQL plugin)
3. Choose a lookup column (e.g., "dat" for DAT scores)
4. Test the configuration using the test lookup feature

## Usage

### Shortcodes

#### Basic Lookup Interface
```
[dat_score_lookup]
```
Displays a form where users can enter a value and lookup corresponding scores.

#### Display Specific Score
```
[dat_score_display value="15"]
```
Displays the score data for a specific value without showing an input form.

#### Configuration Status
```
[dat_score_config]
```
Shows the current configuration status and settings.

### Shortcode Attributes

#### dat_score_lookup
- `format` - Display format: "table" or "list" (default: "table")
- `show_input` - Show input form: "true" or "false" (default: "true")
- `placeholder` - Input placeholder text (default: "Enter DAT score (e.g., 15)")
- `button_text` - Submit button text (default: "Lookup Scores")
- `error_message` - Error message for no results (default: "No scores found for the entered value.")
- `success_title` - Title for results section (default: "Score Results")

#### dat_score_display
- `value` - The lookup value (required)
- `format` - Display format: "table" or "list" (default: "table")
- `show_title` - Show results title: "true" or "false" (default: "true")
- `title` - Results title text (default: "Score Results")

#### dat_score_config
- `show_status` - Show configuration status: "true" or "false" (default: "true")
- `show_table` - Show table name: "true" or "false" (default: "true")
- `show_column` - Show lookup column: "true" or "false" (default: "true")

### Examples

```
[dat_score_lookup format="list" placeholder="Enter your DAT score"]
[dat_score_display value="20" format="table"]
[dat_score_config show_status="true"]
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Tables created by the CSV-to-SQL plugin

## Security Features

- Nonce verification for all AJAX requests
- SQL injection prevention through prepared statements
- Input sanitization and validation
- Table and column name validation
- Error handling and logging

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## File Structure

```
dat-score-converter/
├── dat-score-converter.php          # Main plugin file
├── includes/
│   ├── admin-interface.php          # Admin interface
│   ├── lookup-functions.php         # Core lookup functions
│   └── shortcodes.php               # Frontend shortcodes
├── assets/
│   ├── style.css                    # Frontend styles
│   └── script.js                    # Frontend JavaScript
└── README.md                        # This file
```

## Development

### Adding New Features

1. **New Display Formats**: Add format functions in `lookup-functions.php`
2. **New Shortcodes**: Add shortcode functions in `shortcodes.php`
3. **Admin Features**: Extend `admin-interface.php`
4. **Frontend Enhancements**: Modify `script.js` and `style.css`

### Hooks and Filters

The plugin provides several hooks for customization:

```php
// Filter the lookup result before display
add_filter('dat_score_lookup_result', 'my_custom_filter', 10, 2);

// Action after successful lookup
add_action('dat_score_lookup_success', 'my_custom_action', 10, 2);
```

## Troubleshooting

### Common Issues

1. **"No table or lookup column configured"**
   - Go to admin panel and select a table and lookup column

2. **"Table does not exist"**
   - Ensure the table was created by CSV-to-SQL plugin
   - Check table name in database

3. **"No data found"**
   - Verify the lookup value exists in the selected column
   - Check for typos in the lookup value

4. **AJAX errors**
   - Check browser console for JavaScript errors
   - Verify nonce is properly generated

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and feature requests, please contact the plugin author or create an issue in the plugin repository.

## Changelog

### Version 1.0
- Initial release
- Admin interface with table selection
- Basic lookup functionality
- Multiple display formats
- Responsive design
- Security features

