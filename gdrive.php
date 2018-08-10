<?php
require_once( 'config.php' );
require_once( 'lib.php' );
require_once( 'oauth.php' );

// Read the Access Token and the Refresh Token from the file.
$token_json = _load_token();
$access_token = $token_json[ 'access_token' ];
$refresh_token = $token_json[ 'refresh_token' ];

// Create Google Drive API parameters for getting the file list.
$header = sprintf( 'Authorization: Bearer %s', $access_token );

$min_updated = date( DATE_ATOM, strtotime( MIN_UPDATED_TIME ) );
if( TEAM_DRIVE_ID == '' ){
	$params = array(
			'corpora'		=> 'user',
			'q'			=> sprintf( "modifiedTime > '%s' and not mimeType contains 'folder' and trashed = false", $min_updated ),
			'orderBy'		=> 'modifiedTime desc',
			'fields'		=> 'files(id, name, kind, mimeType, lastModifyingUser, modifiedTime, webViewLink)',
			'pageSize'		=> PAGE_SIZE,
	);
}else{
	$params = array(
			'corpora'		=> 'teamDrive',
			'includeTeamDriveItems'	=> 'true',
			'supportsTeamDrives'	=> 'true',
			'teamDriveId'		=> TEAM_DRIVE_ID,
			'q'			=> sprintf( "modifiedTime > '%s' and not mimeType contains 'folder' and trashed = false", $min_updated ),
			'orderBy'		=> 'modifiedTime desc',
			'fields'		=> 'files(id, name, kind, mimeType, lastModifyingUser, modifiedTime, webViewLink)',
			'pageSize'		=> PAGE_SIZE,
	);
}
$param = http_build_query( $params );
$url = sprintf( '%s?%s', GDRIVE_FILES_URL, $param );

// Run the API
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POST, false );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $header ) );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

// Handle the result
$response_text = curl_exec( $ch );
$file_list_json = json_decode( $response_text, true );
if( !array_key_exists( 'error', $file_list_json ) ){
	// Successfully got the file list
	if( count( $file_list_json[ 'files' ] ) == 0 ) exit();
	$mail_body = '';
	foreach( $file_list_json[ 'files' ] as $file_items ){
		$name    = $file_items[ 'name' ];
		$link    = $file_items[ 'webViewLink' ];
		$user    = $file_items[ 'lastModifyingUser' ][ 'displayName' ];
		$updated = _utc_to_localtime( $file_items[ 'modifiedTime' ] );
		$mail_body .= sprintf( "----------\n" );
		$mail_body .= sprintf( "$name\n$link\n$updated by $user\n" );
	}
	$mail_body .= sprintf( "----------\n" );
	printf( $mail_body );
}else{
	// Access Token is no more valid. Try to renew the Access Token using the Refresh Token.
	$response_json = _refresh_token();
	if( array_key_exists( 'error', $response_json ) ){
		exit( 'error' );
		
	}
	printf( 'refreshed' );
}
?>
