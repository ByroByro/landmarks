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
    if (isset($data->id) && isset($data->email) && isset($data->mode) && isset($data->value)) {
        // check data value for empty
        if (!empty($data->id) && !empty($data->email)) {

            //check if there is already a record for this user

            $sql = "SELECT COUNT(*) AS num FROM `settings` WHERE user_email = :mail AND user_id = :id";

            //Prepare the SQL statement.
            $stmt = $conn->prepare($sql);

            //Bind our values
            $stmt->bindValue(':mail', $data->email);
            $stmt->bindValue(':id', $data->id);

            //Execute the statement.
            $stmt->execute();

            //Fetch the row / result.
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // If num is bigger than 0, a record already exists.
            if ($row['num'] > 0) {
                 // update value
                $update_query = '';

                if($data->mode == "landmark"){
                    $update_query = "UPDATE `settings` SET prefered_mark = ? WHERE user_id = ? AND user_email = ?";
                } else {
                    $update_query = "UPDATE `settings` SET metric = ? WHERE user_id = ? AND user_email = ?";
                }

                $update_stmt = $conn->prepare($update_query);
                
                // data binding
                $update_stmt->bindParam(1, $data->value);
                $update_stmt->bindParam(2, $data->id);
                $update_stmt->bindParam(3, $data->email);

                if ($update_stmt->execute()) {
                    $msg['message'] = 'Data Inserted Successfully';

                } else {
                    $msg['message'] = 'Data not Inserted';
                }
            } else {
                // insert value

                $insert_query = '';

                if($data->mode == "landmark"){
                    $insert_query = "INSERT INTO `settings`(user_id,user_email,prefered_mark) VALUES(?,?,?)";
                } else {
                    $insert_query = "INSERT INTO `settings`(user_id,user_email,metric) VALUES(?,?,?)";
                }

                $insert_stmt = $conn->prepare($insert_query);
                
                // data binding
                $insert_stmt->bindParam(1, $data->id);
                $insert_stmt->bindParam(2, $data->email);
                $insert_stmt->bindParam(3, $data->value);

                if ($insert_stmt->execute()) {
                    $msg['message'] = 'Data Inserted Successfully';

                } else {
                    $msg['message'] = 'Data not Inserted';
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