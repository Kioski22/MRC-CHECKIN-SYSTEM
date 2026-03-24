<?php
require('dbConnection.php');

$region = $_GET['region'] ?? '';

if (!empty($region)) {
    // Prepare the SQL statement to fetch chapters for the selected region
    $stmt = $db->prepare("SELECT chapter FROM chapters WHERE region = ?");
    $stmt->bind_param("s", $region);
    $stmt->execute();
    $result = $stmt->get_result();

    $chapters = [];
    while ($row = $result->fetch_assoc()) {
        $chapters[] = $row['chapter'];
    }

    // Return the chapters as a JSON response
    header('Content-Type: application/json');
    echo json_encode($chapters);

    $stmt->close();
}

$db->close();
