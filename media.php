<?php

require_once('config.inc.php');

$gRequest = $_SERVER['REQUEST_URI'];
$gSelf = $_SERVER['PHP_SELF'];

$TYPE_IMAGE = 'image';
$TYPE_SOUND = 'sound';
$TYPE_MOVIE = 'movie';
$TYPE_UPLOAD = 'upload';


//// File Management Functions ////
function extension($fname) {
    $pi = pathinfo($fname);
    return $pi['extension'];
}

function cleanName($fname) {
    $fname = strtolower($fname);
    $fname = str_replace('/', '_', $fname);
    $fname = str_replace('..', '', $fname);
    return $fname;
}

function checkType($fname) {
    global $gAllowedTypes;
    $ext = strtolower(extension($fname));
    return array_key_exists($ext, $gAllowedTypes) ;
}

function checkSize($fname, $fsize) {
    global $gAllowedTypes;
    $type = extension($fname);
    //echo var_dump($fsize);
    return true;
    return $gAllowedTypes[$type]*1024 < $fsize;
}

function checkExists($fname) {
    global $gMediaFolder;
    return file_exists($gMediaFolder . $fname);
}

function checkError($ferror) {
    if ($ferror == UPLOAD_ERR_PARTIAL or $ferror == UPLOAD_ERR_NO_FILE) {
        return True;
    } else	{
        return False;
    }
}

function uploadFile($fpath, $fname) {
    global $gMediaFolder;
    $upload = move_uploaded_file($fpath, $gMediaFolder . $fname);
    return $upload;
}

function dispose($fname) {
    global $gSelf;
    global $gMediaFolder, $gThumbFolder;
    @unlink($gMediaFolder . $fname);
    @unlink($gThumbFolder . $fname);
    header("Location: $gSelf?type=upload");
}

//// Image Management Functions ////
function createThumb($sourcefile, $destfile) {   
    global $gThumbWidth, $gThumbHeight, $gThumbQuality;
    global $gThumbBackRed, $gThumbBackGreen, $gThumbBackBlue;
    $fw = $gThumbWidth;
    $fh = $gThumbHeight;
    list($ow, $oh, $from_type) = getimagesize($sourcefile);

    if ($ow == 0 or $oh == 0) {
       return;
    }
    
    switch($from_type) {
        case 1: // GIF
            $srcImage = imageCreateFromGif($sourcefile);
            break;
        case 2: // JPG
            $srcImage = imageCreateFromJpeg($sourcefile);
            break;
        case 3: // PNG
            $srcImage = imageCreateFromPng($sourcefile);
            break;
    }
    
    if ($ow > $oh) {
        $factor = $fw / $ow;
        $dstw = $fw;
        $dsth = $fw * $factor;
        $dsty = ($fh - $dsth) / 2;
        $dstx = 0;
    } else {
        $factor = $fh / $oh;
        $dsth = $fh;
        $dstw = $fw * $factor;
        $dstx = ($fw - $dstw) / 2;
        $dsty = 0;
    }
    
    $destImage = imageCreateTrueColor($fw, $fh);
    $backColor = imageColorAllocate($destImage, $gThumbBackRed, $gThumbBackGreen, $gThumbBackBlue);
    imageFill($destImage, 0, 0, $backColor);
    imageAntiAlias($destImage, true);

    imagecopyresampled($destImage, $srcImage, $dstx, $dsty, 0, 0, $dstw, $dsth, $ow, $oh);
    umask(0);
    imageJpeg($destImage, $destfile, $gThumbQuality);
}



function html_header() {
    global $gWikiName, $gSelf;
    echo "<html><head><meta HTTP-EQUIV=\"content-type\" CONTENT=\"text/html; charset=UTF-8\">";
    echo "<title>$gWikiName - Media</title>\n";
    echo "<link href=\"themes/default/default.css\" rel=\"stylesheet\" type=\"text/css\">\n";
    echo "<script type=\"text/javascript\" src=\"edit.js\"></script>\n";
    echo "</head><body>\n";
    echo "<a href=\"$gSelf?type=image\">Image</a>&nbsp;<a href=\"$gSelf?type=sound\">Sound</a>&nbsp;<a href=\"$gSelf?type=movie\">Movie</a>&nbsp;<a href=\"$gSelf?type=upload\">Upload</a><br><br>\n";
}

function html_upload() {
    global $gRequest, $gAllowedTypes;
    echo "<p>Supported file formats are: JPG, GIF, DCR, PDF and ZIP. Make sure your files have a valid extension, for example: .jpg, .gif, .dcr, .pdf, or .zip. Maximum file size is 100KB for images, 600KB for Shockwave and PDF's, or 2MB for zips.</p>";
    echo "<p>";
    echo "<form action=\"$gRequest\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkUploading(this);\">";
    echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"" . $gAllowedTypes["mov"]*1024 . "\" />";
    echo "<input type=\"file\" name=\"file\" size=\"50\"><br />";
    echo "<input type=\"submit\" value=\"Upload\" />";
    echo "</form>";
    echo "</p>";
}

