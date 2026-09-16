<?php

use UoL\JSLE\AccessibleRecordsFetcher;
use UoL\JSLE\Contracts\RedcapRepositoryInterface;
use Pest\Expectation;

// A tiny test double implementing the repo interface for assertions/behavior.
class InMemoryRedcapRepo implements RedcapRepositoryInterface
{
    public $lastCall = [];
    private $responses;

    /**
     * @param array $responses Map of groupIdentifier|null => returned data array
     *                         Use null key for no-group calls.
     */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function getData($projectId, $format = 'array', $records = null, $fields = null, $events = null, $groups = null): array
    {
        $this->lastCall = [
            'projectId' => $projectId,
            'format' => $format,
            'records' => $records,
            'fields' => $fields,
            'events' => $events,
            'groups' => $groups,
        ];

        // Provide per-group responses; fallback to empty array
        $key = $groups === null ? '::null::' : (string)$groups;
        return $this->responses[$key] ?? [];
    }
}

it('returns records restricted to the user DAG when user has a DAG', function () {
    $projectId = 123;
    $userId = 'bob';
    $userRights = ['data_access_group' => 'dag_1'];

    // Repo configured to return two records when group 'dag_1' requested
    $repo = new InMemoryRedcapRepo([
        'dag_1'    => ['42' => ['field1' => 'a'], '100' => ['field1' => 'b']],
        '::null::' => ['1' => []], // not used in this test
    ]);

    $fetcher = new AccessibleRecordsFetcher($repo, $projectId, $userId, $userRights);
    $ids = $fetcher->getAccessibleRecordIds();

    expect($repo->lastCall['groups'])->toBe('dag_1');
    expect($ids)->toBe([42, 100]);
});

it('returns all records when user has no DAG', function () {
    $projectId = 123;
    $userId = 'alice';
    $userRights = []; // no DAG keys

    $repo = new InMemoryRedcapRepo([
        '::null::' => ['A' => [], 'B' => [], 'C' => []],
    ]);

    $fetcher = new AccessibleRecordsFetcher($repo, $projectId, $userId, $userRights);
    $ids = $fetcher->getAccessibleRecordIds();

    expect($repo->lastCall['groups'])->toBeNull();
    expect($ids)->toBe(['A', 'B', 'C']);
});

it('throws if user rights are not provided', function () {
    $projectId = 123;
    $userId = 'charlie';
    $repo = new InMemoryRedcapRepo();

    $fetcher = new AccessibleRecordsFetcher($repo, $projectId, $userId, null);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("User rights for user [{$userId}] are required.");

    $fetcher->getAccessibleRecordIds();
});
