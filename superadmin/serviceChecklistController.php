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

			case 'getService':
				getService($_REQUEST,$con);
				break;

			case 'addService':
				addService($_REQUEST,$con);
				break;

			case 'updateService':
				updateService($_REQUEST,$con);
				break;

			case 'deleteService':
				deleteService($_REQUEST,$con);
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

	function getService($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getServiceDB($conn) 
		);
		echo json_encode($response);
	}

	function getServiceDB($conn){
		$data = select('*','service_checklist','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function addService($request,$conn){
		// $data = array('service_name' =>  $request['service_name']);
		if(insert('service_checklist', $request, $conn)){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Service Added Successfully",
				'data' => getServiceDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Add Service",
				'data' => getServiceDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}

	function updateService($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$update = update($request,'service_checklist',$condition,$conn);
		if($update)
		{
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Service Updated Successfully",
				'data' => getServiceDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Update Service",
				'data' => getServiceDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}

	function deleteService($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('service_checklist',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Service Deleted Successfully",
				'data' => getServiceDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Delete Service",
				'data' => getServiceDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}