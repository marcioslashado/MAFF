<?php
namespace Users\Controller;

use Zend\Db\Sql\Select;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Users\Form\LoginForm;
use Users\Model\LoginValidation;
use Users\Model\Users;
use Users\Form\UsersForm;
use Users\Model\ForgotPasswordValidation;
use Users\Model\ResetPasswordValidation;
use Users\Model\ChangePasswordValidation;
use Users\Model\LoginMessages;
use Users\Form\ResetPasswordForm;
use Users\Form\ForgotPasswordForm;
use Users\Form\ChangePasswordForm;
use Zend\View\Renderer\PhpRenderer;
use \Zend\View\Resolver\TemplateMapResolver;
use Zend\Form\Annotation\Object;
use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;

class UserController extends AbstractActionController
{

    protected $usersTable;
    protected $gruposTable;
    protected $storage;

    protected $authservice;

    /**
     * Login Form Action
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object ViewModel
     */
    public function indexAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        $userPassword = $this->getServiceLocator()->get('Users\Service\UserEncryption');
        $session = new Container('User');
        $viewModel = new ViewModel();
        $loginForm = new LoginForm('loginForm');
        $request = $this->getRequest();
        $message = array();
        // //Redirect to the Home Page if user already login////
        if ($session->offsetExists('userId')) {
            return $this->redirect()->toRoute($config['afterLoginURL']);
        }
        
