<?php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

function validate_string($value, $maxLength) {
    return isset($value) && is_string($value) && strlen($value) <= $maxLength;
}

function validate_int($value, $min = null, $max = null) {
    if (!isset($value) || !is_numeric($value)) return false;
    $intVal = intval($value);
    if ($min !== null && $intVal < $min) return false;
    if ($max !== null && $intVal > $max) return false;
    return true;
}

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT books.*, authors.name AS author_name FROM books LEFT JOIN authors ON books.author_id = authors.id WHERE books.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            http_response_code(200);
            echo json_encode($result->fetch_assoc());
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found']);
        }
    } else {
        $result = $conn->query("SELECT books.*, authors.name AS author_name FROM books LEFT JOIN authors ON books.author_id = authors.id");
        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
        http_response_code(200);
        echo json_encode($books);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!validate_string($data['title'], 255) || 
        !validate_int($data['author_id'], 1) || 
        !validate_int($data['publication_year'], 0, intval(date("Y")))) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid title, author ID, or publication year']);
        exit;
    }

    if (!preg_match("/^[A-Za-z0-9\s.,!?':;()-]+$/", $data['title'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid title format']);
        exit;
    }
    $title = $conn->real_escape_string($data['title']);
    $author_id = intval($data['author_id']);
    $publication_year = intval($data['publication_year']);

    $checkAuthor = $conn->prepare("SELECT id FROM authors WHERE id = ?");
    $checkAuthor->bind_param("i", $author_id);
    $checkAuthor->execute();
    $authorResult = $checkAuthor->get_result();

    if ($authorResult->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Author ID does not exist']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO books (title, author_id, publication_year) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $title, $author_id, $publication_year);
    $stmt->execute();

    http_response_code(201);
    echo json_encode(['message' => 'Book created']);
}

if ($method === 'PUT') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents('php://input'), true);

        if (!validate_string($data['title'], 255) || 
            !validate_int($data['author_id'], 1) || 
            !validate_int($data['publication_year'], 0, intval(date("Y")))) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid title, author ID, or publication year']);
            exit;
        }

        if (!preg_match("/^[A-Za-z0-9\s.,!?':;()-]+$/", $data['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid title format']);
            exit;
        }

        $checkBook = $conn->prepare("SELECT id FROM books WHERE id = ?");
        $checkBook->bind_param("i", $id);
        $checkBook->execute();
        $bookResult = $checkBook->get_result();

        if ($bookResult->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found']);
            exit;
        }

        $author_id = intval($data['author_id']);
        $checkAuthor = $conn->prepare("SELECT id FROM authors WHERE id = ?");
        $checkAuthor->bind_param("i", $author_id);
        $checkAuthor->execute();
        $authorResult = $checkAuthor->get_result();

        if ($authorResult->num_rows === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Author ID does not exist']);
            exit;
        }

        $title = $conn->real_escape_string($data['title']);
        $publication_year = intval($data['publication_year']);

        $stmt = $conn->prepare("UPDATE books SET title = ?, author_id = ?, publication_year = ? WHERE id = ?");
        $stmt->bind_param("siii", $title, $author_id, $publication_year, $id);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(['message' => 'Book updated']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing book ID']);
    }
}

if ($method === 'DELETE') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $check = $conn->prepare("SELECT id FROM books WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(['message' => 'Book deleted']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing book ID']);
    }
}
?>
