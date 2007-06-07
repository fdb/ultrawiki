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
    
    function Page($name=NULL) {
        global $gLayoutOrder;
        // Load names
        $this->name = $name;
        $this->filename = name_page2file($name);
        $this->cachename = name_page2cache($name);
        $this->display = name_page2display($name);
        // Load defaults
        $this->group = 'admin';
        $this->mode = '764';
        // Retrieve page
        if (file_exists($this->filename)) {
            $this->_retrieve();
        }       
    }
    
    function exists() {
        return file_exists($this->filename);
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
    
    function render() {
        if ($this->_cacheExists()) {
            return $this->_cacheRetrieve();
        } else {
            return $this->_cacheCreate();
        }
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
        $handle = @fopen($this->filename, "r");
        if (!$handle) return null;
        // Read metadata
        $meta = fgets($handle,1024);
        $this->_parseMetadata($meta);
        // Read content
        $this->content = fread($handle, filesize($this->filename));
        fclose($handle);
    }
    
    function _createFolderIfNeeded($fname) {
        $dirname = dirname($fname);
        @mkdir($dirname, 0777, true);
    }
    
    function store() {
        $this->_createFolderIfNeeded($this->filename);
        $handle = @fopen($this->filename, "w");
        if (!$handle) return null;
        // Write metadata
        $meta = $this->_formatMetadata();
        fwrite($handle, $meta . "\n");
        // Write content
        fwrite($handle, $this->content);
        fclose($handle);
        $this->_cacheRemove();
    }
    
    function last_update() {
        return @filemtime($this->filename);
    }
    
    function _formatMetadata() {
        $s = '';
        if (!empty($this->creator)) $s  = ":creator=$this->creator";        
        if (!empty($this->group)) $s .= ":group=$this->group";
        if (!empty($this->mode)) $s .= ":mode=$this->mode";
        if (!empty($this->parentPage)) $s .= ":parent=$this->parentPage";
        $s .= ':';
        return $s;
    }
    
    function _parseMetadata($meta) {
        $kvpairs = explode(':', $meta);
        foreach ($kvpairs as $kv) {
            @list($key, $val) = explode('=', $kv);
            if (!empty($val)) {
                if ($key == 'creator') {
                    $this->creator = $val;
                } else if ($key == 'group') {
                    $this->group = $val;
                } else if ($key == 'mode') {            
                    $this->mode = $val;
                } else if ($key == 'parent') {
                    $this->parentPage = $val;
                }
            }
        }   
    }

    function _cacheRemove() {
        @unlink($this->cachename);
    }

    function _cacheExists() {
        return file_exists($this->cachename);
    }

    function _cacheCreate() {
        $render = Smartypants(Markdown($this->content));
        $this->_createFolderIfNeeded($this->cachename);
        util_write_file($this->cachename, $render);
        return $render;
    }
    
    function _cacheRetrieve() {
        return util_read_file($this->cachename);
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
