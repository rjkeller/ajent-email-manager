<?php
namespace Oranges\MongoDbBundle\Helper;

/**

 @author R.J. Keller <rjkeller@pixonite.com>
*/
interface ModelEvent
{
	public function create(array $db_models);

	public function save(array $db_models);

	public function delete(array $db_models);
}
