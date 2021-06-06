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

			case 'getDetails':
				getDetails($_REQUEST,$con);
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
		$data = select('`bus_no`, `auto_inc_id`, `type`','bus_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?[]: $data;
	}
	function getDepartmentDB($conn){
		$data = select('*','department','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function getPurchaseDB($conn){
		$data = select('*','purchase_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				$data[$key]['view_date'] = date("d-m-Y", strtotime($value['date']));
				$data[$key]["payment_info"] = unserialize($value["payment_info"]);
				$data[$key]["item_info"] = unserialize($value["item_info"]);
				if ($value['image_name'] != "") {
					$temp_values = unserialize($value['image_name']);
					$temp_data = [];
					for ($i = 0; $i < count($temp_values); $i++) {
						if (file_exists("/var/www/html/snbgroup.in/spb/uploads/purchase/".$temp_values[$i].'.png')) {
							array_push($temp_data, "/spb/uploads/purchase/".$temp_values[$i].'.png');
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

	function getDetails($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getPurchaseDB($conn),
			'busData' => getBusDetailsDB($conn),
			'departmentData' => getDepartmentDB($conn)
		);
		echo json_encode($response);
	}

	function addDetails($request,$conn){
		$request['date'] = date('Y-m-d', strtotime($request['date']));
		$request['created_date'] = date('Y-m-d h:m:s');
		$request['modified_date'] = date('Y-m-d h:m:s');
		$request["payment_info"] = serialize($request["payment_info"]);
		$request["item_info"] = serialize($request["item_info"]);
		$request['image_name'] = hash('sha256', mt_rand() . microtime());

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$image_array = [];
			$request['imageUrl'] = json_decode($request['imageUrl']);

			for ($i=0; $i < count($request['imageUrl']); $i++) {
				array_push($image_array, $request['image_name']."_".$i);
				$imageUpload = base64tofile($request['imageUrl'][$i], "../uploads/purchase/".$request['image_name']."_".$i.".png");
			}
			$request['image_name'] = serialize($image_array);
		}
		unset($request['imageUrl']);
		if ($imageUpload) {
			if(insert('purchase_details',$request,$conn)){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details added Successfully",
					'data' => getPurchaseDB($conn),
					'busData' => getBusDetailsDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}else {
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to add Details",
					'data' => getPurchaseDB($conn),
					'busData' => getBusDetailsDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getWorkShopDB($conn),
				'serviceData' => getServiceDB($conn),
				'busData' => getBusDetailsDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}
	
	function updateDetails($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['date'] = date('Y-m-d', strtotime($request['date']));
		$request['modified_date'] = date('Y-m-d h:m:s');
		$request["payment_info"] = serialize($request["payment_info"]);
		$request["item_info"] = serialize($request["item_info"]);
		$request['image_name'] = hash('sha256', mt_rand() . microtime());

		$imageUpload = true;
		if ($request['imageUrl'] != "") {
			$image_array = [];
			$request['imageUrl'] = json_decode($request['imageUrl']);

			for ($i=0; $i < count($request['imageUrl']); $i++) {
				array_push($image_array, $request['image_name']."_".$i);
				$imageUpload = base64tofile($request['imageUrl'][$i], "../uploads/purchase/".$request['image_name']."_".$i.".png");
			}
			$request['image_name'] = serialize($image_array);
		}
		unset($request['imageUrl']);
		if ($imageUpload) {
			$update = update($request,'purchase_details',$condition,$conn);
			if($update)
			{
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details Updated Successfully",
					'data' => getPurchaseDB($conn),
					'busData' => getBusDetailsDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}else{
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to Update Details",
					'data' => getPurchaseDB($conn),
					'busData' => getBusDetailsDB($conn),
					'departmentData' => getDepartmentDB($conn)
				);
			}
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to process file",
				'data' => getWorkShopDB($conn),
				'serviceData' => getServiceDB($conn),
				'busData' => getBusDetailsDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	 
	}

	function deleteDetails($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('purchase_details',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details Deleted Successfully",
				'data' => getPurchaseDB($conn),
				'busData' => getBusDetailsDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to Delete Details",
				'data' => getPurchaseDB($conn),
				'busData' => getBusDetailsDB($conn),
				'departmentData' => getDepartmentDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}
	}
