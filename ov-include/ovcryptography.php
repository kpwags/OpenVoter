<?php
//ini_set("include_path", ".:./:./include:./../include:./../../include:./ov-admin/include:./../ov-admin/include:./../usercontrols:./usercontrols:./ov-admin/usercontrols:./../ov-admin/usercontrols:./themes:./../themes");
/*
	Copyright 2008-2011 OpenVoter
	
	This file is part of OpenVoter.

	OpenVoter is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, version 3.

	OpenVoter is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with OpenVoter.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * OpenVoter Cryptography Class
 * Class dealing with encryption for site
 *
 * @package OpenVoter
 * @subpackage Cryptography
 * @since 3.0
 */
class ovCryptography
{
	function __construct()
	{

	}
	
	function __destruct() 
	{
		
	}

	/**
	 * Encrypts a string
	 * @param string $text The text to encrypt
	 * @param int $salt The salt for the encryption
	 * @param int $key The key for the encryption
	 * @return string The encryption string
	 * @access public
	 * @since 3.0
	 */
	public function OVEncrypt($text, $salt, $key)
	{	
		$cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');
		
		if (mcrypt_generic_init($cipher, $key, $salt) != -1)
		{
			// PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
			$cipherText = mcrypt_generic($cipher, $text);
			mcrypt_generic_deinit($cipher);
			
			return bin2hex($cipherText);
		}
		else
		{
			return false;	
		}
	}
	
	/**
	 * Gets a random salt
	 * @return int Salt for the encryption
	 * @access public
	 * @since 3.0
	 */
	public function GetSalt()
	{
		return rand(10000000, 99999999);
	}
	
	/**
	 * Gets a random key
	 * @return int Key for the encryption
	 * @access public
	 * @since 3.0
	 */
	public function GetKey()
	{
		return rand(1000000000000000, 9999999999999999);
	}
}
?>