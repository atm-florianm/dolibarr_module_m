<?php
/*
 * Copyright (C) 2021  ATM Consulting <support@atm-consulting.fr>
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
 *
 */
class MSound
{
	const STORAGE_PCM = 1;
	const STORAGE_PCM_FLOAT = 3;
	const STORAGE_PCM_WAVE_FORMAT_EXTENSIBLE = 65534;

	const INTERNAL_MAX_BYTES_PER_SAMPLE = 3;

	const FREQ_11025 = 11025;
	const FREQ_22050 = 22050;
	const FREQ_44100 = 44100;
	const FREQ_48000 = 48000;
	const FREQ_96000 = 96000;

	var $length = 0;

	/**
	 * @var double[] $data  un sample est un flottant entre -1 et 1.
	 */
	var $data = [];

	var $storageFmt = self::STORAGE_PCM;
	var $freq = self::FREQ_22050;

	/**
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * @param int $freq
	 * @param int $duration
	 */
	public function sine($freq = 440, $duration = 4) {
		$t = 0;
		while ($t < 100) {
			$data[] = sin($t); // TODO = n’importe quoi, juste un placeholder
		}
	}

	/**
	 * Returns the length (in bytes) of the data once coerced to the desired format
	 * @param $bytesPerSample
	 * @return float|int
	 */
	public function getByteSize($bytesPerSample)
	{
		return count($this->data) * $bytesPerSample;
	}

	/**
	 * Convertit un échantillon au format mathématique (amplitude de -1.0 à 1.0) en un échantillon au format
	 * encodé Wave (entier sur 8, 16 ou 24 bits little endian).
	 *
	 * Du coup, en 8 bits, -1 → 0
	 *                      1 → 255
	 *                      0 → 127.
	 *
	 * @param double $sample
	 * @param int $bytesPerSample
	 * @return string
	 */
	public function serializeSample($sample, $bytesPerSample)
	{
		$sample = $sample & 0xffffff;
		$shift = 8 * (3 - $bytesPerSample);
		return $sample >> $shift;
	}
}

/*
 * Ne gérera qu’un seul format pour simplifier.
 */
class WavFile
{
	/* constantes immuables du format Wave */
	const format = 'WAVE';
	const filetype = 'RIFF';
	const fmtblocksize = 16;


	/* constantes ici, mais pourraient devenir variables avec l’évol du module, surtout si on fait de la stéréo */
	const n_channels = 1;
	const bitsPerSample = 16;  // 8, 16, 24
	const bytesPerSample = self::bitsPerSample >> 3;

	/**
	 * @param string $fpath
	 * @param MSound $msound
	 * @throws WavException
	 */
	public static function write($fpath, $msound)
	{

		/*
		 * Aide-mémoire pour `pack()`:
		 *  'V' = entier non signé sur 32 bits (4 octets) little endian
		 *  'v' = entier non signé sur 16 bits (2 octets) little endian
		 */
		$f = fopen($fpath, 'wb');
		if (!$f) {
			throw new WavException("File $fpath: write access denied");
		}

		// --------- TOP-LEVEL BLOCK: WAVE (header of header)
		fwrite($f, self::format);
		fwrite($f, pack('V', $msound->getByteSize(self::bytesPerSample) + 44 - 8));
		fwrite($f, self::filetype);


		// --------- TOP-LEVEL BLOCK: FORMAT (describes how to interpret the data block)
		fwrite($f, 'fmt ');
		fwrite($f, pack('V', self::fmtblocksize)); // taille du bloc

		fwrite($f, pack('v', $msound->storageFmt));
		fwrite($f, pack('v', self::n_channels));
		fwrite($f, pack('V', $msound->freq));

		$bytesPerChunk = self::n_channels * self::bytesPerSample;
		$bytesPerSecond = $msound->freq * $bytesPerChunk;
		$bitsPerSample = self::bitsPerSample;

		fwrite($f, pack('V', $bytesPerSecond));
		fwrite($f, pack('v', $bytesPerChunk));
		fwrite($f, pack('V', $bitsPerSample));

		// --------- TOP-LEVEL BLOCK: DATA
		fwrite($f, 'data');
		fwrite($f, pack('V', $msound->getByteSize(self::bytesPerSample)));
		foreach ($msound->data as $datum) {
			fwrite($f, $msound->serializeSample($datum, self::bytesPerSample));
		}
	}
}

class WavException extends Exception
{
}
