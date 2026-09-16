function addCollectedSamplesNotice() {
    // Check that UKJSLE and UKJSLE.samples exist
    if (typeof UKJSLE === 'undefined' || UKJSLE === null) return;
    if (typeof UKJSLE.samples !== 'object' || UKJSLE.samples === null) return;

    const dateRow = document.getElementById('samples_date-tr');
    if (!dateRow) return;

    const samples = UKJSLE.samples;
    const messages = [];

    // == so both 1 and "1" match
    //if (samples.edta == 1) messages.push('- The EDTA Sample was last collected on ' + samples.edta_date);
    //if (samples.pmbc == 1) messages.push('- The PBMC Sample was last collected on ' + samples.pmbc_date);

    if (messages.length === 0) return;

    // Remove any previously inserted notice so it isn't duplicated
    const existing = document.getElementById('samples-collected-note-tr');
    if (existing) existing.remove();

    // Build the new row, spanning both REDCap columns
    const row = document.createElement('tr');
    row.id = 'samples-collected-note-tr';

    const cell = document.createElement('td');
    cell.colSpan = 2;
    cell.className = 'labelrc';
    cell.style.color = 'red';
    cell.style.fontWeight = 'bold';

    messages.forEach(function (text) {
        const line = document.createElement('div');
        line.textContent = text;
        cell.appendChild(line);
    });

    row.appendChild(cell);

    // Insert directly after the samples_date row (i.e. before samples-tr)
    dateRow.insertAdjacentElement('afterend', row);
}

function appendEdtaDateToLabel() {
    // Check that UKJSLE and UKJSLE.samples exist
    if (typeof UKJSLE === 'undefined' || UKJSLE === null) return;
    if (typeof UKJSLE.samples !== 'object' || UKJSLE.samples === null) return;

    const edtaDate = UKJSLE.samples.edta_date;
    if (!edtaDate) return; // nothing to append

    const label = document.getElementById('label-samples-1');
    if (!label) return;

    // Reuse the span if it already exists, so the date isn't appended twice
    let dateSpan = document.getElementById('edta-date-suffix');
    if (!dateSpan) {
        dateSpan = document.createElement('span');
        dateSpan.id = 'edta-date-suffix';
        dateSpan.style.color = 'darkcyan';
        label.appendChild(dateSpan);
    }

    dateSpan.textContent = ' (last collected ' + edtaDate + ')';
}

function appendPMBCDateToLabel() {
    // Check that UKJSLE and UKJSLE.samples exist
    if (typeof UKJSLE === 'undefined' || UKJSLE === null) return;
    if (typeof UKJSLE.samples !== 'object' || UKJSLE.samples === null) return;

    const pmbcDate = UKJSLE.samples.pmbc_date;
    if (!pmbcDate) return; // nothing to append

    const label = document.getElementById('label-samples-2');
    if (!label) return;

    // Reuse the span if it already exists, so the date isn't appended twice
    let dateSpan = document.getElementById('pmbc-date-suffix');
    if (!dateSpan) {
        dateSpan = document.createElement('span');
        dateSpan.id = 'pmbc-date-suffix';
        dateSpan.style.color = 'darkcyan';
        label.appendChild(dateSpan);
    }

    dateSpan.textContent = ' (last collected ' + pmbcDate + ')';
}

// Run once the page has loaded
document.addEventListener('DOMContentLoaded', addCollectedSamplesNotice);
document.addEventListener('DOMContentLoaded', appendEdtaDateToLabel);
document.addEventListener('DOMContentLoaded', appendPMBCDateToLabel);