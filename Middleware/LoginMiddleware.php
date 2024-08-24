<?php

namespace Middleware;

use Helpers\Authenticate;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

class LoginMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log('Running login check...');
        if (!Authenticate::isLoggedIn()) {
            FlashData::setFlashData('error', 'Must login to view this page.');
            return new RedirectRenderer('/login');
        }

        return $next();
    }
}
