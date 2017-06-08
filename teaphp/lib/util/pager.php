<?php

//////////////////////////////////////////////////////////////////////
// 分页类，可配置分页模版
//////////////////////////////////////////////////////////////////////
class pager
{
    //初始化变量及配置
    public $page_var = 'page';        //分页用的page变量，用来控制url生成，如xxx.php?a=1&teapage=2中的teapage
    //产生连接的字符串
    public $str_firstpage = '首页';        //首页
    public $str_prevpage = '上一页';        //上一页
    public $str_nextpage = '下一页';        //下一页
    public $str_lastpage = '尾页';        //尾页
    //样式设置字符串
    public $css_disable = 'disabled';    //样式表 失效状态
    public $css_active = 'active';        //样式表 当前选择
    public $css_normal = '';            //样式表 正常状态
    //HTML模板
    public $html_tag = 'li';            //HTML标签选择，请配合模板使用
    public $html_tpl = '<ul class="pagination">[firstpage][prevpage][pagebar][nextpage][lastpage]</li></ul>'; //模板
    //数量初始化
    public $pagebar_num = 10;            //控制 数字页面条 显示的个数 [1][2][3][4][5][6][7][8][9][10]
    public $pagesize = 10;            //每页记录数
    public $total = 0;            //总记录数
    public $pagenow = 1;            //当前页
    public $offset = 0;            //记录偏移

    //构造函数
    public function __construct($arr)
    {
        if (is_array($arr)) {
            if (!array_key_exists('total', $arr)) {
                debug::error('Pager Error', __FUNCTION__.' need a param of total');
            }
            $this->total = intval($arr['total']);
            $this->pagesize = (array_key_exists('pagesize', $arr)) ? intval($arr['pagesize']) : $this->pagesize;
            $this->pagenow = (array_key_exists('pagenow', $arr)) ? intval($arr['pagenow']) : $this->pagenow;
            $url = (array_key_exists('url', $arr)) ? $arr['url'] : '';
        } else {
            $this->total = intval($arr);
            $nowindex = '';
            $url = '';
        }
        if ((!is_int($this->total)) || ($this->total < 0)) {
            debug::error('Pager Error', __FUNCTION__.' [total] is not a positive integer!');
        }
        if ((!is_int($this->pagesize)) || ($this->pagesize <= 0)) {
            debug::error('Pager Error', __FUNCTION__.' [pagesize] is not a positive integer!');
        }

        if (!empty($arr['page_var'])) {
            $this->page_var = $arr['page_var'];    //设置页面变量
        }
        $this->pagenow = isset($_GET[$this->page_var]) ? intval($_GET[$this->page_var]) : $this->pagenow;
        $this->totalpages = ceil($this->total / $this->pagesize);
        $this->offset = ($this->pagenow - 1) * $this->pagesize;
    }

    //渲染输出函数
    public function render()
    {
        $pager['total'] = $this->total;
        $pager['pagesize'] = $this->pagesize;
        $pager['totalpages'] = $this->totalpages;
        $pager['pagenow'] = $this->pagenow;
        $pager['firstpage'] = $this->getfirstpage();
        $pager['prevpage'] = $this->getprevpage();
        $pager['nextpage'] = $this->getnextpage();
        $pager['lastpage'] = $this->getlastpage();
        $pager['pagebar'] = $this->getpagebar();
        $html = $this->html_tpl;
        foreach ($pager as $key=>$val) {
            $html = str_replace('['.$key.']', $val, $html);
        }

        return $html;
    }

    //首页
    public function getfirstpage()
    {
        if ($this->pagenow == 1) {
            return $this->_get_link($this->_get_url('#'), $this->str_firstpage, $this->css_disable);
        }

        return $this->_get_link($this->_get_url(), $this->str_firstpage, $this->css_normal);
    }

    //上一页
    public function getprevpage()
    {
        if ($this->pagenow > 1) {
            return $this->_get_link($this->_get_url($this->pagenow - 1), $this->str_prevpage, $this->css_normal);
        }

        return $this->_get_link($this->_get_url('#'), $this->str_prevpage, $this->css_disable);
    }

    //下一页
    public function getnextpage()
    {
        if ($this->pagenow < $this->totalpages) {
            return $this->_get_link($this->_get_url($this->pagenow + 1), $this->str_nextpage, $this->css_normal);
        }

        return $this->_get_link($this->_get_url('#'), $this->str_nextpage, $this->css_disable);
    }

    //最尾页
    public function getlastpage()
    {
        if ($this->pagenow == $this->totalpages) {
            return $this->_get_link($this->_get_url('#'), $this->str_lastpage, $this->css_disable);
        }

        return $this->_get_link($this->_get_url($this->totalpages), $this->str_lastpage, $this->css_normal);
    }

    //页码盘 [1][2][3][4][5][6][7][8][9][10]
    public function getpagebar()
    {
        $plus = ceil($this->pagebar_num / 2);
        if (($this->pagebar_num - $plus + $this->pagenow) > $this->totalpages) {
            $plus = ($this->pagebar_num - ($this->totalpages - $this->pagenow));
        }
        $begin = $this->pagenow - $plus + 1;
        $begin = ($begin >= 1) ? $begin : 1;
        $return = '';
        for ($i = $begin; $i < $begin + $this->pagebar_num; $i++) {
            if ($i <= $this->totalpages) {
                if ($i != $this->pagenow) {
                    $return .= $this->_get_link($this->_get_url($i), $i, $this->css_normal);
                } else {
                    $return .= $this->_get_link($this->_get_url('#'), $i, $this->css_active);
                }
            } else {
                break;
            }
            $return .= "\n";
        }
        unset($begin);

        return $return;
    }

    public function select()
    {
        $return = '<select name="Tea_Page_Select">';
        for ($i = 1; $i <= $this->totalpages; $i++) {
            if ($i == $this->pagenow) {
                $return .= '<option value="'.$i.'" selected>'.$i.'</option>';
            } else {
                $return .= '<option value="'.$i.'">'.$i.'</option>';
            }
        }
        unset($i);
        $return .= '</select>';

        return $return;
    }

    public function _get_tag($start = 0, $style = '')
    {
        if ($start) {
            if (!empty($style)) {
                return '<'.$this->html_tag.' class="'.$style.'">';
            } else {
                return '<'.$this->html_tag.'>';
            }
        } else {
            return '</'.$this->html_tag.'>';
        }
    }

    public function _get_url($pageno = 1)
    {
        //if(strpos('#',$pageno)>-1)	return $pageno; //如果是#号连接
        return url('', ['page'=>$pageno]);
    }

    //内部函数，获取连接
    public function _get_link($url, $text, $style = '')
    {
        return $this->_get_tag(1, $style).'<a href="'.$url.'">'.$text.'</a>'.$this->_get_tag();
    }
}
