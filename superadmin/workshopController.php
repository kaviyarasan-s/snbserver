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

			case 'getWorkShop':
				getWorkShop($_REQUEST,$con);
				break;

			case 'addWorkshop':
				addWorkshop($_REQUEST,$con);
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

	function getServiceDB($conn){
		$data = select('*','service','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function getServiceChecklistDB($conn){
		$data = select('*','service_checklist','1',$conn);
		return $data == "empty"?[]: $data;
	}
	
	function getBusDetailsDB($conn){
		$data = select('`bus_no`, `auto_inc_id`, `type`','bus_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?[]: $data;
	}

	function getWorkShopDB($conn){
		$data = select('*','workshop','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				$data[$key]['view_date'] = date("d-m-Y", strtotime($value['entry_date']));
				if ($value['image_name'] != "") {
					$temp_values = unserialize($value['image_name']);
					$temp_data = [];
					for ($i = 0; $i < count($temp_values); $i++) {
						if (file_exists("/var/www/html/snbgroup.in/spb/uploads/workshop/".$temp_values[$i].'.png')) {
							array_push($temp_data, "/spb/uploads/workshop/".$temp_values[$i].'.png');
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

	function getWorkShop($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getWorkShopDB($conn),
			'serviceData' => getServiceDB($conn),
			'busData' => getBusDetailsDB($conn),
			'serviceChecklistData' => getServiceChecklistDB($conn)
		);
		echo json_encode($response);
	}

	function addWorkshop($request,$conn){
		$request['entry_date'] = date('Y-m-d', strtotime($request['entry_date']));
		$request['created_date'] = date('Y-m-d h:m:s');
		$request['modified_date'] = date('Y-m-d h:m:s');
		$request['image_name'] = hash('sha256', mt_rand() . microtime());

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$image_array = [];
			$request['imageUrl'] = json_decode($request['imageUrl']);

			for ($i=0; $i < count($request['imageUrl']); $i++) {
				array_push($image_array, $request['image_name']."_".$i);
				$imageUpload = base64tofile($request['imageUrl'][$i], "../uploads/workshop/".$request['image_name']."_".$i.".png");
			}
			$request['image_name'] = serialize($image_array);
		}
		unset($request['imageUrl']);
		if ($imageUpload) {
			if(insert('workshop',$request,$conn)){
				$data = select('*','service','`service_name` = "'.$request["service"].'" AND `vehicle_type` = '.$request["vehicle_type"],$conn);
				if ($data == "empty") {
					$insertData = array(
						'service_name' => $request["service"], 
						'vehicle_type' => $request["vehicle_type"]
					);
					insert('service', $insertData, $conn);
				}

				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details added Successfully",
					'data' => getWorkShopDB($conn),
					'serviceData' => getServiceDB($conn),
					'busData' => getBusDetailsDB($conn),
					'serviceChecklistData' => getServiceChecklistDB($conn)
				);
			}else {
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to add Details",
					'data' => getWorkShopDB($conn),
					'serviceData' => getServiceDB($conn),
					'busData' => getBusDetailsDB($conn),
					'serviceChecklistData' => getServiceChecklistDB($conn)
				);
			}
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getWorkShopDB($conn),
				'serviceData' => getServiceDB($conn),
				'busData' => getBusDetailsDB($conn),
				'serviceChecklistData' => getServiceChecklistDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	

	}

	function updateDetails($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['entry_date'] = date('Y-m-d', strtotime($request['entry_date']));
		$request['modified_date'] = date('Y-m-d h:m:s');
		$request['image_name'] = hash('sha256', mt_rand() . microtime());

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$image_array = [];
			$request['imageUrl'] = json_decode($request['imageUrl']);

			for ($i=0; $i < count($request['imageUrl']); $i++) {
				array_push($image_array, $request['image_name']."_".$i);
				$imageUpload = base64tofile($request['imageUrl'][$i], "../uploads/workshop/".$request['image_name']."_".$i.".png");
			}
			$request['image_name'] = serialize($image_array);
		}
		
		unset($request['imageUrl']);
		if ($imageUpload) {
			$update = update($request,'workshop',$condition,$conn);
			if($update){
				$data = select('*','service','`service_name` = "'.$request["service"].'" AND `vehicle_type` = '.$request["vehicle_type"],$conn);
				if ($data == "empty") {
					$insertData = array(
						'service_name' => $request["service"], 
						'vehicle_type' => $request["vehicle_type"]
					);
					insert('service', $insertData, $conn);
				}
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details Updated Successfully",
					'data' => getWorkShopDB($conn),
					'serviceData' => getServiceDB($conn),
					'busData' => getBusDetailsDB($conn),
					'serviceChecklistData' => getServiceChecklistDB($conn)
				);
			}else{
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to Update Details",
					'data' => getWorkShopDB($conn),
					'serviceData' => getServiceDB($conn),
					'busData' => getBusDetailsDB($conn),
					'serviceChecklistData' => getServiceChecklistDB($conn)
				);
			}
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getWorkShopDB($conn),
				'serviceData' => getServiceDB($conn),
				'busData' => getBusDetailsDB($conn),
				'serviceChecklistData' => getServiceChecklistDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	 
	}

	function deleteDetails($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('workshop',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Deleted Successfully",
				'data' => getWorkShopDB($conn),
				'serviceData' => getServiceDB($conn),
				'busData' => getBusDetailsDB($conn),
				'serviceChecklistData' => getServiceChecklistDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Delete Details",
				'data' => getWorkShopDB($conn),
				'serviceData' => getServiceDB($conn),
				'busData' => getBusDetailsDB($conn),
				'serviceChecklistData' => getServiceChecklistDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}