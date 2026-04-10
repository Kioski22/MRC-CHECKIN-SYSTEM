<?php

function isAuthenticatedUser()
{
    return isset($_SESSION['user_id'], $_SESSION['username']);
}

function requireAuthenticatedUser($asJson = false)
{
    if (isAuthenticatedUser()) {
        return true;
    }

    if ($asJson) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
    } else {
        header('Location: login.php');
    }

    exit();
}
