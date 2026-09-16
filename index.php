<?php
namespace UoL\UKJSLE;

use \REDCap as REDCap;
use UoL\JSLE\Dashboard;

require_once __DIR__ . '/vendor/autoload.php';

global $Proj;

if (!isset($project_id)) {
    die('Project ID is a required field @ Index');
}

$em = new UKJSLE();

$repo = new \UoL\JSLE\Adapters\RedcapRepositoryAdapter();

$fetcher = new \UoL\JSLE\AccessibleRecordsFetcher(
    $repo,
    $Proj->project_id,
    USERID,
    $Proj->user_rights[USERID] ?? null
);
$recordIds = array_map('strval', $fetcher->getAccessibleRecordIds());

$selectedRecordId = isset($_GET['record_id']) ? (string)$_GET['record_id'] : ($recordIds[0] ?? null);
if ($selectedRecordId !== null && !in_array($selectedRecordId, $recordIds, true)) {
    $selectedRecordId = $recordIds[0] ?? null;
}

$dataDictionary = REDCap::getDataDictionary('array');
$choiceMapByField = [];
foreach ($dataDictionary as $dictionaryFieldName => $dictionaryFieldMeta) {
    $rawChoices = trim((string)($dictionaryFieldMeta['select_choices_or_calculations'] ?? ''));
    if ($rawChoices === '') {
        continue;
    }

    $choices = [];
    foreach (preg_split('/\s*\|\s*/', $rawChoices) as $rawChoice) {
        $rawChoice = trim((string)$rawChoice);
        if ($rawChoice === '') {
            continue;
        }

        $parts = explode(',', $rawChoice, 2);
        $choiceCode = trim((string)($parts[0] ?? ''));
        if ($choiceCode === '') {
            continue;
        }

        $choiceLabel = trim((string)($parts[1] ?? $choiceCode));
        $choices[$choiceCode] = $choiceLabel;
    }

    if ($choices !== []) {
        $choiceMapByField[(string)$dictionaryFieldName] = $choices;
    }
}

$mapValueToLabel = static function (string $fieldName, $value) use ($choiceMapByField): string {
    if (is_array($value)) {
        return json_encode($value);
    }

    $rawValue = trim((string)$value);
    if ($rawValue === '') {
        return '';
    }

    if (isset($choiceMapByField[$fieldName])) {
        if (str_contains($rawValue, ',')) {
            $codes = array_filter(array_map('trim', explode(',', $rawValue)), static fn(string $code): bool => $code !== '');
            $labels = array_map(
                static fn(string $code): string => $choiceMapByField[$fieldName][$code] ?? $code,
                $codes
            );

            return implode(', ', $labels);
        }

        return $choiceMapByField[$fieldName][$rawValue] ?? $rawValue;
    }

    if (preg_match('/^(.+)___(.+)$/', $fieldName, $matches) === 1) {
        $baseFieldName = $matches[1];
        $checkboxCode = $matches[2];
        if (isset($choiceMapByField[$baseFieldName])) {
            if ($rawValue === '1') {
                return $choiceMapByField[$baseFieldName][$checkboxCode] ?? $checkboxCode;
            }

            if ($rawValue === '0') {
                return '';
            }
        }
    }

    return $rawValue;
};

$extractDagIdentifier = static function (?array $userRights): ?string {
    if (!$userRights) {
        return null;
    }

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
};

