<?php
namespace Oranges\user\Query;

use Oranges\searchResults\SearchResults;
use Oranges\errorHandling\ForceError;
use Oranges\sql\SqlIterator;
use Oranges\misc\WgTextTools;
use Oranges\gui\MessageBoxHandler;
use Oranges\user\Model\User as UserModel;

class UserSearch extends SearchResults
{
	public function __construct()
	{
		if (!empty($_POST['suspendUsr']))
		{
			$this->onSuspendUser();
		}

		if (!empty($_POST['deleteUsr']))
		{
			$this->onDeleteUser();
		}


		parent::__construct(new UserSpec());
	}

	public function getSqlQuery()
	{
		return new SqlIterator(parent::getSqlQuery(), "parseRow", $this);
	}

	public function parseRow($data)
	{
		$data->email = WgTextTools::truncate($data->email, 24);
		return $data;
	}

	public function onSuspendUser()
	{
		ForceError::$inst->checkId($_POST['suspendUsr']);
		$user = new UserModel();
		$user->load($_POST['suspendUsr']);
		ForceError::$inst->checkUserModifyPermissions($user);

		if ($user->role == "Customer")
		{
			$user->role = 'Inactive';
			$user->save();

			MessageBoxHandler::happy("This user has been successfully suspended.");
		}
		else
		{
			$user->role = 'Customer';
			$user->save();

			MessageBoxHandler::happy("This user's account has been successfully restored.");
		}
	}

	public function onDeleteUser()
	{
		ForceError::$inst->checkId($_POST['deleteUsr']);
		$user = new UserModel();
		$user->load($_POST['deleteUsr']);
		ForceError::$inst->checkUserModifyPermissions($user);

		$user->isDeleted = true;
		$user->save();

		MessageBoxHandler::happy("This user has been successfully deleted.");
	}
}
