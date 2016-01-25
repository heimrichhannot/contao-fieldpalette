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


use Contao\DC_Table;

class FieldPaletteWizard extends \Widget
{
	/**
	 * Submit user input
	 *
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'be_fieldpalette';

	protected $arrDca = array();

	protected $objModels;

	protected $arrButtonDefaults = array();


	public function __construct($arrAttributes = null)
	{
		parent::__construct($arrAttributes);

		\Controller::loadLanguageFile(\Config::get('fieldpalette_table'));
		\Controller::loadLanguageFile($this->strTable);

		$this->import('Database');

		$this->arrDca = \HeimrichHannot\FieldPalette\FieldPalette::getDca($this->strTable, $this->strName);
	}


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$this->reviseTable();

		$this->addCssFiles();
		
		$this->objModels = FieldPaletteModel::findByPidAndTableAndField($this->currentRecord, $this->strTable, $this->strName);

		$this->arrButtonDefaults = array
		(
			'do'              => \Input::get('do'),
			'ptable'          => $this->strTable,
			'table'           => \Config::get('fieldpalette_table'),
			'pid'             => $this->currentRecord,
			'fieldpalette'    => $this->strName,
			'fieldpaletteKey' => FieldPalette::$strPaletteRequestKey,
			'popup'           => true,
			'syncId'          => 'ctrl_' . $this->strId,
			'pfield'		  => $this->strId,
		);
		
		$objT = new \FrontendTemplate('fieldpalette_wizard_default');

		$objT->buttons  = $this->generateGlobalButtons();
		$objT->listView = $this->generateListView();
		$objT->strId    = $this->strId;

		$varValue = array();

		if ($this->objModels !== null) {
			$varValue = $this->objModels->fetchEach('id');
		}

		$objT->value   = $varValue;
		$objT->strName = $this->strName;

		return $objT->parse();
	}

	protected function generateListView()
	{
		$objT            = new \FrontendTemplate('fieldpalette_listview_default');
		$objT->label     = $this->strLabel;
		$objT->strId     = $this->strId;
		$objT->sortable  = !$this->arrDca['config']['notSortable'];
		$objT->labelIcon = '<img src="system/modules/fieldpalette/assets/img/fieldpalette.png" width="16" height="16" alt="">';

		$arrItems = array();
		$i        = 0;

		if ($this->objModels !== null) {
			while ($this->objModels->next()) {
				$arrItems[] = $this->generateListItem($this->objModels->current(), $i++);
			}
		}

		$objT->items = $arrItems;

		return $objT->parse();
	}

	protected function generateItemLabel($objRow, $folderAttribute)
	{
		$blnProtected = false;
		$showFields   = $this->arrDca['list']['label']['fields'];

		foreach ($showFields as $k => $v) {
			// Decrypt the value
			if ($this->arrDca['fields'][$v]['eval']['encrypt']) {
				$objRow->$v = \Encryption::decrypt(deserialize($objRow->$v));
			}

			if (strpos($v, ':') !== false) {
				list($strKey, $strTable) = explode(':', $v);
				list($strTable, $strField) = explode('.', $strTable);

				$objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
					->limit(1)
					->execute($objRow->$strKey);

				$args[$k] = $objRef->numRows ? $objRef->$strField : '';
			} elseif (in_array($this->arrDca['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10))) {
				$args[$k] = \Date::parse(\Config::get('datimFormat'), $objRow->$v);
			} elseif ($this->arrDca['fields'][$v]['inputType'] == 'checkbox' && !$this->arrDca['fields'][$v]['eval']['multiple']) {
				$args[$k] =
					($objRow->$v != '') ? (isset($this->arrDca['fields'][$v]['label'][0]) ? $this->arrDca['fields'][$v]['label'][0] : $v) : '';
			} else {
				$args[$k] = $this->arrDca['fields'][$v]['reference'][$objRow->$v] ?: $objRow->$v;
			}
		}

		$label = vsprintf(((strlen($this->arrDca['list']['label']['format'])) ? $this->arrDca['list']['label']['format'] : '%s'), $args);

		// Shorten the label if it is too long
		if ($this->arrDca['list']['label']['maxCharacters'] > 0
			&& $this->arrDca['list']['label']['maxCharacters'] < utf8_strlen(
				strip_tags($label)
			)
		) {
			$label = trim(\StringUtil::substrHtml($label, $this->arrDca['list']['label']['maxCharacters'])) . ' …';
		}

		// Call the label_callback ($row, $label, $this)
		if (is_array($this->arrDca['list']['list']['label']['label_callback'])) {
			$strClass  = $this->arrDca['list']['list']['label']['label_callback'][0];
			$strMethod = $this->arrDca['list']['list']['label']['label_callback'][1];

			$this->import($strClass);

			return $this->$strClass->$strMethod($objRow->row(), $label, $this, $folderAttribute, false, $blnProtected);
		} elseif (is_callable($this->arrDca['list']['list']['label']['label_callback'])) {
			return $this->arrDca['list']['list']['label']['label_callback']($objRow->row(), $label, $this, $folderAttribute, false, $blnProtected);
		} else {
			return $label;
		}

		return $label;

	}

	/**
	 * Compile buttons from the table configuration array and return them as HTML
	 *
	 * @param array   $arrRow
	 * @param string  $strTable
	 * @param array   $arrRootIds
	 * @param boolean $blnCircularReference
	 * @param array   $arrChildRecordIds
	 * @param string  $strPrevious
	 * @param string  $strNext
	 *
	 * @return string
	 */
	protected function generateButtons($objRow, $arrRootIds = array(), $blnCircularReference = false, $arrChildRecordIds = null, $strPrevious = null, $strNext = null)
	{
		if (empty($this->arrDca['list']['operations'])) {
			return '';
		}

		$return = '';
		
		$dc               = new DC_Table(\Config::get('fieldpalette_table'));
		$dc->id           = $this->currentRecord;
		$dc->activeRecord = $objRow;
		
		foreach ($this->arrDca['list']['operations'] as $k => $v) {
			$v  = is_array($v) ? $v : array($v);
			$id = specialchars(rawurldecode($objRow->id));

			$label      = $v['label'][0] ?: $k;
			$title      = sprintf($v['label'][1] ?: $k, $id);
			$attributes = ($v['attributes'] != '') ? ltrim(sprintf($v['attributes'], $id, $id)) : '';

			$objButton = FieldPaletteButton::getInstance();
			$objButton->addOptions($this->arrButtonDefaults);
			$objButton->setType($k);
			$objButton->setId($objRow->id);
			$objButton->setModalTitle(
				sprintf(
					$GLOBALS['TL_LANG']['tl_fieldpalette']['modalTitle'],
					$GLOBALS['TL_LANG'][$this->strTable][$this->strName][0] ?: $this->strName,
					sprintf($title, $objRow->id)
				)
			);
			$objButton->setAttributes(array($attributes));
			$objButton->setLabel(\Image::getHtml($v['icon'], $label));
			$objButton->setTitle(specialchars($title));
			
			// Call a custom function instead of using the default button
			if (is_array($v['button_callback'])) {
				$this->import($v['button_callback'][0]);
				$return .= $this->{$v['button_callback'][0]}->{$v['button_callback'][1]}(
					$objRow->row(),
					$objButton->getHref(),
					$label,
					$title,
					$v['icon'],
					$attributes,
					\Config::get('fieldpalette_table'),
					$arrRootIds,
					$arrChildRecordIds,
					$blnCircularReference,
					$strPrevious,
					$strNext,
					$dc
				);
				continue;
			} elseif (is_callable($v['button_callback'])) {
				$return .= $v['button_callback'](
					$objRow->row(),
					$objButton->getHref(),
					$label,
					$title,
					$v['icon'],
					$attributes,
					\Config::get('fieldpalette_table'),
					$arrRootIds,
					$arrChildRecordIds,
					$blnCircularReference,
					$strPrevious,
					$strNext,
					$dc
				);
				continue;
			}

			// Generate all buttons except "move up" and "move down" buttons
			if ($k != 'move' && $v != 'move') {
				$return .= $objButton->generate();
				continue;
			}

			$arrDirections = array('up', 'down');
			$arrRootIds    = is_array($arrRootIds) ? $arrRootIds : array($arrRootIds);

			foreach ($arrDirections as $dir) {
				$label = $GLOBALS['TL_LANG'][\Config::get('fieldpalette_table')][$dir][0] ?: $dir;
				$title = $GLOBALS['TL_LANG'][\Config::get('fieldpalette_table')][$dir][1] ?: $dir;

				$label = \Image::getHtml($dir . '.gif', $label);
				$href  = $v['href'] ?: '&amp;act=move';

				if ($dir == 'up') {
					$return .= ((is_numeric($strPrevious)
								 && (!in_array($objRow->id, $arrRootIds)
									 || empty($this->arrDca['list']['sorting']['root'])))
							? '<a href="' . $this->addToUrl(
								$href . '&amp;id=' . $objRow->id
							) . '&amp;sid=' . intval($strPrevious) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> '
							: \Image::getHtml(
								'up_.gif'
							)) . ' ';
					continue;
				}

				$return .= ((is_numeric($strNext)
							 && (!in_array($objRow->id, $arrRootIds)
								 || empty($this->arrDca['list']['sorting']['root']))) ? '<a href="' . $this->addToUrl(
							$href . '&amp;id=' . $objRow->id
						) . '&amp;sid=' . intval($strNext) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label
																						. '</a> ' : \Image::getHtml('down_.gif')) . ' ';
			}

		}

		// Sort elements
		if (!$this->arrDca['config']['notSortable']) {
			$href = 'contao/main.php';
			$href .= '?do=' . \Input::get('do');
			$href .= '&amp;table=' . \Config::get('fieldpalette_table');
			$href .= '&amp;id=' . $objRow->id;
			$href .= '&amp;' . FieldPalette::$strTableRequestKey . '=' . $this->strTable;
			$href .= '&amp;' . FieldPalette::$strPaletteRequestKey . '=' . $this->strName;
			$href .= '&amp;rt=' . \RequestToken::get();

			$return .= ' ' . \Image::getHtml(
					'drag.gif',
					'',
					'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG'][$this->strTable]['cut'][1], $objRow->id) . '" data-href="' . $href
					. '" data-id="' . $objRow->id . '" data-pid="' . $objRow->pid . '"'
				);
		}
		
		return trim($return);
	}

	protected function generateListItem($objRow, $intCount)
	{
		$objT = new \FrontendTemplate('fieldpalette_item_default');
		$objT->setData($objRow->row());

		$objT->folderAttribute = '';
		$objT->label           = $this->generateItemLabel($objRow, $objT->folderAttribute);
		$objT->buttons         = $this->generateButtons($objRow);
		$objT->strId           = sprintf('%s_%s_%s', $objRow->ptable, $objRow->pfield, $objRow->id);

		return $objT->parse();
	}

	protected function generateGlobalButtons()
	{
		$objCreateButton = FieldPaletteButton::getInstance();
		$objCreateButton->addOptions($this->arrButtonDefaults);
		$objCreateButton->setType('create');
		$objCreateButton->setModalTitle(
			sprintf(
				$GLOBALS['TL_LANG']['tl_fieldpalette']['modalTitle'],
				$GLOBALS['TL_LANG'][$this->strTable][$this->strName][0] ?: $this->strName,
				$GLOBALS['TL_LANG']['tl_fieldpalette']['new'][1]
			)
		);
		$objCreateButton->setLabel($GLOBALS['TL_LANG']['tl_fieldpalette']['new'][0]);
		$objCreateButton->setTitle($GLOBALS['TL_LANG']['tl_fieldpalette']['new'][0]);

		return $objCreateButton->generate();
	}

	/**
	 * Delete all incomplete and unrelated records
	 */
	protected function reviseTable()
	{
		$reload = false;
		$ptable = $this->arrDca['config']['ptable'];
		$ctable = $this->arrDca['config']['ctable'];

		$new_records = $this->Session->get('new_records');
		
		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['reviseTable']) && is_array($GLOBALS['TL_HOOKS']['reviseTable'])) {
			foreach ($GLOBALS['TL_HOOKS']['reviseTable'] as $callback) {
				$status = null;

				if (is_array($callback)) {
					$this->import($callback[0]);
					$status = $this->{$callback[0]}->{$callback[1]}($this->strTable, $new_records[$this->strTable], $ptable, $ctable);
				} elseif (is_callable($callback)) {
					$status = $callback($this->strTable, $new_records[$this->strTable], $ptable, $ctable);
				}

				if ($status === true) {
					$reload = true;
				}
			}
		}

		// Delete all new but incomplete records (tstamp=0)
		if (!empty($new_records[\Config::get('fieldpalette_table')]) && is_array($new_records[\Config::get('fieldpalette_table')])) {
			$objStmt = $this->Database->execute(
				"DELETE FROM " . \Config::get('fieldpalette_table') . " WHERE id IN(" . implode(
					',',
					array_map(
						'intval',
						$new_records[\Config::get('fieldpalette_table')]
					)
				) . ") AND tstamp=0"
			);

			if ($objStmt->affectedRows > 0) {
				$reload = true;
			}
		}

		// Delete all records of the current table that are not related to the parent table
		if ($ptable != '') {
			if ($this->arrDca['config']['dynamicPtable']) {
				$objStmt = $this->Database->execute(
					"DELETE FROM " . \Config::get('fieldpalette_table') . " WHERE ptable='" . $ptable . "' AND NOT EXISTS (SELECT * FROM " . $ptable
					. " WHERE " . \Config::get('fieldpalette_table') . ".pid = " . $ptable . ".id)"
				);
			} else {
				$objStmt = $this->Database->execute(
					"DELETE FROM " . \Config::get('fieldpalette_table') . " WHERE NOT EXISTS (SELECT * FROM " . $ptable . " WHERE " . \Config::get(
						'fieldpalette_table'
					) . ".pid = " . $ptable . ".id)"
				);
			}

			if ($objStmt->affectedRows > 0) {
				$reload = true;
			}
		}

		// Delete all records of the child table that are not related to the current table
		if (!empty($ctable) && is_array($ctable)) {
			foreach ($ctable as $v) {
				if ($v != '') {
					// Load the DCA configuration so we can check for "dynamicPtable"
					if (!isset($GLOBALS['loadDataContainer'][$v])) {
						\Controller::loadDataContainer($v);
					}

					if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable']) {
						$objStmt = $this->Database->execute(
							"DELETE FROM $v WHERE ptable='" . \Config::get('fieldpalette_table') . "' AND NOT EXISTS (SELECT * FROM " . \Config::get(
								'fieldpalette_table'
							) . " WHERE $v.pid = " . \Config::get('fieldpalette_table') . ".id)"
						);
					} else {
						$objStmt = $this->Database->execute(
							"DELETE FROM $v WHERE NOT EXISTS (SELECT * FROM " . \Config::get('fieldpalette_table') . " WHERE $v.pid = "
							. \Config::get('fieldpalette_table') . ".id)"
						);
					}

					if ($objStmt->affectedRows > 0) {
						$reload = true;
					}
				}
			}
		}

		// Reload the page
		if ($reload) {
			\Controller::reload();
		}
	}


	protected function addCssFiles()
	{
		if (TL_MODE == 'BE') {
			$GLOBALS['TL_CSS']['fieldpalette-wizard-be'] = 'system/modules/fieldpalette/assets/css/fieldpalette-wizard-be.css';
		}
	}
}


