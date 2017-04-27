<?php

namespace UnicaenApp\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use UnicaenApp\View\Renderer\CsvRenderer;
use Zend\View\ViewEvent;
use UnicaenApp\View\Model\CsvModel;

class CsvStrategy extends AbstractListenerAggregate
{
    /**
     * Character set for associated content-type
     *
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Multibyte character sets that will trigger a binary content-transfer-encoding
     *
     * @var array
     */
    protected $multibyteCharsets = array(
        'UTF-16',
        'UTF-32',
    );

    /**
     * @var CsvRenderer
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param  CsvRenderer $renderer
     */
    public function __construct(CsvRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }

    /**
     * Set the content-type character set
     *
     * @param  string $charset
     * @return JsonStrategy
     */
    public function setCharset($charset)
    {
        $this->charset = (string) $charset;
        return $this;
    }

    /**
     * Retrieve the current character set
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Detect if we should use the CsvRenderer based on model type and/or
     * Accept header
     *
     * @param  ViewEvent $e
     * @return null|JsonRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();

        if (!$model instanceof CsvModel) {
            // no CsvModel; do nothing
            return;
        }

        // CsvModel found
        return $this->renderer;
    }

    /**
     * Inject the response with the CSV payload and appropriate Content-Type header
     *
     * @param  ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            // Discovered renderer is not ours; do nothing
            return;
        }

        $result   = $e->getResult();
        if (!is_string($result)) {
            // We don't have a string, and thus, no JSON
            return;
        }

        $model = $e->getModel(); /* @var $model CsvModel */

        // Populate response
        $response = $e->getResponse();
        $response->setContent($result);
        $headers = $response->getHeaders();

        $headers->addHeaderLine('content-type', 'text/csv; charset=' . $this->charset);
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"".$model->getFilename()."\"");
        $headers->addHeaderLine('Content-Length', strlen($model->serialize()));

        if (in_array(strtoupper($this->charset), $this->multibyteCharsets)) {
            $headers->addHeaderLine('content-transfer-encoding', 'BINARY');
        }
    }
}
