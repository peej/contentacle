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
        $repoDir = $c['repo_dir'].'/'.$username.'/'.$repoName;
        return new Git\Repo($repoDir);
    };
};
$container['user_repository'] = function ($c) {
    return new Contentacle\Services\UserRepository($c['repo_dir'], $c['user']);
};
$container['repo_repository'] = function ($c) {
    return new Contentacle\Services\RepoRepository($c['repo_dir'], $c['repo']);
};
$container['user'] = function ($c) {
    return function ($data) use ($c) {
        return new Contentacle\Models\User($data);
    };
};
$container['repo'] = function ($c) {
    return function ($data) use ($c) {
        return new Contentacle\Models\Repo($data, $c['git'], $c['repo_dir'], $c['yaml'], $c['user_repository']);
    };
};

if (strpos($_SERVER['HTTP_ACCEPT'], 'text/json') !== false) {
    $_SERVER['HTTP_ACCEPT'] .= ',application/json';
}
$_SERVER['HTTP_ACCEPT'] .= ',text/yaml';

$request = new Tonic\Request(array(
    'uri' => $_SERVER['PHP_SELF'],
    'mimetypes' => array(
        'yaml' => 'text/yaml',
        'yml' => 'text/yaml',
        'json' => 'application/json'
    )
));

if (substr($request->contentType, -4) == 'yaml') {
    $request->data = $container['yaml']->decode($request->data);
} elseif (substr($request->contentType, -4) == 'json') {
    $request->data = json_decode($request->data, true);
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

$response->setHeader('Access-Control-Allow-Origin', '*');
if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
    $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
}
if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
    $response->setHeader('Access-Control-Allow-Headers', $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
}

if (is_array($response->body)) {
    if (substr($response->contentType, -4) == 'json') {
        $response->body = json_encode($response->body, JSON_PRETTY_PRINT);
    } else {
        $response->body = $container['yaml']->encode($response->body);
    }
}

if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla') !== false) {
    $response->headers['X-Content-Type'] = $response->contentType;
    $response->contentType = 'text/plain';
}

$response->output();
