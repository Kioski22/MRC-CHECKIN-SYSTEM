<?php

if (!defined('ACTIVE_EVENT_ID_DEFAULT')) {
    define('ACTIVE_EVENT_ID_DEFAULT', 65);
}

function ensureSystemSettingsTable($db)
{
    if (!$db) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(100) NOT NULL,
        setting_value VARCHAR(255) NOT NULL,
        updated_by INT(11) NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    return (bool) $db->query($sql);
}

function getActiveEventId($db)
{
    if (!ensureSystemSettingsTable($db)) {
        return ACTIVE_EVENT_ID_DEFAULT;
    }

    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'active_event_id' LIMIT 1");
    if (!$stmt) {
        return ACTIVE_EVENT_ID_DEFAULT;
    }

    $stmt->execute();
    $stmt->bind_result($value);
    $hasRow = $stmt->fetch();
    $stmt->close();

    if (!$hasRow) {
        seedDefaultActiveEventId($db);
        return ACTIVE_EVENT_ID_DEFAULT;
    }

    $eventId = intval($value);
    if ($eventId <= 0) {
        return ACTIVE_EVENT_ID_DEFAULT;
    }

    return $eventId;
}

function seedDefaultActiveEventId($db)
{
    $default = strval(ACTIVE_EVENT_ID_DEFAULT);
    $stmt = $db->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES ('active_event_id', ?)");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $default);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function setActiveEventId($db, $eventId, $updatedBy = null)
{
    $eventId = intval($eventId);
    if ($eventId <= 0) {
        return false;
    }

    if (!ensureSystemSettingsTable($db)) {
        return false;
    }

    $eventValue = strval($eventId);

    if ($updatedBy === null) {
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('active_event_id', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = NULL, updated_at = CURRENT_TIMESTAMP");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $eventValue);
    } else {
        $updatedBy = intval($updatedBy);
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_by) VALUES ('active_event_id', ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = CURRENT_TIMESTAMP");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $eventValue, $updatedBy);
    }

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function getActiveEventConfig($db)
{
    $eventId = getActiveEventId($db);

    $config = [
        'event_id' => $eventId,
        'updated_at' => null,
        'updated_by_username' => null,
    ];

    $query = "SELECT s.setting_value, s.updated_at, u.username
              FROM system_settings s
              LEFT JOIN users u ON u.user_id = s.updated_by
              WHERE s.setting_key = 'active_event_id'
              LIMIT 1";

    $result = $db->query($query);
    if (!$result) {
        return $config;
    }

    $row = $result->fetch_assoc();
    if (!$row) {
        return $config;
    }

    $config['event_id'] = intval($row['setting_value']) > 0 ? intval($row['setting_value']) : $eventId;
    $config['updated_at'] = $row['updated_at'] ?? null;
    $config['updated_by_username'] = $row['username'] ?? null;

    return $config;
}

?>