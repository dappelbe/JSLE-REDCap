<?php
/**
 * -----------------------------------------------------------------------------
 * File: RecordHomePageService.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 * This class holds methods that perform operations on the record_home.php code
 *
 * @package   UoL\JSLE\Services
 * @author    orms0734
 * @version   0.0.1
 * @created   08/12/2025 17:33
 * -----------------------------------------------------------------------------
 */

namespace UoL\JSLE\Services;

use DateTime;
use Sabre\VObject\Property\VCard\Date;

class RecordHomePageService
{
    private int $project_id;
    private array $data;
    private array $events;
    private string $baseline_event_name = 'Year 0 - Baseline';
    private $bilag_date_field = 'bilag_date';
    private $acr_date_field = 'ascc_date';
    private $annual_date_field = 'aa_date';
    private $sledai_date_field = 'sledai_date';
    private $chaq_date_field = 'chaq_date';
    private $sf36_date_field = 'sf36_date';
    private $diagnosis_date = 'demo_diag_date';
    private $dob_date = 'demo_dob';

    public function __construct( array $record_data, array $event_details, int $project_id)
    {
        $this->data = $record_data;
        $this->events = $event_details;
        $this->project_id = $project_id;
    }

    function findEventId(array $events, string $name): int {
        foreach ($events as $id => $event) {
            if ($event['name'] === $name) {
                return $id;
            }
        }
        return -1;
    }

    public function getRegistrationAndDischargeDates(string $record_id) : void {
        $registrationDate = ' - ';
        $dischargeDate = ' - ';
        $reg_date_var = 'cons_date';
        $discharge_date_fld = 'disch_date';

        if ( array_key_exists($record_id, $this->data) ) {
            $baselineEventId = $this->findEventId($this->events, 'Year 0 - Baseline');
            $dischargeEventId = $this->findEventId($this->events, 'Discharge');

            if (
                ( array_key_exists($baselineEventId, $this->data[$record_id]) )
                && ( array_key_exists($reg_date_var, $this->data[$record_id][$baselineEventId]))
            ) {
                $registrationDate = $this->data[$record_id][$baselineEventId][$reg_date_var];
            }
            if (
                ( array_key_exists($dischargeEventId, $this->data[$record_id]) )
                && ( array_key_exists($discharge_date_fld, $this->data[$record_id][$dischargeEventId]) )
            ) {
                $dischargeDate = $this->data[$record_id][$dischargeEventId][$discharge_date_fld];
            }

            if ( $registrationDate !== '' && $dischargeDate !== ' - ' ) {
                $this->data['pptinfo']['registrationdate'] = (new DateTime($registrationDate))->format('d-m-Y');
            }
            if ( $dischargeDate !== '' && $dischargeDate !== ' - ' ) {
                $this->data['pptinfo']['dischargedate'] = (new DateTime($dischargeDate))->format('d-m-Y');
            }

        }
    }

    /**
     * This function looks to see if the annual or bilag forms exist, if so extract the actual date
     * and append that to the visit title, if not generate an expected visit date to be displayed.
     *
     * @param array $event_mapping -> A list of events in the project
     * @param string|null $event_name -> The name of the event holding the participants DoB
     * @param string|null $variable_name -> the variable holding the participants DoB
     * @param int $displaytype -> 1 = Actual date, 2 = Expected date, 3 = Both
     * @return array -> The array to be parsed by the JavaScript library for action on page load.
     * @throws \Exception
     */
    public function getVisitLabels( array $event_mapping,
                                    string $event_name = null,
                                    string $variable_name = null,
                                    int $displaytype = 1 ) : array
    {
        if ( (isset($event_mapping) && is_array($event_mapping) && count($event_mapping) > 0)
            && (isset($event_name) && $event_name !== '')
            && (isset($variable_name) && $variable_name !== '')  )
        {
            $this->data['text2replace'] = [];
            $record_id = array_key_first($this->data);


            $actual_date = [];
            $baselineDate = '';
            $dob_datetime = '';
            $eventDate = '';

            $baselineEventId = $this->findEventId($this->events, $this->baseline_event_name);
            $dob_datetime = $this->getDOBDateTime($baselineEventId, $record_id);
            $isLinked = $this->isLinked($baselineEventId, $record_id);
            if ( $isLinked ) {
                $baselineEventId = $this->findEventId($this->events, 'Year 1 - Annual');
            }
            $baselineDate = $this->getEventDate( $baselineEventId, $record_id, true );

            foreach ($this->events as $key => $event)
            {
                $eventDate = $this->getEventDate($key, $record_id);
                $currentLabel = $event['name'];
                $new_label = $this->getNewLabel( $baselineDate, $dob_datetime, $eventDate, $currentLabel, $displaytype, $isLinked);
                $this->data['text2replace'][] = ['text' => $currentLabel, 'replace' => $new_label];
            }

            return $this->data;
        }
        return [];
    }

