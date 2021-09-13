/* jshint strict:false */
/* jshint esversion: 6 */
/**
 * Éditeur d’instruments (synthétiseur) du module M, à coupler avec l’éditeur de mélodies.
 */

/** {object} console */

let MSynth = {};

MSynth.Instrument = function () {
	this.harmonics = [];
};

MSynth.Harmonic = function (amplitude, frequency, minAmp, oscillAmp, oscillFreq) {
	this.amplitude = amplitude;
	this.frequency = frequency;
	this.minAmp = minAmp;
	this.oscillAmp = oscillAmp;
	this.oscillFreq = oscillFreq;
};
MSynth.Harmonic.prototype.show = function () {
	console.log(this.amplitude);
	console.log(this.frequency);
};

MSynth.HarmonicEditor = function () {
	if (MSynth.HarmonicEditor.n === undefined) {
		MSynth.HarmonicEditor.n = 0;
	} else {
		MSynth.HarmonicEditor.n++;
	}
	this.name = `harmoniceditor[${MSynth.HarmonicEditor.n}]`;
	this.component = $(
		'<div class="harmonic-editor">' +
		' <input' +
		'     name="' + this.name + '[amplitude]"' +
		'     type="range"' +
		'     min="1"' +
		'     max="100"' +
		'     step="1"' +
		' >' +
		' <input' +
		'     name="' + this.name + '[frequency]"' +
		'     type="range"' +
		'     min="1"' +
		'     max="100"' +
		'     step="1"' +
		' >'
	);
};

(() => {
	// let basicClone = function (obj) {
	// 	let clone = {};
	// 	for (let prop in obj) {
	// 		if (obj.hasOwnProperty(prop)) {
	// 			clone[prop] = obj[prop];
	// 		}
	// 	}
	// 	return clone;
	// };
	// let getNewMethod = function (BaseObject) {
	// 	return function () {
	// 		let instance = basicClone(BaseObject);
	// 		if (typeof(instance.constructor) === 'function') {
	// 			instance.constructor.apply(instance, arguments);
	// 		}
	// 		return instance;
	// 	};
	// };
	// for (let prop in MSynth) {
	// 	if (prop in MSynth) {
	// 		let BaseObject = MSynth[prop];
	// 		BaseObject.new = getNewMethod(BaseObject);
	// 	}
	// }
	window.addEventListener('load', () => {
		let instrumentEditor = $('#instrument-editor');
		for (let i = 0; i < 3; i++) {
			instrumentEditor.append((new MSynth.HarmonicEditor()).component);
		}
	});
	// window.h = new MSynth.Harmonic(1, 2, 3, 4, 5, 6, 7);
	// h.show();
})();
