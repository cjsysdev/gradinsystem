/*
 * widget-schemas.js — declarative field schemas for the visual widget config
 * builder in Manage Assessments (application/views/admin/manage_assessments.php).
 *
 * Each entry keys off a widget_key and describes the shape of that widget's
 * `given` config so widget-builder.js can render a form and serialize it back
 * to the SAME JSON the raw-JSON textarea (#modal_given) accepts today. Keep
 * these in lock-step with the widgetExamples map in manage_assessments.php and
 * each application/views/widgets/<key>.php view's expected $config shape.
 *
 * A widget with NO entry here simply has no builder tab — it falls back to the
 * raw-JSON textarea exactly as before (lab_worksheet, case_study, case_dossier,
 * chapter_worksheet, iq_discussion).
 *
 * Field spec primitives understood by widget-builder.js:
 *   { key, type:'text',     label, placeholder, help }
 *   { key, type:'textarea', label, placeholder, help, rows }
 *   { key, type:'number',   label, help, default, min }
 *   { key, type:'checkbox', label, help, default }
 *   { key, type:'select',   label, help, options:[...], default }
 *   { key, type:'list',     label, help, itemLabel, placeholder }   // array of strings
 *   { key, type:'group_list', label, help, itemLabel, fields:[...] } // array of objects
 */
window.widgetSchemas = {
    worksheet: {
        title: 'Worksheet Form',
        fields: [
            { key: 'columns', type: 'list', label: 'Columns', itemLabel: 'Column',
              placeholder: 'Column heading', help: 'Each column becomes a cell the student fills in per row.' },
            { key: 'min_rows', type: 'number', label: 'Pre-filled blank rows', default: 3, min: 1 },
            { key: 'allow_add_rows', type: 'checkbox', label: 'Let students add more rows', default: true }
        ]
    },

    quiz: {
        title: 'Multiple Choice Quiz',
        fields: [
            { key: 'questions', type: 'group_list', label: 'Questions', itemLabel: 'Question', fields: [
                { key: 'question', type: 'text', label: 'Question', placeholder: 'e.g. 2 + 2 = ?' },
                { key: 'choices', type: 'list', label: 'Choices', itemLabel: 'Choice',
                  placeholder: 'Answer option', help: 'Leave empty for a free-text question (case-insensitive match).' },
                { key: 'answer', type: 'text', label: 'Correct answer', placeholder: 'Exact expected answer' }
            ] }
        ]
    },

    // secure_quiz uses the identical question format as quiz (same grade_quiz()
    // path server-side); reuse the same schema shape.
    secure_quiz: {
        title: 'Timed / Secure Quiz',
        fields: [
            { key: 'questions', type: 'group_list', label: 'Questions', itemLabel: 'Question', fields: [
                { key: 'question', type: 'text', label: 'Question', placeholder: 'e.g. 2 + 2 = ?' },
                { key: 'choices', type: 'list', label: 'Choices', itemLabel: 'Choice',
                  placeholder: 'Answer option', help: 'Leave empty for a free-text question (case-insensitive match).' },
                { key: 'answer', type: 'text', label: 'Correct answer', placeholder: 'Exact expected answer' }
            ] }
        ]
    },

    card_sort: {
        title: 'Card Sort Board',
        fields: [
            { key: 'bins', type: 'list', label: 'Bins (categories)', itemLabel: 'Bin', placeholder: 'Category name' },
            { key: 'items', type: 'list', label: 'Items to sort', itemLabel: 'Item', placeholder: 'Card text' },
            { key: 'require_justification', type: 'checkbox', label: 'Require a justification per placed item', default: false }
        ]
    },

    diagram: {
        title: 'Diagram / Flow Builder',
        fields: [
            { key: 'nodes', type: 'list', label: 'Nodes (in order)', itemLabel: 'Node',
              placeholder: 'Box label', help: 'A fixed sequence of labeled boxes; the student fills in the text inside each.' }
        ]
    },

    decision_matrix: {
        title: 'Decision Matrix',
        fields: [
            { key: 'rows', type: 'list', label: 'Rows (options)', itemLabel: 'Row', placeholder: 'Option name' },
            { key: 'columns', type: 'group_list', label: 'Columns (criteria)', itemLabel: 'Column', fields: [
                { key: 'name', type: 'text', label: 'Column name', placeholder: 'e.g. Cost' },
                { key: 'type', type: 'select', label: 'Cell type', options: ['text', 'select'], default: 'text' },
                { key: 'options', type: 'list', label: 'Dropdown options', itemLabel: 'Option',
                  placeholder: 'Choice', help: 'Only used when Cell type is "select". Leave empty for "text".' }
            ] }
        ]
    },

    calculator: {
        title: 'Calculator',
        fields: [
            { key: 'inputs', type: 'group_list', label: 'Inputs', itemLabel: 'Input', fields: [
                { key: 'label', type: 'text', label: 'Field label', placeholder: 'e.g. Equipment Cost (₱)' },
                { key: 'key', type: 'text', label: 'Variable name', placeholder: 'e.g. cost',
                  help: 'Use this name in the formula. Letters/numbers, no spaces.' }
            ] },
            { key: 'formula', type: 'text', label: 'Formula', placeholder: 'e.g. cost / savings',
              help: 'Supports + - * / and parentheses, using each input’s variable name.' },
            { key: 'result_label', type: 'text', label: 'Result label', placeholder: 'e.g. Months to Break Even' }
        ]
    },

    brainstorm: {
        title: 'Brainstorm & Voting Board',
        fields: [
            { key: 'prompt', type: 'textarea', label: 'Prompt', rows: 2,
              placeholder: 'The question students brainstorm around' },
            { key: 'max_votes_per_student', type: 'number', label: 'Max votes per student', default: 3, min: 1 }
        ]
    }
};
