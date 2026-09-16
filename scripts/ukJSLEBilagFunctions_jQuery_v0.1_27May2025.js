/**
 * This file contains the jquery functions that are used to manage UI behaviour and getting/setting field values.
 * The calculations can be found in ukJSLEBilagFunctions_library_v0.1_27May2025.js.
 * The functions in this script are tested manually.
 *
 * @author Duncan Appelbe
 * @version 0.1 27May2025
 */

$(document).ready(function(){
    $('#opt-bilag_bnotp_1').on('change', function() {
        UkJsleJQuery.setBlankToZero();
    });

    if (
        typeof UKJSLE === 'undefined' ||
        !Array.isArray(UKJSLE.rtx)
    ) {
        return;
    }
    UKJsleBilagFunctions.insertRtxTable(UKJSLE.belimumab, { debug: true });
    if (
        typeof UKJSLE === 'undefined' ||
        !Array.isArray(UKJSLE.belimumab)
    ) {
        return;
    }
    UKJsleBilagFunctions.insertBelimumabTable(UKJSLE.belimumab, { debug: true });
    if (
        typeof UKJSLE === 'undefined' ||
        !Array.isArray(UKJSLE.cyclo)
    ) {
        return;
    }
    UKJsleBilagFunctions.insertCycloTable(UKJSLE.cyclo, { debug: true });
    if (
        typeof UKJSLE === 'undefined' ||
        !Array.isArray(UKJSLE.ivmp)
    ) {
        return;
    }
    UKJsleBilagFunctions.insertIVMPTable(UKJSLE.ivmp, { debug: true });
    if (
        typeof UKJSLE === 'undefined' ||
        !Array.isArray(UKJSLE.ivig)
    ) {
        return;
    }
    UKJsleBilagFunctions.insertIVIGTable(UKJSLE.ivig, { debug: true });
});

(function () {
    'use strict';

    // Helper: read first element by name or radio group checked value
    function getFieldValue(name) {
        var el = document.querySelector('[name="' + name + '"]');
        if (el) {
            if (el.type === 'radio') {
                var checked = document.querySelector('[name="' + name + '"]:checked');
                return checked ? checked.value : '';
            }
            return el.value != null ? el.value : '';
        }
        // fallback for radio groups or multiple nodes
        var nodes = document.querySelectorAll('[name="' + name + '"]');
        if (nodes && nodes.length) {
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].checked) return nodes[i].value;
            }
            // if not radio/checked, return first node's value
            return nodes[0].value != null ? nodes[0].value : '';
        }
        return '';
    }

    // Parse to Number if looks numeric; otherwise return original string
    function parseNumberIfLooksNumeric(val) {
        if (val === null || val === undefined) return '';
        var s = String(val).trim();
        if (s === '') return '';
        // normalize comma decimal separators
        s = s.replace(',', '.');
        // integer or float
        if (/^[+-]?\d+(\.\d+)?$/.test(s)) {
            var n = Number(s);
            return isNaN(n) ? s : n;
        }
        return s;
    }

    // For fields where we intentionally keep string (e.g. creatinine) simply return trimmed string
    function asString(val) {
        return val === null || val === undefined ? '' : String(val).trim();
    }

    // Names of REDCap fields (from your list)
    var mapping = {
        proteinuria: 'bilag_proteinuria',
        q99: 'bilag_renalsevhypertension',
        q102a: 'bilag_renalurinaryalbcr',
        q102b: 'bilag_renalurinaryprotcr',
        q102c: 'bilag_24hrurinaryprot',
        q104: 'bilag_renalnephroticsynd',
        q105: 'bilag_renalcreatinine',
        q106b: 'bilag_renalgfrest',
        q107: 'bilag_renalactiveurinesed',
        q108: 'bilag_renalnephritis',
        systolic: 'bilag_sysbp',
        diastolic: 'bilag_diabp'
    };

    // Build the input object in the shape you specified
    function buildInput() {
        // read raw values from form
        var raw = {};
        Object.keys(mapping).forEach(function (k) {
            raw[k] = getFieldValue(mapping[k]);
        });

        // Construct top-level fields with conversions matching your example:
        // - proteinuria, q99, q104, q107, q108 -> numbers when possible
        // - q105 (creatinine) and q106b (GFrest) -> keep as strings (per example)
        // - q102a/b/c -> keep as strings (text boxes)
        var input = {
            proteinuria: parseNumberIfLooksNumeric(raw.proteinuria),
            q99: parseNumberIfLooksNumeric(raw.q99),
            q102a: asString(raw.q102a),
            q102b: asString(raw.q102b),
            q102c: asString(raw.q102c),
            q104: parseNumberIfLooksNumeric(raw.q104),
            q105: asString(raw.q105),
            q106b: asString(raw.q106b),
            q107: parseNumberIfLooksNumeric(raw.q107),
            q108: parseNumberIfLooksNumeric(raw.q108),
            UKJSLE: UKJSLE
        };

        return input;
    }

    function callCalculate() {
        try {
            var input = buildInput();

            if (typeof UKJsleBilagFunctions === 'undefined') {
                console.error('UKJsleBilagFunctions is not defined.');
                return;
            }
            if (typeof UKJsleBilagFunctions.calculateBilagRenalCatA !== 'function') {
                console.error('UKJsleBilagFunctions.calculateBilagRenalCatA is not a function.');
                return;
            }

            let score = UKJsleBilagFunctions.calculateBilagRenalCatA(input);
            if ( score === 0 ) {
                score = UKJsleBilagFunctions.calculateBilagRenalCatB(input);
                if ( score === 0 ) {
                    score = UKJsleBilagFunctions.calculateBilagRenalCatC(input);
                }
            }
            var field = document.querySelector('input[name="bilag_renal2004_today"]');
            if (field) {
                field.value = score;
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }

        } catch (err) {
            console.error('Error calling calculateBilagRenalCatA:', err);
        }
    }

    // Attach listeners to all mapped REDCap fields
    function attachListeners() {
        var names = Object.values(mapping);
        // also attach lastRenal originals (ensure consistency)
        var lastRenalNames = [
            'bilag_proteinuria',
            'bilag_renalcreatinine',
            'bilag_renalgfrest',
            'bilag_24hrurinaryprot',
            'bilag_renalurinaryprotcr',
            'bilag_renalurinaryalbcr'
        ];
        names = names.concat(lastRenalNames);

        // dedupe
        names = names.filter(function (v, i, a) { return a.indexOf(v) === i; });

        names.forEach(function (fname) {
            var nodes = document.querySelectorAll('[name="' + fname + '"]');
            if (!nodes || nodes.length === 0) return;
            nodes.forEach(function (node) {
                node.addEventListener('change', callCalculate, { passive: true });
                if (node.tagName === 'INPUT' && (node.type === 'text' || node.type === 'number' || node.type === 'tel')) {
                    node.addEventListener('input', callCalculate, { passive: true });
                }
            });
        });
    }

    function populatePreviousInvolvement() {
        if (typeof UKJSLE !== 'object' || !UKJSLE) {
            console.info('UKJSLE not defined — skipping bilag previous involvement population.');
            return;
        }
        const last = UKJSLE.previous_bilag_map;
        if (!last || typeof last !== 'object' || Object.keys(last).length === 0) {
            console.info('UKJSLE.previous_bilag_map empty or missing — nothing to populate.');
            return;
        }
        Object.entries(last).forEach(([fieldName, value]) => {
            const normalizedValue = value === null || value === undefined ? '' : String(value);

            const input = document.querySelector(`input[name="${fieldName}"]`);
            if (input) {
                input.value = normalizedValue;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                return;
            }

            const select = document.querySelector(`select[name="${fieldName}"]`);
            if (select) {
                select.value = normalizedValue;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                return;
            }

            const radio = document.querySelector(`input[type="radio"][name="${fieldName}"][value="${normalizedValue}"]`);
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            attachListeners();
            populatePreviousInvolvement();
            callCalculate();
        });
    } else {
        attachListeners();
        populatePreviousInvolvement();
        callCalculate();
    }
})();

