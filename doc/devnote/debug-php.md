# DEBUG PHP

Pour débugger pas à pas le code PHP dans l'IDE, il faut installer Xdebug.

Xdebug fournit une page (https://xdebug.org/wizard) qui détaille les étapes à suivre à partir des informations concernant l'installation locale de PHP. Il suffit de lancer en local en ligne de commande `php -r "phpinfo();"` puis copier toute la sortie de cette commande sur le site Wizard d'Xdebug, qui va alors afficher la liste des étapes à suivre.

Par exemple pour mon poste (Ubuntu 22.04), voici ces étapes :

 - Download [xdebug-3.3.2.tgz](https://xdebug.org/files/xdebug-3.3.2.tgz)
 - Install the pre-requisites for compiling PHP extensions. On your Ubuntu system, install them with: `apt-get install php-dev autoconf automake`
 - Unpack the downloaded file with `tar -xvzf xdebug-3.3.2.tgz`
 - Run: `cd xdebug-3.3.2`
 - Run: `./configure`
 - Run: `make`
 - Run: `cp modules/xdebug.so /usr/lib/php/20220829/`
 - Create `/etc/php/8.2/cli/conf.d/99-xdebug.ini` and add the line: `zend_extension = xdebug`
 - Please also update `php.ini` files in adjacent directories, as your system seems to be configured with a separate php.ini file for the web server and command line.

On peut alors vérifier que Xdebug est bien installé : `php -r "xdebug_info();`

Maintenant que Xdebug est installé, on peut suivre la documentation pour activer les fonctionnalités suivantes :
 - [Development Helpers](https://xdebug.org/docs/develop) — help you get better error messages and obtain better information from PHP's built-in functions.
 - [Step Debugging](https://xdebug.org/docs/step_debug) — allows you to interactively walk through your code to debug control flow and examine data structures.
 - [Profiling](https://xdebug.org/docs/profiler) — allows you to find bottlenecks in your script and visualize those with an external tool.


## Activation du débogage pas à pas

Ajouter dans les fichiers `/etc/php/8.2/apache2/conf.d/99-xdebug.ini` et `/etc/php/8.2/cli/conf.d/99-xdebug.ini` les lignes suivantes :

```ini
[xdebug]
xdebug.mode = debug
xdebug.start_with_request = yes
```

Ensuite, redémarrer le serveur apache : `systemctl restart apache`

### Dans l'IDE

 - Mettre un point d'arrêt par exemple dans index.php
 - Pour PHP Storm : cliquer sur l'icône d'insecte "Start Listening for PHP Debug Connections"
 - Pour VSCode/VSCodium/Theia :
   - installer l'extension [PHP debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug)
   - cliquer sur l'icône d'insecte puis "Listen for Xdebug"
 - naviguer sur http://localhost/

Si tout a bien été configuré, le point d'arrêt devrait se déclencher dans l'IDE, permettant d'avancer pas à pas, d'inspecter et de modifier la valeur des variables etc.
