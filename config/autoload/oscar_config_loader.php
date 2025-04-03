<?php

$oscar_config_loader = \Oscar\Utils\Config\ConfigurationLoader::getInstance(
    __DIR__. "/oscar.yml",
    __DIR__."/../../",
    ".env"
);

return $oscar_config_loader;
