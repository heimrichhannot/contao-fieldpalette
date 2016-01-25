<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @package ${CARET}
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FieldPalette;


class FieldPalette
{

	/**
	 * Object instance (Singleton)
	 * @var \Session
	 */
	protected static $objInstance;

	public static $strTableRequestKey = 'ptable';

	public static $strPaletteRequestKey = 'fieldpalette';

	public static $strFieldpaletteRefreshAction = 'refreshFieldPaletteField';


	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final public function __clone() {}


	/**
	 * Return the object instance (Singleton)
	 *
	 * @return \FieldPalette The object instance
	 */
	public static function getInstance()
	{
		if (static::$objInstance === null)
		{
			static::$objInstance = new static();
		}

		return static::$objInstance;
	}


	public static function isActive($strTable, $strField)
	{
		if(!in_array($strField, FieldPaletteRegistry::get($strTable)))
		{
			return false;
		}

		// determine active state by current element
		if(\Input::get('table') == \Config::get('fieldpalette_table'))
		{
			$id = strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;

			switch (\Input::get('act'))
			{
				case 'edit':
				case 'show':
				case 'delete':
				case 'toggle':
					$objModel = \HeimrichHannot\FieldPalette\FieldPaletteModel::findByPk($id);

					if($objModel === null)
					{
						return false;
					}

					return ($strTable == $objModel->ptable && $strField == $objModel->pfield);
			}
		}

		return ($strTable == \Input::get(static::$strTableRequestKey) && $strField == \Input::get(static::$strPaletteRequestKey));
	}

	public static function getPaletteFromRequest()
	{
		return \Input::get(static::$strPaletteRequestKey);
	}

	public static function getDca($strTable, $strField)
	{
		\Controller::loadDataContainer($strTable);
		\Controller::loadDataContainer(\Config::get('fieldpalette_table'));

		$arrData = array();

		$arrDefaults = $GLOBALS['TL_DCA'][\Config::get('fieldpalette_table')];
		$arrCustom = $GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['fieldpalette'];

		if(!is_array($arrDefaults) || !is_array($arrCustom))
		{
			return $arrData;
		}

		// replace tl_fieldpalette with custom config
		$arrData = @array_replace_recursive($arrDefaults, $arrCustom); // supress warning, as long as references may exist in both arrays
		$arrData['config']['ptable'] = $strTable;
		$arrData['palettes']['default'] .= ';{published_legend},published'; // always append published
		
		return $arrData;
	}

	public static function adjustBackendModules()
	{
		foreach($GLOBALS['BE_MOD'] as $strGroup => $arrGroup)
		{
			if(!is_array($arrGroup))
			{
				continue;
			}

			foreach($arrGroup as $strModule => $arrModule)
			{
				if(!is_array($arrModule) && !is_array($arrModule['tables']))
				{
					continue;
				}
				
				$GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'][] = \Config::get('fieldpalette_table');
			}
		}
	}

	public static function refuseFromBackendModuleByTable($strTable)
	{
		foreach($GLOBALS['BE_MOD'] as $strGroup => $arrGroup)
		{
			if(!is_array($arrGroup))
			{
				continue;
			}

			foreach($arrGroup as $strModule => $arrModule)
			{
				if(!is_array($arrModule) && !is_array($arrModule['tables']))
				{
					continue;
				}

				if(!in_array($strTable, $arrModule['tables']) || ($idx = array_search(\Config::get('fieldpalette_table'), $arrModule['tables'])) === false)
				{
					continue;
				}

				unset($GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'][$idx]);
			}
		}
	}
}