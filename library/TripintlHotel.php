<?php
/////////////////////////////////////////////////////
// Copyright(c) 2018,父母邦，帮父母
// 日   期：2018-12-19
// 作　者：卢晓峰
// 编   码：UTF-8
// 摘   要:主要用于处理知行酒店接口对接
/////////////////////////////////////////////////////

// class CI_TripintlHotel{

// 	 //接口地址
//     protected $interfaceUrl = "";//正式的正式地址

//     protected $ClientID = ''; //正式的clientID

//     protected $LicenseKey = ''; //正式的LIcenseKey

//     protected $callback_info = null; //请求的参数


        
 
//     /**
//      * 构造函数
//      */

//     public function __construct(){
//     	 //实例化CI
//         $this->ci=& get_instance();
//     	$this->ci->load->library("Xml2Array", '', 'xml2array');
//     	if(in_array($_SERVER['RUN_ENV'], array('local','test'))){
//             $this->ClientID = 'FMBTest_Key'; //cliengID的标识
//             $this->LicenseKey = 'FMBTest'; //LicenseKey的标识
//             $this->interfaceUrl = 'http://www.fumubang.tripintl.vip/API/Hotel/';
            
//         } else {
//             $this->ClientID = 'FMB_18810587950';//clientID上线前补齐
//             $this->LicenseKey = 'FMB_18810587950';//LicenseKey上线前补齐
//             $this->interfaceUrl = 'http://www.fumubang.intltrip.com/API/Hotel/'; //请求url上线前补齐
//         }

//         //默认配送的header信息头
// 		$this->callback_info= array('Header' =>array('ClientID'=>$this->ClientID,'LicenseKey'=>$this->LicenseKey));
// 		//默认的返回状态处理
// 		$this->default_msg = array('Status'=>-1,'Error'=>'参数缺失','Result'=>array());
//     }

//     /**@:获取公共参数：header
//      * @return array
//      */
//     public function get_callback_info(){
//         return $this->callback_info;
//     }

//     /**@:获取接口key对应的接口url数组集合
//      * @usage  array(
//      *          'hotel_image' =>'http://www.fumubang.tripintl.vip/API/Hotel/hotelimage.html',
//      *          ........
//      *         )
//      * @return array
//      */
//     public function get_interfaceUrl (){
//         $config_hotel=$this->ci->config->item('api_zhixing_domain');
//         $new_conf=array();
//         foreach($config_hotel as $key=>$val){
//              $new_conf[$key]=$this->interfaceUrl.$val;
//         }
//         return $new_conf;
//     }

//     /**
// 	* @note 获取知行接口路由地址<映射表>
// 	*
// 	* @param $item arr 单个配置节点
// 	* @return array|str
// 	*/
// 	public function get_access_router($item= ""){
// 		$this->router_list= array(
// 			'Hotel_Search' => 'search.html', //酒店搜索
// 			'Hotel_Image' => 'hotelimage.html', //酒店图片
// 			'Hotel_Ratings' => 'ratings.html', //酒店评分（支持列表）
// 			'Hotel_Info'	=> 'info.html', //酒店基础信息
// 			'Hotel_Room_Image'	=> 'roomimage.html',//房型图片
// 			'Hotel_Description' => 'description.html', //酒店基本描述
// 			'Hotel_Facilities'	=> 'facilities.html', //酒店设施
// 			'Hotel_Area_Attractions'	=>'areaattractions.html', //酒店周边设备
// 			'Hotel_Rating'	=> 'rating.html', //酒店评分获取单个
// 			'Hotel_Price'	=>'hotelprice.html', //酒店价格
// 			'Hotel_Price_Confirm' =>'priceconfirm.html',//价格确认
// 			'Hotel_Create_Order' => 'createorder.html', //创建订单接口
// 			'Hotel_Query_Order' =>'queryorder.html', //订单查询接口
// 			'Hotel_Pre_Cancel' =>'precancel.html',//酒店预取消接口
// 			'Hotel_Cancel'	=>'cancel.html',//酒店取消接口
// 			'Hotel_Room_Type' =>'RoomType.html',//酒店房型英文名称
// 		);
// 		if($item){
// 			if($item && isset($this->router_list[$item])){
// 				return $this->router_list[$item];
// 			}else{
// 				return '';
// 			}
// 		}
// 		return $this->router_list;
// 	}


