<?php
/**
 * Description of routes
 *
 * @author Bilna Development Team <development@bilna.com>
 */
use Phalcon\Mvc\Router;

$router      = new Router;
$router->removeExtraSlashes(false);
$requestUri  = $_SERVER['REQUEST_URI'];
//$requestPath = $requestUri;
//$routesLib   = new BilnaRoutes();
//$routesLib->getRequestPath($requestUri);

$routes = array (
    array (
        'url' => '/',
        'method' => array (
            'controller' => 'index',
            'action' => 'index',
        ),
    ),
    array (
        'url' => '/index',
        'method' => array (
            'controller' => 'index',
            'action' => 'index',
        ),
    ),
    array (
        'url' => '/login',
        'method' => array (
            'controller' => 'login',
            'action' => 'index',
        ),
    ),
    array (
        'url' => '/register',
        'method' => array (
            'module' => 'customer',
            'controller' => 'register',
            'action' => 'index',
        ),
    ),
    array (
        'url' => '/verify-register',
        'method' => array (
            'controller' => 'login',
            'action' => 'verifyRegister',
        ),
    ),
    array (
        'url' => '/forget-password',
        'method' => array (
            'controller' => 'login',
            'action' => 'forget',
        ),
    ),
    array (
        'url' => '/verify-forget',
        'method' => array (
            'controller' => 'login',
            'action' => 'verifyForget',
        ),
    ),
    array (
        'url' => '/change-password',
        'method' => array (
            'controller' => 'login',
            'action' => 'changePassword',
        ),
    ),
    array (
        'url' => '/brands',
        'method' => array (
            'controller' => 'brand',
            'action' => 'index',
        ),
    ),
    array (
        'url' => '/vendors',
        'method' => array (
            'controller' => 'vendor',
            'action' => 'index'
        ),
    ),
    
);


foreach ($routes as $route) {
    if ($route['url'] == $requestUri) {
        $router->add($route['url'], $route['method']);
    }
}


/**
 * STEP#2 => check route from routes table
 */
/*
$routeData = $routesLib->getRoute($requestPath);
if ($routeData) {
    if ($routeData['response_code'] == 200) {
        $router->add($requestPathFix, array (
            'controller' => $routeData['type'],
            'action' => 'index',
            'path' => $routeData['type_id'],
        ));
        $router->handle();
        
        return $router;
    }
    elseif ($routeData['response_code'] == 404) {
        $router->notFound(array(
            'controller' => 'index',
            'action' => 'notfound',
        ));
        $router->handle();
        
        return $router;
    }
}
*/

/**
 * STEP#3 => check route is Product Page
 */
/*
$productData = $routesLib->getProduct($requestPath);
if ($productData) {
    $routesLib->setRoute($requestPath, 200, '', 'product', $productData['id']);
    $router->add($requestPathFix, array (
        'controller' => 'product',
        'action' => 'index',
        'path' => $productData,
    ));
    $router->handle();
        
    return $router;
}
*/

/**
 * STEP#4 => check route is Category Page
 */
/*
$categoryData = $routesLib->getCategory($requestPath);
if ($categoryData) {
    $routesLib->setRoute($requestPath, 200, '', 'category', $categoryData['id']);
    $router->add($requestPathFix, array (
        'controller' => 'category',
        'action' => 'index',
        'path' => $categoryData,
    ));
    $router->handle();
    return $router;
}
*/

/**
 * STEP#5 => check route is Brand Page
 */
/*$brandData = $routesLib->getBrand($requestPath);
if ($brandData) {
    $routesLib->setRoute($requestPath, 200, '', 'brand', $brandData['id']);
    $router->add($requestPathFix, array (
        'controller' => 'brand',
        'action' => 'detail',
        'path' => $brandData,
    ));
    $router->handle();
        
    return $router;
}*/

/**
 * STEP#6 => check route is Vendor Page
 */
/*$vendorData = $routesLib->getVendor($requestPath);
if ($vendorData) {
    $routesLib->setRoute($requestPath, 200, '', 'vendor', $vendorData['id']);
    $router->add($requestPathFix, array (
        'controller' => 'vendor',
        'action' => 'detail',
        'path' => $vendorData,
    ));
    $router->handle();
        
    return $router;
}*/

/**
 * STEP#7 => check route is Static Page
 */
/*$staticPageData = $routesLib->getStaticPage($requestPath);
if ($staticPageData) {
    $routesLib->setRoute($requestPath, 200, '', 'staticpage', $staticPageData['id']);
    $router->add($requestPathFix, array (
        'controller' => 'staticpage',
        'action' => 'index',
        'path' => $staticPageData,
    ));
    $router->handle();
        
    return $router;
}*/


$router//->setDefaultModule('')
->setDefaultController('index')
    ->setDefaultAction('index');

$router->add('/:controller/:action',
    [
        'controller' => 1,
        'action' => 2
    ])->convert('action', function ($action) {
    return \Phalcon\Text::camelize($action);
})->convert('module', function ($module) {
    return lcfirst(\Phalcon\Text::camelize($module));
})->setName('default');


$router->add('/product/:int', array (
    'controller' => 'product',
    'action' => 'index',
    'path' => 1,
));
$router->add('/category/:int', array (
    'controller' => 'category',
    'action' => 'index',
    'path' => 1,
));
$router->add('/brand/:int', array (
    'controller' => 'brand',
    'action' => 'detail',
    'path' => 1,
));
$router->add('/vendor/:int', array (
    'controller' => 'vendor',
    'action' => 'detail',
    'path' => 1,
));
$router->add('/login-check/:params', array (
    'controller' => 'social',
    'action' => 'auth',
    'path' => 1,
));
$router->add('/customer/addresses/edit/:int', array (
    'controller' => 'customer',
    'action' => 'editAddress',
    'path' => 1
));
$router->add('/', array (
    'controller' => 'index',
    'action' => 'index',
    'path' => 1
));
$router->notFound(array(
    'controller' => 'index',
    'action' => 'route404',
));

$router->handle();
//die($_SERVER['REQUEST_URI']);

return $router;
 


