<?php

namespace AmcLab\Baseline\HashGenerators;

use AmcLab\Baseline\Contracts\HashGenerator;
use AmcLab\Baseline\Exceptions\HashGeneratorException;
use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Encryption\Encrypter;
use InvalidArgumentException;

class SodiumHashGenerator implements HashGenerator {

    protected $config;
    protected $keys;

    public function __construct(Repository $configRepository) {
        $this->config = $configRepository->get('baseline.hash-generators.sodium');

        foreach ($this->config as $keyName => $keyValue) {
            if (substr($keyValue, 0, 7) === 'base64:') {
                $keyValue = substr($keyValue, 7);
            }
            $this->keys[$keyName] = base64_decode($keyValue);
        }
    }

    /**
     * Genera una chiave hash da un array di stringhe o da un numero arbitrario di stringhe passate come argument
     *
     * @param string $keyName short | generic
     * @param mixed ...$args
     * @return void
     */
    public function generate(string $keyName, ...$args) :? string {

        if (is_array($args[0] ?? [])) {
            $args = $args[0];
        }

        [$name, $size] = array_slice(explode(':', $keyName) + [null, null], 0, 2);

        // if (!$key = $this->keys[$name] ?? null) {
        //     throw new HashGeneratorException("No application-wide key configured for '$name'");
        // }

        $input = join("\xFE", $args) . "\xDE";

        // genera una chiave con lunghezza specifica (default \SODIUM_CRYPTO_GENERICHASH_BYTES = 32)
        if ($name === 'key') {
            return sodium_crypto_generichash($input, $this->keys['generic'], $size ?? \SODIUM_CRYPTO_GENERICHASH_BYTES);
        }

        // genera una chiave hash breve (per indicizzazione)
        else if ($name === 'uid') {
            return sodium_crypto_shorthash($input, $this->keys['short']);
        }

        else {
            throw new HashGeneratorException("No generator for '$name'");
        }

    }

}
