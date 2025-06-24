<?php
return [
    //泡书吧的规则
    'paoshu8' => [
        //小说列表的轮训取值范围
        'range_update' => '#newscontent .l li',
        'range_ruku' => '#newscontent .r li', //入库下的列表
        //小说列表数据
        'update_list' => [
            'story_link' => [
                '.s2 a',
                'href',
                '',
                function ($content) {
                    //利用回调函数补全相对链接
                    $baseUrl = Env::get('APICONFIG.PAOSHU_HOST');
                    return $baseUrl . $content;
                }
            ], //链接地址
            'title' => ['.s2 a', 'text'], //标题
            'cate_name' => [
                '.s1',
                'text',
                '',
                function ($string) {
                    $t = preg_replace('/\[|\]/', '', $string);
                    return $t;
                }
            ], //分类
            'author' => ['.s4', 'text'], //作者名称
            //存储对应的story_id
            'story_id' => [
                '.s2 a',
                'href',
                '',
                function ($item) {
                    $str = '';
                    if ($item) {
                        $str = substr($item, 1, -1);
                    }
                    return $str;
                }
            ], //存一下对应的连接
            'nearby_chapter' => ['.s3', 'text'], //最新更新的章节
        ],

        'ipaoshuba_append' => [
            'keywords'=>['meta[name=keywords]','content'],
            'status' => ['.box_info tr:eq(2) td:eq(1)', 'text', '', function ($str) {
                $str = str_replace('小说状态：', '', $str);
                return $str;
            }],
            'third_update_time' => ['.box_info tr:eq(3) td:eq(2)', 'text', '', function ($timer) {
                $time_now = str_replace('更新时间：(', '', $timer);
                $time_now = str_replace(')', '', $time_now);
                return $time_now;
            }],
            'title' => ['.f21h', 'text', '', function ($title) {
                $newTitle  = preg_replace('/作者:.*/', '', $title);
                return $newTitle;
            }],
            //获取总字数
            'text_num'=>['.box_info tr:eq(3) td:eq(0)','text','-b',function($str){
                $textNum = 0;
                 if($str && strstr($str, '万字')){
                    $textNum = str_replace('万字', '',$str);
                    $textNum = $textNum.str_repeat(0, 4);
                    $textNum = (int) $textNum;
                 }
                 return $textNum;
            }],
        ],
        //最新入库的列表
        'ruku_list' => [
            'story_link' => [
                '.s2 a',
                'href',
                '',
                function ($content) { //链接地址
                    //利用回调函数补全相对链接
                    $baseUrl = Env::get('APICONFIG.PAOSHU_NEW_HOST');
                    return $baseUrl . $content;
                }
            ],
            'title' => ['.s2 a', 'text'], //标题
            'cate_name' => [
                '.s1',
                'text',
                '',
                function ($string) {
                    $t = preg_replace('/\[|\]/', '', $string);
                    $cate_name = $t . '小说';
                    return $cate_name;
                }
            ], //分类
            'author' => ['.s5', 'text'], //作者名称
            //存储对应的story_id
            'story_id' => [
                '.s2 a',
                'href',
                '',
                function ($item) {
                    preg_match('/\d+/', $item, $matches);
                    $story_id = $matches[0] ?? 0;
                    return $story_id;
                }
            ], //存一下对应的连接
            'nearby_chapter' => ['.s30', 'text'],

        ],
        #佳士小说首页规则
        'banjiashi_index' => [
            'cate_name' => ['.s1', 'text', '', function ($string) {
                $text =  preg_replace('/\[|\]/', '', $string);
                return $text;
            }],
            'story_link' => ['.s2 a', 'href', '', function ($url) {
                $index = 1; #默认拉取第二页
                $link =  Env::get('APICONFIG.BANJIASHI_URL') . $url;
                $link = str_replace("xiaoshuo", "index", $link);
                $newLink = $link . $index . '/';
                return $newLink;
            }],
            'title' => ['.s2 a', 'text'],
            'nearby_chapter' => ['.s3', 'text'],
            'author' => ['.s4', 'text'],
        ],
        'banjiashi_range' => '.list_l2 li:gt(0)',
        'douyin_range' => '#newscontent .l li', //抖音的首页列表范围
        'douyin_index' => [  //抖音列表采集规则
            'story_link' => [
                '.s2 a',
                'href',
                '',
                function ($content) {
                    //利用回调函数补全相对链接
                    $baseUrl = Env::get('APICONFIG.DOUYIN_URL');
                    return $baseUrl . $content;
                }
            ], //链接地址
            'title' => ['.s2 a', 'text'], //标题
            'cate_name' => [
                '.s1',
                'text',
                '',
                function ($string) {
                    $t = preg_replace('/\[|\]/', '', $string);
                    return $t;
                }
            ], //分类
            'author' => ['.s4', 'text'], //作者名称
            //存储对应的story_id
            'story_id' => [
                '.s2 a',
                'href',
                '',
                function ($item) {
                    $str = '';
                    if ($item) {
                        $str = substr($item, 1, -1);
                    }
                    return $str;
                }
            ], //存一下对应的连接
            'nearby_chapter' => ['.s3', 'text'], //最新更新的章节
        ],
        //详情页面的URL
        'detail_url' => [
            'path' => ['meta[property=og:novel:read_url]', 'content'], //小说的当前阅读链接
        ],
        'list_home' => [
            'story_link' => ['span:eq(1) a', 'href'],
            'title' => ['span:eq(1) a', 'text'],
        ],
        //章节列表数据
        'chapter_list' => [
            'link_url' => ['a', 'href'],
            'link_name' => ['a', 'text'],

        ],
        '27k' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
        ],
        'bqg24' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
        ],
        'siluke520' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
        ],
        'biquge34' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
        ],
        //小说详情-跑书吧的
        'default_info' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
            'title' => ['meta[property=og:title]', 'content'], //小说标题
            'author' => ['meta[property=og:novel:author]', 'content'], //小说作者
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类
            'status' => ['meta[property=og:novel:status]', 'content'], //小说的状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[property=og:novel:latest_chapter_name]', 'content'], //最近的文章
            'intro' => ['meta[property=og:description]', 'content'],
            'tag' => ['meta[property=og:novel:category]', 'content'],
            'location' => ['.con_top', 'text'], //小说的面包屑位置
        ],
        //笔趣阁24的请求
        'bqg24_info' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
            'author' => ['meta[property=og:novel:author]', 'content','',function($author_string){
                $author_string = str_replace('| 作者：','',$author_string);
                return $author_string;
            }], //小说作者
            'title' => ['meta[property=og:novel:book_name]', 'content'], //小说作者
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类
            'status' => ['meta[property=og:novel:status]', 'content'], //小说的状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[property=og:novel:latest_chapter_name]', 'content'], //最近的文章
            'intro' => ['meta[property=og:description]', 'content'], //介绍
            'tag' => ['meta[property=og:novel:category]', 'content'], //标记
            'location' => ['#maininfo .weizhi', 'text'], //小说的面包屑位置
        ],
        //处理27k的配置
        '27k_info'=>[
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
            'title' => ['meta[name=og:novel:book_name]', 'content'], //小说作者
            'author' => ['meta[name=og:novel:author]', 'content'], //小说作者
            'cate_name' => ['meta[name=og:novel:category]', 'content'], //分类
            'status' => ['.labelbox label:eq(2)', 'text'], //小说的状态
            'third_update_time' => ['meta[name=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[name=og:novel:latest_chapter_name]', 'content'], //最近的文章
            'intro' => ['meta[property=og:description]', 'content'], //介绍
            'tag' => ['meta[name=og:novel:category]', 'content'], //标记
            'location' => ['.bread', 'text'], //小说的面包屑位置
        ],
        //思路客
        'siluke520_info'=>[
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
            'title' => ['meta[property=og:novel:book_name]', 'content'], //小说作者
            'author' => ['meta[property=og:novel:author]', 'content'], //小说作者
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类
            'status' => ['meta[property=og:novel:status]', 'content'], //小说的状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[property=og:novel:latest_chapter_name]', 'content'], //最近的文章
            'intro' => ['meta[property=og:description]', 'content'], //介绍
            'tag' => ['meta[property=og:novel:category]', 'content'], //标记
            'location' => ['.nav-mbx', 'text','-div'], //小说的面包屑位置
        ],
        'siluke520_content'=>[ //获取思路客的正文
            'content'=>['#htmlContent','html','-a'],
        ],
        'bqg24_index' => [
            'story_link' => ['.sp_2 a', 'href'],
            'title' => ['.sp_2 a', 'text'],
            'author' => ['.sp_4', 'text'],
            'nearby_chapter' => ['.sp_3 a', 'text'],
        ],
        'bqg24_range' => '#mm_14 li:gt(0)',
        'bqg24_content' => [
            'content' => ['#htmlContent', 'html'],
        ],
        //小说详情替换网站的采集方案
        'info_replace' => [
            'title' => ['meta[property=og:novel:book_name]', 'content'], //小说标题
            'author' => ['meta[property=og:novel:author]', 'content'], //小说作者
            'cover_logo' => [
                'meta[property=og:image]',
                'content',
                '',
                function ($image) {
                    $cover_logo = '';
                    if (strpos($image, 'otcwuxi')) {
                        //判断是否存在https中但是图片用的http保存会有问题
                        if (!preg_match('/https/', $image)) {
                            $cover_logo = str_replace('http', 'https', $image);
                        }
                        $url = $cover_logo ?? $image;
                    } else {
                        //正常返回的数据信息
                        $url = $image;
                    }
                    return $url;
                }
            ], //小说封面
            // 'story_link'  =>   ['meta[property=og:novel:read_url]','content'],//当前的URL
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类
            'status' => ['meta[property=og:novel:status]', 'content'], //小说的状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[property=og:novel:latest_chapter_name]', 'content'], //最近的文章
            // 'intro' =>array('meta[property=og:description]','content'),
            'intro' => [
                '#intro',
                'text',
                '',
                function ($intro) {
                    // $content = array_iconv($intro);
                    return $intro;
                }
            ], //简介
            'tag' => ['meta[property=og:novel:category]', 'content'],
            // 'location'  =>  array('.path .p','text' ,'', function($location){
            //     $str = array_iconv($location);
            //     $str = str_replace('加入书架','',$str);//替换调这个没啥用
            //     return $str;
            // }),//小说的面包屑位置
            'location' => [
                '.con_top',
                'text',
                '',
                function ($content) {
                    // $arr = array_iconv($content);
                    return $content;
                }
            ], //面包屑
        ],
        //自传的基本信息采集
        'xuges_info' => [
            'title' => ['.tdw', 'text'], //小说标题
            'author'    =>  ['.tdw2', 'text'], //作者
            'cover_logo' => ['.fm', 'src'], //图片
            'intro' => ['.zhj', 'html'], //简介
            //分类默认给一个
            'cate_name' => [
                '.shijiewenxue',
                'text',
                '',
                function ($category) {
                    return $category ? $category : '(经典)世界名著';
                }
            ],
            //标签列表选择
            'tag' => [
                '.shijiewenxue',
                'text',
                '',
                function ($tagArr) {
                    return $tagArr ? $tagArr : '(文学)世界名著';
                }
            ],
            'third_update_time' => [
                '.latest_chapter_name',
                'text',
                '',
                function ($locale) {
                    if (empty($locale)) {
                        $third_update_time = date('Y-m-d 00:00:00', time());
                        $locale = $third_update_time;
                    }
                    return $locale;
                }
            ],
            //小说完本状态
            'status' => [
                '.book_status',
                'text',
                '',
                function ($status) {
                    if (empty($status)) {
                        $status = '已完结';
                    }
                    return $status;
                }
            ],
            'location'  =>  ['.tbw tr:eq(0)', 'text'],
            //最细更新
            'nearby_chapter'    => ['.updateNearyby', 'text', '', function ($item) {
                return '小说状态已完成更新,请阅读全本';
            }]
        ],
        'douyin_info' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
            'author' => ['meta[property=og:novel:author]', 'content'], //小说作者
            'title' => ['meta[property=og:novel:book_name]', 'content'], //小说作者
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类
            'status' => ['meta[property=og:novel:status]', 'content'], //小说的状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[property=og:novel:lastest_chapter_name]', 'content'], //最近的文章
            'intro' => ['meta[property=og:description]', 'content'], //介绍
            'tag' => ['meta[property=og:novel:category]', 'content'], //标记
            'location' => ['.con_top', 'text'], //小说的面包屑位置
            'chapter_pages' => ['#indexselect', 'html', '', function ($page_str) {
                if ($page_str) {
                    $occurrences = substr_count($page_str, '</option>'); //以/option计算你出现的次数
                    if ($occurrences) {
                        $allPage = range(0, $occurrences - 1);
                    } else {
                        $allPage = range(0, 1);
                    }
                    return $allPage;
                }
            }], //获取页码
        ],
        //读音正文内容
        'douyin_content' => [
            'content' => ['#content', 'html'],
        ],
        'ipaoshuba_content' => [
            'content' => ['#content', 'html', '-div'],
        ],
        //27k的正文
        '27k_content'=>[
            'content'=>['.txtnav','html','-div -script -h1 -p:first'],
        ],
        #就爱言情网
        '92yanqing_content'=>[
            'content'   =>  ['#booktxt','html'], #获取内容
            'chapter_pager'=>['.read h1','text','',function($pagestr){
                $res = explode('/', $pagestr);
                $num = 0;
                if ($res && isset($res[1])) {
                    preg_match('/\d+/', $res[1], $matches);
                    $num = $matches[0] ?? 0;
                }
                $number = intval($num);
                if ($number > 0) {
                    $every_num = range(0, $number - 1);
                } else {
                    $every_num = [0];
                }
                return $every_num;
            }],//分页目录
        ],
        'xiaoshubao_append'=>[
            'intro' => ['meta[name=description]', 'content'], //小说描述
        ],
        #获取标签规则
        'banjiashi_info' => [
            'title' => ['meta[property=og:title]', 'content'], //小说标题
            'intro' => ['meta[property=og:description]', 'content'], //小说描述
            'author' => ['meta[property=og:novel:author]', 'content'], #作者
            'cover_logo' => ['.img img', 'src', '', function ($url) {
                if ($url) {
                    $s_image = sprintf("%s%s", Env::get('APICONFIG.BANJIASHI_URL'), $url);
                    return $s_image;
                }
                // $data = explode('/',$url);
                // $s_image = '';
                // if($data){
                //     $s_image = sprintf("%s/%s/%s",Env::get('APICONFIG.BANJIASHI_URL'),'img',end($data));
                // }
                // return $s_image; #拼接网站url
            }], //小说封面
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类
            'status' => ['meta[property=og:novel:status]', 'content'], //状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[property=og:novel:latest_chapter_name]', 'content'], //最近的文章
            'tag' => ['meta[property=og:novel:category]', 'content'], #标签
            'location' => ['.info_dv1 .title', 'text'], //小说的面包屑位置
            'story_link' => ['meta[property=og:url]', 'content'], //当前的URL
            'chapter_pages' => ['.info_dv3 .title', 'html', '', function ($item) {
                preg_match("/共.*?\d页/", $item, $matches);
                $start = 1; //默认从1开始
                $allPage = 0;
                if ($matches) {
                    preg_match('/\d+/', $matches[0], $newData);
                    $allPage = $newData[0] ?? 0;
                }

                $per_pages = 0;
                //这里需要从2开始计算后面两页已经算过一页了，
                if ($allPage > 1) {
                    $t = range(0, $allPage - 1);
                    // array_shift($t);
                    $per_pages = array_values($t);
                } else {
                    $per_pages = [$start];  #这里说明有就从1开始。默认就不需要啦
                }
                sort($per_pages); #倒叙排列，主要那边是第一页是在最后一个。
                $per_pages = array_values($per_pages);
                return $per_pages;
            }], #获取章节总分页总数
        ],
        #新的书源配置
        'xbiqiku2_info' => [
            #基本详情
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
            'author' => ['meta[property=og:novel:author]', 'content'], //小说作者
            'title' => ['meta[property=og:title', 'content'], //小说标题
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类
            'status' => ['meta[property=og:novel:status]', 'content', '', function ($item) {
                if (empty($item)) {
                    #给一个默认值
                    $status = "连载中";
                } else {
                    $status = $item;
                }
                return $status;
            }], //小说的状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //最近的更新时间
            'nearby_chapter' => ['meta[property=og:novel:latest_chapter_name]', 'content'], //最近的文章
            'intro' => ['meta[property=og:description]', 'content'],
            'tag' => ['meta[property=og:novel:category]', 'content'],
            'location' => ['.con_top', 'text'], //小说的面包屑位置

        ],
        //世界名著的替换方式
        'world_info' => [
            'title' => ['.book-describe h1', 'text'],
            'author' => [
                '.book-describe p:eq(0)',
                'text',
                '',
                function ($item) {
                    $author = preg_replace("/作者：/", '', $item);
                    return $author;
                }
            ],
            'cover_logo' => ['.book-img img', 'src'],
            'location' => [
                '.bc-div',
                'text',
                '',
                function ($item) {
                    $location = trimBlankLine($item);
                    return $location;
                }
            ],
            //分类默认给一个
            'cate_name' => [
                '.shijiewenxue',
                'text',
                '',
                function ($category) {
                    return $category ? $category : '[经典]外国名著';
                }
            ],
            //标签列表选择
            'tag' => [
                '.shijiewenxue',
                'text',
                '',
                function ($tagArr) {
                    return $tagArr ? $tagArr : '(文学)世界名著';
                }
            ],
            //最近更新的章节取最后一个
            'nearby_chapter' => ['.book-list li:last a', 'text'],
            //如果当前没有时间就给他补一个时间
            'third_update_time' => [
                '.latest_chapter_name',
                'text',
                '',
                function ($locale) {
                    if (empty($locale)) {
                        $third_update_time = date('Y-m-d 00:00:00', time());
                        $locale = $third_update_time;
                    }
                    return $locale;
                }
            ],
            //简介
            'intro' => ['.describe-html', 'text', '-p:last'],
            //小说完本状态
            'status' => [
                '.book_status',
                'text',
                '',
                function ($status) {
                    if (empty($status)) {
                        $status = '已完结';
                    }
                    return $status;
                }
            ],
        ],
        //手机端的采集规则
        'mobile_content' => [
            'first_line' => ['.body>.text p:eq(0)', 'text'], //获取第一行
            'content' => ['.body>.text', 'html'], //正文
            'meta_data' => ['meta[property="og:url"]', 'content'], //meta标签
            // 'href'      =>['.navigator-nobutton a:eq(3)','href'],
        ],
        'default_content' => [
            'content' => ['#content', 'html', '-div'],
        ],
        //采集新的内容章节采集器
        'banjiashi_content' => [
            'chapter_pager' => ['.chapter-title', 'text', '', function ($con) {
                $res = explode('/', $con);
                $num = 0;
                if ($res && isset($res[1])) {
                    preg_match('/\d+/', $res[1], $matches);
                    $num = $matches[0] ?? 0;
                }
                $number = intval($num);
                if ($number > 0) {
                    $every_num = range(0, $number - 1);
                } else {
                    $every_num = [0];
                }
                return $every_num;
            }],
            'content' => ['.info_dv1', 'html', '-div -h2 -p:first -p:last'],
        ],
        #特殊魔板处理
        'banjiashi_content_new' => [
            'chapter_pager' => ['.chapter-title', 'text', '', function ($con) {
                return [0];
            }],
            'content' => ['.content', 'html', '-div -h2 -p:first -p:last'],
        ],
        //新的替换规则
        'content_replace' => [
            'content' => ['#content', 'html','-div'],
        ],
        //世界经典名著的抓取链接分析
        'world_content' => [
            'content' => ['#nr1>div', 'html', '-a'],
        ],
        'xuges_content' => [
            'content'   =>  ['.tb', 'html'], //处理传送的
            // 'content'   =>  ['.zhs', 'html'],
        ],
    ],
    //台湾站的小说采集规则
    'twking' => [
        /////////////////////////完本类型排行榜
        'page_range' => '.lastupdate .list-out', //分页循环的PC的列表范围
        //PC端分页列表
        'page_list' => [
            'title' => ['.w80 em:eq(0) a', 'text'],  //小说名
            'author' => [
                '.gray',
                'text',
                '',
                function ($item) { //小说作者处理
                    $author_reg = '/[0-9]{1,}-[0-9]{1,}/';
                    $str = preg_replace($author_reg, '', $item);
                    $str = trimBlankSpace($str); //去除首尾空格
                    return $str;
                }
            ],
            'story_link' => ['.w80 em:eq(0) a', 'href'], //小说链接
            'story_id' => [
                '.w80 em:eq(0) a',
                'href',
                '',
                function ($item) { //小说网站ID
                    $urlData = parse_url($item);
                    $story_id = str_replace('/', '', $urlData['path']);
                    return $story_id ?? '';
                }
            ], //存一下对应的
        ],
        ///////////////获取每个分页的页码
        'page_ret' => [
            'currentPage' => ['.articlepage em', 'text'], //获取分页
        ],
        ///////////////////////////小说详情页相关
        'chapter_range' => '.liebiao li', //章节循环的范围
        //章节列表数据
        'chapter_list' => [
            'link_url' => ['a', 'href'],
            'link_name' => ['a', 'text'],
        ],
        /////////////小说详情页的获取分类和连载状态
        //分类和连载的状态信息
        'detail_info' => [
            'cover_logo' => ['meta[property=og:image]', 'content'], //小说封面
            'title' => ['meta[property=og:novel:book_name]', 'content'], //小说标题
            'author' => ['meta[property=og:novel:author]', 'content'], //作者
            'cate_name' => ['meta[property=og:novel:category]', 'content'], //分类名称
            'status' => ['meta[property=og:novel:status]', 'content'], //连载状态
            'third_update_time' => ['meta[property=og:novel:update_time]', 'content'], //小说更新时间
            'story_link' => ['meta[property=og:novel:read_url]', 'content'], //当前的URL
            'nearby_chapter' => ['meta[property=og:novel:lastest_chapter_name]', 'content'], //最近的文章
            'intro' => [
                'meta[name=description]',
                'content',
                '',
                function ($content) { //小说简介
                    $content = filterHtml($content); //过滤字符
                    return $content;
                }
            ],
            'tag' => ['meta[property=og:novel:category]', 'content'], //tag标签
            'location' => ['.info-title', 'text'], //小说的面包屑位置
        ],
        /////////////////////////文章内容相关
        //采集文章内容的规则
        'content' => [
            'content' => ['#article', 'html', '-script -ins -p:last'], //内容主体解析,去除一些笔不要的元素，和一些过多的标签对
        ],
    ],

];