$selectedRecordDataRows = [];
$participantGender = '';
$participantEthnicity = '';
$participantLastVisit = '';
$participantHeight = '';
$participantWeight = '';
$participantAcrRecordedAt = '';
$participantAcrScore = '';
$participantSystolicBp = '';
$participantDiastolicBp = '';
$participantUacr = '';
$participantUpcr = '';
$participantLatestBilagDate = '';
$participantBilagConstitutional = '';
$participantBilagMucocutaneous = '';
$participantBilagNeuropsychiatric = '';
$participantBilagMusculoskeletal = '';
$participantBilagCardiorespiratory = '';
$participantBilagGastrointestinal = '';
$participantBilagOpthalmic = '';
$participantBilagRenal = '';
$participantBilagHaematological = '';
$participantBilagTotal = '';
$participantBloodVisitDate = '';
$participantBloodHaemoglobin = '';
$participantBloodWcc = '';
$participantBloodNeutrophils = '';
$participantBloodLymphocytes = '';
$participantBloodCrp = '';
$participantBloodEsr = '';
$participantBloodC3 = '';
$participantBloodC4 = '';
$participantBloodDsdna = '';
$participantBloodCreatinine = '';
$participantBloodIgg = '';
$participantBloodIga = '';
$participantBloodIgm = '';
$participantStuffVisitDate = '';
$participantStuffAnaTitre = '';
$participantStuffDsdnaTitre = '';
$participantStuffAntiSm = '';
$participantStuffAntiRnp = '';
$participantStuffAntiRo = '';
$participantStuffAntiLa = '';
$participantStuffAllEna = '';
$participantStuffTsh = '';
$participantStuffT4 = '';
$participantStuffCholesterol = '';
$participantStuffTriglycerides = '';
$linkedOldRecordId = null;
$linkedOldBilagVisits = [];
$allBilagVisits = [];
$recentBilagVisits = [];
$lastVisitDate = null;
$latestBilagHeightDate = null;
$latestBilagWeightDate = null;
$latestAsccDate = null;
$latestAsccEventName = null;
$latestBilagSystolicDate = null;
$latestBilagDiastolicDate = null;
$latestBilagUacrDate = null;
$latestBilagUpcrDate = null;
$latestBilagFormDate = null;
$latestBilagEventName = null;
$latestAnnualVisitDate = null;
$latestAnnualVisitEventName = null;
$lastVisitFieldNames = [
    'ascc_date' => true,
    'aa_date' => true,
    'bilag_date' => true,
];
$parseDateValue = static function ($value): ?\DateTimeImmutable {
    $rawDateValue = trim((string)$value);
    if ($rawDateValue === '') {
        return null;
    }

    $dateFormats = [
        'Y-m-d',
        'd-m-Y',
        'm-d-Y',
        'Y/m/d',
        'd/m/Y',
        'm/d/Y',
        'Y-m-d H:i',
        'Y-m-d H:i:s',
        'd-m-Y H:i',
        'd-m-Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y H:i:s',
    ];

    foreach ($dateFormats as $dateFormat) {
        $candidateDate = \DateTimeImmutable::createFromFormat($dateFormat, $rawDateValue);
        if ($candidateDate instanceof \DateTimeImmutable) {
            return $candidateDate;
        }
    }

    $timestamp = strtotime($rawDateValue);
    if ($timestamp === false) {
        return null;
    }

    return (new \DateTimeImmutable())->setTimestamp($timestamp);
};
if ($selectedRecordId !== null) {
    $dag = $extractDagIdentifier($Proj->user_rights[USERID] ?? null);
    $rawRecordData = ($dag !== null && $dag !== '')
        ? $repo->getData($Proj->project_id, 'array', [$selectedRecordId], null, null, $dag)
        : $repo->getData($Proj->project_id, 'array', [$selectedRecordId]);

    $recordData = $rawRecordData[$selectedRecordId] ?? [];
    foreach ($recordData as $eventName => $fieldValues) {
        if (!is_array($fieldValues)) {
            continue;
        }

        $eventBilagDate = $parseDateValue($fieldValues['bilag_date'] ?? '');

        if ($eventBilagDate instanceof \DateTimeImmutable) {
            if ($latestBilagFormDate === null || $eventBilagDate > $latestBilagFormDate) {
                $latestBilagFormDate = $eventBilagDate;
                $latestBilagEventName = (string)$eventName;
            }

            if (isset($fieldValues['bilag_ht']) && trim((string)$fieldValues['bilag_ht']) !== '' && ($latestBilagHeightDate === null || $eventBilagDate > $latestBilagHeightDate)) {
                $latestBilagHeightDate = $eventBilagDate;
                $participantHeight = $mapValueToLabel('bilag_ht', $fieldValues['bilag_ht']);
            }

            if (isset($fieldValues['bilag_wt']) && trim((string)$fieldValues['bilag_wt']) !== '' && ($latestBilagWeightDate === null || $eventBilagDate > $latestBilagWeightDate)) {
                $latestBilagWeightDate = $eventBilagDate;
                $participantWeight = $mapValueToLabel('bilag_wt', $fieldValues['bilag_wt']);
            }

            if (isset($fieldValues['bilag_sysbp']) && trim((string)$fieldValues['bilag_sysbp']) !== '' && ($latestBilagSystolicDate === null || $eventBilagDate > $latestBilagSystolicDate)) {
                $latestBilagSystolicDate = $eventBilagDate;
                $participantSystolicBp = $mapValueToLabel('bilag_sysbp', $fieldValues['bilag_sysbp']);
            }

            if (isset($fieldValues['bilag_diabp']) && trim((string)$fieldValues['bilag_diabp']) !== '' && ($latestBilagDiastolicDate === null || $eventBilagDate > $latestBilagDiastolicDate)) {
                $latestBilagDiastolicDate = $eventBilagDate;
                $participantDiastolicBp = $mapValueToLabel('bilag_diabp', $fieldValues['bilag_diabp']);
            }

            if (isset($fieldValues['bilag_renalurinaryprotcr']) && trim((string)$fieldValues['bilag_renalurinaryprotcr']) !== '' && ($latestBilagUacrDate === null || $eventBilagDate > $latestBilagUacrDate)) {
                $latestBilagUacrDate = $eventBilagDate;
                $participantUacr = $mapValueToLabel('bilag_renalurinaryprotcr', $fieldValues['bilag_renalurinaryprotcr']);
            }

            if (isset($fieldValues['bilag_24hrurinaryprot']) && trim((string)$fieldValues['bilag_24hrurinaryprot']) !== '' && ($latestBilagUpcrDate === null || $eventBilagDate > $latestBilagUpcrDate)) {
                $latestBilagUpcrDate = $eventBilagDate;
                $participantUpcr = $mapValueToLabel('bilag_24hrurinaryprot', $fieldValues['bilag_24hrurinaryprot']);
            }

            $allBilagVisits[] = ['date' => $eventBilagDate, 'data' => $fieldValues];
        }

        $eventAsccDate = $parseDateValue($fieldValues['ascc_date'] ?? '');
        if ($eventAsccDate instanceof \DateTimeImmutable && ($latestAsccDate === null || $eventAsccDate > $latestAsccDate)) {
            $latestAsccDate = $eventAsccDate;
            $latestAsccEventName = (string)$eventName;
        }

        $eventAaDate = $parseDateValue($fieldValues['aa_date'] ?? '');
        if ($eventAaDate instanceof \DateTimeImmutable && ($latestAnnualVisitDate === null || $eventAaDate > $latestAnnualVisitDate)) {
            $latestAnnualVisitDate = $eventAaDate;
            $latestAnnualVisitEventName = (string)$eventName;
        }

        if ($linkedOldRecordId === null && isset($fieldValues['demo_oldstudyno']) && trim((string)$fieldValues['demo_oldstudyno']) !== '') {
            $linkedOldRecordId = trim((string)$fieldValues['demo_oldstudyno']);
        }

        foreach ($fieldValues as $fieldName => $value) {
            if ($fieldName === 'record_id') {
                continue;
            }

            $displayValue = $mapValueToLabel((string)$fieldName, $value);

            if ($fieldName === 'demo_gender' && $participantGender === '') {
                $participantGender = $displayValue;
            }

            if ($fieldName === 'demo_ethnic' && $participantEthnicity === '') {
                $participantEthnicity = $displayValue;
            }

            if (isset($lastVisitFieldNames[$fieldName])) {
                $parsedDate = $parseDateValue($value);
                if ($parsedDate instanceof \DateTimeImmutable && ($lastVisitDate === null || $parsedDate > $lastVisitDate)) {
                    $lastVisitDate = $parsedDate;
                }
            }

            $selectedRecordDataRows[] = [
                'event' => (string)$eventName,
                'field' => (string)$fieldName,
                'value' => $displayValue,
            ];
        }
    }

    if ($linkedOldRecordId !== null) {
        $linkedRawData = $repo->getData($Proj->project_id, 'array', [$linkedOldRecordId]);
        $linkedEventData = $linkedRawData[$linkedOldRecordId] ?? [];
        foreach ($linkedEventData as $linkedEventName => $linkedFieldValues) {
            if (!is_array($linkedFieldValues)) {
                continue;
            }
            $linkedBilagDate = $parseDateValue($linkedFieldValues['bilag_date'] ?? '');
            if ($linkedBilagDate instanceof \DateTimeImmutable) {
                $linkedOldBilagVisits[] = ['date' => $linkedBilagDate, 'data' => $linkedFieldValues];
                $allBilagVisits[] = ['date' => $linkedBilagDate, 'data' => $linkedFieldValues];
            }
        }
        usort($linkedOldBilagVisits, static function (array $a, array $b): int {
            return $b['date'] <=> $a['date'];
        });
    }
    usort($allBilagVisits, static function (array $a, array $b): int {
        return $b['date'] <=> $a['date'];
    });
    $recentBilagVisits = array_slice($allBilagVisits, 0, 8);

    if ($lastVisitDate instanceof \DateTimeImmutable) {
        $participantLastVisit = $lastVisitDate->format('d-M-Y');
    }

    if ($latestAsccDate instanceof \DateTimeImmutable) {
        $participantAcrRecordedAt = $latestAsccDate->format('d-M-Y');
        $latestAsccEventData = $recordData[$latestAsccEventName] ?? null;
        if (is_array($latestAsccEventData) && isset($latestAsccEventData['acr_score_today'])) {
            $participantAcrScore = $mapValueToLabel('acr_score_today', $latestAsccEventData['acr_score_today']);
        }
    }

    if ($latestBilagFormDate instanceof \DateTimeImmutable && $latestBilagEventName !== null) {
        $participantLatestBilagDate = $latestBilagFormDate->format('d-M-Y');
        $latestBilagEventData = $recordData[$latestBilagEventName] ?? null;

        if (is_array($latestBilagEventData)) {
            $participantBilagConstitutional = $mapValueToLabel('bilag_const2004_new', $latestBilagEventData['bilag_const2004_new'] ?? '');
            $participantBilagMucocutaneous = $mapValueToLabel('bilag_muco2004_new', $latestBilagEventData['bilag_muco2004_new'] ?? '');
            $participantBilagNeuropsychiatric = $mapValueToLabel('bilag_neuro2004_new', $latestBilagEventData['bilag_neuro2004_new'] ?? '');
            $participantBilagMusculoskeletal = $mapValueToLabel('bilag_musc2004_new', $latestBilagEventData['bilag_musc2004_new'] ?? '');
            $participantBilagCardiorespiratory = $mapValueToLabel('bilag_cardio2004_new', $latestBilagEventData['bilag_cardio2004_new'] ?? '');
            $participantBilagGastrointestinal = $mapValueToLabel('bilag_gastro2004_new', $latestBilagEventData['bilag_gastro2004_new'] ?? '');
            $participantBilagOpthalmic = $mapValueToLabel('bilag_ophthal2004_new', $latestBilagEventData['bilag_ophthal2004_new'] ?? '');
            $participantBilagRenal = $mapValueToLabel('bilag_renal2004_new', $latestBilagEventData['bilag_renal2004_new'] ?? '');
            $participantBilagHaematological = $mapValueToLabel('bilag_haem2004_new', $latestBilagEventData['bilag_haem2004_new'] ?? '');

            $bilagTotalFields = [
                'bilag_const2004_new',
                'bilag_muco2004_new',
                'bilag_neuro2004_new',
                'bilag_musc2004_new',
                'bilag_cardio2004_new',
                'bilag_gastro2004_new',
                'bilag_ophthal2004_new',
                'bilag_renal2004_new',
                'bilag_haem2004_today',
            ];
            $bilagTotal = 0.0;
            $hasNumericBilagValue = false;
            foreach ($bilagTotalFields as $bilagTotalField) {
                $rawBilagValue = trim((string)($latestBilagEventData[$bilagTotalField] ?? ''));
                if ($rawBilagValue !== '' && is_numeric($rawBilagValue)) {
                    $bilagTotal += (float)$rawBilagValue;
                    $hasNumericBilagValue = true;
                }
            }
            if ($hasNumericBilagValue) {
                $participantBilagTotal = (fmod($bilagTotal, 1.0) === 0.0)
                    ? (string)(int)$bilagTotal
                    : (string)$bilagTotal;
            }

            $participantBloodVisitDate = $participantLatestBilagDate;
            $participantBloodHaemoglobin = $mapValueToLabel('bilag_haemhaemoglobin', $latestBilagEventData['bilag_haemhaemoglobin'] ?? '');
            $participantBloodWcc = $mapValueToLabel('bilag_haemwcc', $latestBilagEventData['bilag_haemwcc'] ?? '');
            $participantBloodNeutrophils = $mapValueToLabel('bilag_haemeutrophils', $latestBilagEventData['bilag_haemeutrophils'] ?? '');
            $participantBloodLymphocytes = $mapValueToLabel('bilag_haemlymphocytes', $latestBilagEventData['bilag_haemlymphocytes'] ?? '');
            $participantBloodCrp = $mapValueToLabel('bilag_othcrp', $latestBilagEventData['bilag_othcrp'] ?? '');
            $participantBloodEsr = $mapValueToLabel('bilag_othesr', $latestBilagEventData['bilag_othesr'] ?? '');
            $participantBloodC3 = $mapValueToLabel('bilag_othc3', $latestBilagEventData['bilag_othc3'] ?? '');
            $participantBloodC4 = $mapValueToLabel('bilag_othc4', $latestBilagEventData['bilag_othc4'] ?? '');
            $participantBloodDsdna = $mapValueToLabel('bilag_othdsdna', $latestBilagEventData['bilag_othdsdna'] ?? '');
            $participantBloodCreatinine = $mapValueToLabel('bilag_renalcreatinine', $latestBilagEventData['bilag_renalcreatinine'] ?? '');
            $participantBloodIgg = $mapValueToLabel('bilag_othigg', $latestBilagEventData['bilag_othigg'] ?? '');
            $participantBloodIga = $mapValueToLabel('bilag_othiga', $latestBilagEventData['bilag_othiga'] ?? '');
            $participantBloodIgm = $mapValueToLabel('bilag_othigm', $latestBilagEventData['bilag_othigm'] ?? '');
        }
    }

    if ($latestAnnualVisitDate instanceof \DateTimeImmutable && $latestAnnualVisitEventName !== null) {
        $participantStuffVisitDate = $latestAnnualVisitDate->format('d-M-Y');
        $latestAnnualVisitEventData = $recordData[$latestAnnualVisitEventName] ?? null;

        if (is_array($latestAnnualVisitEventData)) {
            $participantStuffAnaTitre = $mapValueToLabel('aa_ana_titre1', $latestAnnualVisitEventData['aa_ana_titre1'] ?? '');
            $participantStuffDsdnaTitre = $participantBloodDsdna;
            $enaCheckbox = static function (string $key) use ($latestAnnualVisitEventData): string {
                return ($latestAnnualVisitEventData[$key] ?? '0') === '1' ? 'Yes' : 'No';
            };
            $participantStuffAntiSm = $enaCheckbox('aa_enapos___1');
            $participantStuffAntiRnp = $enaCheckbox('aa_enapos___2');
            $participantStuffAntiRo = $enaCheckbox('aa_enapos___3');
            $participantStuffAntiLa = $enaCheckbox('aa_enapos___4');
            $participantStuffAllEna = $enaCheckbox('aa_enapos___6');
            $participantStuffTsh = $mapValueToLabel('aa_tsh', $latestAnnualVisitEventData['aa_tsh'] ?? '');
            $participantStuffT4 = $mapValueToLabel('aa_t4', $latestAnnualVisitEventData['aa_t4'] ?? '');
            $participantStuffCholesterol = $mapValueToLabel('aa_cholest', $latestAnnualVisitEventData['aa_cholest'] ?? '');
            $participantStuffTriglycerides = $mapValueToLabel('aa_triglyce', $latestAnnualVisitEventData['aa_triglyce'] ?? '');
        }
    }
}

