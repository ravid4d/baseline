<?php

namespace AmcLab\Baseline\Traits;

use Illuminate\Contracts\Events\Dispatcher;

trait HasEventsDispatcherTrait {

    protected $events;

    final public function setEventsDispatcher(Dispatcher $events) : self {
        $this->events = $events;
        return $this;
    }

    final public function getEventsDispatcher() : Dispatcher {
        return $this->events;
    }

    final public function dispatch($event, $payload = [], bool $halt = false) {
        if ($this->events) {
            $payload = $payload ? ['with' => $payload]: [];
            return $this->events->dispatch($event, array_merge(['class' => static::class], $payload));
        }
    }

}
