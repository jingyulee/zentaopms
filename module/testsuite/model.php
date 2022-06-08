<?php
/**
 * The model file of test suite module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html) or AGPL
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     testsuite
 * @version     $Id: model.php 5114 2013-07-12 06:02:59Z chencongzhi520@gmail.com $
 * @link        http://www.zentao.net
 */
?>
<?php
class testsuiteModel extends model
{
    /**
     * Build select string.
     *
     * @param  array  $products
     * @param  int    $productID
     * @param  string $currentModule
     * @param  string $currentMethod
     * @param  string $extra
     * @access public
     * @return string
     */
    public function select($products, $productID, $currentModule, $currentMethod, $extra = '')
    {
        if(!$productID)
        {
            unset($this->lang->product->setMenu->branch);
            return;
        }

        setcookie("lastProduct", $productID, $this->config->cookieLife, $this->config->webRoot, '', $this->config->cookieSecure, true);
        $currentProduct = $this->product->getById($productID);

        $dropMenuLink = helper::createLink('product', 'ajaxGetDropMenu', "objectID=$productID&module=$currentModule&method=$currentMethod&extra=$extra");
        $output = "<div class='btn-group angle-btn'><div class='btn-group'><button data-toggle='dropdown' type='button' class='btn btn-limit' id='currentItem' ><span class='text'>{$currentProduct->name}</span> <span class='caret'></span></button><div id='dropMenu' class='dropdown-menu search-list' data-ride='searchList' data-url='$dropMenuLink'>";
        $output .= '<div class="input-control search-box has-icon-left has-icon-right search-example"><input type="search" class="form-control search-input" /><label class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label><a class="input-control-icon-right search-clear-btn"><i class="icon icon-close icon-sm"></i></a></div>';
        $output .= "</div></div>";
        if($currentProduct->type != 'normal')
        {
            $this->app->loadLang('branch');
            $branchName = $this->lang->branch->main;
            $output .= "<div class='btn-group'><button id='currentBranch' type='button' class='btn btn-limit'>{$branchName} </button></div></div>";
        }
        $output .= '</div>';

        return $output;
    }

    /**
     * Create a test suite.
     *
     * @param  int   $productID
     * @access public
     * @return bool|int
     */
    public function create($productID)
    {
        $suite = fixer::input('post')
            ->trim('name')
            ->stripTags($this->config->testsuite->editor->create['id'], $this->config->allowedTags)
            ->setIF($this->config->systemMode == 'new' and $this->lang->navGroup->testsuite != 'qa', 'project', $this->session->project)
            ->add('product', (int)$productID)
            ->add('addedBy', $this->app->user->account)
            ->add('addedDate', helper::now())
            ->remove('uid')
            ->get();
        $suite = $this->loadModel('file')->processImgURL($suite, $this->config->testsuite->editor->create['id'], $this->post->uid);
        $this->dao->insert(TABLE_TESTSUITE)->data($suite)
            ->batchcheck($this->config->testsuite->create->requiredFields, 'notempty')
            ->checkFlow()
            ->exec();
        if(!dao::isError())
        {
            $suiteID = $this->dao->lastInsertID();
            $this->file->updateObjectID($this->post->uid, $suiteID, 'testsuite');
            return $suiteID;
        }
        return false;
    }

