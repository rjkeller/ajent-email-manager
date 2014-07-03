<?php
namespace Oranges\forms;

use Oranges\MasterContainer;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;

/**
 Contains functions to simplify Symfony form processing.
 
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class FormUtility
{
	/**
	 Returns whether or not the symfony form was successfully submitted. If the
	 form was not submitted, it stores the validation errors in
	 MessageBoxHandler.
	 
	 @param $form The symfony form object.
	*/
	public static function isSubmitted($form, $formdata)
	{
		$request = MasterContainer::get('request');
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);

			if (!$form->hasErrors())
			{
				return true;
			}
			else
			{
//				echo "FORM IS INVALID";
//				print_r($form->hasErrors());
			}

			$errorList = MasterContainer::get("validator")->validate($formdata);
			$errors = "";
			foreach ($errorList as $error)
			{
				MessageBoxHandler::error($error->getMessage());
			}
		}
		return false;
	}

	public static function isFormValid($form)
	{
		$request = MasterContainer::get('request');

		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			return $form->isValid();
		}
		return false;
	}
}