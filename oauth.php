<?php
require_once( 'lib.php' );
require_once( 'config.php' );

function _get_and_save_token( $_auth_code ){
	$ch = curl_init();
	$url = TOKEN_URL;
	$header = array( 'Content-Type: application/x-www-form-urlencoded' );
	$params = array(
		'code'		=> $_auth_code,
		'client_id'	=> CLIENT_ID,
		'client_secret'	=> CLIENT_SECRET,
		'redirect_uri'	=> _get_self_url_base(),
		'grant_type'	=> 'authorization_code',
	);
	$param = http_build_query( $params );

	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $param );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	
	$response_text = curl_exec( $ch );
	_save_token( $response_text );

	$response_json = json_decode( $response_text, true );
	return $response_json;
}

function _save_token( $_token_json_text ){
	$file = fopen( TOKEN_FILE, 'w' );
//	chmod( TOKEN_FILE, 0266 );
	chmod( TOKEN_FILE, 0666 );
	fwrite( $file, $_token_json_text );
}

function _load_token(){
	$file_token_text = file_get_contents( TOKEN_FILE );
	$file_token_json = json_decode( $file_token_text, true );
	return $file_token_json;
}

function _refresh_token(){
	$token_json = _load_token();
	$refresh_token = $token_json[ 'refresh_token' ];
	$url = TOKEN_URL;
	$header = array( 'Content-Type: application/x-www-form-urlencoded' );
	$params = array(
		'client_id'	=> CLIENT_ID,
		'client_secret'	=> CLIENT_SECRET,
		'refresh_token' => $refresh_token,
		'grant_type'	=> 'refresh_token',
	);
	$param = http_build_query( $params );

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $param );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	
	$response_text = curl_exec( $ch );
	$response_json = json_decode( $response_text, true );
	if( array_key_exists( 'error', $response_json ) ){
		return $response_json;
	}
	$response_json[ 'refresh_token' ] = $refresh_token;
	_save_token( json_encode( $response_json ) );
	return $response_json;
}
?>
