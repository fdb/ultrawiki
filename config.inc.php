<?php
/**
 * config.php
 * Minwiki Configuration File
 *
 *
 */

$gWikiName = 'Ultrawiki';

$gWikiPath = '/ultrawiki/';

$gDataFolder = 'data/';

$gTextFolder = $gDataFolder . 'text/';
$gCacheFolder = $gDataFolder . 'cache/';
$gMediaFolder = $gDataFolder . 'media/';
$gThumbFolder = $gDataFolder . 'thumbs/';
$gPermFolder = $gDataFolder . 'perm/';
$gUserFile = $gPermFolder . 'users.db';
$gGroupFile = $gPermFolder . 'groups.db';

$gThemesFolder = 'themes/';
$gThemePath = $gWikiPath . 'themes/';


$gMediaPath = $gWikiPath . 'data/media/';
$gThumbPath = $gWikiPath . 'data/thumbs/';
$gThumbWidth = $gThumbHeight = 150;
$gThumbQuality = 90;
$gThumbBackRed = 240;
$gThumbBackGreen = 240;
$gThumbBackBlue = 240;

$gAllowedTypes = array("jpg"=>250, "gif"=>250, "png"=>250, "mp3"=>3000, "mov"=>3000);
$gImageTypes = array("jpg", "gif", "png");
$gSoundTypes = array("mp3");
$gMovieTypes = array("mov");

$gTheme = 'default';
$gOutputType = 'html';

$gHomepage = 'homepage';

//=== Don't change these ==//
$gReadFlag = 4;
$gEditFlag = 2;
$gLayoutFlag = 1;

?>