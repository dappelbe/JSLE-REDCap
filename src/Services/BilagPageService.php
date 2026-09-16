<?php
/**
 * -----------------------------------------------------------------------------
 * File: BilagPageService.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 * Used to handle BILAG page work
 *
 * @package   UoL\JSLE\Services
 * @author    orms0734
 * @version   0.0.1
 * @created   27/02/2026 13:44
 * -----------------------------------------------------------------------------
 */

namespace UoL\JSLE\Services;

use DateTimeImmutable;

class BilagPageService
{
    private array $data;
    private array $events;
    private array $formFields;

    private array $last_renal;
    private array $last_haem;

    public function __construct(array $record_data, array $events, array $fieldsOnForm)
    {
        $this->data = $record_data;
        $this->events = $events;
        $this->formFields = $fieldsOnForm;
        $this->initialiseLastRenal();
    }

    public function initialiseLastRenal() {
        $this->last_renal = array();
        $this->last_renal['bilag_studyid'] = '';
        $this->last_renal['bilag_24hrurinaryprot'] = 0;  // 102c
        $this->last_renal['bilag_renalurinaryprotcr'] = 0; // 102b
        $this->last_renal['bilag_renalurinaryalbcr'] = 0; // 102a
        $this->last_renal['bilag_renalsevhypertension'] = 0; // 99 1/0
        $this->last_renal['bilag_renalcreatinine'] = 0; // 105
        $this->last_renal['bilag_renalgfrest'] = 0; // 106b
        $this->last_renal['bilag_renalactiveurinesed'] = 0; // 107 1/0
        $this->last_renal['bilag_renalnephritis'] = 0; // 108 1/0
        $this->last_renal['bilag_renalnephroticsynd'] = 0; // 104 1/0
        $this->last_renal['bilag_sysbp'] = 0; // Systolic bP
        $this->last_renal['bilag_diabp'] = 0; // Diastolic bp
        $this->last_renal['bilag_renal2004_today'] = 0; // Diastolic bp
    }

