<?php
/**
 * -----------------------------------------------------------------------------
 * File: AcrSliccPageService.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 * Hold all the funstions that we need to run on the ACR SLICC page
 *
 * @package   UoL\JSLE\Services
 * @author    orms0734
 * @version   0.0.1
 * @created   29/01/2026 15:22
 * -----------------------------------------------------------------------------
 */

namespace UoL\JSLE\Services;

class AcrSliccPageService
{
    private array $data;
    private array $events;
    private array $formFields;

    public function __construct(array $record_data, array $events, array $fieldsOnForm)
    {
        $this->data = $record_data;
        $this->events = $events;
        $this->formFields = $fieldsOnForm;
    }

    public function handlePage(string $record_id, int $event_id, int $repeat_instance) : void {
        $this->copyLastData($record_id, $event_id, $repeat_instance);
    }

    public function copyLastData(string $record_id, int $event_id, int $repeat_instance) : array {
        $last_data = [];

        $valid_field_names = [
            'acr_q01_malrash',
            'acr_q02_disclup',
            'acr_q03_photosens',
            'acr_q04_oralnasalulc',
            'acr_q05_nonerosivearth',
            'acr_q06_serositis_a',
            'acr_q06_serositis_b',
            'acr_q07_nephritis_a',
            'acr_q07_nephritis_b',
            'acr_q08_neuro_a',
            'acr_q08_neuro_b',
            'acr_q09_haem_a',
            'acr_q09_haem_b',
            'acr_q09_haem_c',
            'acr_q09_haem_d',
            'acr_q10_immuno_a',
            'acr_q10_immuno_b',
            'acr_q10_immuno_c1',
            'acr_q10_immuno_c2',
            'acr_q10_immuno_c3',
            'acr_q11_ana',
            'scc_q01_acla',
            'scc_q01_aclb',
            'scc_q01_aclc',
            'scc_q01_acld',
            'scc_q01_acle',
            'scc_q01_aclf',
            'scc_q02_ccla',
            'scc_q02_cclb',
            'scc_q02_cclc',
            'scc_q02_ccld',
            'scc_q02_ccle',
            'scc_q02_cclf',
            'scc_q02_cclg',
            'scc_q02_cclh',
            'scc_q03_ulca',
            'scc_q03_ulcb',
            'scc_q04_nsa',
            'scc_q05_syn',
            'scc_q06_seroa',
            'scc_q06_serob',
            'scc_q07_rena',
            'scc_q07_renb',
            'scc_q08_neua',
            'scc_q08_neub',
            'scc_q08_neuc',
            'scc_q08_neud',
            'scc_q08_neue',
            'scc_q08_neuf',
            'scc_q09_ha',
            'scc_q10_lorla',
            'scc_q10_lorlb',
            'scc_q11_throm',
            'scc_q12_ana',
            'scc_q13_dsdna',
            'scc_q14_sm',
            'scc_q15_aapa',
            'scc_q15_aapb',
            'scc_q15_aapc',
            'scc_q15_aapd',
            'scc_q16_lowca',
            'scc_q16_lowcb',
            'scc_q16_lowcc',
            'scc_q17_dct',
        ];
        $slicc_forms = array();

        $slicc_study_num_field = 'ascc_studyid';
        $slicc_date_fld = 'ascc_date';
        $last_date = '1970-01-01';
        $form_name = 'acr_slicc_classification';
        $existing_data = array();
        //-- Get all the data for the participant in date order
        $currentDate = date('Y-m-d');

        if ( array_key_exists($record_id, $this->data)) {
            if ( array_key_exists($event_id, $this->data[$record_id])) {
                if (array_key_exists($slicc_date_fld, $this->data[$record_id][$event_id])) {
                    if ( $this->data[$record_id][$event_id][$slicc_date_fld] !== '' ) {
                        $currentDate = $this->data[$record_id][$event_id][$slicc_date_fld];
                    }
                }
            } else {
                if ( array_key_exists('repeat_instances', $this->data[$record_id])) {
                    if (array_key_exists($event_id, $this->data[$record_id]['repeat_instances'])) {
                        if (array_key_exists($form_name, $this->data[$record_id]['repeat_instances'][$event_id])) {
                            if (array_key_exists($repeat_instance, $this->data[$record_id]['repeat_instances'][$event_id][$form_name])) {
                                if (array_key_exists($slicc_date_fld, $this->data[$record_id]['repeat_instances'][$event_id][$form_name][$repeat_instance])) {
                                    if ($this->data[$record_id]['repeat_instances'][$event_id][$form_name][$repeat_instance][$slicc_date_fld] !== '') {
                                        $currentDate = $this->data[$record_id]['repeat_instances'][$event_id][$form_name][$repeat_instance][$slicc_date_fld];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

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
                                $form_date = $last_date;
                                $form_data = $this->data[$record_id]['repeat_instances'][$rk][$form_name][$nr];
                                if (array_key_exists($slicc_date_fld, $form_data) && $form_data[$slicc_date_fld] !== '') {
                                    $form_date = $form_data[$slicc_date_fld];
                                    $tmp_data[$slicc_date_fld] = $form_date;
                                    foreach ($valid_field_names as $name) {
                                        if (array_key_exists($name, $form_data)) {
                                            $tmp_data[$name] = $form_data[$name];
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
                if ( array_key_exists($slicc_date_fld, $form_data) && $form_data[$slicc_date_fld] !== '') {
                    $form_date = $form_data[$slicc_date_fld];
                    $tmp_data[$slicc_date_fld] = $form_date;
                    foreach ( $valid_field_names as $name ) {
                        if ( array_key_exists($name, $form_data) ) {
                            $tmp_data[$name] = $form_data[$name];
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
            $retVal['last_slicc'] = $filtered[$last_key];
            $this->data['last_slicc'] = $filtered[$last_key];
            return $retVal;
        }

        return [];
    }

}