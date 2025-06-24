<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,父母邦，帮父母
// 日 期：2016年4月12日
// 作　者：卢晓峰
// E-mail :xiaofeng.lu@fumubang.com
// 文件名 :bqg24Model.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:笔趣阁24书源更新
// ///////////////////////////////////////////////////

use QL\QueryList;
use Overtrue\Pinyin\Pinyin;

class Bqg24Model{


    public static   $timeout = 900; // 默认缓存过期时间为15分钟




    /**
    * @note 获取笔趣阁的章节列表
    *
    * @param  $html string 采集详情内容
    * @param $title string 标题
    * @param $story_link string 小说详情内容
    * @return
    */

    public static function getBqg24ChapterList($html,$title,$story_link){
        if(!$html){
            return false;
        }
        $referer = parse_url($story_link);
        $title = NovelModel::exchangePregStr($title);
        $contents = '';
        //匹配原来的
        // if( preg_match('/《' . $title . '》正文.*<div class="clear"><\/div>/ism', $html, $with_content)){
        //     $contents = $with_content[0] ?? '';
        // } else 
        if (preg_match('/<ul class="mulu_list">.*?<div class="clear"><\/div>/',$html,$with_content)){//匹配不到重新匹配一次
            $contents = $with_content[0] ?? '';
        }else if(preg_match('/《' . $title . '》正文.*<div class="clear"><\/div>/ism', $html, $with_content)){
             $contents = $with_content[0] ?? '';
        }
            
            
        $link_reg = '/<a.*?href="(.*?)".*?>/';
        //这个地方因为A链接有可能是多个的形式展示，导致取不出来A标签，用一个万能的表达式来根据当前的配置取相关的连接信息
        $text_reg = '/<a.*?href=\"[^\"]*\".*?>(.*?)<\/a>/ims'; //匹配链接里的文本(zhge)
        $chapter_list = [];
        if($contents){
            $contents = str_replace('href =', 'href=', $contents);
            preg_match_all($link_reg, $contents, $link_href); //匹配链接
            preg_match_all($text_reg, $contents, $link_text); //匹配文本;
            $link_text = array_map('trimBlankLine', $link_text);
            $len = count($link_href[1]);
            $result = [];
            for($i = 0;$i<$len;$i++){
                $text =  trimBlankSpace($link_text[1][$i]) ;
                $text = str_replace("<span>",'',$text);
                $text = str_replace("</span>",'',$text);
                $chapter_list[] = [
                    'link_name' =>  $text ,
                    'link_url'  =>$referer['path'] .$link_href[1][$i] ?? '',
                ];
            }
        }
        return $chapter_list;
    }



    /**
    * @note 获取首页更新的小说
    *
    * @param  $url array 列表信息
    * @return  $url
    */
    public static  function getUpdateList($url){
        if(!$url){
            return false;
        }

        global $redis_data,$urlRules;
        $range = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['bqg24_range'];
        $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['bqg24_index'];
        $source = NovelModel::getSourceUrl($url);
        $res = StoreModel::swooleRquest($url);
        // echo '<pre>';
        $index_data = array_values($res);
        $data = $index_data[0] ?? [];
        $storyList = QueryList::html($data)
                        ->rules($rules)
                        ->range($range)
                        ->query()
                        ->getData();
        $storyList = $storyList->all();
        if(!empty($storyList)){
            foreach($storyList as &$val){
                if(!$val || !isset($val['story_link'])){
                    continue;
                }
                //解析获取story_id
                $story_link = trim($val['story_link']);
                $urlData = parse_url($story_link);
                $path_result  = $urlData['path'];
                $path_result = str_replace('/article/' , '', $path_result);
                $path = preg_replace('/\//','_',$path_result);
                $story_id = substr($path,  0 , -1);
                $val['story_id'] = $story_id;
                $val['source'] = $source;
            }
        }
        return $storyList;
    }
}
?>