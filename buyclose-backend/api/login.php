<?php


require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;

require_once "../config/database.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once "../config/database.php";

require_once "../config/jwt.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);

    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, full_name, email, password
     FROM users
     WHERE email = ?"
);

$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);

    exit;
}

if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);

    exit;
}

$issuedAt = time();
$expirationTime = $issuedAt + (60 * 60 * 24);

$payload = [
    "iat" => $issuedAt,
    "exp" => $expirationTime,
    "data" => [
        "id" => $user["id"],
        "full_name" => $user["full_name"],
        "email" => $user["email"]
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
        "id" => $user["id"],
        "full_name" => $user["full_name"],
        "email" => $user["email"]
    ]
]);