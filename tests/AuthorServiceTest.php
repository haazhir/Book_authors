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
}
