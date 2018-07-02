<?php

namespace AmcLab\Baseline\Pathfinder;

use AmcLab\Baseline\Contracts\HashGenerator;
use AmcLab\Baseline\Contracts\Pathfinder as Contract;
use AmcLab\Baseline\Exceptions\PathfinderException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository;

class Pathfinder implements Contract {

    protected $config;
    protected $hashGenerator;
    protected $cache;

    public function __construct(Repository $configRepository, HashGenerator $hashGenerator, CacheRepository $cache) {
        $this->config = $configRepository->get('baseline.pathfinder');
        $this->hashGenerator = $hashGenerator;
        $this->cache = $cache;
    }

    public function for(...$breadcrumbs) {

        if (!count($breadcrumbs)) {
            throw new PathfinderException('No path to follow');
        }

        if (is_array($breadcrumbs[0])) {
            $breadcrumbs = array_shift($breadcrumbs);
        }

        $cacheUid = 'pathfinder' . md5(json_encode($breadcrumbs));

        // determina se lo store implementa il tagging ed eventualmente lo usa
        $cache = $this->cache->getStore() instanceof \Illuminate\Cache\TaggableStore
            ? $this->cache->tags(['pathfinder']) : $this->cache;

        return $cache->remember($cacheUid, $this->config['cache-ttl'] ?? 120, function() use ($breadcrumbs) {

            return [

                // originale
                'breadcrumbs' => $breadcrumbs,

                // versione normalizzata (senza caratteri non alfanumerici)
                'normalized' => $normalized = array_map(function($v){
                    return trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $v), '_');
                }, $breadcrumbs),

                // costruisce la catena di pezzi per comporre il resourceId
                'resourceId' => $resourceId = $this->mergeChain('resourceId', $normalized),

                // ...sistemare...
                'linkableResourceId' => $linkableResourceId = join('_', $this->mergeChain('resourceId', $normalized, true)),

                // genera un identificatore univoco per la risorsa (non replicabile fuori dall'app)
                'uniqueIdentifier' => bin2hex($this->hashGenerator->generate('uid', $resourceId)),

                // genera una chiave AES-256-CBC unica (non replicabile fuori dall'app) per la risorsa
                'key:AES-256-CBC' => $uniqueKey = $this->hashGenerator->generate('key:32', $this->mergeChain('key', $resourceId)),

            ];

        });

    }

    /**
     * Helper per unire un array dall'indice 'chains' con un secondo array
     *
     * @param string $name
     * @param array $breadcrumbs
     * @return void
     */
    protected function mergeChain(string $name, array $breadcrumbs, bool $skipType = false) {

        $chain = $this->config['chains'][$name];

        if ($skipType && in_array($breadcrumbs[0] ?? null, ['tenant','database-server'])) {
            unset($breadcrumbs[0]);
            $breadcrumbs = array_values($breadcrumbs);
        }

        return array_merge($chain, $breadcrumbs);

    }
}
