<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Path to your CSV file
$csvFilePath = 'include/regionstochapters.csv';

// Check if the file exists and is readable
if (!file_exists($csvFilePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found at ' . $csvFilePath]);
    exit;
}

// Initialize an empty array to store region and chapter data
$regionToChapters = [];

// Open the CSV file and handle potential file errors
if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
    // Get the headers (regions) from the first row
    $headers = fgetcsv($handle);

    if (!$headers) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not read headers from the file.']);
        fclose($handle);
        exit;
    }

    // Loop through each row of the CSV file
    while (($data = fgetcsv($handle)) !== FALSE) {
        // For each region (header), map the corresponding chapter (row data)
        foreach ($headers as $index => $region) {
            $chapter = $data[$index];  // Get the chapter under the region

            // Ensure the region and chapter are not empty
            if (!empty(trim($chapter)) && !empty(trim($region))) {
                // Normalize region to match HTML option values (e.g., lowercase)
                $regionKey = strtolower(trim($region));

                // Initialize array if region key doesn't exist
                if (!isset($regionToChapters[$regionKey])) {
                    $regionToChapters[$regionKey] = [];
                }

                // Add the chapter to the region's list, trim extra whitespace
                $regionToChapters[$regionKey][] = trim($chapter);
            }
        }
    }

    // Close the file after reading
    fclose($handle);

    // Return the data as a JSON object to be used by JavaScript
    header('Content-Type: application/json');
    echo json_encode($regionToChapters);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Could not open the file.']);
}
