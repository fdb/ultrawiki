<?php
//// Page handling functions ////

/**
 * Pages in Miniwiki are stored as text files in the gDataFolder.
 * 
 * N A M I N G
 * The filename is the name of the page, with spaces converted to underscores.
 * (This conversion happens in name.php)
 * 
 * M E T A D A T A
 * The first line of the page file stores the metadata as key/value pairs.
 * Key/value pairs are stored as "key=value", with pairs seperated by colons.
 * The current properties are:
 *
 * == Security permissions ==
 * - creator   : the creator of a page (required)
 * - group     : the group this page belongs to (required)
 * - mode      : defines who can edit/read/layout the page. (required)
 *               The mode is a three-digit number. The first digit defines the 
 *               permissions for the creator, the second digit defines the permissions
 *               for the group, and the third digit defines the permissions for
 *               everyone else ("world"). The digits are a sum of the following
 *               values: 4 = read, 2 = edit, 1 = layout, so e.g. the mode 764 defines
 *               that the creator can read/edit/layout the page, people belonging to 
 *               the group can read and edit, but not layout the page, and everyone else
 *               have read-only access. '0' means no access.
 *
 * == Navigation ==
 * - parentPage: the parent page. Miniwiki uses the concept of the parent instead of 
 *               working with folders. The parent is useful for navigational aids
 *               and for inheriting permissions. (required)
 *
 * == Layout ==
 * - layout    : the modii for layout. Options are chaos, structure and order.
 * - bgcolor   : the background color of the page.
 * - bgimage   : the background image of the page.
 */


$gDB = NewADOConnection('mysql');
$gDB->PConnect('localhost','root','','wikidb');

class Page {
    // Naming
    var $name;
    var $filename;
    var $display;
    
    // Security permissions
    var $creator;
    var $group;
    var $mode;

    // Navigation
    var $parentPage;
    
    // Content
    var $content;
    var $cache;
    
    function Page($name=NULL) {
        global $gLayoutOrder;
        // Load names
        $this->name = $name;
        $this->creator = 'none';
        $this->group = 'none';
        $this->mode = 744;
        $this->display = name_page2display($name);
        // Retrieve page
        if (isset($name)) {
            $this->_retrieve();
        }       
    }
    
    function exists() {
        global $gDB;
        $recordSet = $gDB->Execute('select `name` from `page` where `name`=?', array($this->name));
        if ($recordSet->EOF)
            return false;
        return true;
    }
    
    function childOf($otherPage) {
        if (is_string($otherPage)) { // XXX: Can optimize: check string first
            $otherPage = new Page($otherPage);
        }
        if ($this->name == $otherPage->name)
            return True;
        if ($this->parentPage != 'Home' and $this->parentPage == $otherPage->name)
            return True;
        return False;
    }
    
    function render($RenderObject) {
        global $gDB;
        if (empty($this->cache)) {
            $this->cache = $RenderObject->render($this->content);
            $gDB->Execute('update `page` set cache=? where name=?', array($this->cache, $this->name));
        }
        return $this->cache;
    }
    
    /**
     * Inherit permissions from other page
     */
    function inherit($parentPage) {
        if (is_string($parentPage)) {
            $parentPage = new Page($parentPage);
        }
        $this->parentPage = $parentPage->name;
        $this->group = $parentPage->group;
        $this->mode = $parentPage->mode;
    }
    
    function _retrieve() {
        global $gDB;
        $recordSet = $gDB->GetRow('select `name`, `content`, `cache`, `creator`, `group`, `mode`, `parent` from `page` where `name`=?', array($this->name));
        if ($recordSet) {
            $this->name = $recordSet[0];
            $this->content = $recordSet[1];
            $this->cache = $recordSet[2];
            $this->creator = $recordSet[3];
            $this->group = $recordSet[4];
            $this->mode = $recordSet[5];
            $this->parentPage = $recordSet[6];
        }
    }        
    
    function store() {
        global $gDB;
        if ($this->exists()) {
            $vals = array($this->content, $this->creator, $this->group, $this->mode, $this->parentPage, $this->name);
            $gDB->Execute('update `page` set `content`=?, `cache`=null, `creator`=?, `group`=?, `mode`=?, `parent`=? where `name`=?', $vals);
            $this->cache=null;
            echo $gDB->ErrorMsg();
        } else {
            $vals = array($this->name, $this->content, $this->cache, $this->creator, $this->group, $this->mode, $this->parentPage);
            $gDB->Execute('insert into `page` (`name`,`content`,`cache`,`creator`,`group`,`mode`,`parent`) values(?,?,?,?,?,?,?)', $vals);
            echo $gDB->ErrorMsg();
        }
    }    
}


function page_backlinks($page) {
    $blinks = array();
    $display = $page->display;
    global $gDataFolder;
    $cmd = "cd $gDataFolder;grep -l \"\\[\\[$display\\]\\]\" *.txt";
	exec($cmd, $fnames);
	foreach($fnames as $fname) {
	    $pname = name_file2page($fname);
	    array_push($blinks,$pname);
	} 
	return $blinks;
}

function page_search($q) {
    $links = array();
    global $gDataFolder;
    $cmd = "cd $gDataFolder;grep -li \"$q\" *.txt";
	exec($cmd, $fnames);
	foreach($fnames as $fname) {
	    $pname = name_file2page($fname);
	    array_push($links,$pname);
	} 
	return $links;
}

function page_allpages() {
    $links = array();
    global $gDataFolder;
    $cmd = "cd $gDataFolder;ls -1 *.txt";
	exec($cmd, $fnames);
	foreach($fnames as $fname) {
	    $pname = name_file2page($fname);
	    array_push($links,$pname);
	} 
	return $links;
}
?>