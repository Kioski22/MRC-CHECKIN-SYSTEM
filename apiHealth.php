<?php

if (!defined('API_HEALTH_EVENT_LIMIT')) {
    define('API_HEALTH_EVENT_LIMIT', 25);
}

function apiHealthEnsureTables($db)
{
    if (!$db) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS api_health_events (
        id INT(11) NOT NULL AUTO_INCREMENT,
        service_name VARCHAR(50) NOT NULL,
        endpoint_name VARCHAR(100) NOT NULL,
        health_state VARCHAR(20) NOT NULL,
        failure_type VARCHAR(50) NOT NULL,
        status_source VARCHAR(20) NOT NULL DEFAULT 'upstream',
        http_code INT(11) NULL,
        duration_ms INT(11) NULL,
        message VARCHAR(255) NOT NULL,
        context_json TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_service_created (service_name, created_at),
        INDEX idx_endpoint_created (endpoint_name, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    return (bool) $db->query($sql);
}

function apiHealthStateFromFailureType($failureType)
{
    switch ($failureType) {
        case 'ok':
        case 'no_data':
            return 'healthy';
        case 'auth_failure':
        case 'local_app_error':
            return 'degraded';
        case 'timeout':
        case 'network_failure':
        case 'invalid_response':
        case 'upstream_http_error':
            return 'down';
        default:
            return 'unknown';
    }
}

function apiHealthDefaultUserMessage($failureType)
{
    switch ($failureType) {
        case 'ok':
            return 'External Smartpay API is responding normally.';
        case 'no_data':
            return 'External Smartpay API responded normally, but no matching data was found.';
        case 'auth_failure':
            return 'External Smartpay API authentication failed. This hosted app is still online.';
        case 'timeout':
            return 'External Smartpay API timed out. This hosted app is still online.';
        case 'network_failure':
            return 'External Smartpay API could not be reached. This hosted app is still online.';
        case 'invalid_response':
            return 'External Smartpay API returned an invalid response. This hosted app is still online.';
        case 'upstream_http_error':
            return 'External Smartpay API returned an HTTP error. This hosted app is still online.';
        case 'local_app_error':
            return 'The local application encountered an internal error.';
        default:
            return 'API health is currently unknown.';
    }
}

function apiHealthInferFailureType($httpCode = null, $curlError = '', $responseBody = null)
{
    $curlError = strtolower(trim((string) $curlError));

    if ($curlError !== '') {
        if (strpos($curlError, 'timed out') !== false || strpos($curlError, 'timeout') !== false) {
            return 'timeout';
        }

        return 'network_failure';
    }

    if ($httpCode === 401 || $httpCode === 403) {
        return 'auth_failure';
    }

    if (is_int($httpCode) && $httpCode > 0 && $httpCode !== 200) {
        return 'upstream_http_error';
    }

    if ($responseBody !== null) {
        $decoded = json_decode($responseBody, true);
        if ($responseBody !== '' && json_last_error() !== JSON_ERROR_NONE) {
            return 'invalid_response';
        }

        if (is_array($decoded) && isset($decoded['resultKey']) && intval($decoded['resultKey']) !== 1) {
            return 'no_data';
        }
    }

    return 'unknown';
}

function apiHealthBuildMeta($serviceName, $endpointName, $failureType, $statusSource = 'upstream', $httpCode = null, $durationMs = null, $userMessage = null)
{
    return [
        'service' => $serviceName,
        'endpoint' => $endpointName,
        'state' => apiHealthStateFromFailureType($failureType),
        'failure_type' => $failureType,
        'status_source' => $statusSource,
        'http_code' => $httpCode,
        'duration_ms' => $durationMs,
        'message' => $userMessage !== null ? $userMessage : apiHealthDefaultUserMessage($failureType),
    ];
}

function apiHealthRecordEvent($db, $serviceName, $endpointName, $failureType, $statusSource, $message, $httpCode = null, $durationMs = null, $context = null)
{
    if (!apiHealthEnsureTables($db)) {
        return false;
    }

    $healthState = apiHealthStateFromFailureType($failureType);
    $message = substr((string) $message, 0, 255);
    $contextJson = null;

    if ($context !== null) {
        $contextJson = json_encode($context);
        if ($contextJson === false) {
            $contextJson = null;
        }
    }

    $stmt = $db->prepare("INSERT INTO api_health_events (service_name, endpoint_name, health_state, failure_type, status_source, http_code, duration_ms, message, context_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "sssssiiis",
        $serviceName,
        $endpointName,
        $healthState,
        $failureType,
        $statusSource,
        $httpCode,
        $durationMs,
        $message,
        $contextJson
    );

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function apiHealthGetRecentEvents($db, $serviceName = 'smartpay', $limit = API_HEALTH_EVENT_LIMIT)
{
    if (!apiHealthEnsureTables($db)) {
        return [];
    }

    $limit = max(1, intval($limit));
    $rows = [];
    $query = "SELECT id, service_name, endpoint_name, health_state, failure_type, status_source, http_code, duration_ms, message, created_at FROM api_health_events WHERE service_name = ? ORDER BY created_at DESC, id DESC LIMIT ?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("si", $serviceName, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    return $rows;
}

function apiHealthGetServiceSummary($db, $serviceName = 'smartpay')
{
    $events = apiHealthGetRecentEvents($db, $serviceName, 10);

    $summary = [
        'service' => $serviceName,
        'current_state' => 'unknown',
        'last_event_at' => null,
        'last_success_at' => null,
        'last_failure_at' => null,
        'last_endpoint' => null,
        'last_failure_type' => null,
        'last_message' => 'No Smartpay activity has been recorded yet.',
        'last_http_code' => null,
        'last_duration_ms' => null,
        'failure_streak' => 0,
    ];

    if (empty($events)) {
        return $summary;
    }

    $latest = $events[0];
    $summary['current_state'] = $latest['health_state'];
    $summary['last_event_at'] = $latest['created_at'];
    $summary['last_endpoint'] = $latest['endpoint_name'];
    $summary['last_failure_type'] = $latest['failure_type'];
    $summary['last_message'] = $latest['message'];
    $summary['last_http_code'] = $latest['http_code'];
    $summary['last_duration_ms'] = $latest['duration_ms'];

    foreach ($events as $event) {
        if ($summary['last_success_at'] === null && in_array($event['failure_type'], ['ok', 'no_data'], true)) {
            $summary['last_success_at'] = $event['created_at'];
        }

        if ($summary['last_failure_at'] === null && !in_array($event['failure_type'], ['ok', 'no_data'], true)) {
            $summary['last_failure_at'] = $event['created_at'];
        }
    }

    foreach ($events as $event) {
        if (in_array($event['failure_type'], ['ok', 'no_data'], true)) {
            break;
        }

        $summary['failure_streak']++;
    }

    return $summary;
}

function apiHealthBadgeClass($state)
{
    switch ($state) {
        case 'healthy':
            return 'success';
        case 'degraded':
            return 'warning';
        case 'down':
            return 'danger';
        default:
            return 'secondary';
    }
}

?>