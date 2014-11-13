<?php
namespace Users\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Db\Sql\Select; 

class UsersForm extends Form {

    protected $adapter;

    public function __construct($name = null) {
        // we want to ignore the name passed
        parent::__construct('usuarios');

        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'form_users',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'form_nome',
            'attributes' => array(
                'type' => 'text',
                'id' => 'first_name',
                'class' => 'form-control input-sm',
                'placeholder' => 'Nome',
                'required' => 'required',
            ),
        ));
        
        $this->add(array(
            'name' => 'form_sobrenome',
            'attributes' => array(
                'type' => 'text',
                'id' => 'last_name',
                'class' => 'form-control input-sm',
                'placeholder' => 'Sobrenome',
                'required' => 'required',
            ),
        ));
        
        $this->add(array(
            'name' => 'form_email',
            'attributes' => array(
                'type' => 'email',
                'id' => 'email',
                'class' => 'form-control input-sm',
                'placeholder' => 'Email',
                'required' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'id' => 'password',
                'type' => 'password',
                'class' => 'form-control input-sm',
                'placeholder' => 'SENHA'
            ),
        ));
        
        $this->add(array(
            'name' => 'form_grupo',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'grupo',
                'class' => 'form-control input-sm',
                'required' => 'required',
            'value' => '', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                //'label' => 'Drop Down', 
                'value_options' => array(
                    '' => ':: Selecione um Grupo de Permissões ::',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'form_unorcs[]',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'unorcs',
                'multiple' => '',
                'class' => 'form-control input-sm',
            'value' => '', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                //'label' => 'Drop Down', 
                'value_options' => array(
                    '' => ':: Selecione um Órgão ::',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'form_status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'status',
                'class' => 'form-control input-sm',
                'Active' => 'Ativo', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                //'label' => 'Drop Down', 
                'value_options' => array(
                    'Active' => 'Ativo',
                    'Inactive' => 'Inativo',
                ),
            ),
        ));

        $this->add(array(
            new Element\Button('adicionar'),
            'name' => 'adicionar',
            'attributes' => array(
                'type' => 'submit',
                'id' => 'adicionar',
                'class' => 'btn btn-primary',
                'value' => '<span class="glyphicon" data-icon="&#xe1dc;"></span> Adicionar',
            ),
        ));
    }
}
