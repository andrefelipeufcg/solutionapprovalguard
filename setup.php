<?php
define('PLUGIN_SOLUTIONAPPROVALGUARD_VERSION', '1.0.0');

function plugin_init_solutionapprovalguard() {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['solutionapprovalguard'] = true;

    // Hook disparado logo ANTES de inserir no banco
    $PLUGIN_HOOKS['pre_item_add']['solutionapprovalguard'] = [
        'ITILFollowup' => 'plugin_solutionapprovalguard_pre_item_add'
    ];

    // Registra a página de configuração
    $PLUGIN_HOOKS['config_page']['solutionapprovalguard'] = 'front/config.form.php';

    // Registra a classe para aparecer na aba Configurar > Plugins
    Plugin::registerClass('PluginSolutionapprovalguardConfig', [
        'addtabon' => ['Config']
    ]);
}

function plugin_version_solutionapprovalguard() {
    return [
        'name'           => 'Solution Approval Guard',
        'version'        => PLUGIN_SOLUTIONAPPROVALGUARD_VERSION,
        'author'         => 'andrefelipeufcg',
        'license'        => 'GPLv3+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0.0',
                'max' => '11.1.0'
            ]
        ]
    ];
}

function plugin_solutionapprovalguard_check_prerequisites() {
    return true;
}

function plugin_solutionapprovalguard_check_config() {
    return true;
}