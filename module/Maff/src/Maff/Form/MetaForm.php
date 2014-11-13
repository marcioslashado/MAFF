<?php
namespace Maff\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Db\Sql\Select; 

class MetaForm extends Form {

    protected $adapter;

    public function __construct($name = null) {
        // we want to ignore the name passed
        parent::__construct('metafisicas');

        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'form_codigo',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'form_projeto',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'projeto',
                'class' => 'form-control input-sm',
                'required' => 'required',
            'value' => '', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                //'label' => 'Drop Down', 
                'value_options' => array(
                    '' => ':: Primeiro selecione um Órgão ::',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'form_elemento',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'elemento',
                'class' => 'form-control input-sm',
                'required' => 'required',
            //'value' => '1', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                //'label' => 'Drop Down', 
                'value_options' => array(
                    '' => ':: Primeiro selecione um Projeto ::',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'form_fonte',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'fonte',
                'class' => 'form-control input-sm',
                'required' => 'required',
            //'value' => '1', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                //'label' => 'Drop Down', 
                'value_options' => array(
                    '' => ':: Primeiro selecione um Elemento ::',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'form_execprev',
            'attributes' => array(
                'type' => 'text',
                'id' => 'e_execprev',
                'class' => 'form-control input-sm',
                'placeholder' => '000.000.000,00',
                'disabled' => 'disabled',
            ),
        ));
        
        $this->add(array(
            'name' => 'form_ExecOrcPlan',
            'attributes' => array(
                'type' => 'text',
                'id' => 'ExecOrcPlan',
                'class' => 'form-control input-sm',
                'placeholder' => '000.000.000,00',
                'required' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'form_OrcExec',
            'attributes' => array(
                'type' => 'text',
                'id' => 'OrcExec',
                'class' => 'form-control input-sm',
                'placeholder' => '000.000.000,00',
                'required' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'form_metaprev',
            'attributes' => array(
                'type' => 'text',
                'id' => 'metarealizada',
                'class' => 'form-control input-sm',
                'placeholder' => '0',
                'disabled' => 'disabled',
            ),
        ));
        
        $this->add(array(
            'name' => 'form_metarealizada',
            'attributes' => array(
                'type' => 'text',
                'id' => 'metarealizada',
                'class' => 'form-control input-sm',
                'placeholder' => '',
                'required' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'form_mesano',
            'attributes' => array(
                'type' => 'text',
                'id' => 'mesano',
                'class' => 'form-control input-sm',
                'placeholder' => '06/2014',
                'required' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'form_DesAc',
            'attributes' => array(
                'type' => 'textarea',
                'id' => 'DesAc',
                'class' => 'form-control',
                'cols' => '40',
                'rows' => '8',
                'required' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'form_ResExec',
            'attributes' => array(
                'type' => 'textarea',
                'id' => 'ResExec',
                'class' => 'form-control',
                'cols' => '40',
                'rows' => '8',
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
