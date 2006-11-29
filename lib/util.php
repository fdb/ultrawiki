<?php

//// Utility functions ////

function util_read_file($fname) {
    $handle = @fopen($fname, "r");    
    if (!$handle) return null;
    $text = fread($handle, filesize($fname));
    fclose($handle);
    return $text;
}

function util_write_file($fname, $text) {
    $handle = fopen($fname, "w");
    if (!$handle) return;
    fwrite($handle, $text);
    fclose($handle);
}

?>