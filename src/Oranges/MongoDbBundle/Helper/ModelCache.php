<?php
namespace Oranges\MongoDbBundle\Helper;

/**
 Implement this to create a cache for a database model.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
interface ModelCache
{
	public function create(array $db_models);

	public function save(array $db_models);

	public function delete(array $db_models);
}
