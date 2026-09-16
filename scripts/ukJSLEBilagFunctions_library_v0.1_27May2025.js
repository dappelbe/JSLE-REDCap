/**
 * Class to hold functions that can be unit tested and do not rely on external dependencies, such as jQuery.
 *
 * @author Duncan Appelbe
 * @version 0.1.27May2025
 */


var RTX_TABLE = {
    name:          'rtx',
    fieldName:     'bilag_tblrtx',
    anchorPattern: /rituximab/i,
    emptyText:     'No previous rituximab records',
    columns: [
        { header: 'Visit date',    key: 'date',                  isDate: true },
        { header: 'Dose',          key: 'bilag_rtxdose'                       },
        { header: 'Num Infusions', key: 'bilag_rtxnuminfusions'               },
        { header: 'Date given 1',  key: 'bilag_rtxdate1',        isDate: true },
        { header: 'Date given 2',  key: 'bilag_rtxdate2',        isDate: true },
        { header: 'Notes',         key: 'bilag_rtxnotes'                      }
    ]
};

var BELIMUMAB_TABLE = {
    name:          'belimumab',
    fieldName:     'bilag_tblbelim',
    anchorPattern: /belimumab/i,
    emptyText:     'No previous belimumab records',
    columns: [
        { header: 'Visit date', key: 'date',                   isDate: true },
        { header: 'Dose',       key: 'bilag_belimumdose'                    },
        { header: 'Route',      key: 'bilag_belimumroute'                   },
        { header: 'Frequency',  key: 'bilag_belimumfreq'                    },
        { header: 'Start date', key: 'bilag_belimumstartdate', isDate: true },
        { header: 'End date',   key: 'bilag_belimumstopdate',  isDate: true },
        { header: 'Notes',      key: 'bilag_belimumnotes'                   }
    ]
};

var CYCLO_TABLE = {
    name:          'cyclo',
    fieldName:     'bilag_tblcylcophos',   // NB: spelling must match the REDCap field name
    anchorPattern: /cyclophosphamide/i,
    emptyText:     'No previous Cyclophosphamide records',
    columns: [
        { header: 'Visit date',                 key: 'date',                    isDate: true },
        { header: 'Dose since last visit',      key: 'bilag_cyclophosdose'                   },
        { header: 'Infusions since last visit', key: 'bilag_cyclophosnuminfus'               },
        { header: 'Route',                      key: 'bilag_cyclophosroute'                  },
        { header: 'Date 1',                     key: 'bilag_cyclophosdate1',    isDate: true },
        { header: 'Dose 1',                     key: 'bilag_cyclophosdose1'                  },
        { header: 'Date 2',                     key: 'bilag_cyclophosdate2',    isDate: true },
        { header: 'Dose 2',                     key: 'bilag_cyclophosdose2'                  },
        { header: 'Date 3',                     key: 'bilag_cyclophosdate3',    isDate: true },
        { header: 'Dose 3',                     key: 'bilag_cyclophosdose3'                  },
        { header: 'Notes',                      key: 'bilag_cyclophosnotes'                  }
    ]
};

var IVMP_TABLE = {
    name:          'ivmp',
    fieldName:     'bilag_tblivmepred',   // NB: spelling must match the REDCap field name
    anchorPattern: /IV methyl-prednisolone since last visit/i,
    emptyText:     'No previous IV methyl-prednisolone records',
    columns: [
        { header: 'Visit date',                         key: 'date',                    isDate: true },
        { header: 'Number of pulses since last visit',  key: 'bilag_ivmepredpulses'                  },
        { header: 'Date 1',                             key: 'bilag_ivmepreddate1',     isDate: true },
        { header: 'Dose 1',                             key: 'bilag_ivmepreddose'                  },
        { header: 'Date 2',                             key: 'bilag_ivmepreddate2',     isDate: true },
        { header: 'Dose 2',                             key: 'bilag_ivmepreddose_2'                  },
        { header: 'Date 2',                             key: 'bilag_ivmepreddate3',     isDate: true },
        { header: 'Dose 2',                             key: 'bilag_ivmepreddose_3'                  },
        { header: 'Notes',                              key: 'bilag_ivmeprednotes'                   }
    ]
};

