<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 28/02/18
 * Time: 16:20
 */

namespace Oscar\Strategy\Search;

use Oscar\Entity\Activity;
use Oscar\Exception\OscarException;
use Oscar\Utils\StringUtils;

class ActivityZendLucene implements ActivitySearchStrategy
{
    private $index;
    private $path;

    /**
     * ActivityZendLucene constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function resetIndex()
    {
        $this->index = \Zend_Search_Lucene::create($this->searchIndex_getPath());
    }

    public function searchIndex_rebuild( $activities )
    {
        $this->rebuildIndex($activities);
    }

    /**
     * @return \Zend_Search_Lucene_Interface
     * @throws OscarException
     */
    protected function getIndex()
    {
        try {
            $path = $this->searchIndex_getPath();
            if ($this->index === null) {
                if (!$this->searchIndex_checkPath()) {
                    $this->index = \Zend_Search_Lucene::create($path);
                    $this->index = \Zend_Search_Lucene::create($path);
                } else {
                    $this->index = \Zend_Search_Lucene::open($path);
                }
                // Lucene configuration globale
                \Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
                \Zend_Search_Lucene_Analysis_Analyzer::setDefault(new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
            }
            return $this->index;
        } catch( \Zend_Search_Lucene_Exception $e ){
            throw new OscarException("Une erreur est survenu lors de l'accès à l'index de recherche");
        }
    }

    /**
     * @param $what
     * @return array
     */
    public function search($what)
    {
        $what = StringUtils::transliterateString($what);
        $query = \Zend_Search_Lucene_Search_QueryParser::parse($what);
        $hits = $this->getIndex()->find($query);
        $ids = [];
        foreach ($hits as $hit) {
            $ids[] = $hit->ID;
        }

        return $ids;
    }

    /**
     * @param $what
     * @return array
     */
    public function searchProject($what)
    {
        $query = \Zend_Search_Lucene_Search_QueryParser::parse($what);
        $hits = $this->getIndex()->find($query);
        $ids = [];
        foreach ($hits as $hit) {
            if( $hit->project_id && !in_array($hit->project_id, $ids) ){
                $ids[] = $hit->project_id;
            }
        }
        return $ids;
    }

    /**
     * @param $id
     */
    public function searchDelete( $id )
    {
        $hits = $this->getIndex()->find('key:'.md5($id));
        foreach ($hits as $hit) {
            $this->getIndex()->delete($hit->id);
        }
    }

    /**
     * @param Activity $activity
     */
    public function addActivity(Activity $activity)
    {
        $members = [];
        /** @var ActivityPerson $p */
        foreach ($activity->getPersonsDeep() as $p) {
            $members[] = $p->getPerson()->getCorpus();
        }
        $members = implode(', ', $members);

        $partners = [];
        /** @var ActivityOrganization $o */
        foreach ($activity->getOrganizationsDeep() as $o ) {
            $partners[] = $o->getOrganization()->getCorpus();
        }
        $partners = implode(', ', $partners);

        $project = '';
        $acronym = '';
        if( $activity->getProject() ){
            $project = $activity->getProject()->getCorpus();
            $acronym = $activity->getProject()->getAcronym();
        }

        $corpus = new \Zend_Search_Lucene_Document();

        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'acronym',
            StringUtils::transliterateString($acronym), 'UTF-8'));

        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'label',
            StringUtils::transliterateString($activity->getLabel()), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'description',
            StringUtils::transliterateString($activity->getDescription()), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'saic',
            $activity->getCentaureId(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'oscar',
            $activity->getOscarNum(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'eotp',
            $activity->getCodeEOTP(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'members',
            $members, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'partners',
            $partners, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'project',
            $project, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text(
            'key',
            md5($activity->getId()), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::UnIndexed(
            'ID',
            $activity->getId(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::UnIndexed(
            'project_id',
            $activity->getProject() ? $activity->getProject()->getId() : '', 'UTF-8'));


        $this->getIndex()->addDocument($corpus);
    }

    public function searchUpdate( Activity $activity )
    {
        try {
            $this->searchDelete($activity->getId());
        } catch(\Exception $e ){

        }
        $this->addActivity($activity);
    }

    /**
     * @return mixed
     */
    private function searchIndex_getPath(){
        return $this->path;
    }

    private function searchIndex_checkPath()
    {
        $path = $this->searchIndex_getPath();
        return file_exists($path) && is_readable($path) && ($resources = scandir($path)) && (count($resources) > 2);
    }

    public function rebuildIndex($activities)
    {
        $this->resetIndex();
        foreach($activities as $activity) {
            $this->addActivity($activity);
        }
        // TODO: Implement rebuildIndex() method.
    }
}