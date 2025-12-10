<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年06月24日
// 作　者：卢晓峰
// E-mail :xiaofeng.200@163.com
// 文件名 :book_comment.php
// 创建时间:2024-06-24 17:35:14
// 编 码：UTF-8
// 摘 要:创建并同步书评信息
// ///////////////////////////////////////////////////
ini_set("memory_limit", "8000M");
set_time_limit(500);
require_once __DIR__ . '/library/init.inc.php';

use QL\QueryList;

$download_path = Env::get("SAVE_BOOK_COMMENT");
if (!is_dir($download_path)) {
    createFolders($download_path);
}
$table_book_info = 'mc_book_comment_info'; #书评基础信息
$table_book_detail = 'mc_book_comment_detail'; #书评详情
$db_conn = 'db_master';

$bid = isset($argv[1]) ? $argv[1] : "";
$where = "";
if ($bid) {
    $where  = "book_id = {$bid}";
}
$sql = "select * from {$table_book_info}";
if ($where) {
    $sql .= " where " . $where;
}
$list = $mysql_obj->fetchAll($sql, $db_conn);
if (!$list) {
    echo "no data\r\n";
    exit();
}
foreach ($list as $key => $val) {
    $book_url = trim($val['book_url']);
    if ($book_url) {
        synComments($book_url); #同步数据
        echo "index=" . ($key + 1) . " \t book_id ={$val['book_id']}\turl={$book_url}\ttitle={$val['title']}\t author={$val['author']} \t deal complate\r\n";
    } else {
        "index=" . ($key + 1) . " \t no data\r\n";
    }
}

echo "处理平路能完成，请查看数据库\r\n";

/**
 * @note 同步评论
 *
 * @param  $bid int 评论ID
 * @return  array
 */

function synComments($link_url)
{
    if (!$link_url) {
        return false;
    }

    $token = Env::get('COMMENT_TOKEN');
    $data = QueryList::get($link_url);
    #请求的基础地址
    // $base_url = Env::get('COMMENT_WEB_URL');
    // echo $base_url . 'book/'.$bid;die;
    $html_datas = StoreModel::swooleRquest([$link_url], "post");
    dd($html_datas);
    $tdata = array_values($html_datas);
    $rules = [
        "title" => ['.book-info h1', 'text'],
        "author"    => [
            '.book-author',
            'text',
            '',
            function ($item) {
                $author = '';
                if (!empty($item)) {
                    $author = str_replace("作者：", "", $item);
                }
                return $author;
            }
        ],
        "book_id" => ['.result div:eq(0)', 'attr(bookid)'],
        "cover_logo" => [".book-info-wrap div:eq(0)", 'attr(cover)'],
        'tags'  => ['.public', 'html'],
        'neary_time' => ['.el-tooltip', 'text', '', function ($item) {
            $string = str_replace('更新时间：', '', $item);
            return $string;
        }],
    ];
    #获取相关数据
    $html = $tdata[0];
    $data = QueryList::html($html)
        ->rules($rules)
        ->query()
        ->getData();
    $bookInfo = $data->all();
    // dd($bookInfo);
    #同步数据开始
    $res = combineComments($bookInfo);
}


#同步评论
function combineComments($bookInfo = [])
{
    if (!$bookInfo) {
        return false;
    }
    global $mysql_obj, $table_book_detail, $table_book_info, $db_conn;
    ##获取配置信息并广联查询
    $book_res = getBookComments($bookInfo);
    if ($book_res) {
        $book_id = $book_res['book_id'];
        delete_comments($book_id);
        $comment_list = $book_res['comments'] ?? [];
        unset($book_res['comments']);
        $where = "book_id ='" . $book_id . "'";
        $book_res['utime'] = time();
        ##更新具体的基础信息
        if (isset($book_res['cover_logo']) && empty($book_res['cover_logo'])) {
            unset($book_res['cover_logo']); //防止图片覆盖为空
        }
        $mysql_obj->update_data($book_res, $where, $table_book_info, false, 0, $db_conn);
        echo "插入评论数据......\r\n";
        #同步插入数据开始执行
        $mysql_obj->add_data($comment_list, $table_book_detail, $db_conn); #同步评论数据
    }
    echo "over\r\n";
}



/**
 * @note  删除评论
 *
 * @param  $info array 书籍信息
 * @param $page int 页码
 * @return  array
 */
