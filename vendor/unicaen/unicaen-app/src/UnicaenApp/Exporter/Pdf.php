<?php
namespace UnicaenApp\Exporter;

use mPDF;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Renderer\PhpRenderer;

/**
 * Classe utilitaire permettant de fabriquer un document PDF à partir 
 * de code HTML.
 * 
 * NB: bibliothèque mPDF requise (http://www.mpdf1.com/mpdf/).
 *
 * @author bertrand.gauthier@unicaen.fr
 */
class Pdf implements ExporterInterface
{
    /**
     * Send the file inline to the browser.
     * The name given by filename is used when one selects the "Save as" option on the link generating the PDF. 
     */
    const DESTINATION_BROWSER           = "I";
    /**
     * Send to the browser and force a file download with the name given by filename.
     */
    const DESTINATION_BROWSER_FORCE_DL  = "D";
    /**
     * Save to a local file with the name given by filename (may include a path).
     */
    const DESTINATION_FILE              = "F";
    /**
     * Return the document as a string. filename is ignored.
     */
    const DESTINATION_STRING            = "S";
    
    /**
     * @var string 
     */
    protected $exportDirectoryPath;
    /**
     * @var PhpRenderer
     */
    private $renderer;
    /**
     * @var mPDF
     */
    private $mpdf;
    /**
     * @var array
     */
    private $headerScripts;
    /**
     * @var array
     */
    private $bodyScripts = array();
    /**
     * @var array
     */
    private $footerScripts;
    /**
     * @var array
     */
    private $scriptVars = array();
    /**
     * @var boolean
     */
    private $generated;
    /**
     * @var string
     */
    private $format;
    /**
     * @var bool
     */
    private $orientationPaysage;
    /**
     * @var int
     */
    private $marginLeft = 10;
    /**
     * @var int
     */
    private  $marginRight = 10;
    /**
     * @var int
     */
    private  $marginTop = 25;
    /**
     * @var int
     */
    private  $marginBottom = 15;
    /**
     * @var int
     */
    private  $marginHeader = 5;
    /**
     * @var int
     */
    private  $marginFooter = 10;
    /**
     * @var integer
     */
    private $defaultFontSize = 10;
    /**
     * @var string
     */
    private $body;
    /**
     * @var string
     */
    private $css;
    
    /**
     * @var string
     */
    private $headerTitle;
    /**
     * @var string
     */
    private $headerSubtitle;
    /**
     * @var string
     */
    private $footerTitle;

    /**
     * @var string
     */
    private $logo;

    /**
     * Constructeur.
     *
     * @param PhpRenderer $renderer Moteur de rendu des scripts de vue
     * @param string $format Ex: 'A4' (par défaut), 'A3', 'B0', 'Letter'
     * @param boolean $orientationPaysage false: portrait, true: paysage
     * @param integer $defaultFontSize false: portrait, true: paysage
     */
    public function __construct(
            PhpRenderer $renderer = null, 
            $format = 'A4', 
            $orientationPaysage = false, 
            $defaultFontSize = 10)
    {
        if (null !== $renderer) {
            $this->setRenderer($renderer);
        }
        
        $this->format = $format;
        $this->orientationPaysage = $orientationPaysage;
        $this->defaultFontSize = $defaultFontSize;

        $this->setLogo(file_get_contents(__DIR__ . "/../../../public/img/logo-unicaen.png"));
    }
    
    /**
     * Spécifie le script de vue de l'entête du document PDF.
     * Possibilité de différencier pages paires et impaires.
     * 
     * @param string $script Chemin relatif au répertoire "views" du module en cours, ex: '/module/controleur/pdf/header.phtml'
     * @param bool $oddOrEven 'O', 'E' ou null
     * @return self
     */
    public function setHeaderScript($script = null, $oddOrEven = null)
    {
        if (!$script) {
            $this->headerScripts = array();
            return $this;
        }
        if (in_array($oddOrEven, array('O', 'E'))) {
            $this->headerScripts[$oddOrEven] = $script;
        }
        else {
            // same script for both even and odd pages
            $this->headerScripts['O'] = $script;
            $this->headerScripts['E'] = $script;
        }
        return $this;
    }

