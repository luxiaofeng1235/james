<?php
require_once dirname(__DIR__).'/library/init.inc.php';
$novel_table_name = Env::get('APICONFIG.TABLE_NOVEL');//小说详情页表信息
use QL\QueryList;##引入querylist的采集器


$res = MultiHttp::curlGet(['http://www.paoshu8.info/202_202498/192773978.html'],null,false);
$rules = [
    'title'    =>['.bookname h1','text'],
    'content'    =>['#content','html']
];
foreach($res as $value){
    $data = QueryList::html($value)->rules($rules)->query()->getData();
    $html = $data->all();
    $store_content = $html['content'] ?? '';
    if($store_content){
        $store_content = str_replace(array("\r\n","\r","\n"),"",$store_content);
        //把P标签去掉，直接换成换行符
        $store_content = str_replace("<p>",'',$store_content);
        $store_content = str_replace("</p>","\n\n",$store_content);
    }
    echo '<pre>';
    print_R($store_content);
    echo '</pre>';
    exit;
}

$content = webRequest('http://www.paoshu8.info/202_202498/192773978.html','GET',[]);

$c =<<<STR
<html>
<head><script>var V_PATH="/";window.onerror=function(){ return true; };</script>
<title> 第一卷五行 第七十章大杀四方_逆为仙_泡书吧</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="逆为仙, 第一卷五行 第七十章大杀四方" />
<meta name="description" content="泡书吧提供了辰东创作的玄幻小说《逆为仙》干净清爽无错字的文字章节： 第一卷五行 第七十章大杀四方在线阅读。" />
<meta name="mobile-agent" content="format=html5; url=http://m.paoshu8.info/wapbook-202498-192773978/" />
<link rel="stylesheet" type="text/css" href="/images/yuedu.css"/>
<script src="http://apps.bdimg.com/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
<script src="http://apps.bdimg.com/libs/jquery.cookie/1.4.1/jquery.cookie.min.js" type="text/javascript"></script>
<script type="text/javascript" src="/images/bqg.js"></script>
<script src="/1OsyVNKB/TF16MRZ2xp.js"></script>
<script type="text/javascript">uaredirect("//m.paoshu8.info/wapbook-202498-192773978/");</script>
<script type="text/javascript">var preview_page = "/202_202498/192767733.html";var next_page = "/202_202498/192792038.html";var index_page = "/202_202498/";var article_id = "202498";   var chapter_id = "192773978";   function jumpPage() {var event = document.all ? window.event : arguments[0];if (event.keyCode == 37) document.location = preview_page;if (event.keyCode == 39) document.location = next_page;if (event.keyCode == 13) document.location = index_page;}document.onkeydown=jumpPage;</script>
</head>
<body>
<div id="wrapper">
        <script>login();</script>
        <div class="header">
            <div class="header_logo">
                <a href="http://www.paoshu8.info">泡书吧</a>
            </div>
            <script>bqg_panel();</script>
        </div>
        <div class="nav">
            <ul>
                <li><a href="/">首页</a></li>
                <li><a rel="nofollow" href="/modules/article/bookcase.php">我的书架</a></li>
                <li><a href="/xuanhuanxiaoshuo/">玄幻小说</a></li>
                <li><a href="/xiuzhenxiaoshuo/">修真小说</a></li>
                <li><a href="/dushixiaoshuo/">都市小说</a></li>
                <li><a href="/chuanyuexiaoshuo/">穿越小说</a></li>
                <li><a href="/wangyouxiaoshuo/">网游小说</a></li>
                <li><a href="/kehuanxiaoshuo/">科幻小说</a></li>
                <li><a href="/paihangbang/">排行榜单</a></li>
                <li><a href="/xiaoshuodaquan/">全部小说</a></li>
            </ul>
        </div>
<div class="content_read">
                        <script>dingbu();</script>
                    <div class="box_con">
                <div class="con_top">
                    <script>textselect();</script>
                    <a href="http://www.paoshu8.info">泡书吧</a> &gt; <a href="/xiuzhenxiaoshuo/">修真小说</a> &gt; <a href="/202_202498/">逆为仙</a> &gt;  第一卷五行 第七十章大杀四方                </div>
<script>read2();</script>
                <div class="bookname">

                    <h1> 第一卷五行 第七十章大杀四方</h1>
                    <div class="bottem1">
                        <a href="javascript:vote(202498);" rel="nofollow">投推荐票</a> <a href="/202_202498/192767733.html">上一章</a> &larr; <a href="/202_202498/">章节目录</a> &rarr; <a href="/202_202498/192792038.html">下一章</a> <a href="javascript:addBookMark('202498','192773978','第一卷五行 第七十章大杀四方');" rel="nofollow">加入书签</a>
                    </div>

