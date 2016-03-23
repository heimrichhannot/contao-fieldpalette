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


/**
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 *
 *
 * @method static \NewsletterModel|null findById($id, $opt=array())
 * @method static \NewsletterModel|null findByPk($id, $opt=array())
 * @method static \NewsletterModel|null findByIdOrAlias($val, $opt=array())
 * @method static \NewsletterModel|null findOneBy($col, $val, $opt=array())
 * @method static \NewsletterModel|null findOneByPid($val, $opt=array())
 * @method static \NewsletterModel|null findOneByTstamp($val, $opt=array())
 *
 * @method static \Model\Collection|\NewsletterModel|null findByPid($val, $opt=array())
 * @method static \Model\Collection|\NewsletterModel|null findByTstamp($val, $opt=array())
 * @method static \Model\Collection|\NewsletterModel|null findMultipleByIds($val, $opt=array())
 * @method static \Model\Collection|\NewsletterModel|null findBy($col, $val, $opt=array())
 * @method static \Model\Collection|\NewsletterModel|null findAll($opt=array())
 *
 * @method static integer countById($id, $opt=array())
 * @method static integer countByPid($val, $opt=array())
 * @method static integer countByTstamp($val, $opt=array())
 */
class FieldPaletteModel extends \Model
{
	protected static $strTable = 'tl_fieldpalette';


	/**
	 * Find all published fieldpalette elements by their ids
	 *
	 * @param array   $arrIds         An array of fielpalette ids
	 * @param array   $arrOptions     An optional options array
	 *
	 * @return \Model\Collection|\ContentModel|null A collection of models or null if there are no fieldpalette elements
	 */
	public static function findPublishedByIds(array $arrIds=array(), array $arrOptions=array())
	{
		$t = static::$strTable;

		$arrColumns = array("$t.id IN(" . implode(',', array_map('intval', $arrIds)) . ")");

		if (!BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, null, $arrOptions);
	}

	/**
	 * Find all published fieldpalette elements by their parent ID and parent table
	 *
	 * @param integer $intPid         The article ID
	 * @param string  $strParentTable The parent table name
	 * @param string  $strParentField The parent field name
	 * @param array   $arrOptions     An optional options array
	 *
	 * @return \Model\Collection|\ContentModel|null A collection of models or null if there are no fieldpalette elements
	 */
	public static function findPublishedByPidAndTableAndField($intPid, $strParentTable, $strParentField, array $arrOptions=array())
	{
		$t = static::$strTable;

		$arrColumns = array("$t.pid=? AND $t.ptable=? AND $t.pfield=?");

		if (!BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, array($intPid, $strParentTable, $strParentField), $arrOptions);
	}


	/**
	 * Find all fieldpalette elements by their parent ID and parent table
	 *
	 * @param integer $intPid         The article ID
	 * @param string  $strParentTable The parent table name
	 * @param string  $strParentField The parent field name
	 * @param array   $arrOptions     An optional options array
	 *
	 * @return \Model\Collection|\ContentModel|null A collection of models or null if there are no fieldpalette elements
	 */
	public static function findByPidAndTableAndField($intPid, $strParentTable, $strParentField, array $arrOptions=array())
	{
		$t = static::$strTable;

		$arrColumns = array("$t.pid=? AND $t.ptable=? AND $t.pfield=?");

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, array($intPid, $strParentTable, $strParentField), $arrOptions);
	}
}