<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/auth.php';

// Check authentication
requireAuthenticatedUser(true);

include 'dbConnection.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get DataTables parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$order_column_index = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$order_direction = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';

// Define columns for ordering
$columns = [
    'fr.release_time',      // 0 - Release Time
    'fr.release_date',      // 1 - Release Date  
    'f.email_address',      // 2 - Email
    'f.first_name',         // 3 - First Name
    'f.last_name',          // 4 - Last Name
    'u.username',           // 5 - Released By
    'fr.convention_day'     // 6 - Convention Day
];

$order_column = isset($columns[$order_column_index]) ? $columns[$order_column_index] : $columns[0];

try {
    // Base query using the food_releases_view
    $base_query = "
        FROM food_releases_view fr
        WHERE 1=1
    ";
    
    // Add search functionality
    $search_query = '';
    $search_params = [];
    if (!empty($search_value)) {
        $search_query = " AND (
            fr.email_address LIKE ? OR 
            fr.first_name LIKE ? OR 
            fr.last_name LIKE ? OR 
            fr.released_by_username LIKE ? OR
            fr.convention_day LIKE ? OR
            DATE_FORMAT(fr.release_date, '%Y-%m-%d') LIKE ? OR
            DATE_FORMAT(fr.release_time, '%Y-%m-%d %H:%i:%s') LIKE ?
        )";
        $search_term = "%{$search_value}%";
        $search_params = array_fill(0, 7, $search_term);
    }
    
    // Get total count without filtering
    $total_query = "SELECT COUNT(*) as total " . $base_query;
    $stmt = $pdo->prepare($total_query);
    $stmt->execute();
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get filtered count
    $filtered_query = "SELECT COUNT(*) as total " . $base_query . $search_query;
    $stmt = $pdo->prepare($filtered_query);
    $stmt->execute($search_params);
    $filtered_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get the actual data
    $data_query = "
        SELECT 
            fr.release_id,
            fr.food_id,
            fr.email_address,
            fr.first_name,
            fr.middle_name,
            fr.last_name,
            fr.suffix,
            fr.mapping,
            fr.release_date,
            fr.release_time,
            fr.released_by,
            fr.released_by_username,
            fr.convention_day,
            fr.notes,
            CONCAT(fr.first_name, ' ', IFNULL(fr.middle_name, ''), ' ', fr.last_name, IFNULL(CONCAT(' ', fr.suffix), '')) as full_name
        " . $base_query . $search_query . "
        ORDER BY {$order_column} {$order_direction}
        LIMIT {$start}, {$length}
    ";
    
    $stmt = $pdo->prepare($data_query);
    $stmt->execute($search_params);
    $release_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($release_data as $row) {
        $full_name = trim($row['first_name'] . ' ' . 
                         ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . 
                         $row['last_name'] . 
                         ($row['suffix'] ? ' ' . $row['suffix'] : ''));
        
        $release_time_formatted = date('M j, Y g:i A', strtotime($row['release_time']));
        $release_date_formatted = date('M j, Y', strtotime($row['release_date']));
        
        $data[] = [
            $release_time_formatted,          // Release Time
            $release_date_formatted,          // Release Date
            $row['email_address'],            // Email
            $full_name,                       // Full Name
            $row['released_by_username'],     // Released By
            $row['convention_day'],           // Convention Day
            $row['notes'] ?: 'No notes'       // Notes
        ];
    }
    
    // Return DataTables response
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => intval($total_records),
        'recordsFiltered' => intval($filtered_records),
        'data' => $data
    ]);
    
} catch (PDOException $e) {
    error_log("Get food releases data error: " . $e->getMessage());
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Get food releases data error: " . $e->getMessage());
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'An error occurred while fetching data'
    ]);
}
?>