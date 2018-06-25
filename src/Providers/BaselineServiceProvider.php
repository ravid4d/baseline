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
            __DIR__.'../../config/baseline.php' => config_path('baseline.php'),
        ), 'config');
    }

    public function register()
    {
        $config = $this->app->config['baseline'];

        $this->app->bind(HashGenerator::class, $config['hash-generator']);
        $this->app->bind(Pathfinder::class, \AmcLab\Baseline\Pathfinder\Pathfinder::class);
    }

}
