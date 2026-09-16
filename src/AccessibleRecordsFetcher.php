<?php
namespace UoL\JSLE;

use UoL\JSLE\Contracts\RedcapRepositoryInterface;
use InvalidArgumentException;

class AccessibleRecordsFetcher
{
    private RedcapRepositoryInterface $repo;
    private int $projectId;
    private string $userId;
    /** @var array|null user rights for the current user (only the single user's rights) */
    private ?array $userRights;

    /**
     * @param RedcapRepositoryInterface $repo abstraction over REDCap::getData
     * @param int $projectId
     * @param string $userId REDCap USERID for the current user
     * @param array|null $userRights the entry from $Proj->user_rights[$userId] or equivalent
     */
    public function __construct(RedcapRepositoryInterface $repo, int $projectId, string $userId, ?array $userRights)
    {
        $this->repo = $repo;
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->userRights = $userRights;
    }

    /**
     * Return list of record IDs (strings) that the current user can access in this project.
     *
     * @return string[] sorted list of record ids
     *
     * @throws InvalidArgumentException if userRights are missing
     */
    public function getAccessibleRecordIds(): array
    {
        $dag = '';
        if ( !empty($this->userRights) ) {
            $dag = $this->extractDagIdentifier($this->userRights);
        }

        // Call repository to fetch data. If $dag is null, we don't pass a group filter.
        if ($dag !== null && $dag !== '') {
            $data = $this->repo->getData($this->projectId, 'array', null, null, null, $dag);
        } else {
            $data = $this->repo->getData($this->projectId, 'array');
        }

        // Expect getData(..., 'array') to return an array keyed by record id. Return keys sorted.
        $recordIds = array_keys($data);
        $recordIds = array_values(array_unique($recordIds));
        sort($recordIds, SORT_NATURAL);

        return $recordIds;
    }

    /**
     * Extract the DAG identifier from a user's rights array.
     * Supports common key names used across REDCap versions.
     *
     * @param array $userRights
     * @return string|null
     */
    private function extractDagIdentifier(array $userRights): ?string
    {
        // Common keys: 'data_access_group', 'group_id', 'group_name'
        if (isset($userRights['data_access_group']) && $userRights['data_access_group'] !== '') {
            return (string)$userRights['data_access_group'];
        }

        if (isset($userRights['group_id']) && $userRights['group_id'] !== '') {
            return (string)$userRights['group_id'];
        }

        if (isset($userRights['group_name']) && $userRights['group_name'] !== '') {
            return (string)$userRights['group_name'];
        }

        return null;
    }
}
