<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-29 15:53
 * @copyright Certic (c) 2017
 */

namespace Oscar;


class OscarVersion
{
    const MAJOR = 2;
    const MINOR = 5;
    const PATCH = 1;

    public static function getBuild(){
        $commitHash = trim(exec('git log -1 --pretty="%h" -n1 HEAD'));
        $branch = trim(exec('git branch | grep \* | cut -d \' \' -f2'));

        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        exec('git rev-list HEAD | wc -l', $commit);

        return sprintf('v%s.%s.%s-%s#%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $branch, $commitHash, $commitDate->format('Y-m-d H:m:s'));

    }
}