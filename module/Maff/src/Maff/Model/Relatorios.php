<?php

namespace Maff\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Relatorios implements InputFilterAwareInterface {

    protected $inputFilter;
    /**
     * @metafisicarealizada
     */
    public $m_codigo;
    public $m_projeto;
    public $m_eldespesa;
    public $m_fonte;
    public $m_metarealizada;
    public $m_execorcplan;
    public $m_execorcreal;
    public $m_orcplanconcat;
    public $m_orcrealconcat;
    public $m_desac;
    public $m_resexec;
    public $m_periodo;
    public $m_periododesc;
    
    /**
     * @projeto
     */
    public $p_projeto;
    public $p_descprojeto;
    public $p_meta;
    public $p_programa;
    public $p_acao;
    public $p_unorc;
    public $p_funcao;
    public $p_subFuncao;
    public $p_esfera;
    public $p_locali;
    public $p_regional;
    
    /**
     * @execorc 
     */
    public $e_execano;
    public $e_execprev;
    public $e_execprevconcat;
    
    /**
     * @eldespesa 
     */
    public $el_codigo;
    public $el_desc;
    
    /**
     * @programa
     */
    public $pr_codigo;
    public $pr_desc;
    
    /**
     * @acao
     */
    public $ac_codigo;
    public $ac_desc;
    
    /**
     * @orgao 
     */
    public $un_unorc;
    public $un_desc;
    
    /**
     * @funcao 
     */
    public $f_codigo;
    public $f_desc;
    
    /**
     * @subfuncao 
     */
    public $sf_codigo;
    public $sf_desc;
    
    /**
     * @fonte 
     */
    public $ft_codigo;
    public $ft_desc;
    
    public $estado_orc;
    public $estado_meta;

    /**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data) {
        /**/
        $this->m_codigo = (isset($data['m_Codigo'])) ? $data['m_Codigo'] : null;
        $this->m_projeto = (isset($data['m_Projeto'])) ? $data['m_Projeto'] : null;
        $this->m_eldespesa = (isset($data['m_eldespesa'])) ? $data['m_eldespesa'] : null;
        $this->m_fonte = (isset($data['m_fonte'])) ? $data['m_fonte'] : null;
        $this->m_metarealizada = (isset($data['m_MetaRealizada'])) ? $data['m_MetaRealizada'] : null;
        $this->m_execorcplan = (isset($data['m_execorcplan'])) ? $data['m_execorcplan'] : null;
        $this->m_execorcreal = (isset($data['m_execorcreal'])) ? $data['m_execorcreal'] : null;
        $this->m_orcplanconcat = (isset($data['m_orcplanconcat'])) ? $data['m_orcplanconcat'] : null;
        $this->m_orcrealconcat = (isset($data['m_orcrealconcat'])) ? $data['m_orcrealconcat'] : null;
        $this->m_desac = (isset($data['m_DesAc'])) ? $data['m_DesAc'] : null;
        $this->m_resexec = (isset($data['m_ResExec'])) ? $data['m_ResExec'] : null;
        $this->m_periodo = (isset($data['m_periodo'])) ? $data['m_periodo'] : null;
        $this->m_periododesc = date("m/Y", strtotime(array_shift(explode(',', $data['m_periodo'])))) . ' até ' . date("m/Y", strtotime(array_pop(explode(',', $data['m_periodo']))));
        
