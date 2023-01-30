<?php

namespace Autotask\Tests\Client\Http;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Paginator;
use Autotask\Client\Http\Response\PagedResponseParser;
use Autotask\Tests\Client\Factory\ClientFactory;
use Closure;
use Illuminate\Support\Collection;
use LogicException;
use PHPUnit\Framework\TestCase;

class PaginatorTest extends TestCase
{
    public function test_that_page_existence_can_be_checked()
    {
        $paginator1 = $this->make(__DIR__ . '/../Stubs/query_response_successful_page_1.json');
        $paginator2 = $this->make(__DIR__ . '/../Stubs/query_response_successful_page_2.json');

        $this->assertTrue($paginator1->hasNextPage());
        $this->assertFalse($paginator1->hasPreviousPage());

        $this->assertFalse($paginator2->hasNextPage());
        $this->assertTrue($paginator2->hasPreviousPage());
    }

    public function test_that_next_page_can_be_retrieved()
    {
        $httpClient = Client::fake([
            '*' => Client::response(__DIR__ . '/../Stubs/query_response_successful_page_2.json'),
        ]);

        $page1 = $this->make(__DIR__ . '/../Stubs/query_response_successful_page_1.json', $httpClient);

        $page2 = $page1->nextPage();

        $this->assertInstanceOf(Paginator::class, $page2);
        $this->assertNotEquals($page1, $page2);
    }

    public function test_that_previous_page_can_be_retrieved()
    {
        $httpClient = Client::fake([
            '*' => Client::response(__DIR__ . '/../Stubs/query_response_successful_page_1.json'),
        ]);

        $page2 = $this->make(__DIR__ . '/../Stubs/query_response_successful_page_2.json', $httpClient);

        $page1 = $page2->previousPage();

        $this->assertInstanceOf(Paginator::class, $page1);
        $this->assertNotEquals($page1, $page2);
    }

    public function test_that_page_path_is_split_from_full_url()
    {
        $paginator = new Paginator(
            client: ClientFactory::new()->make(),
            items: new Collection(),
            nextPageUrl: 'https://autotask.net/Tickets/query',
            previousPageUrl: 'https://autotask.net/Tickets/query?page=2',
        );

        $this->assertTrue($paginator->hasNextPage());
        $this->assertTrue($paginator->hasPreviousPage());

        $this->assertSame('Tickets/query', $this->invade($paginator, 'nextPageUrl'));
        $this->assertSame('Tickets/query?page=2', $this->invade($paginator, 'previousPageUrl'));
    }

    public function test_that_page_path_is_invalid_if_unable_to_be_split()
    {
        $paginator = new Paginator(
            client: ClientFactory::new()->make(),
            items: new Collection(),
            nextPageUrl: 'https://autotask.net/Tickets/',
            previousPageUrl: 'https://autotask.net/Tickets?page=2',
        );

        $this->assertFalse($paginator->hasNextPage());
        $this->assertFalse($paginator->hasPreviousPage());

        $this->assertNull($this->invade($paginator, 'nextPageUrl'));
        $this->assertNull($this->invade($paginator, 'previousPageUrl'));
    }

    public function test_that_exception_is_thrown_if_next_page_is_attempted_when_no_next_page_exists()
    {
        $this->expectExceptionObject(new LogicException(
            'There is no next page.'
        ));

        $paginator = new Paginator(
            client: ClientFactory::new()->make(),
            items: new Collection(),
            nextPageUrl: null,
            previousPageUrl: null,
        );

        $paginator->nextPage();
    }

    public function test_that_exception_is_thrown_if_previous_page_is_attempted_when_no_previous_page_exists()
    {
        $this->expectExceptionObject(new LogicException(
            'There is no previous page.'
        ));

        $paginator = new Paginator(
            client: ClientFactory::new()->make(),
            items: new Collection(),
            nextPageUrl: null,
            previousPageUrl: null,
        );

        $paginator->previousPage();
    }

    private function make(string $responseFile, Client $httpClient = null): Paginator
    {
        return PagedResponseParser::parse(
            ClientFactory::new($httpClient)->make(),
            Client::response($responseFile)
        );
    }

    /**
     * Returns a private (or protected) property value from a class.
     */
    private function invade(object $class, string $property): mixed
    {
        return Closure::bind(
            fn ($instance) => $instance->{$property}, null, get_class($class)
        )($class);
    }
}