    /***
     * @param string $baselineDate
     * @param DateTime $dob
     * @param string $currentDate
     * @param string $currentLabel
     * @param int $displayType
     * @return string
     * @throws \Exception
     */
    public function getNewLabel( string $baselineDate, DateTime $dob, string $currentDate, string $currentLabel, int $displayType, bool $isLinked ): string
    {

        if ( $baselineDate === ''
            || str_contains($currentLabel, 'dditional')
            || str_contains($currentLabel, 'MAS')
        ) {
            return $currentLabel;
        }

        $baselineDateTime = new DateTime($baselineDate);
        $firstVisitDateTime = new DateTime($baselineDate);
        $firstVisitDateTime->setDate(
            (int)$baselineDateTime->format('Y'),
            (int)$dob->format('m'),
            (int)$dob->format('d')
        );

        // If birthday is on/before first visit, move first visit to next year
        if ( !$isLinked ) {
            if ($firstVisitDateTime <= $baselineDateTime) {
                $firstVisitDateTime->modify('+1 year');
            }
            //-- if the birthday date is within three months of the first visit we need to push back a year
            $diff = $firstVisitDateTime->diff($baselineDateTime);
            $months = ($diff->y * 12) + $diff->m;
            if ($months < 3) {
                $firstVisitDateTime->modify('+1 year');
            }
        }
        $expectedVisitDateTime = $firstVisitDateTime->modify('+' . (int)($this->extractYearNumber($currentLabel) -1) . ' year');
        $expectedLabel = ' (Expected: ' . $expectedVisitDateTime->format('m-Y') . ')';
        if ( $isLinked && str_contains($currentLabel, '1 - Annual') ) {
            $expectedLabel = '';
        }

        //-- Format the Actual visit date
        $actualVisitLabel = '';
        if ( $currentDate !== '' ) {
            $currentDateTime = new DateTime($currentDate);
            $newLabelDate = $currentDateTime->format('d-m-Y');
            $actualVisitLabel = ' (Actual: ' . $newLabelDate . ')';
        }

        //-- If Quarterly only display actual date
        if ( str_contains($currentLabel, 'Q') || str_contains($currentLabel, 'Baseline')) {
            switch ($displayType) {
                case 1: //-- Std REDCap display
                    return $currentLabel;
                case 2: //-- Expected date
                case 3: //-- Expected and actual date
                    return $currentLabel . $actualVisitLabel;
            }
        } else if ( str_contains($currentLabel, 'ischarge')
                ) {
            switch ($displayType) {
                case 1: //-- Std REDCap display
                    return $currentLabel;
                case 2: //-- Expected date
                    return $currentLabel;
                    break;
                case 3: //-- Expected and actual date
                    return $currentLabel . $actualVisitLabel;
            }

        } else {
            switch ($displayType) {
                case 1: //-- Std REDCap display
                    return $currentLabel;
                case 2: //-- Expected date
                    return $currentLabel . $expectedLabel;
                    break;
                case 3: //-- Expected and actual date
                    return $currentLabel . $expectedLabel . $actualVisitLabel;
            }
        }

        return '';
    }