// 	 /**
// 	 * @note 获取接口的内容信息 
// 	 *
// 	 * @param $url str 接口的请求地址
// 	 * @param $params post参数 
// 	 * @param $method str 处理方式 get:http的get请求 post:通过post提交
// 	 * @return  str|unknow
// 	 */
	      
// 	 public function getApiData($url, $params = array(), $method = "post"){
//         $result = '';
//         if(!is_array($params)){
//             return $result;
//         }elseif(!empty($params)){
//             $posts =json_encode($params);
//         }
//         $posts = urldecode($posts);
//         $ch = curl_init();
//          //https 请求
//         if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
//             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//         }

//         /*------------记录知行酒店请求日志 start------------*/
//         $request_time=microtime(true);
      
//  		/*------------记录知行酒店请求日志 end------------*/
//         @$timeout = intval($timeout)<=0 ? 60 : intval($timeout);

//         //发送头部信息
//         curl_setopt($ch, CURLOPT_URL, $url);
//         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
//         curl_setopt($ch, CURLOPT_FAILONERROR, false);
//         curl_setopt($ch, CURLOPT_TIMEOUT, 60);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0');
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","SOAPAction: \"/soap/action/query\"", "Content-length: ".strlen($posts)));
//         if(isset($posts)){
//         	curl_setopt($ch, CURLOPT_POSTFIELDS, $posts);
//         }
 		
//          $content = array();
//          $returnMsg   = array(
//             'content'   => curl_exec($ch),
//             'errno'     => curl_errno($ch),
//             'error'     => curl_error($ch),
//             'header'    => curl_getinfo($ch),   
//          );
//          $errMsg = '';
//          $Status='';
//          curl_close($ch); 


//         /*------------记录知行酒店请求日志 start------------*/
//         $api_access_log = "/home/www/fmb.photo/newphplog/logs/tripintl/";
//         if ( $api_access_log ){
//              if ( !file_exists($api_access_log) ){
//                  mkdir($api_access_log);
//              }
//              $response_time=microtime(true);
//              $spend_time = $response_time - $request_time;
//              $api_access_file = 'tripintl_request_'.date('Y-m-d').'.log';
//              file_put_contents ($api_access_log.$api_access_file,"[Request]:".$url." ".date('Y-m-d H:i:s')." "."request_time:".$request_time." "."post:".var_export($posts,true)." "."[Response]:".$_SERVER['REMOTE_ADDR']." ".date('Y-m-d H:i:s')." "."response_time:".$response_time." "."spend_time:".$spend_time." ".$_SERVER['REQUEST_URI']." ".'http_code:'.$returnMsg['header']['http_code']."\n",FILE_APPEND|LOCK_EX );
//          }
//  		/*------------记录知行酒店请求日志 end------------*/

//          if($returnMsg['errno']==0){
//          	$content = $returnMsg['content'];
//          	//判断当前的返回是否是合法的json
//          	$is_json = self::is_json($content);
//          	if(!$is_json){//判断是否为合理的json数据
//          		$content=array(
// 	         		'Status'=>500,
// 	         		'Error'=>'接口返回数据错误',
// 	         		'Result'=>$returnMsg,
//          		);
//          	}else{
//          		$content = json_decode($content,true);  
//          	}
//          }else{
//          	$content=array(
//          		'Status'=>-1,
//          		'Error'=>'请求接口错误',
//          		'Result'=>$returnMsg,
//          	);
//          }
//          return $content;
//     }

//     /**
//     * @note 判断解析当前的字符串是否为合法的json格式
//     *
//     * @param $string str 输入的json字符
//     * @return bool
//     */
//    	protected function is_json($string) {
// 		if (is_string($string)) {
//             @json_decode($string);
//             return (json_last_error() === JSON_ERROR_NONE);
//         }
//         return false;
// 	}



//     /**
//     * @note 获取curl接口返回的数据
//     *
//     * @param $prefix str 前缀名称
//     * @param $data arr 请求的参数信息
//     * @return 返回对应的接口信息
//     */
         
//     public function get_depoly_contents($prefix='',$data){
// 		$result= array();
// 		if(!$prefix || !$data)
// 			return $result;
// 		$url = $this->interfaceUrl.$prefix;
// 		//请求外部接口
//  		$result = $this->getApiData($url,$data);
//  		return $result;
// 	}