var IVIG_TABLE = {
    name:          'ivig',
    fieldName:     'bilag_ivig_table',   // NB: spelling must match the REDCap field name
    anchorPattern: /IVIG (g\/pulse)/i,
    emptyText:     'No previous IVIG records',
    columns: [
        { header: 'Visit date',                         key: 'date',                    isDate: true },
        { header: 'Number of pulses since last visit',  key: 'bilag_ivigpulses'                  },
        { header: 'Date 1',                             key: 'bilag_ivigdate',     isDate: true },
        { header: 'Dose 1',                             key: 'bilag_ivigcurdose'                  },
        { header: 'Date 2',                             key: 'bilag_ivigdate_2',     isDate: true },
        { header: 'Dose 2',                             key: 'bilag_ivigcurdose_2'                  },
        { header: 'Date 3',                             key: 'bilag_ivigdate_3',     isDate: true },
        { header: 'Dose 3',                             key: 'bilag_ivigcurdose_3'                  },
        { header: 'Commenced/Stopped/Revised',          key: 'bilag_ivigcsr'                  },
        { header: 'Notes',                              key: 'bilag_ivignotes'                   }
    ]
};


// Renal scoring: field names used for the histological nephritis 3-month
// window fallback (criterion A5). Adjust to match your data.
var RENAL_CURRENT_VISIT_DATE_FIELD = 'visitDate';
var RENAL_PREV_VISIT_DATE_FIELD    = 'bilag_visitdate';


class UKJsleBilagFunctions {

    /**
     * Builds an HTML history table.
     *
     * @param {Array}  rows     Array of row objects (keyed by column.key) or arrays (by column index).
     * @param {Array}  columns  Array of { header: string, key: string, isDate?: boolean }.
     * @param {Object} [options]
     * @param {string}   [options.emptyCell='']                    Text for blank cells.
     * @param {string}   [options.emptyText='No previous records'] Message when there are no rows.
     * @param {string}   [options.tableClass='history-table']      CSS class(es) for the <table>.
     * @param {string}   [options.emptyClass='rtx-history-empty']  CSS class(es) for the "no records" cell.
     * @param {Function} [options.formatDate]                      Formatter applied to isDate columns.
     * @returns {string} HTML string.
     */
    static buildHistoryTable(rows, columns, options) {
        options = options || {};

        var COLUMNS    = Array.isArray(columns) ? columns : [];
        var emptyCell  = typeof options.emptyCell === 'string' ? options.emptyCell : '';
        var emptyText  = options.emptyText  || 'No previous records';
        var tableClass = options.tableClass || 'history-table';
        var emptyClass = options.emptyClass || 'rtx-history-empty';
        var formatDate = typeof options.formatDate === 'function' ? options.formatDate : null;

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function readCell(row, column, columnIndex) {
            var value = Array.isArray(row) ? row[columnIndex] : row[column.key];

            if (value === null || value === undefined) {
                return emptyCell;
            }

            if (typeof value === 'object') {
                if (value instanceof Date && !isNaN(value.getTime())) {
                    value = value.toISOString().slice(0, 10);
                } else {
                    return emptyCell;
                }
            }

            value = String(value).trim();

            if (value === '') {
                return emptyCell;
            }

            if (column.isDate && formatDate) {
                try {
                    var formatted = formatDate(value);
                    value = (formatted === null || formatted === undefined) ? value : String(formatted);
                } catch (e) {
                    // keep the raw value rather than losing the row
                }
            }

            return value;
        }

        var safeRows = Array.isArray(rows) ? rows : [];

        var bodyRows = safeRows.filter(function (row) {
            return row !== null && typeof row === 'object';
        }).map(function (row) {
            var cells = COLUMNS.map(function (column, columnIndex) {
                return '<td>' + escapeHtml(readCell(row, column, columnIndex)) + '</td>';
            }).join('');
            return '<tr>' + cells + '</tr>';
        });

        if (bodyRows.length === 0) {
            bodyRows.push(
                '<tr><td colspan="' + COLUMNS.length + '" class="' + escapeHtml(emptyClass) + '">' +
                escapeHtml(emptyText) + '</td></tr>'
            );
        }

        var headerCells = COLUMNS.map(function (column) {
            return '<th scope="col">' + escapeHtml(column.header) + '</th>';
        }).join('');

        return '<table class="' + escapeHtml(tableClass) + '">' +
            '<thead><tr>' + headerCells + '</tr></thead>' +
            '<tbody>' + bodyRows.join('') + '</tbody>' +
            '</table>';
    }