    public function extractYearNumber(string $text): ?int {
        if (preg_match('/\bYear\s+(\d+)\b/i', $text, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /**
     * Consent form: If we only have a consent form REDCap incorrectly displays todays date.
     * If we used the date of consent folk could miss adding any retrospective data from the date of diagnosis, so I am thinking if we only have a consent form we leave the Actual date blank.
     * Demographic form: If we only have consent and a demographic form please display the date of diagnosis from the demographic form.
     * Baseline CRFs: If we have further forms can the baseline column look for these in the following order: BILAG, ACR, AA, SLEDAI, CHAQ, SF36?
     *
     * @param int $event_id
     * @param string $record_id
     * @return string
     * @throws \Exception
     */
    public function getEventDate( int $event_id, string $record_id ) : string {
        if ( array_key_exists($record_id, $this->data)
            &&
            array_key_exists($event_id, $this->data[$record_id])
            ) {

            if (array_key_exists($this->bilag_date_field, $this->data[$record_id][$event_id])
                 &&
                $this->data[$record_id][$event_id][$this->bilag_date_field] !== '') {
               return (new DateTime($this->data[$record_id][$event_id][$this->bilag_date_field]))->format('d-m-Y');
           }

            if (array_key_exists($this->acr_date_field, $this->data[$record_id][$event_id])
                &&
                $this->data[$record_id][$event_id][$this->acr_date_field] !== ''
            ) {
                return (new DateTime($this->data[$record_id][$event_id][$this->acr_date_field]))->format('d-m-Y');
            }

            if (array_key_exists($this->annual_date_field, $this->data[$record_id][$event_id])
                &&
                $this->data[$record_id][$event_id][$this->annual_date_field] !== ''
            ) {
                return (new DateTime($this->data[$record_id][$event_id][$this->annual_date_field]))->format('d-m-Y');
            }

            if (array_key_exists($this->sledai_date_field, $this->data[$record_id][$event_id])
                &&
                $this->data[$record_id][$event_id][$this->sledai_date_field] !== ''
            ) {
                return (new DateTime($this->data[$record_id][$event_id][$this->sledai_date_field]))->format('d-m-Y');
            }

            if (array_key_exists($this->chaq_date_field, $this->data[$record_id][$event_id])
                &&
                $this->data[$record_id][$event_id][$this->chaq_date_field] !== ''
            ) {
                return (new DateTime($this->data[$record_id][$event_id][$this->chaq_date_field]))->format('d-m-Y');
            }

            if (array_key_exists($this->sf36_date_field, $this->data[$record_id][$event_id])
                &&
                $this->data[$record_id][$event_id][$this->sf36_date_field] !== ''
            ) {
                return (new DateTime($this->data[$record_id][$event_id][$this->sf36_date_field]))->format('d-m-Y');
            }

            if (array_key_exists($this->diagnosis_date, $this->data[$record_id][$event_id])
                &&
                $this->data[$record_id][$event_id][$this->diagnosis_date] !== ''
            ) {
                return (new DateTime($this->data[$record_id][$event_id][$this->diagnosis_date]))->format('d-m-Y');
            }

            //-- Discharge
            if (array_key_exists('disch_date', $this->data[$record_id][$event_id])
                &&
                $this->data[$record_id][$event_id]['disch_date'] !== ''
            ) {
                return (new DateTime($this->data[$record_id][$event_id]['disch_date']))->format('d-m-Y');
            }


        }
        return '';
    }

    /**
     * Get the date of birth
     * @param int $event_id
     * @param string $record_id
     * @return DateTime
     * @throws \Exception
     */
    public function getDOBDateTime( int $event_id, string $record_id ) : DateTime {
        $dob = '';
        if ( array_key_exists($event_id, $this->data[$record_id]) ) {
            //-- Check if we are a linked record
            $old_record = $this->data[$record_id][$event_id]["demo_oldstudyno"];
            if ($old_record == '' ) {
                $dob = $this->data[$record_id][$event_id][$this->dob_date];
            } else {
                $td = \REDCap::getData(PROJECT_ID, 'array', $old_record, $this->dob_date, $event_id);
                $dob = $td[$old_record][$event_id][$this->dob_date];
            }
        }
        return new DateTime($dob);
    }

    /***
     * @param int $event_id
     * @param string $record_id
     * @return bool
     */
    public function isLinked( int $event_id, string $record_id ) : bool {
        if ( array_key_exists($event_id, $this->data[$record_id]) ) {
            $old_record = $this->data[$record_id][$event_id]["demo_oldstudyno"];
            if ( $old_record !== '' ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recursively look for the var we are interested in
     * @param $event_id
     * @param $fld
     * @param $array2search
     * @return array
     */
    public function getValues($event_id, $fld, $array2search): array
    {
        $sumVal = array();

        foreach ($array2search as $key=>$val) {
            if ( $event_id === $key ) {
                if ( array_key_exists( $fld, $val ) )
                {
                    $sumVal[] = $val[$fld];
                }
                else
                {
                    $sumVal[] = $this->getValues(-1, $fld, $val);
                }
            } else {
                if (is_array($val)) {
                    if ( array_key_exists( $fld, $val ) && $event_id === '-1' )
                    {
                        $sumVal[] = $val[$fld];
                    }
                    else
                    {
                        //-- So the expected format is
                        //-- => record_id
                        //--        => visit
                        //--              => form
                        $ids = array_keys($val);
                        if ( count($ids) === 1 ) {
                            $id = $ids[0];
                            foreach ( $val[$id] as $visit => $repeats ) {
                                foreach ( $repeats as $idx => $instance ) {
                                    if ( array_key_exists( $fld, $instance ) )
                                    {
                                        $sumVal[] = $instance[$fld];
                                    }
                                    else
                                    {
                                        $sumVal[] = $this->getValues(-1, $fld, $instance);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $sumVal;
    }

    public function copyTransferedData(int $project_id, \Project $project) : bool {
        $previous_study_id_variable = 'demo_oldstudyno';
        $data_not_to_copy = ['demo_version', 'demo_data', 'demo_islinked', 'demo_oldstudyno'];
        $keys = array_keys($this->data);

        //-- Get the current record id
        $current_id = '';
        if ( count($keys) > 0 ) {
            $current_id = $keys[0];
        }

        //-- get the baseline data event
        if ( array_key_exists($current_id, $this->data) ) {
            $keys = array_keys($this->data[$current_id]);
            $baseline_event_id = $keys[0];
            if ( array_key_exists($previous_study_id_variable, $this->data[$current_id][$baseline_event_id]) ) {
                if ($this->data[$current_id][$baseline_event_id][$previous_study_id_variable] !== '' ) {
                    $old_study_id_variable = $this->data[$current_id][$baseline_event_id][$previous_study_id_variable];
                    //-- Have we copied data - just check the bilag
                    if ( array_key_exists('bilag_date', $this->data[$current_id][$baseline_event_id]) ) {
                        if ($this->data[$current_id][$baseline_event_id]['bilag_date'] === '') {
                            //-- We need to copy the data
                            //-- Get the previous data
                            $data_2_copy = array();
                            $previous_data = \REDCap::getData($project_id, 'array', $previous_study_id_variable);
                            if ( !empty($previous_data) ) {
                                if (array_key_exists($previous_study_id_variable, $previous_data) ) {
                                    if ( array_key_exists( $baseline_event_id, $previous_data[$previous_study_id_variable] ) ) {
                                        $data_2_copy[$current_id] = array();
                                        $data_2_copy[$current_id][$baseline_event_id] = array();
                                        $baseline_keys = array_keys($this->data[$current_id][$baseline_event_id]);
                                        foreach ( $baseline_keys as $bl_key ) {
                                            if ( !array_key_exists($bl_key, $data_not_to_copy)) {
                                                if ( array_key_exists($bl_key, $previous_data[$previous_study_id_variable][$baseline_event_id])) {
                                                    $data_2_copy[$current_id][$baseline_event_id][$bl_key] = $previous_data[$previous_study_id_variable][$baseline_event_id][$bl_key];
                                                }
                                            }
                                        }
                                    }
                                }
                                //-- Now we need to overwrite
                                //-- Save the data
                                \REDCap::SaveData($project_id, 'array', $data_2_copy);
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}