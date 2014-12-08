<?php
IN_MY_PHP || die(0);
/**
 * 一个简单的分页类
 * @author netmou <leiyanfo@sina.com>
 */
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
     * @param offset   每页显示的条目数
     * @param total     总条目数
     * @param curpage   当前被选中的页
     * @param pagelink    每个分页的链接
     */
    public function __construct($offset, $total, $curpage, $pagelink) {
        $this->offset = intval($offset);
        $this->total = intval($total);
        $this->curpage = max(1, intval($curpage));
        $this->pagetotal = ceil($total / $offset);
        $this->pagelink = $pagelink;
        $this->start = ($this->curpage - 1) * $this->offset;
    }

    /**
     * @return int 分页开始的位置
     */
    public function getStart() {
        return $this->start;
    }
    /**
     * @param showpage   每次显示的页数
     */
    public function showpage($list=6) {
        $page_frag=null;
        $pages = ceil($this->total / $this->offset);
        $homepage = $this->pagelink . '1';
        $rearpage = $this->pagelink . $pages;
        $prevpage = $this->pagelink . ($this->curpage - 1);
        $page_frag.= "<span class='pages'>共<b>{$this->total}</b>条记录</span>";
        $page_frag.= "<span class='pages'><b>{$this->curpage}/{$pages}</b>页</span>";
        $page_frag.= "<span class='pages'>";
        $page_frag.= "<a href='{$homepage}'><span class='first'>首页</span></a>";
        if ($this->curpage == 1) {
            $page_frag.= "<a class='disable' href='#'><span class='prev'>上一页</span></a>";
        } else {
            $page_frag.= "<a  href='{$prevpage}'><span class='prev'>上一页</span></a>";
        }
        $from = max(floor($this->curpage - $list / 2), 1);
        $to = min(ceil($this->curpage + $list / 2), $pages);
        for ($i = $from; $i < $this->curpage; $i++) {
            $pageaddr = $this->pagelink . $i;
            $page_frag.= "<a href='{$pageaddr}'><span class='digit'>{$i}</span></a>";
        }
        $page_frag.= "<a class='disable' href='#'><span class='digit cur'>{$this->curpage}</span></a>";
        for ($i = $this->curpage + 1; $i <= $to; $i++) {
            $pageaddr = $this->pagelink . $i;
            $page_frag.= "<a href='{$pageaddr}'><span class='digit'>{$i}</span></a>";
        }
        $nextpage = $this->pagelink . ($this->curpage + 1);
        if ($this->curpage == $pages) {
            $page_frag.= "<a class='disable' href='#'><span class='next'>下一页</span></a>";
        } else {
            $page_frag.= "<a  href='{$nextpage}'><span class='next'>下一页</span></a>";
        }
        $page_frag.= "<a href='{$rearpage}'><span class='last'>尾页</span></a>";
        return $page_frag.= '</span>';
    }
}

?>
