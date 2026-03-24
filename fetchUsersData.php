<?php
session_start();
include 'dbConnection.php';

// Check user authentication
if (!isset($_SESSION['access_token'])) {
    echo json_encode(['data' => [], 'error' => 'User not authenticated']);
    exit();
}

// Set the timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Get parameters from the DataTables request
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

// Map column index to database column name
$columns = ['u.user_id', 'u.username', 'u.email', 'u.status', 'u.created_at', 'r.role_name'];
$orderBy = $columns[$orderColumn];

// Construct the base query
$query = "SELECT u.user_id, u.username, u.email, u.status, u.created_at, r.role_name 
          FROM users u 
          JOIN user_roles ur ON u.user_id = ur.user_id 
          JOIN roles r ON ur.role_id = r.role_id 
          WHERE 1 ";

// Add search functionality
if (!empty($searchValue)) {
    $query .= " AND (u.username LIKE '%$searchValue%' OR u.email LIKE '%$searchValue%' OR r.role_name LIKE '%$searchValue%')";
}

// Get the total number of records without any filtering
$totalQuery = "SELECT COUNT(*) as total FROM users";
$totalResult = $db->query($totalQuery);
$totalData = $totalResult->fetch_assoc()['total'];

// Apply sorting, limit, and offset for pagination
$query .= " ORDER BY $orderBy $orderDir LIMIT $start, $length";
$result = $db->query($query);

// Prepare the data to be returned to DataTables
$data = [];
while ($row = $result->fetch_assoc()) {
    // Convert created_at to Asia/Manila (UTC+8)
    $createdAt = new DateTime($row['created_at'], new DateTimeZone('UTC'));  // Assume stored as UTC
    //$createdAt->setTimezone(new DateTimeZone('Asia/Manila'));  // Convert to Manila timezone
    $row['created_at'] = $createdAt->format('F j, Y h:i A');  // Format the date

    // Add Actions column with a Delete button
    $row['actions'] = '
    <button class="btn btn-danger btn-sm delete-user-btn" data-id="' . $row['user_id'] . '">Delete</button>
    <button class="btn btn-warning btn-sm reset-password-btn" data-id="' . $row['user_id'] . '">Reset Password</button>';

    $data[] = $row;
}

// Get the filtered count
$filteredQuery = "SELECT COUNT(*) as total FROM users u 
                  JOIN user_roles ur ON u.user_id = ur.user_id 
                  JOIN roles r ON ur.role_id = r.role_id 
                  WHERE 1 ";

if (!empty($searchValue)) {
    $filteredQuery .= " AND (u.username LIKE '%$searchValue%' OR u.email LIKE '%$searchValue%' OR r.role_name LIKE '%$searchValue%')";
}
$filteredResult = $db->query($filteredQuery);
$filteredTotal = $filteredResult->fetch_assoc()['total'];

// Prepare the final output for DataTables
$response = [
    'draw' => $draw,
    'recordsTotal' => $totalData,
    'recordsFiltered' => $filteredTotal,
    'data' => $data
];

echo json_encode($response);
