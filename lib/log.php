<?php

$LOG_INFO = 1;
$LOG_NOTICE = 2;
$LOG_WARN = 3;
$LOG_ERR = 4;

function _log($msg, $type) {
    global $LOG_INFO, $LOG_NOTICE, $LOG_WARN, $LOG_ERR;
    if ($type == $LOG_INFO) {
        echo "<br><em>LOGGER (INFO)</em>: $msg<br>";
    } else if ($type == $LOG_NOTICE) {
        echo "<br><em>LOGGER (NOTICE)</em>: $msg<br>";
    } else if ($type == $LOG_WARN) {
        echo "<br><strong>LOGGER (WARN): </strong><font size=\"+1\"><strong>$msg</strong></font><br>";
    } else {
        echo "<br><strong>LOGGER (ERROR): </em><font color=\"red\" size=\"+1\">$msg</font><br>";
    }
}

function log_info($msg) {
    global $LOG_INFO;
    _log($msg, $LOG_INFO);
}

function log_notice($msg) {
    global $LOG_NOTICE;
    _log($msg, $LOG_NOTICE);
}

function log_warn($msg) {
    global $LOG_WARN;
    _log($msg, $LOG_WARN);
}

function log_err($msg) {
    global $LOG_ERR;
    _log($msg, $LOG_ERR);
}


?>