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

namespace HeimrichHannot\FieldPalette;


class FieldPaletteHooks extends \Controller
{

	protected static $arrSkipTables = array('tl_formdata');
	protected static $intMaximumDepth = 10;
	protected static $intCurrentDepth = 0;

	public function executePostActionsHook($strAction, \DataContainer $dc)
	{
		if($strAction == FieldPalette::$strFieldpaletteRefreshAction)
		{
			if(\Input::post('field'))
			{
				\Controller::loadDataContainer($dc->table);

				$strName = \Input::post('field');
				$arrField = $GLOBALS['TL_DCA'][$dc->table]['fields'][$strName];

				// Die if the field does not exist
				if(!is_array($arrField))
				{
					header('HTTP/1.1 400 Bad Request');
					die('Bad Request');
				}

				/** @var \Widget $strClass */
				$strClass = $GLOBALS['BE_FFL'][$arrField['inputType']];

				// Die if the class is not defined or inputType is not fieldpalette
				if ($arrField['inputType'] != 'fieldpalette' || !class_exists($strClass))
				{
					header('HTTP/1.1 400 Bad Request');
					die('Bad Request');
				}

				$arrData = \Widget::getAttributesFromDca($arrField, $strName, $dc->activeRecord->{$strName}, $strName, $dc->table, $dc);

				/** @var \Widget $objWidget */
				$objWidget = new $strClass($arrData);
				$objWidget->currentRecord = $dc->id;

				die(json_encode(array('field' => $strName, 'target' => '#ctrl_'.$strName, 'content' => $objWidget->generate())));
			}

			header('HTTP/1.1 400 Bad Request');
			die('Bad Request');
		}
	}

	public function initializeSystemHook()
	{
		FieldPalette::adjustBackendModules();
	}

	/**
	 * Add fieldpalette fields to tl_fieldpalette
	 * @param string $strName
	 * @return boolean false if Datacontainer not supported
	 */
	public function loadDataContainerHook($strName)
	{
		if($strName !== \Config::get('fieldpalette_table') && static::$intCurrentDepth++ < static::$intMaximumDepth)
		{
			return false;
		}

		\Controller::loadDataContainer($strName);

		$dc = &$GLOBALS['TL_DCA'][\Config::get('fieldpalette_table')];

		$this->registerFieldsetFields($dc, $strName);
	}

	protected function registerFieldsetFields(&$dc, $strName)
	{
		if(!is_array($dc['fields']))
		{
			return false;
		}

		// prevent endless loop within \Controller::loadDataContainer when not in backend database update mode
		if($strName != \Config::get('fieldpalette_table') && !(TL_MODE == 'BE' && \Input::get('update') == 'database'))
		{
			unset($GLOBALS['TL_HOOKS']['loadDataContainer']['fieldPalette']);
		}

		$arrTables = \Database::getInstance()->listTables();

		foreach($arrTables as $strTable)
		{
			if(in_array($strTable, static::$arrSkipTables))
			{
				continue;
			}

			if(!$GLOBALS['loadDataContainer'][$strTable])
			{
				\Controller::loadDataContainer($strTable);
			}

			$arrDCA = $GLOBALS['TL_DCA'][$strTable];

			$arrFields = $arrDCA['fields'];

			if(!is_array($arrFields)) continue;

			$blnFound = false;

			foreach ($arrFields as $strField => $arrData)
			{
				if(!is_array($arrData['fieldpalette']))
				{
					continue;
				}

				// add fields, for contao database update process
				$dc['fields'] = array_merge($dc['fields'], $arrData['fieldpalette']['fields']);

				FieldPaletteRegistry::set($strTable, $strField);

				// set active ptable
				if(FieldPalette::isActive($strTable, $strField))
				{
					\Controller::loadLanguageFile($strTable); // allow translations within parent fieldpalette table
					$dc = FieldPalette::getDca($strTable, $strField);
				}

				$blnFound = true;
			}


			if(!$blnFound)
			{
				FieldPalette::refuseFromBackendModuleByTable($strTable);
			}

		}
	}
}