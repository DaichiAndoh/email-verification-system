<?php
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/..'));
require_once '../vendor/autoload.php';

$DEBUG = true;

if (preg_match('/\.(?:png|jpg|jpeg|gif|js|css|html)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

// ルートの読み込み
$routes = include('Routing/routes.php');

// リクエストURIを解析してパスだけを取得
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ルートにパスが存在するかチェック
if (isset($routes[$path])) {
    // ルートの取得
    $route = $routes[$path];

    try{
        if (!($route instanceof Routing\Route)) throw new InvalidArgumentException("Invalid route type");

        // ミドルウェア読み込み
        $middlewareRegister = include('Middleware/middleware-register.php');
        $middlewares = array_merge(
            $middlewareRegister['global'],
            array_map(
                fn ($routeAlias) => $middlewareRegister['aliases'][$routeAlias],
                $route->getMiddleware()
            )
        );

        $middlewareHandler = new \Middleware\MiddlewareHandler(
            array_map(fn($middlewareClass) => new $middlewareClass(), $middlewares)
        );

        $renderer = $middlewareHandler->run($route->getCallback());

        // ヘッダーを設定
        foreach ($renderer->getFields() as $name => $value) {
            // ヘッダーの検証
            $sanitized_value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);

            if ($sanitized_value && $sanitized_value === $value) {
                header("{$name}: {$sanitized_value}");
            } else {
                http_response_code(500);
                if ($DEBUG) print("Failed setting header - original: '$value', sanitized: '$sanitized_value'");
                exit;
            }
        }

        print($renderer->getContent());
    }
    catch (Exception $e) {
        http_response_code(500);
        print("Internal error, please contact the admin.<br>");
        if ($DEBUG) print($e->getMessage());
    }
} else {
    // マッチするルートがない場合、404エラーを表示
    http_response_code(404);
    echo "{$path} - 404 Not Found: The requested route was not found on this server.";
}