    /**
     * Get test suites of a product.
     *
     * @param  int    $productID
     * @param  string $orderBy
     * @param  object $pager
     * @param  string $param
     * @access public
     * @return array
     */
    public function getSuites($productID, $orderBy = 'id_desc', $pager = null, $param = '')
    {
        return $this->dao->select("*")->from(TABLE_TESTSUITE)
            ->where('product')->eq((int)$productID)
            ->beginIF($this->config->systemMode == 'new' and $this->lang->navGroup->testsuite != 'qa')->andWhere('project')->eq($this->session->project)->fi()
            ->andWhere('deleted')->eq(0)
            ->beginIF(strpos($param, 'all') === false)->andWhere("(`type` = 'public' OR (`type` = 'private' and addedBy = '{$this->app->user->account}'))")->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /**
     * Get unit suite.
     *
     * @param  int    $productID
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function getUnitSuites($productID, $orderBy = 'id_desc')
    {
        return $this->dao->select("*")->from(TABLE_TESTSUITE)
            ->where('product')->eq((int)$productID)
            ->andWhere('deleted')->eq(0)
            ->andWhere('type')->eq('unit')
            ->orderBy($orderBy)
            ->fetchAll('id');
    }

    /**
     * Get test suite info by id.
     *
     * @param  int   $suiteID
     * @param  bool  $setImgSize
     * @access public
     * @return object
     */
    public function getById($suiteID, $setImgSize = false)
    {
        $suite = $this->dao->select('*')->from(TABLE_TESTSUITE)->where('id')->eq((int)$suiteID)->fetch();
        $suite = $this->loadModel('file')->replaceImgURL($suite, 'desc');
        if($setImgSize) $suite->desc = $this->file->setImgSize($suite->desc);
        return $suite;
    }

    /**
     * Update a test suite.
     *
     * @param  int   $suiteID
     * @access public
     * @return bool|array
     */
    public function update($suiteID)
    {
        $oldSuite = $this->dao->select("*")->from(TABLE_TESTSUITE)->where('id')->eq((int)$suiteID)->fetch();
        $suite    = fixer::input('post')
            ->stripTags($this->config->testsuite->editor->edit['id'], $this->config->allowedTags)
            ->add('id', $suiteID)
            ->add('lastEditedBy', $this->app->user->account)
            ->add('lastEditedDate', helper::now())
            ->remove('uid')
            ->get();
        $suite = $this->loadModel('file')->processImgURL($suite, $this->config->testsuite->editor->edit['id'], $this->post->uid);
        $this->dao->update(TABLE_TESTSUITE)->data($suite)
            ->autoCheck()
            ->batchcheck($this->config->testsuite->edit->requiredFields, 'notempty')
            ->checkFlow()
            ->where('id')->eq($suiteID)
            ->exec();
        if(!dao::isError())
        {
            $this->file->updateObjectID($this->post->uid, $suiteID, 'testsuite');
            return common::createChanges($oldSuite, $suite);
        }
        return false;
    }

    /**
     * Link cases.
     *
     * @param  int   $suiteID
     * @access public
     * @return void
     */
    public function linkCase($suiteID)
    {
        if($this->post->cases == false) return;
        $postData = fixer::input('post')->get();
        foreach($postData->cases as $caseID)
        {
            $row = new stdclass();
            $row->suite      = $suiteID;
            $row->case       = $caseID;
            $row->version    = $postData->versions[$caseID];
            $this->dao->replace(TABLE_SUITECASE)->data($row)->exec();
        }
    }

    /**
     * Get linked cases for suite.
     *
     * @param  int    $suiteID
     * @param  string $orderBy
     * @param  object $pager
     * @param  bool   $append
     * @access public
     * @return array
     */
    public function getLinkedCases($suiteID, $orderBy = 'id_desc', $pager = null, $append = true)
    {
        $suite = $this->getById($suiteID);
        $cases = $this->dao->select('t1.*,t2.version as caseVersion')->from(TABLE_CASE)->alias('t1')
            ->leftJoin(TABLE_SUITECASE)->alias('t2')->on('t1.id=t2.case')
            ->where('t2.suite')->eq($suiteID)
            ->beginIF($this->config->systemMode == 'new' and $this->lang->navGroup->testsuite != 'qa')->andWhere('t1.project')->eq($this->session->project)->fi()
            ->andWhere('t1.product')->eq($suite->product)
            ->andWhere('t1.product')->eq($suite->product)
            ->andWhere('t1.deleted')->eq(0)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
        if(!$append) return $cases;

        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'testcase', false);
        return $this->loadModel('testcase')->appendData($cases);
    }

