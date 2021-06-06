<?php
	include_once '../model/db.php';
	$con=db_connect();
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)){
		$_POST = json_decode(file_get_contents('php://input'), true);
		$_REQUEST = $_POST;
	}
	if(isset($_REQUEST['user_name'])){
		$userName = sanitize($_REQUEST['user_name'],$con);
		$password = $_REQUEST['password'];
		$UserValidationCondition = "`user_name` = '".$userName."'";
		$UserValidation = select('`id`,`user_name`,`password`', '`super_admin_details`',$UserValidationCondition, $con);
		if ($UserValidation != 'empty') {
			if(password_verify($password, $UserValidation[0]['password'])){
				unset($UserValidation[0]['password']);
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "User logged in succesfully!",
					'userData' => $UserValidation[0]
				);
				echo json_encode($response, JSON_PRETTY_PRINT);
			}else{
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Invalid Password"
				);
				echo json_encode($response, JSON_PRETTY_PRINT);
			}
		}else{
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "User Doesn't Exists"
				);
				echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}else{
		$response = array(
			'status' => '401',
			'response' => 'error',
			'message' => "Invalid Parameters");
		echo json_encode($response, JSON_PRETTY_PRINT);
	}