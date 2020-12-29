function showdiv(id){
	$("#"+id).show();
}
function hidediv(id){
	$("#"+id).hide();
}
function showConDiv(id){
	$("#tg_info").removeClass('downsele');
	$("#tg_desc").removeClass('downsele');
	$("#tg_like").removeClass('downsele');
	$("#tg_pet").removeClass('downsele');
	if (id=='g_info'){
		$("#g_info").show();
		$("#g_desc").hide();
		$("#g_like").hide();
		$("#g_pet").hide();
		$("#tg_info").addClass('downsele');
		$("#info_ti").html('标准');
	}else if (id=='g_desc'){
		$("#g_desc").show();
		$("#g_info").hide();
		$("#g_like").hide();
		$("#g_pet").hide();
		$("#tg_desc").addClass('downsele');
		$("#info_ti").html('品种介绍');
	}else if (id=='g_like'){
		$("#g_like").show();
		$("#g_info").hide();
		$("#g_desc").hide();
		$("#g_pet").hide();
		$("#tg_like").addClass('downsele');
		$("#info_ti").html('喜欢Ta的人');
	}else if (id=='g_pet'){
		$("#g_pet").show();
		$("#g_like").hide();
		$("#g_info").hide();
		$("#g_desc").hide();
		$("#tg_pet").addClass('downsele');
		$("#info_ti").html('登记的宠物');
	}
}

function watting(){
	 var watting = document.getElementById('watting');
	 watting.style.display="";
}

function wattingnone(){
	 var watting = document.getElementById('watting');
	 watting.style.display="none";
}

var childWindow;

function login(){
	childWindow = window.open("./ajax.php?s=qq_login","TencentLogin","width=450,height=320,menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");
}

function goNext(id){
	$("#step_1").hide();
	$("#step_2").hide();
	$("#step_3").hide();
	$("#step_"+id).show();

	$("#step_ti_1").addClass("Q_reg_li");
	$("#step_ti_1").removeClass("Q_reg_sel");
	$("#step_ti_2").addClass("Q_reg_li");
	$("#step_ti_2").removeClass("Q_reg_sel");
	$("#step_ti_3").addClass("Q_reg_li");
	$("#step_ti_3").removeClass("Q_reg_sel1");
	if (id=='1'){
		$("#step_ti_1").addClass("Q_reg_sel");
		$("#step_ti_1").removeClass("Q_reg_li");
	}else if(id=='2'){
		//检查填入项
		var name = $("#name").val();
		var sort = $("#tt0").contents().find("#gs1").val();
		var province = $("#tt1").contents().find("#s1").val();
		if (name==''){
			$("#step_1").show();
			$("#step_2").hide();
			alert('请填写宠物昵称！');
		}else if (sort==' '){
			$("#step_1").show();
			$("#step_2").hide();
			alert('请选择宠物品种！');
		}else if (province==' '){
			$("#step_1").show();
			$("#step_2").hide();
			alert('请选择宠物地址！');
		}else{
			$("#step_ti_2").addClass("Q_reg_sel");
			$("#step_ti_2").removeClass("Q_reg_li");
		}
	}else if(id=='3'){
           // alert(111)
		 name = $("#name").val();
                var goods_id    = $("#tt0").contents().find("#gs2").val();
                var pets_id     = $("#pets_id").val();
                //var goods_name  = $("#tt0").contents().find("#gs2").val();
		 province       = $("#tt1").contents().find("#s1").val();
		var city        = $("#tt1").contents().find("#s2").val();
		var pic         = $("#tt").contents().find("#pic").val();
		var sex='',kind='';
               // sort='';
		for (i=0;i<6;i++){
			if ($("#sex"+i).attr("checked")){
				sex = $("#sex"+i).val();
			}
			if ($("#kind"+i).attr("checked")){
				kind = $("#kind"+i).val();
			}
		}
		$.post("./ajax.php",{s:'add_pet',name:name,province:province,city:city,pic:pic,sex:sex,kind:kind,goods_id:goods_id,pets_id:pets_id},function(data){
			if (data){
                           // alert(data);
				$("#step_ti_3").addClass("Q_reg_sel1");
				$("#step_ti_3").removeClass("Q_reg_li");
				//closeDJ();
                                //alert('修改成功')
				window.location.reload();
				//window.location.href="./goods.php?id="+goods_id;
			}
		});
	}
}

