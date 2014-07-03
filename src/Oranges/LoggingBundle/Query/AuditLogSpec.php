<?php
namespace Oranges\LoggingBundle\Query;

use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\UserBundle\Helper\SessionManager;

use Ajent\Mail\MailBundle\Entity\EmailMessage;

class AuditLogSpec extends SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "logs";

	/* Format: title -> column name */
	public $show = array();
	public $sort = array(
		"Creation Date" => "timestamp");
	public $actions = null;

	public $showLetters = true;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("description");
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn;

	public $defaultOrderBy;

	public $whereClause;

	public $forceNumResults = 10;

	public $db_entity = "Oranges\LoggingBundle\Entity\LogEntry";

	public $template = "LoggingBundle:query:results.twig.html";

	public $canDelete = true;

	public $loadResultsAtRuntime = false;

	public function __construct()
	{
		$this->defaultOrderBy = array("timestamp" => -1);

		$this->whereClause = array();
	}

	public function loadJson($json)
	{
		$this->whereClause = json_decode(str_replace("'", "\"", $json), true);
	}
}
