<?php
include "lib_class/init.class.php";
$m=Master::create(array('Guard'));
$mysql=$m['MysqlDriver'];
var_export("fdsjkalj");
// function test(){
//     global $mysql;
//     $mysql->execute('select 1from 2');
// }
// test();
?>
<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <title>测试</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="/css/master.css" type="text/css">
    </head>
    <body>
        这里收集了集中常见js效果案例，有改动的。
        <br />
        <a href="scroll/scroll.html">水平滚动的图片</a><br />
        <a href="scroll/scroll-top.html">垂直滚动的图片</a><br />
        <a href="focus/focus.html">大焦点图</a><br />
        <a href="focus1/focus.html">新闻焦点图</a><br />
        <a href="fixadv/fixadv.html">相对固定【适用ie6】</a>
    </body>
</html>