class UkJsleJQuery {

    /**
     * When selected, set all the blank boxes to 0.
     */
    static setBlankToZero() {
        $('select').each(function () {
            if ($(this).val() === ''
                && $(this).prop('name') !== 'bilag_complete'
                && $(this).prop('name') !== 'bilag_proteinuria'
                && $(this).prop('name') !== 'bilag_haematuria'
                && $(this).prop('name') !== 'bilag_leucocytes'
                && $(this).prop('name') !== 'bilag_nitrites'
        ) {
                $(this).val('0');
            }
        });

        let radioToSetToN = [
            "#opt-bilag_mucoswolfingers_0",
            "#opt-bilag_mucosplinterhaem_0",
            "#opt-bilag_musctendoncontract_0",
            "#opt-bilag_muscnecrosis_0",
            "#opt-bilag_respcxrlung_0",
            "#opt-bilag_respcxrheart_0",
            "#opt-bilag_resppulmonaryfunc_0",
            "#opt-bilag_respcardiacarrhyth_0",
            "#opt-bilag_mucoswollenfingers_0",
            "#opt-bilag_mucosclerodactyly_0",
            "#opt-bilag_mucocalcinosis_0",
            "#opt-bilag_mucotelangiectasia_0",
            "#opt-bilag_mucosplinterhaemorrhages_0",
            "#opt-bilag_musctendoncontractures_0",
            "#opt-bilag_muscasepticnecrosis_0",
            "#opt-bilag_respcxrlungfields_0",
            "#opt-bilag_respcxrheartsize_0",
            "#opt-bilag_respecgcarditis_0",
            "#opt-bilag_respcardiacarrhythmias_0",
            "#opt-bilag_resppulmonaryfunction_0",
            "#opt-bilag_resplungdisease_0",
            "#opt-bilag_vascthromboembolism_0",
            "#opt-bilag_renalhypertension_0",
            "#opt-bilag_renalproteinuria_0",
            "#opt-bilag_renalnephroticsyndrome_0",
            "#opt-bilag_renalnephritis_0",
            "#opt-bilag_vascthromboem1st_0",
            "#opt-bilag_renalsevhypertension_0",
            "#opt-bilag_renalnewdocprotein_0",
            "#opt-bilag_renalnephroticsynd_0",
        ];


        $.each(radioToSetToN, function(index, selector) {
            let yesSelector = selector.replace(/_0$/, '_1');
            let $yes = $(yesSelector);

            if ($yes.length === 0 || !$yes.is(':checked')) {
                $(selector).prop('checked', true);

                // Derive hidden input name
                let hiddenName = selector
                    .replace(/^#opt-/, '')   // remove "#opt-"
                    .replace(/_\d+$/, '');  // remove "_0" or "_1"

                // Set corresponding hidden input value
                $('input[name="' + hiddenName + '"]').val('0');
            }
        });

    }

}