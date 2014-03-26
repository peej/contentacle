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

$container = new Pimple;
$container['repo_dir'] = __DIR__.'/../repos';
$container['yaml'] = function () {
    return new Contentacle\Services\Yaml;
};
$container['git'] = function ($c) {
    return function ($username, $repoName) use ($c) {
        return new Git\Repo($c['repo_dir'].'/'.$username.'/'.$repoName);
    };
};
$container['user_repository'] = function ($c) {
    return new Contentacle\Services\UserRepository($c['repo_dir'], $c['user']);
};
$container['repo_repository'] = function ($c) {
    return new Contentacle\Services\RepoRepository($c['repo_dir'], $c['repo'], $c['yaml']);
};
$container['user'] = function ($c) {
    return function ($username) use ($c) {
        return new Contentacle\Models\User($username, $c['repo_dir']);
    };
};
$container['repo'] = function ($c) {
    return function ($data) use ($c) {
        return new Contentacle\Models\Repo($data, $c['git']);
    };
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
    $request->data = $container['yaml']->decode($request->data);
} elseif ($request->contentType == 'application/json') {
    $request->data = json_decode($request->data);
}

try {

    $resource = $app->getResource($request);
    $resource->setContainer($container);
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
    $response->body = $container['yaml']->encode($response->body);
} elseif ($response->contentType == 'application/json') {
    $response->body = json_encode($response->body, JSON_PRETTY_PRINT);
}

$response->output();
