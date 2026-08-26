<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../config/jwt.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function authenticate()
{
    global $jwt_secret;

    $headers = getallheaders();

    if (
        !isset($headers["Authorization"])
    ) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Authorization token is required"
        ]);

        exit;
    }

    $authHeader = $headers["Authorization"];

    if (
        !preg_match(
            "/Bearer\s(\S+)/",
            $authHeader,
            $matches
        )
    ) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid authorization format"
        ]);

        exit;
    }

    $token = $matches[1];

    try {

        $decoded = JWT::decode(
            $token,
            new Key($jwt_secret, "HS256")
        );

        return $decoded;

    } catch (Exception $e) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired token"
        ]);

        exit;
    }
}