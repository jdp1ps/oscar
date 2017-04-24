<?php

namespace UnicaenApp;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Filter\StringToLower;

/**
 * Regroupe des méthodes statiques utilitaires.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Util
{
    /**
     * @var float
     */
    static protected $microtime;



    /**
     * Transforme en tableau simple une collection d'objets spécifiée sous forme d'un itérateur.
     *
     * @param \Traversable|array $collection                   Collection à transformer.
     * @param boolean            $sort                         Trier ou pas (selon les valeurs).
     * @param string             $attributeOrCallbackForValues Nom de l'attribut ou fonction anonyme à utiliser pour générer chaque valeur.
     * @param string             $attributeOrCallbackForKeys   Nom de l'attribut ou fonction anonyme à utiliser pour générer chaque clé.
     * @param string             $keysPrefix                   Préfixe à ajouter au début de chaque clé.
     *
     * @return array Résultat.
     */
    static public function collectionAsOptions(
        $collection,
        $sort = false,
        $attributeOrCallbackForValues = null,
        $attributeOrCallbackForKeys = null,
        $keysPrefix = null)
    {
        $options = [];

        foreach ($collection as $k => $r) {
            $key = null;
            if ($attributeOrCallbackForKeys) {
                if (isset($r->$attributeOrCallbackForKeys)) {
                    $key = (string)$r->$attributeOrCallbackForKeys;
                } elseif (method_exists($r, $attributeOrCallbackForKeys)) {
                    $key = (string)$r->$attributeOrCallbackForKeys();
                }
            }
            if (!$key) {
                if (method_exists($r, 'getId')) {
                    $key = $r->getId();
                } elseif (method_exists($r, 'getUid')) {
                    $key = $r->getUid();
                }
            }
            if (!$key) {
                $key = $k;
            }
            if ($attributeOrCallbackForValues) {
                if (is_array($attributeOrCallbackForValues)) {
                    $values = [];
                    foreach ($attributeOrCallbackForValues as $attr) {
                        $values[] = (string)self::getObjectAttributeFromPath($r, $attr);
                    }
                    $value = implode(' - ', $values);
                } elseif (is_callable($attributeOrCallbackForValues)) {
                    $value = (string)$attributeOrCallbackForValues($r);
                } elseif (is_string($attributeOrCallbackForValues)) {
                    // possibilité de spécifier un format avec des motifs, ex: "{structure.lc_structure} [{c_structure}]"
                    $pattern = '`{((\w[.]?)+)}`';
                    preg_match_all($pattern, $attributeOrCallbackForValues, $matches);
                    if ($matches[1]) {
                        $attributeValues = $matches[1];
                        foreach ($attributeValues as $i => $match) {
                            $attributeValues[$i] = (string)self::getObjectAttributeFromPath($r, $match);
                        }
                        $format = preg_replace($pattern, '%s', $attributeOrCallbackForValues);
                        $value  = vsprintf($format, $attributeValues);
                    } else {
                        $value = (string)self::getObjectAttributeFromPath($r, $attributeOrCallbackForValues);
                    }
                } else {
                    $value = (string)$r;
                }
            } else {
                $value = "" . $r;
            }

            $key           = $keysPrefix . $key;
            $options[$key] = $value;
        }

        if ($sort) {
            asort($options);
        }

        return $options;
    }



    /**
     * Recherche et remplace des motifs du genre {id} dans une chaîne de caractères.
     *
     * @param string $string       Ex: "Les données personnelles de {intervenant} ont été saisies le {dateModification}."
     * @param array  $replacements Ex: ['dateModification' => 'mardi 14 juillet 2015', 'intervenant' => "Bertrand GAUTHIER"]
     *
     * @return string Ex: "Les données personnelles de Bertrand GAUTHIER ont été saisies le mardi 14 juillet 2015."
     */
    static public function tokenReplacedString($string, array $replacements = [])
    {
        $pattern = '`{((\w?)+)}`';
        $result  = $string; // Ex: "Les données personnelles de {intervenant} ont été saisies le {dateModification}."

        preg_match_all($pattern, $string, $matches);

        if ($matches[0]) {
            $tokens = $matches[0]; // Ex: [0 => '{intervenant}', 1 => '{dateModification}']
            $keys   = $matches[1]; // Ex: [0 => 'intervenant',   1 => 'dateModification']
            foreach ($keys as $i => $key) {
                if (isset($replacements[$key])) {
                    $result = str_replace($tokens[$i], $replacements[$key], $result);
                }
            }
        }

        return $result;
    }



    /**
     * Accède à un attribut d'un objet spécifié par un chemin.
     *
     * @param object $object Objet concerné
     * @param string $path   Ex: "poste.no_poste", "structure.toString", "lc_structure"
     *
     * @return mixed
     */
    static public function getObjectAttributeFromPath($object, $path)
    {
        if (!is_object($object)) {
            throw new LogicException("Le premier argument doit être un objet.");
        }
        if (!is_string($path)) {
            throw new LogicException("Le deuxième argument doit être une chaîne de caractère (ex: 'poste.no_poste').");
        }

        $value      = $object;
        $attributes = explode('.', $path);

        foreach ($attributes as $index => $attr) {
            if ('toString' === $attr) {
                return (string)$value;
            }
            if (isset($value->$attr)) {
                $value = $value->$attr;
            } elseif (method_exists($value, $attr)) {
                $value = $value->$attr();
            } else {
                throw new LogicException(
                    "L'attribut '$attr' spécifié dans le chemin '$path' n'est ni un attribut accessible ni une méthode existante.");
            }
            if ((!$value || !is_object($value)) && $index !== count($attributes) - 1) {
                throw new LogicException(
                    "L'attribut non terminal '$attr' spécifié dans le chemin '$path' retourne une valeur nulle ou un scalaire.");
            }
        }

        return $value;
    }



    /**
     * Génère une chaine de caractères corespondant à la date spécifiée (ou l'instant présent)
     * au format "aaaammjj_hhmmss", pouvant être utilisée dans un nom de fichier par exemple.
     *
     * @param \DateTime $datetime Date/heure voulue éventuelle
     *
     * @return string
     */
    public static function generateStringTimestamp(\DateTime $datetime = null)
    {
        if (!$datetime) {
            $datetime = new \DateTime();
        }

        return $datetime->format('Ymd_His');
    }



    /**
     * Affiche le temps écoulé depuis le dernier top du chronomètre.
     *
     * Permet de chronométrer le temps écoulé à plusieurs endroits de votre code.
     *
     * @param string  $message Message d'accompagnement
     * @param boolean $reset   Remettre à zéro le chronomètre avant
     */
    static public function topChrono($message = null, $reset = false)
    {
        $mt = microtime(true);
        if ($reset) {
            self::$microtime = null;
        }
        if (null !== self::$microtime) {
            $top = $mt - self::$microtime;
        } else {
            $top = 0;
        }
        self::$microtime = $mt;
        if (!$message) {
            $message = "chrono";
        }
        var_dump($message . ": " . round($top, 4) . ' seconde(s)');
    }



    /**
     * Compresse un fichier ou un répertoire.
     *
     * @param string $source      Chemin complet de la source
     * @param string $destination Chemin complet de l'archive à créer.
     *
     * @return \ZipArchive
     * @throws RuntimeException
     */
    public static function zip($source, $destination)
    {
        if (!extension_loaded('zip')) {
            throw new RuntimeException("Extension PHP 'zip' non chargée.");
        }
        if (!file_exists($source)) {
            throw new RuntimeException("Le fichier ou répertoire source spécifié n'existe pas.");
        }

        $zip = new \ZipArchive();
        if (true !== $res = $zip->open($destination, \ZipArchive::CREATE)) {
            throw new RuntimeException("Impossible de créer l'archive '$destination'.");
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', realpath($file));

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        $zip->close();

        return $zip;
    }



    /**
     * Supprime un fichier ou un répertoire.
     *
     * @param string $fileOrDirectoryPath Chemin absolu de la cible à supprimer
     *
     * @return boolean
     */
    static public function removeFile($fileOrDirectoryPath)
    {
        if (!file_exists($fileOrDirectoryPath)) {
            throw new RuntimeException("Le fichier ou répertoire '$fileOrDirectoryPath' n'existe pas.");
        }

        if (is_file($fileOrDirectoryPath)) {
            $r = @unlink($fileOrDirectoryPath);
            if (!$r) {
                return false;
            }

            return true;
        }

        $ouverture = @opendir($fileOrDirectoryPath);
        if (!$ouverture) {
            throw new RuntimeException("Impossible d'ouvrir le répertoire <$fileOrDirectoryPath>.");
        }
        while ($fichier = readdir($ouverture)) {
            if ($fichier == '.' || $fichier == '..') {
                continue;
            }
            if (is_dir($fileOrDirectoryPath . "/" . $fichier)) {
                $r = self::removeFile($fileOrDirectoryPath . "/" . $fichier);
                if (!$r) {
                    return false;
                }
            } else {
                $r = @unlink($fileOrDirectoryPath . "/" . $fichier);
                if (!$r) {
                    return false;
                }
            }
        }
        closedir($ouverture);

        return @rmdir($fileOrDirectoryPath);
    }



    /**
     * Tronque une chaîne de caractères au dernier espace trouvé dans les N premiers caractères de celle-ci.
     *
     * @param string  $string   Chaîne de caractères à tronquer
     * @param integer $length   N
     * @param string  $appended Ajouté à la fin de la chaîne tronquée
     *
     * @return string
     */
    static public function truncatedString($string, $length = 60, $appended = '...')
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        if ($string[$length] === ' ') {
            return substr($string, 0, $length) . $appended;
        }

        return substr($string, 0, strrpos(substr($string, 0, $length), ' ')) . $appended;
    }



    /**
     * Formatte un nombre flottant pour l'affichage.
     *
     * @param mixed   $value          Nombre à formatter
     * @param integer $style          Ex: \NumberFormatter::DECIMAL (par défaut), \NumberFormatter::CURRENCY (2 décimales + symbole monnaie)
     * @param integer $fractionDigits Nombre de chiffres après la virgule (2 par défaut, -1 = affiche autant de digits que nécessaire)
     *
     * @return string
     */
    static public function formattedFloat($value, $style = \NumberFormatter::DECIMAL, $fractionDigits = 2)
    {
        $formatter = new \Zend\I18n\Filter\NumberFormat(\Locale::getDefault(), $style);
        if (-1 != $fractionDigits) {
            $formatter->getFormatter()->setAttribute(\NumberFormatter::FRACTION_DIGITS, $fractionDigits);
        }

        return $formatter->filter($value);
    }



    /**
     * formatte en HTML un nombre d'heures
     *
     * @param float $heures
     *
     * @return string
     */
    public static function formattedNumber($number)
    {
        $number = round((float)$number, 2);
        $class  = $number < 0 ? 'negatif' : 'positif';
        $number = self::formattedFloat($number, \NumberFormatter::DECIMAL, 2);
        $number = str_replace(',00', '<span class="number-dec-00">,00</span>', $number);
        $number = '<span class="number number-' . $class . '">' . $number . '</span>';

        return $number;
    }



    /**
     * Formatte en HTML une somme en euros
     *
     * @param float $heures
     *
     * @return string
     */
    public static function formattedEuros($montant)
    {
        return self::formattedNumber($montant) . ' &euro;';
    }



    /**
     * Formatte en HTML un pourcentage, avec deux chiffres après la virgule
     *
     * @param float $number
     *
     * @return string
     */
    public static function formattedPourcentage($number)
    {
        $number = round((float)$number * 100, 2);
        $class  = $number < 0 ? 'negatif' : 'positif';
        $number = self::formattedFloat($number, \NumberFormatter::DECIMAL, 2);
        $number = str_replace(',00', '<span class="heures-dec-00">,00</span>', $number) . '%';
        $number = '<span class="number number-' . $class . '">' . $number . '</span>';

        return $number;
    }



    /**
     * Convertit en octets le paramètre spécifié.
     *
     * @param string|int $val Ex: '8M', '1024k', 132125
     *
     * @return int Ex: 8388608, 1048576, 132125
     */
    static public function convertAsBytes($val)
    {
        if (is_numeric($val)) {
            return (int)$val;
        }
        $val  = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }



    /**
     *
     * @param array      $data
     * @param array|null $header
     * @param string     $delimiter
     * @param string     $enclosure
     *
     * @return string
     */
    static public function arrayToCsv($data = [], $header = [], $delimiter = ';', $enclosure = '"')
    {
        /**
         * Byte Order Mark (BOM) : nécessaire pour windows (sinon obligé de faire un utf8_decode)
         */
        $BOM       = "\xEF\xBB\xBF";
        $DEC_POINT = ',';

        $handle = fopen("php://temp", 'r+');

        $show_header = !empty($data) || !empty($header);
        if (empty($header)) {
            $show_header = false;
            reset($data);
            $line = current($data);
            if (!empty($line)) {
                reset($line);
                $first = current($line);
                if (substr($first, 0, 2) == 'ID' && !preg_match('/["\\s' . $delimiter . ']/', $first)) {
                    array_shift($data);
                    array_shift($line);
                    if (empty($line)) {
                        fwrite($handle, "\"{$first}\"\r\n");
                    } else {
                        fwrite($handle, "\"{$first}\"" . $delimiter);
                        fputcsv($handle, $line, $delimiter, $enclosure);
                        fseek($handle, -1, SEEK_CUR);
                        fwrite($handle, "\r\n");
                    }
                }
            }
        } else {
            reset($header);
            $first = current($header);
            if (substr($first, 0, 2) == 'ID' && !preg_match('/["\\s' . $delimiter . ']/', $first)) {
                array_shift($header);
                if (empty($header)) {
                    $show_header = false;
                    fwrite($handle, "\"{$first}\"\r\n");
                } else {
                    fwrite($handle, "\"{$first}\"" . $delimiter);
                }
            }
        }
        if ($show_header) {
            fputcsv($handle, $header, $delimiter, $enclosure);
            fseek($handle, -1, SEEK_CUR);
            fwrite($handle, "\r\n");
        }
        if (!empty($data)) {
            foreach ($data as $line) {
                $line = array_map(function ($item) use ($DEC_POINT) {
                    if (is_float($item)) {
                        return str_replace('.', $DEC_POINT, (string)$item);
                    } elseif ($item instanceof \DateTime) {
                        return $item->format('d/m/Y');
                    }

                    return $item;
                }, $line);
                fputcsv($handle, $line, $delimiter, $enclosure);
                fseek($handle, -1, SEEK_CUR);
                fwrite($handle, "\r\n");
            }
        }

        rewind($handle);
        $result = $BOM . stream_get_contents($handle);
        fclose($handle);

        return $result;
    }



    static private function uniUtilTP($traceLine)
    {
        $res = [
            'index'       => null,
            'file'        => null,
            'line-number' => null,
            'code'        => null,
            'is-internal' => false,
            'in-vendor'   => false,
            'not-matched' => null,
        ];

        preg_match('/^#([0-9]+) (.*): (.*)/', $traceLine, $m);
        if (4 == count($m)) {
            preg_match('/^(.+)\(([0-9]+)\)$/', $m[2], $m2);

            $res['index'] = $m[1];
            $res['code']  = $m[3];
            if (3 === count($m2)) {
                $res['file']        = $m2[1];
                $res['line-number'] = $m2[2];
                $res['in-vendor']   = false !== strpos($m2[1], '/vendor/');
            } elseif ($m[2] === '[internal function]') {
                $res['is-internal'] = true;
                $res['file']        = 'Appel interne';
            }
        } else {
            $res['not-matched'] = $traceLine;
        }

        return $res;
    }



    static public function formatTraceString($trace)
    {
        $result = '<style> pre pre {padding:3px;font-size:8pt;background-color:white} </style>';
        $trace  = explode("\n", $trace);
        foreach ($trace as $index => $line) {
            $tr = self::uniUtilTP($line);

            if ($tr['not-matched']) {
                $result .= '<div style="white-space:nowrap">' . substr($tr['not-matched'], 1) . '</div>';
            } else {
                if ($tr['is-internal']) {
                    // on parse la ligne suivante
                    $trNext = self::uniUtilTP($trace[$index + 1]);
                    // on détermine par rapport à la ligne suivante si l'appel interne fait partie de vendor ou non
                    $tr['in-vendor'] = $trNext['in-vendor'];
                }

                if (class_exists('\UnicaenCode\Util')) {
                    $tr['code'] = \UnicaenCode\Util::highlight($tr['code'], 'php', false);
                } else {
                    $tr['code'] = '<pre>' . $tr['code'] . '</pre>';
                }

                $result .= '<div style="white-space:nowrap' . ($tr['in-vendor'] ? ';opacity:.5' : '') . '">' . $tr['index'] . ' ' . $tr['file'];
                if (null !== $tr['line-number']) {
                    $result .= ' <span class="badge">' . $tr['line-number'] . '</span>';
                }
                $result .= '</div>';
                $result .= '<div style="margin-left:8em;margin-top:1px;margin-bottom:5px' . ($tr['in-vendor'] ? ';opacity:.5' : '') . '">' . $tr['code'] . '</div>';
            }
        }

        return $result;
    }



    /**
     * Extrait les feuilles d'un arbre.
     *
     * @param array $array
     */
    static public function extractArrayLeafNodes($array)
    {
        $leaves = [];

        $callback = function ($value) use (&$leaves) {
            $leaves[] = $value;
        };
        array_walk_recursive($array, $callback);

        return $leaves;
    }



    /**
     * @param string $str
     * @param string $encoding
     *
     * @return string
     */
    public static function stripAccents($str, $encoding = 'UTF-8')
    {
        $from = 'ÀÁÂÃÄÅÇÐÈÉÊËÌÍÎÏÒÓÔÕÖØÙÚÛÜŸÑàáâãäåçðèéêëìíîïòóôõöøùúûüÿñ';
        $to   = 'AAAAAACDEEEEIIIIOOOOOOUUUUYNaaaaaacdeeeeiiiioooooouuuuyn';

        return self::strtr($str, $from, $to, false, $encoding);
    }



    /**
     * @param string $str
     * @param string $encoding
     *
     * @return string
     */
    public static function reduce($str, $encoding = 'UTF-8')
    {
        $from = 'ÀÁÂÃÄÅÇÐÈÉÊËÌÍÎÏÒÓÔÕÖØÙÚÛÜŸÑàáâãäåçðèéêëìíîïòóôõöøùúûüÿñ€@ "\'';
        $to   = 'aaaaaacdeeeeiiiioooooouuuuynaaaaaacdeeeeiiiioooooouuuuynea___';

        return strtolower(self::strtr($str, $from, $to, false, $encoding));
    }



    /**
     * @param string  $str      Chaîne à traiter
     * @param string  $from     Chaîne de caractères source
     * @param string  $to       Chaîne de caractères destination
     * @param boolean $strict   retourne false si un caractère de <code>$str</code> n'est pas listé dans <code>$from</code>
     * @param string  $encoding Encodage de la chaîne (UTF-8 si non précisé)
     *
     * @return string
     */
    static public function strtr($str, $from, $to, $strict = false, $encoding = 'UTF-8')
    {
        $rstr = '';
        $ok   = true;
        $len  = mb_strlen($str, $encoding);
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($str, $i, 1, $encoding);
            $pos  = mb_strpos($from, $char, 0, $encoding);
            if (false === $pos) {
                if ($strict) {
                    return false;
                } else $rstr .= $char;
            } else {
                $rstr .= mb_substr($to, $pos, 1, $encoding);
            }
        }

        return $rstr;
    }
}