        $this->p_projeto = (isset($data['p_projeto'])) ? $data['p_projeto'] : null;
        $this->p_descprojeto = (isset($data['p_descprojeto'])) ? $data['p_descprojeto'] : null;
        $this->p_meta = (isset($data['p_meta'])) ? $data['p_meta'] : null;
        $this->p_programa = (isset($data['p_programa'])) ? $data['p_programa'] : null;
        $this->p_acao = (isset($data['p_acao'])) ? $data['p_acao'] : null;
        $this->p_unorc = (isset($data['p_unorc'])) ? $data['p_unorc'] : null;
        $this->p_funcao = (isset($data['p_funcao'])) ? $data['p_funcao'] : null;
        $this->p_subFuncao = (isset($data['p_subFuncao'])) ? $data['p_subFuncao'] : null;
        $this->p_esfera = (isset($data['p_esfera'])) ? $data['p_esfera'] : null;
        $this->p_locali = (isset($data['p_locali'])) ? $data['p_locali'] : null;
        $this->p_regional = (isset($data['p_regional'])) ? $data['p_regional'] : null;
        
        $this->e_execprev = (isset($data['e_execprev'])) ? $data['e_execprev'] : null;
        $this->e_execprevconcat = (isset($data['e_execprevconcat'])) ? $data['e_execprevconcat'] : null;
        $this->e_execano = (isset($data['e_execano'])) ? $data['e_execano'] : null;
        
        $this->el_codigo = (isset($data['el_codigo'])) ? $data['el_codigo'] : null;
        $this->el_desc = (isset($data['el_desc'])) ? $data['el_desc'] : null;
        
        $this->ft_codigo = (isset($data['ft_codigo'])) ? $data['ft_codigo'] : null;
        $this->ft_desc = (isset($data['ft_desc'])) ? $data['ft_desc'] : null;
        
        $this->pr_codigo = (isset($data['pr_codigo'])) ? $data['pr_codigo'] : null;
        $this->pr_desc = (isset($data['pr_desc'])) ? $data['pr_desc'] : null;
        
        $this->ac_codigo = (isset($data['ac_codigo'])) ? $data['ac_codigo'] : null;
        $this->ac_desc = (isset($data['ac_desc'])) ? $data['ac_desc'] : null;
        
        $this->un_unorc = (isset($data['un_unorc'])) ? $data['un_unorc'] : null;
        $this->un_desc = (isset($data['un_desc'])) ? $data['un_desc'] : null;
        
        $this->f_codigo = (isset($data['f_codigo'])) ? $data['f_codigo'] : null;
        $this->f_desc = (isset($data['f_desc'])) ? $data['f_desc'] : null;
        
        $this->sf_codigo = (isset($data['sf_codigo'])) ? $data['sf_codigo'] : null;
        $this->sf_desc = (isset($data['sf_desc'])) ? $data['sf_desc'] : null;

        //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for < que 25...
        if ($this->m_execorcplan) {
            if (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) < 25.0) {
                $this->estado_orc = '<span class="badge badge-important">' . \number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
                //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for > ou = que 25 mas < que 50...
            } elseif (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) >= 25.0 AND \number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) <= 50.0) {
                $this->estado_orc = '<span class="badge badge-warning">' . \number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
                //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for > que 50...
            } elseif (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) >= 50.0 AND \number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) <= 100.0) {
                $this->estado_orc = '<span class="badge badge-success">' . \number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
                //Se Exec. Orç. Realizada / Se Exec. Orç. Planejada for > que 100...
            } elseif (number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) > 100.0) {
                $this->estado_orc = '<span class="badge badge-inverse">' . \number_format((($this->m_execorcreal / $this->m_execorcplan) * 100), 1) . '%</span>';
            }
        }

        if ($this->p_meta) {
            if (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) < 25.0) {
                $this->estado_meta = '<span class="badge badge-important">' . \number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            } elseif (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) >= 25.0 AND \number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) <= 50.0) {
                $this->estado_meta = '<span class="badge badge-warning">' . \number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            } elseif (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) >= 50.0 AND \number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) <= 100.0) {
                $this->estado_meta = '<span class="badge badge-success">' . \number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            } elseif (number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) > 100.0) {
                $this->estado_meta = '<span class="badge badge-inverse">' . \number_format((($this->m_metarealizada / $this->p_meta) * 100), 1) . '%</span>';
            }
        }
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
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}
