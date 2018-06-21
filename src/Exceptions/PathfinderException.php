<?php

namespace AmcLab\Baseline\Exceptions;

use RuntimeException;

class PathfinderException extends RuntimeException {

    public function __construct($message = null, $code = null, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
