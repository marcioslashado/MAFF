<?php

namespace Maff\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Meta implements InputFilterAwareInterface {

    protected $inputFilter;
    public $e_seq;
    public $e_mes;
    public $e_projeto;
    public $e_orgao;
    public $e_elemento;
    public $e_fonte;
    public $e_orcprev;
    public $e_orcrel;
    public $desc_proj;
    public $id_orgao;
    public $o_desc_orgao;
    public $d_desc_elemento;
    public $f_desc_fonte;
    public $m_codigo;
    public $m_projeto;
    public $m_metarealizada;
    public $m_execorcplan;
    public $m_execorcreal;
    public $m_desac;
    public $m_resexec;
    public $m_periodo;
    public $p_desc;
    public $prio_periodo;
    public $estado_orc;
    public $estado_meta;
    public $e_execano;
    
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
        $this->e_seq = (isset($data['e_sequencial'])) ? $data['e_sequencial'] : null;
        $this->e_mes = (isset($data['e_mes'])) ? $data['e_mes'] : null;
        $this->e_projeto = (isset($data['e_projeto'])) ? $data['e_projeto'] : null;
        $this->desc_proj = (isset($data['desc_projeto'])) ? $data['desc_projeto'] : null;
        $this->e_orgao = (isset($data['e_orgaoSefin'])) ? $data['e_orgaoSefin'] : null;
        $this->o_id_orgao = (isset($data['o_unorc'])) ? $data['o_unorc'] : null;
        $this->o_desc_orgao = (isset($data['o_descricao'])) ? $data['o_descricao'] : null;
        $this->e_elemento = (isset($data['e_elemento'])) ? $data['e_elemento'] : null;
        $this->d_desc_elemento = (isset($data['d_descricao'])) ? $data['d_descricao'] : null;
        $this->f_desc_fonte = (isset($data['f_descricao'])) ? $data['f_descricao'] : null;
        $this->e_fonte = (isset($data['e_fonte'])) ? $data['e_fonte'] : null;
        $this->e_orcprev = (isset($data['e_ExecPrevista'])) ? $data['e_ExecPrevista'] : null;
        $this->e_orcrel = (isset($data['e_ExecRel'])) ? $data['e_ExecRel'] : null;
        $this->m_codigo = (isset($data['m_Codigo'])) ? $data['m_Codigo'] : null;
        $this->m_projeto = (isset($data['m_Projeto'])) ? $data['m_Projeto'] : null;
        $this->m_metarealizada = (isset($data['m_MetaRealizada'])) ? $data['m_MetaRealizada'] : null;
        $this->p_meta = (isset($data['p_meta'])) ? $data['p_meta'] : null;
        $this->e_execprev = (isset($data['e_execprev'])) ? $data['e_execprev'] : null;
        $this->e_execano = (isset($data['e_execano'])) ? $data['e_execano'] : null;
        $this->m_execorcplan = (isset($data['m_execorcplan'])) ? $data['m_execorcplan'] : null;
        $this->m_execorcreal = (isset($data['m_execorcreal'])) ? $data['m_execorcreal'] : null;
        $this->m_desac = (isset($data['m_DesAc'])) ? $data['m_DesAc'] : null;
        $this->m_resexec = (isset($data['m_ResExec'])) ? $data['m_ResExec'] : null;
        $this->m_periodo = (isset($data['m_periodo'])) ? date("m/Y", strtotime($data['m_periodo'])) : null;
        $this->p_desc = (isset($data['p_desc_projeto'])) ? $data['p_desc_projeto'] : null;
        $this->prio_periodo = (isset($data['m_periodo'])) ? date("m/Y", strtotime(array_shift(explode(',', $data['m_periodo'])))) . ' até ' . date("m/Y", strtotime(array_pop(explode(',', $data['m_periodo'])))) : null;
        //$this->prio_periodo = $data['m_periodo'];

        //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for < que 25...
        if ($this->m_execorcplan) {
            if (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) < 25.0) {
                $this->estado_orc = '<span class="badge badge-important">' . number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
                //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for > ou = que 25 mas < que 50...
            } elseif (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) >= 25.0 AND number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) <= 50.0) {
                $this->estado_orc = '<span class="badge badge-warning">' . number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
                //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for > que 50...
            } elseif (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) >= 50.0 AND number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) <= 100.0) {
                $this->estado_orc = '<span class="badge badge-success">' . number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
                //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for > que 100...
            } elseif (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) > 100.0) {
                $this->estado_orc = '<span class="badge badge-inverse">' . number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
            }
        }

        if ($this->p_meta) {
            if (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) < 25.0) {
                $this->estado_meta = '<span class="badge badge-important">' . number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            } elseif (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) >= 25.0 AND number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) <= 50.0) {
                $this->estado_meta = '<span class="badge badge-warning">' . number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            } elseif (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) >= 50.0 AND number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) <= 100.0) {
                $this->estado_meta = '<span class="badge badge-success">' . number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            } elseif (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) > 100.0) {
                $this->estado_meta = '<span class="badge badge-inverse">' . number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            }
        }
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
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_elemento',
            'required' => true
        )));
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_fonte',
            'required' => true
        )));
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_execprev',
            'required' => false
        )));
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_ExecOrcPlan',
            'required' => true
        )));
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_OrcExec',
            'required' => true
        )));
        
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_metaprev',
            'required' => false
        )));
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_metarealizada',
            'required' => true
        )));
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_mesano',
            'required' => true
        )));
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_DesAc',
            'required' => true
        )));
        $inputFilter->add($factory->createInput(array(
            'name' => 'form_ResExec',
            'required' => true
        )));
            $this->inputFilter = $inputFilter;        
        }
        return $this->inputFilter;
    }

}
