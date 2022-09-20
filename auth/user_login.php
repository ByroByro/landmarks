<?php
// this script will perform user login logic

// SET HEADER
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
        if (!empty($data->email) && !empty($data->password)) {

            $pass = md5($data->password);

            //check if landlord exist
            $sql = "SELECT COUNT(*) AS num FROM `users` WHERE email = :email AND password = :pass";

            //Prepare the SQL statement.
            $stmt = $conn->prepare($sql);

            //Bind our values
            $stmt->bindValue(':email', $data->email);
            $stmt->bindValue(':pass', $pass);

            //Execute the statement.
            $stmt->execute();

            //Fetch the row / result.
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //If num is bigger than 0, the account already exists.
            if ($row['num'] > 0) {
                $msg['message'] = 'Login Successful';

                //retrieve id for this user
                $sql = "SELECT id,email FROM `users` WHERE email = :user AND password = :pass";

                //Prepare the SQL statement.
                $stmt = $conn->prepare($sql);

                //Bind our values
                $stmt->bindValue(':user', $data->email);
                $stmt->bindValue(':pass', $pass);

                //Execute the statement.
                $stmt->execute();

                $userid = '';
                //Fetch the row / result.
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($stmt->rowCount() > 0) {
                    $msg['id'] = $row['id'];
                    $msg['email'] = $row['email'];
                } else {
                    $msg['message'] = 'Details not found';
                }

            } else {
                $msg['message'] = 'Login Failed';
            }

        } else {
            $msg['message'] = 'Empty field(s) detected';
        }
    } else {
        $msg['message'] = 'Please fill all the fields';
    }
} catch (Exception $e) {
    //$msg['message'] = 'An error occurred'.$e;
    $msg['message'] = 'An error occurred ' . $e;
}

//ECHO DATA IN JSON FORMAT
echo json_encode($msg);
