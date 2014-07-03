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
class UserForm extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('user');
	}

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Oranges\UserBundle\Entity\User',
        );
    }
}
