<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    /** 
     * Conecta ao banco de dados.
     * Conexão realizada via CONFIG pois trata-se de um módulo independente e, portanto, 
     * necessita de conexão à base de dados independentemente do sistema.
     */
    'db' => array(
        'driver' => 'Pdo',
        'dsn'            => 'mysql:dbname=maff_zf;hostname=localhost',
        'username'       => 'root',
        'password'       => '',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Maff\Controller\Index' => 'Maff\Controller\IndexController'
        ),
    ),
    
    'router' => array(
        'routes' => array(
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            //Action da Página INDEX
            'maff' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/maff',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Maff\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),

            'relatorios' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/maff/relatorios[/:action][/:id][/page/:page][/order_by/:order_by][/:order]',
                    'defaults' => array(
                        'controller' => 'Maff\Controller\Index',
                        'action' => 'relatorios',
                    ),
                ),
            ),

            //Actions da Página PRIORIDADES
            'prioridades' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/maff/prioridades[/:action][/:id][/page/:page][/order_by/:order_by][/:order]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Maff\Controller\Index',
                        'action' => 'prioridades',
                    ),
                ),
            ),
            
            //Actions da Página META FÍSICAS (CRUD - Add/Edit/Delete)
            'metafisicas' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/maff/metafisicas[/:action][/:form_codigo][/page/:page][/order_by/:order_by][/:order]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Maff\Controller\Index',
                        'action' => 'metafisicas',
                    ),
                ),
            ),
            
            //Action da Página EXECUÇÕES ORÇAMENTÁRIAS (Pode ser CRUD também)
            'execorcs' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/maff/execorcs[/:action][/:id][/page/:page][/order_by/:order_by][/:order]',
                    'constraints' => array(
                        'action' => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Maff\Controller\Index',
                        'action' => 'execorcs',
                    ),
                ),
            ),
        ),
    ),
    
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'login' => __DIR__ . '/../view/layout/login.phtml',
            'exec-paginator' => __DIR__ . '/../view/layout/execPaginator.phtml',
            'meta-paginator' => __DIR__ . '/../view/layout/metaPaginator.phtml',
            'prioridades-paginator' => __DIR__ . '/../view/layout/prioridadesPaginator.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
