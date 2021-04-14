<?php

class Filter{

    /**
     * @note 涉政涉黄敏感关键词监测
     *
     * @param [type] $[name] [<description>]
     * @author [xiaofeng] <[<luxiaofneg.200@163.com>]>
     * @Date 2021/04/03
     * @return object|bool
     */
    public  function  checkIsError($str =''){
            $filterWordList = [];
            $words = $str; #待输入的字符
            $filterWordList[]=preg_replace("/\s+/i","",str_ireplace(array("|","丨","/"), array(",",",",","),$words));

            $filterWordList[]=preg_replace("/\s+/i","",str_ireplace(array("|","丨","/"), array(",",",",","),$words));

            preg_match_all("/[\x{4e00}-\x{9fa5}A-Za-z0-9_]+/u", strip_tags($str), $result, PREG_PATTERN_ORDER);
            $content = "";
            foreach ($result as $each) {
                foreach ($each as $val) {
                    $content .= $val;
                }
            }
    //同样的检测方法
            $wordArr=$this->segment_word->toSegment2($content);
            foreach($wordArr as $word){
                $memkey=$this->cache->memcache->get(trim($word));
                if($memkey){
                    return array("badword"=>$word);
                }
            }
    }
}