    /**
     * Merges a table config with caller options into options for buildHistoryTable.
     * Every table gets a shared class (styled once) plus a drug-specific class
     * (e.g. 'rtx-history-table') for any custom CSS you already have.
     */
    static historyBuildOptions(config, options) {
        options = options || {};
        var name = config.name || 'history';

        return Object.assign({}, options, {
            emptyText:  options.emptyText || config.emptyText,
            tableClass: 'bilag-history-table ' + (options.tableClass || name + '-history-table'),
            emptyClass: 'bilag-history-empty ' + (options.emptyClass || name + '-history-empty')
        });
    }


    /**
     * Renders a history table into the REDCap form, after the heading paragraph
     * that matches config.anchorPattern. Safe to call repeatedly (re-renders in place).
     *
     * @param {Array}  rows
     * @param {Object} config   One of RTX_TABLE / BELIMUMAB_TABLE / CYCLO_TABLE (or your own).
     * @param {Object} [options]
     * @param {string}  [options.wrapperId]          Defaults to '<name>-history-wrapper'.
     * @param {string}  [options.containerSelector]  Defaults to '#<fieldName>-tr .rich-text-field-label'.
     * @param {Element} [options.container]          Explicit container element.
     * @param {RegExp}  [options.anchorPattern]      Overrides config.anchorPattern.
     * @param {boolean} [options.allowAppend]        Append if no anchor/table found.
     * @param {boolean} [options.injectStyles=true]
     * @param {boolean} [options.debug]
     *   ...plus any buildHistoryTable options.
     * @returns {boolean} true if the table was inserted.
     */
    static insertHistoryTable(rows, config, options) {
        options = options || {};
        config  = config  || {};

        var NAME          = config.name || 'history';
        var FIELD_NAME    = config.fieldName || '';
        var WRAPPER_ID    = options.wrapperId || NAME + '-history-wrapper';
        var STYLE_ID      = 'bilag-history-styles';
        var CONTAINER_SEL = options.containerSelector ||
            (FIELD_NAME ? '#' + FIELD_NAME + '-tr .rich-text-field-label' : '');
        var ANCHOR_RE     = options.anchorPattern || config.anchorPattern || null;

        function fail(reason) {
            if (options.debug && typeof console !== 'undefined' && console.warn) {
                console.warn('insertHistoryTable(' + NAME + '): ' + reason);
            }
            return false;
        }

        try {
            if (typeof document === 'undefined') {
                return fail('no document');
            }

            if (!Array.isArray(config.columns) || config.columns.length === 0) {
                return fail('config has no columns');
            }

            // --- locate the container -------------------------------------
            var container = null;

            if (options.container && options.container.nodeType === 1) {
                container = options.container;
            } else if (CONTAINER_SEL) {
                container = document.querySelector(CONTAINER_SEL);
            }

            if (!container && FIELD_NAME) {
                // Fallback: the label div may not carry the class in every
                // REDCap version, so try the field wrapper itself.
                var fieldRow = document.getElementById(FIELD_NAME + '-tr');
                container = fieldRow
                    ? (fieldRow.querySelector('[data-mlm-field="' + FIELD_NAME + '"]') || fieldRow)
                    : null;
            }

            if (!container) {
                return fail('container not found for selector ' + CONTAINER_SEL);
            }

            // --- find the heading paragraph -------------------------------
            var anchor = null;
            var children = container.children || [];

            if (ANCHOR_RE) {
                for (var i = 0; i < children.length; i++) {
                    if (children[i].tagName === 'P' &&
                        ANCHOR_RE.test(children[i].textContent || '')) {
                        anchor = children[i];
                        break;
                    }
                }
            }

            // --- work out where to insert ---------------------------------
            var insertBeforeNode = null;

            if (anchor) {
                insertBeforeNode = anchor.nextSibling;   // straight after the <p>
            } else {
                // No heading paragraph: fall back to "before the first table".
                for (var j = 0; j < children.length; j++) {
                    if (children[j].tagName === 'TABLE') {
                        insertBeforeNode = children[j];
                        break;
                    }
                }
                if (!insertBeforeNode && !options.allowAppend) {
                    return fail('neither anchor paragraph nor table found');
                }
            }

            // --- build the node -------------------------------------------
            var html = UKJsleBilagFunctions.buildHistoryTable(
                rows,
                config.columns,
                UKJsleBilagFunctions.historyBuildOptions(config, options)
            );

            if (!html) {
                return fail('buildHistoryTable returned nothing');
            }

            var wrapper = document.createElement('div');
            wrapper.id = WRAPPER_ID;
            wrapper.className = 'bilag-history-wrapper ' + NAME + '-history-wrapper';
            wrapper.innerHTML = html;

            // --- remove any previous render (idempotent) ------------------
            var existing = document.getElementById(WRAPPER_ID);
            if (existing && existing.parentNode) {
                existing.parentNode.removeChild(existing);
            }

            // --- insert ----------------------------------------------------
            if (insertBeforeNode) {
                container.insertBefore(wrapper, insertBeforeNode);
            } else {
                container.appendChild(wrapper);
            }

            // --- shared styling, injected once for all tables -------------
            if (options.injectStyles !== false && !document.getElementById(STYLE_ID)) {
                var style = document.createElement('style');
                style.id = STYLE_ID;
                style.type = 'text/css';
                style.appendChild(document.createTextNode(
                    '.bilag-history-wrapper{margin:6px 0 10px 0;}' +
                    '.bilag-history-table{border-collapse:collapse;width:99.9%;font-size:12px;}' +
                    '.bilag-history-table th,.bilag-history-table td{' +
                    'border:1px solid #c0c0c0;padding:3px 5px;text-align:left;' +
                    'vertical-align:top;}' +
                    '.bilag-history-table th{background-color:#dfdfdf;font-weight:bold;}' +
                    '.bilag-history-table tbody tr:nth-child(even) td{background-color:#f7f7f7;}' +
                    '.bilag-history-empty{color:#777;font-style:italic;text-align:center;}'
                ));
                (document.head || document.getElementsByTagName('head')[0]).appendChild(style);
            }

            return true;

        } catch (e) {
            return fail('exception: ' + (e && e.message ? e.message : e));
        }
    }

