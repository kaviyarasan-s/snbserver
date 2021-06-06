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

			case 'getRoute':
				getRoute($_REQUEST,$con);
				break;

			case 'addRoute':
				addRoute($_REQUEST,$con);
				break;

			case 'updateRoute':
				updateRoute($_REQUEST,$con);
				break;

			case 'deleteRoute':
				deleteRoute($_REQUEST,$con);
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

	function getRoute($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getRouteDB($conn) 
		);
		echo json_encode($response);
	}

	function getRouteDB($conn){
		$data = select('*','route_details','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function addRoute($request,$conn){
		if(insert('route_details', $request, $conn)){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Added Successfully",
				'data' => getRouteDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to add details",
				'data' => getRouteDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}

	function updateRoute($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$update = update($request,'route_details',$condition,$conn);
		if($update)
		{
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Updated Successfully",
				'data' => getRouteDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Update details",
				'data' => getRouteDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}

	function deleteRoute($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('route_details',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Deleted Successfully",
				'data' => getRouteDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete details",
				'data' => getRouteDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}