<?php

/**
 * LoginForm class
 * @author Display Name <osscube(Kaushal Kishore)>
 * Class to create login form
 */
namespace Users\Form;

use Zend\Form\Form;

/**
 * Roster Form
 *
 * @category Login
 * @package Form
 *         
 * @author Display Name <osscube(Kaushal Kishore)>
 */
class ForgotPasswordForm extends Form
{

    /**
     * default constructor
     *
     * @param string $name
     *            name of the form
     */
    public function __construct($name)
    {
        
        // Set form name
        parent::__construct($name);
        
        //$this->setAttribute('method', 'post');
        $this->setAttributes(array(
            'class' => 'form-signin',
            'method' => 'post'
        ));
        
        $this->add(array(
            'name' => 'userName',
            'attributes' => array(
                'id' => 'userName',
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => 'Seu E-MAIL'
            ),
            'options' => array(
                'label' => 'Digite seu Email'
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enviar',
                'id' => 'submitbutton',
                'class' => 'btn btn-lg btn-primary btn-block'
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'loginCsrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 3600
                )
            )
        ));
    }
}
