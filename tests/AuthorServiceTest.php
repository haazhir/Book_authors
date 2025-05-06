<?php

use PHPUnit\Framework\TestCase;
use App\AuthorService;

class AuthorServiceTest extends TestCase
{
    private $mockConn;
    private $service;

    protected function setUp(): void
    {
        $this->mockConn = $this->createMock(mysqli::class);
        $this->service = new AuthorService($this->mockConn);
    }

    public function testGetAllAuthorsReturnsArray()
    {
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'name' => 'Author1', 'bio' => 'Bio1'],
            null
        );
        $this->mockConn->method('query')->willReturn($mockResult);

        $authors = $this->service->getAllAuthors();

        $this->assertIsArray($authors);
        $this->assertCount(1, $authors);
        $this->assertEquals('Author1', $authors[0]['name']);
    }

    public function testGetAuthorReturnsNullWhenNotFound()
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('bind_param')->willReturn(true);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn(false);
        $this->mockConn->method('prepare')->willReturn($stmtMock);

        $author = $this->service->getAuthor(999);
        $this->assertNull($author);
    }

    public function testCreateAuthorRejectsInvalidInput()
    {
        $data = ['name' => '', 'bio' => ''];
        $result = $this->service->createAuthor($data);

        $this->assertEquals('error', $result['status']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testCreateAuthorRejectsDuplicate()
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('bind_param')->willReturn(true);
        $stmtMock->method('execute')->willReturn(true);

        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('num_rows')->willReturn(1);

        $stmtMock->method('get_result')->willReturn($mockResult);
        $this->mockConn->method('prepare')->willReturn($stmtMock);

        $data = ['name' => 'Existing Author', 'bio' => 'Some bio'];
        $result = $this->service->createAuthor($data);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Author already exists', $result['error']);
    }

    public function testUpdateAuthorRejectsMissingAuthor()
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('bind_param')->willReturn(true);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn(false);
        $this->mockConn->method('prepare')->willReturn($stmtMock);

        $data = ['name' => 'Updated Name', 'bio' => 'Updated Bio'];
        $result = $this->service->updateAuthor(999, $data);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Author not found', $result['error']);
    }

    public function testDeleteAuthorRejectsMissingAuthor()
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('bind_param')->willReturn(true);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn(false);
        $this->mockConn->method('prepare')->willReturn($stmtMock);

        $result = $this->service->deleteAuthor(999);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Author not found', $result['error']);
    }

    public function testEdgeCaseMaxLengths()
    {
        $data = ['name' => str_repeat('a', 255), 'bio' => str_repeat('b', 1000)];
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('bind_param')->willReturn(true);
        $stmtMock->method('execute')->willReturn(true);
        $this->mockConn->method('prepare')->willReturn($stmtMock);

        $result = $this->service->createAuthor($data);
        $this->assertEquals('success', $result['status']);
    }

    public function testRejectsInvalidDataTypes()
    {
        $data = ['name' => 123, 'bio' => 456];
        $result = $this->service->createAuthor($data);

        $this->assertEquals('error', $result['status']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testHandlesDatabaseFailureGracefully()
    {
        $this->mockConn->method('prepare')->willReturn(false);
        $data = ['name' => 'Test', 'bio' => 'Bio'];

        $result = $this->service->createAuthor($data);

        $this->assertEquals('error', $result['status']);
    }
}
