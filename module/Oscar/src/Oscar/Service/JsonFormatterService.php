<?php

namespace Oscar\Service;

use Oscar\Entity\Activity;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Person;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use UnicaenSignature\Provider\SignaturePrivileges;

class JsonFormatterService implements UseOscarConfigurationService, UseEntityManager, UseLoggerService,
                                      UseOscarUserContextService
{
    use UseOscarConfigurationServiceTrait,
        UseEntityManagerTrait,
        UseLoggerServiceTrait,
        UseOscarUserContextServiceTrait;

    private $urlHelper;

    public function getUrlHelper()
    {
        return $this->urlHelper;
    }

    public function setUrlHelper($urlHelper): self
    {
        $this->urlHelper = $urlHelper;
        return $this;
    }

    public function contractDocuments(array $documents, bool $showUrls = true): array
    {
        $output = array();
        foreach ($documents as $document) {
            $output[] = $this->contractDocument($document, $showUrls);
        }
        return $output;
    }

    public function contractDocument(ContractDocument $doc, bool $showUrls = true): array
    {
        // Processus du document
        $process = $doc->getProcess();
        $urlProcessUpdate = null;

        if ($process) {
            $allowSign = $this->getOscarUserContextService()->hasPrivileges(
                SignaturePrivileges::SIGNATURE_CREATE,
                $doc->getActivity()
            );
            $manageProcess = false;
            if ($this->getOscarUserContextService()->hasPrivileges(SignaturePrivileges::SIGNATURE_ADMIN)) {
                if ($doc->getProcess()) {
                    $urlProcessUpdate = $this->getUrlHelper()->fromRoute(
                        'contractdocument/process',
                        ['id' => $doc->getId()]
                    );
                }
            }
        }
        $processTriggerable = ($doc->getProcess() == null && $allowSign);

        // Liens
        $manage = $this->getOscarUserContextService()->contractDocumentWrite($doc);
        $read = $this->getOscarUserContextService()->contractDocumentRead($doc);

        $urlDownload = null;
        $urlDelete = null;
        $urlReupload = null;
        $docAdded = $doc->toJson();

        if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_SHOW, $doc->getActivity())) {
            $docAdded['activity'] = $this->activitySimple($doc->getActivity(), $showUrls);
        }

        if ($read) {
            $urlDownload = $this->getUrlHelper()->fromRoute('contractdocument/download', ['id' => $doc->getId()]);
        }

        if ($manage) {
            $urlDelete = $this->getUrlHelper()->fromRoute('contractdocument/delete', ['id' => $doc->getId()]);
            $urlReupload = $this->getUrlHelper()->fromRoute('contractdocument/upload', [
                'id'         => $doc->getId(),
                'idtab'      => $doc->getTabDocument()->getId(),
                'idactivity' => $doc->getActivity()->getId()
            ]);
        }
        $docAdded['uploader'] = $this->personSimple($doc->getPerson(), true);
        $docAdded['manage_process'] = $manageProcess;
        $docAdded['process_triggerable'] = $processTriggerable;
        $docAdded['urlDownload'] = $urlDownload;
        $docAdded['urlReupload'] = $urlReupload;
        $docAdded['urlDelete'] = $urlDelete;

        return $docAdded;
    }

    public function activitySimple(Activity $activity, bool $urlShow = false) :array {
        $output = [
            'id' => $activity->getId(),
            'label' => $activity->getLabel(),
            'num' => $activity->getOscarNum(),
            'project_acronym' => $activity->getProject()?->getAcronym(),
            'project_id' => $activity->getProject()?->getId(),
            'project_label' => $activity->getProject()?->getLabel(),
        ];

        $url_show = null;

        if( $urlShow ){
            $url_show = $this->getUrlHelper()->fromRoute('contract/show', ['id' => $activity->getId()]);
        }
        $output['url_show'] = $url_show;

        return $output;
    }

    public function personSimple(Person $person, bool $urlShow = false): array
    {
        $output = array(
            'id'          => $person->getId(),
            'firstName'   => $person->getFirstname(),
            'lastName'    => $person->getLastname(),
            'text'        => $person->getDisplayName(),
            'displayname' => $person->getDisplayName(),
            'label'       => $person->getDisplayName(),
        );

        $url = null;
        if ($urlShow && $this->getOscarUserContextService()->hasPrivileges(Privileges::PERSON_SHOW)) {
            $url = $this->getUrlHelper()->fromRoute('person/show', ['id' => $person->getId()]);
        }
        $output['url_show'] = $url;

        return $output;
    }

    public function person(Person $person, bool $urlShow = false): array
    {
        $output = $this->personSimple($person, $urlShow);

        $output['login'] = $person->getLadapLogin();
        $output['email'] = $person->getEmail();
        $output['mailMd5'] = md5($person->getEmail());
        $output['phone'] = $person->getPhone();
        $output['ucbnSiteLocalisation'] = $person->getLdapSiteLocation();
        $output['affectation'] = $person->getLdapAffectation();

        return $output;
    }
}