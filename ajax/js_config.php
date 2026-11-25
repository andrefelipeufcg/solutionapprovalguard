<?php

if (!defined('GLPI_ROOT')) {
    $inc = __DIR__ . '/../../../inc/includes.php';
    if (!file_exists($inc)) {
        $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/inc/includes.php';
    }
    if (!file_exists($inc)) {
        $inc = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/../inc/includes.php';
    }
    if (!file_exists($inc)) {
        http_response_code(503);
        exit;
    }
    include $inc;
}

header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: private, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');

if (!Session::getLoginUserID()) {
    http_response_code(403);
    exit;
}

include_once __DIR__ . '/../hook.php';

$config = [
    'mode' => plugin_solutionapprovalguard_get_config(),
    'defaultComment' => __('Solution approved', 'solutionapprovalguard'),
    'i18n' => [
        'title' => __('Confirm approval with a comment', 'solutionapprovalguard'),
        'message' => __('You are about to approve the solution while leaving a comment.', 'solutionapprovalguard'),
        'detail' => __('If the problem is not resolved, cancel and use Refuse instead.', 'solutionapprovalguard'),
        'confirm' => __('Approve anyway', 'solutionapprovalguard'),
        'cancel' => __('Cancel', 'solutionapprovalguard'),
    ],
];

echo 'window.PLUGIN_SAG_CONFIG = ' . json_encode(
    $config,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) . ';';