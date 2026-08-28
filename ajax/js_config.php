<?php
/**
 * Exposes the plugin configuration (whether "warning" mode is active or
 * not) to the front-end JavaScript, along with the translated labels used
 * by the confirmation dialog, so that public/js/solutionapprovalguard.js
 * can decide whether to ask for confirmation before an approval.
 *
 * Served as JavaScript (Content-Type: application/javascript) so it can be
 * loaded with a plain <script src="..."> tag, in the same order as the
 * behaviour file (see setup.php).
 */
$inc = __DIR__ . '/../../../inc/includes.php';
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/inc/includes.php'; }
if (!file_exists($inc)) { $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../inc/includes.php'; }
include $inc;

include_once(__DIR__ . '/../hook.php');

header('Content-Type: application/javascript; charset=UTF-8');
// This file depends on the session (user/rights) and on the plugin
// configuration: we avoid caching it between users or after a setting
// change in Setup > Plugins.
header('Cache-Control: no-store, must-revalidate');

$mode = 0;
if (Session::getLoginUserID()) {
    $mode = (int) plugin_solutionapprovalguard_get_config();
}

$config = [
    'mode' => $mode,
    'i18n' => [
        'title'         => __('Confirm approval with a comment', 'solutionapprovalguard'),
        'message'       => __('You are about to approve the solution while leaving a comment.', 'solutionapprovalguard'),
        'detail'        => __('This comment will not be treated as a pending issue: the ticket will be closed as if the solution had fully solved the problem.', 'solutionapprovalguard'),
        'note'          => __('If something is still wrong, click Cancel and use "Refuse" instead.', 'solutionapprovalguard'),
        'confirm_label' => __('Approve anyway', 'solutionapprovalguard'),
        'cancel_label'  => __('Cancel', 'solutionapprovalguard'),
    ],
];

echo 'var PLUGIN_SAG_CONFIG = ' . json_encode(
    $config,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) . ';' . "\n";
