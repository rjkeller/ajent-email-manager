<?php
namespace Pixonite\BillingBundle\Helper;

use Oranges\searchResults\SearchResultsSpec;
use Oranges\UserBundle\Helper\SessionManager;

class BillingSearchResultsSpec extends SearchResultsSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "invoice_cache";

	/* Format: title -> column name */
	public $show = array(
		"Registrations" => "domainRegistration",
		"Renewals" => "renewal",
		"Monitizations" => "monitizationPayment",
		"Escrow" => "escrowOrder",
		"Email" => "emailRenew",
		"SSL" => "sslCertOrder",
		"Hosting" => "hostingRenew",
		"Deposits" => "deposits"
		);
	public $sort = array(
		"Date" => "date",
		"Type" => "type",
		"Amount" => "amount");
	public $actions = null;

	public $showLetters = true;

	public $supportGetQueries = true;
	public $supportPostQueries = true;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("name", "amount");

	public $defaultOrderBy = "date DESC";

	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn = "type";

	/**
	 This parameter contains SQL data that goes after the WHERE statement in a
	 SELECT query. So use this to preload condition data to the query. Please set
	 to null if there is no WHERE clauses you'd like to include.
	 */
	public $whereClause = null;

	public function __construct($user = null)
	{
		if ($user == null)
			$user = SessionManager::$user->id;
		$this->whereClause = "user_id = '$user'";
	}
}
