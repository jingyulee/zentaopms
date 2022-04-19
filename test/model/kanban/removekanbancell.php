#!/usr/bin/env php
<?php
include dirname(dirname(dirname(__FILE__))) . '/lib/init.php';
include dirname(dirname(dirname(__FILE__))) . '/class/kanban.class.php';
su('admin');

/**

title=测试 kanbanModel->removeKanbanCell();
cid=1
pid=1

移除common 看板1的卡片1 >> 1:2,801,; 2:3,4,803,; 3:5,6,805,; 4:7,8,807, 
移除common 看板2的卡片3 4 >> 1:2,801,; 2:803,; 3:5,6,805,; 4:7,8,807,
移除story 看板161的卡片244 >> 401; 402; 403; 404; 405; 406; 407; 408; 409; 410; 411; 428:246,; 429; 430; 431; 432; 433; 434; 435; 436; 437; 438:
移除story 看板161的卡片246 >> 401; 402; 403; 404; 405; 406; 407; 408; 409; 410; 411; 428; 429; 430; 431; 432; 433; 434; 435; 436; 437; 438:
移除bug 看板161的卡片181 >> 412:182,183,; 413; 414; 415; 416; 417; 418; 419; 420; 439:184,185,186,; 440; 441; 442; 443; 444; 445; 446; 447:
移除bug 看板161的卡片182 >> 412:183,; 413; 414; 415; 416; 417; 418; 419; 420; 439:184,185,186,; 440; 441; 442; 443; 444; 445; 446; 447:
移除task 看板161的卡片61 >> 421; 422; 423; 424; 425; 426; 427; 448:62,; 449; 450; 451; 452; 453; 454:
移除task 看板161的卡片62 >> 421; 422; 423; 424; 425; 426; 427; 448; 449; 450; 451; 452; 453; 454:

*/

$typeList   = array('common', 'story', 'bug', 'task');
$cardIDList = array(array('1'), array('3', '4'), '244', '246', '181', '182', '61', '62');
$kanbanList = array('1' => '1', '3' => '1', '4' => '1', '244' => '161', '246' => '162', '181' => '161', '182' => '161', '61' => '161', '62' => '162');

$kanban = new kanbanTest();

r($kanban->removeKanbanCellTest($typeList[0], $cardIDList[0], $kanbanList)) && p() && e('1:2,801,; 2:3,4,803,; 3:5,6,805,; 4:7,8,807, ');                                                                      // 移除common 看板1的卡片1
r($kanban->removeKanbanCellTest($typeList[0], $cardIDList[1], $kanbanList)) && p() && e('1:2,801,; 2:803,; 3:5,6,805,; 4:7,8,807,');                                                                           // 移除common 看板2的卡片3 4
r($kanban->removeKanbanCellTest($typeList[1], $cardIDList[2], $kanbanList)) && p() && e('401; 402; 403; 404; 405; 406; 407; 408; 409; 410; 411; 428:246,; 429; 430; 431; 432; 433; 434; 435; 436; 437; 438:'); // 移除story 看板161的卡片244
r($kanban->removeKanbanCellTest($typeList[1], $cardIDList[3], $kanbanList)) && p() && e('401; 402; 403; 404; 405; 406; 407; 408; 409; 410; 411; 428; 429; 430; 431; 432; 433; 434; 435; 436; 437; 438:');      // 移除story 看板161的卡片246
r($kanban->removeKanbanCellTest($typeList[2], $cardIDList[4], $kanbanList)) && p() && e('412:182,183,; 413; 414; 415; 416; 417; 418; 419; 420; 439:184,185,186,; 440; 441; 442; 443; 444; 445; 446; 447:');    // 移除bug 看板161的卡片181
r($kanban->removeKanbanCellTest($typeList[2], $cardIDList[5], $kanbanList)) && p() && e('412:183,; 413; 414; 415; 416; 417; 418; 419; 420; 439:184,185,186,; 440; 441; 442; 443; 444; 445; 446; 447:');        // 移除bug 看板161的卡片182
r($kanban->removeKanbanCellTest($typeList[3], $cardIDList[6], $kanbanList)) && p() && e('421; 422; 423; 424; 425; 426; 427; 448:62,; 449; 450; 451; 452; 453; 454:');                                          // 移除task 看板161的卡片61
r($kanban->removeKanbanCellTest($typeList[3], $cardIDList[7], $kanbanList)) && p() && e('421; 422; 423; 424; 425; 426; 427; 448; 449; 450; 451; 452; 453; 454:');                                              // 移除task 看板161的卡片62
system("./ztest init");