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

			case 'getDesignation':
				getDesignation($_REQUEST,$con);
				break;

			case 'addDesignation':
				addDesignation($_REQUEST,$con);
				break;

			case 'updateDesignation':
				updateDesignation($_REQUEST,$con);
				break;

			case 'deleteDesignation':
				deleteDesignation($_REQUEST,$con);
				break;

			default:
				$response = array('status' => 'failed','message' => 'Something went wrong.');
	    		echo json_encode($response);
				break;
		}
	}
	else{
		$response = array('status' => 'failed','message' => 'Something went wrong.');
	    echo json_encode($response);
	}

	function getDesignation($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getDesignationDB($conn),
			'departmentData' => getDepartmentDB($conn)
		);
		echo json_encode($response);
	}

	function getDepartmentDB($conn){
		$data = select('*','department','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function getDesignationDB($conn){
		$data = select('*','designation','1',$conn);
		if($data != 'empty'){
			foreach ($data as $key => $value) {
				$temp_data = select("`department_name`","department",'`id` IN ('.$value['department_id'].')',$con);
				if ($temp_data != "empty") {
					$department_name = array();
					foreach ($temp_data as $innerValue) {
						$department_name[] = $innerValue['department_name'];
					}
					$department_name = implode(', ', $department_name);
				}else{
					$department_name = "Not Available";
				}
				$data[$key]['department_names'] = $department_name;
			}
		}
		return $data == "empty"?[]: $data;
	}

	function addDesignation($request,$conn){
		if(insert('designation',$request,$conn)){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Designation added Successfully",
				'data' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to add product",
				'data' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}

	function updateDesignation($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$update = update($request,'designation',$condition,$conn);
		if($update)
		{
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Designation updated Successfully",
				'data' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
		else
		{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to update Designation",
				'data' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}

	function deleteDesignation($request,$conn){		
		$condition="`id` = '".$request['id']."'";
		$delete = delete('designation',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Designation deleted Successfully",
				'data' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete Designation"
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}