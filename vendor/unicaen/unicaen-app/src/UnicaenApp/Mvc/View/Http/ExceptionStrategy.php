<?php

namespace UnicaenApp\Mvc\View\Http;

use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ViewModel;
use UnicaenApp\Exception\ExceptionInterface;

/**
 * Stratégie permettant d'afficher proprement un message d'erreur lorsqu'une exception est levée
 * dans une action.
 * La vue utilisée par cette stratégie est 'error/exception'.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ExceptionStrategy extends \Zend\Mvc\View\Http\ExceptionStrategy
{
    /**
     * Name of exception template
     * @var string
     */
    protected $exceptionTemplate = 'error/exception';
    
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareExceptionViewModel'), 1000);
//        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'prepareExceptionViewModel'), 5000);
    }

    /**
     * Create an exception view model, and set the HTTP status code
     *
     * @todo   dispatch.error does not halt dispatch unless a response is
     *         returned. As such, we likely need to trigger rendering as a low
     *         priority dispatch.error event (or goto a render event) to ensure
     *         rendering occurs, and that munging of view models occurs when
     *         expected.
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareExceptionViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                // Specifically not handling these
                return;

            case Application::ERROR_EXCEPTION:
            default:
                $exception = $e->getParam('exception');
                if (!$exception instanceof ExceptionInterface) {
                    return;
                }
                
                $model = new ViewModel(array(
                    'message'            => $exception->getMessage(),
                    'exception'          => $exception,
                    'display_exceptions' => $this->displayExceptions(),
                    'title'              => "Attention!", // titre utilisé pour les fenêtres modales
                ));
                $model->setTemplate($this->getExceptionTemplate())
                        ->setTerminal($e->getRequest()->isXmlHttpRequest());

                $e->setResult($model);
                
                $e->setError(null);
                $e->setParam('exception', null);

                $response = $e->getResponse();
//                $response->setStatusCode(200);
                if (!$response) {
                    $response = new HttpResponse();
//                    $response->setStatusCode(500);
                    $e->setResponse($response);
                } else {
                    $statusCode = $response->getStatusCode();
                    if ($statusCode === 200) {
//                        $response->setStatusCode(500);
                    }
                }

                break;
        }
    }
}