function html_error($msg) {
    echo "<p class=\"err\">Unable to upload file: $msg</p>\n";
}

function html_files() {
    global $gRequest, $gMediaFolder;
    $files = glob($gMediaFolder . '*.*');

    if (!empty($files)) {
        echo "<table>";
        foreach($files as $fullname) {        
            $fname = basename($fullname);
            echo "<tr><td>$fname&nbsp;&nbsp;</td>";
            echo "<td>" . ceil(filesize($fullname)/1024) . "KB&nbsp;&nbsp;</td>";
            echo "<td><a href=\"$gRequest&delete=$fname\" onclick=\"return confirm('Are you sure you want to delete $fname?')\">delete</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

function html_list($types) {
    global $gRequest, $gMediaFolder;
    $files = glob($gMediaFolder . '*.*');
    echo "<table>";
    foreach($files as $fullname) {
        $ext = extension($fullname);
        if (in_array($ext, $types)) {
            $fname = basename($fullname);
            echo "<tr><td>";
            echo "<a href=\"#\" onclick=\"selectMedia('$fname')\">";
            echo "$fname</td>";
            echo "</a>&nbsp;&nbsp;</td>";
            echo "<td>" . ceil(filesize($fullname)/1024) . "KB&nbsp;&nbsp;</td>";
            echo "</td></tr>";
        }
    }
    echo "</table>";
}

function html_thumbs() {
    global $gRequest, $gScript, $gMediaFolder, $gThumbFolder, $gThumbURL, $gImageTypes;
    $files = glob($gMediaFolder . "*");
    echo "<div class=thumbs>\n";
    if (!empty($files)) {
        foreach ($files as $fullname) {
            $fname = basename($fullname);        
            $ext = extension($fullname);
            if (in_array($ext,$gImageTypes)) {
                $thumbname = $gThumbFolder . $fname;
                $thumburl = $gThumbURL . $fname;
                if (!file_exists($thumbname)) { // Create thumb
                    createThumb($fullname, $thumbname);
                }

                echo "<div class=\"thumb\">";
                echo "<div class=\"wrap1\">";
                echo "<div class=\"wrap2\">";
                echo "<div class=\"wrap3\">";
                echo "<a class=img href=\"#\" onclick=\"selectMedia('$fname')\">";
                echo "<img src=\"$thumburl\" alt=\"\" />";
                echo "</a>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo $fname;
                echo "</div>";
            }
        }
    }
    echo "</div>\n";
}

function html_footer() {
    echo "</div></body></html>";
}

//// Media types ////

function do_image() {
    html_header();
    html_thumbs();
    html_footer();
}

function do_sound() {
    global $gSoundTypes;
    html_header();
    html_list($gSoundTypes);
    html_footer();
}

function do_movie() {
    global $gMovieTypes;
    html_header();
    html_list($gMovieTypes);
    html_footer();
}

function do_upload() {
    if (isset($_GET['delete'])) {
        dispose($_GET['delete']);
        break;
    } else {
        $fname  = @$_FILES["file"]["name"];
        $ftype  = @$_FILES["file"]["type"];
        $fsize  = @$_FILES["file"]["size"];
        $fpath  = @$_FILES["file"]["tmp_name"];
        $ferror = @$_FILES["file"]["error"];    
        
        html_header();
    
        if (!empty($fname)) {
            $fname = cleanName($fname);
            if (!checkType($fname, $fsize)) html_error("the file format is not supported.");
            elseif (!checkSize($fname, $fsize)) html_error("the file size is too large.");
            elseif (checkError($ferror)) html_error("no file was selected or the file was only partially uploaded.");
            elseif (checkExists($fname)) html_error("this file already exists on the server. Delete the file on the server first and try again.");
            elseif (@!uploadFile($fpath, $fname)) html_error("an error occurred on the server. Please inform the administrator.");
        }
            
        html_upload();
        echo "<hr />";
        html_files();
        //html();
        html_footer();
    }
}

// Main loop
$gSelf = $_SERVER['PHP_SELF'];
$gRequest = $_SERVER['REQUEST_URI'];

$gType = @$_GET['type'];
if (empty($gType)) $gType = $TYPE_IMAGE;

if ($gType == $TYPE_IMAGE) {
    do_image();
} else if ($gType == $TYPE_SOUND) {
    do_sound();
} else if ($gType == $TYPE_MOVIE) {
    do_movie();
} else if ($gType == $TYPE_UPLOAD) {
    do_upload();
}
?>