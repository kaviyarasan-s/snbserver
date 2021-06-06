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

			case 'getDepartment':
				getDepartment($_REQUEST,$con);
				break;

			case 'addDepartment':
				addDepartment($_REQUEST,$con);
				break;

			case 'updateDepartment':
				updateDepartment($_REQUEST,$con);
				break;

			case 'deleteDepartment':
				deleteDepartment($_REQUEST,$con);
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

	function getDepartment($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getDepartmentDB($conn) 
		);
		echo json_encode($response);
	}

	function getDepartmentDB($conn){
		$data = select('*','department','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function addDepartment($request,$conn){
		$data = array('department_name' =>  $request['department_name']);
		if(insert('department', $data, $conn)){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Department Added Successfully",
				'data' => getDepartmentDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Add department",
				'data' => getDepartmentDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}

	function updateDepartment($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$update = update($request,'department',$condition,$conn);
		if($update)
		{
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Department Updated Successfully",
				'data' => getDepartmentDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Update Department",
				'data' => getDepartmentDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}

	function deleteDepartment($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('department',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Department Deleted Successfully",
				'data' => getDepartmentDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Delete Department",
				'data' => getDepartmentDB($conn) 
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}