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

			case 'getWagesMode':
				getWagesMode($_REQUEST,$con);
				break;

			case 'addWagesMode':
				addWagesMode($_REQUEST,$con);
				break;

			case 'updateWagesMode':
				updateWagesMode($_REQUEST,$con);
				break;

			case 'deleteWagesMode':
				deleteWagesMode($_REQUEST,$con);
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

	function getWagesMode($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getWagesDB($conn),
			'designationData' => getDesignationDB($conn),
			'departmentData' => getDepartmentDB($conn)
		);
		echo json_encode($response);
	}
	function getWagesDB($conn){
		$data = select('*','wages_mode','1',$conn);
		return $data == "empty"?[]: $data;
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
	function addWagesMode($request,$conn){
		if(insert('wages_mode',$request,$conn)){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Wage Mode added Successfully",
				'data' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to add Wage Mode",
				'data' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}

	function updateWagesMode($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$update = update($request,'wages_mode',$condition,$conn);
		if($update){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Wage Mode updated Successfully",
				'data' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to update WagesMode",
				'data' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}

	function deleteWagesMode($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('wages_mode',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Deleted Successfully",
				'data' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete wage mode",
				'data' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}