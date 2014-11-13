<?php

namespace Grupos\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Db\Sql\Select; 

class ModulosForm extends Form {

    protected $adapter;

    public function __construct($name = null) {
        // we want to ignore the name passed
        parent::__construct('modulos');

        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'form_modulos',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'modulo_controller',
            'attributes' => array(
                'type' => 'text',
                'id' => 'modulo_controller',
                'class' => 'form-control input-sm',
                'placeholder' => 'Controller do Módulo',
                'required' => 'required',
            ),
        ));
        
        $this->add(array(
            'name' => 'modulo_titulo',
            'attributes' => array(
                'type' => 'text',
                'id' => 'modulo_titulo',
                'class' => 'form-control input-sm',
                'placeholder' => 'Título do Módulo',
                'required' => 'required',
            ),
        ));
        
        $this->add(array(
            'name' => 'modulo_action[]',
            'attributes' => array(
                'type' => 'text',
                'id' => 'modulo_action',
                'class' => 'form-control input-sm',
                'placeholder' => 'Action do Módulo',
                'required' => 'required',
            ),
        ));
        
        $this->add(array(
            'name' => 'titulo_action[]',
            'attributes' => array(
                'type' => 'text',
                'id' => 'titulo_action',
                'class' => 'form-control input-sm',
                'placeholder' => 'Título da Action',
                'required' => 'required',
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
