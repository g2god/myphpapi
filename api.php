<?php

header("Content-Type: application/json");

// ✅ Allow requests from any frontend (CORS fix)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ✅ Handle preflight requests (Important for CORS)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

define("DATA_FILE", "data.json");

// Read data from file
function readData() {
    return file_exists(DATA_FILE) ? json_decode(file_get_contents(DATA_FILE), true) ?? [] : [];
}

// Write data to file
function writeData($data) {
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER["REQUEST_METHOD"];
$input = json_decode(file_get_contents("php://input"), true);
$data = readData();

// Handle different API requests
if ($method === "GET") {
    echo json_encode(["status" => "success", "data" => $data]);

} elseif ($method === "POST") {
    if (!isset($input["content"])) {
        echo json_encode(["status" => "error", "message" => "No content provided"]);
        exit;
    }
    $newItem = ["id" => count($data) + 1, "content" => $input["content"]];
    $data[] = $newItem;
    writeData($data);
    echo json_encode(["status" => "success", "message" => "Data added", "data" => $newItem]);

} elseif ($method === "PUT") {
    if (!isset($input["id"], $input["content"])) {
        echo json_encode(["status" => "error", "message" => "ID and content required"]);
        exit;
    }
    foreach ($data as &$item) {
        if ($item["id"] == $input["id"]) {
            $item["content"] = $input["content"];
            writeData($data);
            echo json_encode(["status" => "success", "message" => "Data updated"]);
            exit;
        }
    }
    echo json_encode(["status" => "error", "message" => "ID not found"]);

} elseif ($method === "DELETE") {
    if (!isset($input["id"])) {
        echo json_encode(["status" => "error", "message" => "ID required"]);
        exit;
    }
    $filteredData = array_values(array_filter($data, fn($item) => $item["id"] != $input["id"]));
    
    if (count($filteredData) === count($data)) {
        echo json_encode(["status" => "error", "message" => "ID not found"]);
        exit;
    }

    writeData($filteredData);
    echo json_encode(["status" => "success", "message" => "Data deleted"]);

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

?>
