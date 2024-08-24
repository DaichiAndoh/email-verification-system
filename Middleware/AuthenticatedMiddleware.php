<?php

namespace Middleware;

use Helpers\Authenticate;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

class AuthenticatedMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log('Running authentication check...');
        if (!Authenticate::isVerified()) {
            FlashData::setFlashData('error', 'Must verify email to view this page.');
            return new RedirectRenderer('/verify/resend');
        }

        return $next();
    }
}
