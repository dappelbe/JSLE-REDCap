let uk_jsle_Applied = false;

/**
 * Function to apply textual changes based on a JavaScript array that has been pushed into the page.
 */
function applyTextReplacements() {
    if (uk_jsle_Applied) return;
    uk_jsle_Applied = true;

    if (
        typeof UKJSLE === 'undefined' ||
        !Array.isArray(UKJSLE.text2replace)
    ) {
        return;
    }

    let html = document.body.innerHTML;

    UKJSLE.text2replace.forEach(item => {
        if (!item.text || !item.replace) return;

        const escaped = item.text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        html = html.replace(new RegExp(escaped, 'g'), item.replace);
    });

    document.body.innerHTML = html;
}

/**
 * A function to add an additional logo to represent forms that are not available.
 */
 function addNotAvailableStatusRow() {
    // Extract REDCap version from URL (e.g. redcap_v15.5.21)
    var match = window.location.pathname.match(/\/(redcap_v[\d.]+)\//);
    if (!match) return;

    var redcapVersion = match[1];

    // Get the legend table
    var table = document.getElementById('status-icon-legend');
    if (!table) return;

    // Ensure tbody exists
    var tbody = table.tBodies.length
    ? table.tBodies[0]
    : table.appendChild(document.createElement('tbody'));

    // Create row
    var tr = document.createElement('tr');

    // First cell
    var td1 = document.createElement('td');
    td1.className = 'nowrap';
    td1.style.paddingRight = '5px';

    var img = document.createElement('img');
    img.src = '/' + redcapVersion + '/Resources/images/close_button_black.png';
    img.alt = 'Not available';
    img.height = 10;

    td1.appendChild(img);
    td1.appendChild(document.createTextNode(' Not available'));

    // Second cell
    var td2 = document.createElement('td');
    td2.className = 'nowrap';

    // Assemble row
    tr.appendChild(td1);
    tr.appendChild(td2);

    // Append row
    tbody.appendChild(tr);
}

function replaceGrayStatusIcons() {
    // Defensive checks
    if (typeof UKJSLE !== 'object' || !UKJSLE.statusfields) return;

    // Extract REDCap version from URL
    var versionMatch = window.location.pathname.match(/\/(redcap_v[\d.]+)\//);
    if (!versionMatch) return;

    var redcapVersion = versionMatch[1];
    var newImgSrc = '/' + redcapVersion + '/Resources/images/close_button_black.png';

    // Cache all anchor tags once
    var links = document.getElementsByTagName('a');
    if (!links.length) return;

    // Loop through statusfields[eventid][crf]
    for (var eventid in UKJSLE.statusfields) {
        if (!UKJSLE.statusfields.hasOwnProperty(eventid)) continue;

        var crfs = UKJSLE.statusfields[eventid];
        if (typeof crfs !== 'object') continue;

        for (var crf in crfs) {
            if (!crfs.hasOwnProperty(crf)) continue;

            // Only act on the value you care about
            if (crfs[crf][0] !== -1) continue;

            // Scan anchor tags
            for (var i = 0; i < links.length; i++) {
                var a = links[i];
                if (!a.href) continue;

                try {
                    var url = new URL(a.href, window.location.origin);

                    if (
                        url.searchParams.get('event_id') === String(eventid) &&
                        url.searchParams.get('page') === String(crf)
                    ) {
                        var img = a.querySelector('img');
                        if (!img || !img.src) continue;

                        // Only replace gray icons
                        if (img.src.indexOf('circle_gray.png') !== -1) {
                            img.src = newImgSrc;
                        }
                    }
                } catch (e) {
                    // Ignore malformed URLs or unexpected errors
                    continue;
                }
            }
        }
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
        div.style.color = '#0000FF';

        var span = document.createElement('span');
        span.className = 'nowrap';
        span.style.color = '#0000FF';
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
        fragment.appendChild(buildLine('Recruitment date', registrationDate));
        fragment.appendChild(buildLine('Discharge date', dischargeDate));

        // insertBefore with a null anchor appends to the end
        inner.insertBefore(fragment, anchor);
        return true;
    } catch (err) {
        console.warn(LOG_PREFIX + 'unexpected error.', err);
        return false;
    }
}


// Normal load
document.addEventListener('DOMContentLoaded', applyTextReplacements);
document.addEventListener('DOMContentLoaded', addNotAvailableStatusRow);
document.addEventListener('DOMContentLoaded', replaceGrayStatusIcons);
document.addEventListener('DOMContentLoaded', insertRegistrationData);

// Back/forward cache restore
window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
        applyTextReplacements();
        addNotAvailableStatusRow();
        replaceGrayStatusIcons();
        insertRegistrationData();
    }
});