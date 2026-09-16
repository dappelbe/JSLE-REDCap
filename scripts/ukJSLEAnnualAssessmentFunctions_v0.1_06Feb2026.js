(function populateAnnualRadiosOnLoad() {
    function safeEscape(s) {
        try {
            return CSS.escape(s);
        } catch (e) {
            return String(s).replace(/["'\\]/g, '');
        }
    }

    function setHiddenValue(fieldName, value) {
        // Hidden input usually named exactly as the field: name="{fieldName}"
        const hidden = document.querySelector(`input[type="hidden"][name="${fieldName}"], input[name="${fieldName}"]`);
        if (hidden) {
            hidden.value = value;
            hidden.dispatchEvent(new Event('input', {bubbles: true}));
            hidden.dispatchEvent(new Event('change', {bubbles: true}));
        }
        return hidden;
    }

    function applyValueToField(fieldName, desiredValue) {
        if (desiredValue === null || desiredValue === undefined) return;

        const radioName = `${fieldName}___radio`;
        // Primary selector: exact radio with matching name & value
        const selector = `input[type="radio"][name="${radioName}"][value="${CSS ? safeEscape(desiredValue) : desiredValue}"]`;
        let radio = document.querySelector(selector);

        // Fallbacks
        if (!radio) {
            // 1) Try find label with data-mlm-field and data-mlm-value and use its 'for' attr
            const lbl = document.querySelector(`label[data-mlm-field="${fieldName}"][data-mlm-value="${desiredValue}"]`);
            if (lbl && lbl.getAttribute && lbl.getAttribute('for')) {
                radio = document.getElementById(lbl.getAttribute('for'));
            }
        }
        if (!radio) {
            // 2) Try matching by id pattern (common REDCap id uses opt-{field}_{value})
            radio = Array.from(document.querySelectorAll('input[type="radio"]')).find(r => {
                try {
                    return r.name && r.name.indexOf(fieldName) !== -1 && String(r.value) === String(desiredValue);
                } catch (e) {
                    return false;
                }
            });
        }

        if (!radio) {
            console.warn(`populateAnnualRadios: radio not found for field "${fieldName}" value "${desiredValue}"`);
            // still attempt to set the hidden input to preserve data if available
            setHiddenValue(fieldName, desiredValue);
            return;
        }

        const shouldBeChecked = String(desiredValue) === String(radio.value);
        // If it's already checked, ensure hidden value is set; optionally trigger calculate/branching
        if (radio.checked) {
            setHiddenValue(fieldName, desiredValue);
            // Try to call calculate/branching for consistency
            if (typeof window.calculate === 'function') {
                try {
                    window.calculate(fieldName);
                } catch (e) { /* ignore */
                }
            }
            if (typeof window.doBranching === 'function') {
                try {
                    window.doBranching(fieldName);
                } catch (e) { /* ignore */
                }
            }
            return;
        }

        // Try to click the radio so page-level onclick/onchange handlers run naturally.
        try {
            radio.click();
            // After click, ensure the hidden field matches (some REDCap handlers update it)
            setHiddenValue(fieldName, desiredValue);
            return;
        } catch (err) {
            // click() may throw in some restricted contexts — fall back to manual setting
        }

        try {
            radio.checked = true;
            // Dispatch events for listeners that rely on change
            radio.dispatchEvent(new Event('change', {bubbles: true}));
            radio.dispatchEvent(new Event('input', {bubbles: true}));

            // Ensure the hidden input is updated (REDCap usually requires this hidden value)
            setHiddenValue(fieldName, desiredValue);

            // Call page helper functions if present (defensive)
            if (typeof window.calculate === 'function') {
                try {
                    window.calculate(fieldName);
                } catch (e) {
                    console.warn('calculate() threw', e);
                }
            }
            if (typeof window.doBranching === 'function') {
                try {
                    window.doBranching(fieldName);
                } catch (e) {
                    console.warn('doBranching() threw', e);
                }
            }
        } catch (e) {
            console.error(`populateAnnualRadios: failed to set radio for ${fieldName}`, e);
        }
    }

    function populate() {
        if (typeof UKJSLE !== 'object' || UKJSLE === null) {
            console.info('UKJSLE missing — skipping last_annual population.');
            return;
        }
        const last = UKJSLE.last_annual;
        if (!last || typeof last !== 'object' || Object.keys(last).length === 0) {
            console.info('UKJSLE.last_annual empty or missing — nothing to populate.');
            return;
        }

        Object.entries(last).forEach(([fieldName, value]) => {
            try {
                // accept numbers or strings; ignore null/undefined
                if (value === null || value === undefined || value === '' || value === '0') {
                    // If empty string provided, we may want to reset radios (uncheck). We'll try to click any checked radio to reset.
                    const radioName = `${fieldName}___radio`;
                    const checked = document.querySelector(`input[type="radio"][name="${radioName}"]:checked`);
                    if (checked) {
                        try {
                            // many REDCap forms provide a reset link function radioResetVal(field,'form') — prefer calling it if present
                            if (typeof window.radioResetVal === 'function') {
                                try {
                                    window.radioResetVal(fieldName, 'form');
                                } catch (e) {
                                    checked.click();
                                }
                            } else {
                                checked.click();
                            }
                            setHiddenValue(fieldName, '');
                        } catch (e) {
                            checked.checked = false;
                            setHiddenValue(fieldName, '');
                        }
                    } else {
                        // still update hidden if present
                        setHiddenValue(fieldName, '');
                    }
                } else {
                    applyValueToField(fieldName, value);
                }
            } catch (e) {
                console.error('populateAnnualRadios: error processing', fieldName, e);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', populate, {once: true});
    } else {
        populate();
    }
})();

(function () {
    'use strict';

    // List of field names to set to 0 when aasd_slicc_blanks === "1" AND field is empty
    const TARGET_FIELDS = [
        'aa_menarche', 'aa_menarcheyrs', 'aa_menarchemths', 'aasd_ocu_oce', 'aasd_ocu_rcoa',
        'aasd_neu_cimp', 'aasd_neu_seiz', 'aasd_neu_cvae', 'aasd_neu_cpn', 'aasd_neu_tm',
        'aasd_ren_gfr', 'aasd_ren_prot', 'aasd_ren_esrd', 'aasd_pul_hyp', 'aasd_pul_fib',
        'aasd_pul_sl', 'aasd_pul_pleufib', 'aasd_pul_infar', 'aasd_car_acab', 'aasd_car_mie',
        'aasd_car_cm', 'aasd_car_vd', 'aasd_car_peri', 'aasd_per_clau', 'aasd_per_mtl',
        'aasd_per_stle', 'aasd_per_vt', 'aasd_gas_infar', 'aasd_gas_mi', 'aasd_gas_cp',
        'aasd_gas_sugtse', 'aasd_gas_pi', 'aasd_mus_aw', 'aasd_mus_dea', 'aasd_mus_ostpor',
        'aasd_mus_an', 'aasd_mus_ostmy', 'aasd_mus_rt', 'aasd_skin_alo', 'aasd_skin_esp',
        'aasd_skin_ulc', 'aasd_oth_diab', 'aasd_oth_malig', 'aasd_oth_pgfsa'
    ];

    // Utility: safe CSS escape if available, otherwise basic replace
    function esc(s) {
        if (window.CSS && typeof CSS.escape === 'function') return CSS.escape(String(s));
        return String(s).replace(/(["\\])/g, '\\$1');
    }

    // Returns a string current stored value of the field (hidden input), or empty string if none
    function getHiddenFieldValue(fieldName) {
        // REDCap typically has a hidden input named exactly fieldName
        const selector = `input[name="${fieldName}"]`;
        const hidden = document.querySelector(selector);
        return hidden ? String(hidden.value || '') : '';
    }

    // Set the hidden field (if present)
    function setHiddenFieldValue(fieldName, value) {
        const selector = `input[name="${fieldName}"]`;
        const hidden = document.querySelector(selector);
        if (hidden) {
            hidden.value = String(value);
            hidden.dispatchEvent(new Event('input', {bubbles: true}));
            hidden.dispatchEvent(new Event('change', {bubbles: true}));
            return true;
        }
        return false;
    }

    // Attempt to set the radio with name "{fieldName}___radio" to value "0"
    // Returns true if successful (radio clicked or set), false otherwise
    function setRadioToZero(fieldName) {
        const radioName = `${fieldName}___radio`;
        // Primary approach: find radio with name and value 0
        let candidate = document.querySelector(`input[type="radio"][name="${radioName}"][value="0"]`);

        // fallback: find label[data-mlm-field] matching value "0"
        if (!candidate) {
            const lbl = document.querySelector(`label[data-mlm-field="${fieldName}"][data-mlm-value="0"]`);
            if (lbl && lbl.getAttribute && lbl.getAttribute('for')) {
                candidate = document.getElementById(lbl.getAttribute('for'));
            }
        }

        // another fallback: find any radio whose name contains the fieldName and value is "0"
        if (!candidate) {
            candidate = Array.from(document.querySelectorAll('input[type="radio"]'))
                .find(r => r.name && r.name.indexOf(fieldName) !== -1 && String(r.value) === '0');
        }

        if (!candidate) {
            console.warn(`setRadioToZero: radio for ${fieldName} value 0 not found`);
            return false;
        }

        try {
            // If already checked, nothing to do, but ensure hidden value is set
            if (candidate.checked) {
                setHiddenFieldValue(fieldName, '0');
                return true;
            }

            // Prefer click so page-level handlers run
            candidate.click();

            // After click, ensure hidden input is set (some REDCap handlers update it)
            setHiddenFieldValue(fieldName, '0');

            // best-effort call to calculate & branching if present
            if (typeof window.calculate === 'function') {
                try {
                    window.calculate(fieldName);
                } catch (e) { /* ignore */
                }
            }
            if (typeof window.doBranching === 'function') {
                try {
                    window.doBranching(fieldName);
                } catch (e) { /* ignore */
                }
            }

            return true;
        } catch (err) {
            // click might fail in some environments; set checked & dispatch events as fallback
            try {
                candidate.checked = true;
                candidate.dispatchEvent(new Event('input', {bubbles: true}));
                candidate.dispatchEvent(new Event('change', {bubbles: true}));
                setHiddenFieldValue(fieldName, '0');
                if (typeof window.calculate === 'function') {
                    try {
                        window.calculate(fieldName);
                    } catch (e) {
                    }
                }
                if (typeof window.doBranching === 'function') {
                    try {
                        window.doBranching(fieldName);
                    } catch (e) {
                    }
                }
                return true;
            } catch (e2) {
                console.error(`setRadioToZero: failed to set ${fieldName}`, e2);
                return false;
            }
        }
    }

    // For one field, set to 0 only if it has no existing value
    function ensureFieldZeroIfEmpty(fieldName) {
        try {
            const current = getHiddenFieldValue(fieldName);
            // If current is empty (''), null, or only whitespace -> set to 0
            if (!current || String(current).trim() === '') {
                setRadioToZero(fieldName);
            } else {
                // existing value present — skip
                // console.debug(`Field ${fieldName} already has value "${current}" — skipping`);
            }
        } catch (e) {
            console.error('ensureFieldZeroIfEmpty error for', fieldName, e);
        }
    }

    // Handler when aasd_slicc_blanks changes.
    function onSliccBlanksChange(event) {
        const value = (event && event.target) ? String(event.target.value) : null;
        if (value === '1') { // YES selected
            // iterate targets and set to 0 if empty
            TARGET_FIELDS.forEach(ensureFieldZeroIfEmpty);
        }
        // If value !== '1' we do nothing (per your spec).
    }

    // Setup: attach listeners to the aasd_slicc_blanks radio inputs and run once if already selected
    function attachAndInit() {
        const radioName = 'aasd_slicc_blanks___radio';
        const radios = Array.from(document.querySelectorAll(`input[type="radio"][name="${radioName}"]`));

        if (!radios || radios.length === 0) {
            console.warn('attachAndInit: aasd_slicc_blanks radios not found');
            return;
        }

        radios.forEach(r => {
            r.addEventListener('change', onSliccBlanksChange);
        });

        // If one is already checked as "1", trigger immediately
        const checked = radios.find(r => r.checked === true);
        if (checked && String(checked.value) === '1') {
            // run asynchronously so that any other on-load scripts finish first
            setTimeout(() => {
                TARGET_FIELDS.forEach(ensureFieldZeroIfEmpty);
            }, 20);
        }
    }

    function insertDexaPreviousRow() {
        "use strict";

        try {
            var anchor = document.getElementById("aa_dexatbl-tr");
            if (!anchor) return;

            var innerTable = anchor.querySelector("table");
            if (!innerTable) return;

            if (innerTable.querySelector("#aa_dexa_previous_row")) return;

            var tbody = innerTable.querySelector("tbody");
            if (!tbody) {
                tbody = document.createElement("tbody");
                innerTable.appendChild(tbody);
            }

            // Safe extraction of UKJSLE.dexa
            var dexa = [];
            if (typeof UKJSLE !== "undefined" &&
                UKJSLE &&
                Array.isArray(UKJSLE.dexa) &&
                UKJSLE.dexa.length) {
                dexa = UKJSLE.dexa;
            }

            // Helper: format YYYY-MM-DD → DD/MM/YYYY
            function formatDate(value) {
                if (!value || typeof value !== "string") return "";
                var parts = value.split("-");
                if (parts.length !== 3) return value; // fallback if unexpected format
                return parts[2] + "/" + parts[1] + "/" + parts[0];
            }

            var newRow = document.createElement("tr");
            newRow.id = "aa_dexa_previous_row";

            var cell = document.createElement("td");
            cell.colSpan = 3;

            var strong = document.createElement("strong");
            strong.textContent = "Previous visits:";
            cell.appendChild(strong);

            if (dexa.length > 0) {
                cell.appendChild(document.createElement("br"));

                var histTable = document.createElement("table");
                histTable.style.borderCollapse = "collapse";

                var histBody = document.createElement("tbody");
                var added = 0;

                for (var i = 0; i < dexa.length; i++) {
                    var item = dexa[i];
                    if (item && typeof item === "object") {
                        var r = document.createElement("tr");

                        var tdDate = document.createElement("td");
                        tdDate.textContent = formatDate(item.date);

                        var tdStatus = document.createElement("td");
                        tdStatus.textContent = item.status != null ? String(item.status) : "";

                        r.appendChild(tdDate);
                        r.appendChild(tdStatus);
                        histBody.appendChild(r);
                        added++;
                    }
                }

                if (added === 0) {
                    cell.appendChild(document.createTextNode(" None recorded."));
                } else {
                    histTable.appendChild(histBody);
                    cell.appendChild(histTable);
                }

            } else {
                cell.appendChild(document.createTextNode(" None recorded."));
            }

            newRow.appendChild(cell);

            if (tbody.firstChild) {
                tbody.insertBefore(newRow, tbody.firstChild);
            } else {
                tbody.appendChild(newRow);
            }

        } catch (err) {
            console.error("insertDexaPreviousRow error:", err);
        }
    }

    function insertRenalBiopsyPreviousRow() {
        "use strict";

        try {
            var anchor = document.getElementById("aa_renbioptbl-tr");
            if (!anchor) return;

            var innerTable = anchor.querySelector("table");
            if (!innerTable) return;

            if (innerTable.querySelector("#aa_renalbiopsy_previous_row")) return;

            var tbody = innerTable.querySelector("tbody");
            if (!tbody) {
                tbody = document.createElement("tbody");
                innerTable.appendChild(tbody);
            }

            // Safe extraction of UKJSLE.dexa
            var renalbiopsy = [];
            if (typeof UKJSLE !== "undefined" &&
                UKJSLE &&
                Array.isArray(UKJSLE.renalbiopsy) &&
                UKJSLE.renalbiopsy.length) {
                renalbiopsy = UKJSLE.renalbiopsy;
            }

            // Helper: format YYYY-MM-DD → DD/MM/YYYY
            function formatDate(value) {
                if (!value || typeof value !== "string") return "";
                var parts = value.split("-");
                if (parts.length !== 3) return value; // fallback if unexpected format
                return parts[2] + "/" + parts[1] + "/" + parts[0];
            }

            var newRow = document.createElement("tr");
            newRow.id = "aa_renalbiopsy_previous_row";

            var cell = document.createElement("td");
            cell.colSpan = 3;

            var strong = document.createElement("strong");
            strong.textContent = "Previous visits:";
            cell.appendChild(strong);

            if (renalbiopsy.length > 0) {
                cell.appendChild(document.createElement("br"));

                var histTable = document.createElement("table");
                histTable.style.borderCollapse = "collapse";

                var histBody = document.createElement("tbody");
                var added = 0;

                for (var i = 0; i < renalbiopsy.length; i++) {
                    var item = renalbiopsy[i];
                    if (item && typeof item === "object") {
                        var r = document.createElement("tr");

                        var tdDate = document.createElement("td");
                        tdDate.textContent = formatDate(item.date);

                        var tdStatus = document.createElement("td");
                        tdStatus.textContent = item.status != null ? String(item.status) : "";

                        var tdNep = document.createElement("td");
                        tdNep.textContent = item.nephritis != null ? String(item.nephritis) : "";

                        r.appendChild(tdDate);
                        r.appendChild(tdStatus);
                        r.appendChild(tdNep);
                        histBody.appendChild(r);
                        added++;
                    }
                }

                if (added === 0) {
                    cell.appendChild(document.createTextNode(" None recorded."));
                } else {
                    histTable.appendChild(histBody);
                    cell.appendChild(histTable);
                }

            } else {
                cell.appendChild(document.createTextNode(" None recorded."));
            }

            newRow.appendChild(cell);

            if (tbody.firstChild) {
                tbody.insertBefore(newRow, tbody.firstChild);
            } else {
                tbody.appendChild(newRow);
            }

        } catch (err) {
            console.error("insertRenalBiopsyPreviousRow error:", err);
        }
    }

    function insertOpthalmologyPreviousRow() {
        "use strict";

        try {
            var anchor = document.getElementById("aa_ophtbl-tr");
            if (!anchor) return;

            var innerTable = anchor.querySelector("table");
            if (!innerTable) return;

            if (innerTable.querySelector("#aa_opthalmology_previous_row")) return;

            var tbody = innerTable.querySelector("tbody");
            if (!tbody) {
                tbody = document.createElement("tbody");
                innerTable.appendChild(tbody);
            }

            // Safe extraction of UKJSLE.dexa
            var opthalmology = [];
            if (typeof UKJSLE !== "undefined" &&
                UKJSLE &&
                Array.isArray(UKJSLE.opthalmology) &&
                UKJSLE.opthalmology.length) {
                opthalmology = UKJSLE.opthalmology;
            }

            // Helper: format YYYY-MM-DD → DD/MM/YYYY
            function formatDate(value) {
                if (!value || typeof value !== "string") return "";
                if (value.length !== 10) return value;
                var parts = value.split("-");
                if (parts.length !== 3) return value; // fallback if unexpected format
                return parts[2] + "/" + parts[1] + "/" + parts[0];
            }

            var newRow = document.createElement("tr");
            newRow.id = "aa_opthalmology_previous_row";

            var cell = document.createElement("td");
            cell.colSpan = 3;

            var strong = document.createElement("strong");
            strong.textContent = "Previous visits:";
            cell.appendChild(strong);

            if (opthalmology.length > 0) {
                cell.appendChild(document.createElement("br"));

                var histTable = document.createElement("table");
                histTable.style.borderCollapse = "collapse";

                var histBody = document.createElement("tbody");
                var added = 0;

                for (var i = 0; i < opthalmology.length; i++) {
                    var item = opthalmology[i];
                    if (item && typeof item === "object") {
                        var r = document.createElement("tr");

                        var tdDate = document.createElement("td");
                        tdDate.textContent = formatDate(item.date);
                        tdDate.style.fontWeight = "normal";

                        var tdStatus = document.createElement("td");
                        tdStatus.textContent = item.result != null ? String(item.result) : "";
                        tdStatus.style.fontWeight = "normal";

                        var tdNep = document.createElement("td");
                        tdNep.textContent = item.where != null ? String(item.where) : "";
                        tdNep.style.fontWeight = "normal";

                        r.appendChild(tdDate);
                        r.appendChild(tdStatus);
                        r.appendChild(tdNep);
                        histBody.appendChild(r);
                        added++;
                    }
                }

                if (added === 0) {
                    cell.appendChild(document.createTextNode(" None recorded."));
                } else {
                    histTable.appendChild(histBody);
                    cell.appendChild(histTable);
                }

            } else {
                cell.appendChild(document.createTextNode(" None recorded."));
            }

            newRow.appendChild(cell);

            if (tbody.firstChild) {
                tbody.insertBefore(newRow, tbody.firstChild);
            } else {
                tbody.appendChild(newRow);
            }

        } catch (err) {
            console.error("insertOpthalmologyPreviousRow error:", err);
        }
    }

    function insertRegistrationData() {
        "use strict";

        var anchor = document.getElementById("aa_ophtbl-tr");
        if (!anchor) return;

        var values = [];
        if (typeof UKJSLE !== "undefined" &&
            UKJSLE &&
            Array.isArray(UKJSLE.opthalmology) &&
            UKJSLE.pptinfo.length) {
            values = UKJSLE.pptinfo;
        }

    }

    /**
     * Inserts "Registration date" and "Discharge date" lines into the REDCap
     * record header (#record_display_name), directly above the DAG line
     * (e.g. "Liverpool").
     *
     * Data source: UKJSLE.pptinfo.registrationdate / UKJSLE.pptinfo.dischargedate
     *
     * Defensive behaviour:
     *  - Never throws; all failures are logged with console.warn and return false.
     *  - Checks that UKJSLE and UKJSLE.pptinfo exist and are objects.
     *  - Accepts pptinfo as a keyed object/array, or as an array whose first
     *    element holds the keys.
     *  - Only reads own properties (ignores anything inherited via the prototype).
     *  - Only accepts string/number values; anything else shows a fallback.
     *  - Uses textContent (never innerHTML), so data values cannot inject HTML.
     *  - Idempotent: calling it repeatedly updates the lines rather than
     *    duplicating them.
     *  - If the DAG line is absent, the lines are appended at the end instead.
     *
     * @returns {boolean} true if the lines were inserted, false otherwise.
     */
    function insertRegistrationData() {
        'use strict';

        var CONTAINER_ID = 'record_display_name';
        var MARKER_ATTR = 'data-ukjsle-pptinfo';
        var FALLBACK_TEXT = 'Not recorded';
        var LOG_PREFIX = 'insertRegistrationData: ';

        function hasOwn(obj, key) {
            return Object.prototype.hasOwnProperty.call(obj, key);
        }

        function resolvePptInfo() {
            // typeof is safe even if UKJSLE was never declared
            if (typeof UKJSLE === 'undefined' || UKJSLE === null || typeof UKJSLE !== 'object') {
                return null;
            }
            var info = UKJSLE.pptinfo;
            if (info === null || typeof info !== 'object') {
                return null;
            }
            // Support [ { registrationdate: ..., dischargedate: ... } ]
            if (Array.isArray(info) && !hasOwn(info, 'registrationdate') && !hasOwn(info, 'dischargedate')) {
                info = info.length > 0 ? info[0] : null;
                if (info === null || typeof info !== 'object') {
                    return null;
                }
            }
            return info;
        }

        function toDisplayText(value) {
            if (typeof value !== 'string' && typeof value !== 'number') {
                return FALLBACK_TEXT;
            }
            var text = String(value).trim();
            return text === '' ? FALLBACK_TEXT : text;
        }

        function buildLine(label, value) {
            var div = document.createElement('div');
            div.setAttribute(MARKER_ATTR, '');
            div.style.fontSize = '13px';
            div.style.color = '#FF0000';

            var span = document.createElement('span');
            span.className = 'nowrap';
            span.style.color = '#008000';
            span.style.margin = '0 2px';
            span.textContent = label + ': ' + toDisplayText(value);

            div.appendChild(span);
            return div;
        }

        function firstChildDiv(parent) {
            for (var i = 0; i < parent.children.length; i++) {
                if (parent.children[i].tagName === 'DIV') {
                    return parent.children[i];
                }
            }
            return null;
        }

        try {
            // 1. Validate the data
            var info = resolvePptInfo();
            if (!info) {
                console.warn(LOG_PREFIX + 'UKJSLE.pptinfo is missing or not an object.');
                return false;
            }
            var registrationDate = hasOwn(info, 'registrationdate') ? info.registrationdate : undefined;
            var dischargeDate = hasOwn(info, 'dischargedate') ? info.dischargedate : undefined;

            // 2. Validate the DOM
            var container = document.getElementById(CONTAINER_ID);
            if (!container) {
                console.warn(LOG_PREFIX + '#' + CONTAINER_ID + ' not found on this page.');
                return false;
            }
            var inner = firstChildDiv(container);
            if (!inner) {
                console.warn(LOG_PREFIX + 'expected inner <div> inside #' + CONTAINER_ID + ' not found.');
                return false;
            }

            // 3. Remove any lines from a previous call (idempotency)
            var existing = inner.querySelectorAll('[' + MARKER_ATTR + ']');
            for (var j = 0; j < existing.length; j++) {
                if (existing[j].parentNode) {
                    existing[j].parentNode.removeChild(existing[j]);
                }
            }

            // 4. Locate the DAG line (first direct child <div> containing span.nowrap)
            var anchor = null;
            for (var k = 0; k < inner.children.length; k++) {
                var child = inner.children[k];
                if (child.tagName === 'DIV' && child.querySelector('span.nowrap')) {
                    anchor = child;
                    break;
                }
            }

            // 5. Insert both lines in one DOM operation
            var fragment = document.createDocumentFragment();
            fragment.appendChild(buildLine('Registration date', registrationDate));
            fragment.appendChild(buildLine('Discharge date', dischargeDate));

            // insertBefore with a null anchor appends to the end
            inner.insertBefore(fragment, anchor);
            return true;
        } catch (err) {
            console.warn(LOG_PREFIX + 'unexpected error.', err);
            return false;
        }
    }


    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachAndInit, {once: true});
        document.addEventListener('DOMContentLoaded', insertDexaPreviousRow, {once: true});
        document.addEventListener('DOMContentLoaded', insertRenalBiopsyPreviousRow, {once: true});
        document.addEventListener('DOMContentLoaded', insertOpthalmologyPreviousRow, {once: true});
    } else {
        attachAndInit();
        insertDexaPreviousRow();
        insertRenalBiopsyPreviousRow();
    }

})();