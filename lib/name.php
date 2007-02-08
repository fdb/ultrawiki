<?php

#
# Convert names from page names to display names to file paths.
# Used by index.php and textile.php
# 

function name_from_request() {
    return substr(@$_SERVER['PATH_INFO'], 1);
}

function name_display2page($display) {
    return strtolower(str_replace(' ', '-', $display));
}

function name_page2display($page) {
    return ucwords(str_replace('-', ' ', $page));
}

function name_page2file($page) {
    global $gTextFolder;    
    return $gTextFolder . '/' . $page . ".txt";    
}

function name_page2cache($page) {
    global $gCacheFolder;    
    return $gCacheFolder . '/' . $page . ".html";
}

function name_file2page($file) {
    list($dir, $base, $ext) = pathinfo($file);
    return $base;
}

function name_validate($pname) {
    return true;
}

?>