# Change Log
All notable changes to this project will be documented in this file.

## [1.2.9] - 2017-02-21

### Added
- Fixed saveNclose when contao referrer session failure

## [1.2.8] - 2017-02-21

### Added
- Handle saveNclose within fieldpalette modal

## [1.2.7] - 2017-01-10

### Fixed
- Add mandatory label to header field

## [1.2.6] - 2016-12-12

### Fixed
- fixed class issue in js

## [1.2.5] - 2016-12-12

### Changed
- removed bundled js libraries and referenced them by composer

## [1.2.4] - 2016-12-08

### Fixed
- fixed label handling

## [1.2.3] - 2016-12-05

### Changed
- jquery support within backend is now provided by haste_plus

## [1.2.2] - 2016-11-29

### Fixed
- fixed array check

## [1.2.1] - 2016-11-11

### Fixed
- disable internal cache for tl_fieldpalette dca container, otherwise dynamically added fields will be removed from database or not added

## [1.2.0] - 2016-10-26

### Added
- datatables support added to FieldPaletteWizard, it is now the default viewMode, can be changed within fieldpalette listing config `viewMode` parameter.
- datatables brings pagination, sort and search to fieldpalette widget

### Fixed
- Nested recursion error fixed, reworked the whole fieldpalette DCA registration
- Nested fieldpalettes fixed
