<?php

namespace Autotask\Tests\Client\Http\Request;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Request\QueryBuilder;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @internal
 */
final class QueryBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function that_query_builder_can_be_made()
    {
        $client = ClientFactory::new()->make();

        $builder = QueryBuilder::make($client, 'Contacts');

        $this->assertEquals(new QueryBuilder($client, 'Contacts'), $builder);
    }

    /**
     * @test
     */
    public function that_and_groups_are_applied()
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
                            ],
                        ],
                    ],
                ],
            ],
            $query->toArray()
        );
    }

    /**
     * @test
     */
    public function that_or_groups_are_applied()
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
                            ],
                        ],
                    ],
                ],
            ],
            $query->toArray()
        );
    }

    /**
     * @test
     */
    public function that_where_constraints_are_applied()
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

    /**
     * @test
     */
    public function that_where_udf_constraints_are_applied()
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

    /**
     * @test
     */
    public function that_limit_can_be_applied()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->limit(30);

        $this->assertEqualsCanonicalizing(
            ['MaxRecords' => 30],
            $query->toArray()
        );
    }

    /**
     * @test
     */
    public function that_limit_must_be_greater_than_0()
    {
        $this->expectExceptionObject(new UnexpectedValueException(
            'The limit must be between 1 and 500.'
        ));

        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->limit(0);
    }

    /**
     * @test
     */
    public function that_limit_must_be_less_than_501()
    {
        $this->expectExceptionObject(new UnexpectedValueException(
            'The limit must be between 1 and 500.'
        ));

        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->limit(501);
    }

    /**
     * @test
     */
    public function that_all_fields_can_be_selected()
    {
        $query = new QueryBuilder(ClientFactory::new()->make(), 'Tickets');

        $query->select('*');

        $this->assertEmpty($query->toArray());
    }

    /**
     * @test
     */
    public function that_certain_fields_can_be_selected()
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

    /**
     * @test
     */
    public function that_casting_query_builder_to_string_shows_json_in_pretty_format()
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

    /**
     * @test
     */
    public function that_first_entity_can_be_retrieved()
    {
        $httpClient = Client::fake([
            '*' => Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_1.json'
            ),
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

    /**
     * @test
     */
    public function that_a_get_request_is_performed_when_the_query_is_less_than_1800_characters()
    {
        $httpClient = Client::fake([
            'https://example.net/api/v1.0/Contacts/query*' => Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_1.json'
            ),
        ]);

        $client = ClientFactory::new($httpClient)->baseUri('https://example.net/api/v1.0')->make();
        $query = new QueryBuilder($client, 'Contacts');

        $query
            ->where('firstName', 'eq', 'Jim')
            ->get();

        $httpClient
            ->assertMethod('GET')
            ->assertUri('https://example.net/api/v1.0/Contacts/query?search=' . urlencode($query->toJson()));
    }

    /**
     * @test
     */
    public function that_a_post_request_is_performed_when_the_query_is_more_than_1800_characters()
    {
        $httpClient = Client::fake([
            'https://example.net/api/v1.0/Contacts/query*' => Client::response(
                __DIR__ . '/../../Stubs/query_response_successful_page_1.json'
            ),
        ]);

        $client = ClientFactory::new($httpClient)->baseUri('https://example.net/api/v1.0')->make();
        $query = new QueryBuilder($client, 'Contacts');

        for ($i = 0; $i <= 40; $i++) {
            $query->where('firstName', 'eq', 'Jim');
        }

        $query->get();

        $httpClient
            ->assertMethod('POST')
            ->assertUri('https://example.net/api/v1.0/Contacts/query')
            ->assertBodyIs($query->toJson());
    }
}
