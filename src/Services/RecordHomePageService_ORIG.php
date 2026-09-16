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

class RecordHomePageService_OLD
{
    private int $project_id;
    private array $data;
    private array $events;

    public function __construct( array $record_data, array $event_details, int $project_id)
    {
        $this->data = $record_data;
        $this->events = $event_details;
        $this->project_id = $project_id;
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
    public function getVisitLabels( array $event_mapping, string $event_name = null, string $variable_name = null, int $displaytype = 1 ) : array
    {
        if ( (isset($event_mapping) && is_array($event_mapping) && count($event_mapping) > 0)
            && (isset($event_name) && $event_name !== '')
            && (isset($variable_name) && $variable_name !== '')  )
        {
            $this->data['text2replace'] = [];
            $ctr = 0;
            $dob_event_id = '';
            $dob = '';
            $firstVisit = '';

            //-- Only generate the actual date if there is a demographic form in place
            $bilag_date_field = 'bilag_date';
            $acr_date_field = 'ascc_date';
            $annual_date_field = 'aa_date';
            $demmo_date_field = 'demo_diag_date';

            foreach ($this->events as $key => $event)
            {
                if ( $event['name'] == $event_name ) {
                    $dob_event_id = $key;
                    break;
                }
            }


            $record_id = array_key_first($this->data);

            if ( array_key_exists($dob_event_id, $this->data[$record_id]) ) {
                $record = $this->data[$record_id][$dob_event_id]["demo_oldstudyno"];
                if ($record == '' ) {
                    $dob = $this->data[$record_id][$dob_event_id][$variable_name];
                } else {
                    $td = \REDCap::getData(PROJECT_ID, 'array', $record, $variable_name, $dob_event_id);
                    $dob = $td[$record][$dob_event_id][$variable_name];
                }
                $firstVisit = $this->data[$record_id][$dob_event_id][$demmo_date_field];

                if (array_key_exists($annual_date_field, $this->data[$record_id][$key])
                    &&
                    $this->data[$record_id][$key][$annual_date_field] !== ''
                ) {
                    $firstVisit = $this->data[$record_id][$key][$annual_date_field];
                } elseif (array_key_exists($bilag_date_field, $this->data[$record_id][$key])
                    &&
                    $this->data[$record_id][$key][$bilag_date_field] !== '') {
                    $firstVisit = $this->data[$record_id][$key][$bilag_date_field];
                } elseif (array_key_exists($acr_date_field, $this->data[$record_id][$key])
                    &&
                    $this->data[$record_id][$key][$acr_date_field] !== '') {
                    $firstVisit = $this->data[$record_id][$key][$bilag_date_field];
                }
            }

            $birthdayDate = new DateTime($dob);
            $firstVisitDate = new DateTime($firstVisit);
            //-- ==============================================================
            //-- We have two options for display:
            //-- (1) Ony have baseline, so calculate expected dates
            //-- (2) We have data, so we display the date from BILAG or Annual and then
            //--     calculated the expected ones going forwards.
            //-- ===============================================================
            $birthdayDate->setDate(
                (int)$firstVisitDate->format('Y'),
                (int)$birthdayDate->format('m'),
                (int)$birthdayDate->format('d')
            );
            // If birthday is on/before first visit, move to next year
            if ($birthdayDate <= $firstVisitDate) {
                $birthdayDate->modify('+1 year');
            }
            //-- if the birthday date is within three months of the first visit we need to push back a year
            $diff = $firstVisitDate->diff($birthdayDate);
            $months = ($diff->y * 12) + $diff->m;
            if ( $months < 3) {
                $birthdayDate->modify('+1 year');
            }

            $baseDate = $birthdayDate;
            $baseDate1 = $birthdayDate;
            $txt = 'Actual ';
            $hasActual = false;
            foreach ($this->events as $key => $event) {
                if ( $event['name'] === 'Year 0 - Baseline' ) {
                    $baseDate = $firstVisitDate;
                    $hasActual = true;
                } else {
                    if ( $displaytype === 1 ) {
                        if (array_key_exists($key, $this->data[$record_id])) {
                            if (array_key_exists($annual_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$annual_date_field] !== ''
                            ) {
                                $txt = 'Actual ';
                                $baseDate = new DateTime($this->data[$record_id][$key][$annual_date_field]);
                            } elseif (array_key_exists($bilag_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$bilag_date_field] !== '') {
                                $txt = 'Actual ';
                                $baseDate = new DateTime($this->data[$record_id][$key][$bilag_date_field]);
                            } elseif (array_key_exists($acr_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$acr_date_field] !== '') {
                                $txt = 'Actual ';
                                $baseDate = new DateTime($this->data[$record_id][$key][$bilag_date_field]);
                            } elseif (array_key_exists($demmo_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$demmo_date_field] !== '') {
                                $txt = 'Actual ';
                                $baseDate = new DateTime($this->data[$record_id][$key][$demmo_date_field]);
                            } else {
                                $txt = 'Expected ';
                            }
                        } else {
                            //-- Option 1
                            $txt = 'Expected ';
                            if ($event['name'] === 'Year 1 - Annual') {
                                $baseDate = $birthdayDate;
                            } else {
                                if (str_starts_with($event['name'], 'Year') && str_ends_with($event['name'], 'Annual')) {
                                    $baseDate = clone $birthdayDate;
                                    //-- which year are we at
                                    $tmpStr = trim(str_replace(array('Year ', ' - Annual '), '', $event['name'])) - 1;
                                    $baseDate->modify('+' . $tmpStr . ' year');
                                }
                            }
                        }
                    } else if ( $displaytype === 2 ) {
                        $txt = 'Expected ';
                        if ($event['name'] === 'Year 1 - Annual') {
                            $baseDate = $birthdayDate;
                        } else {
                            if (str_starts_with($event['name'], 'Year') && str_ends_with($event['name'], 'Annual')) {
                                $baseDate = clone $birthdayDate;
                                //-- which year are we at
                                $tmpStr = trim(str_replace(array('Year ', ' - Annual '), '', $event['name'])) - 1;
                                $baseDate->modify('+' . $tmpStr . ' year');
                            }
                        }
                    } else {
                        $hasActual = false;
                        $txt = '';
                        if (array_key_exists($key, $this->data[$record_id])) {
                            if (array_key_exists($annual_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$annual_date_field] !== ''
                            ) {
                                $hasActual = true;
                                $txt = 'Actual ';
                                $baseDate1 = new DateTime($this->data[$record_id][$key][$annual_date_field]);
                            } elseif (array_key_exists($bilag_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$bilag_date_field] !== '') {
                                $hasActual = true;
                                $txt = 'Actual ';
                                $baseDate1 = new DateTime($this->data[$record_id][$key][$bilag_date_field]);
                            } elseif (array_key_exists($acr_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$acr_date_field] !== '') {
                                $hasActual = true;
                                $txt = 'Actual ';
                                $baseDate1 = new DateTime($this->data[$record_id][$key][$bilag_date_field]);
                            } elseif (array_key_exists($demmo_date_field, $this->data[$record_id][$key])
                                &&
                                $this->data[$record_id][$key][$demmo_date_field] !== '') {
                                $hasActual = true;
                                $txt = 'Actual ';
                                $baseDate1 = new DateTime($this->data[$record_id][$key][$demmo_date_field]);
                            }
                        }

                        if ($event['name'] === 'Year 1 - Annual') {
                            $baseDate = $birthdayDate;
                        } else {
                            if (str_starts_with($event['name'], 'Year') && str_ends_with($event['name'], 'Annual')) {
                                $baseDate = clone $birthdayDate;
                                //-- which year are we at
                                $tmpStr2 = trim(str_replace(array('Year ', ' - Annual '), '', $event['name'])) - 1;
                                $baseDate->modify('+' . $tmpStr2 . ' year');
                            }
                        }
                    }
                }

                if ( strpos($event['name'], 'dditional') > 0
                    ||
                    strpos($event['name'], 'ischarge') > 0
                    ||
                    strpos($event['name'], 'MAS') > 0
                ) {
                    continue;
                }

                if (
                        strpos($event['name'], 'Q1') > 0
                        ||
                        strpos($event['name'], 'Q2') > 0
                        ||
                        strpos($event['name'], 'Q3') > 0
                ) {
                    $bd = clone $baseDate;
                    if ( strpos($event['name'], 'Q1') > 0 ) {
                        $bd->modify('+' . 3 . ' month');
                    }   else if ( strpos($event['name'], 'Q2') > 0 ) {
                        $bd->modify('+' . 6 . ' month');
                    } else {
                        $bd->modify('+' . 9 . ' month');
                    }

                    switch( $displaytype) {
                        case 1:
                        case 2:
                            if ( $hasActual ) {
                                $this->data['text2replace'][] = ["text" => $event['name'], "replace" => $event['name']
                                    . ' (Actual ' . $baseDate1->format('d-m-Y') . ')'];
                            }
                            break;
                        case 3:
                            if ( $hasActual ) {
                                $this->data['text2replace'][] = ["text" => $event['name'], "replace" => $event['name']
                                    . ' (Expected ' . $bd->format('m-Y') . ' - Actual ' . $baseDate1->format('d-m-Y') .')'];
                            }
                            break;
                    }
                } else {
                    switch( $displaytype) {
                        case 1:
                        case 2:
                            $this->data['text2replace'][] = ["text" => $event['name'], "replace" => $event['name']
                                . ' (' . $txt . $baseDate->format('m-Y') . ')'];
                            break;
                        case 3:
                            if ( $hasActual ) {
                                $this->data['text2replace'][] = ["text" => $event['name'], "replace" => $event['name']
                                    . ' (Actual ' . $baseDate1->format('d-m-Y') .')'];
                            } else {
                                $this->data['text2replace'][] = ["text" => $event['name'], "replace" => $event['name']
                                    . ' (Expected ' . $baseDate->format('m-Y') . ')'];
                            }
                            break;

                    }
                }
            }

            return $this->data;
        }

        return [];
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