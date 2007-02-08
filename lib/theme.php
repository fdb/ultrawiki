<?php

function theme_out($template) {
    global $gTheme;
    if (theme_check_if_exists($gTheme)) {
        $fname = theme_find_file($gTheme, $template);
        if ($fname)
            include($fname);
    }
}

function theme_find_file($theme, $template) {
    // Find the file in the current theme folder
    if (file_exists(theme_file($theme, $template)))
        return theme_file($theme, $template);

    // Find the file in the default theme folder
    if (file_exists(theme_file('default', $template)))
        return theme_file('default', $template);

    log_err("Template " . $template . " could not be found.");
    return false;
}

function theme_file($theme, $template=false) {
    global $gThemesFolder;
    if (!$template) {
        return "$gThemesFolder$theme";
    } else {
        return "$gThemesFolder$theme/templates/$template.tmpl";
    }
}

function theme_check_if_exists($theme) {
    if (!is_dir(theme_file($theme))) {        
        log_err("Theme folder $themeFolder not found.");
        return false;
    } else {
        return true;
    }
}

?>