<?php
$app->get('/login[/]', function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
    $response = $container->get('Redirector')->withBaseRedirect('/login/user', 307);
    return $response;
});
$app->get('/login/{name}[/]',
    function ($name, \Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
        $container->set('view', function ($container) {
        $view = new \Slim\Views\Twig('modules/login/views/' . $container->get('settings.global')['template'], [
				'cache' => false
			]);
			$router = $container->get('router');
			$uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
			$view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
			return $view;
        });
        $csrfarray = array();
        $csrfarray['nameKey'] = $this->get('csrf')->getTokenNameKey();
        $csrfarray['valueKey'] = $this->get('csrf')->getTokenValueKey();
        $csrfarray['name'] = $request->getAttribute($csrfarray['nameKey']);
        $csrfarray['value'] = $request->getAttribute($csrfarray['valueKey']);
        $lang = new \PerSeo\Translator($container->get('current.language'), \PerSeo\Path::LangPath('login'));
        $langall = $lang->get();
        $faceapp = 'F_APP_' . $_SERVER['SERVER_NAME'];
        $facesecret = 'F_SECRET_' . $_SERVER['SERVER_NAME'];
        if (defined("$faceapp") && defined("$facesecret")) {
            $container['view']['faceapp'] = constant("$faceapp");
        }
        $googlekey = 'G_KEY_' . $_SERVER['SERVER_NAME'];
        $googlesecret = 'G_SECRET_' . $_SERVER['SERVER_NAME'];
        if (defined("$googlekey") && defined("$googlesecret")) {
            $container['view']['googlekey'] = constant("$googlekey");
        }
        return $this->get('view')->render($response, 'index.twig', [
            'titlesite' => $this->get('settings.global')['sitename'],
            'name' => $name,
            'host' => \PerSeo\Path::SiteName($request),
            'csrf' => $csrfarray,
            'lang' => $langall['body'],
            'vars' => $container->get('Templater')->vars('login')
        ]);
    })->setName('loginpage');
$app->post('/login/admin[/]', function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
	$myresponse = array(
		'type' => 'json',
		'verbose' => true
	);
	$container->set('myresponse', $myresponse);
    $remember = $container->get('Sanitizer')->POST('rememberme', 'int') == 1 ? false : true;
    $login = new \login\Controllers\Login($container, 'admins');
	$user = $container->get('Sanitizer')->POST('username', 'user');
	$pass = $container->get('Sanitizer')->POST('password', 'pass');
    return $response->withJson($login->check($user, $pass, $remember));
});