        try {
            if ($request->isPost()) {
                $clientInfo = "";
                $loginValidation = new LoginValidation('loginValidation');
                $loginForm->setInputFilter($loginValidation->getInputFilter());
                $loginForm->setData($request->getPost());
                if ($loginForm->isValid()) {
                    $data = $loginForm->getData();
                    $userTable = $this->getServiceLocator()->get('Users\Model\UsersTable');
                    // ///////Check the Login Wrong Attempts //////////
                    $attempts = $userTable->getLoginAttempts($data['userName']);
                    if ($attempts > 4) {
                        $message['error'] = LoginMessages::LOGIN_LOCKED;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toRoute('users');
                    }
                    $userDetails = $userTable->getUserDetailByUsername($data['userName']);
                    // ///Validate the User Login Details////
                    $encyptPass = $userPassword->create($data['password']);
                    $this->getAuthService()
                        ->getAdapter()
                        ->setIdentity($data['userName'])
                        ->setCredential($encyptPass);
                    $result = $this->getAuthService()->authenticate();
                    if ($result->isValid()) {
                        $userDetails = $userTable->getUserDetailByUsername($data['userName']);
                        if ($userDetails['status'] === 'Active') {
                            $userTable->resetLoginAttempts($data['userName']);
                            
                            // ///Remember Me Functionality ///////
                            if ($data['rememberMe'] == 1) {
                                $this->getSessionStorage()->setRememberMe(1);
                                $this->getAuthService()->setStorage($this->getSessionStorage());
                            }
                            
                            // ///Change Password From Functionality ///////
                            if (isset($data['changePassword']) && $data['changePassword'] == 1) {
                                return $this->redirect()->toUrl('users/change-password');
                            }
                            
                            $this->getAuthService()
                                ->getStorage()
                                ->write($data['userName']);
                            $session->offsetSet('userId', $userDetails['id']);
                            $session->offsetSet('userNome', $userDetails['first_name']);
                            $session->offsetSet('userSobrenome', $userDetails['last_name']);
                            $session->offsetSet('userEmail', $data['userName']);
                            $session->offsetSet('userOrgaos', $userDetails['orgaos']);
                            $session->offsetSet('userLastLogin', $userDetails['last_signed_in']);
                            $session->offsetSet('userIdPermissao', $userDetails['r_rid']);
                            $session->offsetSet('userPermissao', $userDetails['r_nome']);
                        } else {
                            // //// Destroy the Session and redirect to Login
                            $message['error'] = LoginMessages::ACCOUNT_NOT_ACTIVE;
                            $this->flashMessenger()->addMessage($message);
                            return $this->redirect()->toRoute('users');
                        }
                        return $this->redirect()->toRoute($config['afterLoginURL']);
                    } else {
                        $message['error'] = LoginMessages::INVALID_USER_PASSWORD;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toRoute('users');
                    }
                } else {
                    $errorList = $loginForm->getMessages();
                    $message['error'] = '';
                    if (isset($errorList['loginCsrf']['notSame'])) {
                        $message['error'] = LoginMessages::CSRF_ERROR;
                    }
                    if (empty($message['error'])) {
                        $message['error'] = "Email/Senha inválidos";
                    }
                    $this->flashMessenger()->addMessage($message);
                    // return $this->redirect()->toRoute('users');
                }
            }
        } catch (\Exception $excp) {
            print "<pre>";
            print_r($excp->getMessage());
            die;
            $excp->getMessage();
        }
        $viewModel->setVariables(array(
            'loginForm' => $loginForm
        ));
        return $viewModel;
    }

    /**
     * Change Password Action
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object ViewModel
     */
    public function changePasswordAction()
    {
        $session = new Container('User');
        $message = array();
         
        if (! $session->offsetExists('userId')) {
            $message['error'] = LoginMessages::NOT_LOGIN_ACCESS;
            $this->flashMessenger()->addMessage($message);
            return $this->redirect()->toRoute('users');
        }
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $changePasswordForm = new ChangePasswordForm('changePasswordForm');
       
        try {
            if ($request->isPost()) {
                
                $changePasswordValidation = new ChangePasswordValidation('changePasswordValidation');
                
                $changePasswordForm->setInputFilter($changePasswordValidation->getInputFilter());
                $changePasswordForm->setData($request->getPost());
                // ///////Server Side Password Validation///
                if ($changePasswordForm->isValid()) {
                    $userTable = $this->getServiceLocator()->get('Users\Model\UsersTable');
                    $data = $changePasswordForm->getData();
                    
                    // ///Validating the Password in DB and Change it///
                    $passMsg = $userTable->validateChangePassword($data);
                    if ($passMsg['passChange']) {
                        $message['success'] = LoginMessages::PASS_CHANGED_SUCCESS;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toURL('change-password');
                    } elseif ($passMsg['passSame']) {
                        $message['error'] = LoginMessages::PASS_SAME;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toURL('change-password');
                    } elseif ($passMsg['passNotSame']) {
                        $message['error'] = LoginMessages::INVALID_OLD_PASS;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toURL('change-password');
                    }
                } else {
                    $errorList = $changePasswordForm->getMessages();
                    if (isset($errorList['loginCsrf']['notSame'])) {
                        $message['error'] = $errorList['loginCsrf']['notSame'];
                    }
                    $this->flashMessenger()->addMessage($message);
                    // return $this->redirect()->toURL('change-password');
                }
            }
        } catch (\Exception $excp) {
            throw $excp;
        }
        $viewModel->setVariables(array(
            'changePasswordForm' => $changePasswordForm
        ));
        return $viewModel;
    }

    /**
     * Forgot Password Action
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object ViewModel
     */
    public function forgotPasswordAction()
    {
        $viewModel = new ViewModel();
        $session = new Container('User');
        $request = $this->getRequest();
        $config = $this->getServiceLocator()->get('config');
        $userPassword = $this->getServiceLocator()->get('Users\Service\UserEncryption');
        $UserMailServices = $this->getServiceLocator()->get('Users\Service\UserMailServices');
        $forgotPasswordForm = new ForgotPasswordForm('forgotPasswordForm');
        $message = array();
        $error_message = array();
        $mailData = array();
        
        // //Redirect to the home page if user login///
        if ($session->offsetExists('userId')) {
            return $this->redirect()->toRoute($config['afterLoginURL']);
        }
        try {
            if ($request->isPost()) {
                $userTable = $this->getServiceLocator()->get('Users\Model\UsersTable');
                $forgotPasswordValidation = new ForgotPasswordValidation('forgotPasswordValidation');
                $forgotPasswordForm->setInputFilter($forgotPasswordValidation->getInputFilter());
                $forgotPasswordForm->setData($request->getPost());
                
                if ($forgotPasswordForm->isValid()) {
                    $data = $forgotPasswordForm->getData();
                    // //////////Verifying the Email exits in DB or Not ////
                    if ($userID = $userTable->verifyEmailForgotPassword($data)) {
                        // ///Create URL and Send Mail to User //////////
                        $emailID = $data['userName'];
                        $time = time();
                        $encrytedKey = $userPassword->encryptUrlParameter($emailID . '|' . $userID . '|' . $time);
                        // /// Mail Code will be here //////////
                        $message['resetLink'] = $config['settings']['BASE_URL'] . 'users/reset-password/token/' . $encrytedKey;
                        $userData = $userTable->getUsers(array(
                            "user.id" => $userID
                        ));
                        $userData = $userData[0];
                        $message['userName'] = $userData['first_name'] . " " . $userData['last_name'];
                        $mailData['mailTo'] = $emailID;
                        $mailData['mailFrom'] = $config['settings']['EMAIL']['FROM'];
                        $mailData['mailFromNickName'] = $config['settings']['EMAIL']['MAIL_FROM_NICK_NAME'];
                        $mailData['mailSubject'] = $config['settings']['FORGOT_PASSWORD_SUBJECT'];
                        $mailData['mailBody'] = $this->getForgotPasswordTemplate($message);
                        $UserMailServices->sendMail($mailData);
                        
                        $error_message['success'] = LoginMessages::RESET_SUCCESS_MESSAGE;
                        $this->flashMessenger()->addMessage($error_message);
                        return $this->redirect()->toRoute('users');
                    } else {
                        $message['error'] = LoginMessages::EMAIL_NOT_EXIST;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toURL('forgot-password');
                    }
                } else {
                    $errorList = $forgotPasswordForm->getMessages();
                    $message['error'] = '';
                    if (isset($errorList['loginCsrf']['notSame'])) {
                        $message['error'] = $errorList['loginCsrf']['notSame'];
                    }
                    if (empty($message['error'])) {
                        $message['error'] = "Please enter valid Email";
                    }
                    $this->flashMessenger()->addMessage($message);
                    return $this->redirect()->toURL('forgot-password');
                }
            }
        } catch (\Exception $excp) {
            echo $excp->getMessage();
        }
        $viewModel->setVariables(array(
            'forgotPasswordForm' => $forgotPasswordForm
        ));
        return $viewModel;
    }

    /**
     * Reset Password Action
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object ViewModel
     */
    public function resetPasswordAction()
    {
        $userPassword = $this->getServiceLocator()->get('Users\Service\UserEncryption');
        $message = array();
        $userPasswordData = array();
        
        // ////////Get the Token From URL and Decrypt it///////////
        $session = new Container('User');
        $config = $this->getServiceLocator()->get('config');
        // //Redirect to the home page if user login///
        if ($session->offsetExists('userId')) {
            return $this->redirect()->toRoute($config['afterLoginURL']);
        }
        
        $token = $this->params()->fromRoute('token');
        if (! isset($token)) {
            // / Redirect the User to Login page when Token not available ////
            return $this->redirect()->toRoute('users');
        }
        $resetPasswordData = $userPassword->decryptUrlParameter($token);
        $resetPasswordData = explode("|", $resetPasswordData);
        // ////////Redirect to Login Page when time expired /////
        $tokenExpireTime = $resetPasswordData[2] + $userPassword->_forgotPasswordExpireTime;
        if ($tokenExpireTime < time()) {
            $message['error'] = LoginMessages::RESET_TOKEN_EXPIRED;
            $this->flashMessenger()->addMessage($message);
            return $this->redirect()->toRoute('users');
        }
        if (! ($session->offsetExists('resetEmailPassword'))) {
            $session->offsetSet('resetEmailPassword', $resetPasswordData[0]);
            $resetPasswordData[1] = (int) $resetPasswordData[1];
            $session->offsetSet('resetUserID', $resetPasswordData[1]);
        }
        $viewModel = new ViewModel();
        $request = $this->getRequest();
        $resetPasswordForm = new ResetPasswordForm('resetPasswordForm');
        try {
            if ($request->isPost()) {
                $userTable = $this->getServiceLocator()->get('Users\Model\UsersTable');
                $resetPasswordValidation = new ResetPasswordValidation('resetPasswordValidation');
                $resetPasswordForm->setInputFilter($resetPasswordValidation->getInputFilter());
                $resetPasswordForm->setData($request->getPost());
                
                if ($resetPasswordForm->isValid()) {
                    $data = $resetPasswordForm->getData();
                    $userTable = $this->getServiceLocator()->get('Users\Model\UsersTable');
                    $userPasswordData['userId'] = $session->offsetGet('resetUserID');
                    $userPasswordData['password'] = $data['new_password'];
                    if ($userTable->changeUserPassword($userPasswordData, true)) {
                        $message['success'] = LoginMessages::PASS_UPDATE_SUCCESS;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toUrl('/users/password-reset-confirmation');
                    } else {
                        $message['error'] = LoginMessages::PASS_CHANGE_ERROR;
                        $this->flashMessenger()->addMessage($message);
                        return $this->redirect()->toRoute('users');
                    }
                } else {
                    $errorList = $resetPasswordForm->getMessages();
                    $message['error'] = $errorList['loginCsrf']['notSame'];
                    $this->flashMessenger()->addMessage($message);
                    return $this->redirect()->toRoute('users');
                }
            }
        } catch (\Exception $excp) {
            throw $excp;
        }
        $viewModel->setVariables(array(
            'resetPasswordForm' => $resetPasswordForm
        ));
        return $viewModel;
    }

    /**
     * Password Reset Confirmation Action
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object ViewModel
     */
    public function passwordResetConfirmationAction()
    {
        $session = new Container('User');
        $config = $this->getServiceLocator()->get('config');
        // //Redirect to the home page if user login///
        if ($session->offsetExists('userId')) {
            return $this->redirect()->toRoute($config['afterLoginURL']);
        }
        return new ViewModel();
    }

    /**
     * Logout Action
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object ViewModel
     */
    public function logoutAction()
    {
        $session = new Container('User');
        $userId = $session->offsetGet('userId');
        $sessionId = session_id();
        $session->getManager()->destroy();
        $session->getManager()->forgetMe();
        $this->getAuthService()->clearIdentity();
        return $this->redirect()->toRoute('users');
    }

    /**
     * Function for getting the Auth Services
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object AuthService
     */
    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authservice;
    }
    /**
     * Function for getting the Session Storage Services
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Object AuthService
     */
    public function getSessionStorage()
    {
        if (! $this->storage) {
            $this->storage = $this->getServiceLocator()->get('Users\Model\AuthStorage');
        }
        return $this->storage;
    }
    /**
     * Function for getting the forgot password template
     *
     * @author Kaushal Kishore <kaushal.rahuljaiswal@gmail.com>
     * @package Users
     * @access Public
     * @return Ambigous <string, \Zend\Filter\mixed, mixed>
     */
    public function getForgotPasswordTemplate($variables = array())
    {
        $view = new PhpRenderer();
        $resolver = new TemplateMapResolver();
        $resolver->setMap(array(
            'mailTemplate' => __DIR__ . '/../../../view/users/user/forgot-mail-template.phtml'
        ));
        $view->setResolver($resolver);
        
        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setTemplate('mailTemplate');
        $viewModel->setVariables($variables);
        $content = $view->render($viewModel);
        return $content;
    }
    
    public function getUsersTable() {
        if (!$this->usersTable) {
            $sm = $this->getServiceLocator();
            $this->usersTable = $sm->get('Users\Model\UsersTable');
        }
        return $this->usersTable;
    }
    
    public function usuariosAction()
    {
        $select = new Select();

        $order_by = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'u_id';
        $order = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        $usuarios = $this->getUsersTable()->fetchUsers($select->order($order_by . ' ' . $order));
        $itemsPerPage = 20;
        
        $usuarios->current();
        
        $orgaos = $usuarios->current()->u_orgaos;
        $unorc = $this->getUsersTable()->getOrgaos($orgaos);
        
        $paginator = new Paginator(new paginatorIterator($usuarios));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);
        
        $view = new ViewModel(array(
            'orgaos' => $unorc,
            'order_by' => $order_by,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
        ));
        $view->setTemplate('users/user/usuarios');
        return $view;
    }
    
    public function userAddAction()
    {
        $form = new UsersForm;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $user = new Users();
            $form->setData($request->getPost());
            $user = $request->getPost();
            //$this->getMetaTable()->saveMeta($meta);
            //return $this->redirect()->toRoute('metafisicas');
            
            $view = new ViewModel(array(
                'mensagem' => $this->getUsersTable()->saveUser($user),
                'form' => $form,
                'orgaos' => $this->getUsersTable()->getOrgaos(),
                'grupos' => $this->getUsersTable()->getGrupos(),
            ));
            $view->setTemplate('users/user/user-add');
            return $view;
        }

        $view = new ViewModel(array(
            'form' => $form,
            'orgaos' => $this->getUsersTable()->getOrgaos(),
            'grupos' => $this->getUsersTable()->getGrupos(),
        ));
        $view->setTemplate('users/user/user-add');
        return $view;
    }
    
    public function userEditAction()
    {
        $id = (int)$this->params('form_users');
        if (!$id) {
            return $this->redirect()->toRoute('users', array('action'=>'user-add'));
        }
        $user = $this->getUsersTable()->getUsers($id);
        $form = new UsersForm();
        $form->bind($user);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $user = $request->getPost();
            
            $view = new ViewModel(array(
                'mensagem' => $this->getUsersTable()->saveUser($user),
                'form_users' => $id,
                'form' => $form,
                'orgaos' => $this->getUsersTable()->getOrgaos(),
                'grupos' => $this->getUsersTable()->getGrupos(),
            ));
            $view->setTemplate('users/user/user-edit');
            return $view;
        }
        $view = new ViewModel(array(
            'form_users' => $id,
            'form' => $form,
            'permissao' => $user->r_rid,
            'unorcs' => $user->u_orgaos,
            'orgaos' => $this->getUsersTable()->getOrgaos(),
            'grupos' => $this->getUsersTable()->getGrupos(),
        ));
        $view->setTemplate('users/user/user-edit');
        return $view;
    }
    
    public function userDelAction()
    {
        $id = (int)$this->params('form_users');
        if (!$id) {
            return $this->redirect()->toRoute('usuarios');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost()->get('del', 'Cancelar');
            if ($del == 'Confirmar') {
                $id = (int)$request->getPost()->get('u_id');
                $this->getUsersTable()->deleteUser($id);
            }
            return $this->redirect()->toRoute('usuarios');
        }
        
        $user = $this->getUsersTable()->getUsers($id);
        
        $view = new ViewModel(array(
            'form_id' => $id,
            'users' => $user
        ));
        $view->setTemplate('users/user/user-del');
        return $view;
    }
}

