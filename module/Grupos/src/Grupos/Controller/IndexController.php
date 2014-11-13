<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Grupos\Controller;

use Zend\Db\Sql\Select;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Grupos\Model\Grupos;
use Grupos\Model\Modulos;
use Grupos\Form\GruposForm;
use Grupos\Form\ModulosForm;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController {

    protected $gruposTable;
    protected $modulosTable;
    
    //Página principal
    public function indexAction() {
        $view = new ViewModel();
        return $view;
    } 
    
    /**
     * CONSULTAS
     */
    public function getGruposTable() {
        if (!$this->gruposTable) {
            $sm = $this->getServiceLocator();
            $this->gruposTable = $sm->get('Grupos\Model\GruposTable');
        }
        return $this->gruposTable;
    }
    
    public function getModulosTable() {
        if (!$this->modulosTable) {
            $sm = $this->getServiceLocator();
            $this->modulosTable = $sm->get('Grupos\Model\ModulosTable');
        }
        return $this->modulosTable;
    }
    
    /**
     * CRUD GRUPOS
     */
    public function gruposAction()
    {
        $select = new Select();
        $order_by = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'r_rid';
        $order = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        $grupos = $this->getGruposTable()->getGrupos($select->order($order_by . ' ' . $order));
        $itemsPerPage = 20;

        $grupos->current();
        
        $paginator = new Paginator(new paginatorIterator($grupos));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        $view = new ViewModel(array(
            'order_by' => $order_by,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
        ));
        $view->setTemplate('grupos/grupos/index');
        return $view;
    }
    
    public function grupoAddAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GruposForm ($dbAdapter); //Para popular o Select do formulário

        //$form = new GruposForm();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $grupos = new Grupos();
            $form->setData($request->getPost());
            $grupos = $request->getPost();
            
            $view = new ViewModel(array(
                'mensagem' => $this->getGruposTable()->salvaGrupo($grupos),
                'form' => $form,
                'grupos' => $this->getGruposTable()->getGrupos(),
            ));
            $view->setTemplate('grupos/grupos/add');
            return $view;
        }

        $view = new ViewModel(array(
                'form' => $form,
                'grupos' => $this->getGruposTable()->getGrupos(),
        ));
        $view->setTemplate('grupos/grupos/add');
        return $view;
    }
    
    public function grupoEditAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $id = (int)$this->params('form_grupos');
        if (!$id) {
            return $this->redirect()->toRoute('grupos', array('action'=>'grupo-add'));
        }
        $grupos = $this->getGruposTable()->getPermissions($id);
        $actions = explode(',', $grupos->per_nome);
        $form = new GruposForm ($dbAdapter); //Para popular o Select do formulário
        $form->bind($grupos);
        $form->get('grupo_modulo')->setAttributes(array('value' => $actions));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $grupos = $request->getPost();
            
            $view = new ViewModel(array(
                'mensagem' => $this->getGruposTable()->salvaGrupo($grupos),
                'form_grupos' => $id,
                'form' => $form,
            ));
            $view->setTemplate('grupos/grupos/edit');
            return $view;
        }
        $view = new ViewModel(array(
            'form_grupos' => $id,
            'form' => $form,
        ));
        $view->setTemplate('grupos/grupos/edit');
        return $view;
    }
    
    public function grupoDelAction()
    {
        $id = (int)$this->params('form_grupos');
        if (!$id) {
            return $this->redirect()->toRoute('grupos');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost()->get('del', 'Cancelar');
            if ($del == 'Confirmar') {
                $id = (int)$request->getPost()->get('u_id');
                $this->getGruposTable()->deleteGrupo($id);
            }
            return $this->redirect()->toRoute('grupos');
        }
        
        $grupos = $this->getGruposTable()->getPermissions($id);
        
        $view = new ViewModel(array(
            'form_grupos' => $id,
            'grupos' => $grupos
        ));
        $view->setTemplate('grupos/grupos/delete');
        return $view;
    }
    
    /**
     * CRUD MÓDULOS
     */
    public function modulosAction()
    {
        $select = new Select();
        $order_by = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'u_id';
        $order = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        $modulos = $this->getModulosTable()->getModulos($select->order($order_by . ' ' . $order));
        $itemsPerPage = 20;

        $modulos->current();
        
        $paginator = new Paginator(new paginatorIterator($modulos));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        $view = new ViewModel(array(
            'order_by' => $order_by,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
        ));
        $view->setTemplate('grupos/modulos/index');
        return $view;
    }
    
    public function moduloAddAction()
    {
        $form = new ModulosForm();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $modulos = new Modulos();
            $form->setData($request->getPost());
            $modulos = $request->getPost();
            
            $view = new ViewModel(array(
                'mensagem' => $this->getModulosTable()->saveModulo($modulos),
                'form' => $form,
            ));
            $view->setTemplate('grupos/modulos/add');
            return $view;
        }

        $view = new ViewModel(array(
                'form' => $form,
        ));
        $view->setTemplate('grupos/modulos/add');
        return $view;
    }
    
    public function moduloEditAction()
    {
        $id = (int)$this->params('form_modulos');
        if (!$id) {
            return $this->redirect()->toRoute('modulos', array('action'=>'modulo-add'));
        }
        $modulos = $this->getModulosTable()->pegaModulo($id);
        $form = new ModulosForm();
        $form->bind($modulos);
        
        $p_nome = explode(',', $modulos->p_nome);
        $p_amigavel = explode(',', $modulos->p_amigavel);
        $actions_array = array_combine($p_nome, $p_amigavel);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $modulos = $request->getPost();
            
            $view = new ViewModel(array(
                'actions' => $actions_array,
                'mensagem' => $this->getModulosTable()->saveModulo($modulos),
                'form_modulos' => $id,
                'form' => $form,

            ));
            $view->setTemplate('grupos/modulos/edit');
            return $view;
        }
        $view = new ViewModel(array(
            'actions' => $actions_array,
            'form_modulos' => $id,
            'form' => $form,
        ));
        $view->setTemplate('grupos/modulos/edit');
        return $view;
    }
    
    public function moduloDelAction()
    {
        $id = (int)$this->params('form_modulos');
        if (!$id) {
            return $this->redirect()->toRoute('modulos');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost()->get('del', 'Cancelar');
            if ($del == 'Confirmar') {
                $id = (int)$request->getPost()->get('form_modulos');
                $this->getModulosTable()->deleteModulo($id);
            }
            return $this->redirect()->toRoute('modulos');
        }
        
        $modulo = $this->getModulosTable()->pegaModulo($id);
        
        $view = new ViewModel(array(
            'modulos' => $modulo
        ));
        $view->setTemplate('grupos/modulos/delete');
        return $view;
    }
    
}
