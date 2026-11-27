/* global glpi_html_dialog */
// Adapted from the confirmation proposal by steve-hbt (PR #3).
(function () {
    'use strict';

    if (window.pluginSagInitialized) {
        return;
    }
    window.pluginSagInitialized = true;

    const pending = new WeakSet();
    const confirmed = new WeakSet();

    function textContent(html) {
        const parsed = new DOMParser().parseFromString(String(html || ''), 'text/html');
        return (parsed.body.textContent || '').replace(/\u00a0/g, ' ').trim();
    }

    function comment(form) {
        const field = form.querySelector('textarea[name="content"], input[name="content"]');
        if (!field) {
            return '';
        }
        const editor = window.tinymce && field.id ? window.tinymce.get(field.id) : null;
        // Newer editors synchronize a hidden field; TinyMCE must be read directly.
        return textContent(editor ? editor.getContent() : field.value);
    }

    function escapeHtml(value) {
        const element = document.createElement('span');
        element.textContent = value || '';
        return element.innerHTML;
    }

    function intercept(event) {
        const config = window.PLUGIN_SAG_CONFIG;
        if (!config || Number(config.mode) !== 3) {
            return;
        }

        const button = event.type === 'click'
            ? event.target.closest('button[name="add_close"], input[name="add_close"]')
            : event.submitter;
        if (!button || button.name !== 'add_close' || !button.form) {
            return;
        }
        const form = button.form;
        if (!form.querySelector('[name="items_id"]') || !form.querySelector('[name="itemtype"]')) {
            return;
        }
        if (confirmed.has(form)) {
            return;
        }
        const content = comment(form);
        if (!content || content === config.defaultComment) {
            return;
        }

        // Capture before GLPI's click/AJAX and submit-once handlers run.
        event.preventDefault();
        event.stopImmediatePropagation();
        if (pending.has(form)) {
            return;
        }
        pending.add(form);

        function resume() {
            pending.delete(form);
            if (!form.isConnected || button.disabled) {
                return;
            }
            confirmed.add(form);
            try {
                button.click();
            } finally {
                confirmed.delete(form);
            }
        }

        const labels = config.i18n;
        if (typeof glpi_html_dialog !== 'function') {
            pending.delete(form);
            if (window.confirm(labels.message + '\n\n' + labels.detail + '\n\n' + labels.note)) {
                resume();
            }
            return;
        }

        let approved = false;
        glpi_html_dialog({
            title: '<i class="ti ti-alert-triangle text-warning me-2" aria-hidden="true"></i>' + escapeHtml(labels.title),
            body: '<p>' + escapeHtml(labels.message) + '</p><p>' + escapeHtml(labels.detail) + '</p><p><strong>' + escapeHtml(labels.note) + '</strong></p>',
            buttons: [
                { label: escapeHtml(labels.cancel), class: 'btn-secondary' },
                {
                    label: escapeHtml(labels.confirm),
                    class: 'btn-success text-white',
                    click: function () { approved = true; }
                }
            ],
            close: function () {
                pending.delete(form);
                if (approved) {
                    resume();
                }
            }
        });
    }

    document.addEventListener('click', intercept, true);
    document.addEventListener('submit', intercept, true);
})();