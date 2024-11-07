# Export PDF (feuille de temps)

Par défaut, l'export des documents des feuilles de temps sont exportées en utilisant **DOMPdf** : 

 - [x] Synthèse mensuelle des activités
 - [ ] Synthèse mesuelle d'une personne
 
Il est possible d'utlise une alternative avec **Wkhtmltopdf** plus perforant et donnant un résultat visuel plus *propre*.

## Installer Wkhtmltopdf

```bash
apt install wkhtmltopdf
```

Puis installer la police Open Sans : 

```bash
apt install fonts-open-sans
```

> Normalement, la police *open-sans* est installée dans le dossier `/usr/share/fonts/truetype/open-sans`. Dans le cas contraire, il faudra préciser l'emplacement dans la configuration Oscar.


## Test Wkhtmltopdf

Plusieurs problèmes peuvent survenir lors de l'utilisation de *Wkhtmltopdf*.

Avant d'activer *Wkhtmltopdf* dans Oscar, vous pouvez tester le bon fonctionnement sur votre serveur: 

```bash
$ wkhtmltopdf install/demo/timesheets/documents/synthese-mensuelle-apercu.html /tmp/preview.pdf
Loading page (1/2)
Printing pages (2/2)                                               
Done                                                           
```

Si vous obtenez une erreur `QXcbConnection: Could not connect to display` : 

Renseigner le mode *offscreen* en complétant la variable d'environnement `QT_QPA_PLATFORM` : 

```bash
export QT_QPA_PLATFORM=offscreen && wkhtmltopdf install/demo/timesheets/documents/synthese-mensuelle-apercu.html /tmp/preview.pdf
```

Vérifiez le fichier, si le texte ne s'affiche pas, vous allez devoir indiquer l'emplacement de la police open-sans précédement installée : 

```bash
export QT_QPA_PLATFORM=offscreen 
export QT_QPA_FONTDIR=/usr/share/fonts/truetype/open-sans
wkhtmltopdf install/demo/timesheets/documents/synthese-mensuelle-apercu.html /tmp/preview.pdf
```

La commande utilisée par *Oscar* est celle-ci : 

```bash
export QT_QPA_FONTDIR=<FONTPATH>&& export QT_QPA_PLATFORM=offscreen && wkhtmltopdf -O %s %s %s
```

éditez ensuite le fichier `config/autoload/local.php` : 

```php
<?php
// config/autoload/local.php
return array(
    // ...
    // Oscar
    'oscar' => [
        // ...
        // Rendu via WKHtmlToPdf
        'htmltopdfrenderer' => [
            'class' => \Oscar\Formatter\File\HtmlToPdfWkhtmltopdfFormatter::class,
            // Corrigez l'emplacement de la police Open-Sans si besoin
            'arguments' => ['/usr/share/fonts/truetype/open-sans']
        ]
    ]
);
```

## Dysfonctionnement

 Sous **Redhat**, les emplacements pour la commandes sont différents. Donc : 
 
 1. Installer la police **Open-Sans** `dnf install open-sans-fonts`
 2. Créer un lien symbolique `/usr/local/bin/wkhtmltopdf ` > `/opt/wkhtmltopdf/bin/wkhtmltopdf` 