    /**
     * Ajoute un script de vue à inclure dans le rendu du corps du document PDF.
     *
     * @param string $script Chemin relatif au répertoire "views" du module en cours, ex: '/module/controleur/pdf/header.phtml'
     * @param boolean $newPage Faut-il commencer une nouvelle page avant ?
     * @param array $scriptVars Variables passées au moteur de rendu des scripts de vue
     * (tableau dont les clés sont les noms des variables)
     * @param null|int $resetPageNum A combien doit-on recommencer la numérotation des pages ?
     * @return self
     */
    public function addBodyScript($script, $newPage = true, array $scriptVars = array(), $resetPageNum = null)
    {
        $key = uniqid();
        
        $this->bodyScripts[$key] = array('_script' => $script, '_newPage' => $newPage, '_resetPageNum' => $resetPageNum);

        if ($scriptVars) {
            $this->scriptVars[$key] = $scriptVars;
        }
        
        return $this;
    }

    /**
     * Ajoute du code HTML à inclure dans le rendu du corps du document PDF.
     *
     * @param string $html Code HTML
     * @param boolean $newPage Faut-il commencer une nouvelle page avant ?
     * @param null|int $resetPageNum A combien doit-on recommencer la numérotation des pages ?
     * @return self
     */
    public function addBodyHtml($html, $newPage = true, $resetPageNum = null)
    {
        $key = uniqid();
        
        $this->bodyScripts[$key] = array('_html' => $html, '_newPage' => $newPage, '_resetPageNum' => $resetPageNum);

        return $this;
    }
    
    /**
     * Spécifie le script de vue du pied de page du document PDF.
     * Possibilité de différencier pages pairs et impairs.
     * 
     * @param string $script Chemin relatif au répertoire "views" du module en cours, ex: '/module/controleur/pdf/header.phtml'
     * @param bool $oddOrEven 'O' (pages impaires seulement), 'E' (pages paires seulement) ou null (pages paires et impaires)
     * @return self
     */
    public function setFooterScript($script = null, $oddOrEven = null)
    {
        if (!$script) {
            $this->footerScripts = array();
            return $this;
        }
        if (in_array($oddOrEven, array('O', 'E'))) {
            $this->footerScripts[$oddOrEven] = $script;
        }
        else {
            $this->footerScripts['O'] = $script;
            $this->footerScripts['E'] = $script;
        }
        return $this;
    }
    
    /**
     * Génère le document PDF et l'envoie éventuellement au navigateur.
     *
     * @param string $filename Nom du document PDF (avec extension)
     * @param string $destination Destination du document généré
     * Pdf::DESTINATION_BROWSER :
     *  Send the file inline to the browser. 
     *  The name given by filename is used when one selects the "Save as" option on the link generating the PDF.  
     * Pdf::DESTINATION_BROWSER_FORCE_DL :
     *  Send to the browser and force a file download with the name given by filename.
     * Pdf::DESTINATION_FILE :
     *  Save to a local file with the name given by filename (may include a path).
     * Pdf::DESTINATION_STRING :
     *  Return the document as a string. filename is ignored.
     * 
     * @param string $filename Nom du fichier PDF produit
     * @param string $destination Exemple: Pdf::DESTINATION_BROWSER
     * @param string $memoryLimit Quantité de mémoire maximum utilisée (memory_limit PHP), ex: '256M'
     * @return string
     */
    public function export($filename = null, $destination = self::DESTINATION_BROWSER, $memoryLimit = null)
    {
        if (!$filename) {
            throw new LogicException("Aucun nom de fichier spécifié.");
        }
        if (!class_exists('\mPDF', true)) {
            throw new RuntimeException("La bibliothèque mPDF ne semble pas être présente.");
        }
        if (! extension_loaded('gd')) {
            throw new RuntimeException("L'extension php5-gd requise ne semble pas être installée ou chargée.");
        }
        
        if ($memoryLimit) {
            $limit = ini_get('memory_limit');
            ini_set('memory_limit', $memoryLimit);
        }
        
        //no errors
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', 'off'); // indispensable sinon corruption possible du PDF!
            
        $this->_generate();
        
        // Specify the initial Display Mode when the PDF file is opened in Adobe Reader
        // fullpage: Fit a whole page in the screen
        // fullwidth: Fit the width of the page in the screen
        // real: Display at real size
        // default: User's default setting in Adobe Reader
        // INTEGER: Display at a percentage zoom (e.g. 90 will display at 90% zoom)
        $this->getMpdf()->SetDisplayMode('default');

        $exit = true;
        if (self::DESTINATION_FILE == $destination) {
            $filename = sys_get_temp_dir() . '/' . $filename;
            $exit = false;
        }
        elseif (self::DESTINATION_STRING == $destination) {
            $exit = false;
        }
        
        // Output pdf
        $out = $this->getMpdf()->Output($filename, $destination);
        
        ini_set('display_errors', $displayErrors);
        
        if ($memoryLimit) {
            ini_set('memory_limit', $limit);
        }
        
        if (!$exit) {
            return $out;
        }
    }
    