$medTableColumns = [
    ['field' => 'bilag_date',               'label' => 'Visit Date',                                   'type' => 'date'],
    ['field' => 'bilag_hydroxychlorocurrent','label' => 'Hydroxychloroquine',  'revised' => 'bilag_hydroxychlororevised'],
    ['field' => 'bilag_azathiopcurrent',     'label' => 'Azathioprine',        'revised' => 'bilag_azathioprevised'],
    ['field' => 'bilag_mycophenolcurrent',   'label' => 'Mycophenolate',       'revised' => 'bilag_mycophenolrevised'],
    ['field' => 'bilag_cyclosporinacurrent', 'label' => 'CyclosporinA',        'revised' => 'bilag_cyclosporinarevised'],
    ['field' => 'bilag_tacrolimuscurrent',   'label' => 'Tacrolimus',          'revised' => 'bilag_tacrolimusrevised'],
    ['field' => 'bilag_prednisolcurrent',    'label' => 'Prednisolone',        'revised' => 'bilag_prednisolrevised'],
    ['field' => 'bilag_methotrexcurrent',    'label' => 'Methotrexate',        'revised' => 'bilag_methotrexrevised'],
    ['field' => 'bilag_ivigpulses',          'label' => 'IVG Pulses'],
    ['field' => 'bilag_rtxdose',             'label' => 'Rituximab'],
    ['field' => 'bilag_belimumdose',         'label' => 'Belimumab'],
    ['field' => 'bilag_cyclophoscumul_v3',   'label' => 'Cyclophosphamide cumulative'],
    ['field' => 'bilag_ivmepred',            'label' => 'IV MePred pulses since last visit'],
    ['field' => 'bilag_otherdrugs___7',      'label' => 'ACEi',                'type' => 'yesno'],
    ['field' => 'bilag_otherdrugs___5',      'label' => 'Ca** blockers',       'type' => 'yesno'],
];

