<?php namespace Fluid\Models;

use PDO, Fluid\Database\Connection;

class PageToken {
	/**
	 * Get a page token
	 * 
	 * @return  string
	 */
	public static function getToken() {
		return self::createToken();
	}
	
	/**
	 * Create a page token
	 * 
	 * @return  string
	 */
	public static function createToken() {
		$dbh = Connection::getConnection();
		
		$token = self::generate(64);
		
		$sth = $dbh->prepare("INSERT INTO `fluid_page_tokens` (`token`, `expiration`) VALUES (:token, :expiration);");
		$sth->execute(array(
			':token' => $token, 
			':expiration' => date('Y-m-d H:i:s', time()+3600)
		));
		return $token;
	}
	
	/**
	 * Validate a page token and delete it
	 * 
	 * @param   string  $token
	 * @return  bool
	 */
	public static function validateToken( $token ) {
		self::deleteExpired();
		
		$dbh = Connection::getConnection();
		$sth = $dbh->prepare("SELECT `token` FROM `fluid_page_tokens` WHERE `token`=:token AND `expiration`>NOW();");
		$sth->execute(array(':token'=>$token));
		if ($sth->fetch()) {
			// !! Delete token
			return true;
		} else {
			return false;
		}		
	}
	
	/**
	 * Delete expired tokens
	 * 
	 * @return  void
	 */
	private static function deleteExpired() {
		$dbh = Connection::getConnection();
		$dbh->query("DELETE FROM `fluid_page_tokens` WHERE `expiration`<NOW();");
	}
	
	/**
	 * Generate a random string
	 * 
	 * @param   int
	 * @return  string
	 */
	private static function generate( $length ) {
		$characters = [
			'a','b','c','d','e','f','g','h','o','j','k','l',
			'm','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D',
			'E','F','G','H','O','J','K','L','M','N','O','P','Q','R','S','T','U',
			'V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9'
		];
		
		srand((float) microtime() * 1000000);
		
		$token = '';
		
		do {
			shuffle($characters);
			$token .= $characters[mt_rand(0, (count($characters)-1))];
		} while (strlen($token) < $length);
		    	
		return $token;
	}
}