    /**
     * Retourne le code HTML constituant le corps du document PDF,
     * styles CSS inclus, mais sans l'en-tête ni pied de page.
     * 
     * @param boolean $includeCss Faut-il inclure les styles CSS éventuels ?
     * @return string Code HTML
     */
    public function getHtmlBody($includeCss = true)
    {
        $this->_generate();
        
        $html = $this->body . PHP_EOL;
        if ($includeCss) {
           $html .=  $this->css . PHP_EOL;
        }
        
        return $html;
    }
    
    /**
     * Génère et ajoute les différentes sections au document PDF (entête, corps, pied, css).
     *
     * @return self 
     */
    protected function _generate()
    {
        if (!$this->generated) {

            $this->_addCss()
                 ->_addHeader()
                 ->_addFooter()
                 ->_addBody();

            $this->generated = true;
        }
        
        return $this;
    }

    /**
     * Génère et ajoute au document PDF les styles CSS.
     * 
     * @return self
     */
    protected function _addCss()
    {
        $parts = array();
        
        // styles de base fournis par la librairie Unicaen
        if (file_exists(($filepath = $this->getDefaultScriptsPath() . '/pdf.css'))) {
            $css = file_get_contents($filepath);
            $this->getMpdf()->WriteHTML($css, 1);
            $parts[] = $css;
        }
//        // styles spécifiques éventuels fournis par chaque application
//        if (file_exists(($filepath = APPLICATION_PATH . '/../public/styles/pdf.css'))) {
//            $css = file_get_contents($filepath);
//            $this->getMpdf()->WriteHTML($css, 1);
//            $parts[] = $css;
//        }
//        else if (file_exists(($filepath = APPLICATION_PATH . '/../public/css/pdf.css'))) {
//            $css = file_get_contents($filepath);
//            $this->getMpdf()->WriteHTML($css, 1);
//            $parts[] = $css;
//        }
        
        $this->css = '<style>' . PHP_EOL . implode(PHP_EOL, $parts) . PHP_EOL . '</style>';
        
        return $this;
    }
    
    /**
     * Génère et ajoute au document PDF l'entête.
     * 
     * @return self
     */
    protected function _addHeader()
    {
        $headerOdd = $headerEven = null;

        $scriptVars = array(
            'headerTitle'    => $this->headerTitle,
            'headerSubtitle' => $this->headerSubtitle,
            'logo'           => $this->logo);

        // le logo doit être passé ainsi pour pouvoir être référencé dans la balise <img> sous la forme "var:logo"
        $this->getMpdf()->logo = $this->logo;
        
        if (isset($this->headerScripts['O'])) {
            $headerOdd = $this->getRenderer()->render($this->headerScripts['O'], $scriptVars);
        }
        elseif (file_exists($filepath = $this->getDefaultScriptsPath() . '/header-odd.phtml')) {
            ob_start();
            include $filepath;
            $headerOdd = ob_get_clean();
        }
        
        if (isset($this->headerScripts['E'])) {
            $headerEven = $this->getRenderer()->render($this->headerScripts['E'], $scriptVars);
        }
        elseif (file_exists($filepath = $this->getDefaultScriptsPath() . '/header-even.phtml')) {
            ob_start();
            include $filepath;
            $headerEven = ob_get_clean();
        }
        
        if ($headerOdd) {
            $this->getMpdf()->SetHTMLHeader($headerOdd, 'O');
        }
        if ($headerEven) {
            $this->getMpdf()->SetHTMLHeader($headerEven, 'E');
        }
        
        return $this;
    }

