<?php

namespace Routing;

use Closure;
use Helpers\Settings;

class Route {
    private string $path;
    /**
     * @var string[]
     */
    private array $middleware;
    private Closure $callback;

    public function __construct(string $path, callable $callback) {
        $this->path = $path;
        // Closure::fromCallable($callable) の代替構文
        $this->callback = $callback(...);
    }

    // ルートを作成するための静的関数
    public static function create(string $path, callable $callback): Route {
        return new self($path, $callback);
    }

    public function setMiddleware(array $middleware): Route {
        $this->middleware = $middleware;
        return $this;
    }

    public function getMiddleware(): array {
        return $this->middleware ?? [];
    }

    public function getCallback(): Closure {
        return $this->callback;
    }

    public function getPath(): string {
        return $this->path;
    }

    private function getBaseURL(): string {
        if (!isset($_SERVER)) return $this->getPath();

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        return sprintf($protocol . $host . $this->getPath()) ;
    }

    private function getSecretKey(): string {
        return Settings::env("SIGNATURE_SECRET_KEY");
    }

    public function getSignedURL(array $queryParameters): string {
        $url = $this->getBaseURL();

        // URLパラメータのクエリ文字列を配列から作成
        $queryString = http_build_query($queryParameters);

        // HMAC-SHA256を使って署名を作成
        $signature = hash_hmac('sha256', $url . '?' . $queryString, $this->getSecretKey());

        // パーツを組み合わせて値を返します。
        return sprintf("%s?%s&signature=%s", $url, $queryString, $signature);
    }

    public function isSignedURLValid(string $url, bool $absolute = true): bool {
        // URLデータを含む連想配列を返すparse_url組み込み関数を使用して、URLから署名を抽出
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['query'])) return false;

        $queryParams = [];
        
        // $parsedUrl['query']をparse_strでパース
        parse_str($parsedUrl['query'], $queryParams);

        if (!isset($queryParams['signature'])) return false;

        $signature = $queryParams['signature'];

        // 検証用URLから署名を削除
        $urlWithoutSignature = str_replace('&signature=' . $signature, '', $url);

        // URL生成時と同じ方法で署名を再作成
        $expectedSignature = hash_hmac('sha256', $urlWithoutSignature, $this->getSecretKey());

        return hash_equals($expectedSignature, $signature);
    }
}
