<?php

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;

$providers = [
    AppServiceProvider::class,
];

if (class_exists('Laravel\\Telescope\\TelescopeApplicationServiceProvider')) {
    $providers[] = TelescopeServiceProvider::class;
}

return $providers;
