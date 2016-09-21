<?php

namespace Zenapply\Viddler\Upload\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Storage;

class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getPackageProviders($app)
    {
        return ['Zenapply\Viddler\Upload\Providers\Viddler'];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getPackageAliases($app)
    {
        return [
            'Viddler' => 'Zenapply\Viddler\Upload\Facades\Viddler'
        ];
    }

    protected function migrate()
    {
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../migrations'),
        ]);
    }

    protected function migrateReset()
    {
        $version = $this->app->version();
        $version = explode(".", $version);

        if (intval($version[0]) >= 5 && intval($version[1]) >= 3) {
            $this->artisan('migrate:reset', [
                '--database' => 'testbench',
                '--realpath' => realpath(__DIR__.'/../migrations'),
            ]);
        } else {
            $this->artisan('migrate:reset', [
                '--database' => 'testbench'
            ]);
        }
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('viddler', [
            "auth" => [
                "key" => "key",
                "user" => "user",
                "pass" => "pass",
            ],

            "table" => "viddler",

            "disk" => "default",

            "convert" => [
                "enabled" => true,

                "instructions" => [
                    "video/quicktime" => "video/mp4",
                    "application/octet-stream" => "video/mp4"
                ]
            ],

            "supported" => [
                "video/x-msvideo",
                "video/mp4",
                "video/x-m4v",
                "video/x-flv",
                "video/quicktime",
                "video/x-ms-wmv",
                "video/mpeg",
                "video/3gpp",
                "video/x-ms-asf",
                "application/octet-stream",
            ],

            "queue" => [
                "enabled" => true,
            ],
        ]);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.disks', [
            'default' => [
                'driver' => 'local',
                'root'   => __DIR__.'/tmp',
            ],
        ]);
    }

    protected function flushTestStorageDisks () {
        $disk = Storage::disk(config('viddler.disk'));

        //Delete files on disk
        $files = $disk->files();
        foreach ($files as $f) {
            if($f !== '.gitignore' && $f !== '.gitkeep'){
                $disk->delete($f);
            }
        }

        //Delete directories on disk
        $directories = $disk->directories();
        foreach ($directories as $dir) {
            $disk->deleteDirectory($dir);
        }

    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}