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
        if (!is_dir($repoDir)) {
            throw new Git\Exception;
        }
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
    return function ($username) use ($c) {
        return new Contentacle\Models\User($username, $c['repo_dir']);
    };
};
$container['repo'] = function ($c) {
    return function ($data) use ($c) {
        return new Contentacle\Models\Repo($data, $c['git'], $c['yaml']);
    };
};

$request = new Tonic\Request(array(
    'uri' => $_SERVER['REQUEST_URI'],
    'mimetypes' => array(
        'yaml' => 'application/hal+yaml',
        'yml' => 'application/hal+yaml',
        'json' => 'application/hal+json'
    )
));

// switch out alternative mimetypes
foreach (array(
    'text/yaml' => 'yaml',
    'application/yaml' => 'yaml',
    'text/json' => 'json',
    'application/json' => 'json'
) as $mimetype => $format) {
    if ($pos = array_search($mimetype, $request->accept) !== false) {
        $request->accept[$pos] = 'application/hal+'.$format;
    }
}

// add HAL if not in accept array
if (array_search('application/hal+yaml', $request->accept) === false) {
    $request->accept[] = 'application/hal+yaml';
}

if ($request->contentType == 'application/hal+yaml') {
    $request->data = $container['yaml']->decode($request->data);
} elseif ($request->contentType == 'application/hal+json') {
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

if ($response->contentType == 'application/hal+yaml') {
    $response->body = $container['yaml']->encode($response->body);
} elseif ($response->contentType == 'application/hal+json') {
    $response->body = json_encode($response->body, JSON_PRETTY_PRINT);
}

if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla') !== false) {
    $response->XContentType = $response->contentType;
    $response->contentType = 'text/plain';
}

$response->output();