    /**
     * Génère et ajoute au document PDF le corps.
     * 
     * @return self
     */
    protected function _addBody()
    {
        if (!$this->bodyScripts) {
            throw new LogicException("Aucun script spécifié.");
        }

        $bodyParts = array();

        // Render body of document
        foreach ($this->bodyScripts as $key => $report) {

            if (array_key_exists('_html', $report)) {
                // contenu HTML simple
                $part = $report['_html'];
            }
            elseif (array_key_exists('_script', $report)) {
                // script de vue à rendre
                $vars = isset($this->scriptVars[$key]) ? $this->scriptVars[$key] : array();
                $part = $this->getRenderer()->render($report['_script'], $vars);
            }
            else {
                throw new LogicException("Format de report inattendu!");
            }
            
            $resetPageNum = '';
            if (array_key_exists('_resetPageNum', $report) && is_numeric($report['_resetPageNum'])) {
                $resetPageNum = $report['_resetPageNum'];
            }
            
            // write body
            if ($report['_newPage']) {
                $this->getMpdf()->AddPage('', '', $resetPageNum);
            }
            $this->getMpdf()->WriteHTML($part);

            $bodyParts[] = $part;
        }

        $this->body = implode(PHP_EOL, $bodyParts);

        return $this;
    }

    /**
     * Génère et ajoute au document PDF le pied de page.
     * 
     * @return self
     */
    protected function _addFooter()
    {
        $footerOdd = $footerEven = null;
        
        $scriptVars = array(
            'footerTitle' => $this->footerTitle);
        
        if (isset($this->footerScripts['O'])) {
            $footerOdd = $this->getRenderer()->render($this->footerScripts['O'], $scriptVars);
        }
        elseif (file_exists($filepath = $this->getDefaultScriptsPath() . '/footer-odd.phtml')) {
            ob_start();
            include $filepath;
            $footerOdd = ob_get_clean();
        }
        
        if (isset($this->footerScripts['E'])) {
            $footerEven = $this->getRenderer()->render($this->footerScripts['E'], $scriptVars);
        }
        elseif (file_exists($filepath = $this->getDefaultScriptsPath() . '/footer-even.phtml')) {
            ob_start();
            include $filepath;
            $footerEven = ob_get_clean();
        }
        
        if ($footerOdd) {
            $this->getMpdf()->SetHTMLFooter($footerOdd, 'O');
        }
        if ($footerEven) {
            $this->getMpdf()->SetHTMLFooter($footerEven, 'E');
        }

        return $this;
    }
    
    /**
     * Spécifie le moteur de rendu des scripts de vue PHP à utiliser.
     * 
     * @param PhpRenderer $renderer
     * @return self
     */
    public function setRenderer(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }
    
    /**
     * Retourne le moteur de rendu des scripts de vue PHP utilisé.
     *
     * @return PhpRenderer
     */
    public function getRenderer()
    {
        if (null === $this->renderer) {
            $this->renderer = new PhpRenderer();
        }
        return $this->renderer;
    }

    /**
     * Spécifie l'objet de fabrication du document PDF.
     * 
     * @param mPDF $mPdf
     * @return self
     */
    public function setMpdf(mPDF $mPdf = null)
    {
        $this->mpdf = $mPdf;
        return $this;
    }
    
    /**
     * Retourne l'objet de fabrication du document PDF.
     *
     * @return mPDF
     */
    public function getMpdf()
    {
        if (null === $this->mpdf) {
            
//            define("_MPDF_TEMP_PATH", sys_get_temp_dir() . '/mpdf');

            // create object mpdf
            $this->mpdf = new mPDF(
                    $mode = 's',
                    $this->format . ($this->orientationPaysage ? '-L' : null),
                    $this->defaultFontSize,
                    '' /* $default_font */,
                    $this->marginLeft,
                    $this->marginRight,
                    $this->marginTop,
                    $this->marginBottom,
                    $this->marginHeader,
                    $this->marginFooter);
            
            $this->mpdf->useSubstitutions = false;
//            $this->mpdf->simpleTables = true; // ne respecte pas les styles de border
            $this->mpdf->mirrorMargins = true; // different header and footer on odd/even pages numbers
        }
        
        return $this->mpdf;
    }

    /**
     * Retourne le chemin absolu du répertoire contenant les scripts de vue par défaut.
     * 
     * @return string
     */
    public function getDefaultScriptsPath()
    {
        return __DIR__ . '/scripts';
    }
    
    /**
     * Spécifie le chemin absolu dans lequel enregistrer le document PDF généré
     * avec le paramètre DESTINATION_FILE.
     * 
     * @param string $path
     * @return self
     */
    public function setExportDirectoryPath($path)
    {
        $this->exportDirectoryPath = $path;
        return $this;
    }
    
    /**
     * Retourne le chemin absolu dans lequel enregistrer le document PDF généré
     * avec le paramètre DESTINATION_FILE.
     * 
     * @return string
     */
    public function getExportDirectoryPath()
    {
        if (null === $this->exportDirectoryPath) {
            $this->exportDirectoryPath = sys_get_temp_dir();
        }
        return $this->exportDirectoryPath;
    }