$renderMedCell = static function (array $col, array $visitData) use ($mapValueToLabel, $parseDateValue): string {
    $type = $col['type'] ?? 'value';
    $field = $col['field'];

    if ($type === 'date') {
        $d = $parseDateValue($visitData[$field] ?? '');
        return $d instanceof \DateTimeImmutable ? $d->format('d-M-Y') : '-';
    }

    if ($type === 'yesno') {
        return ($visitData[$field] ?? '0') === '1' ? 'Yes' : 'No';
    }

    $current = $mapValueToLabel($field, $visitData[$field] ?? '');
    if ($current === '') {
        return '-';
    }

    if (isset($col['revised'])) {
        $revised = $mapValueToLabel($col['revised'], $visitData[$col['revised']] ?? '');
        if ($revised !== '') {
            return $current . ' [' . $revised . ']';
        }
    }

    return $current;
};

$bilagScoreHistory = [];
foreach (array_reverse($allBilagVisits) as $visit) {
    $raw = trim((string)($visit['data']['total_bilag_score'] ?? ''));
    if ($raw !== '' && is_numeric($raw)) {
        $score = (float)$raw;
        $bilagScoreHistory[] = [
            'label' => $visit['date']->format('m-Y'),
            'value' => (fmod($score, 1.0) === 0.0) ? (int)$score : $score,
        ];
    }
}

$bilagHistoryColumns = [
    ['field' => 'bilag_date',         'label' => 'Visit Date',       'type' => 'date'],
    ['field' => 'bilag_const2004_new','label' => 'Constitutional'],
    ['field' => 'bilag_muco2004_new', 'label' => 'Mucocutaneous'],
    ['field' => 'bilag_neuro2004_new','label' => 'Neuropsychiatric'],
    ['field' => 'bilag_musc2004_new', 'label' => 'Musculoskeletal'],
    ['field' => 'bilag_cardio2004_new','label' => 'Cardiorespiratory'],
    ['field' => 'bilag_gastro2004_new','label' => 'Gastrointestinal'],
    ['field' => 'bilag_ophthal2004_new','label' => 'Opthalmic'],
    ['field' => 'bilag_renal2004_new','label' => 'Renal'],
    ['field' => 'bilag_haem2004_new', 'label' => 'Haematological'],
];

