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

require_once 'lib/m.lib.php';
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

$TMelodyPresets = [
	'eemeemebdca3',
	'A3BC4a3bG4b2af2afeG2BA4',
	'G2Ga3G3c3b6G2Ga3G3d3c6',
];
$melody = GETPOST('melody', 'alphanohtml');

if (!$melody) $melody = $TMelodyPresets[rand(0, count($TMelodyPresets) - 1)];
$tempo = intval(GETPOST('tempo', 'int'));
if (!$tempo) $tempo = 120;

switch($action) {
case 'generate':

	// tempo is in bpm, if 1 is a semiquaver, then the duration of 1 in seconds is 0.5 == (60 / 60) * 0.5

	if ($melody) {
		$s = new MSound();
		$TNote = [];
		if (preg_match_all('/([A-Ga-g][#m]?)([\d.]*)/', $melody, $TMelody, PREG_SET_ORDER)) {
			foreach ($TMelody as $note) {
				LIST($full_note, $note_name, $note_duration) = $note;
				if ($note_duration === '') $note_duration = 1;
				$note_duration = 60 / ($tempo ?: 60) * 0.5 * floatval($note_duration);
				$TNote[] = ['name' => $note_name, 'duration' => $note_duration];
				$s->note($note_name, $note_duration, 0.8);
			}
		}
		umask(0);
		$filename = 'test.wav';
		$filepath = $conf->m->multidir_output[$conf->entity] . '/' . $filename;
		WavFile::write($filepath, $s);
	}

	break;

}


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

$arrayofcss = ['/m/css/m.css'];
$arrayofjs = ['/m/js/synthe.js'];
llxHeader("", $langs->trans("MTuneEditor"), '', '', '', '', $arrayofjs, $arrayofcss, '');

print load_fiche_titre($langs->trans("MTuneEditor"), '', '');

echo '<div class="fichecenter">';
$url = DOL_URL_ROOT . '/document.php?' . http_build_query([
	'modulepart' => 'm',
	'entity' => $conf->entity,

	'file' => '/test.wav', // TODO: objet en BDD avec une REF donc un nom de fichier dépendant de la ref

	// pour empêcher le navigateur d’utiliser le cache, on change l’URL
	'cache-prevention' => sprintf('%03d', rand(0, 999)),
]);

?>
<audio src="<?php  echo $url;  ?>"
  type="audio/mpeg"
  controls>
	<?php echo $langs->trans('AudioHTML5NotAvailable'); ?>
</audio>

<form id="tune-generation">
	<textarea name="melody" placeholder="mélodie"><?php echo $melody; ?></textarea>
	<br/>
	<input name="tempo" type="number" value="<?php echo $tempo; ?>" placeholder="tempo" />
	<button name="action" value="generate"><?php echo $langs->trans('Generate'); ?></button>
</form>

<div id="instrument-editor">

</div>

<?php

print '<pre class="debug_melody">' . json_encode($TNote ?? '', JSON_PRETTY_PRINT) . '</pre>';

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
