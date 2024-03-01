<?php
return [
    //泡书吧的规则
    'paoshu8'    =>  [
        //小说列表的轮训取值范围
        'range' =>  '.novellist',
        //小说列表数据
        'list'  =>  [
                'story_link'       => ['a','href'],
                'title'     =>  ['a','text'],
        ],
        //小说详情
        'info'  =>[
            'cover_logo'       =>array('#fmimg img','src'),//小说封面
            'author'    => array('#info p:eq(0)','text'),//小说作者
            'title'     =>array('#info>h1','text'),//小说标题
            'status'    =>array('meta[property=og:novel:status]','content'),//小说的状态
            'third_update_time'    =>array('#info p:eq(2)','text'), //最近的更新时间
            'nearby_chapter'    =>array('meta[property=og:novel:latest_chapter_name]','content'), //最近的文章
            'intro' =>array('meta[property=og:description]','content'),
            'tag'   => array('meta[property=og:novel:category]','content'),
            'location'  =>  array('.con_top','text'),//小说的面包屑位置
        ],
        //采集文章的内容配置
        'content'   => [
            'content'    =>['#content','html'],
            'meta_data'       =>['meta[name=mobile-agent]','content'],
            'href'      =>['.con_top a:eq(2)','href'],
        ],
    ],
    //报的网站的基本配置
    'baode' =>  [
        'range' =>  '#newscontent li',
        'list'  => [
            'cate_name'       => ['.s1','text'],
            'title'     =>  ['.s2 a','text'],
            'story_link'     =>  ['.s2 a','href'],
        ],
        'info'  =>  [
            'cover_logo'       =>array('#fmimg img','src'),//小说封面
            'author'    => array('#info p:eq(0)','text'),//小说作者
            'title'     =>array('#info>h1','text'),//小说标题
            'status'    =>array('meta[property=og:novel:status]','content'),//小说的状态
            'third_update_time'    =>array('#info p:eq(2)','text'), //最近的更新时间
            'nearby_chapter'    =>array('meta[property=og:novel:latest_chapter_name]','content'), //最近的文章
            'intro' =>array('meta[property=og:description]','content'),
            'tag'   => array('meta[property=og:novel:category]','content'),
            'location'  =>  array('.con_top','html'),//小说的面包屑位置
        ],
    ],
];
?>