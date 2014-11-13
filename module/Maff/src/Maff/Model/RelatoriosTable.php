<?php

namespace Maff\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\View\Model\JsonModel;

class RelatoriosTable extends TableGateway {

    public function __construct(Adapter $adapter) {
        $this->table = '';
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Relatorios());

        $this->initialize();
    }

    public function getOrgaos() {
        $sql = new Sql($this->adapter);
        $select = new Select(array('p' => 'projeto'));
        $select->columns(array(
                    'p_orgaoSefin' => 'orgaoSefin',
                    'p_codigo' => 'codigo',
                ))
                ->join(array('o' => 'orgao'), 'o.orgaoSefin = p.orgaoSefin', array(
                    'o_unorc' => 'unorc',
                    'o_desc' => 'descricao'
                ))
                ->join(array('m' => 'metafisicarealizada'), 'm.Projeto = p.codigo', array(
                    'm_projeto' => 'Projeto',
                ))
                ->group(array('p_orgaoSefin'));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        return $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
    }
    
    public function getProjetos($id) {
        $id = $id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('p' => 'projeto'));
        $select->columns(array(
                    'p_codigo' => 'codigo',
                    'p_descricao' => 'descricao',
                ))
                ->join(array('m' => 'metafisicarealizada'), 'm.Projeto = p.codigo', array(
                    'm_projeto' => 'Projeto',
                ))
                ->group(array('m_projeto'))
                ->where(array('unorc' => $id));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        $selectData[] = array('valor' => '', 'label' => ':: Selecione um Projeto ::');
        foreach ($retorno as $res) {
            $selectData[] = array('valor' => $res['p_codigo'], 'label' => $res['p_codigo'] . ' - ' . $res['p_descricao']);
        }
        return new JsonModel($selectData);
    }
    
    public function getElementos($id) {
        $id = $id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('m' => 'metafisicarealizada'));
        $select->columns(array(
                    'm_eldespesa' => 'el_despesa',
                    'm_projeto' => 'Projeto',
                ))
                ->join(array('d' => 'eldespesa'), 'd.codigo = m.el_despesa', array(
                    'd_codigo' => 'codigo',
                    'd_desc' => 'descricao'
                ))
                ->group(array('m_eldespesa'))
                ->where(array('m.Projeto' => $id));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        $selectData[] = array('valor' => '', 'label' => ':: Selecione um Elemento ::');
        foreach ($retorno as $res) {
            $selectData[] = array('valor' => $res['m_eldespesa'], 'label' => $res['m_eldespesa'] . ' - ' . $res['d_desc']);
        }
        return new JsonModel($selectData);
    }
    
    public function getFontes($id) {
        $id = $id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('m' => 'metafisicarealizada'));
        $select->columns(array(
                    'm_eldespesa' => 'el_despesa',
                    'm_fonte' => 'fonte',
                ))
                ->join(array('f' => 'fonte'), 'f.codigo = m.fonte', array(
                    'f_codigo' => 'codigo',
                    'f_desc' => 'descricao'
                ))
                ->group(array('m_fonte'))
                ->where(array('m.el_despesa' => $id));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        $selectData[] = array('valor' => '', 'label' => ':: Selecione uma Fonte ::');
        foreach ($retorno as $res) {
            $selectData[] = array('valor' => $res['f_codigo'], 'label' => $res['f_codigo'] . ' - ' . $res['f_desc']);
        }
        return new JsonModel($selectData);
    }
    
    public function getPeriodo($proj_id, $el_id, $fonte_id) {
        $proj_id = $proj_id;
        $el_id = $el_id;
        $fonte_id = $fonte_id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('m' => 'metafisicarealizada'));
        $select->columns(array(
                    'm_Projeto' => 'Projeto',
                    'm_eldespesa' => 'el_despesa',
                    'm_fonte' => 'fonte',
                    'm_periodo' => new \Zend\Db\Sql\Expression('group_concat(periodo ORDER BY periodo ASC)'),
                ))
                ->where(array('m.Projeto' => $proj_id, 'm.el_despesa' => $el_id, 'm.fonte' => $fonte_id))
                ->order('m.periodo ASC'); 

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        foreach ($retorno as $res) {
            $selectData[] = array(
                    'inidata' => date("m/Y", strtotime(array_shift(explode(',', $res['m_periodo'])))), 
                    'fimdata' => date("m/Y", strtotime(array_pop(explode(',', $res['m_periodo'])))), 
                );
        }
        return new JsonModel($selectData);
    }

    public function getRelatorios($post) {
        $unorc = filter_input(INPUT_POST, 'unorc');
        $projeto = filter_input(INPUT_POST, 'form_projeto');
        $elemento = filter_input(INPUT_POST, 'form_elemento');
        $fonte = filter_input(INPUT_POST, 'form_fonte');
        $inidata = date('d/m/Y', strtotime('01/'.filter_input(INPUT_POST, 'inidata')));
        $fimdata = date('d/m/Y', strtotime('01/'.filter_input(INPUT_POST, 'fimdata')));
        
        $this->table = array('m' => 'metafisicarealizada'); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select = new Select();
        $select->from(array('m' => 'metafisicarealizada')); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select->columns(array(
                    //'nome_renomeado' => 'nome_campo_db'
                    'm_Codigo' => 'Codigo',
                    'm_Projeto' => 'Projeto',
                    'm_eldespesa' => 'el_despesa',
                    'm_fonte' => 'fonte',
                    'm_MetaRealizada' => new \Zend\Db\Sql\Expression('SUM(MetaRealizada)'), //Soma as Metafísicas realizadas
                    'm_execorcplan' => new \Zend\Db\Sql\Expression('SUM(execorcplan)'), //Soma Orçamento Planejado 
                    'm_orcplanconcat' => new \Zend\Db\Sql\Expression('group_concat(execorcplan)'), //Concatena Orçamento Planejado 
                    'm_execorcreal' => new \Zend\Db\Sql\Expression('SUM(execorcreal)'), //Soma Orçamento Realizado
                    'm_orcrealconcat' => new \Zend\Db\Sql\Expression('group_concat(execorcreal)'), //Concatena Orçamento Realizado
                    'm_DesAc' => new \Zend\Db\Sql\Expression('group_concat(DesAc SEPARATOR \'/--/\')'),
                    'm_ResExec' => new \Zend\Db\Sql\Expression('group_concat(ResExec SEPARATOR \'/--/\')'),
                    'm_periodo' => new \Zend\Db\Sql\Expression('group_concat(periodo ORDER BY periodo ASC)'), //Concatena o Período
                ))
                ->join(array('p' => 'projeto'), 'm.Projeto = p.codigo', array(
                    'p_projeto' => 'codigo',
                    'p_descprojeto' => 'descricao',
                    'p_meta' => 'metaFisica',
                    'p_programa' => 'programa',
                    'p_acao' => 'acao',
                    'p_unorc' => 'unorc',
                    'p_funcao' => 'funcao', 
                    'p_subFuncao' => 'subFuncao',
                    'p_esfera' => 'esfera',
                    'p_locali' => 'locali',
                    'p_regional' => 'regional',
                ))
                ->join(array('e' => 'execorc'), 'm.Projeto = e.projeto AND m.el_despesa = e.elemento AND m.fonte = e.fonte', array(
                    'e_execano' => 'vEmpAno',
                    'e_execprev' => 'vDotAtual',
                    'e_execprevconcat' => new \Zend\Db\Sql\Expression('group_concat(vDotAtual)'), //Concatena Orçamento Previsto 
                ))
                ->join(array('el' => 'eldespesa'), 'el.codigo = m.el_despesa', array(
                    'el_codigo' => 'codigo',
                    'el_desc' => 'descricao',
                ))
                ->join(array('ft' => 'fonte'), 'ft.codigo = m.fonte', array(
                    'ft_codigo' => 'codigo',
                    'ft_desc' => 'descricao',
                ))
                ->join(array('pr' => 'programa'), 'pr.codigo = p.programa', array(
                    'pr_codigo' => 'codigo',
                    'pr_desc' => 'descricao',
                ))
                ->join(array('ac' => 'acao'), 'ac.codigo = p.acao', array(
                    'ac_codigo' => 'codigo',
                    'ac_desc' => 'descricao',
                ))
                ->join(array('un' => 'orgao'), 'un.unorc = p.unorc', array(
                    'un_unorc' => 'unorc',
                    'un_desc' => 'descricao',
                ))
                ->join(array('f' => 'funcao'), 'f.codigo = p.funcao', array(
                    'f_codigo' => 'codigo',
                    'f_desc' => 'descricao',
                ))
                ->join(array('sf' => 'subfuncao'), 'sf.codigo = p.subFuncao', array(
                    'sf_codigo' => 'codigo',
                    'sf_desc' => 'descricao',
                ))
                ->where(array(
                    'm.Projeto' => $projeto, 
                    'm.el_despesa' => $elemento, 
                    'm.fonte' => $fonte,
                    'm.periodo >= "'.date('Y-m-d', strtotime($inidata)).'"', 
                    'm.periodo <= "'.date('Y-m-d', strtotime($fimdata)).'"',
                ))
                ->order(new \Zend\Db\Sql\Expression('STR_TO_DATE(m.periodo, "%d/%m/%Y")'));
        
        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }
}