    static insertRtxTable(rows, options) {
        return UKJsleBilagFunctions.insertHistoryTable(rows, RTX_TABLE, options);
    }

    static insertBelimumabTable(rows, options) {
        return UKJsleBilagFunctions.insertHistoryTable(rows, BELIMUMAB_TABLE, options);
    }

    static insertCycloTable(rows, options) {
        return UKJsleBilagFunctions.insertHistoryTable(rows, CYCLO_TABLE, options);
    }

    static insertIVMPTable(rows, options) {
        return UKJsleBilagFunctions.insertHistoryTable(rows, IVMP_TABLE, options);
    }
    static insertIVIGTable(rows, options) {
        return UKJsleBilagFunctions.insertHistoryTable(rows, IVIG_TABLE, options);
    }


    // =========================================================================
    // BILAG 2004 RENAL SCORING (Categories A, B and C)
    // -------------------------------------------------------------------------
    // Replicates the legacy MS Access / VBA implementation (RenalGradeA/B/C and
    // helpers) so results match historical scores. Where the VBA deviates from
    // the written BILAG definition this is marked "LEGACY:".
    //
    // Input shape:
    //   input.proteinuria, input.q99, input.q102a, input.q102b, input.q102c,
    //   input.q104, input.q105, input.q106b, input.q107, input.q108,
    //   input.systolic, input.diastolic
    //   input.UKJSLE.lastRenal  -> the IMMEDIATELY PRECEDING visit (by date);
    //                              null/undefined/{} when there is no earlier visit.
    // Optional (criterion A5):
    //   input.UKJSLE.nephritisWithin3Months (boolean), or visit dates named by
    //   RENAL_CURRENT_VISIT_DATE_FIELD / RENAL_PREV_VISIT_DATE_FIELD.
    //
    // D and E are determined elsewhere.
    // =========================================================================

