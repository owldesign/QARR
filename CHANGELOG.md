# QARR Changelog

## 1.1.3 - 2019-06-17

### Fixed
- Updating missing columns from the Install.php file, again. Ooops.

## 1.1.2 - 2019-06-17

### Fixed
- Missing `hasPurchased` column from the Install.php file

## 1.1.1 - 2019-06-17

### Fixed
- Version bump, missing schema version required for migrations

## 1.1.0 - 2019-06-17

### Improved
- Channels and Single pages can now have reviews and questions!!!
- CP UI updates
- Use html form to submit Reviews [Reviews](https://docs.qarr.tools/custom/reviews) 
- Use html form to submit Questions [Questions](https://docs.qarr.tools/custom/questions)
- A bunch of housekeeping and code cleanup.

### Added
- Custom templates for frontend [Usage](https://docs.qarr.tools/custom/templates)
- Added jQuery plugin for frontend built-in templates + jQuery will be automatically added if not already. [Details](https://docs.qarr.tools/core/details)

### Added
- Bug fixes


## 1.0.9 - 2019-02-16

### Fixed
- Fixed errors when product would be missing
- Fixed bug on Questions that do not have rules assigned to them

## 1.0.8 - 2019-01-29

### Fixed
- Fixed a bug where checking pending entries would break the plugin and cp in PHP 7.3

## 1.0.7 - 2019-01-24

## Fixed
- Fixed CDN error for frontend display

### Improved
- Improved calls to get array of reviews & questions `craft.qarr.reviews().all()`

## 1.0.6 - 2018-11-20

### Added
- Added Top Submissions by country and continent to qarr dashboard.

### Improved
- Move Rules to job ques

## Fixed
- fixed bug in applying rules to submissions + various other bugs
- added rules and geolocations to Questions element


## 1.0.5 - 2018-11-16

## Fixed
- fixed bug inside rules 

## 1.0.4 - 2018-11-16

### Added
- Added Rules [Documentation](https://docs.qarr.tools/rules)
- Added geolocation for submissions (for location stats & charts)

### Improved
- Improved UI

## Fixed
- Handful of bug fixes

## 1.0.3 - 2018-11-08

### Added
- Added craft dashboard widgets for Overall Stats and Recent Submissions

## 1.0.2 - 2018-11-05

### Added
- Star filtering and sorting [Screenshot](https://s3-us-west-2.amazonaws.com/qarr/demos/filter-sorting.jpg)

## 1.0.1 - 2018-11-02

### Improved
- Updated UI to closely match Crafts' UI
- [Documentation](https://docs.qarr.tools)

### Added
- Ability to quickly moderate pending submissions from QARR dashboard [gif](https://s3-us-west-2.amazonaws.com/qarr/demos/quick-moderate.gif)

## 1.0.0 - 2018-09-11

### Added
- Initial release
