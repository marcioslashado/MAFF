<?php

namespace Grupos\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

class GruposForm extends Form {

    protected $adapter;

    public function __construct(AdapterInterface $dbAdapter) {
        $this->adapter = $dbAdapter;

        // we want to ignore the name passed
        parent::__construct('grupos');

        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'form_grupos',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name' => 'grupo_titulo',
            'attributes' => array(
                'type' => 'text',
                'id' => 'grupo_titulo',
                'class' => 'form-control input-sm',
                'placeholder' => 'Título do Grupo',
                'required' => 'required',
            ),
        ));

        $this->add(array(
            'name' => 'grupo_modulo',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'grupo_modulo',
                'multiple' => 'multiple',
                'class' => 'form-control input-sm',
                'required' => 'required',
                'value' => '', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                'value_options' => $this->getOptionsForModulos(),
                'empty_options' => 'Please Select'
            ),
        ));

        $this->add(array(
            'name' => 'modulo_acoes',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'modulo_acoes',
                'multiple' => 'multiple',
                'class' => 'form-control input-sm',
                'value' => '', //Mantém selecionado o valor '1'
            ),
            'options' => array(
                //'label' => 'Drop Down', 
                'value_options' => array(
                    '' => ':: Selecione as Ações ::',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'modulo_status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'modulo_status',
                'class' => 'form-control input-sm',
                'Active' => 'Ativo', //Mantém selecionado o valor '1'
            ),
            'options' => array(
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

    public function getOptionsForModulos() {
        $sql = new Sql($this->adapter);
        $select = new Select(array('p' => 'permission'));
        $select->columns(array(
                    'p_id' => 'id',
                    'p_nome' => 'permission_name',
                    'p_res_id' => 'resource_id',
                    'p_amigavel' => 'n_amigavel',
                ))
                ->join(array('r' => 'resource'), 'r.id = p.resource_id', array(
                    'r_id' => 'id',
                    'r_nome' => 'resource_name',
                    'n_amigavel' => 'n_amigavel',
        ));

        $selectString = $sql->getSqlStringForSqlObject($select);
        $retorno = $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $selectData = array();
        foreach ($retorno as $res) {
            if (!isset($selectData[$res['p_res_id']])) {
                $selectData[$res['p_res_id']] = array('label' => $res['n_amigavel'], 'options' => array($res['p_nome'] => $res['p_amigavel']));
            } else {
                $selectData[$res['p_res_id']]['options'][$res['p_nome']] = $res['p_amigavel'];
            }
        }
        //print_r($selectData);
        return $selectData;
    }
}
