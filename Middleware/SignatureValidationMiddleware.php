<?php

namespace Middleware;

use Helpers\ValidationHelper;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;

class SignatureValidationMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        $parsedUrl = parse_url($currentPath);
        $pathWithoutQuery = $parsedUrl['path'] ?? '';

        // 現在のパスのRouteオブジェクトを作成
        $route = Route::create($pathWithoutQuery, function(){});

        // URLに有効な署名があるかチェック
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        if ($route->isSignedURLValid($protocol . $host . $currentPath)) {
            // 有効期限があるかどうかを確認し、有効期限がある場合は有効期限が切れていないことを確認
            if(isset($_GET['expiration']) && ValidationHelper::integer($_GET['expiration']) < time()){
                FlashData::setFlashData('error', "The URL has expired.");
                return new RedirectRenderer('/');
            }

            // 署名が有効であれば、ミドルウェアチェインを進める
            return $next();
        } else {
            // 署名が有効でない場合、トップページにリダイレクト
            FlashData::setFlashData('error', sprintf("Invalid URL (%s).", $pathWithoutQuery));
            return new RedirectRenderer('/');
        }
    }
}
