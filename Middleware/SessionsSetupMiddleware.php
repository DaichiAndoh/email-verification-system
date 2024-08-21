<?php

namespace Middleware;

use Response\HTTPRenderer;

class SessionsSetupMiddleware implements Middleware {
    public function handle(Callable $next): HTTPRenderer {
        error_log('Setting up sessions...');
        session_start();
        // セッションに関するその他の処理を行う

        // 次のミドルウェアに進む
        return $next();
    }
}
