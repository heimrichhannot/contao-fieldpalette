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


    public static function loadDynamicPaletteByParentTable($strAct, $strTable, &$dc)
    {
        switch ($strAct)
        {
            case 'create':
                $strParentTable = FieldPalette::getParentTableFromRequest();
                $strPalette     = FieldPalette::getPaletteFromRequest();
                break;
            case 'cut':
            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
                $id = strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;

                $objModel = \HeimrichHannot\FieldPalette\FieldPaletteModel::findByPk($id);

                if ($objModel === null)
                {
                    break;
                }

                $strParentTable = FieldPalette::getParentTable($objModel, $objModel->id);
                $strPalette = $objModel->pfield;


                // set back link from request
                if(\Input::get('popup') && \Input::get('popupReferer'))
                {
                    $arrSession = \Session::getInstance()->getData();

                    if(class_exists('\Contao\StringUtil'))
                    {
                        $arrSession['popupReferer'][TL_REFERER_ID]['current'] = \StringUtil::decodeEntities(rawurldecode(\Input::get('popupReferer')));
                    }
                    else {
                        $arrSession['popupReferer'][TL_REFERER_ID]['current'] = \StringUtil::decodeEntities(rawurldecode(\Input::get('popupReferer')));
                    }

                    \Session::getInstance()->setData($arrSession);
                }

                break;
        }

        if(!$strParentTable || !$strPalette)
        {
            return false;
        }

        if($strTable !== $strParentTable)
        {
            \Controller::loadDataContainer($strParentTable);
        }

        static::registerFieldPalette($dc, $strParentTable, $strTable, $strPalette);
    }

    public static function registerFieldPalette(&$dc, $strTable, $strLoadedTable, $strPalette = null)
    {
        if (!is_array($dc['fields']))
        {
            return false;
        }

        $arrDCA = $GLOBALS['TL_DCA'][$strTable];

        // request nested fieldpalette
        if($strLoadedTable == \Config::get('fieldpalette_table') && $strTable !== $strLoadedTable)
        {
            $arrDCA = $dc;
        }

        $arrFields = $arrDCA['fields'];

        if (!is_array($arrFields))
        {
            return false;
        }

        if ($strPalette !== null)
        {
            if (!isset($arrFields[$strPalette]))
            {
                return false;
            }

            $arrFields = array($strPalette => $arrFields[$strPalette]);
        }

        $blnFound = static::registerFieldPaletteFields($dc, $strTable, $strLoadedTable, $arrFields);

        if (!$blnFound)
        {
            static::refuseFromBackendModuleByTable($strTable);
        }
    }

    public static function registerFieldPaletteFields(&$dc, $strTable, $strLoadedTable, $arrFields, $blnFound = false)
    {
        foreach ($arrFields as $strField => $arrData)
        {
            if (!is_array($arrData) || !is_array($arrData['fieldpalette']))
            {
                continue;
            }


            // add fields, for contao database update process
            $dc['fields'] = array_merge($dc['fields'], $arrData['fieldpalette']['fields']);

            // support fieldpalette nesting
            static::registerFieldPaletteFields($dc, $strTable, $strLoadedTable, $arrData['fieldpalette']['fields'], $blnFound);

            FieldPaletteRegistry::set($strLoadedTable, $strField, $dc);

            // set active ptable
            if (static::isActive($strLoadedTable, $strField))
            {
                \Controller::loadLanguageFile($strLoadedTable); // allow translations within parent fieldpalette table
                $dc = static::getDca($strLoadedTable, $strField);
            }

            $blnFound = true;
        }

        return $blnFound;
    }

	public static function isActive($strTable, $strField)
	{
	    $arrRegistry = FieldPaletteRegistry::get($strTable);

		if(!isset($arrRegistry[$strField]))
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

	public static function getParentTableFromRequest()
    {
        return \Input::get(static::$strTableRequestKey);
    }

	public static function getPaletteFromRequest()
	{
		return \Input::get(static::$strPaletteRequestKey);
	}

	public static function getParentTable($objModel, $intId)
    {
        if($objModel->ptable == \Config::get('fieldpalette_table'))
        {
            $objModel = \HeimrichHannot\FieldPalette\FieldPaletteModel::findByPk($objModel->pid);

            if($objModel === null)
            {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['fieldPaletteNestedParentTableDoesNotExist'], $intId));
            }

            return static::getParentTable($objModel, $intId);
        }

        return $objModel->ptable;
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
				if(!is_array($arrModule) || !is_array($arrModule['tables']))
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