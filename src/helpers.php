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
function arrayVersionCompare(?array $old, ?array $new) : array {
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
                    'changes' => $exists ? $this->{__FUNCTION__}($currentValue, $comparedValue) : [
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
                    'changes' => $this->{__FUNCTION__}($comparedValue, $currentValue)
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
