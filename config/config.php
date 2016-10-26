<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @package fieldpalette
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

/**
 * Config
 */
$GLOBALS['TL_CONFIG']['fieldpalette_table'] = 'tl_fieldpalette';

/**
 * Back end form fields
 */
$GLOBALS['BE_FFL']['fieldpalette'] = 'HeimrichHannot\FieldPalette\FieldPaletteWizard';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer']['fieldPalette']  = array('HeimrichHannot\FieldPalette\FieldPaletteHooks', 'loadDataContainerHook');
$GLOBALS['TL_HOOKS']['initializeSystem']['fieldPalette']   = array('HeimrichHannot\FieldPalette\FieldPaletteHooks', 'initializeSystemHook');
$GLOBALS['TL_HOOKS']['executePostActions']['fieldPalette'] = array('HeimrichHannot\FieldPalette\FieldPaletteHooks', 'executePostActionsHook');
$GLOBALS['TL_HOOKS']['sqlGetFromDca']['fieldPalette']      = array('HeimrichHannot\FieldPalette\FieldPaletteHooks', 'sqlGetFromDcaHook');

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_fieldpalette'] = 'HeimrichHannot\FieldPalette\FieldPaletteModel';


/**
 * Assets
 */
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_JAVASCRIPT']['jquery']            = 'assets/jquery/core/' . $GLOBALS['TL_ASSETS']['JQUERY'] . '/jquery.min.js';
    $GLOBALS['TL_JAVASCRIPT']['jquery-noconflict'] = 'system/modules/fieldpalette/assets/js/jquery-noconflict.js';


    $GLOBALS['TL_JAVASCRIPT']['datatables-i18n'] = 'system/modules/fieldpalette/assets/vendor/datatables-i18n/datatables-i18n.min.js';
    $GLOBALS['TL_JAVASCRIPT']['datatables-core'] = 'system/modules/fieldpalette/assets/vendor/datatables.net/js/jquery.dataTables.min.js';
    $GLOBALS['TL_JAVASCRIPT']['datatables-rowReorder'] = 'system/modules/fieldpalette/assets/vendor/datatables-RowReorder/js/dataTables.rowReorder.min.js';

    $GLOBALS['TL_CSS']['datatables-core'] = 'system/modules/fieldpalette/assets/vendor/datatables.net-dt/css/jquery.dataTables.min.css';
    $GLOBALS['TL_CSS']['datatables-rowReorder'] = 'system/modules/fieldpalette/assets/vendor/datatables-RowReorder/css/rowReorder.dataTables.min.css';

    $GLOBALS['TL_JAVASCRIPT']['fieldpalette-be.js'] = 'system/modules/fieldpalette/assets/js/fieldpalette-be.js' . (TL_MODE == 'BE' ? '' : '|static');
    $GLOBALS['TL_CSS']['fieldpalette-wizard-be']    = 'system/modules/fieldpalette/assets/css/fieldpalette-wizard-be.css';
}
