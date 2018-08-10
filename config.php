<?php
$secret_json = json_decode( file_get_contents( '.secret.json' ), true );
define( 'CLIENT_ID',		 $secret_json[ 'web' ][ 'client_id' ] );
define( 'CLIENT_SECRET',	 $secret_json[ 'web' ][ 'client_secret' ] );
define( 'AUTH_URL',		'https://accounts.google.com/o/oauth2/v2/auth' );
define( 'REVOKE_URL',		'https://accounts.google.com/o/oauth2/revoke' );
define( 'TOKEN_URL',		'https://www.googleapis.com/oauth2/v4/token' );
define( 'SCOPE',		'https://www.googleapis.com/auth/drive' );
define( 'TOKEN_FILE',		'.token.json' );
define( 'GDRIVE_FILES_URL',	'https://www.googleapis.com/drive/v3/files' );
define( 'MIN_UPDATED_TIME',     '-1 days' );
define( 'TEAM_DRIVE_ID',	'0ANgQhBypclFIUk9PVA' );
define( 'PAGE_SIZE',		'30' );
?>