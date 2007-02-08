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
require_once('lib/util.php');
require_once('lib/markdown.php');
require_once('lib/smartypants.php');
require_once('lib/do.php');
require_once('lib/diff.php');
session_start();


function browse() {
    global $gPage, $gUser;
    if (sec_can_read($gPage,$gUser)) {
        do_content($gPage->render());
        do_page('browse');
    } else {
        do_message("The $gPage->name page is only viewable with the correct permissions.<br />Please login here.");
        do_action('login');
        do_page('login');
    }
    // Remember the last page I visited for inheriting the permissions from.
    $_SESSION['prevpage'] = $gPage;
}

function login() {
    global $gPage;
    if (getenv("REQUEST_METHOD") == "POST") {
        if (sec_validate_user($_POST['username'], $_POST['password'])) {
            sec_login($_POST['username']);
            header("Location: " . do_href($gPage));
        } else {
            do_message("Invalid login or password.");
            do_action('login');
            do_page('login');
        }
    } else {
        do_action('login');
        do_page('login');
    }
}

function logout() {
    global $gPage;
    sec_logout();
    header("Location: " . do_href($gPage));
}

function create() {
    global $gPage, $gUser;
    if (sec_can_create($gPage, $gUser)) {
        if ($gPage->exists()) // If the page exists, redirect to the edit page.
            header("Location: " . do_href($gPage, 'edit'));
        if (getenv("REQUEST_METHOD") == "POST") {
            $gPage->content = $_POST['text'];
            $gPage->creator = $gUser;
            $gPage->inherit($_SESSION['prevpage']);
            $gPage->store();
            header("Location: " . do_href($gPage));
        } else {
            do_action('create');
            do_page('create');
        }
    } else {
        do_message("You do not have the required permisions to create this page.");
        do_page('forbidden');
    }
}

function edit() {
    global $gPage, $gUser;
    if (sec_can_edit($gPage, $gUser)) {
        if (!$gPage->exists()) // If the page doesn't exist, redirect to the create page.
            header("Location: " . do_href($gPage, 'create'));
        if (getenv("REQUEST_METHOD") == "POST") {
            if ($gPage->last_update() > $_POST['mtime']) {
                do_content($gPage->content);
                $diff_html = diff_html($_POST['text'], $gPage->content);
                do_secondary_content($diff_html);
                do_action('edit');
                do_page('edit');
            } else {
                $gPage->content = $_POST['text'];
                $gPage->store();
                header("Location: " . do_href($gPage));
            }
        } else {
            do_content($gPage->content);
            do_action('edit');
            do_page('edit');
        }
    } else {
        do_message("You do not have the required permisions to edit this page.");
        do_page('forbidden');
    }
}

function perm() {
    global $gPage, $gUser;
    if (sec_can_perm($gPage, $gUser)) {
        if (getenv("REQUEST_METHOD") == "POST") {
            $gPage->creator = $_POST['creator'];
            $gPage->group = $_POST['group'];
            $creatormode = $_POST['creatormode'];
            $groupmode = $_POST['groupmode'];
            $worldmode = $_POST['worldmode'];
            $gPage->mode = $creatormode . $groupmode . $worldmode;
            $gPage->parentPage = $_POST['parent'];
            $gPage->store();
            header("Location: " . do_href($gPage));
        } else {
            do_action('perm');
            do_page('perm');
        }
    } else {
        do_message("You do not have the correct permissions to edit permissions.");
        do_page('forbidden');
    }
}

function layout() {
    global $gPage, $gUser;
    if (sec_can_layout($gPage, $gUser)) {
        if (getenv("REQUEST_METHOD") == "POST") {
            $gPage->layout = $_POST['layout'];
            $gPage->bgcolor = $_POST['bgcolor'];
            $gPage->bgimage = $_POST['bgimage'];
            $gPage->fgcolor = $_POST['fgcolor'];
            $gPage->store();
            header("Location: " . do_href($gPage));
        } else {
            do_action('layout');
            do_page('layout');
        }
    } else {
        do_message("You do not have the correct permissions to layout this page.");
        do_page('forbidden');
    }
}

function passwd() {
    global $gPage, $gUser;
    if (sec_can_passwd($gUser)) {
        if (getenv("REQUEST_METHOD") == "POST") {
            $users = $_POST['users'];
            $groups = $_POST['groups'];
            sec_store_users($users);
            sec_store_groups($groups);
            header("Location: " . do_href($gPage));
        } else {
            $users = sec_read_users();
            $groups = sec_read_groups();
            do_var('users', $users);
            do_var('groups', $groups);
            do_action('passwd');
            do_page('passwd');
        }
    } else {
        do_message("You do not have the correct permissions to edit passwords.");
        do_page('forbidden');
    }

}

function search() {
    $q = $_GET['q'];
    $links = page_search($q);
    html_results($q, $links);
}

function backlinks() {
    $blinks = page_backlinks($gPage);
    html_backlinks($gPage, $blinks);  
}

function all() {
    $links = page_allpages($q);
    html_allpages($links);
}

function get_page_or_die() {
    global $gPage, $gUser;
    if (!$gPage->exists()) { // Redirect to create interface
        header("HTTP/1.0 404 Not Found");
        if (sec_can_create($gPage, $gUser)) {
            header("Location: " . do_href($gPage, 'create'));
        } else {
            do_page('404');
        }
        exit();
    }
}

//// Main loop ////
$gRequest = $_SERVER['REQUEST_URI'];
$gSelf = $_SERVER['PHP_SELF'];
$gScript = $_SERVER['SCRIPT_NAME'];
$gPageName = name_from_request();
if (empty($gPageName))
    $gPageName = $gHomepage;
$gPage = new Page($gPageName);
$gUser = @$_SESSION['user'];

if (strtolower($gPageName) != $gPageName) {
    $gPageName = strtolower($gPageName);
    header("Location: " . do_href($gPageName));
} else if (stripos($gRequest, 'index.php')) {
    $gPageName = strtolower($gPageName);
    header("Location: " . do_href($gPageName));
} else {
    if (isset($_GET['login'])) {
        login();
    } else if (isset($_GET['logout'])) {
        logout();
    } else if (isset($_GET['create'])) {
        create();
    } else if (isset($_GET['edit'])) {
        get_page_or_die();
        edit();
    } else if (isset($_GET['q'])) {
        search();
    } else if (isset($_GET['perm'])) {
        get_page_or_die();
        perm();
    } else if (isset($_GET['layout'])) {
        get_page_or_die();
        layout();
    } else if (isset($_GET['passwd'])) {
        passwd();
    } else if (isset($_GET['backlinks'])) {
        get_page_or_die();
        backlinks();
    } else if (isset($_GET['all'])) {
        all();
    } else {
        get_page_or_die();
        browse();
    }
}
?>