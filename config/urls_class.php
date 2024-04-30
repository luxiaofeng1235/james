<?php
return [
    //泡书吧的规则
    'paoshu8'    =>  [
        //小说列表的轮训取值范围
        'range_update'  =>'#newscontent .l li',
        'range_ruku'    => '#newscontent .r li', //入库下的列表
        //小说列表数据
        'update_list'  =>  [
            'story_link'       => ['.s2 a','href','',function($content){
                 //利用回调函数补全相对链接
                $baseUrl = Env::get('APICONFIG.PAOSHU_NEW_HOST');
                return $baseUrl.$content;
            }],//链接地址
            'title'     =>  ['.s2 a','text'],//标题
            'cate_name' =>  ['.s1','text','',function($string){
                 $t = preg_replace('/\[|\]/','',$string);
                 return $t;
            }], //分类
            'author'    =>  ['.s4','text'],//作者名称
            //存储对应的story_id
            'story_id'  =>['.s2 a','href' , '',function($item){
                preg_match('/\d+/', $item,$matches);
                $story_id = $matches[0] ?? 0;
                return $story_id;
            }], //存一下对应的连接
            'nearby_chapter'    =>['.s3','text'],//最新更新的章节
        ],
        //最新入库的列表
        'ruku_list' =>[
            'story_link'       => ['.s2 a','href','',function($content){//链接地址
                  //利用回调函数补全相对链接
                $baseUrl = Env::get('APICONFIG.PAOSHU_NEW_HOST');
                return $baseUrl.$content;
            }],
            'title'     =>  ['.s2 a','text'],//标题
             'cate_name' =>  ['.s1','text','',function($string){
                 $t = preg_replace('/\[|\]/','',$string);
                 $cate_name = $t . '小说';
                 return $cate_name;
            }], //分类
            'author'    =>  ['.s5','text'],//作者名称
             //存储对应的story_id
            'story_id'  =>['.s2 a','href' , '',function($item){
                preg_match('/\d+/', $item,$matches);
                $story_id = $matches[0] ?? 0;
                return $story_id;
            }], //存一下对应的连接
             'nearby_chapter'  =>['.s30','text'],

        ],
        //详情页面的URL
        'detail_url'   =>[
            'path'=>        ['meta[property=og:novel:read_url]','content'],//小说的当前阅读链接
        ],
        'list_home'  =>  [
            'story_link'       => ['span:eq(1) a','href'],
            'title'     =>  ['span:eq(1) a','text'],
        ],
        //章节列表数据
        'chapter_list'  =>  [
            'link_url'       => ['a','href'],
            'link_name'     =>  ['a','text'],

        ],
        //小说详情
        'info'  =>[
            'cover_logo'       =>['#fmimg img','src'],//小说封面
            'author'    => ['meta[property=og:novel:author]','content'],//小说作者
            'title'     =>['#info>h1','text'],//小说标题
            'cate_name' =>['meta[property=og:novel:category]','content'],//分类
            'status'    =>['meta[property=og:novel:status]','content'],//小说的状态
            'third_update_time'    =>['meta[property=og:novel:update_time]','content'], //最近的更新时间
            'nearby_chapter'    =>['meta[property=og:novel:latest_chapter_name]','content'], //最近的文章
            'intro' =>['meta[property=og:description]','content'],
            'tag'   => ['meta[property=og:novel:category]','content'],
            'location'  =>  ['.con_top','text'],//小说的面包屑位置
        ],
         //小说详情替换网站的采集方案
        'info_replace'  =>[
            'title'     =>['meta[property=og:novel:book_name]','content'],//小说标题
            'author'    =>['meta[property=og:novel:author]','content'],//小说作者
            'cover_logo'       =>['meta[property=og:image]','content','',function($image){
                $cover_logo = '';
                if(strpos($image, 'otcwuxi')){
                    //判断是否存在https中但是图片用的http保存会有问题
                    if(!preg_match('/https/',$image)){
                       $cover_logo = str_replace('http','https',$image);
                    }
                    $url = $cover_logo ?? $image;
                }else{
                    $url = $image;
                }
               return $url;
            }],//小说封面
            'cate_name' =>['meta[property=og:novel:category]','content'],//分类
            'status'    =>['meta[property=og:novel:status]','content'],//小说的状态
            'third_update_time'    =>['meta[property=og:novel:update_time]','content'], //最近的更新时间
            'nearby_chapter'    =>['meta[property=og:novel:latest_chapter_name]','content'], //最近的文章
            // 'intro' =>array('meta[property=og:description]','content'),
            'intro' =>  ['#intro','text'],//简介
            'tag'   => ['meta[property=og:novel:category]','content'],
            // 'location'  =>  array('.path .p','text' ,'', function($location){
            //     $str = array_iconv($location);
            //     $str = str_replace('加入书架','',$str);//替换调这个没啥用
            //     return $str;
            // }),//小说的面包屑位置
            'location'  =>['.con_top','text','',function($location){
                $str = str_replace('最新章节列表','',$location);//
                return $str;

            }],//面包屑
        ],
        //手机端的采集规则
        'mobile_content'   => [
            'first_line'    =>['.body>.text p:eq(0)','text'], //获取第一行
            'content'    =>['.body>.text','html'],//正文
            'meta_data'       =>['meta[property="og:url"]','content'], //meta标签
            // 'href'      =>['.navigator-nobutton a:eq(3)','href'],
        ],
        //web采集文章的内容配置
        'content'   => [
            'content'    =>['#content','html','-div'],
            'meta_data'       =>['meta[name=mobile-agent]','content'],
            'href'      =>['.con_top a:eq(2)','href'],
        ],
        'content_replace'   => [
            'content'    =>['#content','html'],
            'meta_data'       =>['meta[http-equiv="mobile-agent"]','content'],
             'href'      =>['.page_chapter li:eq(0)','html','',function($content){
                 //匹配正则
                $link_reg = '/<a.+?href=\"(.+?)\".*>/i';
                preg_match($link_reg, $content,$matches);
                $url = '';
                if(isset($matches[1])){
                    $url = $matches[1] ??'';
                }
                return $url;
             }],
        ],
    ],
    //台湾站的小说采集规则
    'twking'   =>[
        /////////////////////////完本类型排行榜
        'page_range'    =>  '.lastupdate .list-out',//分页循环的PC的列表范围
        //PC端分页列表
        'page_list' =>[
            'title' =>['.w80 em:eq(0) a','text'],  //小说名
            'author'    => ['.gray','text','',function($item){//小说作者处理
                $author_reg = '/[0-9]{1,}-[0-9]{1,}/';
                $str  = preg_replace($author_reg, '',$item);
                $str = trimBlankSpace($str);//去除首尾空格
                return $str;
            }],
            'story_link'    =>['.w80 em:eq(0) a','href'],//小说链接
             'story_id'  =>['.w80 em:eq(0) a','href' , '',function($item){//小说网站ID
                $urlData = parse_url($item);
                $story_id = str_replace('/', '',  $urlData['path']);
                return $story_id ?? '';
            }], //存一下对应的
        ],
        ///////////////获取每个分页的页码
        'page_ret'  =>  [
            'currentPage'  => [ '.articlepage em' , 'text'], //获取分页
        ],
        ///////////////////////////小说详情页相关
        'chapter_range' =>  '.liebiao li',//章节循环的范围
         //章节列表数据
        'chapter_list'  =>  [
            'link_url'       => ['a','href'],
            'link_name'     =>  ['a','text'],
        ],
        /////////////小说详情页的获取分类和连载状态
         //分类和连载的状态信息
        'detail_info'   =>  [
            'cover_logo' =>  ['meta[property=og:image]','content'],//小说封面
            'title' =>  ['meta[property=og:novel:book_name]','content'],//小说标题
            'author' => ['meta[property=og:novel:author]','content'], //作者
            'cate_name' =>  ['meta[property=og:novel:category]','content'],//分类名称
            'status'  =>  ['meta[property=og:novel:status]','content'],//连载状态
            'third_update_time' =>  ['meta[property=og:novel:update_time]','content'],//小说更新时间
            'story_link'  =>   ['meta[property=og:novel:read_url]','content'],//当前的URL
            'nearby_chapter'    =>['meta[property=og:novel:lastest_chapter_name]','content'], //最近的文章
            'intro' =>['meta[name=description]','content','',function($content){ //小说简介
                $content = filterHtml($content); //过滤字符
                return $content;
             }],
             'tag'   => ['meta[property=og:novel:category]','content'],//tag标签
             'location'  =>  ['.info-title','text'],//小说的面包屑位置
        ],
        /////////////////////////文章内容相关
         //采集文章内容的规则
        'content'   =>  [
            'content'   =>['#content','html'], //内容主体
            'meta_data'    =>['.bread-crumbs li:eq(3)','text'],//当前页面的对应的ID
            'href'      =>['meta[http-equiv="mobile-agent"]','content'], //页面链接
        ],
    ],
];
?>