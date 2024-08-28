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
        $activity = $doc->getActivity();
        $process = $doc->getProcess();

        $download_url = null;
        $delete_url = null;
        $reupload_url = null;
        $edit_url = null;

        $allowRead = $this->getOscarUserContextService()->contractDocumentRead($doc);
        $allowManage = $this->getOscarUserContextService()->contractDocumentWrite($doc);
        $allowDownload = $allowRead;
        $allowReUpload = $allowManage;
        $allowDelete = $allowManage;
        $allowEdit = $allowManage;

        $infoDelete = "";


        $process_update_url = null;
        $process_delete_url = null;

        $allowProcessCreate = false;
        $allowProcessDelete = false;
        $allowProcessUpdate = false;

        $infoProcess = "";

        // Il y a un processus en cours
        if ($process) {
            $allowReUpload = false;

            $allowProcessUpdate = $this->getOscarUserContextService()->hasPrivileges(
                SignaturePrivileges::SIGNATURE_SYNC,
                $activity
            );

            if ($process->isFinished()) {
                $allowProcessUpdate = false;
                $allowDelete = $this->getOscarUserContextService()->hasPrivileges(
                    Privileges::MAINTENANCE_SIGNATURE_DELETE,
                    $activity
                );
            }
            else {
                $allowDelete = $allowProcessDelete = $this->getOscarUserContextService()->hasPrivileges(
                    SignaturePrivileges::SIGNATURE_ADMIN,
                    $activity
                );
            }

            if ($allowProcessUpdate) {
                $process_update_url = $this->getUrlHelper()->fromRoute(
                    'contractdocument/process',
                    ['id' => $doc->getId()]
                );
            }
        }
        else {
            $allowProcessCreate = $this->getOscarUserContextService()->hasPrivileges(
                SignaturePrivileges::SIGNATURE_CREATE,
                $doc->getActivity()
            );
        }

        // Accès aux fonctionnalités du document.
        $docAdded = $doc->toJson();

        if ($this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_SHOW, $doc->getActivity())) {
            $docAdded['activity'] = $this->activitySimple($doc->getActivity(), $showUrls);
        }

        if ($allowRead) {
            $download_url = $this->getUrlHelper()->fromRoute('contractdocument/download', ['id' => $doc->getId()]);
        }

        if ($allowDelete) {
            $delete_url = $this->getUrlHelper()->fromRoute('contractdocument/delete', ['id' => $doc->getId()]);
        }

        if ($allowEdit) {
            $edit_url = $this->getUrlHelper()->fromRoute('contractdocument/edit', ['document_id' => $doc->getId()]);
        }

        if($allowProcessCreate){
            $process_create_url = $this->getUrlHelper()->fromRoute('contractdocument/process-create', ['document_id' => $doc->getId()]);
        }

        if($allowProcessDelete){
            $process_delete_url = $this->getUrlHelper()->fromRoute('contractdocument/process-delete', ['document_id' => $doc->getId()]);
        }

        if($allowReUpload){
            $reupload_url = $this->getUrlHelper()->fromRoute('contractdocument/reupload', [
                'document_id'         => $doc->getId()
            ]);
        }
        $docAdded['uploader'] = $doc->getPerson() ? $this->personSimple($doc->getPerson(), true) : null;
        $docAdded['manage_process'] = $allowProcessUpdate;
        $docAdded['process_triggerable'] = $allowProcessCreate;
        $docAdded['urlProcessDelete'] = $process_delete_url;
        $docAdded['urlProcessCreate'] = $process_create_url;
        $docAdded['urlProcessUpdate'] = $process_update_url;
        $docAdded['urlDownload'] = $download_url;
        $docAdded['urlReupload'] = $reupload_url;
        $docAdded['urlDelete'] = $delete_url;
        $docAdded['urlEdit'] = $edit_url;

        return $docAdded;
    }

    public function activitySimple(Activity $activity, bool $urlShow = false): array
    {
        $output = [
            'id'              => $activity->getId(),
            'label'           => $activity->getLabel(),
            'num'             => $activity->getOscarNum(),
            'project_acronym' => $activity->getProject()?->getAcronym(),
            'project_id'      => $activity->getProject()?->getId(),
            'project_label'   => $activity->getProject()?->getLabel(),
        ];

        $url_show = null;

        if ($urlShow) {
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