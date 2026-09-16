<?php

namespace UoL\JSLE\Contracts;

interface RedcapRepositoryInterface
{
    /**
     * Fetch REDCap data.
     *
     * @param int $projectId
     * @param string $format Typically 'array'
     * @param mixed $records
     * @param mixed $fields
     * @param mixed $events
     * @param mixed $groups Optional DAG filter or group identifier
     *
     * @return array Data in array format, keyed by record id when format is 'array'
     */
    public function getData(int $projectId,
                            string $format = 'array',
                            mixed $records = null,
                            mixed $fields = null,
                            mixed $events = null,
                            mixed $groups = null): array;
}