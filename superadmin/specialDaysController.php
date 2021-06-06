<?php
	include_once '../model/db.php';
	$con=db_connect();
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)){
		$_POST = json_decode(file_get_contents('php://input'), true);
		$_REQUEST = $_POST;
	}

	$function = $_REQUEST['flag'];
	unset($_REQUEST['flag']);

	if (!empty($function)) {
		switch ($function) {

			case 'getSpecial':
				getSpecial($_REQUEST,$con);
				break;

			case 'addSpecial':
				addSpecial($_REQUEST,$con);
				break;

			case 'updateSpecial':
				updateSpecial($_REQUEST,$con);
				break;

			case 'deleteSpecial':
				deleteSpecial($_REQUEST,$con);
				break;

			default:
				$response = array('status' => 'failed','message' => 'Something went wrong.');
	    		echo json_encode($response);
				break;
		}
	}else{
		$response = array('status' => 'failed','message' => 'Something went wrong.');
	    echo json_encode($response);
	}

	function getSpecial($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getSpecialDB($conn) 
		);
		echo json_encode($response);
	}

	function getSpecialDB($conn){
		$data = select('*','special_days','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function addSpecial($request,$conn){
		if(insert('special_days', $request, $conn)){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Added Successfully",
				'data' => getSpecialDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to add details",
				'data' => getSpecialDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}

	function updateSpecial($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$update = update($request,'special_days',$condition,$conn);
		if($update)
		{
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Updated Successfully",
				'data' => getSpecialDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Update details",
				'data' => getSpecialDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}

	function deleteSpecial($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('special_days',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Deleted Successfully",
				'data' => getSpecialDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete details",
				'data' => getSpecialDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}