function delete_comments($book_id)
{
    if (!$book_id) {
        return false;
    }
    global $table_book_detail, $mysql_obj, $db_conn;
    $sql = "delete from {$table_book_detail} where book_id = " . intval($book_id);
    $ret = $mysql_obj->query($sql, $db_conn);
}





/**
 * @note  获取标签分类信息
 *
 * @param  $string strting 标签
 * @return  array
 */
function getTagCategory($string)
{
    if (empty($string)) {
        return false;
    }
    $text_reg = '/<a.*?.*?>(.*?)<\/a>/ims';
    preg_match_all($text_reg, $string, $link_text); //匹配文本;
    $category = '';
    if (!empty($link_text) && isset($link_text[1])) {
        $category = implode('-', $link_text[1]);
    }
    return $category;
}

function escape_emoji($text)
{
    // 使用正则表达式匹配Emoji字符
    $regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

    // 使用preg_replace_callback函数进行转义
    $callback = function ($match) {
        // 返回转义后的字符串，这里可以自定义转义规则，例如替换为HTML实体或其他字符
        return '[EMOJI]';
    };

    // 执行替换操作
    return preg_replace_callback($regex, $callback, $text);
}

/**
 * @note  获取书籍评论列表
 *
 * @param  $info array 书籍信息
 * @param $page int 页码
 * @return  array
 */

function getBookComments($info = [], $page = 1)
{


    if (!$info)
        return false;
    echo "book_id = {$info['book_id']} \t title={$info['title']} \tauthor={$info['author']} \t 开始拉取评论数据\r\n";
    $category = getTagCategory($info['tags']);
    #图片的请求格式
    #https://image.lkong.com/avatar/756653/928bb94a93f235be1f4be0d358fd227e
    $book_id = intval($info['book_id']);
    if ($book_id) {
        $time = time();
        $api_url = sprintf("%s%s/comment?type=latest&page=%d&t=%s", Env::get('COMMENT_API_URL'), $book_id, $page, $time);
        echo "comments api url :{$api_url}\r\n";
        $book_id = intval($info['book_id']);
        #获取网站的token方便进行抓取
        $token = Env::get('COMMENT_TOKEN');
        $headers[] = "cookie: {$token}";
        $comments = webRequest($api_url, 'GET', [], $headers);
        $comments_list = json_decode($comments, true);
        $comments_data = [];
        #获取评论接口
        if ($comments_list['success'] == 1) {
            $total = $comments_list["data"]["total"] ?? 0;
            if (!$total) { //如果没有具体的分页信息直接返回
                return $info;
            }
            $data = $comments_list["data"]['comments'] ?? [];
            // echo '<pre>';
            // var_dump($data);
            // echo '</pre>';
            // exit;
            $info['comment_count'] = $total;
            foreach ($data as $value) {
                if (!$data) {
                    continue;
                }
                $createrInfo = $value['createrId'] ?? []; #创建者的信息
                $avtar_url  = '';
                //处理获取用户的头像信息
                if ($createrInfo && $createrInfo['avatarId']) {
                    //拼装头像信息
                    $avtar_url = sprintf("%s%s/%s", Env::get('COMMENT_IMG_URL'), $createrInfo['_id'], $createrInfo['avatarId']);
                } else {
                    $avtar_url = 'https://s2.ax1x.com/2019/10/14/KSoO3T.png'; //给一个默认的
                }

                //处理时间
                $datetime = strtotime($value['createdAt']);
                $create_time = date('Y-m-d H:i', $datetime);
                $commentInfo['book_id'] = $book_id;
                $commentInfo['user_id'] = $createrInfo['_id'] ?? 0; #用户ID
                $commentInfo['avtar_id']  = $createrInfo['avatarId'] ?? ''; //头像关联的ID
                $commentInfo['username'] = $createrInfo["userName"]; #用户头像信息
                $commentInfo['avtar_url'] = $avtar_url; #头像
                $commentInfo['content'] =  escape_emoji($value['content']); #发布内容
                $commentInfo['score'] = $value['score']; //评论得分
                $commentInfo['syn_update_time'] = $create_time; //创建时间
                $commentInfo['addtime'] = time();
                $comments_data[] = $commentInfo;
            }
        }
        $info['book_id'] = intval($info['book_id']);
        $info['comments'] = $comments_data ?? [];
        if (isset($info['tags'])) {
            unset($info['tags']);
        }
        $info['category'] = $category;
        return $info;
    }
}
