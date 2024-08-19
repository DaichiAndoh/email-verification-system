<?php

use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;

return [
    '/' => function(): HTTPRenderer {
        return new HTMLRenderer('top', []);
    },
    '/register' => function(): HTTPRenderer {
        return new HTMLRenderer('register');
    },
];
