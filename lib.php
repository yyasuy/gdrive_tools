<?php
function _debug_print( $var ){
	if( gettype( $var ) != 'array' ){
		$str = $var;
	}else{
		$str = var_export( $var, true );
	}
	error_log( $str . "\n", 3, '/tmp/test.log' );
}
function _get_server_param( $_name ){
	return isset( $_SERVER[ $_name ] ) ?  $_SERVER[ $_name] : '';
}
function _get_request_param( $_name ){
	$value  = isset( $_REQUEST[ $_name ] ) ?  $_REQUEST[ $_name] : '';
	if( get_magic_quotes_gpc() ){
        	$value  = stripslashes( $value );
	}
	return $value;
}
function _get_self_url_base(){
	$self_url = ( isset( $_SERVER["HTTPS"] ) ? "https://" : "http://" ) . $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ];
	$self_url_filename = basename( $_SERVER[ 'SCRIPT_FILENAME' ] );

	$pos = strpos( $self_url, '?' );
	if( $pos !== false ){
		$self_url = substr( $self_url, 0, $pos );
	}

	$pos = strpos( $self_url, $self_url_filename );
	if( $pos !== false ){
		return substr( $self_url, 0, $pos );
	}

	return $self_url;
}
function _utc_to_localtime( $_utc, $_format = 'Y-m-d H:i:s' ){
	$t = new DateTime( $_utc );
	$t->setTimeZone( new DateTimeZone( 'Asia/Tokyo' ) );
	return $t->format( $_format );
}
?>
