<?php

function hasPermission($permission_name)
{
    global $db;
    global $username;
    $role_id = getRoleID($username);
    $stmt = $db->prepare("SELECT COUNT(*) FROM role_permissions rp JOIN permissions p ON rp.permission_id = p.permission_id WHERE rp.role_id = ? AND p.permission_name = ?");
    $stmt->bind_param("is", $role_id, $permission_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();

    return $count > 0;
}

function hasRole($role_name)
{
    global $db;
    global $username;
    $stmt = $db->prepare("SELECT COUNT(*) FROM `users` u LEFT JOIN user_roles ur ON ur.user_id = u.user_id LEFT JOIN roles r ON ur.role_id = r.role_id WHERE u.username = ? AND r.role_name = ?");
    $stmt->bind_param("ss", $username, $role_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();

    return $count > 0;
}

function getRoleID($username)
{
    global $db;
    $stmt = $db->prepare("SELECT ur.role_id FROM `users` u LEFT JOIN user_roles ur ON u.user_id = ur.user_id WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($role_id);
    $stmt->fetch();
    return $role_id;
}
