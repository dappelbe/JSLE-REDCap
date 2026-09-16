function parseDMY(dateStr) {
    if (!dateStr) return null;
    const parts = dateStr.trim().split('-');
    if (parts.length !== 3) return null;

    const d = parseInt(parts[0], 10);
    const m = parseInt(parts[1], 10);
    const y = parseInt(parts[2], 10);

    if (Number.isNaN(d) || Number.isNaN(m) || Number.isNaN(y)) return null;
    // month in JS Date is 0-based
    return new Date(y, m - 1, d);
}

function formatDMY(dateObj) {
    const dd = String(dateObj.getDate()).padStart(2, '0');
    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
    const yyyy = dateObj.getFullYear();
    return `${dd}-${mm}-${yyyy}`;
}

function clampNonNegativeInt(v) {
    const n = parseInt(v, 10);
    if (Number.isNaN(n) || n < 0) return 0;
    return n;
}

/* Core update function
- fromAgeChange: boolean indicating the event came from yrs/mths input.
If true, we always regenerate output (when inputs valid).
If false (e.g. DOB changed), we only set onset date if the onset field is empty.
*/
function updateOnsetDate(fromAgeChange = false) {
    const dobField = document.querySelector('[name="demo_dob"]');
    const yrsField = document.querySelector('[name="demo_onsymp_yrs"]');
    const mthsField = document.querySelector('[name="demo_onsymp_mths"]');
    const onsetField = document.querySelector('[name="demo_onsymp_date"]');

    if (!dobField || !yrsField || !mthsField || !onsetField ) return;

    const dob = parseDMY(dobField.value);
    if (!dob || isNaN(dob.getTime())) {
        // no valid DOB -> nothing to do
        return;
    }

    const years = clampNonNegativeInt(yrsField.value);
    const months = clampNonNegativeInt(mthsField.value);

    // Only proceed if at least one of years/months is provided (> 0)
    if (years === 0 && months === 0) {
        // user explicitly zeroed both fields -> we consider this "no onset offset"
        // so we won't auto-generate (per your requirement). You may adjust to allow zero.
        return;
    }

    // If onset field is non-empty and this is NOT an age change event, don't overwrite it.
    if (!fromAgeChange && onsetField.value.trim() !== '') {
        return;
    }

    // Build onset date from DOB + years + months; day forced to 1
    const onsetDate = new Date(dob.getTime()); // copy
    onsetDate.setDate(1); // day = 01 as required
    onsetDate.setFullYear(onsetDate.getFullYear() + years);
    // JS handles month overflow automatically
    onsetDate.setMonth(onsetDate.getMonth() + months);

    // Validation: onset date must not be in the future
    const today = new Date();
    // We compare by year-month-day ignoring time by normalizing both to 1st of their months or using time directly.
    // Since onset day is forced to 1, direct comparison is fine.
    // Clear time-of-day for robust compare:
    const onsetYMD = new Date(onsetDate.getFullYear(), onsetDate.getMonth(), onsetDate.getDate());
    const todayYMD = new Date(today.getFullYear(), today.getMonth(), today.getDate());

    // All good -> set formatted D-M-Y
    onsetField.value = formatDMY(onsetDate);
}

/* Core update function
- fromAgeChange: boolean indicating the event came from yrs/mths input.
If true, we always regenerate output (when inputs valid).
If false (e.g. DOB changed), we only set onset date if the onset field is empty.
*/
function updatePresentationDate(fromAgeChange = false) {
    const dobField = document.querySelector('[name="demo_dob"]');
    const yrsField = document.querySelector('[name="demo_pres_yrs"]');
    const mthsField = document.querySelector('[name="demo_pres_mths"]');
    const presentField = document.querySelector('[name="demo_pres_date"]');

    if (!dobField || !yrsField || !mthsField || !presentField ) return;

    const dob = parseDMY(dobField.value);
    if (!dob || isNaN(dob.getTime())) {
        // no valid DOB -> nothing to do
        return;
    }

    const years = clampNonNegativeInt(yrsField.value);
    const months = clampNonNegativeInt(mthsField.value);

    // Only proceed if at least one of years/months is provided (> 0)
    if (years === 0 && months === 0) {
        // user explicitly zeroed both fields -> we consider this "no onset offset"
        // so we won't auto-generate (per your requirement). You may adjust to allow zero.
        return;
    }

    // If onset field is non-empty and this is NOT an age change event, don't overwrite it.
    if (!fromAgeChange && presentField.value.trim() !== '') {
        return;
    }

    // Build onset date from DOB + years + months; day forced to 1
    const onsetDate = new Date(dob.getTime()); // copy
    onsetDate.setDate(1); // day = 01 as required
    onsetDate.setFullYear(onsetDate.getFullYear() + years);
    // JS handles month overflow automatically
    onsetDate.setMonth(onsetDate.getMonth() + months);

    // Validation: onset date must not be in the future
    const today = new Date();
    // We compare by year-month-day ignoring time by normalizing both to 1st of their months or using time directly.
    // Since onset day is forced to 1, direct comparison is fine.
    // Clear time-of-day for robust compare:
    const onsetYMD = new Date(onsetDate.getFullYear(), onsetDate.getMonth(), onsetDate.getDate());
    const todayYMD = new Date(today.getFullYear(), today.getMonth(), today.getDate());

    // All good -> set formatted D-M-Y
    presentField.value = formatDMY(onsetDate);
}

