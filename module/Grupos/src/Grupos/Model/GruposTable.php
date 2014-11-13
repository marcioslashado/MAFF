<?php

namespace Grupos\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\View\Model\JsonModel;

class GruposTable extends AbstractTableGateway {

    protected $table = 'role';

    public function __construct(Adapter $adapter) {
        //$this->table = '';
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Grupos());

        $this->initialize();
    }

    /**
     * CRUD GRUPOS
     */
    public function getGrupos() {
        $this->table = array('r' => 'role');
        $select = new Select();
        $select->from(array('r' => 'role'));
        
        $select->columns(array(
                    'r_rid' => 'rid',
                    'r_nome' => 'role_name',
                    'r_status' => 'status',
                ))
                ->join(array('rp' => 'role_permission'), 'rp.role_id = r.rid', array(
                    'rp_id' => 'id',
                ))
                ->join(array('per' => 'permission'), 'per.permission_name = rp.permission_nome', array(
                    'per_id' => 'id',
                    'per_nome' => new \Zend\Db\Sql\Expression('group_concat(per.permission_name ORDER BY per.id ASC)'),
                    'per_amigavel' => new \Zend\Db\Sql\Expression('group_concat(per.n_amigavel ORDER BY per.id ASC)'),
                    'per_resource' => 'resource_id',
                ))
                ->join(array('re' => 'resource'), 're.id = per.resource_id', array(
                    're_id' => 'id',
                    're_nome' => new \Zend\Db\Sql\Expression('group_concat(DISTINCT resource_name ORDER BY re.resource_name ASC)'),
                    're_amigavel' => new \Zend\Db\Sql\Expression('group_concat(DISTINCT re.n_amigavel ORDER BY re.resource_name ASC)'),
                ))->group('r.rid');

        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }
    
    public function getPermissions($id) {
        $id = $id;
        $this->table = array('r' => 'role');
        $select = new Select();
        $select->from(array('r' => 'role'));
        $select->columns(array(
                    'r_rid' => 'rid',
                    'r_nome' => 'role_name',
                    'r_status' => 'status',
                ))
                ->join(array('rp' => 'role_permission'), 'rp.role_id = r.rid', array(
                    'rp_id' => 'id',
                ))
                ->join(array('per' => 'permission'), 'per.permission_name = rp.permission_nome', array(
                    'per_id' => 'id',
                    'per_nome' => new \Zend\Db\Sql\Expression('group_concat(per.permission_name ORDER BY per.id ASC)'),
                    'per_amigavel' => new \Zend\Db\Sql\Expression('group_concat(per.n_amigavel ORDER BY per.id ASC)'),
                    'per_resource' => 'resource_id',
                ))
                ->join(array('re' => 'resource'), 're.id = per.resource_id', array(
                    're_id' => 'id',
                    're_nome' => new \Zend\Db\Sql\Expression('group_concat(DISTINCT resource_name ORDER BY re.resource_name ASC)'),
                    're_amigavel' => new \Zend\Db\Sql\Expression('group_concat(DISTINCT re.n_amigavel ORDER BY re.resource_name ASC)'),
                ))
                ->where(array('r.rid' => $id));

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $row = $result->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function salvaGrupo($grupos) {
        $sql = new Sql($this->adapter);
        $id = $grupos->form_grupos; //ID para edição caso exista. Vazio para a ação ADD
        $grupo_titulo = $grupos->grupo_titulo; //Título do grupo
        $grupo_status = $grupos->modulo_status; //Status do Grupo
        $grupo_modulo = $grupos->grupo_modulo; //Módulos e Actions

        $grupo = array(
            'role_name' => $grupo_titulo,
            'status' => $grupo_status,
        );
        
        if ($id == 0) {
            $query = $sql->insert('role');
            $query->values($grupo);
            $selectString = $sql->getSqlStringForSqlObject($query);
            $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

            $pega_id = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();

            if (count($grupo_modulo) > 0) {
                foreach ($grupo_modulo as $k => $v) {
                    $data_arr = array(
                        'role_id' => $pega_id,
                        'permission_nome' => $v,
                    );
                    $query2 = $sql->insert('role_permission');
                    $query2->values($data_arr);
                    $selectString2 = $sql->getSqlStringForSqlObject($query2);
                    $results = $this->adapter->query($selectString2, Adapter::QUERY_MODE_EXECUTE);
                }
                return $results;
            }
        } else {
            if ($this->getPermissions($id)) {
                $role = array(
                    'rid' => $id,
                    'role_name' => $grupo_titulo,
                    'status' => $grupo_status,
                );
                
                $query = $sql->update('role');
                $query->set($role);
                $query->where(array('rid' => $id));
                $selectString = $sql->getSqlStringForSqlObject($query);
                $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

                $delete = new TableGateway('role_permission', $this->adapter);
                $delete->delete(array('role_id' => $id));
                
                foreach ($grupo_modulo as $k => $v) {
                    $data_arr = array(
                        'role_id' => $id,
                        'permission_nome' => $v,
                    );
                    $query2 = $sql->insert('role_permission');
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
    public function deleteGrupo($id) {
        $user = new TableGateway('role', $this->adapter);
        $user->delete(array('rid' => $id));
        $grupo = new TableGateway('role_permission', $this->adapter);
        $grupo->delete(array('role_id' => $id));
    }
}
