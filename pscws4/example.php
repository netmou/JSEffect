<?php
include('pscws4.class.php');
/**
 * 中文分词处理方法
 * @param string  $text 要处理的字符串
 * @param int $top 返回指定数量
 * @return array
 */
function scws($text, $top = 5) {
    $cws = new pscws4('utf-8');
    $cws -> set_charset('utf-8');
    $cws -> set_dict('etc/dict.utf8.xdb');
    $cws -> set_rule('etc/rules.utf8.ini');
    $cws -> set_ignore(true);
    $cws -> send_text($text);
    return  $cws -> get_tops($top, 'r,v,p');
}