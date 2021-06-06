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

			case 'getCollectionDetails':
				getCollectionDetails($_REQUEST,$con);
				break;

			case 'getCollectionDetailCondition':
				getCollectionDetailCondition($_REQUEST,$con);
				break;

			case 'addCollectionDetails':
				addCollectionDetails($_REQUEST,$con);
				break;

			case 'updateCollectionDetails':
				updateCollectionDetails($_REQUEST,$con);
				break;

			case 'deleteCollectionDetails':
				deleteCollectionDetails($_REQUEST,$con);
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
		$data = select('`bus_no`, `auto_inc_id`, `no_trips`','bus_details','`type` = 0',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?[]: $data;
	}

	function getSpecialDaysDB($conn){
		$data = select('*','special_days','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function getRouteDB($conn){
		$data = select('*','route_details','1',$conn);
		return $data == "empty"?[]: $data;
	}

	function getCollectionDetailsDB($conn){
		$data = select('*','collection_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				$data[$key]['view_date'] = date("d-m-Y", strtotime($value['entry_date']));
				$data[$key]["trip_data"] = unserialize($value["trip_data"]);
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


	function getCollectionDetailCondition($request,$conn){
		$condition = '`entry_date`="'.$request['date'].'" AND `bus_details`="'.$request['bus_details'].'"';
		$data = select('collection_amount','collection_details',$condition,$conn);

		echo json_encode($data == "empty"?[]: $data);
	}
	function getCollectionDetails($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getCollectionDetailsDB($conn),
			'driverData' => getEmployeeDB($conn,4),
			'conductorData' => getEmployeeDB($conn,5),
			'busData' => getBusDetailsDB($conn),
			'routeData' => getRouteDB($conn),
			'daysData' => getSpecialDaysDB($conn)
		);
		echo json_encode($response);
	}


	function addCollectionDetails($request,$conn){
		$request['entry_date'] = date('Y-m-d', strtotime($request['entry_date']));
		$request['created_date'] = date('Y-m-d');
		$request['modified_date'] = date('Y-m-d');
		$request["trip_data"] = serialize($request["trip_data"]);
		
		if(insert('collection_details',$request,$conn)){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details added Successfully",
				'data' => getCollectionDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'busData' => getBusDetailsDB($conn),
				'routeData' => getRouteDB($conn),
				'daysData' => getSpecialDaysDB($conn)
			);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to add Details",
				'data' => getCollectionDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'busData' => getBusDetailsDB($conn),
				'routeData' => getRouteDB($conn),
				'daysData' => getSpecialDaysDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}
	function updateCollectionDetails($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['entry_date'] = date('Y-m-d', strtotime($request['entry_date']));
		$request['modified_date'] = date('Y-m-d');
		$request["trip_data"] = serialize($request["trip_data"]);

		$update = update($request,'collection_details',$condition,$conn);
		if($update){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details updated Successfully",
				'data' => getCollectionDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'busData' => getBusDetailsDB($conn),
				'routeData' => getRouteDB($conn),
				'daysData' => getSpecialDaysDB($conn)
			);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to update details",
				'data' => getCollectionDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'busData' => getBusDetailsDB($conn),
				'routeData' => getRouteDB($conn),
				'daysData' => getSpecialDaysDB($conn)
			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}

	function deleteCollectionDetails($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('collection_details',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Collection details deleted Successfully",
				'data' => getCollectionDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'busData' => getBusDetailsDB($conn),
				'routeData' => getRouteDB($conn),
				'daysData' => getSpecialDaysDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete Collection Details",
				'data' => getCollectionDetailsDB($conn),
				'driverData' => getEmployeeDB($conn,4),
				'conductorData' => getEmployeeDB($conn,5),
				'busData' => getBusDetailsDB($conn),
				'routeData' => getRouteDB($conn),
				'daysData' => getSpecialDaysDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}		
	}

