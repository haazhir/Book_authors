<?php
namespace App;

use Rakit\Validation\Validator;

class AuthorService
{
    private $conn;
    private $validator;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->validator = new Validator();
        $this->validator->addValidator('string', new \Rakit\Validation\Rules\Required());
    }

    public function getAllAuthors()
    {
        $result = $this->conn->query("SELECT * FROM authors");
        $authors = [];
        while ($row = $result->fetch_assoc()) {
            $authors[] = $row;
        }
        return $authors;
    }

    public function getAuthor($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM authors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            return null;
        }
        return $result->fetch_assoc();
    }

    public function createAuthor($data)
    {
        
        $validation = $this->validator->validate($data, [
            'name' => 'required|max:255',
            'bio' => 'required|max:1000'
        ]);

        if ($validation->fails()) {
            return ['status' => 'error', 'errors' => $validation->errors()->firstOfAll()];
        }

        $name = $this->conn->real_escape_string($data['name']);
        $bio = $this->conn->real_escape_string($data['bio']);

        $check = $this->conn->prepare("SELECT id FROM authors WHERE name = ?");
        $check->bind_param("s", $name);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            return ['status' => 'error', 'error' => 'Author already exists'];
        }

        $stmt = $this->conn->prepare("INSERT INTO authors (name, bio) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $bio);
        $stmt->execute();

        return ['status' => 'success'];
    }

    public function updateAuthor($id, $data)
    {
        $validation = $this->validator->validate($data, [
            'name' => 'required|max:255',
            'bio' => 'required|max:1000'
        ]);

        if ($validation->fails()) {
            return ['status' => 'error', 'errors' => $validation->errors()->firstOfAll()];
        }

        $check = $this->conn->prepare("SELECT id FROM authors WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if (!$result || $result->num_rows === 0) {
            return ['status' => 'error', 'error' => 'Author not found'];
        }

        $name = $this->conn->real_escape_string($data['name']);
        $bio = $this->conn->real_escape_string($data['bio']);

        $stmt = $this->conn->prepare("UPDATE authors SET name = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $bio, $id);
        $stmt->execute();

        return ['status' => 'success'];
    }

    public function deleteAuthor($id)
    {
        $check = $this->conn->prepare("SELECT id FROM authors WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if (!$result || $result->num_rows === 0) {
            return ['status' => 'error', 'error' => 'Author not found'];
        }

        $stmt = $this->conn->prepare("DELETE FROM authors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return ['status' => 'success'];
    }
}