// 	/**
// 	 * 查询分享表某条数据
// 	 * @param int $hotel_id 酒店id否
// 	 * @return array
//  	 */
// 	public function getHotelInfo($hotel_id =''){
// 		$hotel_id  = intval($hotel_id);
// 		$hotelinfo = array();
// 		if(!$hotel_id) {
// 			return $this->default_msg;
// 		}
// 		//取当前的接口调用路由
// 		$access_router = $this->get_access_router('Hotel_Info');
// 		//拼装数据
// 		$this->callback_info['HotelID'] = $hotel_id;
// 		$hotelinfo = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $hotelinfo;
// 	}

// 	/**
// 	* @note 获取表名称
// 	*
// 	* @param $tablname 集合表名称
// 	* @return array|string
// 	*/
	     
// 	public function getMongoColName($tablename =''){
// 		$list = array(
// 			'table_info'=>'hotel_info',//酒店详情集合
// 			'table_search'=>'hotel_search_list' //搜索酒店hotelID集合
// 		);
// 		if($tablename && isset($list[$tablename])){
// 			return $list[$tablename];
// 		}
// 		return $list;
// 	}

// 	/**
// 	* @note 获取酒店图片信息
// 	*
// 	* @param int $hotel_id 酒店ID
// 	* @return array
// 	*/
// 	public function getHotelImage($hotel_id=''){
// 		$hotel_id = $hotel_id ? $hotel_id : '';
// 		$hotel_image = array();
// 		if(!$hotel_id){
// 			return $this->default_msg;
// 		}
// 		$hotel_ids= array();
// 		if(!is_array($hotel_id)){
// 			$hotel_ids = array($hotel_id);
// 		}else{
// 			$hotel_ids = $hotel_id;
// 		}
// 		//获取当前的访问接口
// 		$access_router = $this->get_access_router('Hotel_Image');
// 		 //拼装列表数据
// 		$this->callback_info['HotelIDList'] = $hotel_ids;
// 		$hotel_image = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $hotel_image;
// 	}

// 	/**
// 	* @note 获取酒店搜索列表
// 	*
// 	* @param $hotel_id int 酒店信息
// 	* @param $page int 当前分页
// 	* @param $ShowAround bool 是否显示周边数据
// 	* @param $page int 分页
// 	* @param $page_size 最大分页数
// 	* 搜索接收的参数
// 		'City'  => 城市id,
// 		'CheckInDate'   =>入住时间,
// 		'CheckOutDate'  =>离店时间,
// 		 'Keyword' =>搜索关键字,
// 		 'RoomCount'=>房间数,
// 		 'AdultCount'=>成人数,
// 		 'ChildCount'=>儿童数,
// 		 'ChildAgeList'  =>array() //儿童年龄段,
// 		 'Star' =>星级,
// 		 'MinPrice'  =>最低价格,
// 		 'MaxPrice' =>最高价格,
// 		 'Rating' =>评分,
// 		 'Brand' =>品牌,
		
// 	* 排序需要处理的参数
// 	* array('Key'=>'Price','Sort'=>1); key为排序依据：  [Price：按照价格排序 ; Star：星级排序 ; Star：星级排序] Sort:1：正序 0:倒叙
// 	* @return array
// 	*/
// 	public function getHotelItem($where_data=array(),$order_by= array(),$ShowAround=true,$page= 1 , $page_size= 20){
// 		$page_size = $page_size ? intval($page_size) : 20;
// 		$page = $page ? intval($page) : 1;
// 		$Filter = $Search = $params=  array();
// 		if(!$where_data || !is_array($where_data)){
// 			return $this->default_msg;
// 		}
//  		// $where_data = array_filter($where_data);
// 		if($where_data){
// 			$tkey = array_keys($where_data);


// 			foreach($tkey as $val){
// 				if(!$val) continue;
// 				//处理搜索条件，只在指定的字段内输出
// 				if(in_array($val, array(
// 					'City','CheckInDate','CheckOutDate',
// 					'Keyword','RoomCount','AdultCount',
// 					'ChildCount','ChildAgeList',
// 				))){
// 					$Search[$val] = $where_data[$val];
// 				}
// 				//过滤条件处理
// 				if(in_array($val, array(
// 					'Star','MinPrice','MaxPrice',
// 					'Rating','Brand'))){
// 						$Filter[$val] = $where_data[$val];
// 				}
// 			}
// 		}
 		
 	
// 		$Search['ShowAround'] = $ShowAround;//周边展示
// 		//组合header条件
// 		$params =array_merge($this->callback_info,array());
// 		$params['Search'] = $Search;//搜索条件
// 		$params['Filter'] = $Filter;//过滤条件
// 		//排序依据
// 		$params['Order'] = $order_by&& is_array($order_by) ? $order_by : array(); //特殊处理下
		
