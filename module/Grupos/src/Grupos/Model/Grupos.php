<?php

namespace Grupos\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Grupos implements InputFilterAwareInterface {

    protected $inputFilter;
    public $r_rid;
    public $r_nome;
    public $r_status;
    public $rp_id;
    public $rp_permission;
    public $per_id;
    public $per_nome;
    public $per_resource;
    public $re_id;
    public $re_nome;
    public $per_amigavel;
    public $re_amigavel;

    /**
     * Grupos
     */
    public $form_grupos;
    public $grupo_titulo;
    public $grupo_modulo;
    public $modulo_acoes;
    public $modulo_status;
    
    /**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data) {
        /**
         * Registro de campos do banco de dados 
         * Necessários para consultas e criação de páginas independentes
         */
        $this->r_rid = (isset($data['r_rid'])) ? $data['r_rid'] : null;
        $this->r_nome = (isset($data['r_nome'])) ? $data['r_nome'] : null;
        $this->r_status = (isset($data['r_status'])) ? $data['r_status'] : null;
        $this->rp_id = (isset($data['rp_id'])) ? $data['rp_id'] : null;
        $this->rp_permission = (isset($data['rp_permission'])) ? $data['rp_permission'] : null;
        $this->per_id = (isset($data['per_id'])) ? $data['per_id'] : null;
        $this->per_nome = (isset($data['per_nome'])) ? $data['per_nome'] : null;
        $this->per_resource = (isset($data['per_resource'])) ? $data['per_resource'] : null;
        $this->re_id = (isset($data['re_id'])) ? $data['re_id'] : null;
        $this->re_nome = (isset($data['re_nome'])) ? $data['re_nome'] : null;
        $this->re_amigavel = (isset($data['re_amigavel'])) ? $data['re_amigavel'] : null;
        $this->per_amigavel = (isset($data['per_amigavel'])) ? $data['per_amigavel'] : null;
        
        $this->form_grupos = (isset($data['r_rid'])) ? $data['r_rid'] : null;
        $this->grupo_titulo = (isset($data['r_nome'])) ? $data['r_nome'] : null;
        $this->modulo_status = (isset($data['r_status'])) ? $data['r_status'] : null;
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
                        'name' => 'form_grupos',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'Int'),
                        ),
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'grupo_titulo',
                        'required' => true
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'modulo_status',
                        'required' => true
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'grupo_modulo',
                        'required' => true
            )));
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}
