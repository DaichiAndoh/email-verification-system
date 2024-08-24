<?php

namespace Middleware;

use Helpers\Authenticate;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

class NotVerifiedMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log('Running verification check...');
        if (Authenticate::isVerified()) {
            FlashData::setFlashData('error', 'Email verification has already been completed.');
            return new RedirectRenderer('/');
        }

        return $next();
    }
}
