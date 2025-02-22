#!/usr/bin/env php
<?php
include dirname(dirname(dirname(__FILE__))) . '/lib/init.php';
include dirname(dirname(dirname(__FILE__))) . '/class/execution.class.php';
$db->switchDB();
su('admin');

/**

title=测试executionModel->activateTest();
cid=1
pid=1

敏捷执行激活 >> status,suspended,doing
瀑布执行激活 >> status,suspended,doing
看板执行激活 >> status,suspended,doing
修改激活时间 >> begin
修改顺延 >> begin
子瀑布激活获取父瀑布状态 >> doing

*/

$executionIDList = array('105', '136', '165', '106', '107', '708');
$readjustTask    = array('readjustTime' => '1', 'readjustTask' => '1');
$readjustTime    = array('readjustTime' => '1');

$execution = new executionTest();
r($execution->activateTest($executionIDList[0]))                && p('0:field,old,new') && e('status,suspended,doing'); // 敏捷执行激活
r($execution->activateTest($executionIDList[1]))                && p('0:field,old,new') && e('status,suspended,doing'); // 瀑布执行激活
r($execution->activateTest($executionIDList[2]))                && p('0:field,old,new') && e('status,suspended,doing'); // 看板执行激活
r($execution->activateTest($executionIDList[3],$readjustTime))  && p('1:field')         && e('begin');                  // 修改激活时间
r($execution->activateTest($executionIDList[4],$readjustTask))  && p('1:field')         && e('begin');                  // 修改顺延
r($execution->activateTest($executionIDList[5], array(), true)) && p('status')          && e('doing');                  // 子瀑布激活获取父瀑布状态
$db->restoreDB();
