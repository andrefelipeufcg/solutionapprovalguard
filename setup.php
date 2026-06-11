<?php
define('PLUGIN_SOLUTIONAPPROVALGUARD_VERSION', '1.0.0');

function plugin_init_solutionapprovalguard() {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['solutionapprovalguard'] = true;

    // Carrega o arquivo de regras de negócio para a memória do servidor.
    // O uso da constante __DIR__ garante a montagem de um caminho absoluto seguro, 
    // enquanto o include_once impede erros fatais de redeclaração de funções 
    // caso o GLPI processe o setup.php mais de uma vez na mesma requisição.
    // Sem esta linha, os métodos amarrados no array $PLUGIN_HOOKS ficariam inacessíveis (órfãos).
    include_once(__DIR__ . '/hook.php');

    // Registrar hooks usando as chaves de string compatíveis com o núcleo
    // Para o hook pre_item_add do ITILFollowup
    $PLUGIN_HOOKS['pre_item_add']['solutionapprovalguard'] = [
        'ITILFollowup' => 'plugin_solutionapprovalguard_pre_item_add'
    ];

    // Para o hook pre_item_update do ITILSolution
    $PLUGIN_HOOKS['pre_item_update']['solutionapprovalguard'] = [
        'ITILSolution' => 'plugin_solutionapprovalguard_pre_item_update'
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
                'min' => '11.0.0'            
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