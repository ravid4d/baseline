<?php

namespace AmcLab\Baseline\HashGenerators;

use AmcLab\Baseline\Contracts\HashGenerators\SodiumHashGenerator as Contract;
use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Encryption\Encrypter;
use InvalidArgumentException;

class SodiumHashGenerator implements Contract {

    protected $config;
    protected $keys;

    public function __construct(Repository $configRepository) {
        $this->config = $configRepository->get('sodium-hash-generator');

        foreach ($this->config as $keyName => $keyValue) {
            if (substr($keyValue, 0, 7) === 'base64:') {
                $keyValue = substr($keyValue, 7);
            }
            $this->keys[$keyName] = base64_decode($keyValue);
        }
    }

    /**
     * Genera una chiave hash per un array variabile di stringhe
     *
     * @param string $keyName short | generic
     * @param mixed ...$args
     * @return void
     */
    public function generate(string $keyName, ...$args) :? string {

        if (is_array($args[0] ?? [])) {
            $args = $args[0];
        }

        if ($key = $this->keys[strtolower($keyName)] ?? null) {
            $function = strlen($key) === 16 ? 'sodium_crypto_shorthash' : 'sodium_crypto_generichash';
            $params = strlen($key) === 16 ? [$key] : [$key, 16];
            return bin2hex($function(implode("\xFE", $args) . "\xDE", ...$params));
        }

        throw new InvalidArgumentException("No generator for '$keyName'");

    }

    /**
     * Metodo magic per instradare correttamente le chiamate verso $this->generate
     * con una sintassi semplificata (es.: $this->generateShortKey($str) )
     *
     * @param string $name
     * @param array $args
     * @return void
     */
    public function __call(string $name, array $args) :? string {

        $recent = null;

        if (substr($name, 0, 8) === "generate" && substr($name, -3) === "Key") {

            $keyName = strtolower(substr($name, 8, -3));

            try {
                return $this->generate($keyName, $args);
            }

            catch(InvalidArgumentException $e) {
                // ...viene smaltita sotto
                $recent = $e;
            }

            catch(Exception $e) {
                throw $e;
            }


        }
        throw new BadMethodCallException("Invalid method ".__CLASS__."->$name() called", 0, $recent);

    }

}
