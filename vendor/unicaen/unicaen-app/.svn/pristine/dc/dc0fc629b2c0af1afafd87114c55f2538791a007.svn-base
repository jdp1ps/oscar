<?php

namespace UnicaenApp\View\Model;

use Zend\View\Model\ViewModel;


class CsvModel extends ViewModel
{
    /**
     * Csv probably won't need to be captured into a
     * a parent container by default.
     *
     * @var string
     */
    protected $captureTo = null;

    /**
     * CSV is usually terminal
     *
     * @var bool
     */
    protected $terminate = true;

    /**
     * Tableau de données CSV
     *
     * @var array
     */
    protected $data;

    /**
     * Entête CSV
     *
     * @var array
     */
    protected $header;

    /**
     * Délimiteur de champ
     *
     * @var string
     */
    protected $delimiter = ';';

    /**
     * Encapsuleur de valeur
     *
     * @var string
     */
    protected $enclosure = '"';

    /**
     *
     * @var string
     */
    protected $filename = 'export.csv';


    


    /**
     * Serialize to Csv
     *
     * @return string
     */
    public function serialize()
    {
        $variables = $this->getVariables();

        if (isset($variables['data']))      $this->addLines(        $variables['data']      );
        if (isset($variables['header']))    $this->setHeader(       $variables['header']    );
        if (isset($variables['delimiter'])) $this->setDelimiter(    $variables['delimiter'] );
        if (isset($variables['enclosure'])) $this->setEnclosure(    $variables['enclosure'] );
        if (isset($variables['filename']))  $this->setFilename(     $variables['filename']  );

        return \UnicaenApp\Util::arrayToCsv($this->getData(), $this->getHeader(), $this->getDelimiter(), $this->getEnclosure());
    }

    /**
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @return string[]|null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     *
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     *
     * @param array $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Ajoute une ligne de données
     * 
     * @param array $line
     * @return self
     */
    public function addLine( $line )
    {
        if (null === $this->data) $this->data = [];
        $this->data[] = $line;
        return $this;
    }

    /**
     * Ajoute un ensemnble de données
     * 
     * @param array[] $lines
     * @return self
     */
    public function addLines( $lines )
    {
        foreach( $lines as $line ){
            $this->addLine($line);
        }
        return $this;
    }

    /**
     *
     * @param string[]|null $header
     * @return self
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     *
     * @param string $delimiter
     * @return self
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     *
     * @param string $enclosure
     * @return self
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     *
     * @param string $filename
     * @return self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    public function __serialize()
    {
        return $this->serialize();
    }

    public function __toString()
    {
        return $this->serialize();
    }
}