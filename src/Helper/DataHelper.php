<?php
/**
 * -----------------------------------------------------------------------------
 * File: DataHelper.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 * This class is used to take a users data and to then return a date ordered array by instrument
 *
 * @package   UoL\JSLE\Helper
 * @author    orms0734
 * @version   0.0.1
 * @created   10/09/2026 13:09
 * -----------------------------------------------------------------------------
 */

namespace UoL\JSLE\Helper;

class DataHelper
{
    public array $processedData;
    private array $participantsData;
    private array $instrumentDictionary;
    private array $instrumentDateLookUp;
    public function __construct(array $rawData, array $instrumentsData ) {
        $this->processedData = array();
        $this->participantsData = $rawData;
        $this->instrumentDictionary = $instrumentsData;
        $this->populateDateLookUpTable();
        $this->reorganiseData();
    }

    protected function populateDateLookUpTable() {
        $this->instrumentDateLookUp['demographics'] = 'demo_first_saved';
        $this->instrumentDateLookUp['acr_slicc_classification'] = 'ascc_date';
        $this->instrumentDateLookUp['annual_assessment'] = 'aa_date';
        $this->instrumentDateLookUp['bilag'] = 'bilag_date';
        $this->instrumentDateLookUp['sledai_2k'] = 'sledai_date';
        $this->instrumentDateLookUp['chaq'] = 'chaq_date';
        $this->instrumentDateLookUp['samples'] = 'samples_date';
    }

    /***
     * Function to take the input data and reformat into record_id|instrument_name|date|aata
     * @return void
     */
    protected function reorganiseData() : void {
        if ( count($this->participantsData) > 0
            && count($this->instrumentDictionary) > 0
           ) {
            foreach ( $this->participantsData as $record_id => $visits ) {
                $this->processedData[$record_id] = array();
                foreach ( $visits as $visit_id => $data ) {
                    if ( $visit_id === 'repeat_instances') {
                        //- repeating visits/forms
                        foreach( $this->participantsData[$record_id][$visit_id] as $sub_visit => $instruments) {
                            foreach ( $instruments as $instrment_id => $responses ) {
                                if ( !array_key_exists($instrment_id, $this->processedData[$record_id] ) ) {
                                    $this->processedData[$record_id][$instrment_id] = array();
                                }
                                $instrumentFields = $this->instrumentDictionary[$instrment_id]['fields'];
                                $date_field = $this->instrumentDateLookUp[$instrment_id];
                                foreach ( $responses as $repeat => $answers ) {
                                    $date_value = $answers[$date_field];
                                    if ( $date_value !== '' ) {
                                        if ( !array_key_exists($date_value, $this->processedData[$record_id][$instrment_id] ) ) {
                                            $this->processedData[$record_id][$instrment_id][$date_value] = array();
                                        }
                                        foreach ($instrumentFields as $key => $description) {
                                            $this->processedData[$record_id][$instrment_id][$date_value][$key] = $answers[$key];
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        //-- Loop through all the instruments
                        foreach ( $this->instrumentDictionary as $instrument => $values ) {
                            $instrumentFields = $values['fields'];
                            if ( !array_key_exists($instrument, $this->processedData[$record_id] ) ) {
                                $this->processedData[$record_id][$instrument] = array();
                            }
                            if ( array_key_exists( $instrument, $this->instrumentDateLookUp) ) {
                                $date_field = $this->instrumentDateLookUp[$instrument];
                                if ( array_key_exists($date_field, $this->participantsData[$record_id][$visit_id]) ) {
                                    $date_value = $this->participantsData[$record_id][$visit_id][$date_field];
                                    if ($date_value !== '') {
                                        if ( !array_key_exists($date_value, $this->processedData[$record_id][$instrument] ) ) {
                                            $this->processedData[$record_id][$instrument][$date_value] = array();
                                        }
                                        foreach ($instrumentFields as $key => $description) {
                                            $this->processedData[$record_id][$instrument][$date_value][$key] = $this->participantsData[$record_id][$visit_id][$key];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ( $this->instrumentDictionary as $instrument => $vals ) {
                    if ( array_key_exists($instrument, $this->processedData[$record_id] ) &&
                        count( $this->processedData[$record_id][$instrument]) > 0
                        ) {
                        $tmpData = $this->processedData[$record_id][$instrument];
                        ksort( $tmpData, SORT_REGULAR);
                        $this->processedData[$record_id][$instrument] = $tmpData;
                    }
                }
            }
            //-- Sort the instruments by date in ascending date order
        }
    }

}