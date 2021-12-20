<?php
/**
 * The view file of kanban module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2021 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Guangming Sun<sungangming@easycorp.ltd>
 * @package     kanban
 * @version     $Id: view.html.php 935 2021-12-09 10:49:24Z $
 * @link        https://www.zentao.net
 */
?>
<?php include '../../common/view/header.html.php';?>
<?php include '../../common/view/kanban.html.php';?>

<?php
$laneCount = 0;
foreach($regions as $region) $laneCount += $region->laneCount;

js::set('regions', $regions);
js::set('kanbanLang', $lang->kanban);
js::set('kanbanlaneLang', $lang->kanbanlane);
js::set('kanbancolumnLang', $lang->kanbancolumn);
js::set('kanbancardLang', $lang->kanbancard);
js::set('kanbanID', $kanban->id);
js::set('laneCount', $laneCount);
js::set('userList', $userList);
js::set('noAssigned', $lang->kanbancard->noAssigned);
js::set('users', $users);

js::set('priv',
    array(
        'canAssignCard' => common::hasPriv('kanban', 'assigncard'),
    ));

$canSortRegion   = commonModel::hasPriv('kanban', 'sortRegion') && count($regions) > 1;
$canEditRegion   = commonModel::hasPriv('kanban', 'editRegion');
$canDeleteRegion = commonModel::hasPriv('kanban', 'deleteRegion');
$canCreateLane   = commonModel::hasPriv('kanban', 'createLane');
?>

<div id="kanban">
  <?php foreach($regions as $region):?>
  <div class="region<?php if($canSortRegion) echo ' sort';?>" data-id="<?php echo $region->id;?>">
    <div class="region-header dropdown">
      <span class="strong"><?php echo $region->name;?></span>
      <label class="label label-region"><?php echo $this->lang->kanbanlane->common . ' ' . $region->laneCount;?></label>
      <span><i class="icon icon-chevron-double-up" data-id="<?php echo $region->id;?>"></i></span>
      <?php if($canEditRegion || $canCreateLane || $canDeleteRegion):?>
      <button class="btn btn-link action" type="button" data-toggle="dropdown"><i class="icon icon-ellipsis-v"></i></button>
      <ul class="dropdown-menu pull-right">
        <?php if($canEditRegion) echo '<li>' . html::a(inlink('editRegion', "regionID={$region->id}", '', 1), $this->lang->kanban->editRegion, '', 'class="iframe"') . '</li>';?>
        <?php if($canCreateLane) echo '<li>' . html::a(inlink('createLane', "kanbanID={$kanban->id}&regionID={$region->id}", '', 1), $this->lang->kanban->createLane, '', "class='iframe'") . '</li>';?>
        <?php if($canDeleteRegion and count($regions) > 1) echo '<li>' . html::a(inlink('deleteRegion', "regionID={$region->id}"), $this->lang->kanban->deleteRegion, "hiddenwin") . '</li>';?>
      </ul>
      <?php endif;?>
    </div>
    <div id='kanban<?php echo $region->id;?>' data-id='<?php echo $region->id;?>' class='kanban'></div>
  </div>
  <?php endforeach;?>
</div>
<div id='moreTasks'></div>
<div id='moreColumns'></div>
<?php include '../../common/view/footer.html.php';?>
