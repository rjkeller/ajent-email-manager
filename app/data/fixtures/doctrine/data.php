<?php

use Ajent\Mail\MailBundle\Entity\EmailUser;
use Ajent\Mail\MailBundle\Entity\EmailDomain;
use Oranges\sql\Database;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\framework\BuildOptions;

$db = MongoDb::getDatabase();
$db->email_accounts->remove();
$db->email_categories->remove();
$db->email_vendors->remove();
$db->email_alerts->remove();
$db->email_external_accounts->remove();
$db->email_domains->remove();

Database::query("DELETE FROM users");
Database::query("DELETE FROM contacts");

$emailDomain = new EmailDomain();
$emailDomain->domain = BuildOptions::$get['MailBundle']['DefaultEmailDomain'];
$emailDomain->create();

$db->email_messages->remove();

/*
 XXXRJ: Is this still in use?
*/
/*
Database::query("DELETE FROM vendor_recommendations");

$f = fopen(__DIR__ ."/vendors.csv", "r");
while (($s = fgets($f)) != null)
{
	$data = explode(";", $s);
	$dbh = Database::getDatabase();

	if (!isset($data[3]))
		$data[3] = "";

	$q = $dbh->executeUpdate("
		INSERT INTO
			vendor_recommendations
		VALUES
			(NULL,
			'". htmlentities($data[0]) ."',
			'". htmlentities($data[1]) ."',
			'". htmlentities($data[2]) ."',
			'". htmlentities($data[3]) ."'
			)
	");
}
fclose($f);
*/