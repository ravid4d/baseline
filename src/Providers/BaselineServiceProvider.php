<?php
namespace AmcLab\Baseline\Providers;

use AmcLab\Baseline\Contracts\HashGenerator;
use AmcLab\Baseline\Contracts\Pathfinder;
use Illuminate\Support\ServiceProvider;

class BaselineServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes(array(
            __DIR__.'../../config/hash-generator.php' => config_path('hash-generator.php'),
            __DIR__.'../../config/pathfinder.php' => config_path('pathfinder.php'),
        ), 'config');
    }

    public function register()
    {
        $this->app->bind(Pathfinder::class, \AmcLab\Baseline\Pathfinder\Pathfinder::class);
    }

}