// 		if($page && $page_size){//分页处理
// 			$params['Page']  =array('PageIndex'=>$page,'PageCount'=>$page_size);
// 		}
// 		// var_dump($params);
// 		//处理输入的条件，由于接口中有空需要保留下，需要特殊处理下 
// 		$params = self::compare_input_data($params);
// 		$access_router = $this->get_access_router('Hotel_Search'); //取酒店的接口路由
// 		//请求接口
//  		$hotel_list = $this->get_depoly_contents($access_router,$params);
//  		return $hotel_list;
//  	}


// 	/**
// 	* @note 处理搜索输入的参数数据
// 	*
// 	* @param $where_data array 接口参数处理
// 	* @return array
// 	*/
// 	private function compare_input_data($where_data){
// 		if(!$where_data || !is_array($where_data)){
// 			return array();
// 		}
// 		foreach($where_data as $key =>$val){
// 			if(!$val || empty($val)){//处理接口中的异常数据用object去请求
// 				$where_data[$key] = new \ArrayObject();
// 			}
// 		}
// 		return $where_data;
// 	}


// 	/**
// 	* @note 获取酒店的详情信息
// 	*
// 	* @param $hotel_id int 酒店信息
// 	* @return array
// 	*/
	     
// 	public function getHotelRoomImage($hotel_id =''){
// 		$hotel_id  = intval($hotel_id);
// 		$room_image = array();
// 		if(!$hotel_id){
// 			return $this->default_msg;
// 		}
 		
// 		//取当前的接口调用路由
// 		$access_router = $this->get_access_router('Hotel_Room_Image');
//  		//拼装数据
// 		$this->callback_info['HotelID'] = $hotel_id;

// 		$room_image = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $room_image;
// 	}


// 	/**
// 	* @note 获取酒店的评分
// 	*
// 	* @param $hotel_id int 酒店信息
// 	* @return array
// 	*/
	     
// 	public function getHotelRating($hotel_id =''){
// 		$hotel_id = $hotel_id ? $hotel_id : '';
// 		$ratinginfo = array();
//  		if(!$hotel_id){
//  			return $this->default_msg;
//  		}
//  		$hotel_ids = array();
//  		if(is_array($hotel_id)){//多条评分接口
//  			$hotel_ids = $hotel_id;
//   			$access_router = $this->get_access_router('Hotel_Ratings');
//  		}else{//单条评分接口
//  			//取当前的接口调用路由
// 			$access_router = $this->get_access_router('Hotel_Rating');
// 			$hotel_ids = $hotel_id;
//   		}
//  		//拼装数据根据传的值的类型来计算
// 		!is_array($hotel_ids) ? $this->callback_info['HotelID'] = $hotel_id : $this->callback_info['HotelIDList']  = $hotel_ids;
// 		$ratinginfo = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $ratinginfo;
// 	}

// 	/**
// 	* @note 获取酒店的描述
// 	*
// 	* @param $hotel_id int 酒店id
// 	* @return array
// 	*/
// 	public function getHotelDescription($hotel_id=""){
// 		$hotel_id  = intval($hotel_id);
// 		$info = array();
// 		if(!$hotel_id){
// 			return $this->default_msg;
// 		}

// 		$access_router = $this->get_access_router('Hotel_Description');
// 		//拼装数据
// 		$this->callback_info['HotelID'] = $hotel_id;
// 		$info = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $info;

// 	}

// 	/**
// 	* @note 获取酒店的设施
// 	*
// 	* @param $hotel_id int 酒店id
// 	* @return array
// 	*/

// 	public function getHotelFacilities($hotel_id = ''){
// 		$hotel_id = intval($hotel_id);
// 		$data = array();
// 		if(!$hotel_id){
// 			return $this->default_msg;
// 		}
// 		//获取当前接口的路径
// 		$access_router = $this->get_access_router('Hotel_Facilities');
 
// 		$this->callback_info['HotelID'] = $hotel_id;
 		