    public function extractBilagData(int $project_id, string $record_id) : array {
        $bilagData = \REDCap::getData($project_id, 'array', $record_id,
            array('record_id', 'bilag_date', 'bilag_today_const', 'bilag_muco_today', 'bilag_neuro2004_today',
                'bilag_musc2004_today', 'bilag_today_cardio', 'bilag_renal2004_today', 'bilag_gastro2004_today',
                'bilag_ophthal2004_today', 'bilag_haem2004_today'));
        return $bilagData;
    }
    public function extractCurrentEventDate(array $bilagData, string $record_id, string $event_id, int $repeat) : string
    {
        //-- Process the bilagData to extract relevant information
        $currentDate = date('Y-m-d');
        if ( array_key_exists($record_id, $bilagData) ) {
            if ( array_key_exists($event_id, $bilagData[$record_id]) ) {
                if (array_key_exists('bilag_date', $bilagData[$record_id][$event_id])) {
                    if ($bilagData[$record_id][$event_id]['bilag_date'] !== '') {
                        $currentDate = $bilagData[$record_id][$event_id]['bilag_date'];
                    }
                }
            } else {
                if ( array_key_exists('repeat_instances', $bilagData[$record_id])) {
                    if (array_key_exists($event_id, $bilagData[$record_id]['repeat_instances'])) {
                        if (array_key_exists('bilag', $bilagData[$record_id]['repeat_instances'][$event_id])) {
                            if (array_key_exists($repeat, $bilagData[$record_id]['repeat_instances'][$event_id]['bilag'])) {
                                if (array_key_exists('bilag_date', $bilagData[$record_id]['repeat_instances'][$event_id]['bilag'][$repeat])) {
                                    if ($bilagData[$record_id]['repeat_instances'][$event_id]['bilag'][$repeat]['bilag_date'] !== '') {
                                        $currentDate = $bilagData[$record_id]['repeat_instances'][$event_id]['bilag'][$repeat]['bilag_date'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $currentDate;
    }
    public function previousBilagInvolvement(int $project_id, string $record_id, string $event_id, int $repeat) : array
    {
        $bilagData = $this->extractBilagData($project_id, $record_id);
        $currentDate = $this->extractCurrentEventDate($bilagData, $record_id, $event_id, $repeat);
        $processed =  $this->processParticipantData($bilagData);
        $filtered = $this->filterProcessedParticipantDataByDateWindow($processed, $currentDate);
        //-- Ok, lets look to see if we are a linked ppt, if so we need to check that data as well.
        //-- Baseline event is

        $previous_map = array();
        $previous_map['bilag_const2004_prev'] = 'bilag_today_const';
        $previous_map['bilag_muco2004_prev'] = 'bilag_muco_today';
        $previous_map['bilag_neuro2004_prev'] = 'bilag_neuro2004_today';
        $previous_map['bilag_musc2004_prev'] = 'bilag_musc2004_today';
        $previous_map['bilag_cardio2004_prev'] = 'bilag_today_cardio';
        $previous_map['bilag_renal2004_prev'] = 'bilag_renal2004_today';
        $previous_map['bilag_gastro2004_prev'] = 'bilag_gastro2004_today';
        $previous_map['bilag_ophthal2004_prev'] = 'bilag_ophthal2004_today';
        $previous_map['bilag_haem2004_prev'] = 'bilag_haem2004_today';

        $prevInv = $this->processPreviousBilagMap($previous_map, $filtered);

        $baseline_event_id = -1;
        foreach( $this->events as $key => $event) {
            if ( $event['name'] === 'Year 0 - Baseline') {
                $baseline_event_id = $key;
                continue;
            }
        }
        if ( array_key_exists( $baseline_event_id, $this->data[$record_id] ) ) {
            if ( array_key_exists( 'demo_oldstudyno', $this->data[$record_id][$baseline_event_id] ) ){
                if (trim($this->data[$record_id][$baseline_event_id]['demo_oldstudyno']) !== '') {
                    $old_study_id = trim($this->data[$record_id][$baseline_event_id]['demo_oldstudyno']);
                    $oldBilagData = $this->extractBilagData($project_id, $old_study_id);
                    $processedOld =  $this->processParticipantData($oldBilagData);
                    foreach( $processedOld as $d => $values ) {
                        foreach ( $values as $k => $v ) {
                            if ( $k === 'bilag_today_const' ) {
                                $ptr = 'bilag_const2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_muco_today' ) {
                                $ptr = 'bilag_muco2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_neuro2004_today' ) {
                                $ptr = 'bilag_neuro2004_prev';
                                if ( $v !== ''  && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_musc2004_today' ) {
                                $ptr = 'bilag_musc2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_today_cardio' ) {
                                $ptr = 'bilag_cardio2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_renal2004_today' ) {
                                $ptr = 'bilag_renal2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_gastro2004_today' ) {
                                $ptr = 'bilag_gastro2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_ophthal2004_today' ) {
                                $ptr = 'bilag_ophthal2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            } else if ( $k === 'bilag_haem2004_today' ) {
                                $ptr = 'bilag_haem2004_prev';
                                if ( $v !== '' && $v !== '0' && $prevInv[$ptr] !== '0')
                                {
                                    $prevInv[$ptr] = $v;
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->data['previous_bilag_map'] = $prevInv;

        return $this->data;
    }

    public function processPreviousBilagMap(array $previousMap, array $filtered): array
    {
        $bilag_prev = [];

        foreach ($previousMap as $targetField => $sourceField) {
            $bilag_prev[$targetField] = 0;

            foreach ($filtered as $fields) {
                $value = null;

                if (is_array($fields) && array_key_exists($sourceField, $fields)) {
                    $value = $fields[$sourceField];
                } elseif (
                    is_array($fields)
                    && array_key_exists('field_name', $fields)
                    && (string)$fields['field_name'] === (string)$sourceField
                    && array_key_exists('value', $fields)
                ) {
                    $value = $fields['value'];
                }

                if ($value !== null && (float)$value > 0) {
                    $bilag_prev[$targetField] = 1;
                    break;
                }
            }
        }

        return $bilag_prev;
    }

    public function processParticipantData(array $bilagData, string $dateField = 'bilag_date') : array
    {
        $processed = [];

        foreach ($bilagData as $recordData) {
            foreach ($recordData as $eventId => $eventData) {
                if ($eventId === 'repeat_instances') {
                    foreach ($eventData as $repeatEventId => $forms) {
                        foreach ($forms as $instances) {
                            foreach ($instances as $repeatInstance => $fields) {
                                if (!is_array($fields) || !array_key_exists($dateField, $fields) || $fields[$dateField] === '' || $fields[$dateField] === null) {
                                    continue;
                                }

                                $date = $fields[$dateField];
                                foreach ($fields as $fieldName => $value) {
                                    $processed[$date][$fieldName] = $value;
                                }
                                $processed[$date]['event_id'] = $repeatEventId;
                                $processed[$date]['repeat_instance'] = (string) $repeatInstance;
                            }
                        }
                    }
                    continue;
                }

                if (!is_array($eventData) || !array_key_exists($dateField, $eventData) || $eventData[$dateField] === '' || $eventData[$dateField] === null) {
                    continue;
                }

                $date = $eventData[$dateField];
                foreach ($eventData as $fieldName => $value) {
                    $processed[$date][$fieldName] = $value;
                }
                $processed[$date]['event_id'] = $eventId;
                $processed[$date]['repeat_instance'] = '';
            }
        }

        return $processed;
    }

    public function filterProcessedParticipantDataByDateWindow(
        array $processedData,
        string $currentDate,
        string $dateField = 'bilag_date'
    ): array {

        ksort($processedData, SORT_REGULAR);
        $filtered = array_filter(
            $processedData,
            function ($key) use ($currentDate) {
                return $key < $currentDate;
            },
            ARRAY_FILTER_USE_KEY
        );

        return $filtered;
    }

    private function normaliseDate(string $value): ?string
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    public function calculateRenalScore(int $project_id, string $record_id, int $event_id, int $repeat_instance) : array {
        $last_data = [];

        $bilagData = \REDCap::getData($project_id, 'array', $record_id,
            array('record_id', 'bilag_date', 'bilag_24hrurinaryprot', 'bilag_renalurinaryprotcr', 'bilag_proteinuria',
                'bilag_renalsevhypertension', 'bilag_renalcreatinine', 'bilag_renalgfrest', 'bilag_renalactiveurinesed',
                'bilag_renalnephritis', 'bilag_renalnephroticsynd', 'bilag_sysbp', 'bilag_diabp', 'bilag_renal2004_today'));

        $currentDate = date('Y-m-d');
        if ( array_key_exists($record_id, $bilagData) ) {
            if ( array_key_exists($event_id, $bilagData[$record_id]) ) {
                if (array_key_exists('bilag_date', $bilagData[$record_id][$event_id])) {
                    if ($bilagData[$record_id][$event_id]['bilag_date'] !== '') {
                        $currentDate = $bilagData[$record_id][$event_id]['bilag_date'];
                    }
                }
            } else {
                if ( array_key_exists('repeat_instances', $bilagData[$record_id])) {
                    if (array_key_exists($event_id, $bilagData[$record_id]['repeat_instances'])) {
                        if (array_key_exists('bilag', $bilagData[$record_id]['repeat_instances'][$event_id])) {
                            if (array_key_exists($repeat_instance, $bilagData[$record_id]['repeat_instances'][$event_id]['bilag'])) {
                                if (array_key_exists('bilag_date', $bilagData[$record_id]['repeat_instances'][$event_id]['bilag'][$repeat_instance])) {
                                    if ($bilagData[$record_id]['repeat_instances'][$event_id]['bilag'][$repeat_instance]['bilag_date'] !== '') {
                                        $currentDate = $bilagData[$record_id]['repeat_instances'][$event_id]['bilag'][$repeat_instance]['bilag_date'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $processed = $this->processParticipantData($bilagData);
        $filtered = $this->filterProcessedParticipantDataByDateWindow($processed, $currentDate);

        if ( count($filtered) > 0 ) {
            $last_key = array_key_last($filtered);
            $this->last_renal = $filtered[$last_key];
            //-- check to see if the date of the previous visit was within three months of the current one.
            $first  = new DateTimeImmutable($this->last_renal['bilag_date']);
            $second = new DateTimeImmutable($currentDate);

            $isLessThanThreeMonthsAfter =
                $second > $first &&
                $second <= $first->modify('+3 months');

            if (!$isLessThanThreeMonthsAfter) {
                $this->last_renal['bilag_renalnephritis'] = '';
            }
        }

        //-- now return the data to the page
        $this->data['lastRenal'] = $this->last_renal;
        return $this->data;
    }

    public function getDateOfCurrentVisit(string $record_id, int $event_id, int $repeat_instance, string $date_fld = 'aa_date') : string {
        $last_date = '1970-01-01';
        $form_name = 'annual_assessment';
        $currentDate = date('Y-m-d');

        //-- Get the current forms date
        if ( array_key_exists($record_id, $this->data) ) {
            if (array_key_exists($event_id, $this->data[$record_id])) {
                if (array_key_exists($date_fld, $this->data[$record_id][$event_id])) {
                    if ($this->data[$record_id][$event_id][$date_fld] !== '') {
                        $currentDate = $this->data[$record_id][$event_id][$date_fld];
                    }
                }
            } else {
                if (array_key_exists('repeat_instances', $this->data[$record_id])) {
                    if (array_key_exists($event_id, $this->data[$record_id]['repeat_instances'])) {
                        if (array_key_exists($form_name, $this->data[$record_id]['repeat_instances'][$event_id])) {
                            if (array_key_exists($repeat_instance, $this->data[$record_id]['repeat_instances'][$event_id][$form_name])) {
                                if (array_key_exists($date_fld, $this->data[$record_id]['repeat_instances'][$event_id][$form_name][$repeat_instance])) {
                                    if ($this->data[$record_id]['repeat_instances'][$event_id][$form_name][$repeat_instance][$date_fld] !== '') {
                                        $currentDate = $this->data[$record_id]['repeat_instances'][$event_id][$form_name][$repeat_instance][$date_fld];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $currentDate;
    }

    public function getLastRTX(string $record_id,
                               int $event_id,
                               int $repeat_instance,
                               string $currentDate,
                               array $orderedBILAGData,
                               string $index = 'rtx',
                               string $key1 = 'bilag_rtxdose',
                               string $key2 = 'bilag_rtxnuminfusions',
                               string $key3 = 'bilag_rtxnumcycles_v4_5',
                               string $key4 = 'bilag_rtxdose',
                               string $key5 = 'bilag_rtxdate1',
                               string $key6 = 'bilag_rtxdate2',
                               string $key7 = 'bilag_rtxdate3',
                               string $key8 = 'bilag_rtxdate4',
                               string $key9 = 'bilag_rtxnotes'
    ) : array {

        $value_2_add = array();
        foreach ($orderedBILAGData as $date => $row) {
            if ($date >= $currentDate) {
                break;
            }
            $allBlank = true;
            $value_2_add['date'] = $date;
            $value_2_add[$key1] = $row[$key1];
            if ( $row[$key1] !== '' ) $allBlank = false;
            $value_2_add[$key2] = $row[$key2];
            if ( $row[$key2] !== '' ) $allBlank = false;
            $value_2_add[$key3] = $row[$key3];
            if ( $row[$key3] !== '' ) $allBlank = false;
            $value_2_add[$key4] = $row[$key4];
            if ( $row[$key4] !== '' ) $allBlank = false;
            $value_2_add[$key5] = $row[$key5];
            if ( $row[$key5] !== '' ) $allBlank = false;
            $value_2_add[$key6] = $row[$key6];
            if ( $row[$key6] !== '' ) $allBlank = false;
            $value_2_add[$key7] = $row[$key7];
            if ( $row[$key7] !== '' ) $allBlank = false;
            $value_2_add[$key8] = $row[$key8];
            if ( $row[$key8] !== '' ) $allBlank = false;
            $value_2_add[$key9] = $row[$key9];
            if ( $row[$key9] !== '' ) $allBlank = false;

            if ( !$allBlank ) {
                $this->data[$index][] = $value_2_add;
            }
        }


        return $this->data;
    }

    public function getLastBelimumab(string $record_id,
                               int $event_id,
                               int $repeat_instance,
                               string $currentDate,
                               array $orderedBILAGData,
                               string $index = 'belimumab',
                               string $key1 = 'bilag_belimumdose',
                               string $key2 = 'bilag_belimumroute',
                               string $key3 = 'bilag_belimumfreq',
                               string $key4 = 'bilag_belimumstartdate',
                               string $key5 = 'bilag_belimumstopdate',
                               string $key6 = 'bilag_belimumnuminfus_v6',
                               string $key7 = 'bilag_belimumrevdose_v6',
                               string $key8 = 'bilag_belimumdate1_v6',
                                     string $key9 = 'bilag_belimumdate2_v6',
                                     string $key10 = 'bilag_belimumnotes'
    ) : array {

        $value_2_add = array();
        foreach ($orderedBILAGData as $date => $row) {
            if ($date >= $currentDate) {
                break;
            }
            $allBlank = true;
            $value_2_add['date'] = $date;
            $value_2_add[$key1] = $row[$key1];
            if ( $row[$key1] !== '' ) $allBlank = false;
            $value_2_add[$key2] = $row[$key2];
            if ( $row[$key2] !== '' ) {
                $allBlank = false;
                if ( $value_2_add[$key2] === '1') {
                    $value_2_add[$key2] = 'IV';
                } else {
                    $value_2_add[$key2] = 'Oral';
                }
            }
            $value_2_add[$key3] = $row[$key3];
            if ( $row[$key3] !== '' ) $allBlank = false;
            $value_2_add[$key4] = $row[$key4];
            if ( $row[$key4] !== '' ) $allBlank = false;
            $value_2_add[$key5] = $row[$key5];
            if ( $row[$key5] !== '' ) $allBlank = false;
            $value_2_add[$key6] = $row[$key6];
            if ( $row[$key6] !== '' ) $allBlank = false;
            $value_2_add[$key7] = $row[$key7];
            if ( $row[$key7] !== '' ) $allBlank = false;
            $value_2_add[$key8] = $row[$key8];
            if ( $row[$key8] !== '' ) $allBlank = false;
            $value_2_add[$key9] = $row[$key9];
            if ( $row[$key9] !== '' ) $allBlank = false;
            $value_2_add[$key10] = $row[$key10];
            if ( $row[$key10] !== '' ) $allBlank = false;

            if ( !$allBlank ) {
                $this->data[$index][] = $value_2_add;
            }
        }


        return $this->data;
    }

    public function getLastCyclo(string $record_id,
                                     int $event_id,
                                     int $repeat_instance,
                                     string $currentDate,
                                     array $orderedBILAGData,
                                     string $index = 'cyclo',
                                     string $key1 = 'bilag_cyclophosdose',
                                     string $key2 = 'bilag_cyclophosnuminfus',
                                     string $key3 = 'bilag_cyclophosroute',
                                     string $key4 = 'bilag_cyclophosdose1',
                                     string $key5 = 'bilag_cyclophosdate1',
                                     string $key6 = 'bilag_cyclophosdose2',
                                     string $key7 = 'bilag_cyclophosdate2',
                                     string $key8 = 'bilag_cyclophosdose3',
                                     string $key9 = 'bilag_cyclophosdate3',
                                     string $key10 = 'bilag_cyclophosnotes'
    ) : array {

        $value_2_add = array();
        foreach ($orderedBILAGData as $date => $row) {
            if ($date >= $currentDate) {
                break;
            }
            $allBlank = true;
            $value_2_add['date'] = $date;
            $value_2_add[$key1] = $row[$key1];
            if ( $row[$key1] !== '' ) $allBlank = false;
            $value_2_add[$key2] = $row[$key2];
            if ( $row[$key2] !== '' ) $allBlank = false;
            $value_2_add[$key3] = $row[$key3];
            if ( $row[$key3] !== '' ) {
                $allBlank = false;
                if ( $value_2_add[$key3] === '1') {
                    $value_2_add[$key3] = 'IV';
                } else {
                    $value_2_add[$key3] = 'Oral';
                }
            }
            $value_2_add[$key4] = $row[$key4];
            if ( $row[$key4] !== '' ) $allBlank = false;
            $value_2_add[$key5] = $row[$key5];
            if ( $row[$key5] !== '' ) $allBlank = false;
            $value_2_add[$key6] = $row[$key6];
            if ( $row[$key6] !== '' ) $allBlank = false;
            $value_2_add[$key7] = $row[$key7];
            if ( $row[$key7] !== '' ) $allBlank = false;
            $value_2_add[$key8] = $row[$key8];
            if ( $row[$key8] !== '' ) $allBlank = false;
            $value_2_add[$key9] = $row[$key9];
            if ( $row[$key9] !== '' ) $allBlank = false;
            $value_2_add[$key10] = $row[$key10];
            if ( $row[$key10] !== '' ) $allBlank = false;

            if ( !$allBlank ) {
                $this->data[$index][] = $value_2_add;
            }
        }


        return $this->data;
    }


    public function getLastIVMP(string $record_id,
                                int $event_id,
                                int $repeat_instance,
                                string $currentDate,
                                array $orderedBILAGData,
                                string $index = 'ivmp',
                                string $key1 = 'bilag_ivmepredpulses',
                                string $key2 = 'bilag_ivmepreddate1',
                                string $key3 = 'bilag_ivmepreddose',
                                string $key4 = 'bilag_ivmepreddate2',
                                string $key5 = 'bilag_ivmepreddose_2',
                                string $key6 = 'bilag_ivmepreddate3',
                                string $key7 = 'bilag_ivmepreddose_3',
                                string $key8 = 'bilag_ivmepred_an',
                                string $key9 = 'bilag_ivmeprednotes',
    ) : array {

        $value_2_add = array();
        foreach ($orderedBILAGData as $date => $row) {
            if ($date >= $currentDate) {
                break;
            }
            $allBlank = true;
            $value_2_add['date'] = $date;
            $value_2_add[$key1] = $row[$key1];
            if ( $row[$key1] !== '' ) $allBlank = false;
            $value_2_add[$key2] = $row[$key2];
            if ( $row[$key2] !== '' ) $allBlank = false;
            $value_2_add[$key3] = $row[$key3];
            if ( $row[$key3] !== '' ) $allBlank = false;
            $value_2_add[$key4] = $row[$key4];
            if ( $row[$key4] !== '' ) $allBlank = false;
            $value_2_add[$key5] = $row[$key5];
            if ( $row[$key5] !== '' ) $allBlank = false;
            $value_2_add[$key6] = $row[$key6];
            if ( $row[$key6] !== '' ) $allBlank = false;
            $value_2_add[$key7] = $row[$key7];
            if ( $row[$key7] !== '' ) $allBlank = false;
            $value_2_add[$key8] = $row[$key8];
            if ( $row[$key8] !== '' ) $allBlank = false;
            $value_2_add[$key9] = $row[$key9];
            if ( $row[$key9] !== '' ) $allBlank = false;

            if ( !$allBlank ) {
                $this->data[$index][] = $value_2_add;
            }
        }


        return $this->data;
    }

    public function getLastIVIG(string $record_id,
                                int $event_id,
                                int $repeat_instance,
                                string $currentDate,
                                array $orderedBILAGData,
                                string $index = 'ivig',
                                string $key1 = 'bilag_ivigpulses',
                                string $key2 = 'bilag_ivigcurdose',
                                string $key3 = 'bilag_ivigdate',
                                string $key4 = 'bilag_ivigcurdose_2',
                                string $key5 = 'bilag_ivigdate_2',
                                string $key6 = 'bilag_ivigdate_3',
                                string $key7 = 'bilag_ivigcurdose_3',
                                string $key8 = 'bilag_ivigcsr',
                                string $key9 = 'bilag_ivignotes',
    ) : array {

        $value_2_add = array();
        foreach ($orderedBILAGData as $date => $row) {
            if ($date >= $currentDate) {
                break;
            }
            $allBlank = true;
            $value_2_add['date'] = $date;
            $value_2_add[$key1] = $row[$key1];
            if ( $row[$key1] !== '' ) $allBlank = false;
            $value_2_add[$key2] = $row[$key2];
            if ( $row[$key2] !== '' ) $allBlank = false;
            $value_2_add[$key3] = $row[$key3];
            if ( $row[$key3] !== '' ) $allBlank = false;
            $value_2_add[$key4] = $row[$key4];
            if ( $row[$key4] !== '' ) $allBlank = false;
            $value_2_add[$key5] = $row[$key5];
            if ( $row[$key5] !== '' ) $allBlank = false;
            $value_2_add[$key6] = $row[$key6];
            if ( $row[$key6] !== '' ) $allBlank = false;
            $value_2_add[$key7] = $row[$key7];
            if ( $row[$key7] !== '' ) $allBlank = false;

            if ( $row[$key8] !== '' ) {
                $allBlank = false;
                $value_2_add[$key8] = $row[$key9];
                if ( $value_2_add[$key8] === '1') {
                    $value_2_add[$key8] = 'Commenced';
                } else if ($value_2_add[$key9] === '2') {
                    $value_2_add[$key8] = 'Stopped';
                } else {
                    $value_2_add[$key8] = 'Revised';
                }
            }

            if ( $row[$key9] !== '' ) $allBlank = false;
            $value_2_add[$key9] = $row[$key9];

            if ( !$allBlank ) {
                $this->data[$index][] = $value_2_add;
            }
        }


        return $this->data;
    }
}