    /** Returns 5 if Category A, otherwise 0. */
    static calculateBilagRenalCatA(input) {
        var F   = UKJsleBilagFunctions;
        var ctx = F.renalContext(input);
        var a   = F.renalCategoryACriteria(ctx);

        // Two or more criteria, at least one of which is 1, 4 or 5.
        var primary   = [a.c1, a.c4, a.c5].filter(Boolean).length;
        var secondary = [a.c2, a.c3, a.c6].filter(Boolean).length;

        return (primary > 1 || (primary > 0 && secondary > 0)) ? 5 : 0;
    }

    /** Returns 4 if any Category B condition is met, otherwise 0. */
    static calculateBilagRenalCatB(input) {
        var F   = UKJsleBilagFunctions;
        var ctx = F.renalContext(input);
        var cur = ctx.cur, prev = ctx.prev, hasPrev = ctx.hasPrev;

        // (1) Any single Category A feature
        var a    = F.renalCategoryACriteria(ctx);
        var anyA = a.c1 || a.c2 || a.c3 || a.c4 || a.c5 || a.c6;

        // (2) Proteinuria
        // LEGACY thresholds: 24h > 0.4999, PCR > 49.99, ACR >= 49.99
        var proteinuria =
            F.renalProteinNotImproved(cur.urine24, prev.urine24, hasPrev, 0.4999, 0.25) ||
            F.renalProteinNotImproved(cur.pcr, prev.pcr, hasPrev, 49.99, 0.25) ||
            F.renalAcrNotImproved(cur.acr, prev.acr, hasPrev, 49.99, 0.25) ||
            F.renalDipstickRise(ctx, 1, 2);

        // (3) Creatinine > 130 and risen to >= 115% (> 130% is already in A)
        var creatinine = F.renalCreatinineRise(ctx, 130, 1.15);

        return (anyA || proteinuria || creatinine) ? 4 : 0;
    }

    /**
     * Returns 3 if any Category C condition is met, otherwise 0.
     * Call only after Cat A and Cat B have returned 0.
     */
    static calculateBilagRenalCatC(input) {
        var F   = UKJsleBilagFunctions;
        var ctx = F.renalContext(input);
        var cur = ctx.cur, prev = ctx.prev, hasPrev = ctx.hasPrev;

        // (1) Mild/stable proteinuria
        // LEGACY: 24h and PCR must not have fallen at all since the last visit;
        // ACR uses >= 25 when a previous visit exists, with no stability check.
        var proteinuria =
            F.renalProteinNotImproved(cur.urine24, prev.urine24, hasPrev, 0.25, 0) ||
            F.renalProteinNotImproved(cur.pcr, prev.pcr, hasPrev, 25, 0) ||
            F.renalAcrNotImproved(cur.acr, prev.acr, hasPrev, 25, 0) ||
            (!cur.quantAvailable && cur.dipstick !== null && cur.dipstick > 0);

        // (2) Rising blood pressure
        var bp = F.renalRisingBloodPressure(ctx);

        return (proteinuria || bp) ? 3 : 0;
    }