//  		$data = $this->get_depoly_contents($access_router,$this->callback_info);
//  		return $data;
// 	}

// 	/**
// 	* @note 获取酒店的周边设备
// 	*
// 	* @param $hotel_id int 酒店id
// 	* @return array
// 	*/
// 	public function getHotelAreaAttractions($hotel_id = ''){
// 		$hotel_id = intval($hotel_id);
// 		$data = array();
// 		if(!$hotel_id){
// 			return $this->default_msg;
// 		}
// 		//获取当前接口的路由信息
// 		$access_router = self::get_access_router('Hotel_Area_Attractions');
// 		$this->callback_info['HotelID']  = $hotel_id;
// 		$area_attractions = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $area_attractions;
// 	}

// 	/**
// 	* @note 知行订单查询
// 	*
// 	* @param $booking_id string 预定id
// 	* @return array
// 	*/
// 	public function orderQueryOrder($booking_id=''){
// 		$booking_id = trim($booking_id);
// 		if(!$booking_id){
// 			return $this->default_msg;
// 		}
// 		$access_router = self::get_access_router('Hotel_Query_Order');
// 		$this->callback_info['BookingID']  = $booking_id;
// 		// echo json_encode($this->callback_info);die;
// 		$orders = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $orders;
// 	}


// 	/**
// 	* @note 订单预取消
// 	*
// 	* @param $booking_id string 预定id
// 	* @return array
// 	*/
// 	public  function cancelPreOrder($booking_id=''){
// 		$booking_id = trim($booking_id);
// 		if(!$booking_id){
// 			return $this->default_msg;
// 		}
// 		$access_router = self::get_access_router('Hotel_Pre_Cancel');
// 		$this->callback_info['BookingID']  = $booking_id;
// 		$result = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $result;
// 	}

// 	/**
// 	* @note 订单发起取消接口
// 	*
// 	* @param $booking_id string 预定id 
// 	* @param $confirmID string  确认号ID <由预定订单创建生成>
// 	* @return array
// 	*/
// 	public function cancelOrder($booking_id='',$confirmID=''){
// 		$booking_id = trim($booking_id);
// 		$confirmID = trim($confirmID);
// 		if(!$booking_id || !$confirmID){
// 			return $this->default_msg;
// 		}
// 		$access_router = self::get_access_router('Hotel_Cancel');
// 		$this->callback_info['BookingID'] = $booking_id;
// 		$this->callback_info['ConfirmID'] = $confirmID;
// 		//请求发起接口
// 		$result = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $result;
// 	}



// 	/**
// 	 * 掉价格确认接口
// 	 * @Author zhixin
// 	 * @Date   2018-12-20
// 	 * @param  array      $details 请求对方接口携带的数组参数
// 				Array
// 				(
// 				    [CheckOutDate] => 2018-03-10
// 				    [CheckInDate] => 2018-03-07
// 				    [NumOfRooms] => 1
// 				    [HotelID] => 94160
// 				    [GuestDetails] => Array
// 				        (
// 				            [0] => Array
// 				                (
// 				                    [ChildCount] => 0
// 				                    [AdultCount] => 2
// 				                    [RoomNum] => 1
// 				                    [ChildAgeList] => Array
// 				                        (
// 				                            [0] => 6
// 				                        )
// 				                )
// 				        )
// 				    [Currency] => CNY
// 				    [Nationality] => CN
// 				    [RatePlanID] => 2903: 93023: 93023
// 				)
// 	 * @return  array 包含订单号ReferenceNo，以及价格
// 	 */
// 	public function priceConfirm($details=array()){
// 		if(empty($details)){
// 			return false;
// 		}
// 		//获取当前接口的路由信息
// 		$access_router = self::get_access_router('Hotel_Price_Confirm');
// 		$this->callback_info['PriceConfirmDetails']  = $details;
// 		// echo json_encode($this->callback_info);die;
// 		$price_list = $this->get_depoly_contents($access_router,$this->callback_info);

// 		return $price_list;
// 	}

