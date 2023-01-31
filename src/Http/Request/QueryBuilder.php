<?php

namespace Autotask\Client\Http\Request;

use Autotask\Client\Client;
use Autotask\Client\Http\Request\Constraints\Constraint;
use Autotask\Client\Http\Request\Constraints\GroupConstraint;
use Autotask\Client\Http\Request\Constraints\WhereConstraint;
use Autotask\Client\Http\Request\Operators\GroupOperator;
use Autotask\Client\Http\Request\Operators\WhereOperator;
use Autotask\Client\Http\Response\QueryResponseParser;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

final class QueryBuilder
{
    private readonly Client $client;

    private readonly string $endpoint;

    private ?int $limit = null;

    /** @var array<array-key, string> */
    private array $select = [];

    /** @var array<array-key,Constraint> $constraints */
    private array $constraints = [];

    public static function make(Client $client, string $endpoint): self
    {
        return new self($client, $endpoint);
    }

    public function __construct(Client $client, string $endpoint)
    {
        $this->client = $client;
        $this->endpoint = trim($endpoint, '/');
    }

    public function and(callable $group): self
    {
        $group($query = new self($this->client, $this->endpoint));

        $this->constraints[] = new GroupConstraint(GroupOperator::And, ...$query->constraints);

        return $this;
    }

    public function or(callable $group): self
    {
        $group($query = new self($this->client, $this->endpoint));

        $this->constraints[] = new GroupConstraint(GroupOperator::Or, ...$query->constraints);

        return $this;
    }

    public function where(string $field, WhereOperator|string $operator = WhereOperator::Equals, mixed $value = null): self
    {
        if (is_string($operator)) {
            $operator = WhereOperator::from($operator);
        }

        $this->constraints[] = new WhereConstraint($field, $operator, $value);

        return $this;
    }

    public function whereUdf(string $field, WhereOperator|string $operator = WhereOperator::Equals, mixed $value = null): self
    {
        if (is_string($operator)) {
            $operator = WhereOperator::from($operator);
        }

        $this->constraints[] = new WhereConstraint($field, $operator, $value, true);

        return $this;
    }

    public function limit(int $limit): self
    {
        if ($limit < 1 || $limit > 500) {
            throw new UnexpectedValueException('The limit must be between 1 and 500.');
        }

        $this->limit = $limit;

        return $this;
    }

    public function select(string ...$fields): self
    {
        if (in_array('*', $fields)) {
            $this->select = [];

            return $this;
        }

        $this->select = $fields;

        return $this;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function first(): ?array
    {
        return $this->limit(1)->get()['items'][0] ?? null;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     *
     * @return array{
     *     items: array<array-key, array>,
     *     pageDetails: array{count: int, nextPageUrl: null|string, prevPageUrl: null|string}
     * }
     */
    public function get(): array
    {
        $response = $this->performRequest();

        return QueryResponseParser::parse($response);
    }

    /**
     * @return array{
     *     filter?: non-empty-array<array-key, array>,
     *     MaxRecords?: int,
     *     IncludeFields?: non-empty-array<array-key, string>
     * }
     */
    public function toArray(): array
    {
        $search = [];

        if ($this->constraints) {
            $search['filter'] = array_map(
                fn (Constraint $constraint) => $constraint->toArray(),
                $this->constraints
            );
        }

        if (isset($this->limit)) {
            $search['MaxRecords'] = $this->limit;
        }

        if ($this->select) {
            $search['IncludeFields'] = $this->select;
        }

        return $search;
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    private function performRequest(): ResponseInterface
    {
        $query = $this->toJson();

        if (strlen($query) > 1800) {
            return $this->client->post("$this->endpoint/query", $query);
        }

        return $this->client->get("$this->endpoint/query", [
            'search' => $query,
        ]);
    }
}