    // ---- Renal: generic helpers ---------------------------------------------

    static renalIsBlank(v) {
        return v === null || v === undefined ||
            (typeof v === 'string' && v.trim() === '');
    }

    static renalToNum(v) {
        if (UKJsleBilagFunctions.renalIsBlank(v)) return null;
        var n = Number(v);
        return Number.isFinite(n) ? n : null;
    }

    /** Accepts 1 / true / "1" / "Y" / "Yes" (case-insensitive, as Access compares). */
    static renalIsYes(v) {
        return v === 1 || v === true ||
            (typeof v === 'string' && /^(1|y|yes)$/i.test(v.trim()));
    }

    /** Date-only value; avoids UTC shifts when parsing "YYYY-MM-DD". */
    static renalToDateOnly(v) {
        if (v instanceof Date && !isNaN(v.getTime())) {
            return new Date(v.getFullYear(), v.getMonth(), v.getDate());
        }
        if (typeof v === 'string') {
            var m = v.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (m) return new Date(+m[1], +m[2] - 1, +m[3]);
            var d = new Date(v);
            if (!isNaN(d.getTime())) return UKJsleBilagFunctions.renalToDateOnly(d);
        }
        return null;
    }

    /** Equivalent of VBA DateAdd("m", -n, date): clamps to the last day of the month. */
    static renalSubtractMonths(date, months) {
        var y = date.getFullYear();
        var m = date.getMonth() - months;
        var lastDay = new Date(y, m + 1, 0).getDate();
        return new Date(y, m, Math.min(date.getDate(), lastDay));
    }

    /**
     * Use to precompute input.UKJSLE.nephritisWithin3Months from visit history.
     * Mirrors VBA ActiveNephritis: any visit from (visitDate - 3 months) up to and
     * including visitDate with nephritis = Yes.
     *
     * @param {Date|string} visitDate
     * @param {Array} visits  Array of { visitDate: Date|string, nephritis: any }
     * @returns {boolean}
     */
    static nephritisWithinThreeMonths(visitDate, visits) {
        var F   = UKJsleBilagFunctions;
        var end = F.renalToDateOnly(visitDate);
        if (!end) return false;
        var start = F.renalSubtractMonths(end, 3);

        return (Array.isArray(visits) ? visits : []).some(function (v) {
            if (!v) return false;
            var d = F.renalToDateOnly(v.visitDate);
            return d !== null && d >= start && d <= end && F.renalIsYes(v.nephritis);
        });
    }


    // ---- Renal: parse input once --------------------------------------------

    static renalContext(input) {
        var F = UKJsleBilagFunctions;
        input = input || {};

        var uk      = input.UKJSLE || {};
        var prevRaw = uk.lastRenal;
        var hasPrev = !!prevRaw && typeof prevRaw === 'object' &&
            Object.keys(prevRaw).length > 0;
        var p = hasPrev ? prevRaw : {};

        var cur = {
            dipstick:     F.renalToNum(input.proteinuria),
            acr:          F.renalToNum(input.q102a),   // albumin:creatinine (VBA renalUrinaryCr)
            pcr:          F.renalToNum(input.q102b),   // protein:creatinine (VBA renalUrinaryProtein)
            urine24:      F.renalToNum(input.q102c),   // 24h urine protein (g)
            creat:        F.renalToNum(input.q105),
            gfr:          F.renalToNum(input.q106b),
            sys:          F.renalToNum(input.systolic),
            dia:          F.renalToNum(input.diastolic),
            hypertension: F.renalIsYes(input.q99),
            nephrotic:    F.renalIsYes(input.q104),
            sediment:     F.renalIsYes(input.q107),
            nephritis:    F.renalIsYes(input.q108)
        };
        cur.quantAvailable = cur.acr !== null || cur.pcr !== null || cur.urine24 !== null;

        var prev = {
            dipstick: F.renalToNum(p.bilag_proteinuria),
            acr:      F.renalToNum(p.bilag_renalurinaryalbcr),
            pcr:      F.renalToNum(p.bilag_renalurinaryprotcr),
            urine24:  F.renalToNum(p.bilag_24hrurinaryprot),
            creat:    F.renalToNum(p.bilag_renalcreatinine),
            gfr:      F.renalToNum(p.bilag_renalgfrest),
            sys:      F.renalToNum(p.bilag_sysbp),
            dia:      F.renalToNum(p.bilag_diabp)
        };

        return { input: input, uk: uk, hasPrev: hasPrev, prevRaw: p, cur: cur, prev: prev };
    }


