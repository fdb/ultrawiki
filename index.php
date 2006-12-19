<?php

#
# UltraWiki is a light-weight, easy and fully customizable content management system.
# It provides good security and elegant media management.
# Copyright 2004-2005 Frederik De Bleser <frederik@pandora.be>
# 
# UltraWiki uses the PHP version of MarkDown by Michel Fortin.
#

require_once('config.inc.php');
require_once('lib/name.php');
require_once('lib/log.php');
require_once('lib/theme.php');
require_once('lib/do.php');
require_once('lib/sec.php');
require_once('lib/page-fs.php');
require_once('lib/embed.php');
#require_once('lib/page-db.php');
#require_once('lib/adodb/adodb.inc.php');
require_once('lib/util.php');
require_once('lib/markdown.php');
require_once('lib/do.php');
session_start();

//// Main loop ////
$gRequest = $_SERVER['REQUEST_URI'];
$gSelf = $_SERVER['PHP_SELF'];
$gScript = $_SERVER['SCRIPT_NAME'];
$gPageName = name_from_request();
if (empty($gPageName))
    $gPageName = $gHomepage;
$gPage = new Page($gPageName);
$gUser = @$_SESSION['user'];
theme_load($gTheme);

if (strtolower($gPageName) != $gPageName) {
    $gPageName = strtolower($gPageName);
    header("Location: " . do_href($gPageName));    
} else if (stripos($gRequest, 'index.php')) {
    $gPageName = strtolower($gPageName);
    header("Location: " . do_href($gPageName));
} else {
    // Logout
    if (isset($_GET['logout'])) { // Logout
        sec_logout();
        header("Location: " . do_href($gPage));
    }
    // Forms
    if (isset($_POST['__login'])) { // Login form
        if (sec_validate_user($_POST['username'], $_POST['password'])) {
            sec_login($_POST['username']);
            header("Location: " . do_href($gPage));
        }
    }
    if (isset($_POST['__create'])) { // Create the page
        $gPage->content = $_POST['text'];
        $gPage->creator = $gUser;
        $gPage->inherit($_SESSION['prevpage']);
        $gPage->store();
        header("Location: " . do_href($gPage));
    }
    if (isset($_POST['__passwd'])) { // Passwords
        $users = $_POST['users'];
        $groups = $_POST['groups'];
        sec_store_users($users);
        sec_store_groups($groups);
        header("Location: " . do_href($gPage));
    }
    if (isset($_POST['__edit'])) { // Update the page
        $gPage->content = $_POST['text'];
        $gPage->store();
        header("Location: " . do_href($gPage));
    }
    if (isset($_POST['__layout'])) { // Update the page
        $gPage->layout = $_POST['layout'];
        $gPage->bgcolor = $_POST['bgcolor'];
        $gPage->bgimage = $_POST['bgimage'];
        $gPage->fgcolor = $_POST['fgcolor'];
        $gPage->store();
        header("Location: " . do_href($gPage));
    }
    if (isset($_POST['__perm'])) { // Change page permissions
        $gPage->creator = $_POST['creator'];
        $gPage->group = $_POST['group'];
        $creatormode = $_POST['creatormode'];
        $groupmode = $_POST['groupmode'];
        $worldmode = $_POST['worldmode'];
        $gPage->mode = $creatormode . $groupmode . $worldmode;
        $gPage->parentPage = $_POST['parent'];
        $gPage->store();
        header("Location: " . do_href($gPage));
    }
    
    // Others
    if (isset($_GET['login'])) { // Show login interface
        do_page('login');
    } elseif (isset($_GET['create'])) { // Show create interface
        if (sec_can_create($gPage, $gUser)) {
            do_page('create');
        } else {
                do_message("The $gPage->name page is currently empty.<br />If you have the correct permissions, please login to create this page.");
                do_action('create');
                do_page('login');
            // html_login($gPage, "The $gPage->name page is currently empty.<br />If you have the correct permissions, please login to create this page.", 'create');
        }
    } elseif (isset($_GET['edit'])) { // Show edit interface
        if (!$gPage->exists()) { // If the page doesn't exist, redirect to the create interface.
            header("Location: $gSelf?create");
        } else {        
            if (sec_can_edit($gPage, $gUser)) {
                do_content($gPage->content);
                do_page('edit');
            } else {
                do_message("The $gPage->name page is only editable with the correct permissions.<br />Please login here.");
                do_action('edit');
                do_page('login');
            }
        }
    } else if (!$gPage->exists()) { // Redirect to create interface
        header("HTTP/1.0 404 Not Found");
        if (sec_can_create($gPage, $gUser)) {
            do_page('create');
        } else {
            do_page('404');
        }
    } elseif (isset($_GET['perm'])) { // Show permission interface
        if (sec_can_perm($gPage, $gUser)) {
            do_page('perm');
        } else {
            do_message("The permissions of the $gPage->name page are only editable by its creator.<br />Please login here.");
            do_action('perm');
            do_page('login');
        }
    } elseif (isset($_GET['passwd'])) { // Show passwd interface
        if (sec_can_passwd($gUser)) {
            $users = sec_read_users();
            $groups = sec_read_groups();
            do_var('users', $users);
            do_var('groups', $groups);
            do_page('passwd');
        } else {
            do_message("The passwords of the website are only editable by the administrator.<br />Please login here.");
            do_action('passwd');
            do_page('login');
        }  
    } elseif (isset($_GET['backlinks'])) { // Show backlinks
        $blinks = page_backlinks($gPage);
        html_backlinks($gPage, $blinks);  
    } elseif (!empty($_GET['q'])) { // Search
        $q = $_GET['q'];
        $links = page_search($q);
        html_results($q, $links);
    } elseif (isset($_GET['all'])) { // All pages
        $links = page_allpages($q);
        html_allpages($links);
    } else { // Show page
        if (sec_can_read($gPage,$gUser)) {
            do_content($gPage->render());
            do_page('browse');
        } else {
            do_message("The $gPage->name page is only viewable with the correct permissions.<br />Please login here.");
            do_page('login');
        }
    }
    if (!isset($_GET['create']) && $gPage->exists()) {
    	$_SESSION['prevpage'] = $gPage;
    }
}
?>