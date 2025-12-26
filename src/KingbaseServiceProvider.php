<?php

namespace srcker\Kingbase;

use Illuminate\Support\ServiceProvider;
use srcker\Kingbase\Database\KingbaseConnection;

class KingbaseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['db']->extend('kingbase', function (array $config) {
            return new KingbaseConnection(
                KingbaseConnection::createPdo($config),
                $config['database'] ?? '',
                $config['prefix'] ?? '',
                $config
            );
        });
    }
}