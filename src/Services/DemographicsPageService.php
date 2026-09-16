<?php
/**
 * -----------------------------------------------------------------------------
 * File: DemographicsPageService.php
 * Project: REDCapDevelopmentEnvironment
 * -----------------------------------------------------------------------------
 * Description:
 * This code will manage changes needed for the Demographics form
 *
 * @package   UoL\JSLE\Services
 * @author    orms0734
 * @version   0.0.1
 * @created   24/02/2026 09:53
 * -----------------------------------------------------------------------------
 */

namespace UoL\JSLE\Services;

use DateTime;

class DemographicsPageService
{
    // Variable names taken from the REDCap data dictionary, excluding calc/descriptive fields.
    private const DEMOGRAPHICS_FIELDS = [
        'demo_version',
        'demo_studyid',
        'demo_islinked',
        'demo_oldstudyno',
        'demo_newstudyno',
        'demo_data',
        'demo_nhsnum',
        'demo_gender',
        'demo_pc',
        'demo_dob',
        'demo_origref',
        'demo_sub_spec_det',
        'demo_onsymp_yrs',
        'demo_onsymp_mths',
        'demo_onsymp_date',
        'demo_pres_yrs',
        'demo_pres_mths',
        'demo_pres_date',
        'demo_diag_yrs',
        'demo_diag_mths',
        'demo_diag_date',
        'demo_ethnic',
        'demo_oth_ethnic',
        'pmxchoice',
        'demo_fhx_none',
        'demo_fhx_sle',
        'demo_fhx_thy',
        'demo_fhx_arth',
        'demo_fhx_ctd',
        'demo_fhx_1dm',
        'demo_fhx_oth',
        'demo_fhx_details',
        'demo_par_consang',
        'demo_inf',
        'demo_trig_inf',
        'demo_drugs',
        'demo_trig_drugs',
        'demo_sun',
        'demo_trig_oth',
        'demo_trig_oth_spec',
        'demo_notes',
        'demo_dateadd',
    ];
    private const ACR_SLICC_CLASSIFICATION_FIELDS = [
        'ascc_studyid',
        'ascc_date',
        'ascc_version',
        'ascc_diag_review',
        'ascc_data',
        'acr_nonew',
        'acr_q01_malrash',
        'acr_q01_malrash_dur',
        'acr_q02_disclup',
        'acr_q02_disclup_dur',
        'acr_q03_photosens',
        'acr_q03_photosens_dur',
        'acr_q04_oralnasalulc',
        'acr_q04_oralnasalulc_dur',
        'acr_q05_nonerosivearth',
        'acr_q05_nonerosivearth_dur',
        'acr_q06_serositis_a',
        'acr_q06_serositis_a_dur',
        'acr_q06_serositis_b',
        'acr_q06_serositis_b_dur',
        'acr_q07_nephritis_a',
        'acr_q07_nephritis_a_dur',
        'acr_q07_nephritis_b',
        'acr_q07_nephritis_b_dur',
        'acr_q08_neuro_a',
        'acr_q08_neuro_a_dur',
        'acr_q08_neuro_b',
        'acr_q08_neuro_b_dur',
        'acr_q09_haem_a',
        'acr_q09_haem_a_dur',
        'acr_q09_haem_b',
        'acr_q09_haem_b_dur',
        'acr_q09_haem_c',
        'acr_q09_haem_c_dur',
        'acr_q09_haem_d',
        'acr_q09_haem_d_dur',
        'acr_q10_immuno_a',
        'acr_q10_immuno_a_dur',
        'acr_q10_immuno_b',
        'acr_q10_immuno_b_dur',
        'acr_q10_immuno_c_dur',
        'acr_q10_immuno_c1',
        'acr_q10_immuno_c2',
        'acr_q10_immuno_c3',
        'acr_q11_ana',
        'acr_q11_ana_dur',
        'acr_why_evolving_lup',
        'acr_areas',
        'acr_cumul_areas',
        'scc_nonew',
        'scc_q01_acla',
        'scc_q01_acla_dur',
        'scc_q01_aclb',
        'scc_q01_aclb_dur',
        'scc_q01_aclc',
        'scc_q01_aclc_dur',
        'scc_q01_acld',
        'scc_q01_acld_dur',
        'scc_q01_acle',
        'scc_q01_acle_dur',
        'scc_q01_aclf',
        'scc_q01_aclf_dur',
        'scc_q02_ccla',
        'scc_q02_ccla_dur',
        'scc_q02_cclb',
        'scc_q02_cclb_dur',
        'scc_q02_cclc',
        'scc_q02_cclc_dur',
        'scc_q02_ccld',
        'scc_q02_ccld_dur',
        'scc_q02_ccle',
        'scc_q02_ccle_dur',
        'scc_q02_cclf',
        'scc_q02_cclf_dur',
        'scc_q02_cclg',
        'scc_q02_cclg_dur',
        'scc_q02_cclh',
        'scc_q02_cclh_dur',
        'scc_q03_ulca',
        'scc_q03_ulca_dur',
        'scc_q03_ulcb',
        'scc_q03_ulcb_dur',
        'scc_q04_nsa',
        'scc_q04_nsa_dur',
        'scc_q05_syn',
        'scc_q05_syn_dur',
        'scc_q06_seroa',
        'scc_q06_seroa_dur',
        'scc_q06_serob',
        'scc_q06_serob_dur',
        'scc_q07_rena',
        'scc_q07_rena_dur',
        'scc_q07_renb',
        'scc_q07_renb_dur',
        'scc_q08_neua',
        'scc_q08_neua_dur',
        'scc_q08_neub',
        'scc_q08_neub_dur',
        'scc_q08_neuc',
        'scc_q08_neuc_dur',
        'scc_q08_neud',
        'scc_q08_neud_dur',
        'scc_q08_neue',
        'scc_q08_neue_dur',
        'scc_q08_neuf',
        'scc_q08_neuf_dur',
        'scc_q09_ha',
        'scc_q09_ha_dur',
        'scc_q10_lorla',
        'scc_q10_lorla_dur',
        'scc_q10_lorlb',
        'scc_q10_lorlb_dur',
        'scc_q11_throm',
        'scc_q11_throm_dur',
        'scc_q12_ana',
        'scc_q12_ana_dur',
        'scc_q13_dsdna',
        'scc_q13_dsdna_dur',
        'scc_q14_sm',
        'scc_q14_sm_dur',
        'scc_q15_aapa',
        'scc_q15_aapa_dur',
        'scc_q15_aapb',
        'scc_q15_aapb_dur',
        'scc_q15_aapc',
        'scc_q15_aapc_dur',
        'scc_q15_aapd',
        'scc_q15_aapd_dur',
        'scc_q16_lowca',
        'scc_q16_lowca_dur',
        'scc_q16_lowcb',
        'scc_q16_lowcb_dur',
        'scc_q16_lowcc',
        'scc_q16_lowcc_dur',
        'scc_q17_dct',
        'scc_q17_dct_dur',
        'slicc_met',
        'slicc_has_metyn',
        'ascc_notes',
        'ascc_dateadd',
        'ascc_datecomp',
        'ascc_visitnum',
    ];
    private const ANNUAL_ASSESSMENT_FIELDS = [
        'aa_studyid',
        'aa_date',
        'aa_version',
        'aa_data',
        'aa_ana_done',
        'aa_anapos',
        'aa_ana_titre1',
        'aa_ena_done',
        'aa_enapos',
        'aa_ena_addnotes',
        'aa_ena_notes',
        'aa_ena_otherdet',
        'aa_thyroid_antibod',
        'aa_thyroid',
        'aa_c1q_antibod',
        'aa_c1qpos',
        'aa_aca',
        'aa_anticard_acaigg',
        'aa_anticard_acaigm',
        'aa_lupusanticoag_done',
        'aa_lupusanticoag',
        'aa_glucose',
        'aa_hba1c',
        'aa_ast',
        'aa_alt',
        'aa_albumin',
        'aa_ck',
        'aa_cmas',
        'aa_lipid_profile',
        'aa_cholest',
        'aa_triglyce',
        'aa_ldl',
        'aa_hdl',
        'aa_tsh',
        'aa_t4',
        'aa_oph_done',
        'aa_oph_normabnorm',
        'aa_ophdateknown',
        'aa_oph_lastdone',
        'aa_oph_optic',
        'aa_dexadone',
        'aa_dexa_normabnorm',
        'aa_dexa_lastdone',
        'aa_renbiop_done',
        'aa_nephritisclass',
        'aa_who_isn_rps',
        'aa_renbiop_lastdone',
        'aa_puberty',
        'aa_pubertypt',
        'aa_penisscrotscore',
        'aa_mpubhairscore',
        'aa_breastscore',
        'aa_fpubhairscore',
        'aa_menarche_old',
        'aa_menarche',
        'aa_menarcheyrs',
        'aa_menarchemths',
        'aa_irregmenst',
        'aa_newirregmenst',
        'aasd_slicc_blanks',
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
        'aasd_sliccarea',
        'aa_notes',
        'aa_dateadd',
        'aa_datecomp',
        'aa_visitnum',
        'aa_qtype',
    ];
    private const BILAG_FIELDS = [
        'bilag_studyid',
        'bilag_date',
        'bilag_version',
        'bilag_data',
        'bilag_proteinuria',
        'bilag_haematuria',
        'bilag_leucocytes',
        'bilag_nitrites',
        'bilag_menst',
        'bilag_urinenotdone',
        'bilag_bnotp',
        'bilag_bloodssent',
        'bilag_ht',
        'bilag_wt',
        'bilag_sysbp',
        'bilag_diabp',
        'bilag_genpyrexia',
        'bilag_genwtloss',
        'bilag_genlymphad',
        'bilag_genfatigue',
        'bilag_genanorexia',
        'bilag_const2004_new',
        'bilag_const2004',
        'bilag_mucoskinsev',
        'bilag_mucoskinmild',
        'bilag_mucodisclesext',
        'bilag_mucodisclesloc',
        'bilag_mucoalopeciasev',
        'bilag_mucoalopeciamild',
        'bilag_mucopanniculsev',
        'bilag_mucopanniculmild',
        'bilag_mucoangoedema',
        'bilag_mucoangoedemasev',
        'bilag_mucoangoedemamild',
        'bilag_mucomuculcsev',
        'bilag_mucomuculcmild',
        'bilag_mucomalerythema',
        'bilag_mucosubcutnod',
        'bilag_mucoperniotic',
        'bilag_mucoperiungeryth',
        'bilag_mucoswolfingers',
        'bilag_mucosclerodactyly',
        'bilag_mucocalcinosis',
        'bilag_mucotelangiectasia',
        'bilag_mucosplinterhaem',
        'bilag_muco2004',
        'bilag_muco2004_new',
        'bilag_neurimpaired',
        'bilag_neurcognitive',
        'bilag_neuracutepsy',
        'bilag_neurpyschosis',
        'bilag_neurseizdis',
        'bilag_neurepilepticus',
        'bilag_neurcerebvascdis',
        'bilag_neurcerebralvasc',
        'bilag_neuraseptmening',
        'bilag_neurmononeuro',
        'bilag_neurascmyelitis',
        'bilag_neurdemyelinating',
        'bilag_neurmyelopathy',
        'bilag_neurpolyradiculo',
        'bilag_neurperineurop',
        'bilag_neurcranialneurop',
        'bilag_neurplexopathy',
        'bilag_neurpolyneurop',
        'bilag_neurautonomdis',
        'bilag_neurdiscswelling',
        'bilag_neurchorea',
        'bilag_neurcerebataxia',
        'bilag_neurmovementdis',
        'bilag_neursevheadache',
        'bilag_neurepisodichead',
        'bilag_neurtensionhead',
        'bilag_neurclusterhead',
        'bilag_neurichyperhead',
        'bilag_neurorgdepress',
        'bilag_neurmooddis',
        'bilag_neuranxietydis',
        'bilag_neurorgbrain',
        'bilag_neuro2004',
        'bilag_neuro2004_new',
        'bilag_muscdefmyositis',
        'bilag_muscmyositisincomp',
        'bilag_muscmyositismild',
        'bilag_muscmyalgia',
        'bilag_muscsevpolyarthritis',
        'bilag_muscmodarthritis',
        'bilag_muscarthralgia',
        'bilag_musctendonitis',
        'bilag_musctendoncontract',
        'bilag_muscnecrosis',
        'bilag_musc2004',
        'bilag_musc2004_new',
        'bilag_resppleuroperipain',
        'bilag_respdyspnoea',
        'bilag_respcardiacfailure',
        'bilag_respfrictionrub',
        'bilag_respeffusion',
        'bilag_respchestpain',
        'bilag_respcxrlung',
        'bilag_respcxrheart',
        'bilag_respecgcarditis',
        'bilag_respcardiacarrhyth',
        'bilag_resppulmonaryfunc',
        'bilag_resplungdisease',
        'bilag_respmyocarditismild',
        'bilag_respvalvulardysfunc',
        'bilag_respcardiactamponade',
        'bilag_resppleuraleffusion',
        'bilag_resppulmonhaemorr',
        'bilag_respintalveopneu',
        'bilag_respshrinklungsynd',
        'bilag_respaortitis',
        'bilag_respcoronaryvasc',
        'bilag_cardio2004',
        'bilag_cardio2004_new',
        'bilag_vascmajorcutvasc',
        'bilag_vascabdominalcrisis',
        'bilag_vascrecthromboem',
        'bilag_vascraynauds',
        'bilag_vasclividoreticul',
        'bilag_vascsuperficphleb',
        'bilag_vascmincutvasc',
        'bilag_vascthromboem1st',
        'bilag_renalsevhypertension',
        'bilag_renalnewdocprotein',
        'bilag_renalnephroticsynd',
        'bilag_renalnephritis',
        'bilag_renal2004',
        'bilag_renal2004_today',
        'bilag_renal2004_new',
        'bilag_gastperitonitis',
        'bilag_gastabdominalser',
        'bilag_gastlupusentcol',
        'bilag_gastmalabsorption',
        'bilag_gastprotein',
        'bilag_gastintestpseudob',
        'bilag_gasthepatitis',
        'bilag_gastacutecholecystit',
        'bilag_gastacutepancreatit',
        'bilag_gastro2004',
        'bilag_gastro2004_new',
        'bilag_ophorbitalmyprop',
        'bilag_ophkeratitissev',
        'bilag_ophkeratitismild',
        'bilag_ophanterioruveitis',
        'bilag_ophvasculitissev',
        'bilag_ophvasculitismild',
        'bilag_ophepiscleritis',
        'bilag_ophscleritissev',
        'bilag_ophscleritismild',
        'bilag_ophretinaldisease',
        'bilag_ophcytoidbodies',
        'bilag_ophopticneuritis',
        'bilag_ophopticneuropathy',
        'bilag_ophthal2004',
        'bilag_score2004',
        'bilag_ophthal2004_new',
        'bilag_ptchaq',
        'bilag_globalassess',
        'bilag_physicianglobscore',
        'bilag_physicianglobvas',
        'bilag_assesscurrentstatus',
        'bilag_which_tx',
        'bilag_hydroxychlorocurrent',
        'bilag_hydroxychlororevised',
        'bilag_hydroxychlorocsr',
        'bilag_hydroxychlorodate',
        'bilag_hydroxychloro_an',
        'bilag_hydroxychloronotes',
        'bilag_azathiopcurrent',
        'bilag_azathioprevised',
        'bilag_azathiopcsr',
        'bilag_azathiopdate',
        'bilag_azathiop_an',
        'bilag_azathiopnotes',
        'bilag_mycophenolcurrent',
        'bilag_mycophenolrevised',
        'bilag_mycophenolcsr',
        'bilag_mycophenoldate',
        'bilag_mycophenol_an',
        'bilag_mycophenolnotes',
        'bilag_cyclosporinacurrent',
        'bilag_cyclosporinarevised',
        'bilag_cyclosporincsr',
        'bilag_cyclosporindate',
        'bilag_cyclosporin_an',
        'bilag_cyclosporinnotes',
        'bilag_tacrolimuscurrent',
        'bilag_tacrolimusrevised',
        'bilag_tacrolimuscsr',
        'bilag_tacrolimusdate',
        'bilag_tacrolimus_an',
        'bilag_tacrolimusnotes',
        'bilag_prednisolcurrent',
        'bilag_prednisolrevised',
        'bilag_prednisolcsr',
        'bilag_prednisoldate',
        'bilag_prednisol_an',
        'bilag_prednisolnotes',
        'bilag_methotrexcurrent',
        'bilag_methotrexrevised',
        'bilag_methotrexcurroute',
        'bilag_methotrexrevroute',
        'bilag_methotrexcsr',
        'bilag_methotrexdate',
        'bilag_methotrex_an',
        'bilag_methotrexnotes',
        'bilag_ivigcurdose',
        'bilag_ivigpulses',
        'bilag_ivigcsr',
        'bilag_ivigdate',
        'bilag_ivig_an',
        'bilag_ivignotes',
        'bilag_rtxcumul_v3',
        'bilag_rtxpulses_v3',
        'bilag_rtxtotaldosecycle_v4_5',
        'bilag_rtxnuminfusions',
        'bilag_rtxnumcycles_v4_5',
        'bilag_rtxdose',
        'bilag_rtxprevgiven',
        'bilag_rtxcd19count',
        'bilag_rtxcd19date',
        'bilag_rtxbcellsrepop',
        'bilag_rrtxcsr',
        'bilag_rtxdate1',
        'bilag_rtxdate2',
        'bilag_rtxdate3',
        'bilag_rtxdate4',
        'bilag_rtx_an',
        'bilag_rtxnotes',
        'bilag_belimumdose',
        'bilag_belimumroute',
        'bilag_belimumfreq',
        'bilag_belimumstartdate',
        'bilag_belimumstopdate',
        'bilag_belimumnuminfus_v6',
        'bilag_belimumrevdose_v6',
        'bilag_belimumcsr',
        'bilag_belimumdate1_v6',
        'bilag_belimumdate2_v6',
        'bilag_belimum_an',
        'bilag_belimumnotes',
        'bilag_cyclophoscumul_v3',
        'bilag_cyclophospulses_v3',
        'bilag_cyclophosroute',
        'bilag_cyclophosdose',
        'bilag_cyclophosnuminfus',
        'bilag_ccyclophoscumdose',
        'bilag_cyclophoscsr',
        'bilag_cyclophosdate1',
        'bilag_cyclophosdate2',
        'bilag_cyclophos_an',
        'bilag_cyclophosnotes',
        'bilag_ivmepred',
        'bilag_ivmepredpulses',
        'bilag_ivmepreddose',
        'bilag_ivmepreddose_2',
        'bilag_ivmepreddose_3',
        'bilag_ivmepredcsr',
        'bilag_ivmepreddate1',
        'bilag_ivmepreddate2',
        'bilag_ivmepreddate3',
        'bilag_ivmepred_an',
        'bilag_ivmeprednotes',
        'bilag_othimmuno',
        'bilag_othimmsuppdetails',
        'bilag_otherdrugs',
        'bilag_clinintmeds',
        'bilag_decreasetx',
        'bilag_increasetx',
        'bilag_chgdmard',
        'bilag_nochangetx',
        'bilag_mcs_nd',
        'bilag_provenuti',
        'bilag_mixgrowthcontam',
        'bilag_microwcc',
        'bilag_microrcc',
        'bilag_microrccasts',
        'bilag_microwccasts',
        'bilag_renalurinaryalbcr',
        'bilag_renalurinalbcrunits',
        'bilag_renalurinalbcrcalc',
        'bilag_renalurinaryprotcr',
        'bilag_renalurinprotcrunits',
        'bilag_renalurinprotcrcalc',
        'bilag_24hrurinaryprot',
        'bilag_24hrurinaryprotnd',
        'bilag_renalcreatinine',
        'bilag_renalcreatinineunits',
        'bilag_renalcreatininecalc',
        'bilag_renalactiveurinesed',
        'bilag_haem2004_new',
        'bilag_haemhaemoglobin',
        'bilag_haemhaemoglobunits',
        'bilag_haemhaemoglobcalc',
        'bilag_haemwcc',
        'bilag_haemwccunits',
        'bilag_haemwcccalc',
        'bilag_haemeutrophils',
        'bilag_haemneutunits',
        'bilag_haemneutcalc',
        'bilag_haemlymphocytes',
        'bilag_haemlymphsunits',
        'bilag_haemlymphcalc',
        'bilag_haemplatelets',
        'bilag_haemplateletsunits',
        'bilag_haemplateletscalc',
        'bilag_haemhaemolysis',
        'bilag_haemcoombstestpos',
        'bilag_haemttp',
        'bilag_haem2004',
        'bilag_othesr',
        'bilag_othcrp',
        'bilag_othc3',
        'bilag_othc3status',
        'bilag_othc3units',
        'bilag_othc3calc',
        'bilag_othc4',
        'bilag_othc4status',
        'bilag_othc4units',
        'bilag_othc4calc',
        'bilag_othdsdnadone',
        'bilag_othdsdna',
        'bilag_othdsdnaposneg',
        'bilag_othdsddnaunits',
        'bilag_othdsddnacalc',
        'bilag_othigg',
        'bilag_othiga',
        'bilag_othigm',
        'bilag_othferretin',
        'bilag_dateadd',
        'bilag_dateadd2',
        'bilag_notes',
        'bilag_datecomp',
        'bilag_maxdomainscore',
        'bilag_maxdomianname',
        'bilag_visitnum',
        'bilag_sledaicalcscore',
        'bilag_sampleid',
    ];
    private const SLEDAI_2K_FIELDS = [
        'sledai_studyid',
        'sledai_date',
        'sledai_version',
        'sledai_data',
        'sledai_seizure',
        'sledai_psychosis',
        'sledai_obs',
        'sledai_vd',
        'sledai_cnd',
        'sledai_lh',
        'sledai_cva',
        'sledai_vasculitis',
        'sledai_arthritis',
        'sledai_myositis',
        'sledai_uc',
        'sledai_hematuria',
        'sledai_proteinuria',
        'sledai_pyuria',
        'sledai_rash',
        'sledai_alopecia',
        'sledai_mu',
        'sledai_pleurisy',
        'sledai_pericarditis',
        'sledai_lc',
        'sledai_idb',
        'sledai_fever',
        'sledai_throm',
        'sledai_leu',
        'sledai_none',
        'sledai_notes',
        'sledai_dateadd',
    ];
    private const CHAQ_FIELDS = [
        'chaq_studyid',
        'chaq_date',
        'chaq_version',
        'chaq_child_adol',
        'chaq_dress1',
        'chaq_dress2',
        'chaq_dress3',
        'chaq_dress4',
        'chaq_getup1',
        'chaq_getup2',
        'chaq_eating1',
        'chaq_eating2',
        'chaq_eating3',
        'chaq_walking1',
        'chaq_walking2',
        'chaq_devices1',
        'chaq_otherdev_spec',
        'chaq_help',
        'chaq_hygiene1',
        'chaq_hygiene2',
        'chaq_hygiene3',
        'chaq_hygiene4',
        'chaq_hygiene5',
        'chaq_reach1',
        'chaq_reach2',
        'chaq_reach3',
        'chaq_reach4',
        'chaq_grip1',
        'chaq_grip2',
        'chaq_grip3',
        'chaq_grip4',
        'chaq_grip5',
        'chaq_activities1',
        'chaq_activities2',
        'chaq_activities3',
        'chaq_activities4',
        'chaq_activities5',
        'chaq_devices2',
        'chaq_aids',
        'chaq_pain',
        'chaq_par_child_score',
        'chaq_phys_score',
        'chaq_notes',
        'chaq_dateadd',
        'chaq_datecomp',
        'chaq_visit_no',
    ];
    private const SF36_FIELDS = [
        'sf36_studyid',
        'sf36_date',
        'sf36_version',
        'sf3601',
        'sf3602',
        'sf3603a',
        'sf3603b',
        'sf3603c',
        'sf3603d',
        'sf3603e',
        'sf3603f',
        'sf3603g',
        'sf3603h',
        'sf3603i',
        'sf3603j',
        'sf3604a',
        'sf3604b',
        'sf3604c',
        'sf3604d',
        'sf3605a',
        'sf3605b',
        'sf3605c',
        'sf3606',
        'sf3607',
        'sf3608',
        'sf3609a',
        'sf3609b',
        'sf3609c',
        'sf3609d',
        'sf3609e',
        'sf3609f',
        'sf3609g',
        'sf3609h',
        'sf3609i',
        'sf3610',
        'sf3611a',
        'sf3611b',
        'sf3611c',
        'sf3611d',
        'sf36_dateadd',
        'sf36_notes',
        'sf36_datecomp',
        'sf36_visitnum',
    ];
    private $recs2save;

