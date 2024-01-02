<?php
$host = 'localhost';
$username = 'root';
$password = 'mysql';
$database = 'rest_api_demo';
header("Access-Control-Allow-Origin: *");
// if( $_POST ){
//     $requestBody = json_decode(file_get_contents('php://input'), false);
//     var_dump( $requestBody ); die;
// }

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
// var_dump( $uri, $method );die;
switch (true) {
    case ($method == 'GET' && $uri == '/kens_api/users'):
        header('Content-Type: application/json');
        $result = $conn->query("SELECT * FROM users");
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[$row['user_id']] = $row;
        }
        echo json_encode($users, JSON_PRETTY_PRINT);    
        break;

    case ($method == 'GET' && preg_match('/\/kens_api\/users\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        $result = $conn->query("SELECT * FROM users WHERE user_id = $id");
        if ($result->num_rows == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'user does not exist']);
            break;
        }
        $responseData = $result->fetch_assoc();
        echo json_encode($responseData, JSON_PRETTY_PRINT);
        break;

    case ($method == 'POST' && $uri == '/kens_api/users'):
        header('Content-Type: application/json');
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $name = $requestBody['username'];
        $email = $requestBody['user_email'];
        $status = $requestBody['user_status'];
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Please add name of the user']);
            break;
        }
        $conn->query("INSERT INTO users (username, user_email, user_status ) VALUES ('$name', '$email', '$status')");
        echo json_encode(['message' => 'user added successfully']);
        break;

    case ($method == 'PUT' && preg_match('/\/kens_api\/users\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        $result = $conn->query("SELECT * FROM users WHERE user_id = {$id}");
        if ($result->num_rows == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'user does not exist']);
            break;
        }
        $requestBody = json_decode(file_get_contents('php://input'), true);
        $name = $requestBody['username'];
        $email = $requestBody['user_email'];
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Please add name of the user']);
            break;
        }
        $conn->query("UPDATE users SET username = '$name', user_email = '$email' WHERE user_id = $id");
        echo json_encode(['message' => 'user updated successfully']);
        break;

    case ($method == 'DELETE' && preg_match('/\/kens_api\/users\/[1-9]/', $uri)):
        header('Content-Type: application/json');
        $id = basename($uri);
        $result = $conn->query("SELECT * FROM users WHERE id = $id");
        if ($result->num_rows == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'user does not exist']);
            break;
        }
        $conn->query("DELETE FROM users WHERE id = $id");
        echo json_encode(['message' => 'user deleted successfully']);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => "We cannot find what you're looking for."]);
        break;
}

$conn->close();
?>