<?php

class Context {
    var $page;
    var $user;

    function Context($page=false,$user=false) {
        global $gPage, $gUser;
        $this->page = $gPage;
        $this->user = $gUser;
    }
}

/* Controller object */

/* STATE VARIABLES */

/* Sets or gets the content */
function do_content($content=false) {
    global $_do_content;
    if ($content)
        $_do_content = $content;
    return $_do_content;
}

/* Sets or gets the action */
function do_action($action=false) {
    global $_do_action;
    if ($action)
        $_do_action = $action;
    return $_do_action;
}

/* Sets or gets the message */
function do_message($message=false) {
    global $_do_message;
    if ($message)
        $_do_message = $message;
    return $_do_message;
}

/* Appends or gets extra content to body tag */
function do_bodytag($bodytag=false) {
    global $_do_bodytag;
    if ($bodytag)
        $_do_bodytag .= " " . $bodytag;
    return $_do_bodytag;
}

/* Appends or gets extra content to head tag */
function do_headtag($headtag=false) {
    global $_do_headtag;
    if ($headtag)
        $_do_headtag .= $headtag . "\n";
    return $_do_headtag;
}


$_do_var = array();

function do_var($key, $value=null) {
    global $_do_var;
    if (isset($value))
        $_do_var[$key] = $value;
    return $_do_var[$key];
}

function do_page($template) {
    global $_do_content;
    global $CONTENT, $gOutputType, $Theme;
    ob_start();
    do_template($template);
    do_content(ob_get_clean());
    do_template($gOutputType);
}

function do_template($template) {    
    global $Theme;
    $ctx = new Context;
    $fname = $Theme->findTemplate($template);
    if ($fname) {
        include($fname);
    }
}

function do_link($page=false, $display=false, $action=false, $prevpage=false, $accesskey=false) {
    $url = do_href($page, $action, $prevpage);
    if (! $display) {
        $display = name_page2display($page);
    }
    $html = "<a href=\"$url\"";
    if ($accesskey) {
        $html .= " accesskey=\"$accesskey\"";
    }
    $html .= ">$display</a>";
    return $html;    
}

function do_image_link($page=false, $image=false, $alt=false, $action=false, $prevpage=false, $accesskey=false) {
    $display = "<img src=\"$image\"";
    if ($alt) {
      $display .= " alt=\"$alt\"";
    }
    $display .= " />";    
    return do_link($page, $display, $action, $prevpage, $accesskey);
}

function do_homepage() {
  global $gHomepage;
  return $gHomepage;  
}

function do_wiki_name() {
    global $gWikiName;
    return $gWikiName;
}

function do_href($page=false, $action=false, $prevpage=false) {
    global $gPage, $gScript, $gHomepage, $gWikiURL;
    if (!$page)
        $page = $gPage;
    if (is_object($page))
        $page = $page->name;
    if (strcmp($page, $gHomepage) == 0) {
        $link = "$gWikiURL";
    } else {
        $link = "$gWikiURL$page";
    }
    if ($action) {
        $link .= "?$action";
    }
    if ($prevpage) {
        $link .= "&prevpage=$prevpage";
    }
    return $link;
}

function do_wiki_url($suffix="") {
    global $gWikiURL;
    return $gWikiURL . $suffix;
}

function do_theme_url($suffix="") {
    global $gThemeURL;
    return $gThemeURL . $suffix;
}

function do_default_theme_url($suffix="") {
    global $gDefaultThemeURL;
    return $gDefaultThemeURL . $suffix;
}

function do_name() {
    global $gPage;
    return $gPage->name;
}

function do_title() {
    global $gPage;
    return $gPage->display;
}

function do_postvar() {
    return 'edit';
}

function do_getvar($var) {
    if (isset($_GET[$var]))
        return $var;
    return false;
}

function do_title_tag($additional=false) {
    global $gWikiName, $gPage, $gHomepage;
    $title = $gWikiName;
    if ($gPage->name != $gHomepage)
        $title .= " - " . $gPage->display;
    if (!empty($additional))
        $title .= " - " . $additional;
    return "<title>$title</title>";
}

function do_logged_in() {
    return sec_logged_in();
}

function do_can_edit() {
    global $gPage, $gUser;
    return sec_can_edit($gPage, $gUser);
}

function do_can_perm() {
    global $gPage, $gUser;
    return sec_can_perm($gPage, $gUser);
}

function do_can_passwd() {
    global $gUser;
    return sec_can_passwd($gUser);
}

?>