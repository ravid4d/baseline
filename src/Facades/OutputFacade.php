<?php

namespace AmcLab\Baseline\Facades;

use Illuminate\Support\Facades\Facade;

class OutputFacade extends Facade {

    protected static function getFacadeAccessor() {
        return 'console.output';
    }

}
