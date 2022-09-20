<?php
//this script will perform user signup logic

//SET HEADER
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../config/dbconn.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));

//CREATE MESSAGE ARRAY AND SET EMPTY
$msg['message'] = '';

try {
    // CHECK IF RECEIVED DATA FROM THE REQUEST
    if (isset($data->email) && isset($data->password)) {
        // CHECK DATA VALUE IS EMPTY OR NOT
        if (!empty($data->password) && !empty($data->email)) {

            $pass = md5($data->password);

            //check if user exists already using email

            $sql = "SELECT COUNT(*) AS num FROM `users` WHERE email = :mail";

            //Prepare the SQL statement.
            $stmt = $conn->prepare($sql);

            //Bind our values
            $stmt->bindValue(':mail', $data->email);

            //Execute the statement.
            $stmt->execute();

            //Fetch the row / result.
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //If num is bigger than 0, the account already exists.
            if ($row['num'] > 0) {
                $msg['message'] = 'Account already exist';
            } else {
                $insert_query = "INSERT INTO `users`(email,password) VALUES(?,?)";

                $insert_stmt = $conn->prepare($insert_query);
                // DATA BINDING

                $insert_stmt->bindParam(1, $data->email);
                $insert_stmt->bindParam(2, $pass);

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

//ECHO DATA IN JSON FORMAT
echo json_encode($msg);