$buildBilagHistory = static function (string $field) use ($allBilagVisits): array {
    $labels = [];
    $values = [];
    foreach (array_reverse($allBilagVisits) as $visit) {
        $raw = trim((string)($visit['data'][$field] ?? ''));
        if ($raw !== '' && is_numeric($raw)) {
            $labels[] = $visit['date']->format('M-Y');
            $values[] = (float)$raw;
        }
    }
    return ['labels' => $labels, 'values' => $values];
};

$esrHistory   = $buildBilagHistory('bilag_othesr');
$c3History    = $buildBilagHistory('bilag_othc3');
$c4History    = $buildBilagHistory('bilag_othc4');
$dsdnaHistory = $buildBilagHistory('bilag_othdsdna');

$bpLabels = [];
$bpSystolic = [];
$bpDiastolic = [];
foreach (array_reverse($allBilagVisits) as $visit) {
    $sys = trim((string)($visit['data']['bilag_sysbp'] ?? ''));
    $dia = trim((string)($visit['data']['bilag_diabp'] ?? ''));
    $hasSys = $sys !== '' && is_numeric($sys);
    $hasDia = $dia !== '' && is_numeric($dia);
    if ($hasSys || $hasDia) {
        $bpLabels[]    = $visit['date']->format('M-Y');
        $bpSystolic[]  = $hasSys ? (float)$sys : null;
        $bpDiastolic[] = $hasDia ? (float)$dia : null;
    }
}
?>

