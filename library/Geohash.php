<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2013,父母邦，帮父母
// 日 期：2013-5-24
// 作　者：卢少锦
// E-mail :shaojin.lu@linktone.com
// 文件名：Geohash.php
// 创建时间:下午2:01:55
// 编 码：UTF-8
// 摘 要: geohash算法实现
// ///////////////////////////////////////////////////

class Geohash {
	
	//编码字符串
	private $coding = "0123456789bcdefghjkmnpqrstuvwxyz";
	private $codingMap = array ();
	
	
	/**
	 * 构造函数初始化
	 */
	public function __construct() {
		for($i = 0; $i < 32; $i ++) {
			$this->codingMap [substr ( $this->coding, $i, 1 )] = str_pad ( decbin ( $i ), 5, "0", STR_PAD_LEFT );
		}
	}
	
	/**
	 * 解码操作
	 * @param string $hash
	 * @return array
	 */
	public function decode($hash) {
		$binary = "";
		$hl = strlen ( $hash );
		for($i = 0; $i < $hl; $i ++) {
			$binary .= $this->codingMap [substr ( $hash, $i, 1 )];
		}
		
		$bl = strlen ( $binary );
		$blat = "";
		$blong = "";
		for($i = 0; $i < $bl; $i ++) {
			if ($i % 2)
				$blat = $blat . substr ( $binary, $i, 1 );
			else
				$blong = $blong . substr ( $binary, $i, 1 );
		}
		
		$lat = $this->binDecode ( $blat, - 90, 90 );
		$long = $this->binDecode ( $blong, - 180, 180 );
		
		$latErr = $this->calcError ( strlen ( $blat ), - 90, 90 );
		$longErr = $this->calcError ( strlen ( $blong ), - 180, 180 );
		
		$latPlaces = max ( 1, - round ( log10 ( $latErr ) ) ) - 1;
		$longPlaces = max ( 1, - round ( log10 ( $longErr ) ) ) - 1;
		
		$lat = round ( $lat, $latPlaces );
		$long = round ( $long, $longPlaces );
		
		return array (
				$lat,
				$long 
		);
	}
	
	/**
	 * 编码操作
	 * @param float $lat
	 * @param float $long
	 * @return string
	 */
	public function encode($lat, $long) {
		$plat = $this->precision ( $lat );
		$latbits = 1;
		$err = 45;
		while ( $err > $plat ) {
			$latbits ++;
			$err /= 2;
		}
		
		$plong = $this->precision ( $long );
		$longbits = 1;
		$err = 90;
		while ( $err > $plong ) {
			$longbits ++;
			$err /= 2;
		}
		
		$bits = max ( $latbits, $longbits );
		
		$longbits = $bits;
		$latbits = $bits;
		$addlong = 1;
		while ( ($longbits + $latbits) % 5 != 0 ) {
			$longbits += $addlong;
			$latbits += ! $addlong;
			$addlong = ! $addlong;
		}
		
		$blat = $this->binEncode ( $lat, - 90, 90, $latbits );
		
		$blong = $this->binEncode ( $long, - 180, 180, $longbits );
		
		$binary = "";
		$uselong = 1;
		while ( strlen ( $blat ) + strlen ( $blong ) ) {
			if ($uselong) {
				$binary = $binary . substr ( $blong, 0, 1 );
				$blong = substr ( $blong, 1 );
			} else {
				$binary = $binary . substr ( $blat, 0, 1 );
				$blat = substr ( $blat, 1 );
			}
			$uselong = ! $uselong;
		}
		
		$hash = "";
		for($i = 0; $i < strlen ( $binary ); $i += 5) {
			$n = bindec ( substr ( $binary, $i, 5 ) );
			$hash = $hash . $this->coding [$n];
		}
		
		return $hash;
	}
	
	
	private function calcError($bits, $min, $max) {
		$err = ($max - $min) / 2;
		while ( $bits -- )
			$err /= 2;
		return $err;
	}
	
	private function precision($number) {
		$precision = 0;
		$pt = strpos ( $number, '.' );
		if ($pt !== false) {
			$precision = - (strlen ( $number ) - $pt - 1);
		}
		
		return pow ( 10, $precision ) / 2;
	}
	
	private function binEncode($number, $min, $max, $bitcount) {
		if ($bitcount == 0)
			return "";
		$mid = ($min + $max) / 2;
		if ($number > $mid)
			return "1" . $this->binEncode ( $number, $mid, $max, $bitcount - 1 );
		else
			return "0" . $this->binEncode ( $number, $min, $mid, $bitcount - 1 );
	}
	
	private function binDecode($binary, $min, $max) {
		$mid = ($min + $max) / 2;
		
		if (strlen ( $binary ) == 0)
			return $mid;
		
		$bit = substr ( $binary, 0, 1 );
		$binary = substr ( $binary, 1 );
		
		if ($bit == 1)
			return $this->binDecode ( $binary, $mid, $max );
		else
			return $this->binDecode ( $binary, $min, $mid );
	}
	
	/**
	 * 计算两个不同地理位置之间的距离
	 * @param float $lat1
	 * @param float $lng1
	 * @param float $lat2
	 * @param float $lng2
	 * @return int
	 */
	public function getDistance($lat1,$lng1,$lat2,$lng2){
		// 地球半径
		$R = 6378137;
		// 将角度转为狐度
		$radLat1 = deg2rad($lat1);
		$radLat2 = deg2rad($lat2);
		$radLng1 = deg2rad($lng1);
		$radLng2 = deg2rad($lng2);
		// 结果
		$s = acos(cos($radLat1)*cos($radLat2)*cos($radLng1-$radLng2)+sin($radLat1)*sin($radLat2))*$R;
		// 精度
		$s = round($s* 10000) /10000 ;
		return  round($s);
	}
}

?>