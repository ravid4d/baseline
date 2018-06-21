<?php

namespace AmcLab\Baseline\Pathfinder;

use AmcLab\Baseline\Contracts\HashGenerator;
use AmcLab\Baseline\Contracts\Pathfinder as Contract;
use AmcLab\Baseline\Exceptions\PathfinderException;
use Illuminate\Contracts\Config\Repository;

class Pathfinder implements Contract {

    protected $config;
    protected $hashGenerator;

    public function __construct(Repository $configRepository, HashGenerator $hashGenerator) {
        $this->config = $configRepository->get('pathfinder');
        $this->hashGenerator = $hashGenerator;
    }

    public function for(array $breadcrumbs = []) {

        if (!count($breadcrumbs)) {
            throw new PathfinderException('No path to follow');
        }

        if (is_array($breadcrumbs[0])) {
            $breadcrumbs = array_shift($breadcrumbs);
        }

        return [

            // originale
            'breadcrumbs' => $breadcrumbs,

            // versione normalizzata (senza caratteri non alfanumerici)
            'normalized' => $normalized = array_map(function($v){
                return trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $v), '_');
            }, $breadcrumbs),

            // costruisce la catena di pezzi per comporre il resourceId
            'resourceId' => $resourceId = $this->mergeChain('resourceId', $normalized),

            // genera un identificatore univoco per la risorsa
            'uid' => bin2hex($this->hashGenerator->generate('short', $resourceId)),

            // genera la chiave di cifratura unica per l'applicazione e per la risorsa
            'uniqueKey' => $uniqueKey = $this->mergeChain('uniqueKey', $resourceId, 'generic'),

        ];

    }

    /**
     * Helper per unire un array dall'indice 'chains' con un secondo array
     *
     * @param string $name
     * @param array $breadcrumbs
     * @return void
     */
    protected function mergeChain(string $name, array $breadcrumbs, $generator = null) {

        $chain = $this->config['chains'][$name];
        $merged = array_merge($chain, $breadcrumbs);
        return $generator ? $this->hashGenerator->generate($generator, $merged) : $merged;

    }
}