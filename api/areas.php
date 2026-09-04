<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            a.id AS area_id,
            a.name AS area_name,
            a.state,
            dz.id AS zone_id,
            dz.name AS zone_name,
            dz.fee,
            dz.minimum_order
        FROM areas a
        LEFT JOIN delivery_zones dz
            ON dz.area_id = a.id
            AND dz.active = TRUE
        WHERE a.active = TRUE
        ORDER BY a.id ASC, dz.id ASC
    ");

    $stmt->execute();

    $areas = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $areaId = $row["area_id"];

        if (!isset($areas[$areaId])) {
            $areas[$areaId] = [
                "id" => (int) $row["area_id"],
                "name" => $row["area_name"],
                "state" => $row["state"],
                "delivery_zones" => []
            ];
        }

        if ($row["zone_id"] !== null) {
            $areas[$areaId]["delivery_zones"][] = [
                "id" => (int) $row["zone_id"],
                "name" => $row["zone_name"],
                "fee" => (float) $row["fee"],
                "minimum_order" => (float) $row["minimum_order"]
            ];
        }
    }

    echo json_encode([
        "success" => true,
        "areas" => array_values($areas)
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve areas"
    ]);
}
