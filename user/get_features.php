<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/dbconn.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

// GET DATA FORM REQUEST
// $data = json_decode(file_get_contents("php://input"));

try {

    // $rows = $data->row_num;
    // $page_no = $data->page_num;

    // $begin = ($rows * $page_no) - $rows;
    // $no = 'No';

    // $filter = $data->filter;

    // $sql = "SELECT * FROM `features` WHERE type = ?";

    $sql = "SELECT * FROM `features`";

    // Prepare the SQL statement.
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(1, $filter);

    // Execute the statement.
    $stmt->execute();

    //Fetch the row / result.
    $response_array = [];

    if ($stmt->rowCount() > 0) {
        //create feats array
        $features_array = [];
        array_push($response_array, array('response' => 'yes'));

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $feats_list = [
                'id' => $row['id'],
                'latitude' => $row['latitude'],
                'longtude' => $row['longtude'],
                'latlong' => $row['latlong'],
                'address' => $row['address'],
                'name' => $row['name'],
                'type' => $row['type'],
            ];
            //push list into array
            array_push($features_array, $feats_list);
        }
        array_push($response_array, array('data' => $features_array));
        echo json_encode($response_array);
    } else {
        array_push($response_array, array('response' => 'no'));
        echo json_encode($response_array);
    }

} catch (Exception $e) {
    $res = array();
    array_push($res, array('response' => 'error'));
    echo json_encode($res);
}

?>