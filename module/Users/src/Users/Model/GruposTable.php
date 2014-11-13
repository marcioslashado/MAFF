<?php

namespace Users\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Session\Container;
use Zend\Db\Sql\Update;
use Users\Service\UserEncryption;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\JsonModel;
use Zend\Db\Adapter\Driver\DriverInterface;

class GruposTable extends AbstractTableGateway {

    protected $table = 'role';
    
    public function __construct(Adapter $adapter) {
        //$this->table = '';
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Grupos());

        $this->initialize();
    }

    public function fetchGrupos(Select $select = null) {
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
                    'rp_permission' => 'permission_id',
                ))
                ->join(array('per' => 'permission'), 'per.id = rp.id', array(
                    'per_id' => 'id',
                    'per_nome' => new \Zend\Db\Sql\Expression('group_concat(per.permission_name ORDER BY per.id ASC)'),
                    'per_resource' => 'resource_id',
                ))
                ->join(array('re' => 'resource'), 're.id = per.resource_id', array(
                    're_id' => 'id',
                    're_nome' => new \Zend\Db\Sql\Expression('group_concat(DISTINCT re.resource_name ORDER BY re.resource_name ASC)'),
                ));

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }

}
