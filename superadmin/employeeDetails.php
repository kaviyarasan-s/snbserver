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

			case 'getEmployees':
				getEmployees($_REQUEST,$con);
				break;

			case 'addEmployee':
				addEmployee($_REQUEST,$con);
				break;

			case 'updateEmployee':
				updateEmployee($_REQUEST,$con);
				break;

			case 'deleteEmployee':
				deleteEmployee($_REQUEST,$con);
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


	function getEmployees($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getEmployeeDB($conn),
			'wagesData' => getWagesDB($conn),
			'designationData' => getDesignationDB($conn),
			'departmentData' => getDepartmentDB($conn)
		);
		echo json_encode($response);
	}

	function getEmployeeDB($conn){
		$data = select('*','employee_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				$data[$key]['doj_default'] = $value['doj'];
				$data[$key]['doj'] = date("d-m-Y", strtotime($value['doj']));
				if (file_exists("/var/www/html/snbgroup.in/spb/uploads/".$value['id'].'.png')) {
					// $data[$key]['imageUrl'] = "data:image/jpeg;base64,".base64_encode(file_get_contents("/var/www/html/snbgroup.in/spb/uploads/".$value['id'].'.png'));
					$data[$key]['imageUrl'] = "/spb/uploads/".$value['id'].'.png';
				}else{
					$data[$key]['imageUrl'] = "";
				}
			}
		}
		return $data == "empty"?[]: $data;
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
		return $data == "empty"?[]: $data;
	}

	function addEmployee($request,$conn){
		$request['license_exp_date'] = date('Y-m-d', strtotime($request['license_exp_date']));
		$request['doj'] = date('Y-m-d', strtotime($request['doj']));
		$request['id'] = hash('sha256', mt_rand() . microtime());

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$imageUpload = base64tofile($request['imageUrl'], "../uploads/".$request['id'].'.png');
		}
		
		unset($request['imageUrl']);
		if ($imageUpload) {
			if(insert('employee_details',$request,$conn)){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Employee added Successfully",
					'data' => getEmployeeDB($conn),
					'wagesData' => getWagesDB($conn),
					'designationData' => getDesignationDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}else {
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to add Employee",
					'data' => getEmployeeDB($conn),
					'wagesData' => getWagesDB($conn),
					'designationData' => getDesignationDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getEmployeeDB($conn),
				'wagesData' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}

	function updateEmployee($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['license_exp_date'] = date('Y-m-d', strtotime($request['license_exp_date']));
		$request['doj'] = date('Y-m-d', strtotime($request['doj']));

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$imageUpload = base64tofile($request['imageUrl'], "../uploads/".$request['id'].'.png');
		}
		
		unset($request['imageUrl']);
		if ($imageUpload) {		
			$update = update($request,'employee_details',$condition,$conn);
			if($update){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Employee updated Successfully",
					'data' => getEmployeeDB($conn),
					'wagesData' => getWagesDB($conn),
					'designationData' => getDesignationDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}else{
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to update Employee",
					'data' => getEmployeeDB($conn),
					'wagesData' => getWagesDB($conn),
					'designationData' => getDesignationDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getEmployeeDB($conn),
				'wagesData' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);
	}

	function deleteEmployee($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('employee_details',$condition,$conn);

		if($delete){
			if (file_exists("/var/www/html/snbgroup.in/spb/uploads/".$request['id'].'.png')) {
				unlink("/var/www/html/snbgroup.in/spb/uploads/".$request['id'].'.png');
			}
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Employee deleted Successfully",
				'data' => getEmployeeDB($conn),
				'wagesData' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete Employee",
				'data' => getEmployeeDB($conn),
				'wagesData' => getWagesDB($conn),
				'designationData' => getDesignationDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}
?>