# Change Log
All notable changes to this project will be documented in this file.

## [1.2.0] - 2016-10-26

### Added
- datatables support added to FieldPaletteWizard, it is now the default viewMode, can be changed within fieldpalette listing config `viewMode` parameter.
- datatables brings pagination, sort and search to fieldpalette widget

### Fixed
- Nested recursion error fixed, reworked the whole fieldpalette DCA registration
- Nested fieldpalettes fixed