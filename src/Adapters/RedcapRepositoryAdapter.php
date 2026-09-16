<?php
namespace UoL\JSLE\Adapters;

use UoL\JSLE\Contracts\RedcapRepositoryInterface;

class RedcapRepositoryAdapter implements RedcapRepositoryInterface
{
    public function getData($projectId, $format = 'array', $records = null, $fields = null, $events = null, $groups = null): array
    {
        // call the REDCap static helper (or whatever your REDCap build provides)
        return \REDCap::getData($projectId, $format, $records, $fields, $events, $groups);
    }
}