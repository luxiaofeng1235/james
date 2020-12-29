function sendPM(){
	var content = $(".PF_SX_tc_content_txt02").val();
        //alert(content)
        var strLen = '';
        strLen = jmz.GetLength(content);
        if(strLen>140)
           {
               //exit();
               $("#pm_error").html('私信内容超过了140个字。');
               return false;
           }
       //alert(aaa)
	var get_user_id = $("#get_user_id").val();
        //alert(get_user_id);
        //alert("取到了么")
	if (content=='' || get_user_id==''){
		$("#pm_error").html('错误，请检查发送人或私信内容！');
		setTimeout("$('#pm_error').html('');",4000);
		return false;
	}else{
                //alert(get_user_id)
		$.post("./ajax_2.php",{s:'sendPM',get_user_id:get_user_id,content:content},function(data){
			if (data=='1'){
				//$(".PF_SX_tc_content_txt01").val('好友昵称,多个用户用分号间隔');
				//$(".PF_SX_tc_content_txt01").css("color","#999");
				//$(".PF_SX_tc_content_txt02").val('说明：长度不能超过140字');
				//$(".PF_SX_tc_content_txt02").css("color","#999");
                                $(".PF_SX_tc_content_txt02").val('');
				$(".PF_SX_tanchu").hide();
				//window.location.reload();
			}else{
				$("#pm_error").html('发送失败，请重试！');
				setTimeout("$('#pm_error').html('');",3000);
			}
		});
	}

}
// 回复
function reply(id,nick){
    $(".PF_SX_tanchu").show();
    $(".PF_SX_tc_content_txt01").val(nick);
    $("#get_user_id").val(id);
    //("readonly":"readonly");
    $(".PF_SX_tc_content_txt01").attr({"readonly":"readonly"});
    $('.PF_SX_tc_content_txt02').focus();


}

// 回复结束
function changeNotice(id){
	$("#notice_c"+id).slideToggle(500);
	$("#showNotice_"+id).toggle();
	$("#hideNotice_"+id).toggle();
	$("#newNotice_"+id).hide();
	$("#noticeBg_"+id).toggleClass("PF_PC_inform01_conbg");
	$.post("./ajax_2.php",{s:'noticeOld',id:id});
}
function cancelFollow(id){
	$.post("./ajax_2.php",{s:'cancelFollow',id:id},function(data){
            //alert(data)
		if (data){
                    //alert(data)
			//alert('取消关注成功！');
			$('#user_'+id).css({ "height": "0px", "border": "0px" });

		}else{
			alert('提交失败，请再次尝试！');
		}
	});
}
function followUser(id){
	$.post("./ajax_2.php",{s:'followUser',id:id},function(data){
            //alert(data);
		if (data==1){
                    //alert(data)
			alert('关注成功！');
                        window.location.reload();
		}else if(data==2){
			alert('您已关注过，谢谢！');
		}else{
			alert('提交失败，请再次尝试！');
		}
	});
}
function likePet(id){
	$.post("./ajax_2.php",{s:'likePet',id:id},function(data){
           // alert(data);
		if (data==1){
			alert('提交成功！');
                         window.location.reload();
		}else if(data==0){
			alert('提交失败！');
		}else{
                    alert('您已喜欢过,谢谢');
                }
	});
}

function saveUserInfo(){
	var button = $('#submit'), interval, err = $("#tip");
	showdiv('tip');
	interval = window.setInterval(function(){
		var text = err.text();
		if (text.length < 13){
			err.text(text + '.');
		} else {
			err.text('保存中..');
		}
	}, 200);
	var nick = $('#nick').val();
	var name = $('#name').val();
	var province = $('#province').val();
	var city = $('#city').val();

	var gender=$('input:radio[name="gender"]:checked').val();
	var birthyear = $('#birthyear').val();
	var birthmonth = $('#birthmonth').val();
	var birthday = $('#birthday').val();
	var qq = $('#qq').val();

	$.post("./ajax_2.php",{s:'add_info',nick:nick,name:name,province:province,city:city,gender:gender,birthyear:birthyear,birthmonth:birthmonth,birthday:birthday,qq:qq},function(data){
		if (data){
                    //alert(data);
			window.clearInterval(interval);
			err.text('保存成功！');
		}else{
			window.clearInterval(interval);
			err.text('保存失败，请再次点击保存。');
		}
	});
	setTimeout("hidediv('tip');",1500);
}

function showbirthday(){
	var el = $("#birthday");
	var birthday = el.val();
	el.empty();
	el.append('<option value="">选择日</option>');
	for(var i=0;i<28;i++){
		j = i+1;
		if (birthday==j){
			el.append('<option value="'+j+'" selected="selected">'+j+'</option>');
		}else{
			el.append('<option value="'+j+'">'+j+'</option>');
		}
	}
	if($('#birthmonth').val()!="2"){
		if (birthday=='29'){
			el.append('<option value="29" selected="selected">29</option>');
		}else{
			el.append('<option value="29">29</option>');
		}
		if (birthday=='30'){
			el.append('<option value="30" selected="selected">30</option>');
		}else{
			el.append('<option value="30">30</option>');
		}
		switch($('#birthmonth').val()){
			case "1":
			case "3":
			case "5":
			case "7":
			case "8":
			case "10":
			case "12":{
				if (birthday=='31'){
					el.append('<option value="31" selected="selected">31</option>');
				}else{
					el.append('<option value="31">31</option>');
				}
			}
		}
	}else if($('#birthyear').val()!="") {
		var nbirthyear=$('#birthyear').val();
		if(nbirthyear%400==0 || (nbirthyear%4==0 && nbirthyear%100!=0)) el.append('<option value="29">29</option>');
	}
	el.val() = birthday;
}

//图片按比例缩放
var flag=false;
function DrawImage(ImgD,iwidth,iheight){
	//参数(图片,允许的宽度,允许的高度)
	var image=new Image();
	image.src=ImgD.src;
	if(image.width>0 && image.height>0){
	flag=true;
	if(image.width/image.height>= iwidth/iheight){
		if(image.width>iwidth){
		ImgD.width=iwidth;
		ImgD.height=(image.height*iwidth)/image.width;
		}else{
		ImgD.width=image.width;
		ImgD.height=image.height;
		}
		ImgD.alt=image.width+"×"+image.height;
		}
	else{
		if(image.height>iheight){
		ImgD.height=iheight;
		ImgD.width=(image.width*iheight)/image.height;
		}else{
		ImgD.width=image.width;
		ImgD.height=image.height;
		}
		ImgD.alt=image.width+"×"+image.height;
		}
	}
}

//js获取字符串长度
//demo alert(jmz.GetLength('测试测试ceshiceshi));
var jmz = {};
jmz.GetLength = function(str) {
    ///<summary>获得字符串实际长度，中文2，英文1</summary>
    ///<param name="str">要获得长度的字符串</param>
    var realLength = 0, len = str.length, charCode = -1;
    for (var i = 0; i < len; i++) {
        charCode = str.charCodeAt(i);
        if (charCode >= 0 && charCode <= 128) realLength += 1;
        else realLength += 2;
    }
    return realLength;
};
