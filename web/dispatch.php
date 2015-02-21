<?php

if (is_file(__DIR__.$_SERVER['REQUEST_URI'])) {
    return false;
}

require(__DIR__.'/../vendor/autoload.php');


$container = new Pimple\Container;
$container['app'] = function () {
    return new Tonic\Application(array(
        'load' => array(
            __DIR__.'/../src/Contentacle/Resources/*.php'
        )
    ));
};
$container['request'] = function () {
    return new Contentacle\Request(array(
        'uri' => $_SERVER['PHP_SELF'],
        'mimetypes' => array(
            'yaml' => 'text/yaml',
            'yml' => 'text/yaml',
            'json' => 'application/json'
        )
    ));
};
$container['repo_dir'] = __DIR__.'/../repos';
$container['temp_dir'] = sys_get_temp_dir();
$container['yaml'] = function () {
    return new Contentacle\Services\Yaml;
};
$container['template'] = function ($c) {
    return new Contentacle\Services\Template($c['temp_dir']);
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
$container['resource_factory'] = function ($c) {
    return function ($className) use ($c) {
        $deps = array(
            'app' => $c['app'],
            'request' => $c['request'],
            'resourceFactory' => $c['resource_factory'],
            'response' => $c['response'],
            'userRepository' => $c['user_repository'],
            'repoRepository' => $c['repo_repository']
        );
        return new $className($deps);
    };
};
$container['response'] = function ($c) {
    return function ($code = null, $template = null) use ($c) {
        return new Contentacle\Response($code, $template, $c['yaml'], $c['template']);
    };
};

$app = $container['app'];
$request = $container['request'];

if (
    in_array('text/yaml', $request->accept) ||
    in_array('application/yaml', $request->accept)
) {
    $request->addAccept('application/hal+yaml');
}

if (
    in_array('text/json', $request->accept) ||
    in_array('application/json', $request->accept)
) {
    $request->addAccept('application/hal+json');
}

if ($request->contentType == 'application/x-www-form-urlencoded') {
    $request->data = $_POST;
} elseif (substr($request->contentType, -4) == 'yaml') {
    $request->data = $container['yaml']->decode($request->data);
} elseif (substr($request->contentType, -4) == 'json') {
    $request->data = json_decode($request->data, true);
}

try {
    $route = $app->route($request);

    $resource = $container['resource_factory']($route->getClass());
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

$response->output($request);