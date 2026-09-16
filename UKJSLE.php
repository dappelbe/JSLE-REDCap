<?php
namespace UoL\UKJSLE;

use ExternalModules\AbstractExternalModule;
use Project;
use UoL\JSLE\Helper\DataHelper;
use UoL\JSLE\ModuleHelper;
use UoL\JSLE\Services\AcrSliccPageService;
use UoL\JSLE\Services\AnnualPageService;
use UoL\JSLE\Services\BilagPageService;
use UoL\JSLE\Services\DemographicsPageService;
use UoL\JSLE\Services\RecordHomePageService;
use UoL\JSLE\Services\SamplesPageService;

require_once __DIR__ . '/vendor/autoload.php';

class UKJSLE extends AbstractExternalModule
{
    private $participant_data = [];
    private $settings = [];

    public function __construct()
    {
        parent::__construct();
        // Ensure composer autoload available for module classes and adapters
        if (!ModuleHelper::ensureVendorAutoload(__DIR__)) {
            // during development fail fast so the developer knows to run composer install
            // but log first so you can see what's happening in logs
            throw new \RuntimeException("Vendor autoload not found in module. Run `composer install` in module root.");
        }
    }

    // Return true so the project link displays
    public function redcap_module_link_check_display($project_id, $link): true
    {
        return true;
    }

    // Use explicit visibility. Add a quick debug log so we can confirm invocation.
    public function redcap_every_page_top($project_id): void
    {
        // PAGE is a REDCap constant; check for the pages you care about
        if ( (defined('PAGE') && PAGE === 'DataEntry/index.php' && !empty($_GET['id']) )
            || (defined('PAGE') && PAGE === 'surveys/index.php' && !empty($_GET['id']) )
            || (defined('PAGE') && PAGE === 'DataEntry/record_home.php' && !empty($_GET['id']) )
        ) {
            // safe to instantiate Project here
            $project = new \Project($project_id);
            $current_form = '';
            if (PAGE === 'DataEntry/record_home.php') {
                $this->participant_data = \REDCap::getData($project_id, 'array', $_GET['id']);
                $study_visits = $project->eventInfo;
                $displayType = $this->getProjectSetting('uol-jsle-dasboard-display-type');
                $service = new RecordHomePageService($this->participant_data, $study_visits, $project_id);
                $service->getRegistrationAndDischargeDates($_GET['id']);
                $data_for_page = $service->getVisitLabels($project->eventInfo, 'Year 0 - Baseline', 'demo_dob', $displayType);
                //-- Now look to see if we have a linked record.
                $service->copyTransferedData($project_id, $project);
                //-- Now make sure that our JS is included.
                $this->setJsSettings($data_for_page);
                $this->includeJs('scripts/ukJSLERecordHomePage_library_v0.1_29Jan2026.js');
            }
        }
    }


    // Make public and keep signature
    public function redcap_data_entry_form(
        $project_id,
        $record,
        $instrument,
        $event_id,
        $group_id,
        $repeat_instance
    ): void
    {
        if (!$project_id) {
            return;
        }

        $this->participant_data = \REDCap::getData($project_id, 'array', $_GET['id']);
        $project = new \Project($project_id);
        $helper = new DataHelper($this->participant_data, $project->forms);
        $orderedData = $helper->processedData;

        switch ($instrument) {
            case 'acr_slicc_classification':
                $obj = new AcrSliccPageService($this->participant_data, $project->eventInfo, $project->forms['acr_slicc_classification']['fields']);
                $data = $obj->copyLastData($record, $event_id, $repeat_instance);
                $this->setJsSettings($data);
                $this->includeJs('scripts/ukJSLESLICCFunctions_v0.1_06Feb2026.js');
                break;
            case 'annual_assessment':
                $obj = new AnnualPageService($this->participant_data, $project->eventInfo, $project->forms['annual_assessment']['fields']);
                $obj->copyLastData($record, $event_id, $repeat_instance);
                $obj->getLastDEXA($record, $event_id, $repeat_instance, $orderedData[$record]['annual_assessment']);
                $obj->getLastRenalBiopsy($record, $event_id, $repeat_instance);
                $obj->getLastOpthalmology($record, $event_id, $repeat_instance);
                $this->setJsSettings($obj->data);
                $this->includeJs('scripts/ukJSLEAnnualAssessmentFunctions_v0.1_06Feb2026.js');
                break;
            case 'bilag':
                $obj = new BilagPageService($this->participant_data, $project->eventInfo, $project->forms['bilag']['fields']);
                $obj->previousBilagInvolvement($project_id, $record, $event_id, $repeat_instance);
                $currentDate = $obj->getDateOfCurrentVisit($record, $event_id, $repeat_instance, 'bilag_date');
                $obj->getLastRTX($record, $event_id, $repeat_instance, $currentDate, $orderedData[$record]['bilag']);
                $obj->getLastBelimumab($record, $event_id, $repeat_instance, $currentDate, $orderedData[$record]['bilag']);
                $obj->getLastCyclo($record, $event_id, $repeat_instance, $currentDate, $orderedData[$record]['bilag']);
                $obj->getLastIVMP($record, $event_id, $repeat_instance, $currentDate, $orderedData[$record]['bilag']);
                $obj->getLastIVIG($record, $event_id, $repeat_instance, $currentDate, $orderedData[$record]['bilag']);
                $data = $obj->calculateRenalScore($project_id, $record, $event_id, $repeat_instance);
                $this->includeJs('scripts/ukJSLEBilagFunctions_library_v0.1_27May2025.js');
                $this->includeJs('scripts/ukJSLEBilagFunctions_jQuery_v0.1_27May2025.js');
                $this->setJsSettings($data);
                break;
            case 'demographics':
                $this->includeJs('scripts/ukJSLEDemographicsFunctions_library_v0.1_05Feb2026.js');
                break;
            case 'samples':
                $obj = new BilagPageService($this->participant_data, $project->eventInfo, $project->forms['bilag']['fields']);
                $currentDate = $obj->getDateOfCurrentVisit($record, $event_id, $repeat_instance, 'samples_date');
                $obj = new SamplesPageService($this->participant_data, $project->eventInfo);
                $data = $obj->EDTAOrPMBC_PreviouslyTaken($orderedData[$record]['samples'], $currentDate);
                $this->setJsSettings($data);
                $this->includeJs('scripts/ukJSLESamplesFunctions_v0.1_15Sep2026.js');
                break;
            default:
                break;
        }
    }