// 	/**
// 	 * 支付成功后调用知行接口创建订单
// 	 * @Author zhixin
// 	 * @Date   2018-12-20
// 	 * @param  array      $order_details 请求对方接口携带的数组参数
// 	 * Array
//         (
//             [CheckOutDate] => 2017-09-10
//             [CheckInDate] => 2017-09-07
//             [NumOfRooms] => 1
//             [GuestList] => Array
//                 (
//                     [0] => Array
//                         (
//                             [RoomNum] => 1
//                             [GuestInfo] => Array
//                                 (
//                                     [0] => Array
//                                         (
//                                             [IsAdult] => 1
//                                             [Name] => Array
//                                                 (
//                                                     [Last] => ZHAO
//                                                     [First] => SHANNI
//                                                 )
//                                         )
//                                 )
//                         )
//                 )
//             [Contact] => Array
//                 (
//                     [Name] => Array
//                         (
//                             [Last] => ZHAO
//                             [First] => SHANNI
//                         )

//                     [Email] => 15812345678@qq.com
//                     [Phone] => 15812345678
//                 )

//             [CustomerRequest] => 无烟房，大床
//             [OutNo] => 5464315646465
//             [BookingID] => TP56434163456466886
//         )
// 	 * @return [type]             [description]
// 	 */
// 	public function createOrder($order_details= array())
//     {
//         if (empty($order_details)) {
//             return false;
//         }
//         //获取当前接口的路由信息
//         $access_router = self::get_access_router('Hotel_Create_Order');
//         $this->callback_info['OrderDetails'] = $order_details;
//         // echo json_encode($this->callback_info);die;
//         $order_result = $this->get_depoly_contents($access_router, $this->callback_info);

//         return $order_result;

//     }
//     /**
// 	 * 价格查询接口
// 	 * @Author FYY
// 	 * @Date   2018-12-26
// 	 * @param  array      $details 请求对方接口携带的数组参数
// 				{
// 				  "Header": {
// 				    "ClientID": "TripintlTestID",
// 				    "LicenseKey": "TripintlTestLicenseKey"
// 				  },
// 				  "HotelID": 807809,
// 				  "CheckInDate": "2018-03-06",
// 				  "CheckOutDate": "2018-03-07",
// 				  “RoomCount”:1,
// 				  “ShowDoubleConfirm”:true,
// 				   “GuestDetails”:{
// 				        “AdultCount”:2,
// 				        “ChildCount”,0
// 				        "ChildAgeList": [
				      
// 				        ]
// 				   }
// 				}

// 	 * @return  array 房型及价格
// 	 */
// 	public function hotelPrice($details=array()){
// 		if(empty($details)){
// 			return false;
// 		}
// 		//获取当前接口的路由信息
// 		$access_router = self::get_access_router('Hotel_Price');
// 		$this->callback_info['HotelID']  = $details['HotelID'];
// 		$this->callback_info['CheckInDate']  = $details['CheckInDate'];
// 		$this->callback_info['CheckOutDate']  = $details['CheckOutDate'];
// 		$this->callback_info['RoomCount']  = $details['RoomCount'];
// 		$this->callback_info['ShowDoubleConfirm']  = $details['ShowDoubleConfirm'];
// 		$this->callback_info['GuestDetails']  = $details['GuestDetails'];
// 		// echo json_encode($this->callback_info);die;
// 		//请求执行数据
// 		$price_list = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $price_list;
// 	}
	
// 	/**
// 	 * 通过第三方订单号查询订单
// 	 * @Author zhixin
// 	 * @Date   2018-12-28
// 	 * @param  integer    $booking_id 知行订单号
// 	 * @return array                 订单信息
// 	 */
// 	public function queryOrder($booking_id=''){
// 		$booking_id = trim($booking_id);
// 		if(!$booking_id){
// 			return false;
// 		}
// 		//获取当前接口的路由信息
//         $access_router = self::get_access_router('Hotel_Query_Order');
//         $this->callback_info['BookingID'] = $booking_id;
// 		// echo json_encode($this->callback_info);die;
// 		$order_result = $this->get_depoly_contents($access_router, $this->callback_info);
//         return $order_result;
// 	}
// 	/**
// 	 *获取房型的英文名称
// 	 */
// 	public function hotelRoomType($hotel_id=0){
// 		if(!$hotel_id){
// 			return false;
// 		}
// 		//获取当前接口的路由信息
// 		$access_router = self::get_access_router('Hotel_Room_Type');
// 		$this->callback_info['HotelID']  = $hotel_id;
// 		//请求执行数据
// 		$room_type = $this->get_depoly_contents($access_router,$this->callback_info);
// 		return $room_type;
// 	}
	
// }
?>