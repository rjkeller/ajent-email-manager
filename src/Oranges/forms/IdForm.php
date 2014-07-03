<?php
namespace Oranges\forms;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 This is a special form that only takes in an ID. This is used for quick
 "Delete" or "Edit" forms that only take in an ID.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class IdForm extends WgForm
{
	/** @Assert\Type("numeric") */
	public $id;

	private $name;
	
	
	public function __construct($form_name)
	{
		$this->name = $form_name;
		
		parent::__construct();
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add("id");
	}

	public function getName()
	{
		return $this->name;
	}
}