/* Core update function
- fromAgeChange: boolean indicating the event came from yrs/mths input.
If true, we always regenerate output (when inputs valid).
If false (e.g. DOB changed), we only set onset date if the onset field is empty.
*/
function updateDiagnosticDate(fromAgeChange = false) {
    const dobField = document.querySelector('[name="demo_dob"]');
    const yrsDiagnosticField = document.querySelector('[name="demo_diag_yrs"]');
    const mthsDiagnosticField = document.querySelector('[name="demo_diag_mths"]');
    const diagnosticField = document.querySelector('[name="demo_diag_date"]');

    if (!dobField || !yrsDiagnosticField || !mthsDiagnosticField || !diagnosticField ) return;

    const dob = parseDMY(dobField.value);
    if (!dob || isNaN(dob.getTime())) {
        // no valid DOB -> nothing to do
        return;
    }

    const years = clampNonNegativeInt(yrsDiagnosticField.value);
    const months = clampNonNegativeInt(mthsDiagnosticField.value);

    // Only proceed if at least one of years/months is provided (> 0)
    if (years === 0 && months === 0) {
        // user explicitly zeroed both fields -> we consider this "no onset offset"
        // so we won't auto-generate (per your requirement). You may adjust to allow zero.
        return;
    }

    // If onset field is non-empty and this is NOT an age change event, don't overwrite it.
    if (!fromAgeChange && diagnosticField.value.trim() !== '') {
        return;
    }

    // Build onset date from DOB + years + months; day forced to 1
    const onsetDate = new Date(dob.getTime()); // copy
    onsetDate.setDate(1); // day = 01 as required
    onsetDate.setFullYear(onsetDate.getFullYear() + years);
    // JS handles month overflow automatically
    onsetDate.setMonth(onsetDate.getMonth() + months);

    // Validation: onset date must not be in the future
    const today = new Date();
    // We compare by year-month-day ignoring time by normalizing both to 1st of their months or using time directly.
    // Since onset day is forced to 1, direct comparison is fine.
    // Clear time-of-day for robust compare:
    const onsetYMD = new Date(onsetDate.getFullYear(), onsetDate.getMonth(), onsetDate.getDate());
    const todayYMD = new Date(today.getFullYear(), today.getMonth(), today.getDate());

    // All good -> set formatted D-M-Y
    diagnosticField.value = formatDMY(onsetDate);
}

function forceUppercaseInput(input) {
    if (!input || typeof input.value !== 'string') return;
    const start = input.selectionStart;
    const end = input.selectionEnd;

    input.value = input.value.toUpperCase();

    // Preserve cursor position
    if (start !== null && end !== null) {
        input.setSelectionRange(start, end);
    }
}

/* Wiring: use input events for responsiveness.
- yrs/mths input => treat as age change (force regeneration)
- dob input    => only set if onset empty (not overwrite user-entered onset)
*/
document.addEventListener('DOMContentLoaded', function () {
    const dobField = document.querySelector('[name="demo_dob"]');
    const yrsField = document.querySelector('[name="demo_onsymp_yrs"]');
    const mthsField = document.querySelector('[name="demo_onsymp_mths"]');

    const yrsPresentationField = document.querySelector('[name="demo_pres_yrs"]');
    const mthsPresentationField = document.querySelector('[name="demo_pres_mths"]');

    const yrsDiagnosticField = document.querySelector('[name="demo_diag_yrs"]');
    const mthsDiagnosticionField = document.querySelector('[name="demo_diag_mths"]');

    if (yrsField) {
        yrsField.addEventListener('input', function () {
            updateOnsetDate(true);
        });
    }
    if (mthsField) {
        mthsField.addEventListener('input', function () {
            updateOnsetDate(true);
        });
    }
    if (dobField) {
        dobField.addEventListener('input', function () {
            updateOnsetDate(false);
            updatePresentationDate(true);
            updateDiagnosticDate(true);
        });
    }

    if (yrsPresentationField) {
        yrsPresentationField.addEventListener('input', function () {
            updatePresentationDate(true);
        });
    }
    if (mthsPresentationField) {
        mthsPresentationField.addEventListener('input', function () {
            updatePresentationDate(true);
        });
    }

    if (yrsDiagnosticField) {
        yrsDiagnosticField.addEventListener('input', function () {
            updateDiagnosticDate(true);
        });
    }
    if (mthsDiagnosticionField) {
        mthsDiagnosticionField.addEventListener('input', function () {
            updateDiagnosticDate(true);
        });
    }
    updateOnsetDate(false);

    const field = document.querySelector('[name="demo_pc"]');
    if (field) {
        field.addEventListener('input', function () {
            forceUppercaseInput(this);
        });
    }
});
