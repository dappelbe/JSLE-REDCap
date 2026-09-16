(function populateSliccCheckboxesOnLoad() {
    function populateSliccCheckboxes() {
        // safety checks
        if (typeof UKJSLE !== 'object' || !UKJSLE) {
            console.info('UKJSLE not defined — skipping slicc population.');
            return;
        }
        const last = UKJSLE.last_slicc;
        if (!last || typeof last !== 'object' || Object.keys(last).length === 0) {
            console.info('UKJSLE.last_slicc empty or missing — nothing to populate.');
            return;
        }

        // For each field in last_slicc
        Object.entries(last).forEach(([fieldName, valueMap]) => {
            if (!valueMap || typeof valueMap !== 'object') return;

            // First handle NAVU if present
            if (Object.prototype.hasOwnProperty.call(valueMap, 'NAVU')) {
                const navuVal = valueMap['NAVU'];
                // Example hidden input name: __chk__acr_q01_malrash_RC_NAVU
                const navuSelector = `input[type="hidden"][name="__chk__${fieldName}_RC_NAVU"]`;
                const navuInput = document.querySelector(navuSelector);
                if (navuInput) {
                    // set the value exactly as provided (usually "0" or "1")
                    navuInput.value = navuVal;
                    // If there's any listener watching value change via events, dispatch input event
                    navuInput.dispatchEvent(new Event('input', {bubbles: true}));
                } else {
                    // fallback: maybe name uses different format - try contains
                    const alt = Array.from(document.querySelectorAll('input[type="hidden"]'))
                        .find(el => el.name && el.name.includes(fieldName) && el.name.toUpperCase().endsWith('_NAVU'));
                    if (alt) {
                        alt.value = navuVal;
                        alt.dispatchEvent(new Event('input', {bubbles: true}));
                    }
                }
            }

            // Now handle checkbox values. Keys other than NAVU are checkbox codes (e.g., "1","0","2"...)
            Object.entries(valueMap).forEach(([valKey, flag]) => {
                if (valKey === 'NAVU') return;

                // flag is "1" or "0" (strings), interpret truthiness
                const shouldBeChecked = String(flag) === '1';

                // Try primary selector: input checkbox with name="__chkn__{fieldName}" and code="{valKey}"
                let input = document.querySelector(
                    `input[type="checkbox"][name="__chkn__${fieldName}"][code="${valKey}"]`
                );

                // If not found, fallback to label[data-mlm-field][data-mlm-value] -> label.for -> input#id
                if (!input) {
                    const lbl = document.querySelector(
                        `label[data-mlm-field="${CSS.escape(fieldName)}"][data-mlm-value="${CSS.escape(valKey)}"]`
                    );
                    if (lbl && lbl.getAttribute('for')) {
                        input = document.getElementById(lbl.getAttribute('for'));
                    } else {
                        // another fallback: inputs with id containing fieldName and RC_{valKey}
                        input = document.querySelector(`input[type="checkbox"][id*="${fieldName}"][id$="_RC_${valKey}"]`);
                    }
                }

                if (!input) {
                    // Could not find the checkbox — log and continue
                    // console.debug(`Checkbox for ${fieldName} value ${valKey} not found.`);
                    return;
                }

                // Only add checks from the previous SLICC state; never uncheck an already checked box.
                const currentlyChecked = Boolean(input.checked);
                if (!currentlyChecked && shouldBeChecked) {
                    // Use .click() to ensure page-level onclick/onchange handlers run as they would on manual click.
                    // But clicking will toggle the checked state automatically.
                    try {
                        input.click();
                    } catch (err) {
                        // If click() fails for some reason, set checked and dispatch change
                        input.checked = true;
                        input.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                }
            }); // end each valueMap entry
        }); // end each field
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', populateSliccCheckboxes, {once: true});
    } else {
        // already ready
        populateSliccCheckboxes();
    }
})();
