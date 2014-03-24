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
$app->container['user_repository'] = function ($c) {
    return new Contentacle\Services\UserRepository($c);
};

$request = new Tonic\Request(array(
    'uri' => $_SERVER['REQUEST_URI'],
    'mimetypes' => array(
        'yml' => 'text/yaml',
        'yaml' => 'text/yaml',
        'json' => 'application/json'
    )
));

// add YAML if not in accept array
if (!array_search('text/yaml', $request->accept)) {
    $request->accept[] = 'text/yaml';
}

if ($request->contentType == 'application/yaml' || $request->contentType == 'text/yaml') {
    $request->data = \Symfony\Component\Yaml\Yaml::parse($request->data);
} elseif ($request->contentType == 'application/json') {
    $request->data = json_decode($request->data);
}

try {

    $resource = $app->getResource($request);
    $response = $resource->exec();

} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, 'Nothing found');

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="Contentacle"';

} catch (Tonic\Exception $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}

if ($response->contentType == 'application/yaml' || $response->contentType == 'text/yaml') {
    #$response->body = \Symfony\Component\Yaml\Yaml::dump($response->body);
    $response->body = Spyc::YAMLDump($response->body, false, false, true);
} elseif ($response->contentType == 'application/json') {
    $response->body = json_encode($response->body, JSON_PRETTY_PRINT);
}

$response->output();
