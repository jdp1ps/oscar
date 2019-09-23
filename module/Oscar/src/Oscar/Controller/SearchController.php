<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 22/06/15 11:38
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Oscar\Entity\LogActivity;
use Oscar\Service\SearchService;

class SearchController extends AbstractOscarController
{
    /***
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->getServiceLocator()->get('Search');
    }
    /**
     *
     */
    public function rebuildAction()
    {
        $projects = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->all();
        $this->getSearchService()->resetIndex();

        foreach ($projects as $project) {
            $this->getSearchService()->addNewProject($project);
        }

        return 'INDEX REBUILD';
    }

    public function findAction()
    {
        $request = $this->getRequest();

        // Get user email from console and check if the user used --verbose or -v flag
        $searchFor = $request->getParam('search');

        $this->getActivity()->addActivity(
            sprintf("%s a lancé la recherche '%s'", $this->getCurrentPerson(), $searchFor),
            LogActivity::DEFAULT_LEVEL, 'Search'
        );

        $ids = $this->getSearchService()->find($searchFor);

        $projects = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->findBy(array('id' => $ids));
        foreach ($projects as $p) {
            echo 'P'.$p->getLabel()."\n";
        }
    }
}
