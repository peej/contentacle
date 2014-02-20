<?php

if (is_file(__DIR__.$_SERVER['REQUEST_URI'])) {
    return false;
}

require(__DIR__.'/../vendor/autoload.php');


$app = new Tonic\Application(array(
    'load' => array(
        __DIR__.'/../src/Contentacle/Resources/*.php'
    )
));
$app->container = new Pimple;
$app->container['repo_dir'] = __DIR__.'/../repos';
$app->container['store'] = function($c) {
    return new Contentacle\Services\JsonStore($c);
};
$app->container['smarty'] = function($c) {
    $smarty = new Smarty;
    $smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED;
    $smarty->setTemplateDir('../views');
    $smarty->setCompileDir('/tmp');
    require_once __DIR__.'/../src/modifier.relative_date.php';
    $smarty->registerPlugin('modifier', 'relative', 'smarty_modifier_relative_date');
    $smarty->registerPlugin('modifier', 'username', array($c['store'], 'emailToUsername'));
    $smarty->registerPlugin('modifier', 'name', array($c['store'], 'emailToName'));
    return $smarty;
};

$request = new Tonic\Request();
$request->uri = $_SERVER['REQUEST_URI'];

try {

    $resource = $app->getResource($request);
    $response = $resource->exec();

} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $app->container['smarty']->fetch('404.html'));

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\Exception $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}

$response->output();