<style>
    /* small, reusable brand color */
    .ukjsle-brand {
        color: #213180;
    }

    .brand-sub {
        color: #a9ad8e;
    }

    /* Visual style for value boxes (not form controls) */
    .value-box {
        display: inline-block;
        width: 100%;
        padding: .45rem .6rem;
        background: #f8f9fa; /* light */
        border: 1px solid #e9ecef;
        border-radius: .375rem;
        text-align: center;
        font-weight: 500;
    }

    /* Make small label text slightly lighter */
    .field-label {
        font-style: italic;
        color: #6c757d;
        font-size: .88rem;
        display: block;
        margin-bottom: .25rem;
    }

    /* Header style for small section titles */
    .section-title {
        margin-bottom: .5rem;
        font-weight: 700;
        color: #213180;
        font-size: .95rem;
    }

    /* Renal grid helper: adjust number of columns at breakpoints */
    .renal-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: .75rem;
    }
    .renal-grid .field-label {
        white-space: nowrap;
    }
    @media (min-width: 576px) { /* sm and up */
        .renal-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (min-width: 768px) { /* md and up */
        .renal-grid { grid-template-columns: repeat(5, 1fr); }
    }

    /* Keep superscript readable inside value-box */
    .value-box sup { font-size: .6em; vertical-align: super; }
</style>

<div class="container-fluid">

    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card bg-info bg-opacity-25 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center g-3">
                            <!-- Left Logo -->
                            <div class="col-12 col-md-2 text-center text-md-start">
                                <img
                                        src="<?php echo $em->getUrl('pages/images/uol_logo.png'); ?>"
                                        alt="The University of Liverpool Logo"
                                        class="img-fluid"
                                        style="max-height: 80px;"
                                />
                            </div>

                            <!-- Title and Version -->
                            <div class="col-12 col-md-6 text-center text-md-start">
                                <h2 class="ukjsle-brand mb-1">JSLE: Study Dashboard</h2>
                                <div class="brand-sub small">Version 0.1 &middot; Oct 2025</div>
                            </div>

                            <!-- Right Logos: side-by-side on md+, stacked centered on small screens -->
                            <div class="col-12 col-md-4">
                                <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-3">
                                    <img
                                            src="<?php echo $em->getUrl('pages/images/ertc_logo.jpg'); ?>"
                                            alt="The ERTC Logo"
                                            class="img-fluid"
                                            style="max-height: 70px;"
                                    />
                                    <img
                                            src="<?php echo $em->getUrl('pages/images/JSLE-Logo.jpg'); ?>"
                                            alt="The JSLE Logo"
                                            class="img-fluid"
                                            style="max-height: 70px;"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mr-5">
        &#160;
    </div>

    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h4 ukjsle-brand mb-2">Welcome to the UKJSLE Study Dashboard</h1>
                        <p class="mb-0 text-muted">
                            This is a prototype of a study dashboard for the UKJSLE study. It aims to replicate the
                            functionality of the reports that were available via the study website.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mr-5">
        &#160;
    </div>

    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h3 class="" style="color: #213180">Clinical Patient Summary Report</h3>
                                <h6>
                                    <label for="ppt_it">Please select the participant whose summary report you would
                                        like to view:</label>
                                    <select id="ppt_it" class="form-control">
                                        <?php
                                        foreach ($recordIds as $recordId) {
                                            $selected = ((string)$recordId === (string)$selectedRecordId) ? ' selected' : '';
                                            echo "<option value=\"" . htmlspecialchars((string)$recordId, ENT_QUOTES, 'UTF-8') . "\"$selected>" . htmlspecialchars((string)$recordId, ENT_QUOTES, 'UTF-8') . "</option>";
                                        }
                                        ?>
                                    </select>
                                </h6>
                                <?php if (empty($recordIds)) { ?>
                                    <div class="alert alert-warning mt-2 mb-0">
                                        No accessible records were found for your account.
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="row">
                            <hr/>
                        </div>

                        <div class="row">
                            <div class="row-12">
                                <div class="card">
                                    <div class="row">
                                        <div class="col-12">
                                            &#160;
                                        </div>
                                    </div>

                                    <div class="container py-2">
                                        <div class="row gy-3 align-items-start">
                                            <!-- Gender -->
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                    <span class="fw-semibold me-sm-2 mb-1 mb-sm-0 text-nowrap">Gender:</span>
                                                    <span class="px-3 py-2 bg-light border rounded w-100 w-sm-auto text-center text-sm-start"><?php echo htmlspecialchars($participantGender !== '' ? $participantGender : '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </div>

                                            <!-- Ethnicity -->
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                    <span class="fw-semibold me-sm-2 mb-1 mb-sm-0 text-nowrap">Ethnicity:</span>
                                                    <span class="px-3 py-2 bg-light border rounded w-100 w-sm-auto text-center text-sm-start"><?php echo htmlspecialchars($participantEthnicity !== '' ? $participantEthnicity : '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </div>

                                            <!-- Last Visit -->
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                    <span class="fw-semibold me-sm-2 mb-1 mb-sm-0 text-nowrap">Last Visit:</span>
                                                    <span class="px-3 py-2 bg-light border rounded w-100 w-sm-auto text-center text-sm-start"><?php echo htmlspecialchars($participantLastVisit !== '' ? $participantLastVisit : '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            &#160;
                                            <br/>
                                            <hr/>
                                        </div>
                                    </div>

                                    <div class="container py-2">
                                        <div class="row gy-3">
                                            <!-- Latest Growth -->
                                            <div class="col-12 col-md-4">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Latest Growth</div>

                                                    <div class="row g-2">
                                                        <div class="col-6 col-sm-6">
                                                            <div class="field-label">Height (cm)</div>
                                                            <div id="ppt_height" class="value-box"><?php echo htmlspecialchars($participantHeight !== '' ? $participantHeight : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>

                                                        <div class="col-6 col-sm-6">
                                                            <div class="field-label">Weight (kg)</div>
                                                            <div id="ppt_weight" class="value-box"><?php echo htmlspecialchars($participantWeight !== '' ? $participantWeight : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Last recorded ACR score -->
                                            <div class="col-12 col-md-4">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Last recorded ACR score</div>

                                                    <div class="row g-2">
                                                        <div class="col-6 col-sm-6">
                                                            <div class="field-label">Recorded At</div>
                                                            <div id="ppt_acr_at" class="value-box"><?php echo htmlspecialchars($participantAcrRecordedAt !== '' ? $participantAcrRecordedAt : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>

                                                        <div class="col-6 col-sm-6">
                                                            <div class="field-label">ACR Score</div>
                                                            <div id="ppt_acr_score" class="value-box"><?php echo htmlspecialchars($participantAcrScore !== '' ? $participantAcrScore : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Renal -->
                                            <div class="col-12 col-md-4">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Renal</div>

                                                    <!-- Responsive grid: 2 cols on xs, 3 on sm, 5 on md+ -->
                                                    <div class="renal-grid">
                                                        <div>
                                                            <div class="field-label">Systolic BP</div>
                                                            <div id="ppt_systolic" class="value-box"><?php echo htmlspecialchars($participantSystolicBp !== '' ? $participantSystolicBp : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>

                                                        <div>
                                                            <div class="field-label">Diastolic BP</div>
                                                            <div id="ppt_diastolic" class="value-box"><?php echo htmlspecialchars($participantDiastolicBp !== '' ? $participantDiastolicBp : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>

                                                        <div>
                                                            <div class="field-label">Dipstick</div>
                                                            <div id="ppt_dipstick" class="value-box">0<sup>**</sup></div>
                                                        </div>

                                                        <div>
                                                            <div class="field-label">uACR</div>
                                                            <div id="ppt_uacr" class="value-box"><?php echo htmlspecialchars($participantUacr !== '' ? $participantUacr : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>

                                                        <div>
                                                            <div class="field-label">uPCR</div>
                                                            <div id="ppt_pacr" class="value-box"><?php echo htmlspecialchars($participantUpcr !== '' ? $participantUpcr : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="container py-2">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Latest BILAG scores</div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm align-middle mb-0">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th>Latest BILAG</th>
                                                            <th>Constitutional</th>
                                                            <th>Mucocutaneous</th>
                                                            <th>Neuropsychiatric</th>
                                                            <th>Musculoskeletal</th>
                                                            <th>Cardiorespiratory</th>
                                                            <th>Gastrointestinal</th>
                                                            <th>Opthalmic</th>
                                                            <th>Renal</th>
                                                            <th>Haematological</th>
                                                            <th>Total</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($participantLatestBilagDate !== '' ? $participantLatestBilagDate : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagConstitutional !== '' ? $participantBilagConstitutional : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagMucocutaneous !== '' ? $participantBilagMucocutaneous : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagNeuropsychiatric !== '' ? $participantBilagNeuropsychiatric : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagMusculoskeletal !== '' ? $participantBilagMusculoskeletal : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagCardiorespiratory !== '' ? $participantBilagCardiorespiratory : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagGastrointestinal !== '' ? $participantBilagGastrointestinal : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagOpthalmic !== '' ? $participantBilagOpthalmic : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagRenal !== '' ? $participantBilagRenal : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagHaematological !== '' ? $participantBilagHaematological : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars($participantBilagTotal !== '' ? $participantBilagTotal : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="blood-results" class="container py-2">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Latest Blood Results</div>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle mb-0">
                                                            <thead class="table-light">
                                                            <tr>
                                                                <th>Visit Date</th>
                                                                <th>Haemoglobin</th>
                                                                <th>WCC</th>
                                                                <th>Neutrophils</th>
                                                                <th>Lymphocytes</th>
                                                                <th>CRP</th>
                                                                <th>ESR</th>
                                                                <th>C3</th>
                                                                <th>C4</th>
                                                                <th>dsDNA</th>
                                                                <th>Creatinine</th>
                                                                <th>IgG</th>
                                                                <th>IgA</th>
                                                                <th>IgM</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($participantBloodVisitDate !== '' ? $participantBloodVisitDate : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodHaemoglobin !== '' ? $participantBloodHaemoglobin : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodWcc !== '' ? $participantBloodWcc : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodNeutrophils !== '' ? $participantBloodNeutrophils : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodLymphocytes !== '' ? $participantBloodLymphocytes : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodCrp !== '' ? $participantBloodCrp : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodEsr !== '' ? $participantBloodEsr : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodC3 !== '' ? $participantBloodC3 : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodC4 !== '' ? $participantBloodC4 : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodDsdna !== '' ? $participantBloodDsdna : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodCreatinine !== '' ? $participantBloodCreatinine : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodIgg !== '' ? $participantBloodIgg : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodIga !== '' ? $participantBloodIga : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantBloodIgm !== '' ? $participantBloodIgm : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="section-title mt-3">Auto Antibody Profile - From last Annual Visit</div>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle mb-0">
                                                            <thead class="table-light">
                                                            <tr>
                                                                <th colspan="8" class="border-bottom-0"></th>
                                                                <th colspan="2" class="text-center border-bottom-0">Thyroid</th>
                                                                <th colspan="2" class="text-center border-bottom-0">Biomarkers</th>
                                                            </tr>
                                                            <tr>
                                                                <th>Annual Visit date</th>
                                                                <th>ANA titre</th>
                                                                <th>dsDNA titre</th>
                                                                <th>antiSM</th>
                                                                <th>antiRNP</th>
                                                                <th>antiRo</th>
                                                                <th>antiLa</th>
                                                                <th>All ENA -ve</th>
                                                                <th>TSH</th>
                                                                <th>T4</th>
                                                                <th>Cholesterol</th>
                                                                <th>Triglycerides</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($participantStuffVisitDate !== '' ? $participantStuffVisitDate : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffAnaTitre !== '' ? $participantStuffAnaTitre : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffDsdnaTitre !== '' ? $participantStuffDsdnaTitre : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffAntiSm !== '' ? $participantStuffAntiSm : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffAntiRnp !== '' ? $participantStuffAntiRnp : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffAntiRo !== '' ? $participantStuffAntiRo : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffAntiLa !== '' ? $participantStuffAntiLa : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffAllEna !== '' ? $participantStuffAllEna : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffTsh !== '' ? $participantStuffTsh : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffT4 !== '' ? $participantStuffT4 : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffCholesterol !== '' ? $participantStuffCholesterol : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars($participantStuffTriglycerides !== '' ? $participantStuffTriglycerides : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="medications" class="container py-2">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Medications<?php if (!empty($recentBilagVisits)): ?> (Last <?php echo count($recentBilagVisits); ?> Visit<?php echo count($recentBilagVisits) !== 1 ? 's' : ''; ?>)<?php endif; ?></div>
                                                    <?php if (empty($recentBilagVisits)): ?>
                                                        <p class="text-muted mb-0">No BILAG visit data available.</p>
                                                    <?php else:
                                                        $medCols1 = array_slice($medTableColumns, 0, 8);
                                                        $medCols2 = array_merge([$medTableColumns[0]], array_slice($medTableColumns, 8));
                                                    ?>
                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-bordered table-sm align-middle mb-0">
                                                            <thead class="table-light">
                                                            <tr>
                                                                <?php foreach ($medCols1 as $col): ?>
                                                                    <th><?php echo htmlspecialchars($col['label'], ENT_QUOTES, 'UTF-8'); ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php foreach ($recentBilagVisits as $visit): ?>
                                                            <tr>
                                                                <?php foreach ($medCols1 as $col): ?>
                                                                    <td><?php echo htmlspecialchars($renderMedCell($col, $visit['data']), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle mb-0">
                                                            <thead class="table-light">
                                                            <tr>
                                                                <?php foreach ($medCols2 as $col): ?>
                                                                    <th><?php echo htmlspecialchars($col['label'], ENT_QUOTES, 'UTF-8'); ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php foreach ($recentBilagVisits as $visit): ?>
                                                            <tr>
                                                                <?php foreach ($medCols2 as $col): ?>
                                                                    <td><?php echo htmlspecialchars($renderMedCell($col, $visit['data']), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="bilag-score-chart-section" class="container py-2">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Total BILAG Score Over Time</div>
                                                    <?php if (empty($bilagScoreHistory)): ?>
                                                        <p class="text-muted mb-0">No BILAG score data available.</p>
                                                    <?php else: ?>
                                                        <canvas id="bilag-score-chart" style="max-height:450px;"></canvas>
                                                        <script>
                                                        (function () {
                                                            var labels = <?php echo json_encode(array_column($bilagScoreHistory, 'label')); ?>;
                                                            var scores = <?php echo json_encode(array_column($bilagScoreHistory, 'value')); ?>;
                                                            new Chart(document.getElementById('bilag-score-chart'), {
                                                                type: 'line',
                                                                data: {
                                                                    labels: labels,
                                                                    datasets: [{
                                                                        label: 'Total BILAG Score',
                                                                        data: scores,
                                                                        borderColor: '#213180',
                                                                        backgroundColor: 'rgba(33, 49, 128, 0.1)',
                                                                        fill: true,
                                                                        tension: 0.1,
                                                                        pointRadius: 4,
                                                                        pointHoverRadius: 6,
                                                                    }]
                                                                },
                                                                options: {
                                                                    responsive: true,
                                                                    maintainAspectRatio: true,
                                                                    plugins: {
                                                                        legend: { display: false }
                                                                    },
                                                                    scales: {
                                                                        x: {
                                                                            title: { display: true, text: 'Visit Date (MM-YYYY)' }
                                                                        },
                                                                        y: {
                                                                            title: { display: true, text: 'Total BILAG Score' },
                                                                            beginAtZero: true
                                                                        }
                                                                    }
                                                                }
                                                            });
                                                        })();
                                                        </script>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="bilag-history" class="container py-2">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="p-3 h-100 border rounded-3">
                                                    <div class="section-title">Latest BILAG History<?php if (!empty($recentBilagVisits)): ?> (Last <?php echo count($recentBilagVisits); ?> Visit<?php echo count($recentBilagVisits) !== 1 ? 's' : ''; ?>)<?php endif; ?></div>
                                                    <?php if (empty($recentBilagVisits)): ?>
                                                        <p class="text-muted mb-0">No BILAG visit data available.</p>
                                                    <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle mb-0">
                                                            <thead class="table-light">
                                                            <tr>
                                                                <?php foreach ($bilagHistoryColumns as $col): ?>
                                                                    <th><?php echo htmlspecialchars($col['label'], ENT_QUOTES, 'UTF-8'); ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php foreach ($recentBilagVisits as $visit): ?>
                                                            <tr>
                                                                <?php foreach ($bilagHistoryColumns as $col): ?>
                                                                    <td><?php echo htmlspecialchars($renderMedCell($col, $visit['data']), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                        <p>
                                                            BILAG Scores calculated based on BILAG 2004 criteria.
                                                            <br/>
                                                            <em>Clinical Summary indicators: A = "severe, active"; B = "Moderate, resolving"; C = "Mild, stable"; D = "Previous involvement"; E = "No previous involvement"</em>
                                                        </p>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="biomarker-plots" class="container py-2">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="p-3 border rounded-3">
                                                    <div class="section-title mb-3">Biomarker &amp; Clinical Trends</div>
                                                    <script>
                                                    function ukjsleMakeLine(id, labels, datasets, yLabel) {
                                                        var el = document.getElementById(id);
                                                        if (!el) return;
                                                        new Chart(el, {
                                                            type: 'line',
                                                            data: { labels: labels, datasets: datasets },
                                                            options: {
                                                                responsive: true,
                                                                maintainAspectRatio: false,
                                                                plugins: { legend: { display: datasets.length > 1 } },
                                                                scales: {
                                                                    x: { ticks: { maxRotation: 45, minRotation: 45 } },
                                                                    y: { title: { display: !!yLabel, text: yLabel || '' }, beginAtZero: false }
                                                                }
                                                            }
                                                        });
                                                    }
                                                    </script>
                                                    <div class="row g-3">

                                                        <!-- ESR History -->
                                                        <div class="col-12 col-md-4">
                                                            <div class="border rounded p-2 h-100">
                                                                <div class="section-title">ESR History</div>
                                                                <?php if (empty($esrHistory['labels'])): ?>
                                                                    <p class="text-muted small mb-0">No data available.</p>
                                                                <?php else: ?>
                                                                    <div style="height:220px;">
                                                                        <canvas id="esr-chart"></canvas>
                                                                    </div>
                                                                    <script>
                                                                    ukjsleMakeLine('esr-chart',
                                                                        <?php echo json_encode($esrHistory['labels']); ?>,
                                                                        [{ data: <?php echo json_encode($esrHistory['values']); ?>, borderColor: '#213180', backgroundColor: 'rgba(33,49,128,0.1)', fill: true, tension: 0.1, pointRadius: 3, spanGaps: false }],
                                                                        'ESR'
                                                                    );
                                                                    </script>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- C3 History -->
                                                        <div class="col-12 col-md-4">
                                                            <div class="border rounded p-2 h-100">
                                                                <div class="section-title">C3 History</div>
                                                                <?php if (empty($c3History['labels'])): ?>
                                                                    <p class="text-muted small mb-0">No data available.</p>
                                                                <?php else: ?>
                                                                    <div style="height:220px;">
                                                                        <canvas id="c3-chart"></canvas>
                                                                    </div>
                                                                    <script>
                                                                    ukjsleMakeLine('c3-chart',
                                                                        <?php echo json_encode($c3History['labels']); ?>,
                                                                        [{ data: <?php echo json_encode($c3History['values']); ?>, borderColor: '#213180', backgroundColor: 'rgba(33,49,128,0.1)', fill: true, tension: 0.1, pointRadius: 3, spanGaps: false }],
                                                                        'C3'
                                                                    );
                                                                    </script>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- C4 History -->
                                                        <div class="col-12 col-md-4">
                                                            <div class="border rounded p-2 h-100">
                                                                <div class="section-title">C4 History</div>
                                                                <?php if (empty($c4History['labels'])): ?>
                                                                    <p class="text-muted small mb-0">No data available.</p>
                                                                <?php else: ?>
                                                                    <div style="height:220px;">
                                                                        <canvas id="c4-chart"></canvas>
                                                                    </div>
                                                                    <script>
                                                                    ukjsleMakeLine('c4-chart',
                                                                        <?php echo json_encode($c4History['labels']); ?>,
                                                                        [{ data: <?php echo json_encode($c4History['values']); ?>, borderColor: '#213180', backgroundColor: 'rgba(33,49,128,0.1)', fill: true, tension: 0.1, pointRadius: 3, spanGaps: false }],
                                                                        'C4'
                                                                    );
                                                                    </script>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- Blood Pressure -->
                                                        <div class="col-12 col-md-4">
                                                            <div class="border rounded p-2 h-100">
                                                                <div class="section-title">Blood Pressure</div>
                                                                <?php if (empty($bpLabels)): ?>
                                                                    <p class="text-muted small mb-0">No data available.</p>
                                                                <?php else: ?>
                                                                    <div style="height:220px;">
                                                                        <canvas id="bp-chart"></canvas>
                                                                    </div>
                                                                    <script>
                                                                    ukjsleMakeLine('bp-chart',
                                                                        <?php echo json_encode($bpLabels); ?>,
                                                                        [
                                                                            { label: 'Systolic',  data: <?php echo json_encode($bpSystolic); ?>,  borderColor: '#dc3545', backgroundColor: 'transparent', tension: 0.1, pointRadius: 3, spanGaps: false },
                                                                            { label: 'Diastolic', data: <?php echo json_encode($bpDiastolic); ?>, borderColor: '#213180', backgroundColor: 'transparent', tension: 0.1, pointRadius: 3, spanGaps: false }
                                                                        ],
                                                                        'mmHg'
                                                                    );
                                                                    </script>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- dsDNA History -->
                                                        <div class="col-12 col-md-4">
                                                            <div class="border rounded p-2 h-100">
                                                                <div class="section-title">dsDNA History</div>
                                                                <?php if (empty($dsdnaHistory['labels'])): ?>
                                                                    <p class="text-muted small mb-0">No data available.</p>
                                                                <?php else: ?>
                                                                    <div style="height:220px;">
                                                                        <canvas id="dsdna-chart"></canvas>
                                                                    </div>
                                                                    <script>
                                                                    ukjsleMakeLine('dsdna-chart',
                                                                        <?php echo json_encode($dsdnaHistory['labels']); ?>,
                                                                        [{ data: <?php echo json_encode($dsdnaHistory['values']); ?>, borderColor: '#213180', backgroundColor: 'rgba(33,49,128,0.1)', fill: true, tension: 0.1, pointRadius: 3, spanGaps: false }],
                                                                        'dsDNA'
                                                                    );
                                                                    </script>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- Type (placeholder) -->
                                                        <div class="col-12 col-md-4">
                                                            <div class="border rounded p-2 h-100">
                                                                <div class="section-title">Type</div>
                                                                <p class="text-muted small mb-0">Coming soon.</p>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<script>
    (function () {
        const participantSelect = document.getElementById('ppt_it');
        if (!participantSelect) {
            return;
        }

        participantSelect.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('record_id', participantSelect.value);
            window.location.href = url.toString();
        });
    })();
</script>