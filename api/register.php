<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../config/database.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (
    empty($full_name) ||
    empty($email) ||
    empty($password)
) {

    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);

    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid email address"
    ]);

    exit;
}

$checkUser = $pdo->prepare(
    "SELECT id FROM users WHERE email = ?"
);

$checkUser->execute([$email]);

if ($checkUser->fetch()) {

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
    "SELECT id FROM users WHERE email = ?"
);

$stmt->execute([$email]);

if ($stmt->fetch()) {
    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "Email already exists"
    ]);

    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO users (full_name, email, password)
     VALUES (?, ?, ?)"
);

$stmt->execute([
    $full_name,
    $email,
    $hashedPassword
]);

echo json_encode([
    "success" => true,
    "message" => "Registration successful"
]);