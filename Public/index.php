<?php
spl_autoload_extensions(".php");
spl_autoload_register(function($name) {
    $filepath = __DIR__ . "/../" . str_replace('\\', '/', $name) . ".php";
    require_once $filepath;
});

session_start();

$DEBUG = true;

// ルートの読み込み
$routes = include('../Routing/routes.php');

// リクエストURIを解析してパスだけを取得
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ルートにパスが存在するかチェック
if (isset($routes[$path])) {
    // コールバックを呼び出してrendererを作成
    try{
        $renderer = $routes[$path]();

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
