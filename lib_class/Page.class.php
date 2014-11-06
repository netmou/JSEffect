<?php

class Page {

    private $offset; //每页显示的条目数
    private $start; //开始位置
    private $total; //总条目数
    private $curpage; //当前被选中的页
    private $showpage; //每次显示的页数
    private $pagetotal; //总页数
    private $page_array = array(); //用来构造分页的数组
    private $pagelink; //每个分页的链接

    /**
     * @$offset   每页显示的条目数
     * +---------------------------------------------------------------
     * @total     总条目数
     * @current_num     当前被选中的页
     * +---------------------------------------------------------------
     * @showpage       每次显示的页数
     * +---------------------------------------------------------------
     * @pagelink    每个分页的链接
     * +---------------------------------------------------------------
     * example：   第1/453页 [首页] [上页] [1] [2] [3] [4] [下页] [尾页]
     */

    function __construct($offset, $total, $curpage, $pagelink, $showpage = 6) {
        $this->offset = intval($offset);
        $this->total = intval($total);
        $this->curpage = max(1, intval($curpage));
        $this->showpage = intval($showpage);
        $this->pagetotal = ceil($total / $offset);
        $this->pagelink = $pagelink;
        $this->start = ($this->curpage - 1) * $this->offset;
    }

    function getStart() {
        return $this->start;
    }

    function showpage() {
        $pages = ceil($this->total / $this->offset);
        $homepage = $this->pagelink . '1';
        $rearpage = $this->pagelink . $pages;
        $prevpage = $this->pagelink . ($this->curpage - 1);
        echo "<span class='pagesinfo'>共<b>{$this->total}</b>条记录</span>";
        echo "<span class='pagesinfo'><b>{$this->curpage}/{$pages}</b>页</span>";
        echo "<span id='pages'>";
        echo "<a href='{$homepage}'><span>首页</span></a>";
        if ($this->curpage == 1) {
            echo "<a class='disable' href='#'><span>上一页</span></a>";
        } else {
            echo "<a  href='{$prevpage}'><span>上一页</span></a>";
        }
        $from = max(floor($this->curpage - $this->showpage / 2), 1);
        $to = min(ceil($this->curpage + $this->showpage / 2), $pages);
        for ($i = $from; $i < $this->curpage; $i++) {
            $pageaddr = $this->pagelink . $i;
            echo "<a href='{$pageaddr}'><span>{$i}</span></a>";
        }
        echo "<a class='disable' href='#'><span>{$this->curpage}</span></a>";
        for ($i = $this->curpage + 1; $i <= $to; $i++) {
            $pageaddr = $this->pagelink . $i;
            echo "<a href='{$pageaddr}'><span>{$i}</span></a>";
        }
        $nextpage = $this->pagelink . ($this->curpage + 1);
        if ($this->curpage == $pages) {
            echo "<a class='disable' href='#'><span>下一页</span></a>";
        } else {
            echo "<a  href='{$nextpage}'><span>下一页</span></a>";
        }
        echo "<a href='{$rearpage}'><span>尾页</span></a>";
        echo '</span>';
    }
}

?>
