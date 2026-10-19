<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if ($app['config']->get('database.default') !== 'pgsql'
            || $app['config']->get('database.connections.pgsql.database') !== 'novashop_testing') {
            throw new LogicException('Tests must run exclusively against the novashop_testing PostgreSQL database.');
        }

        return $app;
    }
}
