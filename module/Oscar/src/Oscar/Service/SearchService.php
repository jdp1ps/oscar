<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 22/06/15 13:44
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Service;

use Oscar\Entity\Project;

class SearchService
{
    private $dataPath;
    private $index;

    public function __construct($indexPath)
    {
        $this->dataPath = $indexPath;
    }

    public function find($expression)
    {
        $query = \Zend_Search_Lucene_Search_QueryParser::parse($expression);
        $hits = $this->getIndex()->find($query);
        $ids = [];
        foreach ($hits as $hit) {
            $ids[] = $hit->ID;
        }

        return $ids;
    }

    public function update(Project $project)
    {
        $keys = $this->getIndex()->find(\Zend_Search_Lucene_Search_QueryParser::parse(
            sprintf('key:"%s"', md5($project->getId())))
        );

        foreach ($keys as $doc) {
            if ($doc->ID === $project->getId()) {
                $this->getIndex()->delete($doc->ID);
            }
        }

        $this->addNewProject($project);
    }

    ////////////////////////////////////////////////////////////////////////////
    protected function getIndex()
    {
        if ($this->index === null) {
            if (!$this->checkPath()) {
                $this->index = \Zend_Search_Lucene::create($this->dataPath);
            } else {
                $this->index = \Zend_Search_Lucene::open($this->dataPath);
            }
            // Lucene configuration globale
            \Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
            \Zend_Search_Lucene_Analysis_Analyzer::setDefault(new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
        }

        return $this->index;
    }

    public function addNewProject(Project $project)
    {
        $members = [];
        foreach ($project->getMembers() as $memberProject /* @var \Oscar\Entity\ProjectMember */) {
            $members[] = $memberProject->getPerson()->getFirstname().' '.$memberProject->getPerson()->getLastName();
        }
        $members = implode(', ', $members);

        $partners = [];
        foreach ($project->getPartners() as $partnerProject /* @var \Oscar\Entity\ProjectPartner */) {
            $partners[] = (string) $partnerProject->getOrganization();
        }
        $partners = implode(', ', $partners);

        $grants = [];
        foreach ($project->getGrants() as $grant /* @var \Oscar\Entity\Activity */) {
            $grants[] = (string) $grant;
        }
        $grants = implode(' , ', $grants);

        $corpus = new \Zend_Search_Lucene_Document();
        $corpus->addField(\Zend_Search_Lucene_Field::text('label', $project->getLabel(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('acronym', $project->getAcronym(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('description', $project->getDescription(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('saic', $project->getCentaureId(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('eotp', $project->getEotp(), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('members', $members, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('partners', $partners, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('grants', $grants, 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('key', md5($project->getId()), 'UTF-8'));
        $corpus->addField(\Zend_Search_Lucene_Field::text('ID', $project->getId(), 'UTF-8'));

        $this->getIndex()->addDocument($corpus);
    }

    public function resetIndex()
    {
        $this->index = \Zend_Search_Lucene::create($this->dataPath);
    }

    private function checkPath()
    {
        return file_exists($this->dataPath) && is_readable($this->dataPath) && ($resources = scandir($this->dataPath)) && (count($resources) > 2);
    }
}
