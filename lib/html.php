<?php
//// HTML template functions ////

function html_header($page, $title=NULL, $bodytag='', $head='') {
    global $gWikiName, $gHomepage;
    $finalTitle = $gWikiName;
    if ($page->name != $gHomepage)
        $finalTitle .= " - " . $page->display;
    if (!empty($title))
        $finalTitle .= " - " . $title;
    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";    
    echo "<html><head>\n";
    echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n";
    echo "<meta http-equiv=\"imagetoolbar\" content=\"no\" />\n";
    echo "<title>$finalTitle</title>\n";
    echo "<link href=\"../stylesheets/default.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
    echo $head;
    echo "</head>\n";
    echo "<body $bodytag><div class=\"b\">\n";
    echo "<div class=\"hd\"><img src=\"../images/logo.gif\" width=\"720\" height=\"130\" alt=\"ChampdAction Logo\" /></div>\n";
    html_headlinks($page);
    echo "<div class=\"c\">\n";
}

function html_error($page, $err) {
    $err = nl2br($err);
    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";    
    echo "<html><head>\n";
    echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n";
    echo "<meta http-equiv=\"imagetoolbar\" content=\"no\" />\n";
    echo "<title>$gWikiName$display</title>\n";
    echo "<link href=\"../stylesheets/default.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
    echo "</head>\n";
    echo "<body $bodytag><div class=\"b\">\n";
    echo "<div class=\"hd\"><img src=\"../images/logo.gif\" width=\"720\" height=\"130\" alt=\"ChampdAction Logo\" /></div>\n";
    echo "<div class=\"c\">\n";
    echo "<p class=\"err\">$err</p>";
    html_footer($page);
}

function html_headlinks($page) {
    global $gUser, $gSelf, $gScript;
    echo "<ul class=\"hl\">\n";
    html_headlink($page, "Home");
    html_headlink($page, "Biography");
    html_headlink($page, "Calendar");
    html_headlink($page, "Composers");
    html_headlink($page, "Musicians");
    html_headlink($page, "Projects");
    html_headlink($page, "Studio");
    html_headlink($page, "Reviews");
    html_headlink($page, "Links");
    html_headlink($page, "Contact");
    echo "<li>&nbsp;</li>";
    if (isset($gUser)) {
        echo "<li><a href=\"$gSelf?logout\">Logout</a></li>\n";
        if (sec_can_edit($page, $gUser))
            echo "<li><a href=\"$gSelf?edit\" accesskey=\"e\">Edit</a></li>\n";
        if (sec_can_layout($page, $gUser))
            echo "<li><a href=\"$gSelf?layout\" accesskey=\"l\">Layout</a></li>\n";
        if (sec_can_perm($page, $gUser))
            echo "<li><a href=\"$gSelf?perm\" accesskey=\"p\">Permissions</a></li>\n";
        if (sec_can_passwd($gUser))
            echo "<li><a href=\"$gScript/Home?passwd\">Passwords</a></li>\n";
        if (sec_is_admin($gUser))
            echo "<li><a href=\"/db/contacts/contacts.php\">Contacts</a></li>\n";
        if (sec_can_read('AR', $gUser)) // 'AR' Page is an exception for Champ d'Action
            html_headlink($page, "AR");
    } else {
        echo "<li><a href=\"$gSelf?login\">Login</a></li>\n";
    }
    echo "</ul>\n";
}

function html_headlink($page, $name) {
    if ($page->childOf($name)) {
        echo "<div class=\"hldot\"><img src=\"../images/reddot.gif\" alt=\"\" /></div>";
    }
    echo "<li><a href=\"" . name_linkto($name). "\">$name</a></li>\n";
}


function html_footer($page) {
    echo "<p class=\"ft\">Copyright &copy; 2004 ChampdAction</p>\n";
    echo "</div>\n";
    /*
    echo "<form method=get action=\"$gSelf\"><input type=\"text\" name=\"q\" size=\"31\" maxlength=\"2048\" value=\"\" accesskey=\"f\"> <input type=\"submit\" name=\"g\" value=\"Search\"></form>\n";
    if (isset($gUser)) {
        echo "Logged in as $gUser. (<a href=\"$gSelf?logout=1\">Logout</a>)<br />\n";
    } else {
        echo "<a href=\"#\" onclick=\"document.getElementById('lgn').style.display='block'\">L</a><div id=\"lgn\" style=\"display:none\"><form method=\"post\" action="$gSelf">user:&nbsp;<input type=\"text\" name=\"username\" size=\"20\" maxlength=\"2048\" value=\"\" accesskey=\"l\">&nbsp;password:&nbsp;<input type=\"password\" name=\"password\" size=\"20\" maxlength=\"2048\" value=\"\">&nbsp;<input type=\"submit\" name=\"s\" VALUE=\"Login\"></form></div>\n";
    }
    echo "<a href="$gScript/Home\" accesskey=\"h\">Home</a> | <a href="$gSelf?edit" accesskey=\"e\">Edit</a> | <a href="$gSelf?backlinks" accesskey=\"b\">Backlinks</a> | <a href="$gSelf?all\" accesskey=\"a\">All Pages</a>\n";
    echo "</div>\n";
    */
    echo "</div></body></html>";
}

