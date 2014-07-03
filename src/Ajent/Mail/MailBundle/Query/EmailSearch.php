<?php
namespace Ajent\Mail\MailBundle\Query;

use Oranges\searchResults\SearchResults;
use Oranges\errorHandling\ForceError;
use Oranges\sql\SqlModelIterator;
use Oranges\misc\WgTextTools;
use Oranges\gui\MessageBoxHandler;
use Oranges\user\Model\User as UserModel;

use Ajent\Mail\MailBundle\Helper\MailSync;

class EmailSearch extends SearchResults
{
	public function __construct($spec = null)
	{
		if ($spec == null)
			$spec = new EmailSpec();

		parent::__construct($spec);
	}

	public function getSqlQuery()
	{
		return new SqlModelIterator(parent::getSqlQuery(),
		    "Ajent\Mail\MailBundle\Entity\EmailMessage");
	}
}
