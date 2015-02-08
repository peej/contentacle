<?php

require(__DIR__.'/vendor/autoload.php');

@mkdir('repos');

require(__DIR__.'/features/bootstrap/FeatureContext.php');

$fc = new FeatureContext(array());
$fc->setupRepo();

echo "Done\n";