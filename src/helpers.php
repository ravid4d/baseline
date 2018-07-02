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