function closeDJ(){
	setTimeout('TINY.box.hide();',100);
}

function upload(){
	$("#upload_div").hide();
	$("#sub_div").show();
}

function showupload(){
	$("#upload_div").show();
	$("#sub_div").hide();
}
function like(id){
	$.post("./ajax.php",{s:'add_like',id:id},function(data){
		if (data=='1'){	//成功
			alert('提交成功！');
			window.location.href="./goods.php?id="+id;
		}else if (data == '2'){	//已经提交过
			alert('您已经提交过！');
		}else{	//其他情况
			alert('提交失败，可能是网络原因或者你还没登录Q+平台，请重试！');
		}
	});
}

function openJS(){}
function closeJS(){}

function goUrl(url){
	window.location.href=url;
}

//commend
function addStep(id){
	var front = $("#selectStep").html();
	var num = $("#num"+id).html();
	var stepNum = $("#stepNum").html();
	var s = front+num+'|';
	var num2 = 0;
	var title = new Array(
			"特殊用途：如导盲犬","孩子喜欢，陪伴孩子","看家护院","替自己陪伴老人","和自己做伴，伴侣宠物","丁宠一族，替代孩子",
			"高原气候青藏及云贵高原","东北内蒙等冬季寒冷地区","炎热潮湿台湾南方沿海","北方气候温和地区","新疆甘肃等干燥少雨地区","南方湿润气候，华南西南",
			"家里只有一间房归我支配","一室户小户型","实用的中等户型","大户型或复式","别墅带花园","平房大杂院",
			"住六层以上有电梯","住六层以上无电梯","住六层以下有电梯","住六层以下无电梯","洋房别墅","平房或一层不爬楼",
			"出租房，经常换租客","有年幼孩子的家庭","朝九晚五上班族","喜欢安静的老人","刁蛮的老妇人","也饲养宠物的家庭",
			"早出晚归两点一线","喜欢散步，情调","平常宅、周末疯","朋友多，应酬多","不着家","能不出门就不出门的纯宅",
			"没洁癖家里乱得像狗窝","不算有洁癖家里定期收拾","一般般可以忍受小乱","有点小洁癖，过敏性鼻炎","有洁癖，每天必须打扫","强迫症必须保持一尘不染",
			"劳碌命，没有闲的时候","思想懒但身体不懒","有点小懒但该干的还是干","比较懒不到万不得已不干","很懒，能不折腾就不折腾","懒死算饭来张口衣来伸手",
			"规规矩矩的office风格","不修边幅随便乱穿","休闲运动范","凸显个性潮人范","西服笔挺正装范","追求品味的时尚范",
			"威武高大毛发浓密的狗狗","体型彪悍容易打理的狗狗","体型适中性格开朗的狗狗","个子不大活泼好动的狗狗","娇小可爱惹人怜爱的狗狗","个子小但性格强的狗狗"
		);
	var text = new Array(
			"你为什么要养狗？","你目前居住地的气候属于？","你家的房子有多大？","你目前住所的楼层情况？","你左邻右舍有以下哪种人？",
			"你的生活更偏向于以下哪种？","你觉得你有洁癖吗？","你觉得你是懒人吗？","你觉得你的打扮更接近哪种？","你喜欢的狗狗类型？"
		);
	if (stepNum==9){
		window.location.href="./commend_end.php?s="+s;
		return false;
	}else{
		$("#stepNum").html(stepNum*1+1);
	}
	$("#selectStep").html(s);
	$("#stepLoad").attr('class','RecPet_titstep RecPet_titstep_'+$("#stepNum").html());
	for (i=1; i<7; i++){
		num2 = $("#num"+i).html()*1+6;
		$("#num"+i).html(num2);
		$("#img"+i).attr('src', 'images/commend/photo/'+num2+'.jpg');
		$("#title"+i).html(title[num2]);
	}
	$("#goback").show();
	$("#mid_lef_word").attr('class','mid_lef_word_'+$("#stepNum").html());
	$("#mid_lef_word2").html(text[$("#stepNum").html()]);
}

