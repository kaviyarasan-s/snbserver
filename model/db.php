<?php 
	header("Access-Control-Allow-Origin: *");
	header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
	include_once 'curd_operations.php';
	// error_reporting(E_ERROR | E_PARSE);
	//error_reporting(0);
	session_start();
	function db_connect(){
		$connection = mysqli_connect("localhost", "root", "SI6VWAv^?hGI@#$1!prints", "spb");
		if (!$connection) {
			die("Connection failed: " . mysqli_connect_error());
			exit();
		}
		return $connection;
	}

	function execute_query($query, $link){
		if(!empty($link)){
			return mysqli_query($link, $query);
		}else{
			return mysqli_query(db_connect(), $query);
		}
	}

	function get_array_from_object($result){
		return mysqli_fetch_array($result, MYSQLI_ASSOC);
	}

	function sanitize($input, $con){
		return mysqli_real_escape_string($con, $input);
	}
	function base64tofile($base64string, $filename){
		if (file_exists($filename)) {
			error_log("The file $filename exists deleting");
			unlink($filename);
		}
		$img = explode( ',',  $base64string);
		$data = base64_decode($img[1]);
		$result = file_put_contents($filename, $data);
		error_log("returning ".$result);
		return $result;
	}
	function send_message($phone_number,$message){
		$url = 'http://sms.digimiles.in/bulksms/bulksms';
		$fields_string ="";
		$fields = array( 'username' =>urlencode('di78-sigi'), 'password'=>urlencode('sigi0715'),'type'=>'0', 'dlr'=>'1', 'destination'=>urlencode($phone_number),'source'=>'SIGITS', 'message'=>urlencode($message));
		//url-ify
		foreach($fields as $key=>$value){
			$fields_string .= $key.'='.$value.'&'; rtrim($fields_string,'&');
		}
		rtrim($fields_string,'&');
		$url_final = $url.'?'.$fields_string;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url_final);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
	}