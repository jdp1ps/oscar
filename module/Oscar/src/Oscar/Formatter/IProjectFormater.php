<?php


namespace Oscar\Formatter;


use Oscar\Entity\Project;

interface IProjectFormater
{
    public function formatProject( Project $project );
}