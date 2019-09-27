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

$sheet_id = '1S0J0pcMwnb97GY-hI9HypgOIIidOCBytPu6HHyvwjsU';
$url = sprintf( '%s/%s', GSHEETS_URL, $sheet_id );

// Get the spreadsheet info
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POST, false );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $header ) );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

// Handle the result
$response_text = curl_exec( $ch );
$response_json = json_decode( $response_text, true );
if( !array_key_exists( 'error', $response_json ) ){
	printf( "success\n" );
}else{
	var_dump( $response_json );
	sleep( 10 );
	// Access Token is no more valid. Try to renew the Access Token using the Refresh Token.
	$response_json = _refresh_token();
	if( array_key_exists( 'error', $response_json ) ){
		var_dump( $response_json );
		printf( "\nCannot refresh\n" );
		exit();
		
	}
	printf( "refreshed\n" );
	sleep( 10 );

	$token_json = _load_token();
	$access_token = $token_json[ 'access_token' ];
	$refresh_token = $token_json[ 'refresh_token' ];
	$header = sprintf( 'Authorization: Bearer %s', $access_token );
	$sheet_id = '1S0J0pcMwnb97GY-hI9HypgOIIidOCBytPu6HHyvwjsU';
	$url = sprintf( '%s/%s', GSHEETS_URL, $sheet_id );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, false );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $header ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	$response_text = curl_exec( $ch );
	$response_json = json_decode( $response_text, true );
}

var_dump( $response_json );

$doc_name = $response_json[ 'properties' ][ 'title' ];

$sheet_names = array();
foreach( $response_json[ 'sheets' ] as $sheet_info ){
	$sheet_names[] = $sheet_info[ 'properties' ][ 'title' ];
}

// Get range data
// Below is just an example of how to use batchGet. I don't use it though.
// GET https://sheets.googleapis.com/v4/spreadsheets/{spreadsheetId}/values:batchGet?ranges=シート1!A1:B2&ranges=シート1!C1:C3
$row_data_array = array();
foreach( $sheet_names as $sheet_name ){
	$range = sprintf( '%s!%s', $sheet_name, 'A1:C' ); // 'C' means the last row in column C with a value.
	$params = array(
		'majorDimension'	=> 'ROWS',
	);
	$param = http_build_query( $params );
	$url = sprintf( '%s/%s/values/%s?%s', GSHEETS_URL, $sheet_id, urlencode( $range ), $param );
	var_dump( $url );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, false );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $header ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

	// Handle the result
	$response_text = curl_exec( $ch );
	$response_json = json_decode( $response_text, true );
	foreach( $response_json[ 'values' ] as $row_values ){
		if( !isset( $row_values[ 0 ] ) ) $row_values[ 0 ] = '';
		if( !isset( $row_values[ 1 ] ) ) $row_values[ 1 ] = '';
		if( !isset( $row_values[ 2 ] ) ) $row_values[ 2 ] = '';
		$row_data_array[] = array(
					'Name'     => $row_values[ 0 ],
					'English'  => $row_values[ 1 ],
					'Japanese' => $row_values[ 2 ],
		);
	}
}

try{


$pdo = new PDO(
        'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';charset=utf8mb4',
	DB_USER,
	DB_PASSWORD
);

$table = $doc_name;
$sql = "DROP TABLE IF EXISTS `$table`";
$stmt = $pdo->prepare( $sql );
$stmt->execute();

$sql  = "CREATE TABLE `$table`";
$sql .= ' (`Name` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL, ';
$sql .= '  `English` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL, ';
$sql .= '  `Japanese` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL) ';
$sql .= 'ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci';
$pdo->exec( $sql );

foreach( $row_data_array as $cols ){
	$sql = "INSERT INTO `$table` VALUES( :name, :english, :japanese )";
	$stmt = $pdo->prepare( $sql );

	$name     = $cols[ 'Name' ];
	$english  = $cols[ 'English' ];
	$japanese = $cols[ 'Japanese' ];
	
	$stmt->bindValue( ':name',     $name,     PDO::PARAM_STR );
	$stmt->bindValue( ':english',  $english,  PDO::PARAM_STR );
	$stmt->bindValue( ':japanese', $japanese, PDO::PARAM_STR );

	$stmt->execute();
}


}catch( PDOException $_e ){
	exit( $_e -> getMessage() );
}
?>
