<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

function validate_string($value, $maxLength) {
    return isset($value) && is_string($value) && strlen($value) <= $maxLength;
}

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM authors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            http_response_code(200);
            echo json_encode($result->fetch_assoc());
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Author not found']);
        }
    } else {
        $result = $conn->query("SELECT * FROM authors");
        $authors = [];
        while ($row = $result->fetch_assoc()) {
            $authors[] = $row;
        }
        http_response_code(200);
        echo json_encode($authors);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!validate_string($data['name'], 255) || !validate_string($data['bio'], 1000)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and bio are required and must meet length limits']);
        exit;
    }

    if (!preg_match("/^[A-Za-z\s.'-]+( [IVXLCDM]+)?$/", $data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid name format']);
        exit;
    }

    if (!preg_match("/^[A-Za-z0-9\s.,!?':;()-]+$/", $data['bio'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid bio format']);
        exit;
    }

    $name = $conn->real_escape_string($data['name']);
    $bio = $conn->real_escape_string($data['bio']);

    $check = $conn->prepare("SELECT id FROM authors WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Author with this name already exists']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO authors (name, bio) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $bio);
    $stmt->execute();

    http_response_code(201);
    echo json_encode(['message' => 'Author created']);
}

if ($method === 'PUT') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents('php://input'), true);

        if (!validate_string($data['name'], 255) || !validate_string($data['bio'], 1000)) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and bio are required and must meet length limits']);
            exit;
        }

        if (!preg_match("/^[A-Za-z\s.'-]+( [IVXLCDM]+)?$/", $data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid name format']);
            exit;
        }

        if (!preg_match("/^[A-Za-z0-9\s.,!?':;()-]+$/", $data['bio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid bio format']);
            exit;
        }

        $check = $conn->prepare("SELECT id FROM authors WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Author not found']);
            exit;
        }

        $name = $conn->real_escape_string($data['name']);
        $bio = $conn->real_escape_string($data['bio']);

        $stmt = $conn->prepare("UPDATE authors SET name = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $bio, $id);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(['message' => 'Author updated']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing author ID']);
    }
}

if ($method === 'DELETE') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $check = $conn->prepare("SELECT id FROM authors WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Author not found']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM authors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(['message' => 'Author deleted']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing author ID']);
    }
}
?>
