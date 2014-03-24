<?php

/************************************************************************
*  The Craigwatch engine was designed to periodically scan a website and
*  notify users of very specific changes.
*  Copyright (C) 2014  Beau Danger Lynn-Miller
*  
*  This program is free software: you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*  
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*  
*  You should have received a copy of the GNU General Public License
*  along with this program.  If not, see <http://www.gnu.org/licenses/>.
************************************************************************/

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