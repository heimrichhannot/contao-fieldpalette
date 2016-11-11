# Change Log
All notable changes to this project will be documented in this file.

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