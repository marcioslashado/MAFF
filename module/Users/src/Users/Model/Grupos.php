<?php

namespace Users\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Grupos implements InputFilterAwareInterface {

    protected $inputFilter;
    public $u_id;
    public $u_email;
    public $u_password;
    public $u_tentativas;
    public $u_login_attempt_time;
    public $u_first_name;
    public $u_last_name;
    public $u_orgaos;
    public $u_status;
    public $u_last_signed_in;
    public $o_unorc;
    public $o_descricao;
    public $ur_id;
    public $ur_user_id;
    public $r_rid;
    public $r_nome;
    public $r_status;
    public $p_id;
    public $p_role_name;
    public $p_status;
    public $per_id;
    public $per_nome;
    public $per_resource;
    public $re_id;
    public $re_nome;
    
    /**
     * Para o Form
     */
    public $form_codigo;
    public $form_projeto;
    public $form_elemento;
    public $form_fonte;
    public $form_execprev;
    public $form_ExecOrcPlan;
    public $form_OrcExec;
    public $form_metaprev;
    public $form_metarealizada;
    public $form_mesano;
    public $form_DesAc;
    public $form_ResExec;

    /**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data) {
        /**
         * Registro de campos do Formulário
         * Necessários para a ação de CRUD
         */
        $this->form_codigo = (isset($data['m_Codigo'])) ? $data['m_Codigo'] : null;
        $this->form_projeto = (isset($data['m_Projeto'])) ? $data['m_Projeto'] .' - '. $data['p_desc_projeto'] : null;
        $this->form_elemento = (isset($data['m_Elemento'])) ? $data['m_Elemento'] .' - '. $data['d_desc'] : null;
        $this->form_fonte = (isset($data['m_Fonte'])) ? $data['m_Fonte'] .' - '. $data['f_desc'] : null;
        $this->form_execprev = (isset($data['e_execprev'])) ? number_format($data['e_execprev'], 2, ',', '.') : null;
        $this->form_ExecOrcPlan = (isset($data['m_execorcplan'])) ? number_format($data['m_execorcplan'], 2, ',', '.') : null;
        $this->form_OrcExec = (isset($data['m_execorcreal'])) ? number_format($data['m_execorcreal'], 2, ',', '.') : null;
        $this->form_metaprev = (isset($data['p_meta'])) ? $data['p_meta'] : null;
        $this->form_metarealizada = (isset($data['m_MetaRealizada'])) ? $data['m_MetaRealizada'] : null;
        $this->form_mesano = (isset($data['m_periodo'])) ? date("m/Y", strtotime($data['m_periodo'])) : null;
        $this->form_DesAc = (isset($data['m_DesAc'])) ? $data['m_DesAc'] : null;
        $this->form_ResExec = (isset($data['m_ResExec'])) ? $data['m_ResExec'] : null;
        
        /**
         * Registro de campos do banco de dados 
         * Necessários para consultas e criação de páginas independentes
         */
        $this->u_id = (isset($data['u_id'])) ? $data['u_id'] : null;
        $this->u_email = (isset($data['u_email'])) ? $data['u_email'] : null;
        //$this->u_password = (isset($data['u_password'])) ? $data['u_password'] : null;
        $this->u_tentativas = (isset($data['u_tentativas'])) ? $data['u_tentativas'] : null;
        $this->u_login_attempt_time = (isset($data['u_login_attempt_time'])) ? $data['u_login_attempt_time'] : null;
        $this->u_first_name = (isset($data['u_first_name'])) ? $data['u_first_name'] : null;
        $this->u_last_name = (isset($data['u_last_name'])) ? $data['u_last_name'] : null;
        $this->u_orgaos = (isset($data['u_orgaos'])) ? $data['u_orgaos'] : null;
        $this->u_status = (isset($data['u_status'])) ? $data['u_status'] : null;
        $this->u_last_signed_in = (isset($data['u_last_signed_in'])) ? $data['u_last_signed_in'] : null;
        $this->ur_id = (isset($data['ur_id'])) ? $data['ur_id'] : null;
        $this->ur_user_id = (isset($data['ur_user_id'])) ? $data['ur_user_id'] : null;
        $this->r_rid = (isset($data['r_rid'])) ? $data['r_rid'] : null;
        $this->r_nome = (isset($data['r_nome'])) ? $data['r_nome'] : null;
        $this->r_status = (isset($data['r_status'])) ? $data['r_status'] : null;
        $this->per_id = (isset($data['per_id'])) ? $data['per_id'] : null;
        $this->per_nome = (isset($data['per_nome'])) ? $data['per_nome'] : null;
        $this->per_resource = (isset($data['per_resource'])) ? $data['per_resource'] : null;
        $this->re_id = (isset($data['re_id'])) ? $data['re_id'] : null;
        $this->re_nome = (isset($data['re_nome'])) ? $data['re_nome'] : null;

    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'form_codigo',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'form_projeto',
                'required' => true
            )));
        
            $this->inputFilter = $inputFilter;        
        }
        return $this->inputFilter;
    }

}
