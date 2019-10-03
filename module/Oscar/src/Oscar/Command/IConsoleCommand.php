<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 03/10/19
 * Time: 16:18
 */

namespace Oscar\Command;


interface IConsoleCommand
{
    public function getParameters() :array;
    public function getOptions(): array;
    public function execute($options);

}