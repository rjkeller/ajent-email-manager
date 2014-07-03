<?php
namespace Ajent\Vendor\VendorScanBundle\Query;

use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\UserBundle\Helper\SessionManager;

class VendorSpec extends SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "vendors";

	/* Format: title -> column name */
	public $show = array();
	public $sort = array();
	public $actions = null;

	public $showLetters = true;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array();
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn;

	public $defaultOrderBy = "sort_index";

	public $whereClause;

	public $forceNumResults = 10;

	public $db_entity = "Ajent\Vendor\VendorBundle\Entity\Vendor";

    public $loadResultsAtRuntime = true;

	public $template = "MailRegistrationBundle:query:results.twig.html";

    public function loadMoreResults()
    {
        header("Location: /sign-up/import_mail");
        die();
    }

	public function __construct()
	{
		$this->whereClause = array(
			"user_id" => SessionManager::$user->id,
			"name" => array('$ne' => "Ajent.com"));
	}
}
