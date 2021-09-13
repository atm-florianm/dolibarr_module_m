function main() {
    let ac = new AudioContext();

    let o = ac.createOscillator();

    let g = ac.createGain();
    g.gain.value = 0.25;

    o.type = 'sine';

    o.frequency.setValueAtTime(440, ac.currentTime);
    o.frequency.setValueAtTime(590, ac.currentTime + 1);
    o.frequency.setValueAtTime(790, ac.currentTime + 2);

    g.connect(ac.destination);
    o.connect(g);


    window.a = (i) => {
        g.gain.setValueAtTime(g.gain.value + i, ac.currentTime + 0.3);
    };


    a(-0.1);

    o.start();

}


window.addEventListener('load', main);
