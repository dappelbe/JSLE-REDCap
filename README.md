Calculations needed:

# ACR/SLICC

- SLIC Clinical
- SLIC Immunological
- SLICC Total

# Annual Assessment
- SLICC Total

# Module setting: Dashboard display type

When enabling/configuring this module at project level, use the setting:

- `uol-jsle-dasboard-display-type`
  - `1` = Standard - Display Expected if no forms, else display Actual date
  - `2` = Enhanced - Display both expected and actual
  - Default = `1`

## PHP snippet: retrieve this project setting value

```php
// Inside your External Module class (e.g. UoL_JSLE_v0.1.php)
$displayType = $this->getProjectSetting('uol-jsle-dasboard-display-type');

if ($displayType === '2') {
    // Enhanced - show both expected and actual
} else {
    // Standard (default) - expected if no forms, else actual
}
```