function html_create($page) {
    html_edit($page, 'Create page', $postvar='__create');
}

function html_edit($page, $title='Edit page', $postvar='__edit') {
    global $gSelf, $gRequest;
    html_header($page, $title, 'onload="document.f.text.focus()"', "<script type=\"text/javascript\" src=\"/edit.js\"></script>");
    echo "<h1>$page->display &#8212; $title</h1>";
    echo "<a href=\"#\" onclick=\"insertMediaElement();\">Media</a>\n";
    echo "<form method=\"post\" action=\"" . name_linkto($page) . "\" class=\"sf\" name=\"f\"><textarea cols=\"70\" rows=\"25\" name=\"text\" id=\"text\" onselect=\"storeCaret(this)\" onclick=\"storeCaret(this)\" onkeyup=\"storeCaret(this)\">$page->content</textarea><br /><input type=\"submit\" value=\"Save\" accesskey=\"s\" /><input type=\"hidden\" name=\"$postvar\" />";
    if (isset($_GET['prevpage'])) {
        echo "<input type=\"hidden\" name=\"prevpage\" value=\"{$_GET['prevpage']}\" />";
    }
    echo "</form>";
    html_footer($page);
}

function html_perm($page) {
    global $gUser;
    list($creatormode, $groupmode, $worldmode) = $page->mode;
    html_header($page);
    echo "<h1>$page->display &#8212; Permissions</h1>\n";
    echo "<form method=\"post\" action=\"" . name_linkto($page) . "\" class=\"sf\" name=\"f\">\n";
    echo "<table>\n";
    echo "<tr><td align=\"right\">Creator:</td><td>\n";
    if (sec_is_admin($gUser)) {
        $users = sec_all_users();
        echo "<select name=\"creator\">\n";
        foreach($users as $aUser) {
            if ($page->creator == $aUser) {
                echo "    <option value=\"$aUser\" selected=\"selected\">$aUser</option>\n";
            } else {
                echo "    <option value=\"$aUser\">$aUser</option>\n";
            }
        }
        echo "</select>\n";
    } else {
        echo "$creator<input type=\"hidden\" name=\"creator\" value=\"$creator\" />\n";
    }
    echo "</td></tr>\n";
    echo "<tr><td align=\"right\">Access:</td>\n<td><select name=\"creatormode\">\n";
    html_perm_options($creatormode, False);
    echo "</select></td></tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Group:</td>\n<td><select name=\"group\">\n";
    if (sec_is_admin($gUser)) {
        $groups = sec_all_groups($gUser);
    } else {
        $groups = sec_groups($gUser);
    }
    foreach($groups as $aGroup) {
        if ($page->group == $aGroup) {
            echo "    <option value=\"$aGroup\" selected=\"selected\">$aGroup</option>\n";
        } else {
            echo "    <option value=\"$aGroup\">$aGroup</option>\n";
        }
    }
    echo "</select></td></tr>\n";
    echo "<tr><td align=\"right\">Access:</td><td><select name=\"groupmode\">\n";
    html_perm_options($groupmode);
    echo "</select></td></tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Everyone:</td>\n<td><select name=\"worldmode\">\n";
    html_perm_options($worldmode);
    echo "</select></td></tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    echo "<tr><td align=\"right\">Parent:</td>\n<td><input type=\"text\" name=\"parent\" size=\"20\" value=\"$page->parentPage\" /></td></tr>\n";
    echo "</table>\n";
    echo "<br />\n<input type=\"submit\" value=\"Save\" accesskey=\"s\" />\n<input type=\"hidden\" name=\"__perm\" /></form>\n";
    html_footer($page);
}

