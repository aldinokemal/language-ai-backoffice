<?php

return [
    'custom' => env('BROWSERSHOT_CUSTOM', false),
    'chrome' => env('BROWSERSHOT_CHROME', '/usr/bin/chrome-headless-shell-linux64/chrome-headless-shell'),
    'node' => env('BROWSERSHOT_NODE', '/usr/bin/node'),
    'npm' => env('BROWSERSHOT_NPM', '/usr/bin/npm'),
];
