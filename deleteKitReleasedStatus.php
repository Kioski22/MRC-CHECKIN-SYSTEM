<?php
// deleteKitReleasedStatus.php

include 'dbConnection.php';  // Add your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin_id'])) {
    $checkin_id = $_POST['checkin_id'];

    // Check if kit release time is already NULL (kit not released)
    $checkStmt = $db->prepare("SELECT kit_release_time FROM checkins WHERE checkin_id = ?");
    $checkStmt->bind_param('i', $checkin_id);
    $checkStmt->execute();
    $checkStmt->bind_result($kit_release_time);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($kit_release_time === NULL) {
        // Kit has not been released, no need to delete
        echo json_encode([
            'success' => false,
            'message' => 'Kit has not been released yet.'
        ]);
    } else {
        // Proceed to delete (set kit_release_time to NULL)
        $stmt = $db->prepare("UPDATE checkins SET kit_release_time = NULL, releasedkit_by = NULL WHERE checkin_id = ?");
        $stmt->bind_param('i', $checkin_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Kit release status deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete kit release status. Please try again.'
            ]);
        }

        $stmt->close();
    }

    $db->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Check-in ID not provided.'
    ]);
}
