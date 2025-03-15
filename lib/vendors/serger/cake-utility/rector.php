<?php
return \Rector\Config\RectorConfig::configure()
    ->withPaths([__DIR__.'/src'])
    ->withSets([\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_84]);