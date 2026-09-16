/**
 * JEST Tests for the UK JSLE BILAG Library functions.
 *
 * @author Duncan Appelbe
 * @version 1.0
 *
 */

const UKJsleBilagFunctions = require('../scripts/ukJSLEBilagFunctions_library_v0.1_27May2025.js');

describe('Tests for the class UKJsleBilagFunctions\n', () => {
    describe('Tests for the function calcConsStore\n', () => {
        test('When the function is called with the default arguments, the value "0" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore()).toBe(0);
        });
        test('When the function is called with an input of 0 and a maximum score of 4, the value "0" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(0,4)).toBe(0);
        });
        test('When the function is called with an input of 1 and a maximum score of 4, the value "1" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(1,4)).toBe(1);
        });
        test('When the function is called with an input of 2 and a maximum score of 4, the value "4" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(2,4)).toBe(4);
        });
        test('When the function is called with an input of 3 and a maximum score of 4, the value "4" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(3,4)).toBe(4);
        });
        test('When the function is called with an input of 4 and a maximum score of 4, the value "4" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(4,4)).toBe(4);
        });
        test('When the function is called with an input of 0 and a maximum score of 9, the value "0" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(0,9)).toBe(0);
        });
        test('When the function is called with an input of 1 and a maximum score of 9, the value "1" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(1,9)).toBe(1);
        });
        test('When the function is called with an input of 2 and a maximum score of 9, the value "9" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(2,9)).toBe(9);
        });
        test('When the function is called with an input of 3 and a maximum score of 9, the value "9" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(3,9)).toBe(9);
        });
        test('When the function is called with an input of 4 and a maximum score of 9, the value "9" is returned.', () => {
            expect(UKJsleBilagFunctions.calcConsStore(4,9)).toBe(9);
        });

    });
    describe('Tests for the function calcConstitutional\n', () => {
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is not present (0); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been no previous involvement, we should return "E".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(0, 0, 0, 0, 0)).toBe('E');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is not present (0); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "D".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 0, 0, 0, 0)).toBe('D');
        });
        test('When: '
            + ' > Pyrexia is not improving (1);\n'
            + ' > Weight loss is not present (0); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been no previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(0, 1, 0, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is improving (1);\n'
            + ' > Weight loss is not present (0); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 1, 0, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as improving (1); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has not been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(0, 0, 1, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as improving (1); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 0, 1, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as same (2); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has not been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(0, 0, 2, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as same (2); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 0, 2, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as worse (3); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has not been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(0, 0, 3, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as worse (3); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 0, 3, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as new (4); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has not been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(0, 0, 4, 0, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is not present (0);\n'
            + ' > Weight loss is recorded as new (4); '
            + ' > Lymphadenopathy/splenomegaly is not present (0);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 0, 4, 0, 0)).toBe('C');
        });
        //-- We do not need to test scoring 0-4 individually for Lymphadenopathy/splenomegaly and Anaorexia due to the nature of the code
        //-- We will test one of them with a pyrexia score of 1 (improving)
        //-- We will also not test specifically the effect of the previous involvement, as this is only taken into account if the sum of scores
        //-- of the other factors is 0, this has been tested above.
        test('When: '
            + ' > Pyrexia is improving (1);\n'
            + ' > Weight loss is recorded as not present (0); '
            + ' > Lymphadenopathy/splenomegaly is improving (1);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 1, 0, 1, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is improving (1);\n'
            + ' > Weight loss is recorded as not present (0); '
            + ' > Lymphadenopathy/splenomegaly is same (2);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 1, 0, 2, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is improving (1);\n'
            + ' > Weight loss is recorded as not present (0); '
            + ' > Lymphadenopathy/splenomegaly is worse (3);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 1, 0, 3, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is improving (1);\n'
            + ' > Weight loss is recorded as not present (0); '
            + ' > Lymphadenopathy/splenomegaly is new (4);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 1, 0, 4, 0)).toBe('C');
        });
        test('When: '
            + ' > Pyrexia is improving (1);\n'
            + ' > Weight loss is recorded as improving (1); '
            + ' > Lymphadenopathy/splenomegaly is new (4);\n'
            + ' > Anorexia is not present (0);\n'
            + ' and there has been previous involvement, we should return "C".', () => {
            expect(UKJsleBilagFunctions.calcConstitutional(1, 1, 0, 4, 0)).toBe('C');
        });
    });
    describe('calcConstitutional', () => {
        describe('Category A', () => {
            test('should return A for pyrexia=3 and weightLoss=2, lymphadenopathy=2', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 3, 2, 2, 0);
                expect(result).toBe('A');
            });

            test('should return A for pyrexia=4 and weightLoss=3, anorexia=2', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 4, 3, 0, 2);
                expect(result).toBe('A');
            });
        });

        describe('Category B', () => {
            test('should return B for pyrexia=3 and only one other criterion (weightLoss=1)', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 3, 1, 0, 0);
                expect(result).toBe('B');
            });

            test('should return B for no pyrexia but lymphadenopathy=3 and anorexia=3', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 0, 0, 3, 3);
                expect(result).toBe('B');
            });

            test('should return B for pyrexia=2 and weightLoss=0, anorexia=3 (only 1 extra item)', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 2, 0, 0, 3);
                expect(result).toBe('B');
            });
        });

        describe('Category C', () => {
            test('should return C for pyrexia=1 (improving)', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 1, 0, 0, 0);
                expect(result).toBe('C');
            });

            test('should return C for pyrexia=0 but weightLoss=1 (does not meet A or B)', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 0, 1, 0, 0);
                expect(result).toBe('C');
            });

            test('should return C for anorexia=1 only (mild)', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 0, 0, 0, 1);
                expect(result).toBe('C');
            });
        });

        describe('Category D or E (no symptoms)', () => {
            test('should return D or E for all 0s — assuming prevScore determines this', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(1, 0, 0, 0, 0); // Previously active
                expect(['D', 'E']).toContain(result);
            });

            test('should return E for all 0s and prevScore=0', () => {
                const result = UKJsleBilagFunctions.calcConstitutional(0, 0, 0, 0, 0); // Never active
                expect(result).toBe('E');
            });
        });
    });
    describe('calcMucocutaneous — it.each table-driven tests', () => {
        const F = UKJsleBilagFunctions;

        // parameter order for calcMucocutaneous:
        // (prevScore, q6, q7, q10, q11, q12, q13, q14a, q14b, q15, q16, q20, q25, q89, q95)
        const PARAM_COUNT = 15;

        const idx = {
            prevScore: 0,
            q6: 1,
            q7: 2,
            q10: 3,
            q11: 4,
            q12: 5,
            q13: 6,
            q14a: 7,
            q14b: 8,
            q15: 9,
            q16: 10,
            q20: 11,
            q25: 12,
            q89: 13,
            q95: 14
        };

        // category membership according to your function:
        const A_questions = ['q6','q14a','q15','q12','q89'];      // >1 => A; ===1 => B
        const B_questions = ['q7','q13','q95','q10'];            // >1 => B; ===1 => C
        const C_positive = ['q14b','q16','q11','q20','q25'];     // >0 => C (checked after B checks)

        function makeArgs(overrides = {}) {
            const arr = new Array(PARAM_COUNT).fill(0);
            Object.entries(overrides).forEach(([k, v]) => {
                arr[idx[k]] = v;
            });
            return arr;
        }

        // ---- A questions: values 2/3/4 -> "A" ----
        const aValueCases = [];
        A_questions.forEach(q => [2,3,4].forEach(v => aValueCases.push([q, v, "A"])));

        it.each(aValueCases)(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );

        // ---- A questions: value 1 -> "B" ----
        it.each(A_questions.map(q => [q, 1, "B"]))(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );

        // ---- B questions: values 2/3/4 -> "B" ----
        const bValueCases = [];
        B_questions.forEach(q => [2,3,4].forEach(v => bValueCases.push([q, v, "B"])));

        it.each(bValueCases)(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );

        // ---- B questions: value 1 -> "C" ----
        it.each(B_questions.map(q => [q, 1, "C"]))(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );

        // ---- C positive checks (>0 => C) ----
        it.each(C_positive.map(q => [q, 1, "C"]))(
            '%s > 0 should return C when earlier A/B conditions do not trigger',
            (question, value, expected) => {
                // ensure no A/B triggers: set only this C-field
                const args = makeArgs({ [question]: value });
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );

        // Also test C-positive with larger positive numbers
        it.each(C_positive.map(q => [q, 2, "C"]))(
            '%s > 0 (value 2) should return C when earlier A/B conditions do not trigger',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );

        // ---- Precedence & order-dependent tests ----
        const interactionCases = [
            // A takes precedence over B (A checked first)
            [{ q6: 2, q7: 3 }, "A"],
            // If an A === 1 occurs early, the function returns B immediately (so later A>1 won't be seen)
            [{ q6: 1, q14a: 4 }, "B"],
            // If first A is zero but a later A>1 exists -> A
            [{ q14a: 3 }, "A"],
            // A=1 & B=1 -> B (first A/B check returns B)
            [{ q6: 1, q7: 1 }, "B"],
            // If no A triggers and B>1 somewhere -> B
            [{ q13: 2 }, "B"],
            // If no A, B=1 somewhere -> C
            [{ q13: 1 }, "C"],
            // If no A/B triggers and C_positive present -> C
            [{ q14b: 1 }, "C"],
            // If multiple C_positive flags set -> C
            [{ q14b: 1, q11: 1 }, "C"],
            // If no A/B/C and prevScore>0 -> D
            [{ prevScore: 1 }, "D"],
            // If no A/B/C and prevScore=0 -> E
            [{ prevScore: 0 }, "E"]
        ];

        it.each(interactionCases)(
            'With inputs %j expected %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );

        // ---- Sanity / combination cases ----
        const sanityCases = [
            [{}, "E"], // all zeros -> E
            [{ prevScore: 1 }, "D"],
            // all A=1 -> first A encountered returns B (q6 is checked first)
            [A_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "B"],
            // all B=1 and A=0 -> C (first B encountered)
            [B_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "C"],
            // A and B both >1 -> A (A precedence)
            [{ q6: 4, q7: 4 }, "A"],
            // A zero, B zero, but C positives -> C
            [{ q14b: 2, q20: 1 }, "C"]
        ];

        it.each(sanityCases)(
            'Sanity: inputs %j -> %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcMucocutaneous(...args)).toBe(expected);
            }
        );
    });
    describe('calcNeuro — it.each table-driven tests', () => {
        const F = UKJsleBilagFunctions;

        // parameter order for calcNeuro:
        // (prevScore, q27, q28, q29, q30, q31, q32, q33, q34,
        //  q35, q37, q38, q39, q41, q42, q43, q44, q47, q48, q49, q53)
        const PARAM_COUNT = 21;

        const idx = {
            prevScore: 0,
            q27: 1,
            q28: 2,
            q29: 3,
            q30: 4,
            q31: 5,
            q32: 6,
            q33: 7,
            q34: 8,
            q35: 9,
            q37: 10,
            q38: 11,
            q39: 12,
            q41: 13,
            q42: 14,
            q43: 15,
            q44: 16,
            q47: 17,
            q48: 18,
            q49: 19,
            q53: 20
        };

        // category membership according to latest function:
        const A_questions = [
            'q34','q33','q37','q38','q28','q29','q39','q35','q41','q42','q43','q31','q47'
        ];
        const B_questions = [
            'q30','q32','q27','q48','q44','q49','q53'
        ];

        function makeArgs(overrides = {}) {
            const arr = new Array(PARAM_COUNT).fill(0);
            Object.entries(overrides).forEach(([k, v]) => {
                arr[idx[k]] = v;
            });
            return arr;
        }

        // ---- A questions: values 2/3/4 -> "A" ----
        const aValueCases = [];
        A_questions.forEach(q => [2,3,4].forEach(v => aValueCases.push([q, v, "A"])));

        it.each(aValueCases)(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcNeuro(...args)).toBe(expected);
            }
        );

        // ---- A questions: value 1 -> "B" ----
        it.each(A_questions.map(q => [q, 1, "B"]))(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcNeuro(...args)).toBe(expected);
            }
        );

        // ---- B questions: values 2/3/4 -> "B" ----
        const bValueCases = [];
        B_questions.forEach(q => [2,3,4].forEach(v => bValueCases.push([q, v, "B"])));

        it.each(bValueCases)(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcNeuro(...args)).toBe(expected);
            }
        );

        // ---- B questions: value 1 -> "C" ----
        it.each(B_questions.map(q => [q, 1, "C"]))(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcNeuro(...args)).toBe(expected);
            }
        );

        // ---- Precedence & order-dependent tests (updated for new check order) ----
        const interactionCases = [
            // A >1 and B >1 -> A (A is checked first)
            [{ q34: 2, q30: 3 }, "A"],
            // A=1 and B=1 -> B (first A/B check returns B)
            [{ q34: 1, q30: 1 }, "B"],
            // No A, B>1 -> B
            [{ q30: 2 }, "B"],
            // No A, B=1 -> C
            [{ q30: 1 }, "C"],
            // No A/B, prevScore>0 -> D
            [{ prevScore: 1 }, "D"],
            // No A/B, prevScore=0 -> E
            [{ prevScore: 0 }, "E"],
            // Multiple A-values -> A
            [{ q34: 2, q33: 3, q47: 4 }, "A"],
            // Multiple B-values -> B
            [{ q30: 2, q32: 4, q49: 3 }, "B"],
            // Order-dependent: q34 checked before q33, so q34>1 wins even if q33==1
            [{ q33: 1, q34: 4 }, "A"],
            // If an earlier A is 1 and no A>1 present, the function will return B
            [{ q34: 1, q33: 0, q47: 0 }, "B"],
            // A later A triggers A when earlier A checks are zero
            [{ q47: 2 }, "A"]
        ];

        it.each(interactionCases)(
            'With inputs %j expected %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcNeuro(...args)).toBe(expected);
            }
        );

        // ---- Sanity / combination cases ----
        const sanityCases = [
            [{}, "E"], // all zeros -> E
            [{ prevScore: 1 }, "D"],
            // all A=1 -> first A encountered returns B (q34 is first)
            [A_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "B"],
            // all B=1 and A=0 -> C (first B encountered)
            [B_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "C"],
            // all A=4 and one B=4 -> A (A precedence)
            [A_questions.reduce((acc, q) => (acc[q] = 4, acc), { q30: 4 }), "A"]
        ];

        it.each(sanityCases)(
            'Sanity: inputs %j -> %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcNeuro(...args)).toBe(expected);
            }
        );
    });
    describe('calcMusculoskeletal — it.each table-driven tests', () => {
        const F = UKJsleBilagFunctions;

        // parameter order for calcMusculoskeletal:
        // (prevScore, q58, q60, q61, q62, q63, q64, q65)
        const PARAM_COUNT = 8;

        const idx = {
            prevScore: 0,
            q58: 1,
            q60: 2,
            q61: 3,
            q62: 4,
            q63: 5,
            q64: 6,
            q65: 7
        };

        // Groups according to the function:
        // A-group: q58, q62  ( >1 => "A", ===1 => "B" )
        const A_questions = ['q58', 'q62'];
        // B-group: q60, q63, q65 ( >1 => "B", ===1 => "C" )
        const B_questions = ['q60', 'q63', 'q65'];
        // C-positive: q64, q61 ( >0 => "C" ), checked after B-group

        function makeArgs(overrides = {}) {
            const arr = new Array(PARAM_COUNT).fill(0);
            Object.entries(overrides).forEach(([k, v]) => {
                arr[idx[k]] = v;
            });
            return arr;
        }

        // ---- A questions: values 2/3/4 -> "A" ----
        const aValueCases = [];
        A_questions.forEach(q => [2,3,4].forEach(v => aValueCases.push([q, v, "A"])));

        it.each(aValueCases)(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );

        // ---- A questions: value 1 -> "B" ----
        it.each(A_questions.map(q => [q, 1, "B"]))(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );

        // ---- B questions: values 2/3/4 -> "B" ----
        const bValueCases = [];
        B_questions.forEach(q => [2,3,4].forEach(v => bValueCases.push([q, v, "B"])));

        it.each(bValueCases)(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );

        // ---- B questions: value 1 -> "C" ----
        it.each(B_questions.map(q => [q, 1, "C"]))(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );

        // ---- C-positive checks (>0 => C) ----
        const C_positive = ['q64', 'q61'];
        it.each(C_positive.map(q => [q, 1, "C"]))(
            '%s > 0 should return C when earlier A/B conditions do not trigger',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );

        it.each(C_positive.map(q => [q, 2, "C"]))(
            '%s = %i should return C when earlier A/B conditions do not trigger',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );

        // ---- Precedence & ordering tests ----
        const interactionCases = [
            // A takes precedence over B when both >1 (A-group checked first)
            [{ q58: 2, q60: 3 }, "A"],
            // If earliest A === 1, function returns B immediately — later A>1 won't be seen
            [{ q58: 1, q62: 4 }, "B"],
            // Later A>1 wins if earlier A checks were zero
            [{ q62: 3 }, "A"],
            // A=1 and B=1 -> B (first A check returns B)
            [{ q58: 1, q60: 1 }, "B"],
            // No A triggers and B>1 => B
            [{ q63: 2 }, "B"],
            // No A triggers and B=1 => C
            [{ q63: 1 }, "C"],
            // If no A/B triggers and q64/q61 positive => C
            [{ q64: 1 }, "C"],
            [{ q61: 1 }, "C"],
            // Multiple positives in C region -> C
            [{ q64: 2, q61: 1 }, "C"],
            // If none of the above and prevScore>0 -> D
            [{ prevScore: 1 }, "D"],
            // If none of the above and prevScore=0 -> E
            [{ prevScore: 0 }, "E"]
        ];

        it.each(interactionCases)(
            'With inputs %j expected %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );

        // ---- Sanity combination cases ----
        const sanityCases = [
            [{}, "E"], // all zeros -> E
            [{ prevScore: 1 }, "D"],
            // All A=1 => first A encountered returns B (q58 checked first)
            [A_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "B"],
            // All B=1 and A=0 => C (first B encountered)
            [B_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "C"],
            // A and B both >1 -> A (A precedence)
            [{ q58: 4, q60: 4 }, "A"],
            // A zero, B zero, but C positives -> C
            [{ q64: 2, q61: 1 }, "C"]
        ];

        it.each(sanityCases)(
            'Sanity: inputs %j -> %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcMusculoskeletal(...args)).toBe(expected);
            }
        );
    });
    describe('calcCardioRespiritory — it.each table-driven tests', () => {
        const F = UKJsleBilagFunctions;

        // parameter order for calcCardioRespiritory:
        // (prevScore, q68, q70, q71, q76, q77, q80, q81, q82, q83, q84, q85, q86, q87, q88)
        const PARAM_COUNT = 15;

        const idx = {
            prevScore: 0,
            q68: 1,
            q70: 2,
            q71: 3,
            q76: 4,
            q77: 5,
            q80: 6,
            q81: 7,
            q82: 8,
            q83: 9,
            q84: 10,
            q85: 11,
            q86: 12,
            q87: 13,
            q88: 14
        };

        // According to the function:
        // A-group (checked first): q76, q70, q77, q81, q82, q83, q84, q85, q86, q87, q88
        //  - >1 => "A", ===1 => "B"
        const A_questions = ['q76','q70','q77','q81','q82','q83','q84','q85','q86','q87','q88'];

        // B-group (checked after A-group): q68, q71, q80
        //  - >1 => "B", ===1 => "C"
        const B_questions = ['q68','q71','q80'];

        function makeArgs(overrides = {}) {
            const arr = new Array(PARAM_COUNT).fill(0);
            Object.entries(overrides).forEach(([k, v]) => {
                arr[idx[k]] = v;
            });
            return arr;
        }

        // ---- A questions: values 2/3/4 -> "A" ----
        const aValueCases = [];
        A_questions.forEach(q => [2,3,4].forEach(v => aValueCases.push([q, v, "A"])));

        it.each(aValueCases)(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcCardioRespiritory(...args)).toBe(expected);
            }
        );

        // ---- A questions: value 1 -> "B" ----
        it.each(A_questions.map(q => [q, 1, "B"]))(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcCardioRespiritory(...args)).toBe(expected);
            }
        );

        // ---- B questions: values 2/3/4 -> "B" ----
        const bValueCases = [];
        B_questions.forEach(q => [2,3,4].forEach(v => bValueCases.push([q, v, "B"])));

        it.each(bValueCases)(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcCardioRespiritory(...args)).toBe(expected);
            }
        );

        // ---- B questions: value 1 -> "C" ----
        it.each(B_questions.map(q => [q, 1, "C"]))(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcCardioRespiritory(...args)).toBe(expected);
            }
        );

        // ---- Precedence & order-dependent tests ----
        const interactionCases = [
            // A takes precedence over B when both >1 (A-group checked first)
            [{ q76: 2, q68: 3 }, "A"],
            // If earliest A === 1, function returns B immediately — later A>1 won't be seen
            [{ q76: 1, q70: 4 }, "B"],
            // Later A>1 wins if earlier A checks were zero
            [{ q88: 3 }, "A"],
            // A=1 and B=1 -> B (first A check will return B)
            [{ q76: 1, q68: 1 }, "B"],
            // No A triggers and B>1 => B
            [{ q71: 2 }, "B"],
            // No A triggers and B=1 => C
            [{ q80: 1 }, "C"],
            // If none of above and prevScore>0 -> D
            [{ prevScore: 1 }, "D"],
            // If none of above and prevScore=0 -> E
            [{ prevScore: 0 }, "E"]
        ];

        it.each(interactionCases)(
            'With inputs %j expected %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcCardioRespiritory(...args)).toBe(expected);
            }
        );

        // ---- Sanity / combination cases ----
        const sanityCases = [
            [{}, "E"], // all zeros -> E
            [{ prevScore: 1 }, "D"],
            // all A=1 -> first A encountered returns B (q76 is first)
            [A_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "B"],
            // all B=1 and A=0 -> C (first B encountered)
            [B_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "C"],
            // A and B both >1 -> A (A precedence)
            [{ q76: 4, q68: 4 }, "A"],
            // A zero, B zero, prevScore zero -> E
            [{ q76: 0, q68: 0, prevScore: 0 }, "E"]
        ];

        it.each(sanityCases)(
            'Sanity: inputs %j -> %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcCardioRespiritory(...args)).toBe(expected);
            }
        );
    });
    describe('calcGastro — it.each table-driven tests', () => {
        const F = UKJsleBilagFunctions;

        // parameter order for calcGastro:
        // (prevScore, q109, q110, q111, q112, q113, q114, q115, q116, q117)
        const PARAM_COUNT = 10;

        const idx = {
            prevScore: 0,
            q109: 1,
            q110: 2,
            q111: 3,
            q112: 4,
            q113: 5,
            q114: 6,
            q115: 7,
            q116: 8,
            q117: 9
        };

        // Groups according to the function:
        // A-group (checked first): q109, q111, q114, q116, q117
        //   >1 => "A"; ===1 => "B"
        const A_questions = ['q109','q111','q114','q116','q117'];
        // B-group (checked after A-group): q110, q112, q113, q115
        //   >1 => "B"; ===1 => "C"
        const B_questions = ['q110','q112','q113','q115'];

        function makeArgs(overrides = {}) {
            const arr = new Array(PARAM_COUNT).fill(0);
            Object.entries(overrides).forEach(([k, v]) => {
                arr[idx[k]] = v;
            });
            return arr;
        }

        // ---- A questions: values 2/3/4 -> "A" ----
        const aValueCases = [];
        A_questions.forEach(q => [2,3,4].forEach(v => aValueCases.push([q, v, "A"])));

        it.each(aValueCases)(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcGastro(...args)).toBe(expected);
            }
        );

        // ---- A questions: value 1 -> "B" ----
        it.each(A_questions.map(q => [q, 1, "B"]))(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcGastro(...args)).toBe(expected);
            }
        );

        // ---- B questions: values 2/3/4 -> "B" ----
        const bValueCases = [];
        B_questions.forEach(q => [2,3,4].forEach(v => bValueCases.push([q, v, "B"])));

        it.each(bValueCases)(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcGastro(...args)).toBe(expected);
            }
        );

        // ---- B questions: value 1 -> "C" ----
        it.each(B_questions.map(q => [q, 1, "C"]))(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcGastro(...args)).toBe(expected);
            }
        );

        // ---- Precedence & order-dependent tests ----
        const interactionCases = [
            // A takes precedence over B when both >1 (A-group checked first)
            [{ q109: 2, q110: 3 }, "A"],
            // If earliest A === 1, function returns B immediately — later A>1 won't be seen
            [{ q109: 1, q111: 4 }, "B"],
            // Later A>1 wins if earlier A checks were zero
            [{ q117: 3 }, "A"],
            // A=1 and B=1 -> B (first A check returns B)
            [{ q109: 1, q110: 1 }, "B"],
            // No A triggers and B>1 => B
            [{ q112: 2 }, "B"],
            // No A triggers and B=1 => C
            [{ q115: 1 }, "C"],
            // If none of the above and prevScore>0 -> D
            [{ prevScore: 1 }, "D"],
            // If none of the above and prevScore=0 -> E
            [{ prevScore: 0 }, "E"]
        ];

        it.each(interactionCases)(
            'With inputs %j expected %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcGastro(...args)).toBe(expected);
            }
        );

        // ---- Sanity / combination cases ----
        const sanityCases = [
            [{}, "E"], // all zeros -> E
            [{ prevScore: 1 }, "D"],
            // all A=1 -> first A encountered returns B (q109 is first)
            [A_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "B"],
            // all B=1 and A=0 -> C (first B encountered)
            [B_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "C"],
            // A and B both >1 -> A (A precedence)
            [{ q109: 4, q110: 4 }, "A"],
            // A zero, B zero, prevScore zero -> E
            [{ q109: 0, q110: 0, prevScore: 0 }, "E"]
        ];

        it.each(sanityCases)(
            'Sanity: inputs %j -> %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcGastro(...args)).toBe(expected);
            }
        );
    });
    describe('calcOpthalmic — it.each table-driven tests', () => {
        const F = UKJsleBilagFunctions;

        // parameter order for calcOpthalmic:
        // (prevScore, q118, q119, q120, q121, q122, q123, q124, q125, q126, q127, q128, q129, q130)
        const PARAM_COUNT = 14;

        const idx = {
            prevScore: 0,
            q118: 1,
            q119: 2,
            q120: 3,
            q121: 4,
            q122: 5,
            q123: 6,
            q124: 7,
            q125: 8,
            q126: 9,
            q127: 10,
            q128: 11,
            q129: 12,
            q130: 13
        };

        // Groups according to the function:
        // A-group (checked first): q118, q119, q122, q125, q127, q129, q130
        //   >1 => "A"; ===1 => "B"
        const A_questions = ['q118','q119','q122','q125','q127','q129','q130'];

        // B-group (checked after A-group): q120, q121, q123, q126
        //   >1 => "B"; ===1 => "C"
        const B_questions = ['q120','q121','q123','q126'];

        // C-positive: q124, q128 ( >0 => "C" ), checked after B-group
        const C_positive = ['q124','q128'];

        function makeArgs(overrides = {}) {
            const arr = new Array(PARAM_COUNT).fill(0);
            Object.entries(overrides).forEach(([k, v]) => {
                arr[idx[k]] = v;
            });
            return arr;
        }

        // ---- A questions: values 2/3/4 -> "A" ----
        const aValueCases = [];
        A_questions.forEach(q => [2,3,4].forEach(v => aValueCases.push([q, v, "A"])));

        it.each(aValueCases)(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );

        // ---- A questions: value 1 -> "B" ----
        it.each(A_questions.map(q => [q, 1, "B"]))(
            'A-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );

        // ---- B questions: values 2/3/4 -> "B" ----
        const bValueCases = [];
        B_questions.forEach(q => [2,3,4].forEach(v => bValueCases.push([q, v, "B"])));

        it.each(bValueCases)(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );

        // ---- B questions: value 1 -> "C" ----
        it.each(B_questions.map(q => [q, 1, "C"]))(
            'B-question %s = %i should return %s',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );

        // ---- C-positive checks (>0 => C) ----
        it.each(C_positive.map(q => [q, 1, "C"]))(
            '%s > 0 should return C when earlier A/B conditions do not trigger',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );

        it.each(C_positive.map(q => [q, 2, "C"]))(
            '%s = %i should return C when earlier A/B conditions do not trigger',
            (question, value, expected) => {
                const args = makeArgs({ [question]: value });
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );

        // ---- Precedence & order-dependent tests ----
        const interactionCases = [
            // A takes precedence over B when both >1 (A-group checked first)
            [{ q118: 2, q120: 3 }, "A"],
            // If earliest A === 1, function returns B immediately — later A>1 won't be seen
            [{ q118: 1, q119: 4 }, "B"],
            // Later A>1 wins if earlier A checks were zero
            [{ q130: 3 }, "A"],
            // A=1 and B=1 -> B (first A check returns B)
            [{ q118: 1, q120: 1 }, "B"],
            // No A triggers and B>1 => B
            [{ q121: 2 }, "B"],
            // No A triggers and B=1 => C
            [{ q123: 1 }, "C"],
            // If no A/B triggers and q124 or q128 positive -> C
            [{ q124: 1 }, "C"],
            [{ q128: 2 }, "C"],
            // Multiple C positives -> C
            [{ q124: 1, q128: 1 }, "C"],
            // If none of the above and prevScore>0 -> D
            [{ prevScore: 1 }, "D"],
            // If none of the above and prevScore=0 -> E
            [{ prevScore: 0 }, "E"]
        ];

        it.each(interactionCases)(
            'With inputs %j expected %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );

        // ---- Sanity / combination cases ----
        const sanityCases = [
            [{}, "E"], // all zeros -> E
            [{ prevScore: 1 }, "D"],
            // all A=1 -> first A encountered returns B (q118 is checked first)
            [A_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "B"],
            // all B=1 and A=0 -> C (first B encountered)
            [B_questions.reduce((acc, q) => (acc[q] = 1, acc), {}), "C"],
            // A and B both >1 -> A (A precedence)
            [{ q118: 4, q120: 4 }, "A"],
            // A zero, B zero, but C positives -> C
            [{ q124: 2, q128: 1 }, "C"]
        ];

        it.each(sanityCases)(
            'Sanity: inputs %j -> %s',
            (overrides, expected) => {
                const args = makeArgs(overrides);
                expect(F.calcOpthalmic(...args)).toBe(expected);
            }
        );
    });


});