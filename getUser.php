<?php
include 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $stmt = $db->prepare("SELECT u.user_id, u.username, u.email, ur.role_id FROM users u JOIN user_roles ur ON u.user_id = ur.user_id WHERE u.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode($user);
    } else {
        echo json_encode(["error" => "User not found"]);
    }

    $stmt->close();
}

$db->close();
?>