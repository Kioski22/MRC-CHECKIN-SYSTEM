<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

$request = $_POST;

// Prepare the base query to retrieve data
$columns = [
    0 => 'checkin_id',
    1 => 'firstname',
    2 => 'lastname',
    3 => 'middlename',
    4 => 'email',
    5 => 'prclicensenumber',
    6 => 'chapter',
    7 => 'qrcode',
    8 => 'scanned_time',
    9 => 'checkin_time',
    10 => 'kit_release_time',
    11 => 'releasedprogram_time',
    12 => 'scanned_by_username',
    13 => 'checkin_by_username',
    14 => 'released_by_username',
    15 => 'releasedprogram_by_username'
];

// Pagination
$limit = isset($request['length']) && $request['length'] != -1 ? (int) $request['length'] : 10;
$start = isset($request['start']) ? (int) $request['start'] : 0;

// Ordering
$order_column_index = $request['order'][0]['column'];
$order = isset($columns[$order_column_index]) ? $columns[$order_column_index] : 'checkin_id';
$dir = isset($request['order'][0]['dir']) && $request['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

// Searching
$search = isset($request['search']['value']) ? $request['search']['value'] : '';

// Create query for retrieving filtered and paginated data
$sql = "SELECT 
            c.checkin_id,
            c.firstname,
            c.lastname,
            c.middlename,
            c.email,
            c.prclicensenumber,
            c.chapter,
            c.qrcode,
            c.scanned_time,
            c.checkin_time,
            c.kit_release_time,
            c.releasedprogram_time,
            u1.username AS scanned_by_username,
            u2.username AS checkin_by_username,
            u3.username AS released_by_username,
            u4.username AS releasedprogram_by_username
        FROM checkins c
        LEFT JOIN users u1 ON c.scanned_by = u1.user_id
        LEFT JOIN users u2 ON c.checkin_by = u2.user_id
        LEFT JOIN users u3 ON c.releasedkit_by = u3.user_id
        LEFT JOIN users u4 ON c.releasedprogram_by = u4.user_id";

// Searching
if (!empty($search)) {
    $sql .= " WHERE (
        c.firstname LIKE ? OR
        c.lastname LIKE ? OR
        c.email LIKE ? OR
        c.prclicensenumber LIKE ? OR
        c.chapter LIKE ?
    )";
}

// Ordering and pagination
$sql .= " ORDER BY $order $dir LIMIT ?, ?";

$stmt = $db->prepare($sql);

// Binding parameters
if (!empty($search)) {
    $like_search = "%$search%";
    $stmt->bind_param('ssssssi', $like_search, $like_search, $like_search, $like_search, $like_search, $start, $limit);
} else {
    $stmt->bind_param('ii', $start, $limit);
}

$stmt->execute();
$data_result = $stmt->get_result();

// Get total number of filtered records
$totalFilteredQuery = "SELECT COUNT(*) as totalFiltered FROM checkins c
    LEFT JOIN users u1 ON c.scanned_by = u1.user_id
    LEFT JOIN users u2 ON c.checkin_by = u2.user_id
    LEFT JOIN users u3 ON c.releasedkit_by = u3.user_id";
if (!empty($search)) {
    $totalFilteredQuery .= " WHERE (
        c.firstname LIKE ? OR
        c.lastname LIKE ? OR
        c.email LIKE ? OR
        c.prclicensenumber LIKE ? OR
        c.chapter LIKE ?
    )";
}
$totalFilteredStmt = $db->prepare($totalFilteredQuery);
if (!empty($search)) {
    $totalFilteredStmt->bind_param('sssss', $like_search, $like_search, $like_search, $like_search, $like_search);
}
$totalFilteredStmt->execute();
$totalFilteredResult = $totalFilteredStmt->get_result();
$totalFiltered = $totalFilteredResult->fetch_assoc()['totalFiltered'];

// Fetch total records without filtering for total count
$totalRecordsQuery = "SELECT COUNT(*) as total FROM checkins";
$totalRecordsResult = $db->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Prepare the data to send back to DataTables
$data = [];
while ($row = $data_result->fetch_assoc()) {
    $row['scanned_time'] = $row['scanned_time'] ? date('F j, Y h:i A', strtotime($row['scanned_time'])) : 'N/A';
    $row['checkin_time'] = $row['checkin_time'] ? date('F j, Y h:i A', strtotime($row['checkin_time'])) : 'N/A';
    $row['kit_release_time'] = $row['kit_release_time'] ? date('F j, Y h:i A', strtotime($row['kit_release_time'])) : 'N/A';
    $row['releasedprogram_time'] = $row['releasedprogram_time'] ? date('F j, Y h:i A', strtotime($row['releasedprogram_time'])) : 'N/A';
    $row['middlename'] = $row['middlename'] ?? 'N/A';
    $row['chapter'] = $row['chapter'] ?? 'N/A';
    $row['scanned_by_username'] = $row['scanned_by_username'] ?? 'N/A';
    $row['checkin_by_username'] = $row['checkin_by_username'] ?? 'N/A';
    $row['releasedprogram_by_username'] = $row['releasedprogram_by_username'] ?? 'N/A';
    $data[] = $row;
}

// Return JSON response
$response = [
    "draw" => intval($request['draw']),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
];

echo json_encode($response);

$stmt->close();
$totalFilteredStmt->close();
$db->close();
