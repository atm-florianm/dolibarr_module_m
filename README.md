# M SOUND GENERATOR FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

Module sympa (j'espère) créé pour m'amuser.

C’est un synthé ultra-basique (ne joue que des sin pures pour l'instant)
avec un formulaire pour générer une mélodie simple qui sera enregistrée
au format WAV PCM 16 bits dans les documents du module
(`documents/m/*.wav`) et qui pourra être jouée depuis le navigateur (mais
attention au cache car le fichier s'appelle toujours "test.wav").

Ça me permet aussi d'expérimenter certains trucs que je ne fais pas
d'habitude avec Dolibarr et PHP.


## Features

- générateur d'onde sinusoïdale basique avec atténuation
- encodeur PCM 16 bit (entiers signés) + encapsulateur WAV
- interpréteur de mélodie dans un format très basique :
  * lettres `ABCDEFG` pour l'octave 1
  * `abcdefg` pour l'octave 2
  * altérations avec `#` (dièse) et `m` (bémol)
  * durées exprimées par rapport au tempo (tempo exprimé à la blanche, c'est à dire que `1` = une croche, `2` une noire, `4` une blanche etc.)

## FIXME

- chaque génération écrase la précédente (un seul fichier cible: `documents/m/test.wav`)
- quand on régénère, le fichier se régénère bien mais le player du navigateur reste fixé sur le son mis en cache même avec ctrl+F5


## Licenses

### Main code

GPLv3 or (at your option) any later version. See file COPYING for more information.

### Documentation

All texts and readmes are licensed under GFDL.
