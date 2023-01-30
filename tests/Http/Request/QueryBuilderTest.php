<?php

namespace Autotask\Tests\Client\Http\Request;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Request\QueryBuilder;
use Autotask\Client\Http\Response\PagedResponseParser;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

final class QueryBuilderTest extends TestCase
{
    public function test_that_query_builder_can_be_made()
    {
        $client = ClientFactory::new()->make();

        $builder = QueryBuilder::make($client, 'Contacts');

        $this->assertEquals(new QueryBuilder($client, 'Contacts'), $builder);
    }

    public function test_that_and_groups_are_applied()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->and(function (QueryBuilder $query) {
            $query->where(field: 'firstName', value: 'Jim');
            $query->where(field: 'lastName', value: 'Halpert');
        });

        $this->assertEqualsCanonicalizing(
            [
                'filter' => [
                    [
                        'op' => 'AND',
                        'items' => [
                            [
                                'field' => 'firstName',
                                'op' => 'eq',
                                'value' => 'Jim',
                            ],
                            [
                                'field' => 'lastName',
                                'op' => 'eq',
                                'value' => 'Halpert',
                            ]
                        ]
                    ],
                ],
            ],
            $query->toArray()
        );
    }

    public function test_that_or_groups_are_applied()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->or(function (QueryBuilder $query) {
            $query->where(field: 'firstName', value: 'Jim');
            $query->where(field: 'firstName', value: 'Pam');
        });

        $this->assertEqualsCanonicalizing(
            [
                'filter' => [
                    [
                        'op' => 'OR',
                        'items' => [
                            [
                                'field' => 'firstName',
                                'op' => 'eq',
                                'value' => 'Jim',
                            ],
                            [
                                'field' => 'firstName',
                                'op' => 'eq',
                                'value' => 'Pam',
                            ]
                        ]
                    ],
                ],
            ],
            $query->toArray()
        );
    }

    public function test_that_where_constraints_are_applied()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->where('firstName', 'eq', 'Jim');

        $this->assertEqualsCanonicalizing(
            [
                'filter' => [
                    [
                        'field' => 'firstName',
                        'op' => 'eq',
                        'value' => 'Jim',
                    ],
                ],
            ],
            $query->toArray()
        );
    }

    public function test_that_where_udf_constraints_are_applied()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->whereUdf('userDefinedField1', 'eq', 'Test Value');

        $this->assertEqualsCanonicalizing(
            [
                'filter' => [
                    [
                        'field' => 'userDefinedField1',
                        'op' => 'eq',
                        'value' => 'Test Value',
                        'udf' => true,
                    ],
                ],
            ],
            $query->toArray()
        );
    }

    public function test_that_limit_can_be_applied()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->limit(30);

        $this->assertEqualsCanonicalizing(
            ['MaxRecords' => 30], $query->toArray()
        );
    }

    public function test_that_all_fields_can_be_selected()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->select('*');

        $this->assertEmpty($query->toArray());
    }

    public function test_that_certain_fields_can_be_selected()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->select('id', 'ticketNumber', 'companyId');

        $this->assertEqualsCanonicalizing(
            [
                'IncludeFields' => [
                    'id', 'ticketNumber', 'companyId',
                ],
            ],
            $query->toArray()
        );
    }

    public function test_that_casting_query_builder_to_string_shows_json_in_pretty_format()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->where('email', 'contains', '@theoffice.com');

        $this->assertSame(
            expected: <<<JSON
            {
                "filter": [
                    {
                        "field": "email",
                        "op": "contains",
                        "value": "@theoffice.com"
                    }
                ]
            }
            JSON,
            actual: $query->__toString()
        );
    }

    public function test_that_first_entity_can_be_retrieved()
    {
        $httpClient = Client::fake([
            '*' => Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_1.json'
            )
        ]);

        $client = ClientFactory::new($httpClient)->baseUri('https://example.net/api/v1.0')->make();

        $query = new QueryBuilder($client, 'Contacts');

        $entity = $query->first();

        $this->assertEqualsCanonicalizing(
            ['id' => 1, 'firstName' => 'Jim', 'lastName' => 'Halpert'],
            $entity
        );

        $httpClient
            ->assertUri(
                'https://example.net/api/v1.0/Contacts/query?search='. urlencode('{"MaxRecords":1}')
            );
    }

    public function test_that_a_get_request_is_performed_when_the_query_is_less_than_1800_characters()
    {
        $httpClient = Client::fake([
            'https://example.net/api/v1.0/Tickets/query*' => Client::response('{"items":[]}'),
        ]);

        $client = ClientFactory::new($httpClient)->baseUri('https://example.net/api/v1.0')->make();
        $query = new QueryBuilder($client, 'Tickets');

        $query
            ->where('firstName', 'eq', 'Jim')
            ->get();

        $httpClient
            ->assertMethod('GET')
            ->assertUri('https://example.net/api/v1.0/Tickets/query?search=' . urlencode($query->toJson()));
    }

    public function test_that_a_post_request_is_performed_when_the_query_is_more_than_1800_characters()
    {
        $httpClient = Client::fake([
            'https://example.net/api/v1.0/Tickets/query*' => Client::response('{"items":[]}'),
        ]);

        $client = ClientFactory::new($httpClient)->baseUri('https://example.net/api/v1.0')->make();
        $query = new QueryBuilder($client, 'Tickets');

        for ($i = 0; $i <= 40; $i++) {
            $query->where('firstName', 'eq', 'Jim');
        }

        $query->get();

        $httpClient
            ->assertMethod('POST')
            ->assertUri('https://example.net/api/v1.0/Tickets/query')
            ->assertBodyIs($query->toJson());
    }

    public function test_that_paginate_returns_a_paged_response()
    {
        $httpClient = Client::fake([
            'https://example.net/api/v1.0/Tickets/query*' => Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_1.json'
            ),
        ]);

        $client = ClientFactory::new($httpClient)->baseUri('https://example.net/api/v1.0')->make();

        $query = new QueryBuilder($client, 'Tickets');

        $page = $query->paginate();

        $this->assertEquals(
            PagedResponseParser::parse($client, Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_1.json'
            )),
            $page
        );
    }

    public function test_that_loop_gets_all_pages()
    {
        $httpClient = Client::fake([
            'https://example.net/api/v1.0/Contacts/query?search=%5B%5D' => Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_1.json'
            ),

            'https://example.net/api/v1.0/Contacts/query?page=2' => Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_2.json'
            )
        ]);

        $client = ClientFactory::new($httpClient)->baseUri('https://example.net/api/v1.0')->make();

        $query = new QueryBuilder($client, 'Contacts');

        $array = iterator_to_array($query->loop());

        $this->assertEqualsCanonicalizing(
            [
                ['id' => 1, 'firstName' => 'Jim', 'lastName' => 'Halpert'],
                ['id' => 2, 'firstName' => 'Dwight', 'lastName' => 'Schrute'],
            ],
            $array
        );
    }
}