# QARR Changelog

## 1.2.4 - 2020-12-26

### Fixed
- Fixed average rating now updating after ratings were deleted
- Fixed display dropdown fields render issues

### Added
- Added option to display all reviews or questions with html markup
- You can now post answers to questions from CP

## 1.2.3 - 2020-10-03

### Fixed
- Fixed a bug that would sometimes throw an error on the dashboard and element index pages

## 1.2.2.2 - 2020-09-14

### Fixed
- Displays will now load Layout Designer based on your Craft version compatibility

## 1.2.2.1 - 2020-09-14

### Fixed
- Fixed image configuration on Element Index table 

## 1.2.2 - 2020-09-14

### Fixed
- Craft 3.5 compatibility
- Fixed JS errors
- Fixed EVENT_AFTER_SAVE on plugin load
- Make QARR compatible with custom ElementTypes (Thanks @francoislevesque)

## 1.2.1 - 2019-09-25

### Fixed
- Fixed `Serialization of 'Closure' is not allowed` bug when posting a question
- Fixed bug where Rules were not applied to submissions
- Fixed styles for Answers on questions edit page

### Improved
- Submitted items that are flagged by Rules or reported as abuse will now display on element index and edit pages

## 1.2.0 - 2019-09-17

### Improved
- Updated plugin UI to coexist with Craft's UI (sorry to going back and forth with these UI style changes...)
- Added additional element columns (Stars, Location) + ability to configure element assets from the element index page

### Added
- Added soft deletes to Reviews, Questions and Displays
- Added ability to return objects instead of markup for functions `displayQuestions()`, `displayReviews()` and `displayRating()`
- Elements now display if submission is by a verified purchaser. (Only for Commerce plugin) [See Details](https://github.com/owldesign/QARR/issues/22)
- Added Email Template configuration for Correspondence [See Docs](https://docs.qarr.tools/campaigns/email-templates/). 


## 1.1.9 - 2019-08-29

### Fixed
- Fixed allowAnonymous support

## 1.1.8 - 2019-07-04

### Fixed
- Fixed a bug where plugin settings have not been set yet, prevented access to CP

## 1.1.7 - 2019-07-03

### Added
- Added ability to generate direct links for reviews and questions [See Docs](https://docs.qarr.tools/campaigns/)


## 1.1.6 - 2019-06-30

### Added
- Now you can add Asset Fields and file uploads to custom templates [Displays](https://docs.qarr.tools/custom/displays/)
- Added validation errors to custom templates


## 1.1.5 - 2019-06-28

### Added
- Added custom templates for posting answers [Answer Form](https://docs.qarr.tools/custom/answers/) 
- Added custom templates for reporting abuse [Abuse Form](https://docs.qarr.tools/custom/abuse/) 

### Improved
- Updated [Documentation](https://docs.qarr.tools/) 

## 1.1.4.1 - 2019-06-28

### Fixed
- Fixed pending button style

## 1.1.4 - 2019-06-28

### Fixed
- Various bug fixes
- Missing functions (such as answer deletes, etc)

### Improved
- UI complete update, for better experience and usability

### Added
- Custom element index columns (if you run into errors after update, you might need to clear element index from db)


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
