/*
 * widget-builder.js — generic, schema-driven form renderer for the visual
 * widget config builder in Manage Assessments.
 *
 * Design guarantee: the builder is only a JSON-authoring aid on top of the
 * existing #modal_given textarea, which stays the single source of truth. The
 * builder collects its form state and writes it (as pretty JSON) into that same
 * textarea, then triggers the EXISTING live preview (refreshWidgetPreviewDebounced,
 * defined in manage_assessments.php). The server (save_assessment / preview_widget)
 * needs zero changes — it still just reads `given`.
 *
 * Field schemas live in widget-schemas.js (window.widgetSchemas). A widget with
 * no schema simply has no builder tab and falls back to the raw-JSON textarea.
 *
 * Public API (called from manage_assessments.php):
 *   window.renderWidgetBuilder(schema, data)  — render form into #modal_builder_pane
 *   window.switchWidgetConfigTab('builder'|'raw')
 *   window.initWidgetConfigUI()               — call at the end of toggleGivenWrap()
 */
(function () {
    'use strict';

    var cbCounter = 0;
    var topGetters = [];
    var originalData = {};

    function el(tag, attrs, children) {
        var node = document.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function (k) {
                if (k === 'class') node.className = attrs[k];
                else if (k === 'text') node.textContent = attrs[k];
                else if (k === 'html') node.innerHTML = attrs[k];
                else node.setAttribute(k, attrs[k]);
            });
        }
        (children || []).forEach(function (c) { if (c) node.appendChild(c); });
        return node;
    }

    // --- individual field builders. Each returns { el, getValue }. getValue
    //     reads the live DOM so it stays correct as list/group rows are added
    //     or removed after the field is first built. -----------------------

    function buildScalar(spec, value) {
        var group = el('div', { class: 'form-group mb-2' });
        if (spec.label) {
            group.appendChild(el('label', { class: 'small font-weight-bold mb-1', text: spec.label }));
        }
        var input;
        if (spec.type === 'textarea') {
            input = el('textarea', { class: 'form-control form-control-sm', rows: spec.rows || 2 });
            input.value = value != null ? value : '';
        } else if (spec.type === 'number') {
            input = el('input', { type: 'number', class: 'form-control form-control-sm' });
            if (spec.min != null) input.min = spec.min;
            input.value = (value != null && value !== '') ? value : (spec.default != null ? spec.default : '');
        } else if (spec.type === 'select') {
            input = el('select', { class: 'form-control form-control-sm' });
            (spec.options || []).forEach(function (opt) {
                input.appendChild(el('option', { value: opt, text: opt }));
            });
            input.value = value != null ? value : (spec.default != null ? spec.default : '');
        } else { // text
            input = el('input', { type: 'text', class: 'form-control form-control-sm' });
            input.value = value != null ? value : '';
        }
        if (spec.placeholder) input.placeholder = spec.placeholder;
        group.appendChild(input);
        if (spec.help) group.appendChild(el('small', { class: 'form-text text-muted', text: spec.help }));

        return {
            el: group,
            getValue: function () {
                if (spec.type === 'number') {
                    var n = parseInt(input.value, 10);
                    if (isNaN(n)) return spec.default != null ? spec.default : 0;
                    return n;
                }
                return input.value;
            }
        };
    }

    function buildCheckbox(spec, value) {
        var group = el('div', { class: 'form-check mb-2' });
        var input = el('input', { type: 'checkbox', class: 'form-check-input' });
        input.checked = value != null ? !!value : !!spec.default;
        input.id = 'wb_cb_' + (cbCounter++);
        var label = el('label', { class: 'form-check-label small', text: spec.label });
        label.setAttribute('for', input.id);
        group.appendChild(input);
        group.appendChild(label);
        if (spec.help) group.appendChild(el('small', { class: 'form-text text-muted', text: spec.help }));
        return { el: group, getValue: function () { return input.checked; } };
    }

    function buildList(spec, value) {
        var wrap = el('div', { class: 'form-group mb-2' });
        if (spec.label) wrap.appendChild(el('label', { class: 'small font-weight-bold mb-1 d-block', text: spec.label }));
        if (spec.help) wrap.appendChild(el('small', { class: 'form-text text-muted mb-1 d-block', text: spec.help }));
        var list = el('div', { class: 'wb-list' });
        wrap.appendChild(list);

        function addItem(v) {
            var row = el('div', { class: 'input-group input-group-sm mb-1 wb-list-item' });
            var input = el('input', { type: 'text', class: 'form-control form-control-sm wb-list-input' });
            input.value = v != null ? v : '';
            if (spec.placeholder) input.placeholder = spec.placeholder;
            var append = el('div', { class: 'input-group-append' });
            var rm = el('button', { type: 'button', class: 'btn btn-outline-danger', html: '&times;' });
            rm.addEventListener('click', function () { row.remove(); syncBuilderToTextarea(); });
            append.appendChild(rm);
            row.appendChild(input);
            row.appendChild(append);
            list.appendChild(row);
        }

        (Array.isArray(value) ? value : []).forEach(addItem);

        var addBtn = el('button', {
            type: 'button', class: 'btn btn-sm btn-outline-secondary',
            html: '<i class="fas fa-plus"></i> Add ' + (spec.itemLabel || 'item')
        });
        addBtn.addEventListener('click', function () { addItem(''); syncBuilderToTextarea(); });
        wrap.appendChild(addBtn);

        return {
            el: wrap,
            getValue: function () {
                var out = [];
                list.querySelectorAll('.wb-list-input').forEach(function (inp) {
                    if (inp.value.trim() !== '') out.push(inp.value);
                });
                return out;
            }
        };
    }

    function buildGroupList(spec, value) {
        var wrap = el('div', { class: 'form-group mb-2 p-2 border rounded' });
        if (spec.label) wrap.appendChild(el('label', { class: 'small font-weight-bold mb-1 d-block', text: spec.label }));
        if (spec.help) wrap.appendChild(el('small', { class: 'form-text text-muted mb-1 d-block', text: spec.help }));
        var list = el('div', { class: 'wb-grouplist' });
        wrap.appendChild(list);

        function addCard(obj) {
            obj = obj || {};
            var card = el('div', { class: 'card card-body p-2 mb-2 bg-light wb-group-card' });
            var header = el('div', { class: 'd-flex justify-content-between align-items-center mb-1' });
            header.appendChild(el('span', { class: 'small text-muted font-weight-bold', text: spec.itemLabel || 'Item' }));
            var rm = el('button', { type: 'button', class: 'btn btn-sm btn-outline-danger py-0 px-2', html: '&times;' });
            rm.addEventListener('click', function () { card.remove(); syncBuilderToTextarea(); });
            header.appendChild(rm);
            card.appendChild(header);

            var getters = [];
            spec.fields.forEach(function (sub) {
                var built = buildField(sub, obj[sub.key]);
                card.appendChild(built.el);
                getters.push({ key: sub.key, getValue: built.getValue });
            });
            card._collect = function () {
                var o = {};
                getters.forEach(function (g) { o[g.key] = g.getValue(); });
                return o;
            };
            list.appendChild(card);
        }

        (Array.isArray(value) ? value : []).forEach(addCard);

        var addBtn = el('button', {
            type: 'button', class: 'btn btn-sm btn-outline-secondary',
            html: '<i class="fas fa-plus"></i> Add ' + (spec.itemLabel || 'item')
        });
        addBtn.addEventListener('click', function () { addCard({}); syncBuilderToTextarea(); });
        wrap.appendChild(addBtn);

        return {
            el: wrap,
            getValue: function () {
                var out = [];
                list.querySelectorAll(':scope > .wb-group-card').forEach(function (card) {
                    out.push(card._collect());
                });
                return out;
            }
        };
    }

    function buildField(spec, value) {
        switch (spec.type) {
            case 'checkbox': return buildCheckbox(spec, value);
            case 'list': return buildList(spec, value);
            case 'group_list': return buildGroupList(spec, value);
            default: return buildScalar(spec, value);
        }
    }

    // --- render / collect / sync --------------------------------------------

    window.renderWidgetBuilder = function (schema, data) {
        var container = document.getElementById('modal_builder_pane');
        if (!container) return;
        container.innerHTML = '';
        topGetters = [];
        // A single-field schema (quiz/secure_quiz — one 'questions' group_list) may
        // be fed a bare array [ {...}, {...} ] instead of { questions:[...] }; wrap
        // it into that one field so the builder populates instead of blanking (and
        // then silently overwriting the pasted config with an empty list).
        if (Array.isArray(data) && schema.fields.length === 1) {
            var wrapped = {};
            wrapped[schema.fields[0].key] = data;
            data = wrapped;
        }
        originalData = (data && typeof data === 'object' && !Array.isArray(data)) ? data : {};
        schema.fields.forEach(function (spec) {
            var built = buildField(spec, originalData[spec.key]);
            container.appendChild(built.el);
            topGetters.push({ key: spec.key, getValue: built.getValue });
        });
    };

    function collectWidgetBuilder() {
        // Preserve unknown top-level keys the schema doesn't model (e.g. a widget
        // config with an extra field authored in raw JSON), then overwrite the
        // schema-known keys with the current form values.
        var out = {};
        Object.keys(originalData).forEach(function (k) { out[k] = originalData[k]; });
        topGetters.forEach(function (g) { out[g.key] = g.getValue(); });
        return out;
    }

    function syncBuilderToTextarea() {
        var ta = document.getElementById('modal_given');
        if (!ta) return;
        try {
            ta.value = JSON.stringify(collectWidgetBuilder(), null, 2);
        } catch (e) {
            return;
        }
        if (typeof window.refreshWidgetPreviewDebounced === 'function') {
            window.refreshWidgetPreviewDebounced();
        }
    }

    // --- tab wiring ----------------------------------------------------------

    function currentWidgetKey() {
        var sel = document.getElementById('modal_widget_id');
        if (!sel) return null;
        var opt = sel.options[sel.selectedIndex];
        return opt ? (opt.dataset.key || null) : null;
    }

    function currentSchema() {
        var key = currentWidgetKey();
        return (key && window.widgetSchemas && window.widgetSchemas[key]) ? window.widgetSchemas[key] : null;
    }

    function setTabActive(which) {
        var builderTab = document.getElementById('widget_tab_builder');
        var rawTab = document.getElementById('widget_tab_raw');
        var builderPane = document.getElementById('widget_builder_pane');
        var rawPane = document.getElementById('widget_raw_pane');
        if (!builderTab) return;
        var builderOn = which === 'builder';
        builderTab.classList.toggle('active', builderOn);
        rawTab.classList.toggle('active', !builderOn);
        builderPane.style.display = builderOn ? '' : 'none';
        rawPane.style.display = builderOn ? 'none' : '';
    }

    window.switchWidgetConfigTab = function (which) {
        var warn = document.getElementById('modal_builder_warning');
        if (which === 'builder') {
            var schema = currentSchema();
            if (!schema) return;
            var ta = document.getElementById('modal_given');
            var data = {};
            if (ta && ta.value.trim()) {
                try {
                    data = JSON.parse(ta.value);
                } catch (e) {
                    if (warn) {
                        warn.textContent = 'Fix the JSON below before switching to the Visual Builder (' + e.message + ').';
                        warn.style.display = '';
                    }
                    setTabActive('raw');
                    return;
                }
            }
            if (warn) warn.style.display = 'none';
            window.renderWidgetBuilder(schema, data);
            setTabActive('builder');
        } else {
            setTabActive('raw');
        }
    };

    // Called at the end of toggleGivenWrap() once the widget config section is
    // shown (non-iq_discussion widgets only). Shows/hides the Builder tab based
    // on whether the selected widget has a schema, defaulting to the builder.
    window.initWidgetConfigUI = function () {
        var builderTabLi = document.getElementById('widget_tab_builder_li');
        var warn = document.getElementById('modal_builder_warning');
        if (warn) warn.style.display = 'none';
        if (currentSchema()) {
            if (builderTabLi) builderTabLi.style.display = '';
            window.switchWidgetConfigTab('builder');
        } else {
            if (builderTabLi) builderTabLi.style.display = 'none';
            setTabActive('raw');
        }
    };

    // Live-sync: delegated listeners on the (persistent) builder pane survive
    // re-renders, so scalar edits push into the textarea + preview immediately.
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('modal_builder_pane');
        if (container) {
            container.addEventListener('input', syncBuilderToTextarea);
            container.addEventListener('change', syncBuilderToTextarea);
        }
    });
})();
