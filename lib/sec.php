<?php
//// Security functions ////

function sec_read_users() {
    global $gUserFile;
    return util_read_file($gUserFile);
}

function sec_store_users($users) {
    global $gUserFile;
    return util_write_file($gUserFile, $users);
}

function sec_read_groups() {
    global $gGroupFile;
    return util_read_file($gGroupFile);
}

function sec_store_groups($groups) {
    global $gGroupFile;
    return util_write_file($gGroupFile, $groups);
}

function sec_can_read($page, $user) {
    global $gReadFlag;
    return sec_has_perm($page, $user, $gReadFlag);
}

function sec_can_create($page, $user) {
    // If I can edit the page I came from, I can create the current one as well.
    @$prevpage = $_SESSION['prevpage'];
    return sec_can_edit($prevpage, $user);
}

function sec_can_edit($page, $user) {
    global $gEditFlag;
    return sec_has_perm($page, $user, $gEditFlag);
}

function sec_can_layout($page, $user) {
    global $gLayoutFlag;
    return sec_has_perm($page, $user, $gLayoutFlag);
}

function sec_can_perm($page, $user) {
    if (sec_is_creator($page, $user) or sec_is_admin($user))
        return true;
    return false;
}

function sec_can_passwd($user) {
    if (sec_is_admin($user))
        return true;
    return false;
}

function sec_modes($page) {
    $creatormode = (int)$page->mode[0];
    $groupmode = (int)$page->mode[1];
    $worldmode = (int)$page->mode[2];
    return array($creatormode, $groupmode, $worldmode);
}

function sec_has_perm($page, $user, $perm) {
    $creatormode = (int)$page->mode[0];
    $groupmode = (int)$page->mode[1];
    $worldmode = (int)$page->mode[2];
    
    // 1. World check
    if (($worldmode & $perm) == $perm)
        return true;

    // 2. Group check    
    if (sec_in_group($user, $page->group))
        if (($groupmode & $perm) == $perm)
            return true;

    // 3. User check
    if ($page->creator == $user)
        if (($creatormode & $perm) == $perm)
            return true;

    // 4. Admin check
    if (sec_in_group($user, 'admin'))
        return true;

    return false;
}

function sec_in_group($user, $group) {
    global $gGroupFile;
    if (!isset($user)) return false;
    $handle = @fopen($gGroupFile, "r");
    while (list($aGroup, $aUsers) = fgetcsv($handle, 1024, ':')) { // Go through all groups
        if ($aGroup == $group) { // Correct group?
            $aUsers = explode(',', $aUsers);
            foreach ($aUsers as $aUser ) { // Go through all users of this group
                if (strcasecmp($aUser, $user) == 0) { // User in group?
                    fclose($handle);
                    return true;
                }
            }
        }
    }
    fclose($handle);
    return false;

}

function sec_all_users() {
    global $gUserFile;
    $myusers = array();
    $handle = @fopen($gUserFile, "r");
    while (list($aUser, $aPasswd) = fgetcsv($handle, 1024, ':')) { // Go through all users
        $myusers[] = $aUser;
    }
    return $myusers;
}

function sec_groups($user) {
    global $gGroupFile;
    $mygroups = array();
    $handle = @fopen($gGroupFile, "r");
    while (list($aGroup, $aUsers) = fgetcsv($handle, 1024, ':')) { // Go through all groups
        $aUsers = explode(',', $aUsers);
        foreach ($aUsers as $aUser) { // Go through all users of this group
            if (strcasecmp($aUser, $user) == 0) { // User in group?
                $mygroups[] = $aGroup;
            }
        }
    }
    fclose($handle);
    return $mygroups;
}

function sec_all_groups($user) {
    global $gGroupFile;
    $mygroups = array();
    $handle = @fopen($gGroupFile, "r");
    while (list($aGroup, $aUsers) = fgetcsv($handle, 1024, ':')) { // Go through all groups
        $mygroups[] = $aGroup;
    }
    fclose($handle);
    return $mygroups;
}

function sec_is_creator($page, $user) {
    return strcasecmp($page->creator, $user) == 0;
}

function sec_is_admin($user) {
    return sec_in_group($user, 'admin');
}

function sec_validate_user($user, $passwd) {
    global $gUserFile;
    $handle = @fopen($gUserFile, "r");
    if (!$handle) return true;
    while ($line = fgetcsv($handle, 1024, ':')) {
        $aUser = $line[0];
        $aPasswd = $line[1];
        if (strcasecmp($user, $aUser) == 0 and strcmp($passwd, $aPasswd) == 0)
            return true;
    }
    fclose($handle);
    return false;
}

function sec_login($user) {
    global $gUser;
    $gUser = $_SESSION['user'] = $user;
}

function sec_logout() {
    global $gUser;
    $gUser = $_SESSION['user'] = NULL;
}

function sec_logged_in() {
    return isset($_SESSION['user']);
}
?>