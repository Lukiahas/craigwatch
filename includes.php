<?php

date_default_timezone_set('America/Chicago');

libxml_use_internal_errors(TRUE); //Prevents loadhtmlfile from choking while hopefully using up less memory. IMPORTANT: less memory is only freed when this is coupled with libxml_clear_errors after the call.

define('DATABASE_ESCAPE_CHARACTER', '`');
define('DOMAIN_NAME', 'yourdomain.com');
define('DEFAULT_SENDER', 'system@yourdomain.com');
define('TWO_WEEKS', 1209600);
define('MAX_EMAILS', 10);
define('ADMIN_EMAIL', 'your@email.com');

include_once('/absolute/path/to/functions.php');
include_once('/absolute/path/to/search.php');
include_once('/absolute/path/to/listing.php');
include_once('/absolute/path/to/notificationemail.php');
include_once('/absolute/path/to/post.php');