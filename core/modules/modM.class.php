<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2021 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   m     Module M
 *  \brief      M module descriptor.
 *
 *  \file       htdocs/m/core/modules/modM.class.php
 *  \ingroup    m
 *  \brief      Description and activation file for module M
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module M
 */
class modM extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		parent::__construct($db);
		global $langs, $conf;
		$this->db = $db;

		$this->numero = 104333; // TODO

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'm';

		// available default families:
		// - base (core modules)
		// - crm (customer relationship management)
		// - financial (invoicing, accounting, etc.)
		// - hr (Human Resources)
		// - projects
		// - products
		// - ecm (Enterprise Content Management)
		// - technic (modules providing cross-module functionality)
		// - interface (for data interchange with other applications)
		// - other (core modules)
		$this->family = "other";

		$this->familyinfo = [
			'toy' => ['position' => '01', 'label' => $langs->trans('ModuleFamilyToy')]
		];

		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->version = '0.1.0';
		$this->editor_name = 'ATM Consulting';
		$this->editor_url = 'https://www.atm-consulting.fr';
		$this->module_position = '90';

//		$this->url_last_version = 'https://tech.atm-consulting.fr/xyz';

		// Key used in llx_const table to save module status enabled/disabled (where M is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		$this->picto = 'generic';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = [
			'triggers' => 0,
			'login' => 0,
			'substitutions' => 0,
			'menus' => 0,
			'tpl' => 0,
			'barcode' => 0,
			'models' => 0,
			'printing' => 0,
			'theme' => 0,
			'css' => [],
			'js' => [],
			'hooks' => [
//				'globalcard',
			],
			'moduleforexternal' => 0,
		];

		$_name = $this->name_lowercase = strtolower($this->name);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/m/temp","/m/subdir");
		$this->dirs = ['/' . $_name];
		$this->config_page_url = ["setup.php@" . $_name];

		$this->hidden = false;
		$this->depends = [];
		$this->requiredby = [];
		$this->conflictwith = [];
		$this->langfiles = [
			$_name . '@' . $_name
		];
		$this->phpmin = [7, 0]; // Minimum version of PHP required by module
		$this->need_dolibarr_version = [14, 0]; // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = [];
		$this->warnings_activation_ext = [];

		$this->const = [];

		if (!isset($conf->{$_name}) || !isset($conf->{$_name}->enabled)) {
			$conf->{$_name} = (object) ['enabled' => 0];
		}

		$this->tabs = [];
		$this->dictionaries = [];
		$this->boxes = [];
		$this->cronjobs = [];

		// Permissions provided by this module
		$this->rights = [];
		$this->_newRights(['read', 'write', 'delete'], 'instrument', 'M instruments');
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = [];

		$this->_newMenu([
			'path' => 'tools>M',
			'url' => '/' . $_name . '/index.php',
			'picto' => $this->picto,
		]);
		$this->_newMenu([
			'path' => 'tools>M>TuneEditor',
			'url' => '/' . $_name . '/index.php',
			'perms' => 'instrument->read',
		]);

		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT
		END MODULEBUILDER LEFTMENU MYOBJECT */

		// Exports profiles provided by this module
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/m/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		//include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		//$extrafields = new ExtraFields($this->db);
		//$result5=$extrafields->addExtraField('m_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'm@m', '$conf->m->enabled');

		// Permissions
		$this->remove($options);

		$sql = [];

		// Document templates
		$moduledir = 'm';
		$myTmpObjects = [];
		$myTmpObjects['MyObject'] = array('includerefgeneration'=>0, 'includedocgeneration'=>0);

//		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
//			if ($myTmpObjectKey == 'MyObject') {
//				continue;
//			}
//			if ($myTmpObjectArray['includerefgeneration']) {
//				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/m/template_myobjects.odt';
//				$dirodt = DOL_DATA_ROOT.'/doctemplates/m';
//				$dest = $dirodt.'/template_myobjects.odt';
//
//				if (file_exists($src) && !file_exists($dest)) {
//					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
//					dol_mkdir($dirodt);
//					$result = dol_copy($src, $dest, 0, 0);
//					if ($result < 0) {
//						$langs->load("errors");
//						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
//						return 0;
//					}
//				}
//
//				$sql = array_merge($sql, array(
//					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
//					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."','".strtolower($myTmpObjectKey)."',".$conf->entity.")",
//					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
//					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".strtolower($myTmpObjectKey)."', ".$conf->entity.")"
//				));
//			}
//		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = [];
		return $this->_remove($sql, $options);
	}

	protected function _newRights($TVerb, $group, $objname)
	{
		foreach ($TVerb as $verb) {
			$this->_newRight($verb, $group, $objname);
		}
	}
	protected function _newRight($verb, $group, $objname)
	{
		if (!isset($this->rights)) $this->rights = [];
		static $r = -1;
		$r++;
		$this->rights[] = [
			0 => sprintf("%s%02d", $this->numero, $r + 1),
			1 => sprintf('%s %s', $verb, $objname),
			4 => $group,
			// 3 and 4 are legacy keys, not used anymore
			5 => $verb,
		];
	}

	/**
	 * @param array $menuData
	 * - `path`: for instance 'tools>mymodule>list' ('tools' = top menu, 'mymodule' = left, 'list' = submenu)
	 * - `url`': url of the link
	 * - `perms`: permission string (optional)
	 * - `picto`: for instance 'myimage@mymodule' (optional)
	 */
	protected function _newMenu($menuData)
	{
		static $r = -1;
		$r++;
		$position = 1000 + $r;
		$namePath = array_pad(explode('>', $menuData['path']), 3, '');
		LIST($top, $left, $sub) = $namePath;
		$fk_menu = $left ? 'fk_mainmenu=' . $top : '';
		$fk_menu .= $sub ? ',fk_leftmenu=' . $left : '';
		$type = 'top';
		if ($left) $type = 'left';
		if ($sub) $type = '';
		$titre = 'menu_' . ($sub ?: ($left ?: $top));
		$prefix = '';
		if (isset($menuData['picto'])) {
			$prefix = img_picto(
				'',
				$menuData['picto'],
				'class="paddingright pictofixedwidth valignmiddle"'
			);
		}
		$url = $menuData['url'];
		$langs = $this->langfiles[0];
		$perms = '1';
		if (isset($menuData['perms'])) {
			$evalright = 'user->rights->' . $this->name_lowercase . '->';
			$perms = preg_replace('/^(?!\$' . $evalright . ')/', '$' . $evalright, $menuData['perms']);
		}

		$this->menu[$r] = [
			'fk_menu'  => $fk_menu,
			'type'     => $type,
			'titre'    => $titre,
			'prefix'   => $prefix,
			'mainmenu' => $top,
			'leftmenu' => $left,
			'url'      => $url,
			'langs'    => $langs,
			'position' => $position,
			'enabled'  => '$conf->' . $this->name_lowercase . '->enabled',
			'perms'    => $perms,
			'target'   => '',
			'user'     => 0,
		];
	}
}
