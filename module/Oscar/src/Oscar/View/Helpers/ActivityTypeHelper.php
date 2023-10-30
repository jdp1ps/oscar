<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/11/15 11:49
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;


use Oscar\Service\ActivityTypeService;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Laminas\View\Helper\AbstractHtmlElement;

class ActivityTypeHelper extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    function __invoke()
    {
        return $this;
    }


    public function chain( $activity ){
        if( !$activity ){
            return '<i class="no-data">Pas de type</i>';
        } else {
            $sl = $this->getServiceLocator()->getServiceLocator();
            $types = $sl->get(ActivityTypeService::class)->getActivityTypeChain($activity);
            if( count($types) > 0 && $types[0]->getLabel() === 'ROOT' ){
                array_shift($types);
            }
            if( count($types) > 1 ){
                $label = $types[1];

            } else {
                $label = $types[count($types)-1];
            }

            $title = implode(' / ', $types);

            return sprintf('<span title="%s">%s</span>', implode(' / ', $types), $label);
        }
    }
}