    /**
     * Get unlinked cases for suite.
     *
     * @param  object $suite
     * @param  int    $param
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getUnlinkedCases($suite, $param = 0, $pager = null)
    {
        if($this->session->testsuiteQuery == false) $this->session->set('testsuiteQuery', ' 1 = 1');
        $queryID = (int)$param;
        if($queryID)
        {
            $query = $this->loadModel('search')->getQuery($queryID);
            if($query)
            {
                $this->session->set('testsuiteQuery', $query->sql);
                $this->session->set('testsuiteForm', $query->form);
            }
        }

        $query = $this->session->testsuiteQuery;
        $allProduct = "`product` = 'all'";
        if(strpos($query, '`product` =') === false) $query .= " AND `product` = {$suite->product}";
        if(strpos($query, $allProduct) !== false) $query = str_replace($allProduct, '1', $query);

        $linkedCases = $this->getLinkedCases($suite->id, 'id_desc', null, $append = false);
        $cases = $this->dao->select('*')->from(TABLE_CASE)->where($query)
            ->andWhere('id')->notIN(array_keys($linkedCases))
            ->beginIF($this->config->systemMode == 'new' and $this->lang->navGroup->testsuite != 'qa')->andWhere('project')->eq($this->session->project)->fi()
            ->andWhere('deleted')->eq(0)
            ->orderBy('id desc')
            ->page($pager)
            ->fetchAll();
        return $cases;
    }

    /**
     * Delete suite and library.
     *
     * @param  int    $suiteID
     * @param  string $table
     * @access public
     * @return bool
     */
    public function delete($suiteID, $table = '')
    {
        parent::delete(TABLE_TESTSUITE, $suiteID);
        $this->dao->delete()->from(TABLE_SUITECASE)->where('suite')->eq($suiteID)->exec();
        return !dao::isError();
    }

    /**
     * Get not imported cases.
     *
     * @param  int    $productID
     * @param  int    $libID
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getNotImportedCases($productID, $libID, $orderBy = 'id_desc', $pager = null, $browseType = '', $queryID = 0)
    {
        $importedCases = $this->dao->select('fromCaseID')->from(TABLE_CASE)
            ->where('product')->eq($productID)
            ->andWhere('lib')->eq($libID)
            ->andWhere('fromCaseID')->ne('')
            ->andWhere('deleted')->eq(0)
            ->fetchPairs('fromCaseID', 'fromCaseID');

        $query = '';
        if($browseType == 'bysearch')
        {
            if($queryID)
            {
                $this->session->set('testsuiteQuery', ' 1 = 1');
                $query = $this->loadModel('search')->getQuery($queryID);
                if($query)
                {
                    $this->session->set('testsuiteQuery', $query->sql);
                    $this->session->set('testsuiteForm', $query->form);
                }
            }
            else
            {
                if($this->session->testsuiteQuery == false) $this->session->set('testsuiteQuery', ' 1 = 1');
            }

            $query  = $this->session->testsuiteQuery;
            $allLib = "`lib` = 'all'";
            $withAllLib = strpos($query, $allLib) !== false;
            if($withAllLib)  $query  = str_replace($allLib, 1, $query);
            if(!$withAllLib) $query .= " AND `lib` = '$libID'";
        }

        return $this->dao->select('*')->from(TABLE_CASE)->where('deleted')->eq(0)
            ->beginIF($browseType != 'bysearch')->andWhere('lib')->eq($libID)->fi()
            ->beginIF($browseType == 'bysearch')->andWhere($query)->fi()
            ->andWhere('product')->eq(0)
            ->andWhere('id')->notIN($importedCases)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    
    /**
     * Build testsuite menu. 
     * 
     * @param  object $suite 
     * @param  string $type 
     * @access public
     * @return string
     */
    public function buildOperateMenu($suite, $type = 'view')
    {
        $menu   = '';
        $params = "suiteID=$suite->id";

        if($type == 'view') $menu .= $this->buildFlowMenu('testsuite', $suite, $type, 'direct');

        $menu .= $this->buildMenu('testsuite', 'linkCase', $params, $suite, $type, 'link', '', '', '', '', $this->lang->testsuite->linkCase);
        $menu .= $this->buildMenu('testsuite', 'edit',     $params, $suite, $type);
        $menu .= $this->buildMenu('testsuite', 'delete',   $params, $suite, $type, 'trash', 'hiddenwin');

        return $menu;
    }
}
