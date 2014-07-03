<?php
namespace Oranges\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\user\Helper\UserExtras;
use Oranges\forms\StdTypes;

/**
 This form processes a new user registration.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class ContactForm extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$fields = $options['data']->getArray();

		unset($fields['id']);
		unset($fields['user_id']);

		foreach ($fields as $col => $val)
			$builder->add($col);
	}

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Oranges\UserBundle\Entity\Contact',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'  => 'contact_creation'
        );
    }

	public function parseForm(Controller $controller)
	{
		$form = $controller->createForm($this, new Contact());
		$contact = new Contact();

		$request = $controller->get("request");
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);

			if ($form->isValid()) {
				// perform some action, such as save the object to the database
				$contact->user_id = SessionManager::$user->id;
				$contact->save();
			}
		}
		return $form->createView();
	}
}
