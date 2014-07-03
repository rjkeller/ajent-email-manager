<?php
namespace Oranges\forms;

use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\UnrecoverableSystemException;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\FormsBundle\Helper\CidManager;

use Oranges\MasterContainer;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 Standard Oranges form validation. Basically a more streamlined version of the
 Symfony form framework. Only dependency is the Symfony Validation framework.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
abstract class WgForm
{
	/** Whether or not the form was submitted. */
	public $isSubmitted = false;

	/** Whether or not there are validation errors in this form. If there are
	  validation errors, the system will automatically load them into
	  MessageBoxHandler.
	*/
	public $hasErrors = false;

	public function __construct()
	{
		if (CidManager::isCidValid($this->getName()))
		{
			if (isset($_POST['form']) && is_array($_POST['form']))
			{
				foreach ($_POST['form'] as $key => $value)
				{
					$_POST[$key] = $value;
				}
			}

			$data = get_object_vars($this);
			foreach ($data as $key => $value)
			{
				if ($key == "isSubmitted" || $key == "hasErrors" || $key == "form")
					continue;
				if (isset($_POST[$key]))
				{
				    $value = trim($_POST[$key]);
				    if ($value == "1")
				        $this->$key = true;
				    else
					    $this->$key = trim($_POST[$key]);
				}
			}

			if (!$this->hasErrors())
			{
				$this->hasErrors = false;
				$this->isSubmitted = true;

				$this->submitForm();
			}
			else
			{
				$this->hasErrors = true;
				$this->isSubmitted = true;
			}
		}
		else
		{
			$this->hasErrors = false;
			$this->isSubmitted = false;
		}
	}

	private static $is_codes_loaded = false;
	private static $old_codes = array();
	private static $new_codes = array();

	private function isCidValid($form_name)
	{
		if (!isset($_POST['cid']))
			return false;

		if (!self::$is_codes_loaded)
		{
			$q = Database::query("
				SELECT
					*
				FROM
					form_codes
				WHERE
					user_id = '". SessionManager::$user->id ."'
				",
				"Oranges\FormBundle\Entity\FormCoder");

			foreach ($q as $i)
			{
				$this->old_codes[$i['name']] = $i['code'];
			}

			self::$is_codes_loaded = true;
		}

		return self::$old_codes[$this->getName()] == $_POST['cid'];
	}


	public function getFormCidCode()
	{
		if (isset($form_codes[$this->getName()]))
			return $form_codes[$this->getName()];

		$code = md5(uniqid(rand(), true));
		$form_codes[$this->getName()] = $code;
		return $code;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{	}

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => get_class(),
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'  => $this->getName()
        );
    }

	public function submitForm()
	{
	}

	/**
	 Inserts all validation errors into UserErrorHandler.
	*/
	public function hasErrors()
	{
		$errorList = MasterContainer::get("validator")->validate($this);

		$hasError = false;
		foreach ($errorList as $error)
		{
			MessageBoxHandler::error($error->getMessage());
			$hasError = true;
		}
		return $hasError;
	}
}
