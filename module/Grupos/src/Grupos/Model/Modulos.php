<?php

namespace Grupos\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Modulos implements InputFilterAwareInterface {

    protected $inputFilter;
    public $r_id;
    public $r_nome;
    public $r_amigavel;
    public $p_id;
    public $p_nome;
    public $p_r_id;
    public $p_amigavel;
    
    /**
     * FORM
     */
    public $form_modulos;
    public $modulo_controller;
    public $modulo_titulo;
    public $modulo_action;
    public $titulo_action;
    
    /**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data) {
        /**/
        $this->r_id = (isset($data['r_id'])) ? $data['r_id'] : null;
        $this->r_nome = (isset($data['r_nome'])) ? $data['r_nome'] : null;
        $this->r_amigavel = (isset($data['r_amigavel'])) ? $data['r_amigavel'] : null;
        $this->p_id = (isset($data['p_id'])) ? $data['p_id'] : null;
        $this->p_nome = (isset($data['p_nome'])) ? $data['p_nome'] : null;
        $this->p_r_id = (isset($data['p_r_id'])) ? $data['p_r_id'] : null;
        $this->p_amigavel = (isset($data['p_amigavel'])) ? $data['p_amigavel'] : null;
        
        /**
         * PARA O FORMULÁRIO
         * @return type
         */
        $this->form_modulos = (isset($data['r_id'])) ? $data['r_id'] : null;
        $this->modulo_controller = (isset($data['r_nome'])) ? $data['r_nome'] : null;
        $this->modulo_titulo = (isset($data['r_amigavel'])) ? $data['r_amigavel'] : null;
        $this->modulo_action = (isset($data['p_nome'])) ? $data['p_nome'] : null;
        $this->titulo_action = (isset($data['p_amigavel'])) ? $data['p_amigavel'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                        'name' => 'form_modulos',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'Int'),
                        ),
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'modulo_controller',
                        'required' => true
            )));
            
            $inputFilter->add($factory->createInput(array(
                        'name' => 'modulo_titulo',
                        'required' => true
            )));
            
            $inputFilter->add($factory->createInput(array(
                        'name' => 'modulo_action',
                        'required' => true
            )));
            
            $inputFilter->add($factory->createInput(array(
                        'name' => 'titulo_action',
                        'required' => true
            )));
            
            $this->inputFilter = $inputFilter;
        }
        
        return $this->inputFilter;
    }

}
