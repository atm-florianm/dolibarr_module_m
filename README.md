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



## Installation

### From the ZIP file and GUI interface

- If you get the module in a zip file (like when downloading it from the market place [Dolistore](https://www.dolistore.com)), go into
menu ```Home - Setup - Modules - Deploy external module``` and upload the zip file.

Note: If this screen tell you there is no custom directory, check your setup is correct:

- In your Dolibarr installation directory, edit the ```htdocs/conf/conf.php``` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading ```//```) and assign a sensible value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```

### From a GIT repository

- Clone the repository in ```$dolibarr_main_document_root_alt/m```

```sh
cd ....../custom
git clone git@github.com:gitlogin/m.git m
```

### <a name="final_steps"></a>Final steps

From your browser:

  - Log into Dolibarr as a super-administrator
  - Go to "Setup" -> "Modules"
  - You should now be able to find and enable the module

-->

## Licenses

### Main code

GPLv3 or (at your option) any later version. See file COPYING for more information.

### Documentation

All texts and readmes are licensed under GFDL.
