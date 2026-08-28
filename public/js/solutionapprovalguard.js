/**
 * Solution Approval Guard - front-end behaviour.
 *
 * When the plugin mode is set to "warning" (allow_comments == 1) and the
 * user has typed a comment while approving a ticket solution, this script
 * replaces the current "toast after the fact" feedback with a blocking
 * confirmation dialog (glpi_html_dialog, the same Bootstrap modal mechanism
 * GLPI itself uses for its native confirm() dialogs) that requires an
 * explicit choice *before* the approval is actually submitted.
 *
 * "Allowed" mode (0) and "Block" mode (2) are left untouched: mode 2 is
 * already enforced server-side (pre_item_add / pre_item_update), and mode
 * 0 needs no extra step.
 */
(function ($) {
    "use strict";

    function sanitizeText(html) {
        if (!html) {
            return '';
        }
        var div = document.createElement('div');
        // Mirror the server-side cleanup (strip_tags + html_entity_decode
        // + removal of empty &nbsp; padding left by TinyMCE).
        div.innerHTML = html.replace(/&nbsp;/gi, ' ');
        return (div.textContent || div.innerText || '').replace(/\s+/g, ' ').trim();
    }

    function getApprovalComment($form) {
        var $textarea = $form.find('textarea[name="content"]').first();
        if ($textarea.length === 0) {
            return '';
        }

        if (window.tinymce) {
            var editor = window.tinymce.get($textarea.attr('id'));
            if (editor) {
                return sanitizeText(editor.getContent());
            }
        }

        return sanitizeText($textarea.val());
    }

    $(document).on('click', 'button[name="add_close"]', function (e) {
        var $button = $(this);

        // This is the synthetic click we trigger ourselves once the user
        // has confirmed: let it go through and submit for real.
        if ($button.data('sagConfirmed')) {
            $button.removeData('sagConfirmed');
            return true;
        }

        if (typeof PLUGIN_SAG_CONFIG === 'undefined' || parseInt(PLUGIN_SAG_CONFIG.mode, 10) !== 1) {
            return true;
        }

        var $form = $button.closest('form');
        var comment = getApprovalComment($form);

        if (comment === '') {
            // Nothing typed: nothing to warn about.
            return true;
        }

        e.preventDefault();

        var i18n = PLUGIN_SAG_CONFIG.i18n || {};

        glpi_html_dialog({
            title: '<i class="ti ti-alert-triangle text-warning me-2"></i>' + i18n.title,
            body: '<p>' + i18n.message + '</p>'
                + '<p>' + i18n.detail + '</p>'
                + '<p><strong>' + i18n.note + '</strong></p>',
            buttons: [
                {
                    label: i18n.confirm_label,
                    class: 'btn-success',
                    click: function () {
                        $button.data('sagConfirmed', true);
                        $button.trigger('click');
                    }
                },
                {
                    label: i18n.cancel_label,
                    class: 'btn-secondary'
                }
            ]
        });

        return false;
    });
})(jQuery);