function html_perm_options($perm, $show_noaccess=True) {
    if ($perm == '7') {
       echo "<option value=\"7\" selected=\"selected\">Read, Edit & Layout</option>\n";
    } else {
       echo "<option value=\"7\">Read, Edit &amp; Layout</option>\n";
    }
    if ($perm == '6') {
       echo "<option value=\"6\" selected=\"selected\">Read & Edit</option>\n";
    } else {
       echo "<option value=\"6\">Read &amp; Edit</option>\n";
    }
   if ($perm == '4') {
       echo "<option value=\"4\" selected=\"selected\">Read only</option>\n";
    } else {
       echo "<option value=\"4\">Read only</option>\n";
    }
    if ($show_noaccess) {
       if ($perm == '0') {
           echo "<option value=\"0\" selected=\"selected\">No access</option>\n";
        } else {
           echo "<option value=\"0\">No access</option>\n";
        }
    }
}

function html_passwd($page, $users, $groups) {
    html_header($page, 'Passwords', 'onload="document.f.text.focus()"');
    echo "<h1>User Management</h1>\n";
    echo "<form method=\"post\" action=\"" . name_linkto($page) . "\" class=\"sf\" name=\"f\">\n";
    echo "<textarea cols=\"70\" rows=\"12\" name=\"users\">$users</textarea><br />\n";
    echo "<textarea cols=\"70\" rows=\"12\" name=groups >$groups</textarea><br />\n";
    echo "<input type=\"submit\" value=\"Save\" accesskey=\"s\" />\n";
    echo "<input type=\"hidden\" name=\"__passwd\" />\n";
    echo "</form>\n";
    html_footer($page->display);
}

function html_login($page, $msg='', $action='') {
    if (sec_logged_in()) return html_noaccess($page, 'view');

    html_header($page, 'Login', "onload='document.f.username.focus()'");
    echo "<h1>Login</h1>\n";
    echo "<p>$msg</p>\n";

    echo "<form method=\"post\" action=\"" . name_linkto($page, $action) . "\"  name=\"f\">\n";
    echo "<table cellpadding=\"4\" width=\"200\">\n";
    echo "<tr><td>Username:</td><td><input type=\"text\" name=\"username\" size=\"20\" maxlength=\"32\" value=\"\" accesskey=\"l\" /></td></tr>\n";
    echo "<tr><td>Password:</td><td><input type=\"password\" name=\"password\" size=\"20\" maxlength=\"32\" value=\"\" /></td></tr>\n";
    echo "<tr><td></td><td><input type=\"submit\" name=\"s\" value=\"Login\" /><input type=\"hidden\" name=\"__login\" /></td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
    html_footer($page);
}

function html_noaccess($page, $action='view') {
    $display = $page->display;
    html_header($page, "No Access");
    echo "<h1>$display &#8212; No Access</h1>\n";
    echo "<p>You don't have the required priviliges for the requested operation.</p>\n";
    echo "<p>If you are sure that this message is in error, please contact the management.</p>\n";
    echo "<p>If you <em>are</em> the management, please contact the webmaster.</p>\n";
    html_footer($page);
}

function html_show($page) {
    global $gRenderObject;
    $display = $page->display;
    html_header($page);
    if ($page->name != 'Home') // The Home page has no header.
        echo "<h1>$display</h1>\n"; // XXX: Maybe the title should be optional. If the first line of the content is a <h1>, skip it?
    echo $page->render($gRenderObject);
    html_footer($page);
}

function html_empty($page) {
    $prev = $_SESSION['prevpage'];

    $display = $page->display;
    html_header($page);
    echo "<h1>$display</h1>";
    echo "<p>This page is currently empty.</p>";
    echo "<p>If you have the correct permissions you can href=\"" . name_linkto($page, 'create', $prev) . "\">create</a> this page.</a></p>";
    html_footer($page);
}

function html_backlinks($page, $blinks) {
    global $gScript;
    $pname = $page->name;
    $display = $page->display;
    html_header($page, "Pages linking to '<a href=\"$gScript/$pname\">$display</a>':");
    html_links($blinks, 'There are no pages that link to this page.');
    html_footer($page);
}

function html_results($page, $q, $links) {
    html_header($page, "Pages containing '$q':");
    html_links($links, 'No pages found.');
    html_footer($page);
}

function html_allpages($page, $links) {
    html_header($page, "All pages");
    html_links($links, 'No pages available.');
    html_footer($page);
}

function html_links($page, $links, $err='No results.') {
    global $gScript;
    if (count($links) > 0) {
        echo "<ul>\n";
        foreach($links as $link) {
            $displink = name_page2display($link);
            echo "<li><a href=\"$gScript/$link\">$displink</a></li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p><em>$err</em></p>\n";
    }
}
?>