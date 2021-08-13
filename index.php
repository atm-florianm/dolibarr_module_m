<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       m/mindex.php
 *	\ingroup    m
 *	\brief      Home page of m top menu
 */

// Load Dolibarr environment
$res = 0;
$main_inc = 'main.inc.php';
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . $main_inc;
if (!$res) for ($i = 0; $i < 3 && !$res; $i++) $res = @include (str_repeat('../', $i)) . $main_inc;
if (!$res) die("Include of main fails");

/**
 * @var Translate $langs
 * @var DoliDB $db
 * @var stdClass $conf
 * @var User $user
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("m@m"));

$action = GETPOST('action', 'aZ09');


// Security check
if (!$user->rights->m->instrument->read) {
	accessforbidden();
}

$now = dol_now();


/*
 * Actions
 */



/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("MArea"));

print load_fiche_titre($langs->trans("MArea"), '', 'm.png@m');

echo '<div class="fichecenter">';
echo '<div class="fichethirdleft">';

echo '</div>'; // class=fichethirdleft


echo '<div class="fichetwothirdright">';
echo '<div class="ficheaddleft">';


echo '</div>', // class=ficheaddleft
     '</div>', // class=fichetwothirdright
     '</div>'; // class=fichecenter

// End of page
llxFooter();
$db->close();
