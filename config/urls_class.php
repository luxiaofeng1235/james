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
                $baseUrl = Env::get('APICONFIG.PAOSHU_HOST');
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
                $res = substr($item ,1,-1);
                return $res;
            }], //存一下对应的连接
            'nearby_chapter'    =>['.s3','text'],//最新更新的章节
        ],
        //最新入库的列表
        'ruku_list' =>[
            'story_link'       => ['.s2 a','href','',function($content){//链接地址
                  //利用回调函数补全相对链接
                $baseUrl = Env::get('APICONFIG.PAOSHU_HOST');
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
                $res = substr($item ,1,-1);
                return $res;
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
            'cover_logo'       =>array('#fmimg img','src'),//小说封面
            'author'    => array('meta[property=og:novel:author]','content'),//小说作者
            'title'     =>array('#info>h1','text'),//小说标题
            'cate_name' =>array('meta[property=og:novel:category]','content'),//分类
            'status'    =>array('meta[property=og:novel:status]','content'),//小说的状态
            'third_update_time'    =>array('meta[property=og:novel:update_time]','content'), //最近的更新时间
            'nearby_chapter'    =>array('meta[property=og:novel:latest_chapter_name]','content'), //最近的文章
            'intro' =>array('meta[property=og:description]','content'),
            'tag'   => array('meta[property=og:novel:category]','content'),
            'location'  =>  array('.con_top','text'),//小说的面包屑位置
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
            'content'    =>['#content','html'],
            'meta_data'       =>['meta[name=mobile-agent]','content'],
            'href'      =>['.con_top a:eq(2)','href'],
        ],
    ],
    //台湾站的小说采集规则
    'xsw'   =>[
        /////////////////////////完本类型排行榜
        'page_range'    =>  '#alistbox',//分页循环的PC的列表范围
        'page_range_mobile' =>'.bookbox',//分页循环的移动段列表范围
        //移动端分页列表
        'page_list_mobile' =>[
            'title' =>  ['.iTit a','text'],//小说标题
            'author'    =>  ['.author','text'], //作者
            'cover_logo' => ['.bookimg img','src','',function($img_url){ //图片信息
                if(!preg_match('/https\:\/\//',$img_url)){
                    //自动补齐封面
                    $img_url = Env::get('TWCONFIG.API_HOST_URL') . $img_url;
                 }
                return $img_url;
            }],
            'nearby_chapter'   =>['.update a','text'], //最近的章节状态
            'intro' =>['.intro_line','text'],   //小说简介
            'detail_link'  =>  ['.iTit a','href','',function($url){ //小说的详情页链接
                return Env::get('TWCONFIG.API_HOST_URL').$url;
            }],
            'story_link'  =>['.iTit a','href', '',function($item){//小说的章节链接
                $url = preg_replace('/\.html/','/',$item);
                $url = Env::get('TWCONFIG.API_HOST_URL') . $url;
                return $url;
            }],
            'story_id'  => ['.iTit a','href', '',function($results){ //存一下story_方便到时候去存取
                 preg_match('/\d+/',$results, $matches);
                 $story_id = 0;
                 if(isset($matches[0])){
                    $story_id = $matches[0];
                 }
                 return $story_id;
            }],
        ],
        //PC端分页列表
        'page_list' =>[
            'title' =>['.title a','text'],  //小说名
            'author'    =>  ['.title span','text'], //作者
             'cover_logo' =>['.pic img','src','',function($img_url){//图片信息
                 if(!preg_match('/https\:\/\//',$img_url)){
                    //自动补齐封面
                    $img_url = Env::get('TWCONFIG.API_HOST_URL') . $img_url;
                 }
                return $img_url;
            }],
            'nearby_chapter'   =>['.sys a','text'],//最近的章节状态
            'intro' =>['.intro','text'],  //小说简介
            'detail_link'  =>  ['.pic a','href','',function($url){//详情页链接
                // $url =
                return Env::get('TWCONFIG.API_HOST_URL').$url;
            }],
            'story_link'  =>['.title a','href', '',function($item){//章节链接
                $url = preg_replace('/\.html/','/',$item);
                $url = Env::get('TWCONFIG.API_HOST_URL') . $url;
                return $url;
            }],
            'story_id'  => ['.title a','href', '',function($results){//存一下story_方便到时候去存取
                 preg_match('/\d+/',$results, $matches);
                 $story_id = 0;
                 if(isset($matches[0])){
                    $story_id = $matches[0];
                 }
                 return $story_id;
            }],
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
            'cate_name' =>  ['.box_info tr:eq(4) td:eq(0)','text'], //小说分类
            'status' =>  ['.box_info tr:eq(4) td:eq(2)','text'], //小说分类
            'third_update_time'   =>  ['.box_info tr:eq(5) td:eq(3)','text'],//小说的更新时间
            'story_link'    =>  ['.bread-crumbs li:eq(2)','html','',function($item){
                $link_reg = '/<a.+?href=\"(.+?)\".*>/i'; //匹配A链接
                preg_match($link_reg , $item , $matches);
                $source_url = $matches[1] ?? 0;
                $story_id = str_replace('/book/','',$source_url);
                $story_id = str_replace('/','',$story_id);
                return $story_id;

            }],//获取面包屑里的连接信息
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