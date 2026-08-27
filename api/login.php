<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/jwt.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

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

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (empty($email) || empty($password)) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);

    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, full_name, email, password, role, status
     FROM users
     WHERE email = ?"
);

$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user["password"])) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);

    exit;
}

if ($user["status"] !== "active") {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "This account is not active"
    ]);

    exit;
}

$issuedAt = time();
$expirationTime = $issuedAt + (60 * 60 * 24);

$payload = [
    "iat" => $issuedAt,
    "exp" => $expirationTime,
    "data" => [
        "id" => (int) $user["id"],
        "full_name" => $user["full_name"],
        "email" => $user["email"],
        "role" => $user["role"]
    ]
];

$token = JWT::encode(
    $payload,
    $jwt_secret,
    "HS256"
);

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "token" => $token,
    "user" => [
        "id" => (int) $user["id"],
        "full_name" => $user["full_name"],
        "email" => $user["email"],
        "role" => $user["role"],
        "status" => $user["status"]
    ]
]);
