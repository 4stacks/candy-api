<?php

namespace GetCandy\Api\Providers;

use Drafting;
use Versioning;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Collections\Drafting\CollectionDrafter;
use GetCandy\Api\Core\Collections\Versioning\CollectionVersioner;

class CollectionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Drafting::extend('collections', function ($app) {
            return $app->make(CollectionDrafter::class);
        });

        Versioning::extend('collection', function ($app) {
            return $app->make(CollectionVersioner::class);
        });
    }
}