    public function recalculateBilags($project_id) {
        if (!isset($project_id)) {
            die('Project ID is a required field @ recalculateBilags');
        }
        $project = new \Project($project_id);
        $records = \REDCap::getData($project_id, 'array');
        $bilagService = new BilagPageService(array(), $project->eventInfo, array());
        foreach ($records as $record => $data) {
            $data2save = array();
            foreach ($data as $event_id => $fields) {
                if ($event_id === 'repeat_instances') {
                    foreach( $fields as $evt => $values ) {
                        foreach( $values as $instrument => $repeats ) {
                            if ( $instrument === 'bilag' ) {
                                foreach ( $repeats as $repeat_id => $repeat_data ) {
                                    if ( $repeat_data['bilag_date'] !== '' ) {
                                        $pd = $bilagService->previousBilagInvolvement($project_id, $record, $evt);
                                        $data2save[$record]['repeat_instances'][$evt]['bilag'][$repeat_id] = $pd['previous_bilag_map'];
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ( $fields['bilag_date'] !== '' ) {
                        if (str_contains($project->eventInfo[$event_id]['name'], 'Baseline')) {
                            $ignoreThisStep = 1;
                        } else {
                            $pd = $bilagService->previousBilagInvolvement($project_id, $record, $event_id);
                            $data2save[$record][$event_id] = $pd['previous_bilag_map'];
                            $vv = 1;
                        }
                    }
                }
            }
            $ans = \REDCap::saveData($project_id, 'array', $data2save);
        }

    }

    public function redcap_save_record($project_id,
                                $record,
                                $instrument,
                                $event_id,
                                $group_id,
                                $survey_hash,
                                $response_id,
                                $repeat_instance)
    {
        if (!$project_id) {
            return;
        }

        switch ($instrument) {
            case 'demographics':
                $this->participant_data = \REDCap::getData($project_id, 'array', $_GET['id']);
                if (array_key_exists('demo_dob', $this->participant_data[$record][$event_id])
                    &&
                    array_key_exists('demo_oldstudyno', $this->participant_data[$record][$event_id])
                    &&
                    $this->participant_data[$record][$event_id]['demo_dob'] === ''
                    &&
                    $this->participant_data[$record][$event_id]['demo_oldstudyno'] !== ''
                    ) {
                        $project = new \Project($project_id);
                        $obj = new DemographicsPageService();
                        $obj->populateLinkedParticipantData(
                            $project_id,
                            $this->participant_data,
                            $event_id,
                            $record,
                            $this->participant_data[$record][$event_id]['demo_oldstudyno'],
                            $project->eventInfo);
                    }
                break;
            default:
                break;
        }
    }

    /**
     * Helper: get the event name string required by saveData if you only have event_id.
     * If your project is classic (non-longitudinal) you may omit redcap_event_name entirely.
     */
    private function getEventName($project_id, $event_id)
    {
        if (empty($event_id)) {
            return '';
        }
        // Use REDCap functions to map event_id -> event_name
        $events = \REDCap::getEventNames(true); // returns array id => event_name
        return isset($events[$event_id]) ? $events[$event_id] : '';
    }

    protected function includeJs($path): void
    {
        // For shib installations, it is necessary to use the API endpoint for resources
        global $auth_meth;
        $ext_path = ($auth_meth === 'shibboleth') ? $this->getUrl($path, true, true) : $this->getUrl($path);
        echo '<script src="' . htmlspecialchars($ext_path, ENT_QUOTES, 'UTF-8') . '"></script>';
    }

    protected function includeCSS($path): void
    {
        // Fixed quoting and escaping
        global $auth_meth;
        $ext_path = ($auth_meth === 'shibboleth') ? $this->getUrl($path, true, true) : $this->getUrl($path);
        echo '<link rel="stylesheet" href="' . htmlspecialchars($ext_path, ENT_QUOTES, 'UTF-8') . '" />';
    }

    protected function setJsSettings($settings): void
    {
        // Use escape() if you need, then JSON encode and output
        echo '<script>let UKJSLE = ' . json_encode($this->escape($settings)) . ';</script>';
    }


}
