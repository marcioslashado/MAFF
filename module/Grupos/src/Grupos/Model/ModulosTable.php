<?php

namespace Grupos\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\View\Model\JsonModel;

class ModulosTable extends TableGateway {

    public function __construct(Adapter $adapter) {
        $this->table = '';
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Modulos());

        $this->initialize();
    }

    /**
     * CRUD MODULOS
     */
    public function getModulos() { //Fetch de todos os módulo
        $this->table = array('r' => 'resource');
        $select = new Select();
        $select->from(array('r' => 'resource'));
        $select->columns(array(
                    'r_id' => 'id',
                    'r_nome' => 'resource_name',
                    'r_amigavel' => 'n_amigavel',
                ))
                ->join(array('p' => 'permission'), 'p.resource_id = r.id', array(
                    'p_id' => 'id',
                    'p_nome' => new \Zend\Db\Sql\Expression('group_concat(p.permission_name ORDER BY p.permission_name ASC)'),
                    'p_r_id' => 'resource_id',
                    'p_amigavel' => new \Zend\Db\Sql\Expression('group_concat(p.n_amigavel ORDER BY p.n_amigavel ASC)'),
                ))->group('r.id');

        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }

    public function pegaModulo($id) { //Fetch de todos os módulo
        $this->table = array('r' => 'resource');
        $select = new Select();
        $select->from(array('r' => 'resource'));
        $select->columns(array(
                    'r_id' => 'id',
                    'r_nome' => 'resource_name',
                    'r_amigavel' => 'n_amigavel',
                ))
                ->join(array('p' => 'permission'), 'p.resource_id = r.id', array(
                    'p_id' => 'id',
                    'p_nome' => new \Zend\Db\Sql\Expression('group_concat(p.permission_name)'),
                    'p_r_id' => 'resource_id',
                    'p_amigavel' => new \Zend\Db\Sql\Expression('group_concat(p.n_amigavel)'),
                ))->group('r.id')
                ->where(array('r.id' => $id));

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $row = $result->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveModulo($modulos) {

        $sql = new Sql($this->adapter);
        $id = $modulos->form_modulos; //ID para edição caso exista. Vazio para a ação ADD
        $modulo_controller = $modulos->modulo_controller; //Título do grupo
        $modulo_titulo = $modulos->modulo_titulo; //Título do grupo
        $modulo_action = $modulos->modulo_action; //Título do grupo
        $titulo_action = $modulos->titulo_action; //Título do grupo
        $actions_array = array_combine($modulo_action, $titulo_action);

        $modulo = array(
            'resource_name' => $modulo_controller,
            'n_amigavel' => $modulo_titulo,
        );

        if ($id == 0) {
            $query = $sql->insert('resource');
            $query->values($modulo);
            $selectString = $sql->getSqlStringForSqlObject($query);
            $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

            $pega_id = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();

            if (count($actions_array) > 0) {
                foreach ($actions_array as $k => $v) {
                    $data_arr = array(
                        'permission_name' => $k,
                        'resource_id' => $pega_id,
                        'n_amigavel' => $v,
                    );
                    $query2 = $sql->insert('permission');
                    $query2->values($data_arr);
                    $selectString2 = $sql->getSqlStringForSqlObject($query2);
                    $results = $this->adapter->query($selectString2, Adapter::QUERY_MODE_EXECUTE);
                }
                return $results;
            }
        } else {
            if ($this->pegaModulo($id)) {
                $resource = array(
                    'id' => $id,
                    'resource_name' => $modulo_controller,
                    'n_amigavel' => $modulo_titulo,
                );
                
                $query = $sql->update('resource');
                $query->set($resource);
                $query->where(array('id' => $id));
                $selectString = $sql->getSqlStringForSqlObject($query);
                $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

                $delete = new TableGateway('permission', $this->adapter);
                $delete->delete(array('resource_id' => $id));
                
                foreach ($actions_array as $k => $v) {
                    $data_arr = array(
                        'permission_name' => $k,
                        'resource_id' => $id,
                        'n_amigavel' => $v,
                    );
                    $query2 = $sql->insert('permission');
                    $query2->values($data_arr);
                    $selectString2 = $sql->getSqlStringForSqlObject($query2);
                    $results = $this->adapter->query($selectString2, Adapter::QUERY_MODE_EXECUTE);
                }
                return $results;
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
    public function deleteModulo($id) {
        $user = new TableGateway('resource', $this->adapter);
        $user->delete(array('id' => $id));
        $grupo = new TableGateway('permission', $this->adapter);
        $grupo->delete(array('resource_id' => $id));
    }
}
