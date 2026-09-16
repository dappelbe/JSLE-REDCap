<?php
/**
 * -----------------------------------------------------------------------------
 * File: AnnualPageService.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 * Code to help with the code to support the Annual Assessment changes
 *
 * @package   UoL\JSLE\Services
 * @author    orms0734
 * @version   0.0.1
 * @created   06/02/2026 12:14
 * -----------------------------------------------------------------------------
 */

namespace UoL\JSLE\Services;

class AnnualPageService
{
    public array $data;
    private array $events;
    private array $formFields;

    public bool $isDataPresent;

    public function __construct(array $record_data, array $events, array $fieldsOnForm)
    {
        $this->data = $record_data;
        $this->events = $events;
        $this->formFields = $fieldsOnForm;
        $this->isDataPresent = false;
    }

    public function copyLastData2(string $record_id, int $event_id, int $repeat_instance) : array {
        $last_data = [];
        //-- TODO handle repeat instances
        $annual_study_num_field = 'aa_studyid';
        if ( array_key_exists($record_id, $this->data) ) {
            if ( !array_key_exists($event_id, $this->data[$record_id]) ) {
                foreach( $this->events as $key => $value ) {
                    if ( $key < $event_id) {
                        if ( array_key_exists($key, $this->data[$record_id]) ) {
                            if ($this->data[$record_id][$key][$annual_study_num_field] !== '') {
                                foreach ($this->formFields as $field_name => $field_text) {
                                    if ($field_name === 'aa_menarche'
                                        ||
                                        $field_name === 'aa_menarcheyrs'
                                        ||
                                        $field_name === 'aa_menarchemths'
                                        ||
                                        $field_name === 'aasd_ocu_oce'
                                        ||
                                        $field_name === 'aasd_ocu_rcoa'
                                        ||
                                        $field_name === 'aasd_neu_cimp'
                                        ||
                                        $field_name === 'aasd_neu_seiz'
                                        ||
                                        $field_name === 'aasd_neu_cvae'
                                        ||
                                        $field_name === 'aasd_neu_cpn'
                                        ||
                                        $field_name === 'aasd_neu_tm'
                                        ||
                                        $field_name === 'aasd_ren_gfr'
                                        ||
                                        $field_name === 'aasd_ren_prot'
                                        ||
                                        $field_name === 'aasd_ren_esrd'
                                        ||
                                        $field_name === 'aasd_pul_hyp'
                                        ||
                                        $field_name === 'aasd_pul_fib'
                                        ||
                                        $field_name === 'aasd_pul_sl'
                                        ||
                                        $field_name === 'aasd_pul_pleufib'
                                        ||
                                        $field_name === 'aasd_pul_infar'
                                        ||
                                        $field_name === 'aasd_car_acab'
                                        ||
                                        $field_name === 'aasd_car_mie'
                                        ||
                                        $field_name === 'aasd_car_cm'
                                        ||
                                        $field_name === 'aasd_car_vd'
                                        ||
                                        $field_name === 'aasd_car_peri'
                                        ||
                                        $field_name === 'aasd_per_clau'
                                        ||
                                        $field_name === 'aasd_per_mtl'
                                        ||
                                        $field_name === 'aasd_per_stle'
                                        ||
                                        $field_name === 'aasd_per_vt'
                                        ||
                                        $field_name === 'aasd_gas_infar'
                                        ||
                                        $field_name === 'aasd_gas_mi'
                                        ||
                                        $field_name === 'aasd_gas_cp'
                                        ||
                                        $field_name === 'aasd_gas_sugtse'
                                        ||
                                        $field_name === 'aasd_gas_pi'
                                        ||
                                        $field_name === 'aasd_mus_aw'
                                        ||
                                        $field_name === 'aasd_mus_dea'
                                        ||
                                        $field_name === 'aasd_mus_ostpor'
                                        ||
                                        $field_name === 'aasd_mus_an'
                                        ||
                                        $field_name === 'aasd_mus_ostmy'
                                        ||
                                        $field_name === 'aasd_mus_rt'
                                        ||
                                        $field_name === 'aasd_skin_alo'
                                        ||
                                        $field_name === 'aasd_skin_esp'
                                        ||
                                        $field_name === 'aasd_skin_ulc'
                                        ||
                                        $field_name === 'aasd_oth_diab'
                                        ||
                                        $field_name === 'aasd_oth_malig'
                                        ||
                                        $field_name === 'aasd_oth_pgfsa'
                                    ) {
                                        if (array_key_exists($field_name, $this->data[$record_id][$key])) {
                                            if ($this->data[$record_id][$key][$field_name] >= '1') {
                                                $last_data[$field_name] = $this->data[$record_id][$key][$field_name];
                                            }
                                        }
                                    }
                                    //-- Puberty scores of 5 need to be transferred
                                    if ( $field_name === 'aa_penisscrotscore'
                                        ||
                                        $field_name === 'aa_mpubhairscore'
                                        ||
                                        $field_name === 'aa_breastscore'
                                        ||
                                        $field_name === 'aa_fpubhairscore'
                                        ) {
                                        if (array_key_exists($field_name, $this->data[$record_id][$key])) {
                                            if ($this->data[$record_id][$key][$field_name] === '5') {
                                                $last_data[$field_name] = $this->data[$record_id][$key][$field_name];
                                                //-- ensure that the puberty details question is marked as yes to display this
                                                $last_data['aa_pubertypt'] = '1';
                                                $last_data['aa_menarcheyrs'] = $this->data[$record_id][$key]['aa_menarcheyrs'];
                                                $last_data['aa_menarchemths'] = $this->data[$record_id][$key]['aa_menarchemths'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //-- Now we have the previous data, we now need to squirt the data back to the page
                $retVal = [];
                $retVal['last_annual'] = $last_data;
                $this->data['last_annual'] = $last_data;
                return $retVal;
            }
        }

        return [];
    }

    public function copyLastData(string $record_id, int $event_id, int $repeat_instance) : array {
        $last_data = [];

        $valid_field_names = [
            'aa_menarche',
            'aa_menarcheyrs',
            'aa_menarchemths',
            'aasd_ocu_oce',
            'aasd_ocu_rcoa',
            'aasd_neu_cimp',
            'aasd_neu_seiz',
            'aasd_neu_cvae',
            'aasd_neu_cpn',
            'aasd_neu_tm',
            'aasd_ren_gfr',
            'aasd_ren_prot',
            'aasd_ren_esrd',
            'aasd_pul_hyp',
            'aasd_pul_fib',
            'aasd_pul_sl',
            'aasd_pul_pleufib',
            'aasd_pul_infar',
            'aasd_car_acab',
            'aasd_car_mie',
            'aasd_car_cm',
            'aasd_car_vd',
            'aasd_car_peri',
            'aasd_per_clau',
            'aasd_per_mtl',
            'aasd_per_stle',
            'aasd_per_vt',
            'aasd_gas_infar',
            'aasd_gas_mi',
            'aasd_gas_cp',
            'aasd_gas_sugtse',
            'aasd_gas_pi',
            'aasd_mus_aw',
            'aasd_mus_dea',
            'aasd_mus_ostpor',
            'aasd_mus_an',
            'aasd_mus_ostmy',
            'aasd_mus_rt',
            'aasd_skin_alo',
            'aasd_skin_esp',
            'aasd_skin_ulc',
            'aasd_oth_diab',
            'aasd_oth_malig',
            'aasd_oth_pgfsa',
        ];

        $annual_study_num_field = 'aa_studyid';
        $date_fld = 'aa_date';
        $last_date = '1970-01-01';
        $form_name = 'annual_assessment';
        $currentDate = date('Y-m-d');

        //-- Get the current forms date
        if ( array_key_exists($record_id, $this->data) ) {
            if (array_key_exists($event_id, $this->data[$record_id])) {
                if (array_key_exists($date_fld, $this->data[$record_id][$event_id])) {
                    if ($this->data[$record_id][$event_id][$date_fld] !== '') {
                        $currentDate = $this->data[$record_id][$event_id][$date_fld];
                        $this->isDataPresent = true;
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
                                        $this->isDataPresent = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($this->isDataPresent) {
            return [];
        }

        //-- Get all the previous data
        $keys = array_keys($this->data[$record_id]);
        foreach ( $keys as $evt ) {
            if ( $evt === 'repeat_instances' ) {
                $rpt_keys = array_keys($this->data[$record_id]['repeat_instances']);
                foreach ($rpt_keys as $rk) {
                    $repeat_forms = array_keys($this->data[$record_id]['repeat_instances'][$rk]);
                    foreach( $repeat_forms as $rf ) {
                        if ( $form_name === $rf) {
                            $num_repeats = array_keys($this->data[$record_id]['repeat_instances'][$rk][$form_name]);
                            foreach ($num_repeats as $nr) {
                                $tmp_data = [];
                                $form_data = $this->data[$record_id]['repeat_instances'][$rk][$form_name][$nr];
                                if (array_key_exists($date_fld, $form_data) && $form_data[$date_fld] !== '') {
                                    $form_date = $form_data[$date_fld];
                                    foreach ($valid_field_names as $name) {
                                        if (array_key_exists($name, $form_data)) {
                                            $tmp_data[$name] = $form_data[$name];
                                        }
                                    }

                                    foreach ($this->formFields as $field_name => $field_text) {
                                        //-- Puberty scores of 5 need to be transferred
                                        if ( $field_name === 'aa_penisscrotscore'
                                            ||
                                            $field_name === 'aa_mpubhairscore'
                                            ||
                                            $field_name === 'aa_breastscore'
                                            ||
                                            $field_name === 'aa_fpubhairscore'
                                        ) {
                                            if (array_key_exists($field_name, $form_data) && $form_data[$field_name] === '5') {
                                                $tmp_data[$field_name] = $form_data[$field_name];
                                                //-- ensure that the puberty details question is marked as yes to display this
                                                $tmp_data['aa_pubertypt'] = '1';
                                                $tmp_data['aa_puberty'] = $form_data['aa_puberty'];
                                                $tmp_data['aa_penisscrotscore'] = $form_data['aa_penisscrotscore'];
                                                $tmp_data['aa_mpubhairscore'] = $form_data['aa_mpubhairscore'];
                                                $tmp_data['aa_breastscore'] = $form_data['aa_breastscore'];
                                                $tmp_data['aa_fpubhairscore'] = $form_data['aa_fpubhairscore'];
                                            }
                                        }
                                    }
                                    $existing_data[$form_date] = $tmp_data;
                                }
                            }
                        }
                    }
                }
            } else {
                $tmp_data = [];
                $form_date = $last_date;
                $form_data = $this->data[$record_id][$evt];
                if ( array_key_exists($date_fld, $form_data) && $form_data[$date_fld] !== '') {
                    $form_date = $form_data[$date_fld];
                    foreach ( $valid_field_names as $name ) {
                        if ( array_key_exists($name, $form_data) ) {
                            if ( $form_data[$name] !== '0' ) {
                                $tmp_data[$name] = $form_data[$name];
                            }
                        }
                    }
                    foreach ($this->formFields as $field_name => $field_text) {
                        //-- Puberty scores of 5 need to be transferred
                        if ( $field_name === 'aa_penisscrotscore'
                            ||
                            $field_name === 'aa_mpubhairscore'
                            ||
                            $field_name === 'aa_breastscore'
                            ||
                            $field_name === 'aa_fpubhairscore'
                        ) {
                            if (array_key_exists($field_name, $form_data)) {
                                if ($form_data[$field_name] === '5') {
                                    $tmp_data[$field_name] = $form_data[$field_name];
                                    //-- ensure that the puberty details question is marked as yes to display this
                                    $tmp_data['aa_pubertypt'] = '1';
                                    $tmp_data['aa_puberty'] = $form_data['aa_puberty'];
                                    $tmp_data['aa_penisscrotscore'] = $form_data['aa_penisscrotscore'];
                                    $tmp_data['aa_mpubhairscore'] = $form_data['aa_mpubhairscore'];
                                    $tmp_data['aa_breastscore'] = $form_data['aa_breastscore'];
                                    $tmp_data['aa_fpubhairscore'] = $form_data['aa_fpubhairscore'];
                                }
                            }
                        }
                    }
                    $existing_data[$form_date] = $tmp_data;
                }
            }
        }

        ksort($existing_data, SORT_REGULAR);
        $filtered = array_filter(
            $existing_data,
            function ($key) use ($currentDate) {
                return $key < $currentDate;
            },
            ARRAY_FILTER_USE_KEY
        );

        if ( count($filtered) > 0 ) {
            $last_key = array_key_last($filtered);
            $retVal['last_annual'] = $filtered[$last_key];
            $this->data['last_annual'] = $filtered[$last_key];
            return $retVal;
        }

        return [];
    }
    public function getLastDEXA(string $record_id, int $event_id, int $repeat_instance, array $data2Process,
                                string $index = 'dexa',
                                string $key1 = 'aa_dexa_lastdone',
                                string $key2 = 'aa_dexa_normabnorm') : array {
        if ( array_key_exists($record_id, $this->data) ) {
            foreach ( $this->events as $eid => $event) {
                if ( $eid < $event_id ) {
                    if ( array_key_exists($eid, $this->data[$record_id]) ) {
                        if ( array_key_exists($key1, $this->data[$record_id][$eid]) ) {
                            $value_2_add = [];
                            $value_2_add['date'] = $this->data[$record_id][$eid][$key1];
                            if ( array_key_exists($key2, $this->data[$record_id][$eid]) ) {
                                switch($this->data[$record_id][$eid][$key2]) {
                                    case '0':
                                        $value_2_add['status'] = 'Normal';
                                        break;
                                    case '1':
                                        $value_2_add['status'] = 'Abnormal';
                                        break;
                                    default:
                                        $value_2_add['status'] = 'Unknown';
                                        break;
                                }
                                if ( $value_2_add['date'] !== '' ) {
                                    $this->data[$index][] = $value_2_add;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->data;
    }

    public function getLastRenalBiopsy(string $record_id, int $event_id, int $repeat_instance,
                                string $index = 'renalbiopsy',
                                string $key1 = 'aa_renbiop_lastdone',
                                string $key2 = 'aa_nephritisclass',
                                string $key3 = 'aa_who_isn_rps') : array {
        if ( array_key_exists($record_id, $this->data) ) {
            foreach ( $this->events as $eid => $event) {
                if ( $eid < $event_id ) {
                    if ( array_key_exists($eid, $this->data[$record_id]) ) {
                        if ( array_key_exists($key1, $this->data[$record_id][$eid]) ) {
                            $value_2_add = [];
                            $value_2_add['date'] = $this->data[$record_id][$eid][$key1];
                            if ( array_key_exists($key2, $this->data[$record_id][$eid]) ) {
                                $value_2_add['nephritis'] = $this->data[$record_id][$eid][$key2];
                            }
                            if ( array_key_exists($key3, $this->data[$record_id][$eid]) ) {
                                switch($this->data[$record_id][$eid][$key3]) {
                                    case '1':
                                        $value_2_add['status'] = 'WHO';
                                        break;
                                    case '2':
                                        $value_2_add['status'] = 'ISN/RPS';
                                        break;
                                    case '3':
                                        $value_2_add['status'] = 'Both WHO & ISN/RPS';
                                        break;
                                    case '99':
                                    default:
                                        $value_2_add['status'] = '';
                                        break;
                                }
                                $this->data[$index][] = $value_2_add;
                            }
                        }
                    }
                }
            }
        }
        return $this->data;
    }


    public function getLastOpthalmology(string $record_id, int $event_id, int $repeat_instance,
                                       string $index = 'opthalmology',
                                       string $key1 = 'aa_oph_normabnorm',
                                       string $key2 = 'aa_oph_optic',
                                       string $key3 = 'aa_oph_lastdone') : array
    {
        if (array_key_exists($record_id, $this->data)) {
            foreach ($this->events as $eid => $event) {
                if ($eid < $event_id) {
                    if ( array_key_exists($eid, $this->data[$record_id]) ) {
                        if (array_key_exists($key3, $this->data[$record_id][$eid])) {
                            $value_2_add = [];
                            $addme = true;
                            $value_2_add['date'] = $this->data[$record_id][$eid][$key3];
                            if ($value_2_add['date'] === '' ) {
                                $value_2_add['date'] = 'Date unknown (recorded at ' . $this->events[$eid]['name'] . ')';
                            }
                            if ( array_key_exists($key1, $this->data[$record_id][$eid]) ) {
                                switch ($this->data[$record_id][$eid][$key1]) {
                                    case '0':
                                        $value_2_add['result'] = 'Normal';
                                        $addme = true;
                                        break;
                                    case '1':
                                        $value_2_add['result'] = 'Abormal';
                                        $addme = true;
                                        break;
                                    default:
                                        $value_2_add['result'] = 'Unknown';
                                        $addme = false;
                                        break;
                                }
                            }
                            if ( array_key_exists($key2, $this->data[$record_id][$eid]) ) {
                                switch ($this->data[$record_id][$eid][$key2]) {
                                    case '1':
                                        $value_2_add['where'] = 'Ophthalmologists';
                                        $addme = true;
                                        break;
                                    case '2':
                                        $value_2_add['where'] = 'Opticians';
                                        $addme = true;
                                        break;
                                    default:
                                        $value_2_add['where'] = 'Unknown';
                                        break;
                                }
                            }
                            if ( $addme) {
                                $this->data[$index][] = $value_2_add;
                            }
                        }
                    }
                }
            }
        }
        return $this->data;
    }

    public function getDateOfCurrentVisit(string $record_id, int $event_id, int $repeat_instance) : string {
        $date_fld = 'aa_date';
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



}