    public function populateLinkedParticipantData(int $project_id, array $ppt_data, int $current_event_id,
                                                  string $current_record, string $linked_record, $event_info ) : array {
        $this->recs2save = array();

        if ( $linked_record !== '' ) {
            //-- Only process if the dob is NOT set, and we have a linked record id
            //-- Extract the data for the old participant
            $old = \REDCap::getData(PROJECT_ID, 'array', $linked_record);
            if ( count($old) === 1 ) {
                //-- get the latest demographics data
                $demo_evt = $current_event_id;
                $events_2_ignore = [];
                $additional_quarterly_evt_id = 0;
                $additional_annual_evt_id = 0;
                foreach ( $event_info as $id => $evt ) {
                    if (
                        !str_contains($evt['name'], 'Annual')
                        &&
                        !str_contains($evt['name'], ' - Q')
                        &&
                        !str_contains($evt['name'], 'Additional' )
                    ) {
                        $events_2_ignore[] = $id;
                    }

                    if ( str_contains($evt['name'], 'Additional Quarterly')) { $additional_quarterly_evt_id = $id; }
                    if ( str_contains($evt['name'], 'Additional Annual')) { $additional_annual_evt_id = $id; }
                }
                $this->recs2save[$demo_evt] = array();
                $flds2ignore = ['demo_islinked', 'demo_studyid', 'demo_version', 'demo_oldstudyno', 'demo_newstudyno'];
                foreach( DemographicsPageService::DEMOGRAPHICS_FIELDS as $fld ) {
                    if ( !in_array($fld, $flds2ignore) ) {
                        $this->recs2save[$demo_evt][$fld] = $old[$linked_record][$demo_evt][$fld];
                    }
                }
                //-- Now check the repeat instances
                if ( array_key_exists('repeat_instances', $old[$linked_record]) ) {
                    if ( array_key_exists($additional_annual_evt_id, $old[$linked_record]['repeat_instances']) ){
                        $annual_forms = ['acr_slicc_classification', 'annual_assessment', 'bilag', 'sledai_2k', 'chaq', 'sf36'];
                        foreach ( $annual_forms as $crf ) {
                            if ( array_key_exists($crf, $old[$linked_record]['repeat_instances'][$additional_annual_evt_id]) ) {
                                $lastKey = array_key_last($old[$linked_record]['repeat_instances'][$additional_annual_evt_id][$crf]);
                                $data2process = $old[$linked_record]['repeat_instances'][$additional_annual_evt_id][$crf][$lastKey];
                                $this->extract_data_from_event($data2process, $demo_evt, $current_record);
                            }
                        }
                    }
                    if ( array_key_exists($additional_quarterly_evt_id, $old[$linked_record]['repeat_instances']) ){
                        $qtr_forms = ['bilag', 'sledai_2k', 'chaq'];
                        foreach ( $qtr_forms as $crf ) {
                            if ( array_key_exists($crf, $old[$linked_record]['repeat_instances'][$additional_quarterly_evt_id]) ) {
                                $lastKey = array_key_last($old[$linked_record]['repeat_instances'][$additional_quarterly_evt_id][$crf]);
                                $data2process = $old[$linked_record]['repeat_instances'][$additional_quarterly_evt_id][$crf][$lastKey];
                                $this->extract_data_from_event($data2process, $demo_evt, $current_record);
                            }
                        }
                    }
                }

                foreach ( $event_info as $id => $evt ) {
                    if ( !in_array($id, $events_2_ignore) ) {
                        if ( array_key_exists($id, $old[$linked_record]) ) {
                            $data2process = $old[$linked_record][$id];
                            $this->extract_data_from_event($data2process, $demo_evt, $current_record);
                        }
                    }
                }
                $aa = \Records::saveData($project_id, 'array', [$current_record => $this->recs2save],'overwrite', null, null, null, null, null, null, null, [$current_record => 'Copied data from linked participant - ' . $linked_record]);
            }
        }

        return [];
    }

