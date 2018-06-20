<?php
namespace AmcLab\KeymasterStore\Providers;

use Illuminate\Support\ServiceProvider;

class BaselineServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes(array(
            __DIR__.'../../config/hash-generator.php' => config_path('hash-generator.php'),
        ), 'config');
    }

    public function register()
    {
        //
    }

}
