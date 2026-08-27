<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);

    exit;
}

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";
$role = strtolower(trim($data["role"] ?? "customer"));

$allowedRoles = [
    "customer",
    "vendor",
    "rider"
];

if (
    empty($full_name) ||
    empty($email) ||
    empty($password)
) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Full name, email and password are required"
    ]);

    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email address"
    ]);

    exit;
}

if (!in_array($role, $allowedRoles, true)) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Invalid role"
    ]);

    exit;
}

if (strlen($password) < 8) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 8 characters"
    ]);

    exit;
}

$checkUser = $pdo->prepare(
    "SELECT id FROM users WHERE email = ?"
);

$checkUser->execute([$email]);

if ($checkUser->fetch()) {
    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "Email already exists"
    ]);

    exit;
}

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$stmt = $pdo->prepare(
    "INSERT INTO users
    (full_name, email, password, role, status)
    VALUES (?, ?, ?, ?, 'active')"
);

$stmt->execute([
    $full_name,
    $email,
    $hashedPassword,
    $role
]);

$userId = $pdo->lastInsertId();

http_response_code(201);

echo json_encode([
    "success" => true,
    "message" => "Registration successful",
    "user" => [
        "id" => (int) $userId,
        "full_name" => $full_name,
        "email" => $email,
        "role" => $role,
        "status" => "active"
    ]
]);