$(document).ready(function(){
	$("#testNum").load("./commend_ajax.php" , {s : "num"});
	$("#img1").mouseover(function(e){$("#title1").addClass("mid1_rig_dl_dd");});
	$("#img1").mouseout(function(e){$("#title1").removeClass("mid1_rig_dl_dd");});
	$("#img2").mouseover(function(e){$("#title2").addClass("mid1_rig_dl_dd");});
	$("#img2").mouseout(function(e){$("#title2").removeClass("mid1_rig_dl_dd");});
	$("#img3").mouseover(function(e){$("#title3").addClass("mid1_rig_dl_dd");});
	$("#img3").mouseout(function(e){$("#title3").removeClass("mid1_rig_dl_dd");});
	$("#img4").mouseover(function(e){$("#title4").addClass("mid1_rig_dl_dd");});
	$("#img4").mouseout(function(e){$("#title4").removeClass("mid1_rig_dl_dd");});
	$("#img5").mouseover(function(e){$("#title5").addClass("mid1_rig_dl_dd");});
	$("#img5").mouseout(function(e){$("#title5").removeClass("mid1_rig_dl_dd");});
	$("#img6").mouseover(function(e){$("#title6").addClass("mid1_rig_dl_dd");});
	$("#img6").mouseout(function(e){$("#title6").removeClass("mid1_rig_dl_dd");});
});
//commend_end


