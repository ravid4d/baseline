<?php

return [

    'hash-generators' => [

        'sodium' => [
            'short' => env('SODIUM_SHORTHASH_KEY'),
            'generic' => env('SODIUM_GENERICHASH_KEY'),
        ],

    ],

    'hash-generator' => \AmcLab\Baseline\HashGenerators\SodiumHashGenerator::class,

    'pathfinder' => [

        'cache-ttl' => 120,

        'chains' => [

            // catena di elementi usati per comporre la resourceId
            'resourceId' => [
                env('APP_PRODUCT_CODE'),
                // a cui viene accodato il codice identificativo del tenant
            ],

            // catena di elementi cifrabili usati per la composizione della chiave di cifratura finale
            'key' => [
                env('KEYMASTER_KEY'),
                env('APP_KEY'),
                env('APP_PRODUCT_CODE'),
                // a cui viene accodato il codice identificativo del tenant
            ],
        ],
    ],
];
