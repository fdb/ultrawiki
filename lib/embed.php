<?php

$EMBED_IMAGETYPES = array("jpg", "gif", "png");
$EMBED_SOUNDTYPES = array("mp3");
$EMBED_MOVIETYPES = array("mov", "mpg");

// Embed anything
// localfile is needed for analyzing
function embed_media($url) {
    global $EMBED_IMAGETYPES, $EMBED_SOUNDTYPES, $EMBED_MOVIETYPES;
    list($dir, $base, $ext) = array_values(pathinfo($url));
    
    if (in_array($ext, $EMBED_IMAGETYPES)) {
        return embed_image($url);
    } else if (in_array($ext, $EMBED_SOUNDTYPES)) {
        return embed_sound($url);
    } else if (in_array($ext, $EMBED_MOVIETYPES)) {
        return embed_movie($url);
    } else {
        return "Embed: unknown type";
    }
}

function embed_image($url, $alt='') {
    if (embed_is_local($url)) {        
        list($parmWidth, $parmHeight) = @getimagesize(embed_local_file($url));
        $url = embed_convert_internal_url($url);
    }
    $s  = "<span class=\"media\"><img ";
    $s .= "src=\"$url\" ";
    if (isset($parmWidth)) {
        $s .= "width=\"$parmWidth\" ";
        $s .= "height=\"$parmHeight\" ";
    }
    $s .= "alt=\"$alt\" ";
    $s .= "/></span>\n";
    return $s;
}

function embed_sound($url, $autoplay=False, $controller=True, $loop=True) {
    if (embed_is_local($url)) {        
        $url = embed_convert_internal_url($url);
    }
    $parmAutoplay = ($autoplay ? 'true' : 'false');
    $parmLoop = ($loop ? 'true' : 'false');
    $parmController = ($controller ? 'true' : 'false');

    $s  = "<object ";
    $s .= "classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" ";
    $s .= "codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\">";
	$s .= "<param name=\"src\" value=\"$url\">";
    $s .= "<param name=\"width\" value=\"160\">";
    $s .= "<param name=\"height\" value=\"16\">";
	$s .= "<param name=\"autoplay\" value=\"$parmAutoplay\">";
	$s .= "<param name=\"loop\" value=\"$parmLoop\">";
	$s .= "<param name=\"controller\" value=\"$parmController\">";
	$s .= "<param name=\"type\" value=\"audio/x-mpeg\">";
	$s .= "<param name=\"pluginspage\" value=\"http://www.apple.com/quicktime/download/indext.html\">";
	$s .= "<param name=\"target\" value=\"myself\">";
	$s .= "<embed ";
	$s .= "src=\"$url\" ";
    $s .= "width=\"160\" "; 
    $s .= "height=\"16\" ";
	$s .= "target=\"myself\" ";
	$s .= "autoplay=\"$parmAutoplay\" ";
	$s .= "loop=\"$parmLoop\" ";
	$s .= "controller=\"$parmController\" ";
	$s .= "type=\"audio/x-mpeg\" "; 
	$s .= "pluginspage=\"http://www.apple.com/quicktime/download/indext.html\"";
	$s .= "></embed>";
    $s .= "</object>\n";
    return $s;
}

function embed_movie($url, $autoplay=False, $loop=False, $controller=True) {
    $parmAutoplay = ($autoplay ? 'true' : 'false');
    $parmLoop = ($loop ? 'true' : 'false');
    $parmController = ($controller ? 'true' : 'false');

    if (embed_is_local($url)) {
        $localfile = embed_local_file($url);
        $info = embed_analyze($localfile);
        $parmWidth = $info['video']['resolution_x'];
        $parmHeight = $info['video']['resolution_y'];
        if ($controller)
            $parmHeight += 16;
        $url = embed_convert_internal_url($url);
    }
    
    $s  = "<object ";
    $s .= "classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" ";
    $s .= "codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\">";
	$s .= "<param name=\"src\" value=\"$url\">";
    if (isset($localfile)) {
        $s .= "<param name=\"width\" value=\"$parmWidth\">";
	    $s .= "<param name=\"height\" value=\"$parmHeight\">";
	}
	$s .= "<param name=\"autoplay\" value=\"$parmAutoplay\">";
	$s .= "<param name=\"loop\" value=\"$parmLoop\">";
	$s .= "<param name=\"controller\" value=\"$parmController\">";
	$s .= "<param name=\"type\" value=\"video/quicktime\">";
	$s .= "<param name=\"pluginspage\" value=\"http://www.apple.com/quicktime/download/indext.html\">";
	$s .= "<param name=\"target\" value=\"myself\">";
	$s .= "<embed ";
	$s .= "src=\"$url\" ";
    if (isset($localfile)) {
    	$s .= "width=\"$parmWidth\" "; 
	    $s .= "height=\"$parmHeight\" ";
	}
	$s .= "target=\"myself\" ";
	$s .= "autoplay=\"$parmAutoplay\" ";
	$s .= "loop=\"$parmLoop\" ";
	$s .= "controller=\"$parmController\" ";
	$s .= "type=\"video/quicktime\" "; 
	$s .= "pluginspage=\"http://www.apple.com/quicktime/download/indext.html\"";
	$s .= "></embed>";
    $s .= "</object>\n";
    return $s;
}

function embed_analyze($fname) {
    require_once('getid3/getid3.php');
    define('GETID3_HELPERAPPSDIR', 'no_helper_apps_needed'); 
    $getID3 = new getID3; 
    $getID3->option_tag_id3v1 = false; 
    $getID3->option_tag_id3v2 = false; 
    $getID3->option_tag_lyrics3 = false; 
    $getID3->option_tag_apetag = false; 
    $getID3->option_tags_process = false; 
    $getID3->option_tags_html = false; 
    $getID3->option_extra_info = true; 
    
    // No need to repeat default values 
    $getID3->option_md5_data = false; 
    $getID3->option_md5_data_source = false; 
    $getID3->option_sha1_data = false; 
    $getID3->option_max_2gb_check = true;

    $info = $getID3->analyze($fname);
    return $info;
}

function embed_is_local($url) {
    if (stristr('://',$url)) {
        return false;
    } else {
        return true;
    }
}

function embed_local_file($url) {
    global $gMediaFolder;
    return $gMediaFolder . $url;
}

function embed_convert_internal_url($url) {
    global $gMediaPath;
    return $gMediaPath . $url;
}

?>