    /**
     * Spécifie les opérations autorisées sur le document PDF généré et le mot de passe éventuel 
     * permettant d'ouvrir le documen.
     *
     * @param array $permissions Liste des seules opérations autorisées sur le document, sous-ensemble de
     * array('copy','print','modify','annot-forms','fill-forms','extract','assemble','print-highres') ; 
     * NB: active par la même occasion le chiffrage du document.
     * @param string $userPassword Mot de passe éventuel permettant d'ouvrir le document
     * @return self 
     */
    public function setPermissions(array $permissions, $userPassword = '')
    {
        $this->getMpdf()->SetProtection($permissions, $userPassword);
        return $this;
    }

    /**
     * Spécifie le texte qui figurera en filigrane sur le document PDF généré.
     *
     * @param string $text Texte
     * @return self 
     */
    public function setWatermark($text)
    {
        $this->getMpdf()->SetWatermarkText($text);
        $this->getMpdf()->watermarkTextAlpha = 0.2;
        $this->getMpdf()->showWatermarkText = true;
        return $this;
    }
    
    /**
     * Spécifie le format des pages.
     *
     * @param string $format Ex: 'A4' (par défaut), 'A3', 'B0', 'Letter'
     * @return self 
     */
    public function setFormat($format = 'A4')
    {
        $this->format = $format;
        return $this;
    }
    
    /**
     * Active ou non l'orientation "paysage" des pages.
     *
     * @param bool $orientationPaysage true: paysage, false: portrait
     * @return self 
     */
    public function setOrientationPaysage($orientationPaysage = true)
    {
        $this->orientationPaysage = $orientationPaysage;
        return $this;
    }
    
    /**
     * Spécifie l'espace entre le bord de gauche et le contenu.
     *
     * @param int $marginLeft Marge en millimètres
     * @return self 
     */
    public function setMarginLeft($marginLeft = 10)
    {
        $this->marginLeft = $marginLeft;
        return $this;
    }
    
    /**
     * Spécifie l'espace entre le bord de droite et le contenu.
     *
     * @param int $marginRight Marge en millimètres
     * @return self 
     */
    public function setMarginRight($marginRight = 10)
    {
        $this->marginRight = $marginRight;
        return $this;
    }

    /**
     * Spécifie l'espace entre le bord du haut et le contenu.
     *
     * @param int $marginTop Marge en millimètres
     * @return self 
     */
    public function setMarginTop($marginTop = 25)
    {
        $this->marginTop = $marginTop;
        return $this;
    }

    /**
     * Spécifie l'espace entre le bord du bas et le contenu.
     *
     * @param int $marginBottom Marge en millimètres
     * @return self 
     */
    public function setMarginBottom($marginBottom = 15)
    {
        $this->marginBottom = $marginBottom;
        return $this;
    }

    /**
     * Spécifie la l'espace entre le bord du haut et l'en-tête.
     *
     * @param int $marginHeader Marge en millimètres
     * @return self 
     */
    public function setMarginHeader($marginHeader = 5)
    {
        $this->marginHeader = $marginHeader;
        return $this;
    }

    /**
     * Spécifie la l'espace entre le bord du bas et le pied de page.
     *
     * @param int $marginFooter Marge en millimètres
     * @return self 
     */
    public function setMarginFooter($marginFooter = 10)
    {
        $this->marginFooter = $marginFooter;
        return $this;
    }

    /**
     * Spécifie le titre figurant au centre de l'entête.
     *
     * @param string $headerTitle
     * @return self 
     */
    public function setHeaderTitle($headerTitle = null)
    {
        $this->headerTitle = $headerTitle;
        return $this;
    }
    
    /**
     * Spécifie le sous-titre figurant au centre de l'entête.
     *
     * @param string $headerSubtitle
     * @return self 
     */
    public function setHeaderSubTitle($headerSubtitle = null)
    {
        $this->headerSubtitle = $headerSubtitle;
        return $this;
    }
    
    /**
     * Spécifie le titre figurant au centre du pied de page.
     *
     * @param string $footerTitle
     * @return self 
     */
    public function setFooterTitle($footerTitle = null)
    {
        $this->footerTitle = $footerTitle;
        return $this;
    }

    /**
     * Spécifie le (contenu du) logo figurant dans l'entête.
     *
     * @param string $logo Résultat d'un file_get_contents()
     * @return self
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }
}