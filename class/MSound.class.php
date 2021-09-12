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
	var $sample_rate = self::FREQ_22050;

	/**
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * @param int $freq
	 * @param double $duration
	 */
	public function sine($freq = 220, $duration = 2.0, $volume = 0.5) {
		$attenuation_speed = 3;
		$hz1760 = 1760;
		$n_samples = (int)($duration * $this->sample_rate);
		for ($t = 0; $t < $n_samples; $t++) {
			#$volume_attenuation = 1 - $t / $n_samples;
			#(((x-6)/6)**2-2)+2 cf. http://www.fooplot.com/#W3sidHlwZSI6MCwiZXEiOiIoKCh4LTYpLzYpKio0KSIsImNvbG9yIjoiIzAwMDAwMCJ9LHsidHlwZSI6MTAwMH1d
			#$volume_attenuation = floatval($t) / $n_samples * (floatval($t) / $n_samples - 2) + 1;
			$volume_attenuation = ((floatval($t) - $n_samples) / $n_samples) ** (2*$attenuation_speed);
			$this->data[] = $volume_attenuation * $volume * sin($freq * $t / $hz1760);
		}
	}

	public function note($name, $duration = 1.0, $volume = 0.5)
	{
		# 1.0594630943592953 == 2 ** (1 / 12)
		$base_freq = 220;
		$TSemitone  = [ 'A' => 0, 'B' => 2, 'C' => 3, 'D' => 5, 'E' => 7, 'F' => 8, 'G' => 10 ];
		$TSemitone += [ 'a' => 12, 'b' => 14, 'c' => 15, 'd' => 17, 'e' => 19, 'f' => 20, 'g' => 22 ];
		$TAcc = ['#' => 1, '^' => 1, 'm' => -1, '\'' => 12, ',' => -12];
		$semitone = $TSemitone[$name[0]];
		if (strlen($name) > 1) {
			$accidental = $name[1];
			$semitone += $TAcc[$accidental];
		}
		$freq = (1.0594630943592953 ** $semitone) * $base_freq;
		$this->sine($freq, $duration, $volume);
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
		// NOTE: 8 bit PCM = unsigned (0 - 255)
		// 16 bit PCM = signed (-32768 - 32767)
		// 24 bit PCM = ???
		static $i = 0;
		$i++;
		if ($bytesPerSample === 1) return pack('C', 128 + (int)round($sample * 127));
		if ($bytesPerSample === 2) return pack('v', (int)round($sample * 32767));

		// 3 = harder

		$n_signed = (int)round($sample * 8388608);
		#$n = 8388607 + $n_signed;
		$n = $n_signed;

		// I might never know if this actually works because 16 bits are more than enough for a toy like this module.
		return pack('CCC', $n & 255, ($n >> 8) & 255, ($n >> 16) & 255);
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
		$bytesPerChunk = self::n_channels * self::bytesPerSample;
		$bytesPerSecond = self::bytesPerSample * $msound->sample_rate * self::n_channels;
		$bitsPerSample = self::bitsPerSample;

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
		fwrite($f, self::filetype);
		fwrite($f, pack('V', $msound->getByteSize(self::bytesPerSample) + 44 - 8));
		fwrite($f, self::format);


		// --------- TOP-LEVEL BLOCK: FORMAT (describes how to interpret the data block)
		fwrite($f, 'fmt ');
		fwrite($f, pack('V', self::fmtblocksize)); // taille du bloc

		fwrite($f, pack('v', $msound->storageFmt));
		fwrite($f, pack('v', self::n_channels));
		fwrite($f, pack('V', $msound->sample_rate));
		fwrite($f, pack('V', $bytesPerSecond));
		fwrite($f, pack('v', $bytesPerChunk));
		fwrite($f, pack('v', $bitsPerSample));

		// --------- TOP-LEVEL BLOCK: DATA
		fwrite($f, 'data');
		fwrite($f, pack('V', $msound->getByteSize(self::bytesPerSample)));
		foreach ($msound->data as $datum) {
			fwrite($f, $msound->serializeSample($datum, self::bytesPerSample));
		}
		fclose($f);
	}
}

class WavException extends Exception
{
}
