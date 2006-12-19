<?php
/**
 * config.php
 * Ultrawiki Configuration File
 *
 *
 */

$gWikiName = 'Ultrawiki';
$gTheme = 'default';
$gHomepage = 'homepage';

$gWikiFolder = './';
$gWikiURL = '/ultrawiki/';

$gDataFolder = $gWikiFolder . 'data/';
$gTextFolder = $gDataFolder . 'text/';
$gCacheFolder = $gDataFolder . 'cache/';
$gMediaFolder = $gDataFolder . 'media/';
$gThumbFolder = $gDataFolder . 'thumbs/';
$gUserFile = $gDataFolder . 'perm/users.db';
$gGroupFile = $gDataFolder . 'perm/groups.db';

$gThemesFolder = $gWikiFolder . 'themes/';
$gThemeURL = $gWikiURL . "themes/$gTheme/";
$gDefaultThemeURL = $gWikiURL . 'themes/default/';

$gMediaURL = $gWikiURL . 'data/media/';
$gThumbURL = $gWikiURL . 'data/thumbs/';

$gMediaURL = $gWikiURL . 'data/media/';
$gThumbURL = $gWikiURL . 'data/thumbs/';
$gThumbWidth = $gThumbHeight = 150;
$gThumbQuality = 90;
$gThumbBackRed = 240;
$gThumbBackGreen = 240;
$gThumbBackBlue = 240;

$gAllowedTypes = array("jpg"=>250, "gif"=>250, "png"=>250, "mp3"=>3000, "mov"=>3000);
$gImageTypes = array("jpg", "gif", "png");
$gSoundTypes = array("mp3");
$gMovieTypes = array("mov");

$gOutputType = 'html';

//=== Don't change these ==//
$gReadFlag = 4;
$gEditFlag = 2;
$gLayoutFlag = 1;

?>