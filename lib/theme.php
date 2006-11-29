<?php
class Theme {

    function Theme($name = 'default') {
        global $gThemesFolder;
        $this->_name = $name;
        $this->_path = $gThemesFolder . $name . '/';
        
        if ($name != 'default')
            $this->_defaultTheme = new Theme;
    }
    
    function out($template) {
        $fname = $this->findTemplate($template);
        if ($fname)
            include($fname);
    }

    function findTemplate($name) {
        return $this->_findFile("templates/$name.tmpl");
    }

    function _findFile($fname) {
        // Find the file in the current theme folder
        if (file_exists($this->_path . $fname))
            return $this->_path . $fname;

        // Find the file in the default theme folder
        if (file_exists($this->_defaultTheme->_path . $fname))
            return $this->_defaultTheme->_path . $fname;

        log_err("File " . $fname . " could not be found.");
        return false;
    }
}

function theme_load($name) {
    global $gThemesFolder;
    $themeFolder = $gThemesFolder . $name;
    if (is_dir($themeFolder)) {        
        if (file_exists($themeFolder . '/themeinfo.php')) {
            include($themeFolder . '/themeinfo.php');
        } else {
            log_err("themeinfo.php not found for theme $name.");
        }
    } else {
        log_err("Theme folder $themeFolder not found.");
    }
    return false;
}

?>