<script>gonggao();</script>
                <table style="width:100%; text-align:center;"><tr><td><script>read_1_1();</script></td><td><script>read_1_2();</script></td><td><script>read_1_3();</script></td></tr></table>
                                <div class="kongwei"></div>

                    <div style="widht:910px;margin:0px; auto;"><table id="adt1"><tr>
<td id="adt4"><script type="text/javascript">cad1();</script></td>
<td id="adt5"><script type="text/javascript">cad2();</script></td>
<td id="adt6"><script type="text/javascript">cad3();</script></td>
</tr></table></div>
                <div id="content"><p>　　一石激起千层浪，半街铁甲皆疯狂。</p><p>　　青竹寨步卒眼里的竹通天，不仅是他们的将军，更是未来靠山。</p><p>　　跟着竹通天躲进凤凰山十几年，吃苦受累，图啥。图得就是一朝得道鸡犬升天。</p><p>　　现在可好，眼看着富贵就要来了，却有人拆了通天路，让他们万劫不复。</p><p>　　这些步卒敢赌上一条命跟着竹通天，皆因竹通天是个赏罚分明有情有义之人，是他们的兄长。兄长若是不在了，那个老不死的院监就敢把功劳据为己有，把他们这些人全部扫进臭水沟里。</p><p>　　长兄如父，杀父者人恒杀之。一群铁甲红了眼，发疯般扑向蒙眼瞎。</p><p>　　崔器本要杀鸡儆猴，却适得其反。现如今也顾不得泼猴下面的那个家伙是生是死，只能甩开膀子硬钢了。</p><p>　　只见其腾身后翻，一脚踹在石球上吼道：“滚过去。”</p><p>　　泼猴很听话，脚到球滚，所过之处，筋断骨折，甲碎肉扁……</p><p>　　崔器紧随泼猴之后，但凡有漏网之鱼，便会顺势补刀。</p><p>　　全身铠，甲片相连之处有缝隙。崔器捅过刘队正的腰子，如法炮制起来倒也顺手的很。</p><p>　　一名重甲步卒倒下，便有更多步卒奋不顾身的补上。石球慢了，崔器突进的速度自然也慢了。此消彼长下，斩马刀的威力也就显现出来。</p><p>　　斩马刀握在重甲步卒手中，不止能斩马还能劈石。他们要劈开石头，杀掉藏在后面的那个人，为兄长报仇。</p><p>　　泼猴身上石甲不知挨了多少刀，此时看着百孔千疮，球也不像个球了。就在这时，铁甲中有一大块头飞出，使出一招从天而降的刀法。</p><p>　　刀光耀眼劈开雨幕，似乎将天地也分成两半。</p><p>　　在崔器看来，这一刀很强，即便用刀之人不过养气上境，却劈出了大无畏一刀。他崔器现在宗师境，若是实打实接这一刀，怕也很难全身而退。</p><p>　　刀劈了下来，劈在泼猴身上。石甲开，刀落肩头……</p><p>　　队正一刀劈开石球，顿时惹得步卒欢呼叫好，却又戛然而止。</p><p>　　一双双眼睛睁得大大的，透过头盔缝隙，看到石球内伸出一只毛茸茸的大手。那手顺着刀柄一把抓住队正脖子，下一刻猛地将队正拖进裂开的石球之中。</p><p>　　惨叫传出，血水溅射，那是极度血腥的画面……</p><p>　　“队正！”</p><p>　　“老子跟你拼了……”</p><p>　　一声声怒吼，一把把大刀，朝着裂开的石球劈去。</p><p>　　“轰！”</p><p>　　石球炸裂，铁甲横飞。</p><p>　　一只毛发如铁，高不下三丈的大猴子蹦了出来……</p><p>　　泼猴现身，可是把铁甲步卒吓到了。一个个面色发白不敢上前，却又不想退后。</p><p>　　崔器见状，踹了泼猴一脚：“他们当家的吃了老子的马，老子要连本带利一起收……”</p><p>　　下一刻，一人一猴同时扑向铁甲步卒，搅起腥风血雨……</p><p>　　宗师不无敌，即便窃天机修五行，面对人海也要被吞没。更何况铁甲步卒乃坤国精锐。</p><p>　　这是一场惨烈的，血性的，没有人性的厮杀。</p><p>　　崔器在白虎城六年，见过各国精锐换防驻守。却没见过眼前的重甲步卒，那只能说明一件事，坤国藏了私心，隐瞒了战力。</p><p>　　坤国想干嘛，不是崔器需要考虑的。可这些铁疙瘩却着实让他吃了苦头。</p><p>　　一人一猴皆是宗师，且身子骨也不比那些铁疙瘩单薄。可即便这样，面对最后一队铁疙瘩时，两位也不得不停下来喘口气。</p><p>　　崔器杵着刀回头瞥了一眼。</p><p>　　雨水中东倒西歪躺了一地的铁疙瘩，有捂腰的，有抱着断臂的，有上气不接下气的。</p><p>　　“娘的，硬骨头不好啃。好悬没崩掉牙。”</p><p>　　泼猴扭头看了一眼崔器，大口喘着气：“器哥，牙掉不要紧，眼睛可得蒙住了。不然会满寨皆敌的。”</p><p>　　崔器紧了紧遮掩布，狠声道：“已经人尽皆敌了，还在乎多几个匪类。打过铁疙瘩，后面的更难打。”</p><p>　　泼猴看了一眼拜月楼方向，打得惊天动地，不由往崔器身旁靠了靠：“器哥，一匹马而已，没必要死磕吧……”</p><p>　　朱厌，曾经的大妖。虽然现在一身修为十去七八，可只要活得久未来定然可期。他还不想短命，所以得劝一劝才行。</p><p>　　却见崔器瞪眼道：“一匹，那是三匹。再说了，别说是马，就算是一条狗，只要是老子的谁动老子剁谁。”</p><p>　　泼猴一看，这位是王八吃秤砣铁了心，劝是不顶用了，只能盼着玉山君早点把那个女人打死。也省得两个脱力的，到时还要冒险一战。</p><p>　　石街此时还能站着的，都是不要命的狠人。</p><p>　　狠人与狠人之间，矛盾也好，积怨也罢，只能在一方倒下后才能平息。所以喘口气的时间，对方也不会给。</p><p>　　崔器看着一队铁疙瘩摆好阵型压了过来，不由直起腰握紧刀，一句话也不说，甩开膀子就冲了过去。</p><p>　　狭路相逢勇者胜，那最后一队五十人铁甲，充分展示了什么是勇者，什么是人为财死鸟为食亡。</p><p>　　升官加薪英雄胆，不畏妖兽，不惧强权。</p><p>　　铁疙瘩有他们的执着和念想，崔器也有。</p><p>　　崔器的念想很简单，长命百岁，活得痛快不憋屈。如此简单的想法，对他来说却比登天还难。</p><p>　　入得凤凰山，看到的，听到的，没一件顺心。盗亦有道，他是一点也没见着。所以这杀人放火的心思，便如燎原野火一发不可收拾。</p><p>　　伸张正义，这事二先生来还说得过去，他崔器可不敢这么说。如今这么干，只是为了心中的不痛快，为了亲密无间的战友。</p><p>　　崔器的杀生刀很适合对付重甲，一对一的情况下，卸甲杀人不费吹灰之力。问题是没人跟他一对一，都是一窝蜂的上。</p><p>　　宗师也是人，一刀捅不死，多捅几刀也会死。</p><p>　　崔器一招夜战八方，刚刚荡开数把斩马刀，却又见数把刀劈头盖顶而来。宗师的气也是有数的，继续夜战八方，真气早晚耗光。</p><p>　　千钧一发之际，崔器还刀入鞘，双手一托一抓，便见铁砂化作一面盾牌，一根狼牙棒头上带刺那种。</p><p>　　盾挡刀，棒袭胸。</p><p>　　一记横扫过后，崔器双脚踩碎了青石板，铁疙瘩们也飞了出去。</p><p>　　钝物重击是对付重甲的办法之一，棒子下去无需破甲，仅凭力道也会让铠甲里的人受到重创。轻者头晕眼花一时半会而回不过神，重者内府受创，倒地不起。</p><p>　　打法变了，杀生刀不见了，崔器仅凭肉体力量蛮横地朝重甲步卒杀去。另一面，泼猴本就皮糙肉厚，完全就是伤敌一千自损八百的打法。不过效果很好，伤换伤一合倒一个。</p><p>　　暴雨依旧，拜月楼大战依旧，长街却消停了。</p><p>　　崔器跪在雨水里，双手颤抖着，青衫染尽血水。可他却笑着，笑得很开心。杀穿重甲，前方再无拦路之人。</p><p>　　大猴子坐在雨水里，靠在一处门板上，歪头看着那些畏畏缩缩，想围上来又不敢的无胆匪类，猛地呲牙咆哮一声。</p><p>　　一声嘶吼，吓得那些山贼，丢盔弃甲，跟头把式退后数丈远……</p><p>　　崔器没回头，他晓得那些家伙不敢上来，他们已经杀破胆了。</p><p>　　一人一妖数丈方圆无人敢近，只有冰冷的雨水落下。</p><p>　　崔器仰着头，看着半截拜月楼上，一花一白近身短打，速度快到连雨水也落不进，不由远山之眉相连。</p><p>　　速度力量非常人可及，怕是只有修行了跋折罗外功，才会有那等身手。而真正让崔器皱眉的原因，却非二人恐怖的爆发力，而是时不时便会使出的回旋身法。</p><p>　　弧形步，崔器也会。所以他可以肯定，回旋身法就是弧形步。那么问题来了，他们从哪里学来的弧形步。</p><p>　　离国国师公孙翦死后，天下间会弧形步的便只有他的老师。</p><p>　　崔器不知老师收了多少弟子，但他知道老师的弟子，没人敢把功夫外传的。</p><p>　　那是不是说凤彩衣和玉山君都是老师的弟子，如果都是，为何玉山君很不一样。</p><p>　　尸山，天下罪恶、最凶之人居所。而玉山君是尸山的山君，其凶残可想而知。</p><p>　　崔器第一次见玉山君，便看到了一言不合便要往死里打的狠辣，但却没有感受到来自玉山君的恶意。</p><p>　　这几日相处，他知道玉山君乃父辈好友，可这依旧说不通，玉山君让他感受到的那份亲情。毕竟他只是故人之后，不是玉山君的亲儿子。</p><p>　　都是师傅的弟子，见了要分生死。</p><p>　　见，不如不见。</p><p>　　如今一次见到两个，一个他会杀，一个师傅之命要杀，他却不想去杀。</p><p>　　崔器缓缓起身，扭头看了一眼泼猴，沉声道：“可还能战。”</p><p>　　泼猴摇头笑道：“不能战也得战。小命可还在器哥手里握着呢。”</p><p>　　“问你能不能，哪来的些许废话。”</p><p>　　崔器拔出刀大步朝拜月楼而去，泼猴翻身而起，紧紧跟了上去。</p><p>　　麒麟寨大当家够呛了，青竹寨大当家也出气多进气少了，现在就剩凤彩衣了。努努力，加把劲儿，凤凰山里吃马肉的，也就血债血还了。</p><p>　　狂风暴雨天，想那么多干啥，睁一眼闭一眼弄死一个再说。崔器是这么想的，可到了拜月楼下，却不进反退，差点没闪了腰……</p>        </div>

                                 <script>bdshare();</script>
                <div style="widht:910px;margin:0px; auto;"><table id="adt2"><tr>