    /***
     * Extract the data that we need to populate
     * @param array $data2process
     * @param int $demo_evt
     * @param string $current_record
     * @return array
     */
    private function extract_data_from_event(array $data2process, int $demo_evt, string $current_record)  {

        //-- Now get the latest Annual data
        $study_id_fld = 'aa_studyid';
        if ($data2process[$study_id_fld] !== '' && $this->canProcess('aa_date', $data2process, $demo_evt)) {
            foreach (DemographicsPageService::ANNUAL_ASSESSMENT_FIELDS as $fld) {
                $this->recs2save[$demo_evt][$fld] = $data2process[$fld];
                if ( $fld === $study_id_fld ) {
                    $this->recs2save[$demo_evt][$fld] = $current_record;
                }
            }
        }
        //-- Now get the latest ACR
        $study_id_fld = 'ascc_studyid';
        if ($data2process[$study_id_fld] !== '' && $this->canProcess('ascc_date', $data2process, $demo_evt)) {
            foreach (DemographicsPageService::ACR_SLICC_CLASSIFICATION_FIELDS as $fld) {
                $this->recs2save[$demo_evt][$fld] = $data2process[$fld];
                if ( $fld === $study_id_fld ) {
                    $this->recs2save[$demo_evt][$fld] = $current_record;
                }
            }
        }
        //-- Now the latest BILAG
        $study_id_fld = 'bilag_studyid';
        if ($data2process[$study_id_fld] !== '' && $this->canProcess('bilag_date', $data2process, $demo_evt)) {
            foreach (DemographicsPageService::BILAG_FIELDS as $fld) {
                $this->recs2save[$demo_evt][$fld] = $data2process[$fld];
                if ( $fld === $study_id_fld ) {
                    $this->recs2save[$demo_evt][$fld] = $current_record;
                }
            }
        }
        //-- Now the latest CHAQ
        $study_id_fld = 'chaq_studyid';
        if ($data2process[$study_id_fld] !== '' && $this->canProcess('chaq_date', $data2process, $demo_evt)) {
            foreach (DemographicsPageService::CHAQ_FIELDS as $fld) {
                $this->recs2save[$demo_evt][$fld] = $data2process[$fld];
                if ( $fld === $study_id_fld ) {
                    $this->recs2save[$demo_evt][$fld] = $current_record;
                }
            }
        }
        //-- Now the latest SLEDAI
        $study_id_fld = 'sledai_studyid';
        if ($data2process[$study_id_fld] !== '' && $this->canProcess('sledai_date', $data2process, $demo_evt)) {
            foreach (DemographicsPageService::SLEDAI_2K_FIELDS as $fld) {
                $this->recs2save[$demo_evt][$fld] = $data2process[$fld];
                if ( $fld === $study_id_fld ) {
                    $this->recs2save[$demo_evt][$fld] = $current_record;
                }
            }
        }
        //-- Now the latest SF36
        $study_id_fld = 'sf36_studyid';
        if ($data2process[$study_id_fld] !== '' && $this->canProcess('sf36_date', $data2process, $demo_evt)) {
            foreach (DemographicsPageService::SLEDAI_2K_FIELDS as $fld) {
                $this->recs2save[$demo_evt][$fld] = $data2process[$fld];
                if ( $fld === $study_id_fld ) {
                    $this->recs2save[$demo_evt][$fld] = $current_record;
                }
            }
        }

        return $this->recs2save;
    }

    /***
     * Is the date for the event we are processing after the date we currently have stored for this record?
     * @param string $date_field
     * @param $data_from_linked
     * @return bool
     */
    private function canProcess( string $date_field, $data_from_linked, int $event_id ) : bool {

        if ( array_key_exists($date_field, $data_from_linked) &&  $data_from_linked[$date_field] !== '') {
            if ( !array_key_exists($date_field, $this->recs2save[$event_id]) || $this->recs2save[$event_id][$date_field] === '') {
                return true;
            }
            if ( array_key_exists($date_field, $this->recs2save[$event_id]) && $this->recs2save[$event_id][$date_field] !== '') {
                $current_date = $this->recs2save[$event_id][$date_field];
                $old_date = $data_from_linked[$date_field];

                $current = new DateTime($current_date);
                $old = new DateTime($old_date);

                if ($old > $current) {
                    return true;
                }
            }
        }

        return false;
    }

}
