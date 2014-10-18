<?php
namespace Gatekeeper;

/**
 * Gatekeeper storage manager
 */

use \Exception;

class Storage
{
	function __construct()
	{
		if ( ! session_id() ){
			if( ! session_start() ){
				throw new Exception( "Gatekeeper requires the use of 'session_start()' at the start of your script, which appears to be disabled.", 1 );
			}
		}

		$this->config( "php_session_id", session_id() );
	}

	public function config($key, $value=null)
	{
		$key = strtolower( $key );

		if( $value ){
			$_SESSION["gk_config"][$key] = serialize( $value );
		}
		elseif( isset( $_SESSION["gk_config"][$key] ) ){
			return unserialize( $_SESSION["gk_config"][$key] );
		}

		return NULL;
	}

	public function get($key)
	{
		$key = strtolower( $key );

		if( isset( $_SESSION["gk_store"], $_SESSION["gk_store"][$key] ) ){
			return unserialize( $_SESSION["gk_store"][$key] );
		}

		return NULL;
	}

	public function set( $key, $value )
	{
		$key = strtolower( $key );

		$_SESSION["gk_store"][$key] = serialize( $value );
	}

	function clear()
	{
		$_SESSION["gk_store"] = ARRAY();
	}

	function delete($key)
	{
		$key = strtolower( $key );

		if( isset( $_SESSION["gk_store"], $_SESSION["gk_store"][$key] ) ){
		    $f = $_SESSION['gk_store'];
		    unset($f[$key]);
		    $_SESSION["gk_store"] = $f;
		}
	}

	function deleteMatch($key)
	{
		$key = strtolower( $key );

		if( isset( $_SESSION["gk_store"] ) && count( $_SESSION["gk_store"] ) ) {
		    $f = $_SESSION['gk_store'];
		    foreach( $f as $k => $v ){
				if( strstr( $k, $key ) ){
					unset( $f[ $k ] );
				}
			}
			$_SESSION["gk_store"] = $f;

		}
	}

	function getSessionData()
	{
		if( isset( $_SESSION["gk_store"] ) ){
			return serialize( $_SESSION["gk_store"] );
		}

		return NULL;
	}

	function restoreSessionData( $sessiondata = NULL )
	{
		$_SESSION["gk_store"] = unserialize( $sessiondata );
	}
}