    // ---- Renal: criterion checks (each mirrors one VBA helper) ---------------

    /**
     * VBA PrevUrinaryProtein / UrinaryProteinCrRatio (identical logic).
     * current > threshold AND fractional decrease from previous <= maxDecrease.
     *   No previous visit           -> true
     *   Previous visit, value blank -> previous treated as 0 -> true
     * LEGACY: with maxDecrease = 0 (Cat C) this requires current >= previous.
     */
    static renalProteinNotImproved(curVal, prevVal, hasPrev, threshold, maxDecrease) {
        if (curVal === null || !(curVal > threshold)) return false;
        if (!hasPrev) return true;
        var p = prevVal === null ? 0 : prevVal;
        var decrease = p > 0 ? (p - curVal) / p : 0;
        return decrease <= maxDecrease;
    }

    /**
     * VBA UrinaryAlbCrRatio.
     * LEGACY: ">= threshold" when a previous visit exists, "> threshold" on a first
     * visit; a decrease of exactly maxDecrease does NOT count; with
     * maxDecrease = 0 (Cat C) there is no stability check.
     */
    static renalAcrNotImproved(curVal, prevVal, hasPrev, threshold, maxDecrease) {
        if (curVal === null) return false;
        if (!hasPrev) return curVal > threshold;
        if (!(curVal >= threshold)) return false;
        if (!(maxDecrease > 0)) return true;
        if (prevVal === null) return true;
        if (curVal > prevVal) return true;
        return (prevVal - curVal) / prevVal < maxDecrease;
    }

    /**
     * VBA PrevDipstick(LevelIncrease, CurrentLevel).
     * Only used when no quantitative urine protein value exists for this visit.
     * LEGACY: on a first visit, true if dipstick >= LevelIncrease (A) or
     * >= CurrentLevel (B).
     */
    static renalDipstickRise(ctx, levelIncrease, currentLevel) {
        var cur = ctx.cur, prev = ctx.prev;
        if (cur.quantAvailable || cur.dipstick === null) return false;

        if (!ctx.hasPrev) {
            return currentLevel === 0
                ? cur.dipstick >= levelIncrease
                : cur.dipstick >= currentLevel;
        }
        if (currentLevel !== 0 && !(cur.dipstick >= currentLevel)) return false;
        return prev.dipstick !== null && cur.dipstick >= prev.dipstick + levelIncrease;
    }

    /**
     * VBA PlasmaCreatinine(MaxLevel, LevelIncrease).
     *   No previous visit           -> true (if current > maxLevel)
     *   Previous visit, value blank -> false
     */
    static renalCreatinineRise(ctx, maxLevel, ratio) {
        var cur = ctx.cur, prev = ctx.prev;
        if (cur.creat === null || !(cur.creat > maxLevel)) return false;
        if (!ctx.hasPrev) return true;
        if (prev.creat === null || prev.creat === 0) return false;
        return (1 + (cur.creat - prev.creat) / prev.creat) >= ratio;
    }

