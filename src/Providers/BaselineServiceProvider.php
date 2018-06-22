<?php
namespace AmcLab\Baseline\Providers;

use AmcLab\Baseline\Contracts\HashGenerator;
use AmcLab\Baseline\Contracts\Pathfinder;
use AmcLab\Baseline\Providers\RemoteLoggerServiceProvider;
use Illuminate\Support\ServiceProvider;

class BaselineServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes(array(
            __DIR__.'../../config/baseline.php' => config_path('baseline.php'),
        ), 'config');
    }

    public function register()
    {
        $this->app->bind(Pathfinder::class, \AmcLab\Baseline\Pathfinder\Pathfinder::class);
    }

}
