<?php

namespace AmcLab\Baseline\HashGenerators;

use AmcLab\Baseline\Contracts\HashGenerators\SodiumHashGenerator as Contract;
use AmcLab\Baseline\Exceptions\HashGeneratorException;
use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Encryption\Encrypter;
use InvalidArgumentException;

class SodiumHashGenerator implements Contract {

    protected $config;
    protected $keys;

    public function __construct(Repository $configRepository) {
        $this->config = $configRepository->get('hash-generator.sodium');

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

        $requested = explode(':', $keyName);
        $name = $requested[0];
        $size = $requested[1] ?? null;

        if (!$key = $this->keys[$name]) {
            throw new HashGeneratorException("No application-wide key configured for '$name'");
        }

        $input = join("\xFE", $args) . "\xDE";

        if ($name === 'generic') {
            return sodium_crypto_generichash($input, $key, $size ?? SODIUM_CRYPTO_GENERICHASH_BYTES);
        }

        else if ($name === 'short') {
            return sodium_crypto_shorthash($input, $key);
        }

        else {
            throw new HashGeneratorException("No generator for '$name'");
        }

    }

}
