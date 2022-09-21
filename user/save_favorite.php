<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/dbconn.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

// get data from request
$data = json_decode(file_get_contents("php://input"));

// create message array
$msg['message'] = '';

try {
    // check if all data has been received
    if (isset($data->user_id) && isset($data->location_id)) {
        // check data value for empty
        if (!empty($data->user_id) && !empty($data->location_id)) {

            //check if there is already a record

            $sql = "SELECT COUNT(*) AS num FROM `favorites` WHERE user_id = :uid AND location_id = :lid";

            //Prepare the SQL statement.
            $stmt = $conn->prepare($sql);

            //Bind our values
            $stmt->bindValue(':uid', $data->user_id);
            $stmt->bindValue(':lid', $data->location_id);

            //Execute the statement.
            $stmt->execute();

            //Fetch the row / result.
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // If num is bigger than 0, a record already exists.
            if ($row['num'] > 0) {
                $msg['message'] = 'Exists';
            } else {
                // insert value
                
                $insert_query = "INSERT INTO `favorites`(user_id,location_id) VALUES(?,?)";

                $insert_stmt = $conn->prepare($insert_query);
                
                // data binding
                $insert_stmt->bindParam(1, $data->user_id);
                $insert_stmt->bindParam(2, $data->location_id);

                if ($insert_stmt->execute()) {
                    $msg['message'] = 'Success';

                } else {
                    $msg['message'] = 'Failed';
                }
            }

        } else {
            $msg['message'] = 'Empty field detected';
        }
    } else {
        $msg['message'] = 'Please fill all the fields';
    }
} catch (Exception $e) {
    $msg['message'] = 'An error occurred' . $e;
}

// echo response in json
echo json_encode($msg);

?>