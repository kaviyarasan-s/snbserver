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

			case 'getSchedulingDetails':
				getSchedulingDetails($_REQUEST,$con);
				break;

			case 'addSchedulingDetails':
				addSchedulingDetails($_REQUEST,$con);
				break;

			case 'updateSchedulingDetails':
				updateSchedulingDetails($_REQUEST,$con);
				break;

			case 'deleteSchedulingDetails':
				deleteSchedulingDetails($_REQUEST,$con);
				break;

			case 'reSchedulingDetails':
				reSchedulingDetails($_REQUEST,$con);
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

	function getSchedulingDetailsDB($conn){
		$data = select('*','bus_schedule_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?[]: $data;
	}
	function getBusDetailsDB($conn){
		$data = select('`bus_no`, `no_trips` ,`auto_inc_id`,`driver_id`,`conductor_id`,`trainee_id`','bus_details','`type` = 0',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?[]: $data;
	}
	function getEmployeeDB($conn, $flag){
		$data = select('`auto_inc_id`, `employee_name`','employee_details','`designation_id` = '.$flag,$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?[]: $data;
	}


	function getEmployeeName($conn, $emp_id){
		$data = select('`employee_name`','employee_details','`auto_inc_id` = '.$emp_id,$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?"N/A": $data[0]["employee_name"];
	}

	function getEmployeeMobile($conn, $emp_id){
		$data = select('`employee_mobile`','employee_details','`auto_inc_id` = '.$emp_id,$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?false: $data[0]["employee_mobile"];
	}

	function getSchedulingDetails($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getSchedulingDetailsDB($conn),
			'driverData' => getEmployeeDB($conn,4),
			'conductorData' => getEmployeeDB($conn,5),
			'traineeData' => getEmployeeDB($conn,6),
			'busData' => getBusDetailsDB($conn)
		);
		echo json_encode($response);
	}

	function addSchedulingDetails($request,$conn){

		$flag = true;
		$count = 0;
		$request_data = $request["data"];
		foreach ($request_data as $key => $value) {
			//echo json_encode($value);
			$request_data = $value;
			$request_data['date'] = date('Y-m-d', strtotime($value['date']));
			$request_data['created_date'] = date('Y-m-d');
			$request_data['modified_date'] = date('Y-m-d');


			$condition="`date` = '".date('Y-m-d', strtotime($value['date']))."' and `bus_details` = '".$request_data['bus_details']."'";
			$delete = delete('bus_schedule_details',$condition,$conn);

			if($delete){

				if(insert('bus_schedule_details',$request_data,$conn)){
					$getDriverMobile = getEmployeeMobile($conn,$request_data['driver_id']);
					$getConductorMobile = getEmployeeMobile($conn,$request_data['conductor_id']);
					$getTraineeMobile = getEmployeeMobile($conn,$request_data['trainee_id']);
					$getManagerMobile = getEmployeeMobile($conn,12);

					if ($getDriverMobile != false) {
						$temp = send_message($getDriverMobile,"Route has been assigned  \n Date :".$value['date']."  \n Driver Name : ".getEmployeeName($conn,$request_data['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request_data['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request_data['trainee_id'],$conn).".");
					}
					if ($getConductorMobile != false) {
						$temp = send_message($getConductorMobile,"Route has been assigned \n Date :".$value['date']."  \n Driver Name : ".getEmployeeName($conn,$request_data['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request_data['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request_data['trainee_id'],$conn).".");
					}
					if ($getTraineeMobile != false) {
						$temp = send_message($getTraineeMobile,"Route has been assigned \n Date :".$value['date']."  \n Driver Name : ".getEmployeeName($conn,$request_data['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request_data['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request_data['trainee_id'],$conn).".");
					}
					// if ($getManagerMobile != false) {
					// 	send_message($getManagerMobile,"Route has been assigned \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
					// }
					$count++;
				}else{
					$flag = false;
				}
			}else{
				$flag = false;
			}
		}

		if ($flag) {
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details added Successfully",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)
			);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to add Details",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)
			);
		}
		error_log(json_encode($response));
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}

	function reSchedulingDetails($request,$conn){

		$flag = true;
		$count = 0;
		$request_data = $request["data"];
		foreach ($request_data as $key => $value) {
			//echo json_encode($value);
			$request_data = $value;
			$request_data['date'] = date('Y-m-d', strtotime($value['date']));
			$request_data['created_date'] = date('Y-m-d');
			$request_data['modified_date'] = date('Y-m-d');


			$condition="`date` = '".date('Y-m-d', strtotime($value['date']))."' and `bus_details` = '".$request_data['bus_details']."'";
			$delete = delete('bus_schedule_details',$condition,$conn);

			if($delete){

				if(insert('bus_schedule_details',$request_data,$conn)){
					$getDriverMobile = getEmployeeMobile($conn,$request_data['driver_id']);
					$getConductorMobile = getEmployeeMobile($conn,$request_data['conductor_id']);
					$getTraineeMobile = getEmployeeMobile($conn,$request_data['trainee_id']);
					$getManagerMobile = getEmployeeMobile($conn,12);

					$message = "Route has been re-assigned ".($request_data['trip_no']?" \n Trip No : ".$request_data['trip_no']." ":null)."  \n Date :".$value['date']."  \n Driver Name : ".getEmployeeName($conn,$request_data['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request_data['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request_data['trainee_id'],$conn).".";

					if ($getDriverMobile != false) {
						send_message($getDriverMobile, $message);
					}
					if ($getConductorMobile != false) {
						send_message($getConductorMobile, $message);
					}
					if ($getTraineeMobile != false) {
						send_message($getTraineeMobile, $message);
					}
					// if ($getManagerMobile != false) {
					// 	send_message($getManagerMobile,"Route has been assigned \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
					// }
					$count++;
				}else{
					$flag = false;
				}
			}else{
				$flag = false;
			}
		}

		if ($flag) {
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details updated Successfully",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)
			);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to update Details",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}

	function updateSchedulingDetails($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['start_date'] = date('Y-m-d', strtotime($request['start_date']));
		$request['end_date'] = date('Y-m-d', strtotime($request['end_date']));
		$request['modified_date'] = date('Y-m-d');

		$update = update($request,'bus_schedule_details',$condition,$conn);
		if($update){

			$getDriverMobile = getEmployeeMobile($conn,$request['driver_id']);
			$getConductorMobile = getEmployeeMobile($conn,$request['conductor_id']);
			$getTraineeMobile = getEmployeeMobile($conn,$request['trainee_id']);
			$getManagerMobile = getEmployeeMobile($conn,12);


			if ($getDriverMobile != false) {
				send_message($getDriverMobile,"Route has been re-assigned \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			}
			if ($getConductorMobile != false) {
				send_message($getConductorMobile,"Route has been re-assigned \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			}
			if ($getTraineeMobile != false) {
				send_message($getTraineeMobile,"Route has been re-assigned \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			}
			// if ($getManagerMobile != false) {
			// 	send_message($getManagerMobile,"Route has been re-assigned \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			// }
			
			//send_message("9843629900","Route has been re-assigned \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details updated Successfully",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)

			);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to update details",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)

			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);			
	}

	function deleteSchedulingDetails($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('bus_schedule_details',$condition,$conn);

		if($delete){

			$getDriverMobile = getEmployeeMobile($conn,$request['driver_id']);
			$getConductorMobile = getEmployeeMobile($conn,$request['conductor_id']);
			$getTraineeMobile = getEmployeeMobile($conn,$request['trainee_id']);
			$getManagerMobile = getEmployeeMobile($conn,12);

			if ($getDriverMobile != false) {
				send_message($getDriverMobile,"Route has been cancelled \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			}
			if ($getConductorMobile != false) {
				send_message($getConductorMobile,"Route has been cancelled \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			}
			if ($getTraineeMobile != false) {
				send_message($getTraineeMobile,"Route has been cancelled \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			}
			// if ($getManagerMobile != false) {
			// 	send_message($getManagerMobile,"Route has been cancelled \n Driver Name : ".getEmployeeName($conn,$request['driver_id'])." \n Conductor Name : ".getEmployeeName($conn,$request['conductor_id'])." \n Trainee Name : ".getEmployeeName($conn,$request['trainee_id'],$conn).".");
			// }
			
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details deleted Successfully",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete Details",
				'data' => getSchedulingDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'traineeData' => getEmployeeDB($conn,6),
				'busData' => getBusDetailsDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}		
	}