    /**
     * VBA GFR(..., CalcType = 1): GFR < maxLevel and < factor x previous.
     * LEGACY: first visit -> true if GFR < maxLevel.
     */
    static renalGfrFall(ctx, maxLevel, factor) {
        var cur = ctx.cur, prev = ctx.prev;
        if (cur.gfr === null || !(cur.gfr < maxLevel)) return false;
        if (!ctx.hasPrev) return true;
        return prev.gfr !== null && cur.gfr < prev.gfr * factor;
    }

    /** VBA GFR(..., CalcType = 2): GFR < maxLevel and previous > prevAbove or blank. */
    static renalGfrCrossed(ctx, maxLevel, prevAbove) {
        var cur = ctx.cur, prev = ctx.prev;
        if (cur.gfr === null || !(cur.gfr < maxLevel)) return false;
        if (!ctx.hasPrev) return true;
        return prev.gfr === null || prev.gfr > prevAbove;
    }

    /**
     * VBA ActiveNephritis: any visit in the 3 months up to and including this one
     * with nephritis = Yes. Evaluated as:
     *   1. current visit Yes -> true
     *   2. input.UKJSLE.nephritisWithin3Months (boolean) if supplied
     *   3. fallback: previous visit Yes (within 3 months if both dates are known)
     */
    static renalActiveNephritis(ctx) {
        var F = UKJsleBilagFunctions;
        if (ctx.cur.nephritis) return true;
        if (typeof ctx.uk.nephritisWithin3Months === 'boolean') {
            return ctx.uk.nephritisWithin3Months;
        }
        if (!ctx.hasPrev || !F.renalIsYes(ctx.prevRaw.bilag_renalnephritis)) return false;

        var curDate  = F.renalToDateOnly(ctx.input[RENAL_CURRENT_VISIT_DATE_FIELD]);
        var prevDate = F.renalToDateOnly(ctx.prevRaw[RENAL_PREV_VISIT_DATE_FIELD]);
        if (curDate && prevDate) {
            return prevDate >= F.renalSubtractMonths(curDate, 3) && prevDate <= curDate;
        }
        return true;
    }

    /**
     * VBA RisingBloodPressure(140, 90, 30, 15).
     * Requires systolic > 140 AND diastolic > 90, AND both rises.
     * No previous visit -> false.
     */
    static renalRisingBloodPressure(ctx) {
        var cur = ctx.cur, prev = ctx.prev;
        return cur.sys !== null && cur.sys > 140 &&
            cur.dia !== null && cur.dia > 90 &&
            ctx.hasPrev && prev.sys !== null && prev.dia !== null &&
            (cur.sys - prev.sys) >= 30 &&
            (cur.dia - prev.dia) >= 15;
    }

    /** The six Category A criteria (shared by Cat A and Cat B). */
    static renalCategoryACriteria(ctx) {
        var F = UKJsleBilagFunctions;
        var cur = ctx.cur, prev = ctx.prev, hasPrev = ctx.hasPrev;

        return {
            // (1) Deteriorating proteinuria (severe)
            c1: F.renalProteinNotImproved(cur.urine24, prev.urine24, hasPrev, 1, 0.25) ||
                F.renalProteinNotImproved(cur.pcr, prev.pcr, hasPrev, 100, 0.25) ||
                F.renalAcrNotImproved(cur.acr, prev.acr, hasPrev, 100, 0.25) ||
                F.renalDipstickRise(ctx, 2, 0),
            // (2) Accelerated hypertension
            c2: cur.hypertension,
            // (3) Deteriorating renal function (severe)
            c3: F.renalCreatinineRise(ctx, 130, 1.3) ||
                F.renalGfrFall(ctx, 80, 0.67) ||
                F.renalGfrCrossed(ctx, 50, 50),
            // (4) Active urinary sediment
            c4: cur.sediment,
            // (5) Histological evidence of active nephritis within 3 months
            c5: F.renalActiveNephritis(ctx),
            // (6) Nephrotic syndrome
            c6: cur.nephrotic
        };
    }

}