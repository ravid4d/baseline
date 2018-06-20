<?php
namespace AmcLab\KeymasterStore\Providers;

use Illuminate\Support\ServiceProvider;

class BaselineServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes(array(
            __DIR__.'../../config/sodium-hash-generator.php' => config_path('sodium-hash-generator.php'),
        ), 'config');
    }

    public function register()
    {
        //
    }

}
