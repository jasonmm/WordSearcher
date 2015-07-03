<?php

$VERSION_STRING = "v0.8b";
$DIRECTIONS = array(
    array(-1, 0, 1, 1, 1, 0, -1, -1),            // x
    array(-1, -1, -1, 0, 1, 1, 1, 0)            // y
);
$MAX_PLACEMENT_TRIES = 50000;

function cmp_alpha($a, $b) {
    return (strcasecmp($a, $b));
}

function cmp_strlen($a, $b) {
    $aLen = strlen($a);
    $bLen = strlen($b);
    if( $aLen < $bLen ) {
        return (-1);
    }
    if( $bLen < $aLen ) {
        return (1);
    }

    return (0);
}