<td id="adt4"><script type="text/javascript">cad4();</script></td>
<td id="adt5"><script type="text/javascript">cad5();</script></td>
<td id="adt6"><script type="text/javascript">cad6();</script></td>
</tr></table></div>
<script>read3();</script>
                <div class="bottem2">
                    <a href="javascript:;" onclick="vote(202498);" rel="nofollow">投推荐票</a> <a href="/202_202498/192767733.html">上一章</a> &larr; <a href="/202_202498/">章节目录</a> &rarr; <a href="/202_202498/192792038.html">下一章</a> <a href="javascript:addBookMark('202498','192773978','第一卷五行 第七十章大杀四方');" rel="nofollow">加入书签</a>
                <center><script>read4();</script></center>
                </div>

                <div id="hm_t_42055"></div>
            </div>
        </div>
        <div class="footer">
            <div class="footer_link">&nbsp;新书推荐：                <a href="/211_211487/" style='font-weight:bold'>诸天刀客加持我身</a>
                <a href="/211_211460/" >道上青天</a>
                <a href="/211_211458/" style='font-weight:bold'>轻侠挽倾</a>
                <a href="/211_211451/" >武侠：从偷取九阴真经开始</a>
                <a href="/211_211492/" style='font-weight:bold'>这次地球不一样，居然有祭道之上</a>
                <a href="/211_211552/" >觉醒后小师妹拿稳女主剧本</a>
                <a href="/211_211486/" style='font-weight:bold'>成神回归，呃！回归失败</a>
                <a href="/211_211566/" >大帝归来，我已无敌九万年</a>
                <a href="/211_211454/" style='font-weight:bold'>我在人间斩妖邪</a>
                <a href="/211_211439/" >武道长生，不死的我终将无敌</a>
                <a href="/211_211417/" style='font-weight:bold'>秦刀</a>
                <a href="/211_211518/" >我本红尘浪浪仙</a>
                </div>
            <div class="footer_cont">
                <script>footer();right();dl();</script>
                <div class="reader_mark1"><a href="javascript:;" onclick="addBookMark(article_id, chapter_id);"></a></div>
                <div class="reader_mark0"><a href="javascript:;" onclick="vote(article_id);"></a></div>
            </div>
        </div>
    </div>
<div style="text-align:center;"><p align=center><script>tj();</script></p></div></body>
</html>
STR;
    $rules = [
        'html'  =>  ['#content','text'],
    ];
    // echo '<pre>';
    // var_dump($html);
    // echo '</pre>';
    // exit;
    $data = QueryList::html($c)->rules($rules)->query()->getData();
    echo '<pre>';
    print_R($data);
    echo '</pre>';
    exit;

?>