<?php

namespace Maff\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\View\Model\JsonModel;

class MetaTable extends AbstractTableGateway {

    protected $table = 'metafisicarealizada';
    
    public function __construct(Adapter $adapter) {
        //$this->table = '';
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Meta());

        $this->initialize();
    }

    public function fetchPrioridades(Select $select = null) {
        $prioridades = '247,1000,1'; //Pode ser dados de uma tabela agrupados em array (group_concat)
        $this->table = array('m' => 'metafisicarealizada'); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select = new Select();
        $select->from(array('m' => 'metafisicarealizada')); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select
                ->columns(array(
                    //'nome_renomeado' => 'nome_campo_db'
                    'm_Codigo' => 'Codigo',
                    'm_Projeto' => 'Projeto',
                    'm_MetaRealizada' => new \Zend\Db\Sql\Expression('SUM(MetaRealizada)'), //Soma as Metafísicas realizadas
                    'm_execorcplan' => new \Zend\Db\Sql\Expression('SUM(execorcplan)'), //Soma Orçamento Planejado 
                    'm_execorcreal' => new \Zend\Db\Sql\Expression('SUM(execorcreal)'), //Soma Orçamento Realizado
                    'm_DesAc' => 'DesAc',
                    'm_ResExec' => 'ResExec',
                    'm_periodo' => new \Zend\Db\Sql\Expression('group_concat(periodo ORDER BY periodo ASC)'),
                ))
                ->join(array('p' => 'projeto'), 'p.codigo = m.Projeto', array(
                    'p_projeto' => 'codigo',
                    'p_desc_projeto' => 'descricao',
                    'p_meta' => 'metaFisica'
                ))
                ->join(array('e' => 'execorc'), 'e.projeto = m.Projeto AND e.elemento = m.el_despesa AND e.fonte = m.fonte', array(
                    'e_execano' => 'vEmpAno',
                    'e_execprev' => 'vDotAtual'
                ))
                ->group(array('m_Projeto'))
                ->where('m.Projeto IN(' . $prioridades . ')');

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }

    //Fetch de Execuções Orçamentárias
    public function fetchExecorcs(Select $select = null) {
        $this->table = 'execorc';
        if (null === $select)
            $select = new Select();
        $select->from('execorc');
        $select
                ->columns(array(
                    //'nome_renomeado' => 'nome_campo_db'
                    'e_sequencial' => 'sequencial',
                    'e_mes' => 'mes',
                    'e_projeto' => 'projeto',
                    'e_elemento' => 'elemento',
                    'e_fonte' => 'fonte',
                    'e_orgaoSefin' => 'orgaoSefin',
                    'e_ExecPrevista' => 'vDotAtual',
                    'e_ExecRel' => 'vEmpAno'
                ))
                ->join('projeto', 'projeto.codigo = execorc.projeto', array(
                    'p_projeto' => 'codigo',
                    'desc_projeto' => 'descricao'
                ))
                ->join('orgao', 'orgao.orgaoSefin = execorc.orgaoSefin', array(
                    'o_orgaoSefin' => 'orgaoSefin',
                    'o_unorc' => 'unorc',
                    'o_descricao' => 'descricao'
                ))
                ->join('eldespesa', 'eldespesa.codigo = execorc.elemento', array(
                    'd_codigo' => 'codigo',
                    'd_descricao' => 'descricao'
                ))->join('fonte', 'fonte.codigo = execorc.fonte', array(
                    'f_codigo' => 'codigo',
                    'f_descricao' => 'descricao'
                ));

        //echo $select->getSqlString(); 
        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }

    //Crud Metafísicas 
    public function fetchMetas(Select $select = null) {
        $this->table = array('m' => 'metafisicarealizada'); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select = new Select();
        $select->from(array('m' => 'metafisicarealizada')); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select
                ->columns(array(
                    //'nome_renomeado' => 'nome_campo_db'
                    'm_Codigo' => 'Codigo',
                    'm_Projeto' => 'Projeto',
                    'm_MetaRealizada' => 'MetaRealizada',
                    'm_execorcplan' => 'execorcplan',
                    'm_execorcreal' => 'execorcreal',
                    'm_DesAc' => 'DesAc',
                    'm_ResExec' => 'ResExec',
                    'm_periodo' => 'periodo'
                ))
                ->join(array('p' => 'projeto'), 'p.codigo = m.Projeto', array(
                    'p_projeto' => 'codigo',
                    'p_desc_projeto' => 'descricao',
                    'p_meta' => 'metaFisica'
                ))
                ->join(array('e' => 'execorc'), 'e.projeto = m.Projeto AND e.elemento = m.el_despesa AND e.fonte = m.fonte', array(
                    'e_execprev' => 'vDotAtual'
                ))
                ->group(array('m_Codigo'));

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }

    public function saveMeta($meta) {
        $periodo = date('d/m/Y', strtotime("01/".$meta->form_mesano)); //Tratando data
        $id = $meta->form_codigo;
        $sql = new Sql($this->adapter);
        $data = array(
            'Projeto' => $meta->form_projeto,
            'el_despesa' => $meta->form_elemento,
            'fonte' => $meta->form_fonte,
            'MetaRealizada' => $meta->form_metarealizada,
            'execorcplan' => str_replace('.', '', $meta->form_ExecOrcPlan),
            'execorcreal' => str_replace('.', '', $meta->form_OrcExec),
            'DesAc' => $meta->form_DesAc,
            'ResExec' => $meta->form_ResExec,
            'periodo' => date('Y-m-d', strtotime($periodo)),
        );
        if ($id == 0) {
            $query = $sql->insert('metafisicarealizada');
            $query->values($data);
            $selectString = $sql->getSqlStringForSqlObject($query);
            $results = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            return $results;
        } else {
            if ($this->getMeta($id)) {
                $data = array(
                    'MetaRealizada' => $meta->form_metarealizada,
                    'execorcplan' => str_replace('.', '', $meta->form_ExecOrcPlan),
                    'execorcreal' => str_replace('.', '', $meta->form_OrcExec),
                    'DesAc' => $meta->form_DesAc,
                    'ResExec' => $meta->form_ResExec,
                    'periodo' => date('Y-m-d', strtotime($periodo)),
                );
                $query = $sql->update('metafisicarealizada');
                //$this->update($data, array('m.Codigo' => $id));
                $query->set($data);
                $query->where(array('Codigo' => $id));
                //$query->where(array('Codigo' => $id));

                $selectString = $sql->getSqlStringForSqlObject($query);
                $results = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
                return $results;
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
    
    public function deleteMeta($id)
    {
        $this->delete(array('Codigo' => $id));
    }

    public function getMeta($id) {
        $id = $id;
        $this->table = array('m' => 'metafisicarealizada'); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select = new Select();
        $select->from(array('m' => 'metafisicarealizada')); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select
                ->columns(array(
                    //'nome_renomeado' => 'nome_campo_db'
                    'm_Codigo' => 'Codigo',
                    'm_Projeto' => 'Projeto',
                    'm_Elemento' => 'el_despesa',
                    'm_Fonte' => 'fonte',
                    'm_MetaRealizada' => 'MetaRealizada',
                    'm_execorcplan' => 'execorcplan',
                    'm_execorcreal' => 'execorcreal',
                    'm_DesAc' => 'DesAc',
                    'm_ResExec' => 'ResExec',
                    'm_periodo' => 'periodo'
                ))
                ->join(array('p' => 'projeto'), 'p.codigo = m.Projeto', array(
                    'p_projeto' => 'codigo',
                    'p_desc_projeto' => 'descricao',
                    'p_meta' => 'metaFisica'
                ))
                ->join(array('d' => 'eldespesa'), 'd.codigo = m.el_despesa', array(
                    'd_codigo' => 'codigo',
                    'd_desc' => 'descricao'
                ))
                ->join(array('f' => 'fonte'), 'f.codigo = m.fonte', array(
                    'f_codigo' => 'codigo',
                    'f_desc' => 'descricao'
                ))
                ->join(array('e' => 'execorc'), 'e.projeto = m.Projeto AND e.elemento = m.el_despesa AND e.fonte = m.fonte', array(
                    'e_execprev' => 'vDotAtual'
                ))
                ->where(array('m.Codigo' => $id));

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $row = $result->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getOrgaos() {
        $orgao = $_SESSION['User']['userOrgaos'];
        
        $sql = new Sql($this->adapter);
        $select = new Select(array('p' => 'projeto'));
        $select->columns(array(
                    'p_unorc' => 'unorc',
                    'p_orgaoSefin' => 'orgaoSefin',
                ))
                ->join(array('o' => 'orgao'), 'o.orgaoSefin = p.orgaoSefin', array(
                    'o_unorc' => 'unorc',
                    'o_desc' => 'descricao'
                ));

                if($orgao == 11111){
                   $select->group(array('p_orgaoSefin'));
                }
                else {
                   $select->group(array('p_orgaoSefin')); 
                   $select->where('p.unorc IN(' . $orgao . ')'); 
                }

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        return $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
    }

    public function getProjetos($id) {
        $id = $id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('p' => 'projeto'));
        $select->group(array('codigo'))
                ->where(array('unorc' => $id));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        $selectData[] = array('valor' => '', 'label' => ':: Selecione um Projeto ::');
        foreach ($retorno as $res) {
            $selectData[] = array('valor' => $res['codigo'], 'label' => $res['codigo'] . ' - ' . $res['descricao']);
        }
        return new JsonModel($selectData);
    }

    public function getElementos($id) {
        $id = $id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('e' => 'execorc'));
        $select->columns(array(
                    'e_projeto' => 'projeto',
                    'e_elemento' => 'elemento',
                ))
                ->join(array('d' => 'eldespesa'), 'd.codigo = e.elemento', array(
                    'd_codigo' => 'codigo',
                    'd_desc' => 'descricao'
                ))
                ->join(array('p' => 'projeto'), 'p.codigo = e.projeto', array(
                    'p_meta' => 'metaFisica'
                ))
                ->group(array('e_elemento'))
                ->where(array('e.projeto' => $id));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        $selectData[] = array('valor' => '', 'label' => ':: Selecione um Elemento ::');
        foreach ($retorno as $res) {
            $selectData[] = array('valor' => $res['d_codigo'], 'label' => $res['d_codigo'] . ' - ' . $res['d_desc'], 'meta' => $res['p_meta']);
        }
        return new JsonModel($selectData);
    }

    public function getFontes($id) {
        $id = $id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('e' => 'execorc'));
        $select->columns(array(
                    'e_elemento' => 'elemento',
                    'e_fonte' => 'fonte',
                    'e_orcprev' => 'vDotAtual',
                ))
                ->join(array('f' => 'fonte'), 'f.codigo = e.fonte', array(
                    'f_codigo' => 'codigo',
                    'f_desc' => 'descricao'
                ))
                ->group(array('e.elemento'))
                ->where(array('e.elemento' => $id));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        $selectData[] = array('valor' => '', 'label' => ':: Selecione uma Fonte ::');
        foreach ($retorno as $res) {
            $selectData[] = array('valor' => $res['f_codigo'], 'label' => $res['f_codigo'] . ' - ' . $res['f_desc'], 'orcprev' => $res['e_orcprev']);
        }
        return new JsonModel($selectData);
    }

    public function getOrc($proj_id, $el_id, $fonte_id) {
        $proj_id = $proj_id;
        $el_id = $el_id;
        $fonte_id = $fonte_id;
        $sql = new Sql($this->adapter);
        $select = new Select(array('e' => 'execorc'));
        $select->columns(array(
                    'e_orcprev' => 'vDotAtual',
                ))
                ->where(array('e.projeto' => $proj_id, 'e.elemento' => $el_id, 'e.fonte' => $fonte_id));

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        foreach ($retorno as $res) {
            $selectData[] = array('orcprev' => number_format($res['e_orcprev'], 2, ',', '.'));
        }
        return new JsonModel($selectData);
    }

}
