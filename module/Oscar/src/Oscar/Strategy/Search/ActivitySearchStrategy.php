<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 28/02/18
 * Time: 16:46
 */

namespace Oscar\Strategy\Search;


use Oscar\Entity\Activity;

interface ActivitySearchStrategy
{
    public function search( $search );
    public function addActivity(Activity $activity);
    public function searchProject($what);
    public function searchDelete( $id );
    public function searchUpdate( Activity $activity );
    public function resetIndex();
}