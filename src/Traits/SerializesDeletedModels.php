<?php

namespace AmcLab\Baseline\Traits;

use Illuminate\Contracts\Database\ModelIdentifier;

trait SerializesDeletedModels
{
    /*
        come usare questo trait:

        nell'evento/listener/job che deve serializzare/deserializzare il model PER RIFERIMENTO,
        ossia usando il trait SerializesModels, aggiungere il trait corrente:

        use ..., ..., ... , SerializesModels, SerializesDeletedModels {
            SerializesDeletedModels::getRestoredPropertyValue insteadof SerializesModels;
        }

        in questo modo, se il model trovato in fase di deserializzazione non è istanziabile - sia
        perché SoftDeleted, sia perché ForceDeleted - l'operazione viene portata a termine
        restituendo comunque un'istanza del model o null.

    */
    protected function getRestoredPropertyValue($value)
    {
        if (!$value instanceof ModelIdentifier) {
            return $value;
        }

        if (is_array($value->id)) {
            return $this->restoreCollection($value);
        }

        $instance = new $value->class;
        $query = $instance->newQuery()->useWritePdo();

        if (property_exists($instance, 'forceDeleting')) {
            return $query->withTrashed()->find($value->id);
        }

        //return $query->findOrFail($value->id);
        return $query->find($value->id);
    }
}
