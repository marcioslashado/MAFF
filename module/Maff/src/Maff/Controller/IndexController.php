<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Maff\Controller;

use Zend\Db\Sql\Select;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Maff\Model\Meta;
use Maff\Model\Relatorios;
use Maff\Form\MetaForm;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController {

    protected $metaTable;
    protected $relatoriosTable;

    //Página principal
    public function indexAction() {
        $view = new ViewModel();
        return $view;
    } 

    /**
     * FUNÇÕES DE... AH, SEI LÁ
     */
    //Geração de Relatórios
    public function relatoriosAction() {
        $select = new Select();
        $form = new MetaForm;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            $post = $request->getPost();
            $registros = $this->getRelatoriosTable()->getRelatorios($post);
            
            //print_r($post); //Pra imprimir o que foi postado

            $view = new ViewModel(array(
                'form' => $form,
                'res' => $this->getRelatoriosTable()->getOrgaos(),
                'resultado' => $registros, 
            ));
            $view->setTemplate('maff/relatorios/relatorios');
            return $view;
        }

        $view = new ViewModel(array(
            'form' => $form,
            'res' => $this->getRelatoriosTable()->getOrgaos(),
        ));
        $view->setTemplate('maff/relatorios/relatorios');
        return $view;
    }

    //CRUD de Meta Físicas
    public function prioridadesAction() {
        $select = new Select();
        $order_by = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'm_projeto';
        $order = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        $prioridades = $this->getMetaTable()->fetchPrioridades($select->order($order_by . ' ' . $order));
        $itemsPerPage = 20;

        $prioridades->current();
        $paginator = new Paginator(new paginatorIterator($prioridades));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        $view = new ViewModel(array(
            'order_by' => $order_by,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
        ));
        $view->setTemplate('maff/prioridades/prioridades');
        return $view;
    }

    //CRUD de Meta Físicas
    public function metafisicasAction() {
        $select = new Select();

        $order_by = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'm_projeto';
        $order = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        $metafisicas = $this->getMetaTable()->fetchMetas($select->order($order_by . ' ' . $order));
        $itemsPerPage = 20;

        $metafisicas->current();
        $paginator = new Paginator(new paginatorIterator($metafisicas));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        $view = new ViewModel(array(
            'order_by' => $order_by,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
        ));
        $view->setTemplate('maff/metafisicas/metafisicas');
        return $view;
    }

    //CRUD de Meta Físicas (adicionar)
    public function addmetaAction() {
        $orgao = $_SESSION['User']['userOrgaos'];
        $form = new MetaForm();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $meta = new Meta();
            $form->setData($request->getPost());
            $meta = $request->getPost();
            //$this->getMetaTable()->saveMeta($meta);
            //return $this->redirect()->toRoute('metafisicas');
            
            $view = new ViewModel(array(
                'mensagem' => $this->getMetaTable()->saveMeta($meta),
                'form' => $form,
                'rows' => $this->getMetaTable()->getOrgaos($orgao),
            ));
            $view->setTemplate('maff/metafisicas/addmeta');
            return $view;
        }

        $view = new ViewModel(array(
            'form' => $form,
            'rows' => $this->getMetaTable()->getOrgaos($orgao),
        ));
        $view->setTemplate('maff/metafisicas/addmeta');
        return $view;
    }

    public function editmetaAction() {
        $id = (int)$this->params('form_codigo');
        if (!$id) {
            return $this->redirect()->toRoute('metafisicas', array('action'=>'addmeta'));
        }
        $meta = $this->getMetaTable()->getMeta($id);

        $form = new MetaForm();
        $form->bind($meta);
        $form->get('form_projeto')->setAttributes(array('type' => 'text', 'disabled' => 'disabled'));
        $form->get('form_elemento')->setAttributes(array('type' => 'text', 'disabled' => 'disabled'));
        $form->get('form_fonte')->setAttributes(array('type' => 'text', 'disabled' => 'disabled'));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            //$meta = new Meta();
            $form->setData($request->getPost());
            $meta = $request->getPost();
            //$this->getMetaTable()->saveMeta($meta);
            //return $this->redirect()->toRoute('metafisicas');
            
            $view = new ViewModel(array(
                'mensagem' => $this->getMetaTable()->saveMeta($meta),
                'form_codigo' => $id,
                'form' => $form,
            ));
            $view->setTemplate('maff/metafisicas/editmeta');
            return $view;
        }
        $view = new ViewModel(array(
            'form_codigo' => $id,
            'form' => $form,
        ));
        $view->setTemplate('maff/metafisicas/editmeta');
        return $view;
    }

    public function deletemetaAction() {
        $id = (int)$this->params('form_codigo');
        if (!$id) {
            return $this->redirect()->toRoute('metafisicas');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost()->get('del', 'Cancelar');
            if ($del == 'Confirmar') {
                $id = (int)$request->getPost()->get('id');
                $this->getMetaTable()->deleteMeta($id);
            }
            return $this->redirect()->toRoute('metafisicas');
        }
        
        $view = new ViewModel(array(
            'form_codigo' => $id,
            'meta' => $this->getMetaTable()->getMeta($id)
        ));
        $view->setTemplate('maff/metafisicas/deletemeta');
        return $view;
    }
    
    public function getMetaTable() {
        if (!$this->metaTable) {
            $sm = $this->getServiceLocator();
            $this->metaTable = $sm->get('Maff\Model\MetaTable');
        }
        return $this->metaTable;
    }

    public function execorcsAction() {
        $select = new Select();
        $order_by = $this->params()->fromRoute('order_by') ?
                $this->params()->fromRoute('order_by') : 'projeto';
        $order = $this->params()->fromRoute('order') ?
                $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
        $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;

        $execorcs = $this->getMetaTable()->fetchExecorcs($select->order($order_by . ' ' . $order));
        $itemsPerPage = 10;

        $execorcs->current();
        $paginator = new Paginator(new paginatorIterator($execorcs));
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        $view = new ViewModel(array(
            'order_by' => $order_by,
            'order' => $order,
            'page' => $page,
            'paginator' => $paginator,
        ));
        $view->setTemplate('maff/execorcs/execorcs');
        return $view;
    }

    /**
     * CONSULTAS DO FORMULÁRIO
     */
    public function getRelatoriosTable() {
        if (!$this->relatoriosTable) {
            $sm = $this->getServiceLocator();
            $this->relatoriosTable = $sm->get('Maff\Model\RelatoriosTable');
        }
        return $this->relatoriosTable;
    }

    public function getProjetosAction() {
        $id = filter_input(INPUT_GET, 'unorc_id');
        $projetos = $this->getMetaTable()->getProjetos($id); //print_r($projetos) para debugar
        return $projetos; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }

    public function getProjetosRelAction() {
        $id = filter_input(INPUT_GET, 'unorc_id');
        $projetos = $this->getRelatoriosTable()->getProjetos($id); //print_r($projetos) para debugar
        return $projetos; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }

    public function getElementosAction() {
        $id = filter_input(INPUT_GET, 'projeto_id');
        $elementos = $this->getMetaTable()->getElementos($id);
        return $elementos; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }

    public function getElementosRelAction() {
        $id = filter_input(INPUT_GET, 'projeto_id');
        $elementos = $this->getRelatoriosTable()->getElementos($id);
        return $elementos; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }

    public function getFontesAction() {
        $id = filter_input(INPUT_GET, 'elemento_id');
        $fontes = $this->getMetaTable()->getFontes($id);
        return $fontes; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }

    public function getFontesRelAction() {
        $id = filter_input(INPUT_GET, 'elemento_id');
        $fontes = $this->getRelatoriosTable()->getFontes($id);
        return $fontes; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }
    
    public function getPeriodoAction() {
        $proj_id = filter_input(INPUT_GET, 'projeto_id');
        $el_id = filter_input(INPUT_GET, 'elemento_id');
        $fonte_id = filter_input(INPUT_GET, 'fonte_id');

        $fontes = $this->getRelatoriosTable()->getPeriodo($proj_id, $el_id, $fonte_id);
        return $fontes; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }

    public function getOrcAction() {
        $proj_id = filter_input(INPUT_GET, 'projeto_id');
        $el_id = filter_input(INPUT_GET, 'elemento_id');
        $fonte_id = filter_input(INPUT_GET, 'fonte_id');

        $fontes = $this->getMetaTable()->getOrc($proj_id, $el_id, $fonte_id);
        return $fontes; //Retorna no formato JsonModel(Json) solicitado pelo Formulário
    }

}
