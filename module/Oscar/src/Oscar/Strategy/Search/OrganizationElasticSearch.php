<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 16/04/19
 * Time: 16:58
 */

namespace Oscar\Strategy\Search;


use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Oscar\Connector\ConnectorRepport;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Exception\OscarException;

class OrganizationElasticSearch extends ElasticSearchEngine implements IOrganizationSearchStrategy
{
    public function getIndex(): string
    {
        return 'oscar-organization';
    }

    public function getType(): string
    {
        return 'organization';
    }


    public function add(Organization $entity): callable|array
    {
        return $this->addItem($entity);
    }

    public function getIndexableDatas(mixed $object): array
    {
        $projects = [];
        $activities = [];
        $persons = [];
        $connectors = [];

        /** @var OrganizationPerson $member */
        foreach ($object->getPersons() as $member) {
            $persons[] = (string)$member->getPerson();
        }

        /** @var ActivityOrganization $activityOrganization */
        foreach ($object->getActivities() as $activityOrganization) {
            if ($activityOrganization->getActivity()->getProject()) {
                $project = (string)$activityOrganization->getActivity()->getProject()->getAcronym(
                    ) . " " . (string)$activityOrganization->getActivity()->getProject()->getLabel();
                if (!in_array($project, $projects)) {
                    $projects[] = $project;
                }
            }
            $activity = (string)$activityOrganization->getActivity()->getLabel();
            if (!in_array($activity, $activities)) {
                $activities[] = $activity;
            }
        }
        if ($object->getConnectors()) {
            foreach ($object->getConnectors() as $name => $value) {
                $connectors[] = $value;
            }
        }

        return [
            'id'          => $object->getId(),
            'code'        => $object->getCode() ? $object->getCode() : "",
            'shortname'   => $object->getShortName(),
            'fullname'    => $object->getFullName(),
            'email'       => $object->getEmail(),
            'city'        => $object->getCity(),
            'country'     => $object->getCountry(),
            'zipcode'     => $object->getZipCode(),
            'description' => $object->getDescription(),
            'siret'       => $object->getSiret(),
            'persons'     => $persons,
            'activities'  => $activities,
            'connectors'  => $connectors
        ];
    }

    public function getFieldsSearchedWeighted(string $search): array
    {
        return [
            "bool" => [
                "should"               => [
                    [
                        "match" => [
                            "code" => [
                                "query" => $search,
                                "boost" => 9  // Favoriser les correspondances
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "shortname" => [
                                "query" => $search,
                                "boost" => 9  // Favoriser les correspondances
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "fullname" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes
                                "boost" => 5  // Favoriser les correspondances
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "description" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "email" => [
                                "query" => $search,
                                'fuzziness' => "AUTO"
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "city" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "siret" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "country" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "connector" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "zipcode" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "persons" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                    [
                        "match" => [
                            "activities" => [
                                "query" => $search,
                                'fuzziness' => "AUTO", // "Tolérance" aux fautes,
                            ]
                        ]
                    ],
                ],
                "minimum_should_match" => 1
            ]
        ];
    }

    public function update(Organization $entity): callable|array
    {
        return $this->searchUpdate($entity);
    }
}