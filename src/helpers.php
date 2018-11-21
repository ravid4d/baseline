<?php

/**
 * Versione ricorsiva di array_map
 *
 * @param callable $callback
 * @param array ...$array
 * @return array
 */
function array_map_recursive(callable $callback, array ...$array) : array {
    $out = [];
    foreach ($array as $entry) {
        foreach ($entry as $key => $value) {
            $out[$key] = (is_array($value)) ? (__FUNCTION__)($callback, $value) : $callback($value);
        }
    }
    return $out;
}

/**
 * Effettua un confronto chiave per chiave di due versioni dello stesso array, restituendo
 * tutte le differenze.
 *
 * @param array|null $old
 * @param array|null $new
 * @return array
 */
function array_versions_compare(?array $old, ?array $new) : array {
    $diffs = [];
    $checked = [];

    // confronta il vecchio con il nuovo
    $current = $old;
    $compared = $new;
    if (!is_null($current)) {
        foreach($current as $key => $currentValue) {
            $exists = array_key_exists($key, $compared ?? []);
            $comparedValue = $exists ? $compared[$key] : null;
            if (is_array($currentValue) && count($currentValue)) {
                $diffs[] = [
                    'subAttribute' => $key,
                    'subEvent' => $exists ? 'updated' : 'deleted',
                    'changes' => $exists ? call_user_func(__FUNCTION__, $currentValue, $comparedValue) : [
                        'oldValue' => $currentValue,
                        'newValue' => null,
                    ]
                ];
            }
            else {
                if ($currentValue !== $comparedValue) {
                    $diffs[] = [
                        'subAttribute' => $key,
                        'subEvent' => $exists ? 'updated' : 'deleted',
                        'changes' => [
                            'oldValue' => $currentValue,
                            'newValue' => $comparedValue,
                        ]
                    ];
                }

                $checked[] = $key;
            }
        }
    }

    // confronta il nuovo con il vecchio
    $current = $new;
    $compared = $old;
    if (!is_null($current)) {

        foreach($current as $key => $currentValue) {
            $exists = array_key_exists($key, $compared ?? []);
            $comparedValue = $exists ? $compared[$key] : null;
            if (is_array($currentValue) && count($currentValue)) {
                $diffs[] = [
                    'subAttribute' => $key,
                    'subEvent' => $exists ? 'updated' : 'created',
                    'changes' => call_user_func(__FUNCTION__, $comparedValue, $currentValue),
                ];
            }
            else {

                // ...e verifica se esistono nuove proprietà che nel vecchio non esistevano
                if (!in_array($key, $checked)){
                    $diffs[] = [
                        'subAttribute' => $key,
                        'subEvent' => 'created',
                        'changes' => [
                            'oldValue' => null,
                            'newValue' => $currentValue,
                        ]
                    ];
                }
            }
        }
    }

    // elimina indici duplicati
    $diffs = array_intersect_key($diffs, array_unique(array_map(function($v) {
        return json_encode($v);
    }, $diffs)));

    // elimina update vuoti
    $diffs = array_filter($diffs, function($v) {
        return !($v['subEvent']==='updated' && (($v['changes'] ?? true)===[]) );
    });

    // restituisce il risultato
    return array_values($diffs);

}

/**
 * Genera un hash breve da una stringa o un array di stringhe usando l'HashGenerator
 * configurato (default: lib_sodium).
 *
 * @param mixed $input
 * @param bool $isMixed Settare a true se l'input non è stringa o array di stringhe (usa serialize())
 * @return string
 */
function create_uid($input, bool $isMixed = false) {
    return bin2hex(app(\AmcLab\Baseline\Contracts\HashGenerator::class)->generate('uid', $isMixed ? serialize($input) : $input));
}

function running_in_console() {
    return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
}

function console_login(\Illuminate\Contracts\Auth\Authenticatable $user) {
    $logged = \Auth::guard()->getProvider()->retrieveById($user->id);
    \Auth::guard()->setUser($user);
    return;
}

function console_logout() {
    \Auth::guard()->setUser(new \Illuminate\Foundation\Auth\User);
    return;
}

function restrict_number($value, $firstBoundary = -INF, $lastBoundary = INF) {
    $min = min($firstBoundary, $lastBoundary);
    $max = max($firstBoundary, $lastBoundary);
    return min(max($value, $min), $max);
}
