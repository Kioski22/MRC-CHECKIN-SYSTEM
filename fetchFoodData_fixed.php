<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

// Check for session
if (!isset($_SESSION['access_token'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$request = $_POST;

// Prepare the base query to retrieve data
$columns = [
    0 => 'id',
    1 => 'email_address',
    2 => 'first_name',
    3 => 'middle_name',
    4 => 'last_name',
    5 => 'suffix',
    6 => 'mapping',
    7 => 'total_releases',
    8 => 'can_release_today'
];

// Pagination
$limit = isset($request['length']) && $request['length'] != -1 ? (int) $request['length'] : 10;
$start = isset($request['start']) ? (int) $request['start'] : 0;

// Ordering
$order_column_index = isset($request['order'][0]['column']) ? $request['order'][0]['column'] : 0;
$order = isset($columns[$order_column_index]) ? $columns[$order_column_index] : 'id';
$dir = isset($request['order'][0]['dir']) && $request['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

// Searching
$search = isset($request['search']['value']) ? $request['search']['value'] : '';

try {
    // Create query for retrieving filtered and paginated data using the food_release_status view
    $sql = "SELECT 
                frs.id,
                frs.email_address,
                frs.first_name,
                frs.middle_name,
                frs.last_name,
                frs.suffix,
                frs.mapping,
                frs.day1_released,
                frs.day2_released,
                frs.day3_released,
                frs.total_releases,
                frs.can_release_today
            FROM food_release_status frs";

    $whereClause = '';
    $params = [];
    $types = '';

    // Add search clause if needed
    if (!empty($search)) {
        $whereClause = " WHERE (
            frs.first_name LIKE ? OR
            frs.last_name LIKE ? OR
            frs.email_address LIKE ? OR
            frs.middle_name LIKE ? OR
            frs.suffix LIKE ? OR
            frs.mapping LIKE ?
        )";
        $like_search = "%$search%";
        $params = [$like_search, $like_search, $like_search, $like_search, $like_search, $like_search];
        $types = 'ssssss';
    }

    // Add ordering and pagination
    $sql .= $whereClause . " ORDER BY $order $dir LIMIT ?, ?";
    $params[] = $start;
    $params[] = $limit;
    $types .= 'ii';

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $db->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $data_result = $stmt->get_result();

    // Get total number of filtered records
    $totalFilteredQuery = "SELECT COUNT(*) as totalFiltered FROM food_release_status frs" . $whereClause;
    $totalFilteredStmt = $db->prepare($totalFilteredQuery);
    
    if (!empty($search)) {
        $searchParams = [$like_search, $like_search, $like_search, $like_search, $like_search, $like_search];
        $totalFilteredStmt->bind_param('ssssss', ...$searchParams);
    }
    
    $totalFilteredStmt->execute();
    $totalFilteredResult = $totalFilteredStmt->get_result();
    $totalFiltered = $totalFilteredResult->fetch_assoc()['totalFiltered'];

    // Fetch total records without filtering for total count
    $totalRecordsQuery = "SELECT COUNT(*) as total FROM food_release_status";
    $totalRecordsResult = $db->query($totalRecordsQuery);
    $totalRecords = $totalRecordsResult->fetch_assoc()['total'];

    // Prepare the data to send back to DataTables
    $data = [];
    while ($row = $data_result->fetch_assoc()) {
        $row['middle_name'] = $row['middle_name'] ?? 'N/A';
        $row['suffix'] = $row['suffix'] ?? 'N/A';
        $row['mapping'] = $row['mapping'] ?? 'N/A';
        
        // Format release status
        $row['day1_status'] = $row['day1_released'] ? 'Released' : 'Not Released';
        $row['day2_status'] = $row['day2_released'] ? 'Released' : 'Not Released';
        $row['day3_status'] = $row['day3_released'] ? 'Released' : 'Not Released';
        $row['release_summary'] = $row['total_releases'] . '/3 days';
        $row['can_release_today'] = boolval($row['can_release_today']);
        
        $data[] = $row;
    }

    // Return JSON response
    $response = [
        "draw" => isset($request['draw']) ? intval($request['draw']) : 1,
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    ];

    echo json_encode($response);

    $stmt->close();
    $totalFilteredStmt->close();

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'draw' => isset($request['draw']) ? intval($request['draw']) : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
}

$db->close();
?>