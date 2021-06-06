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

			case 'getHistory':
				getHistory($_REQUEST,$con);
				break;

			case 'addDetails':
				addDetails($_REQUEST,$con);
				break;

			case 'updateDetails':
				updateDetails($_REQUEST,$con);
				break;

			case 'deleteDetails':
				deleteDetails($_REQUEST,$con);
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
		$data = select('`bus_no`, `auto_inc_id`','bus_details','1',$conn);
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

	function getHistoryDB($conn){
		$data = select('*','accident_history','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				$data[$key]['view_date'] = date("d-m-Y", strtotime($value['accident_date']));
				if ($value['image_name'] != "") {
					$temp_values = unserialize($value['image_name']);
					$temp_data = [];
					for ($i = 0; $i < count($temp_values); $i++) {
						if (file_exists("/var/www/html/snbgroup.in/spb/uploads/accident_history/".$temp_values[$i].'.png')) {
							// $data[$key]['imageUrl'] = "data:image/jpeg;base64,".base64_encode(file_get_contents("/var/www/html/snbgroup.in/spb/uploads/".$value['image_name'].'.png'));
							array_push($temp_data, "/spb/uploads/accident_history/".$temp_values[$i].'.png');
						}
					}
					$data[$key]['imageUrl'] = $temp_data;
				}else{
					$data[$key]['imageUrl'] = "";
				}
			}
		}
		return $data == "empty"?[]: $data;
	}

	function getHistory($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getHistoryDB($conn),
			'driverData' => getEmployeeDB($conn,4),
			'busData' => getBusDetailsDB($conn)
		);
		echo json_encode($response);
	}

	function addDetails($request,$conn){
		$request['accident_date'] = date('Y-m-d', strtotime($request['accident_date']));
		$request['review_date'] = date('Y-m-d', strtotime($request['review_date']));
		$request['created_date'] = date('Y-m-d h:m:s');
		$request['modified_date'] = date('Y-m-d h:m:s');
		$request['image_name'] = hash('sha256', mt_rand() . microtime());

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$image_array = [];
			$request['imageUrl'] = json_decode($request['imageUrl']);

			for ($i=0; $i < count($request['imageUrl']); $i++) {
				array_push($image_array, $request['image_name']."_".$i);
				$imageUpload = base64tofile($request['imageUrl'][$i], "../uploads/accident_history/".$request['image_name']."_".$i.".png");
			}
			//$imageUpload = base64tofile($request['imageUrl'], "../uploads/".$request['image_name'].'.png');
			$request['image_name'] = serialize($image_array);
		}
		
		unset($request['imageUrl']);
		if ($imageUpload) {
			if(insert('accident_history',$request,$conn)){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details added Successfully",
					'data' => getHistoryDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'busData' => getBusDetailsDB($conn)
				);
			}else {
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to add Details",
					'data' => getHistoryDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'busData' => getBusDetailsDB($conn)
				);
			}
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getHistoryDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'busData' => getBusDetailsDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}
	function updateDetails($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['accident_date'] = date('Y-m-d', strtotime($request['accident_date']));
		$request['review_date'] = date('Y-m-d', strtotime($request['review_date']));
		$request['modified_date'] = date('Y-m-d h:m:s');
		$request['image_name'] = hash('sha256', mt_rand() . microtime());

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$image_array = [];
			$request['imageUrl'] = json_decode($request['imageUrl']);

			for ($i=0; $i < count($request['imageUrl']); $i++) {
				array_push($image_array, $request['image_name']."_".$i);
				$imageUpload = base64tofile($request['imageUrl'][$i], "../uploads/accident_history/".$request['image_name']."_".$i.".png");
			}
			//$imageUpload = base64tofile($request['imageUrl'], "../uploads/".$request['image_name'].'.png');
			$request['image_name'] = serialize($image_array);
		}
		
		unset($request['imageUrl']);
		if ($imageUpload) {
			$update = update($request,'accident_history',$condition,$conn);
			if($update){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details Updated Successfully",
					'data' => getHistoryDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'busData' => getBusDetailsDB($conn)
				);
			}else{
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to Update Details",
					'data' => getHistoryDB($conn),
					'driverData' => getEmployeeDB($conn,4),
					'busData' => getBusDetailsDB($conn)
				);
			}
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getHistoryDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'busData' => getBusDetailsDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);
	}

	function deleteDetails($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('accident_history',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Deleted Successfully",
				'data' => getHistoryDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'busData' => getBusDetailsDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Delete Details",
				'data' => getHistoryDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'busData' => getBusDetailsDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}
