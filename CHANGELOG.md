# Changelog
All notable changes to this project will be documented in this file.

## [1.3.7] - 2017-06-26

### Fixed
- fixed deps

## [1.3.6] - 2017-06-12

### Changed
- switched to heimrichhannot/datatables

## [1.3.5] - 2017-06-12

### Fixed
- array check in FieldPalette.php
- fixed config.php paths for optional contao 4 support
- removed traditional arrays

## [1.3.4] - 2017-05-11

### Fixed
- empty table error, within DC_Table::create

## [1.3.3] - 2017-05-09

### Fixed
- php 7 support, array handling in FieldPalette.php

## [1.3.2] - 2017-05-08

### Fixed
- `Fieldpalette::extractFieldPaletteFields` do not proceed when no array

## [1.3.1] - 2017-04-25

### Fixed
- list items had the wrong data container

## [1.3.0] - 2017-04-25

### Fixed
- nested fieldpalette issues

## [1.2.11] - 2017-04-12
- created new tag

## [1.2.10] - 2017-04-06

### Changed
- added php7 support, fixed contao-core dependency

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
