<?php

namespace Mile6\LaravelEBMS\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mile6\LaravelEBMS\LaravelEBMSServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function (string $modelName) {
                return 'Mile6\\LaravelEBMS\\Database\\Factories\\'.class_basename($modelName).'Factory';
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelEBMSServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        include_once __DIR__.'/../database/migrations/create_laravel-ebms_table.php.stub';
        (new \CreatePackageTable())->up();
        */
    }
}
