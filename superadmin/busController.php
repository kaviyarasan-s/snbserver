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

			case 'getBusDetails':
				getBusDetails($_REQUEST,$con);
				break;

			case 'addBusDetails':
				addBusDetails($_REQUEST,$con);
				break;

			case 'updateBusDetails':
				updateBusDetails($_REQUEST,$con);
				break;

			case 'deleteBusDetails':
				deleteBusDetails($_REQUEST,$con);
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

	function getBusDetailsDB($conn){
		$data = select('*','bus_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				$data[$key]["trip_data"] = unserialize($value["trip_data"]);
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

	function getRouteDB($conn){
		$data = select('*','route_details','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function getDepartmentDB($conn){
		$data = select('*','department','1',$conn);
		return $data == "empty"?[]: $data;
	}
	function getEmployeeDB($conn, $flag){
		$data = select('`auto_inc_id`, `employee_name`','employee_details','`designation_id` = '.$flag,$conn);
		if ($data != "empty") {

		}
		return $data == "empty"?[]: $data;
	}
	function getBusDetails($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getBusDetailsDB($conn),
			'driverData' => getEmployeeDB($conn,4),
			'conductorData' => getEmployeeDB($conn,5),
			'traineeData' => getEmployeeDB($conn,6),
			'routeData' => getRouteDB($conn),
			'departmentData' => getDepartmentDB($conn)

		);
		echo json_encode($response);
	}

	function addBusDetails($request,$conn){

		$request['invoice_date'] = date('Y-m-d', strtotime($request['invoice_date']));
		$request['permit_renew_date'] = date('Y-m-d', strtotime($request['permit_renew_date']));
		$request['insurance_date'] = date('Y-m-d', strtotime($request['insurance_date']));
		$request['fc_date'] = date('Y-m-d', strtotime($request['fc_date']));
		$request['tax_date'] = date('Y-m-d', strtotime($request['tax_date']));
		$request['loan_due_date'] = date('Y-m-d', strtotime($request['loan_due_date']));
		$request['loan_end_date'] = date('Y-m-d', strtotime($request['loan_end_date']));
		$request['permit_start_date'] = date('Y-m-d', strtotime($request['permit_start_date']));
		$request['permit_end_date'] = date('Y-m-d', strtotime($request['permit_end_date']));
		$request['created_date'] = date('Y-m-d');
		$request['modified_date'] = date('Y-m-d');
		$request['id'] = hash('sha256', mt_rand() . microtime());
		$request["trip_data"] = serialize($request["trip_data"]);

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$imageUpload = base64tofile($request['imageUrl'], "../uploads/".$request['id'].'.png');
		}
		
		unset($request['imageUrl']);
		if ($imageUpload) {
			if(insert('bus_details',$request,$conn)){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details added Successfully",
					'data' => getBusDetailsDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'conductorData' => getEmployeeDB($conn,5),
					'traineeData' => getEmployeeDB($conn,6),
					'routeData' => getRouteDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}else {
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to add bus details",
					'data' => getBusDetailsDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'conductorData' => getEmployeeDB($conn,5),
					'traineeData' => getEmployeeDB($conn,6),
					'routeData' => getRouteDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getBusDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'routeData' => getRouteDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}

	function updateBusDetails($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['invoice_date'] = date('Y-m-d', strtotime($request['invoice_date']));
		$request['permit_renew_date'] = date('Y-m-d', strtotime($request['permit_renew_date']));
		$request['insurance_date'] = date('Y-m-d', strtotime($request['insurance_date']));
		$request['fc_date'] = date('Y-m-d', strtotime($request['fc_date']));
		$request['tax_date'] = date('Y-m-d', strtotime($request['tax_date']));
		$request['loan_due_date'] = date('Y-m-d', strtotime($request['loan_due_date']));
		$request['loan_end_date'] = date('Y-m-d', strtotime($request['loan_end_date']));
		$request['permit_start_date'] = date('Y-m-d', strtotime($request['permit_start_date']));
		$request['permit_end_date'] = date('Y-m-d', strtotime($request['permit_end_date']));
		$request['modified_date'] = date('Y-m-d');
		$request["trip_data"] = serialize($request["trip_data"]);

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$imageUpload = base64tofile($request['imageUrl'], "../uploads/".$request['id'].'.png');
		}
		
		unset($request['imageUrl']);
		if ($imageUpload) {	
			$update = update($request,'bus_details',$condition,$conn);
			if($update){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Bus details updated Successfully",
					'data' => getBusDetailsDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'conductorData' => getEmployeeDB($conn,5),
					'traineeData' => getEmployeeDB($conn,6),
					'routeData' => getRouteDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}else{
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to update Bus details",
					'data' => getBusDetailsDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'conductorData' => getEmployeeDB($conn,5),
					'traineeData' => getEmployeeDB($conn,6),
					'routeData' => getRouteDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getBusDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'routeData' => getRouteDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}

	function deleteBusDetails($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('bus_details',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Bus details deleted Successfully",
				'data' => getBusDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'routeData' => getRouteDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete Bus Details",
				'data' => getBusDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'routeData' => getRouteDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}		
	}


