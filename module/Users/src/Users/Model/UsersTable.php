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

class UsersTable extends AbstractTableGateway {

    public $table = 'users';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        //$this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $this->resultSetPrototype->setArrayObjectPrototype(new Users());

        $this->initialize();
    }

    /**
     * Pega Orgãos
     */
    public function getOrgaos($orgaos = NULL) {
        $orgaos = $orgaos;
        if (!is_null($orgaos)) {
            $this->table = array('o' => 'orgao');
            $select = new Select();
            $select->from(array('o' => 'orgao'));
            $select->columns(array(
                        'o_unorc' => new \Zend\Db\Sql\Expression('group_concat(unorc)'),
                        'o_descricao' => new \Zend\Db\Sql\Expression('group_concat(descricao)'),
                    ))
                    ->where('o.unorc IN(' . $orgaos . ')');
            $result = $this->selectWith($select);
            $row = $result->current();
            if (!$row) {
                throw new \Exception("Could not find row $orgaos");
            }
            return $row;
        }
        else {
            $sql = new Sql($this->adapter);
            $select = new Select(array('o' => 'orgao'));
            $select->columns(array(
                'o_unorc' => 'unorc',
                'o_descricao' => 'descricao',
            ));
            $selectString = $sql->getSqlStringForSqlObject($select);
            return $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    /**
     * Pega grupos de permissões para ações de usuários
     */
    public function getGrupos() {
        $sql = new Sql($this->adapter);
        $select = new Select(array('g' => 'role'));
        $select->columns(array(
            'g_rid' => 'rid',
            'g_role_name' => 'role_name',
            'g_status' => 'status',
        ))->where(array('g.status' => 'Active'));
        ;

        $selectString = $sql->getSqlStringForSqlObject($select);
        //echo $select->getSqlString();
        return $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * CRUD USUARIOS
     */
    public function fetchUsers(Select $select = null) {
        $this->resultSetPrototype = new ResultSet();

        $this->table = array('u' => 'users'); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select = new Select();
        $select->from(array('u' => 'users')); //metafisicarealizada AS m - Remover o Array se não quiser renomear a Tabela
        $select
                ->columns(array(
                    //'nome_renomeado' => 'nome_campo_db'
                    'u_id' => 'id',
                    'u_email' => 'email',
                    'u_password' => 'password',
                    'u_tentativas' => 'login_attempts',
                    'u_login_attempt_time' => 'login_attempt_time',
                    'u_first_name' => 'first_name',
                    'u_last_name' => 'last_name',
                    'u_orgaos' => 'orgaos',
                    'u_status' => 'status',
                    'u_last_signed_in' => 'last_signed_in',
                ))
                ->join(array('ur' => 'user_role'), 'ur.user_id = u.id', array(
                    'ur_id' => 'id',
                    'ur_user_id' => 'user_id',
                    'ur_role_id' => 'role_id'
                ))
                ->join(array('r' => 'role'), 'r.rid = ur.role_id', array(
                    'r_rid' => 'rid',
                    'r_nome' => 'role_name',
                    'r_status' => 'status'
                ));

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $result->buffer();
        return $result;
    }

    public function saveUser($user) {
        $id = $user->form_users;
        $sql = new Sql($this->adapter);
        $unorcs = implode(",", $user->form_unorcs);
        $userPassword = new UserEncryption();
        $new_password = $userPassword->create($user->password);
        $data = array(
            'email' => $user->form_email,
            'password' => $new_password,
            'first_name' => $user->form_nome,
            'last_name' => $user->form_sobrenome,
            'orgaos' => $unorcs,
            'status' => $user->form_status,
        );

        if ($id == 0) {
            $query = $sql->insert('users');
            $query->values($data);
            $selectString = $sql->getSqlStringForSqlObject($query);
            $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

            $last_id = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
            $data_arr = array(
                'user_id' => $last_id,
                'role_id' => $user->form_grupo,
            );
            $query2 = $sql->insert('user_role');
            $query2->values($data_arr);
            $selectString2 = $sql->getSqlStringForSqlObject($query2);
            $results = $this->adapter->query($selectString2, Adapter::QUERY_MODE_EXECUTE);

            return $results;
        } else {
            if ($this->getUsers($id)) {
                if (empty($user->password)) {
                    $data = array(
                        'id' => $id,
                        'email' => $user->form_email,
                        'first_name' => $user->form_nome,
                        'last_name' => $user->form_sobrenome,
                        'orgaos' => $unorcs,
                        'status' => $user->form_status,
                    );
                } else {
                    $data = array(
                        'id' => $id,
                        'email' => $user->form_email,
                        'password' => $new_password,
                        'first_name' => $user->form_nome,
                        'last_name' => $user->form_sobrenome,
                        'orgaos' => $unorcs,
                        'status' => $user->form_status,
                    );
                }
                $query = $sql->update('users');
                $query->set($data);
                $query->where(array('id' => $id));
                $selectString = $sql->getSqlStringForSqlObject($query);
                $this->adapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

                $data_arr = array(
                    'user_id' => $id,
                    'role_id' => $user->form_grupo,
                );
                $query2 = $sql->update('user_role');
                $query2->set($data_arr);
                $query2->where(array('user_id' => $id));
                $selectString2 = $sql->getSqlStringForSqlObject($query2);
                $results = $this->adapter->query($selectString2, Adapter::QUERY_MODE_EXECUTE);

                return $results;
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteUser($id) {
        //$this->delete(array('id' => $id));

        $user = new TableGateway('users', $this->adapter);
        $user->delete(array('id' => $id));
        $grupo = new TableGateway('user_role', $this->adapter);
        $grupo->delete(array('user_id' => $id));
    }

    public function getUsers($id) {
        $id = $id;
        $this->table = array('u' => 'users');
        $select = new Select();
        $select->from(array('u' => 'users'));
        $select
                ->columns(array(
                    //'nome_renomeado' => 'nome_campo_db'
                    'u_id' => 'id',
                    'u_email' => 'email',
                    'u_password' => 'password',
                    'u_tentativas' => 'login_attempts',
                    'u_login_attempt_time' => 'login_attempt_time',
                    'u_first_name' => 'first_name',
                    'u_last_name' => 'last_name',
                    'u_orgaos' => 'orgaos',
                    'u_status' => 'status',
                    'u_last_signed_in' => 'last_signed_in',
                ))
                ->join(array('ur' => 'user_role'), 'ur.user_id = u.id', array(
                    'ur_id' => 'id',
                    'ur_user_id' => 'user_id',
                    'ur_role_id' => 'role_id'
                ))->join(array('r' => 'role'), 'r.rid = ur.role_id', array(
                    'r_rid' => 'rid',
                    'r_nome' => 'role_name',
                    'r_status' => 'status'
                ))
                ->where(array('u.id' => $id));

        //echo $select->getSqlString(); //Exibe a consulta em SQL
        $result = $this->selectWith($select);
        $row = $result->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    /**
     * Function for validating and changing the Password
     *
     * @param unknown $password            
     * @return boolean
     */
    public function validateChangePassword($password) {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $userPassword = new UserEncryption();
        $session = new Container('User');
        $passMsg = array(
            'passChange' => 0,
            'passSame' => 0,
            'passNotSame' => 0
        );
        try {
            // ////Checking the Old Password is valid or not//////
            $old_password = $userPassword->create($password['old_password']);
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()
                    ->from($this->table)
                    ->columns(array(
                        'password'
                    ))
                    ->where(array(
                'id' => $session->offsetGet('userId'),
                'password' => $old_password
            ));
            $statement = $sql->prepareStatementForSqlObject($select);
            $data = $this->resultSetPrototype->initialize($statement->execute())
                    ->toArray();
            if (count($data)) {
                // ///////Password is Valid now change the Password/////
                $userPasswordData['userId'] = $session->offsetGet('userId');
                $userPasswordData['password'] = $password['new_password'];
                if ($this->changeUserPassword($userPasswordData)) {
                    $passMsg['passChange'] = 1;
                } else {
                    $passMsg['passSame'] = 1;
                }
                return $passMsg;
            } else {
                // ///// Password is not valid ///////////
                $passMsg['passNotSame'] = 1;
                return $passMsg;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }

    /**
     * Function for Verifying the Email ID for Forgot Password
     *
     * @param unknown $emailData            
     * @return boolean
     */
    public function verifyEmailForgotPassword($emailData) {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $sql = new Sql($this->getAdapter());
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()
                    ->from($this->table)
                    ->columns(array(
                        'id'
                    ))
                    ->where(array(
                'email' => $emailData['userName']
            ));
            $statement = $sql->prepareStatementForSqlObject($select);
            $data = $this->resultSetPrototype->initialize($statement->execute())
                    ->toArray();
            if (count($data)) {
                return $data[0]['id'];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }

    /**
     * Function for changing the User Password
     *
     * @param array $userPasswordData            
     * @return boolean
     */
    public function changeUserPassword($userPasswordData, $resetPassword = false) {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $userPassword = new UserEncryption();
        $sql = new Sql($this->getAdapter());
        try {
            $new_password = $userPassword->create($userPasswordData['password']);
            $update = $sql->update();
            $update->table($this->table);

            $data = array(
                'password' => $new_password,
                'login_attempts' => 0,
                'login_attempt_time' => 0
            );

            $update->set($data);

            $update->where(array(
                'id' => $userPasswordData['userId']
            ));
            $statement = $sql->prepareStatementForSqlObject($update);
            $result = $statement->execute();
            // /////Password reset Successfully ///////////
            if ($result->getAffectedRows()) {
                return true;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
        return false;
    }

    /**
     * Update super admin details
     *
     * @access public
     * @author Arvind Singh
     * @param array $data
     *            // Data
     * @param array $where
     *            // Conditions
     *            
     * @return integer
     */
    public function updateSuperAdmin($data, $where) {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $sql = new Sql($this->getAdapter());

        $update = new Update($this->table);
        $update->set($data);
        $update->where($where);

        $statement = $sql->prepareStatementForSqlObject($update);
        $results = $statement->execute();
        $affectedRows = $results->getAffectedRows();

        return $affectedRows;
    }

    /**
     * Function for getting and setting the login attempts
     *
     * @param unknown $userName            
     * @return unknown
     */
    public function getLoginAttempts($userName) {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $loginAttempts = 0;
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()
                    ->from($this->table)
                    ->columns(array(
                        'login_attempts',
                        'login_attempt_time'
                    ))
                    ->where(array(
                'email' => $userName
            ));
            $statement = $sql->prepareStatementForSqlObject($select);
            $data = $this->resultSetPrototype->initialize($statement->execute())
                    ->toArray();
            if (count($data)) {
                // ////////User Found Increase the Login Attempts/////
                $loginAttempts = $data[0]['login_attempts'];
                $loginAttemptTime = $data[0]['login_attempt_time'];
                // ///// Reset the time if user quit the screen before 30
                // minutes ////
                if ($loginAttemptTime + 1800 < time() && $loginAttempts < 4) {
                    $loginAttempts = 0;
                }
                $update = $sql->update();
                $update->table($this->table);
                $update->set(array(
                    'login_attempts' => $loginAttempts + 1,
                    'login_attempt_time' => time()
                ));
                $update->where(array(
                    'email' => $userName
                ));
                $statement = $sql->prepareStatementForSqlObject($update);
                $result = $statement->execute();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
        return $loginAttempts;
    }

    /**
     * Reseting the Login Attempts after login
     *
     * @param unknown $email            
     * @param unknown $email            
     * @return boolean
     */
    public function resetLoginAttempts($email) {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        try {
            $sql = new Sql($this->getAdapter());
            $update = $sql->update();
            $update->table($this->table);
            $update->set(array(
                'login_attempts' => 0,
                'login_attempt_time' => 0
            ));
            $update->where(array(
                'email' => $email
            ));
            $statement = $sql->prepareStatementForSqlObject($update);
            $result = $statement->execute();
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
        return true;
    }

    /**
     *
     * @author avadhesh mishra
     * @param string $username            
     * @throws \Exception
     * @return array
     */
    public function getUserDetailByUsername($username) {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()->from(array(
                'sa' => $this->table
            ));
            $select->columns(array(
                        'id',
                        'email',
                        'status',
                        'first_name',
                        'last_name',
                        'orgaos',
                        'last_signed_in',
                    ))
                    ->join(array('ur' => 'user_role'), 'ur.user_id = sa.id', array(
                        'ur_id' => 'id',
                        'ur_user_id' => 'user_id',
                        'ur_role_id' => 'role_id'
                    ))->join(array('r' => 'role'), 'r.rid = ur.role_id', array(
                'r_rid' => 'rid',
                'r_nome' => 'role_name',
                'r_status' => 'status'
            ));
            $select->where(array(
                'email' => $username
            ));
            $statement = $sql->prepareStatementForSqlObject($select);

            $roles = $this->resultSetPrototype->initialize($statement->execute())
                    ->toArray();

            if (!empty($roles[0]) && is_array($roles[0])) {
                return $roles[0];
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }

    /**
     *
     * @param unknown $email            
     * @return boolean
     */
    public function checkMailExits($email = "") {
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select();
            $select->from($this->table);
            $select->where(array(
                'email' => $email
            ));
            $statement = $sql->prepareStatementForSqlObject($select);
            $data = $this->resultSetPrototype->initialize($statement->execute())
                    ->toArray();
            if (count($data)) {
                return true;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
        return false;
    }

}
