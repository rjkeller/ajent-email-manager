<?php
namespace Ajent\Mail\MailBundle\Helper;

use Ajent\Mail\MailBundle\Entity\Contact;

/**
 Written by Aditya Chandra -> http://phplegend.wordpress.com/2010/02/13/importing-gmail-contacts-using-curl-and-php/
 Modified by R.J. Keller
*/

class ImportGmailContacts
{
	/**
	 Returns an array of gmail contacts, where index 0 corresponds to their
	 names, and index 1 corresponds to their emails.
	
	 If we failed to get the gmail contacts, this method will return 1 if login
	 was invalid, or 2 if the username/password was not specified.
	*/
	public function get_contacts($login, $password)
	{


		#the globals will be updated/used in the read_header function
		global $csv_source_encoding;
		global $location;
		global $cookiearr;
		global $ch;

		$csv_source_encoding = "";
		$location = "";
		$csv_source_encoding = "utf-8";

		#initialize the curl session
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://www.google.com/accounts/ServiceLoginAuth?service=mail");
		curl_setopt($ch, CURLOPT_REFERER, "");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, '\Ajent\Mail\MailBundle\Helper\ImportGmailContacts::read_header');

		#get the html from gmail.com
		$html = curl_exec($ch);

		$matches = array();
		$actionarr = array();

		$action = "https://www.google.com/accounts/ServiceLoginAuth?service=mail";

		#parse the login form:
		#parse all the hidden elements of the form
		preg_match_all('/]*name\="([^"]+)"[^>]*value\="([^"]*)"[^>]*>/si', $html, $matches);
		$values = $matches[2];
		$params = "";

		$i = 0;
		foreach ($matches[1] as $name)
		{
			$params .= "$name=" . urlencode($values[$i]) . "&";
			++$i;
		}

		$login = urlencode($login);
		$password = urlencode($password);

		#submit the login form:
		curl_setopt($ch, CURLOPT_URL,$action);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params ."Email=".$login."&Passwd=".$password."&PersistentCookie=");

		$html = curl_exec($ch);

		#test if login was successful:
		print_r($cookiearr);
		if (!isset($cookiearr['GX']) && (!isset($cookiearr['LSID']) || $cookiearr['LSID'] == "EXPIRED"))
		{
			return 1;
		}

		#this is the new csv url:
		curl_setopt($ch, CURLOPT_URL, "http://mail.google.com/mail/contacts/data/export?exportType=ALL&groupToExport=&out=GMAIL_CSV");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);

		$html = curl_exec($ch);
		$html = iconv ($csv_source_encoding,'utf-8',$html);

		$csvrows = explode("\n", $html);
		array_shift($csvrows);

		$contacts = array();
		foreach ($csvrows as $row)
		{
			if (preg_match('/^((?:"[^"]*")|(?:[^,]*)).*?([^,@]+@[^,]+)/', $row, $matches))
			{
				$contact = new Contact();
				$contact->user_id = SessionManager::$user->id;
				$contact->name = trim( ( trim($matches[1] )=="" ) ? current(explode("@",$matches[2])) : $matches[1] , '" ');
				$contact->email = trim( $matches[2] );
			}
		}

		return $contacts;
	}

	#read_header is essential as it processes all cookies and keeps track of the current location url
	#leave unchanged, include it with get_contacts
	public static function read_header($ch, $string)
	{
		global $cookiearr;
		global $location;
		global $csv_source_encoding;

		$length = strlen($string);

		if (preg_match("/Content-Type: text\\/csv; charset=([^\s;$]+)/",$string,$matches))
			$csv_source_encoding = $matches[1];

		if(!strncmp($string, "Location:", 9))
		{
			$location = trim(substr($string, 9, -1));
		}
		if(!strncmp($string, "Set-Cookie:", 11))
		{
			$cookiestr = trim(substr($string, 11, -1));
			$cookie = explode(';', $cookiestr);
			$cookie = explode('=', $cookie[0]);
			$cookiename = trim(array_shift($cookie));
			$cookiearr[$cookiename] = trim(implode('=', $cookie));
		}
		$cookie = "";
		if(trim($string) == "")
		{
			if (is_array($cookiearr))
			{
				foreach ($cookiearr as $key=>$value)
				{
					$cookie .= "$key=$value; ";
				}
				curl_setopt($ch, CURLOPT_COOKIE, $cookie);
			}
		}

		return $length;
	}
}
