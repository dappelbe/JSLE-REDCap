<?php

use UoL\JSLE\Services\BilagPageService;

it('processes non-repeating event data keyed by date', function () {
    $service = new BilagPageService([], [], []);

    $bilagData = [
        '1001' => [
            12 => [
                'record_id' => '1001',
                'bilag_date' => '2026-01-10',
                'bilag_today_const' => '2',
            ],
        ],
    ];

    $processed = $service->processParticipantData($bilagData);

    expect($processed)->toBe([
        '2026-01-10' => [
            'record_id' => '1001',
            'bilag_date' => '2026-01-10',
            'bilag_today_const' => '2',
            'event_id' => 12,
            'repeat_instance' => '',
        ],
    ]);
});

it('processes repeat instance data keyed by date', function () {
    $service = new BilagPageService([], [], []);

    $bilagData = [
        '1001' => [
            'repeat_instances' => [
                34 => [
                    'bilag_form' => [
                        1 => [
                            'record_id' => '1001',
                            'bilag_date' => '2026-02-03',
                            'bilag_haem2004_today' => '1',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $processed = $service->processParticipantData($bilagData);

    expect($processed)->toBe([
        '2026-02-03' => [
            'record_id' => '1001',
            'bilag_date' => '2026-02-03',
            'bilag_haem2004_today' => '1',
            'event_id' => 34,
            'repeat_instance' => '1',
        ],
    ]);
});

it('skips entries when the configured date field is missing or empty', function () {
    $service = new BilagPageService([], [], []);

    $bilagData = [
        '1001' => [
            12 => [
                'record_id' => '1001',
                'bilag_today_const' => '2',
            ],
            13 => [
                'record_id' => '1001',
                'bilag_date' => '',
                'bilag_today_const' => '1',
            ],
            14 => [
                'record_id' => '1001',
                'bilag_date' => null,
                'bilag_today_const' => '0',
            ],
            'repeat_instances' => [
                34 => [
                    'bilag_form' => [
                        1 => [
                            'record_id' => '1001',
                            'bilag_haem2004_today' => '1',
                        ],
                        2 => [
                            'record_id' => '1001',
                            'bilag_date' => '',
                            'bilag_haem2004_today' => '2',
                        ],
                        3 => [
                            'record_id' => '1001',
                            'bilag_date' => null,
                            'bilag_haem2004_today' => '3',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $processed = $service->processParticipantData($bilagData);

    expect($processed)->toBe([]);
});

it('filters processed data to dates up to current-event date and not in the future', function () {
    $service = new BilagPageService([], [], []);

    $processed = [
        '2026-01-01' => [
            'record_id' => '1001',
            'bilag_date' => '2026-01-01',
            'event_id' => 10,
            'repeat_instance' => '',
        ],
        '2026-02-10' => [
            'record_id' => '1001',
            'bilag_date' => '2026-02-10',
            'event_id' => 12,
            'repeat_instance' => '',
        ],
        '2026-03-01' => [
            'record_id' => '1001',
            'bilag_date' => '2026-03-01',
            'event_id' => 13,
            'repeat_instance' => '',
        ],
        '2026-12-20' => [
            'record_id' => '1001',
            'bilag_date' => '2026-12-20',
            'event_id' => 14,
            'repeat_instance' => '',
        ],
    ];

    $filtered = $service->filterProcessedParticipantDataByDateWindow($processed, '12', '2026-05-15');

    expect($filtered)->toBe([
        '2026-01-01' => [
            'record_id' => '1001',
            'bilag_date' => '2026-01-01',
            'event_id' => 10,
            'repeat_instance' => '',
        ],
        '2026-02-10' => [
            'record_id' => '1001',
            'bilag_date' => '2026-02-10',
            'event_id' => 12,
            'repeat_instance' => '',
        ],
    ]);
});

it('when current event has no date and no higher event has a date, keeps all previous dates', function () {
    $service = new BilagPageService([], [], []);

    $processed = [
        '2026-01-10' => [
            'record_id' => '1001',
            'bilag_date' => '2026-01-10',
            'event_id' => 10,
            'repeat_instance' => '',
        ],
        '2026-02-10' => [
            'record_id' => '1001',
            'bilag_date' => '2026-02-10',
            'event_id' => 11,
            'repeat_instance' => '',
        ],
    ];

    // Current event is 12, but there is no entry for event 12 in processed data
    // (e.g. its bilag_date was blank and therefore skipped during processing).
    $filtered = $service->filterProcessedParticipantDataByDateWindow($processed, '12', '2026-05-15');

    expect($filtered)->toBe([
        '2026-01-10' => [
            'record_id' => '1001',
            'bilag_date' => '2026-01-10',
            'event_id' => 10,
            'repeat_instance' => '',
        ],
        '2026-02-10' => [
            'record_id' => '1001',
            'bilag_date' => '2026-02-10',
            'event_id' => 11,
            'repeat_instance' => '',
        ],
    ]);
});

it('maps previous bilag involvement to 0/1 flags from filtered data', function () {
    $service = new BilagPageService([], [], []);

    $previousMap = [
        'bilag_const2004_prev' => 'bilag_today_const',
        'bilag_muco2004_prev' => 'bilag_muco_today',
        'bilag_neuro2004_prev' => 'bilag_neuro2004_today',
    ];

    $filtered = [
        '2026-01-01' => [
            'bilag_today_const' => '0',
            'bilag_muco_today' => '0',
        ],
        '2026-02-01' => [
            'bilag_today_const' => '2',
            'bilag_muco_today' => '0',
        ],
    ];

    $flags = $service->processPreviousBilagMap($previousMap, $filtered);

    expect($flags)->toBe([
        'bilag_const2004_prev' => 1,
        'bilag_muco2004_prev' => 0,
        'bilag_neuro2004_prev' => 0,
    ]);
});
