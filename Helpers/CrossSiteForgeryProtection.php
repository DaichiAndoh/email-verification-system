<?php

namespace Helpers;

class CrossSiteForgeryProtection {
    public static function getToken() {
        return $_SESSION['csrf_token'];
    }

    public static function removeToken() {
        if (isset($_SESSION['csrf_token'])) {
            unset($_SESSION['csrf_token']);
        }
    }
}
