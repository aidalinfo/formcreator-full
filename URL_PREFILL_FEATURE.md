# FormCreator URL Pre-fill Feature

## Overview
This feature allows pre-filling form fields by passing values through URL parameters. This is useful for integrating FormCreator forms with external systems or creating direct links with pre-populated data.

## How to Use

### URL Format
```
https://your-glpi-instance/plugins/formcreator/front/formdisplay.php?id=FORM_ID&field_FieldName=Value
```

### Parameters
- `id`: The form ID (required)
- `field_*`: Field values to pre-fill (optional)
  - Prefix field names with `field_`
  - Field names must match exactly with question names in the form
  - Values should be URL-encoded

### Examples

#### Simple Text Field
```
/front/formdisplay.php?id=1&field_EmployeeName=John%20Doe
```
This will pre-fill the "EmployeeName" field with "John Doe"

#### Multiple Fields
```
/front/formdisplay.php?id=1&field_EmployeeName=John%20Doe&field_TicketID=1234&field_Department=IT
```
This will pre-fill:
- EmployeeName: "John Doe"
- TicketID: "1234"
- Department: "IT"

#### Special Characters
```
/front/formdisplay.php?id=1&field_Description=Issue%20with%20printer%20%26%20scanner
```
This will pre-fill the Description field with "Issue with printer & scanner"

## Implementation Details

### Modified Files

1. **front/formdisplay.php**
   - Captures URL parameters starting with `field_`
   - Passes them to the `displayUserForm()` method

2. **inc/form.class.php**
   - Modified `displayUserForm()` to accept URL values parameter
   - Passes values to FormAnswer for processing

3. **inc/formanswer.class.php**
   - Added `setInitialAnswers()` method
   - Maps field names to question IDs
   - Prepares answers array for form fields

4. **inc/abstractfield.class.php**
   - No modification needed
   - Existing `setFormAnswer()` method handles pre-filled values

### How It Works

1. User accesses form URL with `field_*` parameters
2. `formdisplay.php` extracts parameters and removes `field_` prefix
3. `Form::displayUserForm()` receives the values
4. `FormAnswer::setInitialAnswers()` maps field names to question IDs
5. Values are stored in the answers array
6. When form renders, each field checks for pre-filled values
7. Fields display with pre-filled values from URL

## Limitations

- Field names in URLs must match exactly with question names in the form
- HTML tags and special characters in question names should be avoided
- Complex field types (like file uploads) cannot be pre-filled via URL
- Values are not validated until form submission

## Security Considerations

- URL parameters are visible in browser history and server logs
- Avoid passing sensitive information through URLs
- Values are sanitized before display to prevent XSS attacks
- Form validation still applies on submission

## Testing

Run the test script to verify the implementation:
```bash
php test_url_prefill.php
```

Or test manually:
1. Create a form with various field types
2. Note the form ID and field names
3. Access the form with URL parameters
4. Verify fields are pre-filled correctly

## Troubleshooting

### Fields Not Pre-filling
- Check that field names match exactly (case-sensitive)
- Ensure URL encoding for special characters
- Verify the form ID is correct
- Check browser console for JavaScript errors

### Special Characters Issues
- Always URL-encode parameter values
- Use %20 for spaces, %26 for &, etc.

### Performance
- Large numbers of pre-filled fields may impact page load time
- Consider using POST requests for large data sets