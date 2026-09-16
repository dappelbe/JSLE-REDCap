<?php
/**
 * -----------------------------------------------------------------------------
 * File: SamplesPageService.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 * Perform operations on the Samples page
 *
 * @package   UoL\JSLE\Services
 * @author    orms0734
 * @version   0.0.1
 * @created   15/09/2026 13:25
 * -----------------------------------------------------------------------------
 */

namespace UoL\JSLE\Services;

use DateTime;

class SamplesPageService
{
    public function __construct(array $record_data, array $events)
    {
        $this->data = $record_data;
        $this->events = $events;
    }

    public function EDTAOrPMBC_PreviouslyTaken( array $orderedData, string $currentDate ) : array {
        $retVal = array();
        $hasEDTA = false;
        $hasPMBC = false;

        foreach( $orderedData as $vDate => $vData ) {
            $vDT = new DateTime($vDate);
            $cDT = new DateTime($currentDate);
            if ( $vDT < $cDT ) {
                if ( array_key_exists('samples', $vData) ) {
                    if ( $vData['samples']['1'] === '1' ) {
                        $hasEDTA = true;
                        $this->data['samples']['edta_date'] = $vDT->format('d-m-Y');
                    }
                    if ( $vData['samples']['2'] === '1' ) {
                        $hasPMBC = true;
                        $this->data['samples']['pmbc_date'] = $vDT->format('d-m-Y');
                    }
                }
            }
        }

        if ( $hasEDTA ) {
            $this->data['samples']['edta'] = '1';
        } else {
            $this->data['samples']['edta'] = '0';
        }
        if ( $hasPMBC ) {
            $this->data['samples']['pmbc'] = '1';
        } else {
            $this->data['samples']['pmbc'] = '0';
        }

        return $this->data;
    }
}