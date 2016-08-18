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


use Contao\Input;

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

		if ($arrData['config']['hidePublished'])
		{
			$arrData['fields']['published']['inputType'] = 'hidden';
			$arrData['fields']['published']['default'] = true;
			unset($arrData['list']['operations']['toggle']);
			$arrData['palettes']['default'] .= ',published';
		}
		else
		{
			$arrData['palettes']['default'] .= ';{published_legend},published'; // always append published
		}

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

	/**
	 * Use this method as an oncopy_callback in order to support recursive copying fieldpalette records by copying their parent record
	 * @param $intNewId
	 */
	public function copyFieldPaletteRecords($intNewId)
	{
		if (TL_MODE != 'BE')
			return;

		$intId = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;
		$strTable = 'tl_' . \Input::get('do');

		if (!$intId || !$strTable)
			return;

		\Controller::loadDataContainer($strTable);
		$arrDcaFields = $GLOBALS['TL_DCA'][$strTable]['fields'];

		static::recursivelyCopyFieldPaletteRecords($intId, $intNewId, $strTable, $arrDcaFields);
	}

	/**
	 * @param $intPid int The id of the former parent record
	 * @param $intNewId int the id of the new parent record just copied from the former record
	 * @param $strTable string The parent table
	 * @param $arrDcaFields array A dca array of fields
	 */
	public static function recursivelyCopyFieldPaletteRecords($intPid, $intNewId, $strTable, array $arrDcaFields)
	{
		foreach ($arrDcaFields as $strField => $arrData)
		{
			if ($arrData['inputType'] == 'fieldpalette')
			{
				if (isset($arrData['fieldpalette']['fields']) && !$arrData['eval']['doNotCopy'])
				{
					$objFieldPaletteRecords = FieldPaletteModel::findByPidAndTableAndField($intPid, $strTable, $strField);

					if ($objFieldPaletteRecords === null)
						continue;

					while ($objFieldPaletteRecords->next())
					{
						$objFieldpalette = new FieldPaletteModel();

						// get existing data except id
						$arrFieldData = $objFieldPaletteRecords->row();
						unset($arrFieldData['id']);

						$objFieldpalette->setRow($arrFieldData);

						// set new data
						$objFieldpalette->tstamp = time();
						$objFieldpalette->pid = $intNewId;
						$objFieldpalette->published = true;

						if (isset($arrData['eval']['fieldpalette']['copy_callback']) && is_array($arrData['eval']['fieldpalette']['copy_callback']))
						{
							foreach ($arrData['eval']['fieldpalette']['copy_callback'] as $arrCallback)
							{
								if (is_array($arrCallback))
								{
									\System::importStatic($arrCallback[0]);
									$arrCallback[0]::$arrCallback[1]($objFieldpalette, $intPid, $intNewId, $strTable, $arrData);
								}
								elseif (is_callable($arrCallback))
								{
									$arrCallback($objFieldpalette, $intPid, $intNewId, $strTable, $arrData);
								}
							}
						}

						$objFieldpalette->save();

						static::recursivelyCopyFieldPaletteRecords($objFieldPaletteRecords->id, $objFieldpalette->id,
							\Config::get('fieldpalette_table'), $arrData['fieldpalette']['fields']);
					}
				}
			}
			else
			{
				if ($strTable == \Config::get('fieldpalette_table'))
				{
					$objFieldPaletteRecords = FieldPaletteModel::findByPidAndTableAndField($intPid, $strTable, $strField);

					if ($objFieldPaletteRecords === null)
						continue;

					while ($objFieldPaletteRecords->next())
					{
						$objFieldpalette = new FieldPaletteModel();
						$objFieldpalette->setRow($objFieldPaletteRecords->row());
						// set new data
						$objFieldpalette->tstamp = time();
						$objFieldpalette->pid = $intNewId;
						$objFieldpalette->published = true;

						if (isset($arrData['eval']['fieldpalette']['copy_callback']) && is_array($arrData['eval']['fieldpalette']['copy_callback']))
						{
							foreach ($arrData['eval']['fieldpalette']['copy_callback'] as $arrCallback)
							{
								if (is_array($arrCallback))
								{
									\System::importStatic($arrCallback[0]);
									$arrCallback[0]::$arrCallback[1]($objFieldpalette, $intPid, $intNewId, $strTable, $arrData);
								}
								elseif (is_callable($arrCallback))
								{
									$arrCallback($objFieldpalette, $intPid, $intNewId, $strTable, $arrData);
								}
							}
						}

						$objFieldpalette->save();
					}
				}
			}
		}
	}
}