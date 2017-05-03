<?php
// declare the TIMEZONE for php
date_default_timezone_set('America/New_York');

// Require required classes
require_once(__DIR__ . '/db.php');
require_once(__DIR__ . '/constants.php');
require_once(__DIR__ . '/curl.php');
require_once(__DIR__ . '/imap.php');

require_once(PROJECT_DIR_PATH . 'vendor/autoload.php');