//点击通知 显示更多
var i_more='0';
function more(id){
        i_more=parseInt(i_more)+parseInt(id);
    	$.post("./ajax_2.php",{s:'more',id:i_more},function(data){
            //alert(data)
            var  jdata = eval("("+data+")");
                      //alert(jdata.length)
                      //$.each(jdata,function(){
                      $(jdata).each(function(){
                          //每次追加一条
                          $('.PF_PC_inform').append("<div class='PF_PC_inform01'><div class='PF_PC_inform01_con' id='noticeBg_{$notice[red].id}'><div class='PF_PC_inform_title' ><div class='PF_PC_inform_checkbox'><input name='chkkindid[]' type='checkbox' id='chkkindid[]' value="+this.id+" /></div><div class='PF_PC_inform_news_tit'><p>"+this.title+"&nbsp;<span class='PF_PC_inform_newicon' id='newNotice_"+this.id+"'><img src='images/Q+PC_icon2.gif' alt='new' /></span></p></div><div class='PF_PC_inform_icon' id='showNotice_"+this.id+"'></div><div class='PF_PC_inform_icon' style='display:;' id='hideNotice_"+this.id+"'></div><div class='PF_PC_inform_dada'>{"+this.dateline+".dateline|date_format:'%m月%d日&nbsp;%H:%M'}</div></div><div class='PF_PC_inform_news' id='notice_c"+this.id+"'><p>"+this.content+"</p></div></div></div>");
                        //alert(this.id);

                      });

		if (data==1){
			//alert('！');
                         window.location.reload();
		}else{
			//alert('');
		}
	});

}
//点击用户news_pm 更多
function more_pm(id){
i_more=parseInt(i_more)+parseInt(id);
    	$.post("./ajax_2.php",{s:'more_pm',id:i_more},function(data){
            //alert(data)
            var  jdata = eval("("+data+")");
                      $(jdata).each(function(){
                          //每次追加一条
                    var content = "<div class='PF_PC_con_left_con_news01'><div class='PF_PC_con_left_con_news01_conbg' onmouseover='this.className='PF_PC_con_left_con_news01_conbg';' onmouseout='this.className='PF_PC_con_left_con_news01_con';'><div class='PF_PC_con_left_con_news01_img'><a href='./user.php?s=user&id="+this.user_id+"'><img src='"+this.avatar3+"'  /></a></div><div class='PF_PC_con_left_con_news01_rig'><div class='PF_PC_con_left_con_news01_word01'><div class='PF_PC_con_left_con_news01_word01_lef'><a href='./user.php?s=user&id="+this.user_id+"'>"+this.nick+"</a><span>："+this.content+"</span></div><div class='PF_PC_con_left_con_news01_word01_icon'><a href='#'><img src='images/Q+PC_icon6.gif' alt='删除' /></a></div></div><div class='PF_PC_con_left_con_news01_word02'><span class='PF_PC_con_left_con_news01_data'> "+this.dateline+"</span><span class='PF_PC_con_left_con_news01_word02_rigtxt'><a href='./user.php?s=news_pm_one&suid="+this.small_user_id+"&buid="+this.big_user_id+"'>共有"+this.num+"条对话</a>&nbsp;|&nbsp;<a href='javascript:reply('"+this.user_id+"','"+this.nick+"')' >回复</a></span></div></div></div></div>";
                    //alert(content);
                          $('.PF_PC_con_left_con_news').append(content);
                        //alert(this.id);

                      });

		if (data==1){
			//alert('！');
                         window.location.reload();
		}else{
			//alert('');
		}
	});
}
//用户点击单个用户的 news_pm_one 更多
function more_pm_one(id,user_id,other_id){
    //alert(user_id);
        i_more=parseInt(i_more)+parseInt(id);
    	$.post("./ajax_2.php",{s:'more_pm_one',id:i_more,other_id:other_id},function(data){
                   alert(data)
                   if(data==0){}   //返回无数据 不进行响应
                   else            // 有数据就判断性追加数据
                       {
                              var  jdata = eval("("+data+")");
                      $(jdata).each(function(){
                          //每次追加一条
                          var content = '';
                          var content_1 = "<div class='PF_PC_con_left_con_news_con01'><div class='PF_PC_con_left_con_news_con01_img'><a href='./user.php?s=user&id="+this.user_id+"'><img src='"+this.avatar3+"' alt=' ' /></a></div><div class='PF_PC_con_left_con_news_con01_dialog'><div class='PF_PC_con_left_dia01_mid'><div class='PF_PC_con_left_dia_mid_top'></div><div class='PF_PC_con_left_dia_mid_con'><p class='PF_PC_con_left_dia_mid_con_news'><a href='#'>"+this.nick+"~</a>："+this.content+"<span class='PF_PC_txtcolor03'>"+this.dateline+"</span></p><p class='PF_PC_con_left_dia_mid_con_del'><a href='./user.php/?s=news_pm_one&del_id="+this.user_id+"'>删除</a></p></div><div class='PF_PC_con_left_dia_mid_foot'></div></div><div class='PF_PC_con_left_dia_left'></div></div></div>";
                          //this.user_id <> user_id
                          var content_2 = "<div class='PF_PC_con_left_con_news_con02'><div class='PF_PC_con_left_con_news_con02_img'><a href='./user.php?s=user&id="+this.user_id+"'><img src='"+this.avatar3+"' alt=' ' /></a></div><div class='PF_PC_con_left_con_news_con02_dialog'><div class='PF_PC_con_left_dia02_mid'><div class='PF_PC_con_left_dia_mid_top'></div><div class='PF_PC_con_left_dia_mid_con'><p class='PF_PC_con_left_dia_mid_con_news'>发送给<a href='#'>"+this.nick+"~</a>："+this.content+"<span class='PF_PC_txtcolor03'>"+this.dateline+"</span></p><p class='PF_PC_con_left_dia_mid_con_del'><a href='./user.php/?s=news_pm_one&del_id="+this.user_id+"'>删除</a></p></div><div class='PF_PC_con_left_dia_mid_foot'></div></div><div class='PF_PC_con_left_dia_right'></div></div></div>";
                          if(this.user_id == user_id){
                              content = content_2;
                          }else{
                              content = content_1;
                          }
                          $('.PF_PC_con_left_con_news_con').append(content);
                        //alert(this.id);

                      });

		if (data==1){
			//alert('！');
                         window.location.reload();
		}else{
			//alert('');
		}
                       }

	});

}
function del_pm_one(id)
{
    $.post("ajax_2.php",{s:'del_pm_one',id:id},function(data){
			if (data=='1'){
                            // $("#pm_one"+id+"").remove();
                            $("#pm_one"+id).remove();
			}
		});
	}
function search_submit()
{
    content = $("#nick").value();
    alert(content)

}

