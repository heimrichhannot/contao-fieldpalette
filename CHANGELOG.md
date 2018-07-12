# Changelog
All notable changes to this project will be documented in this file.

## [1.4.10] - 2018-07-12

### Fixed
- Do not die() in `HookListener::executePostActionsHook` if `$field['inputType']` is not `fieldpalette`, otherwise nested field `executePostActions` Hooks wont work anymore (e.g. multicolumneditor) 

## [1.4.9] - 2018-07-03

### Fixed
- correctly size modal window responsive 

## [1.4.8] - 2018-03-02

### Fixed
- toggle icon permission check for custom tables 

## [1.4.7] - 2018-02-15

### Fixed
- toggleVisibility

## [1.4.6] - 2018-02-29

### Fixed
- contao 4 backend style

## [1.4.5] - 2017-11-29

### Fixed
- user and user group field permisssion in contao 4

## [1.4.4] - 2017-11-13

### Fixed
- required to old haste_plus version

## [1.4.3] - 2017-11-13

### Fixed
- fieldpalette is not allowed within `edit` mode for current back end module exception

## [1.4.2] - 2017-11-08

### Fixed
- `Fieldpalette::adjustBackendModules` should not execute `Controller::loadDataContainer()` as there is no `BackendUser` available, that is required for default values of `author` fields inside `tl_news` etc. 

## [1.4.1] - 2017-11-08

### Fixed
- `FieldPaletteHooks::extractTableFields` when fieldpalette dca is not loaded yet

## [1.4.0] - 2017-11-01

### Added
- Support for custom tables (instead of `tl_fieldpalette`), for more details check the README.

## [1.3.19] - 2017-09-05

### Fixed
- php Documentation for `FieldpaletteModel` `@return` value

## [1.3.18] - 2017-09-05

### Added
- `FieldpaletteModel::findPublishedByIds` added `columns` and `values` arguments that will be merged with statement
- `FieldpaletteModel::findPublishedByPidAndTableAndField` added `columns` and `values` arguments that will be merged with statement
- `FieldpaletteModel::findPublishedByPidsAndTableAndField` added `columns` and `values` arguments that will be merged with statement
- `FieldpaletteModel::findByPidAndTableAndField` added `columns` and `values` arguments that will be merged with statement

## [1.3.17] - 2017-09-05

### Fix
- Database updates not showing up in Contao 3 Install tool

## [1.3.16] - 2017-08-18

### Added
- `FieldpaletteModel::findPublishedByPidsAndTableAndField`

## [1.3.15] - 2017-08-18

### Fixed
- contao 4 closeModal fixed

## [1.3.14] - 2017-08-18

### Fixed
- contao 4 fieldpalette javascript compatibility

## [1.3.13] - 2017-08-18

### Fixed
- contao 4 dca-extractor handling for `tl_fieldpalette`

## [1.3.12] - 2017-08-16

### Fixed
- contao 4 compatibility, removed 'main.php' from button link if contao version > 4

## [1.3.11] - 2017-08-15

### Fixed
- contao 4 compatibility, add extra field palette fields

## [1.3.10] - 2017-07-27

### Fixed
- provide all available fieldpalette fields within `tl_user_group` to provide proper permission handling

## [1.3.9] - 2017-07-18

### Fixed
- PHP7 compatibility

## [1.3.8] - 2017-06-27

### Fixed
- array check in FieldPalette.php

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
