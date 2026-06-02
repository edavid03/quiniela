<?php

$providers = [
    App\Providers\AppServiceProvider::class,
];

if (class_exists('Laravel\\Telescope\\TelescopeApplicationServiceProvider')) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
