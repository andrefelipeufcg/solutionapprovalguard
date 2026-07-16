<?php

use Glpi\Application\View\TemplateRenderer;

class PluginSolutionapprovalguardConfig extends CommonDBTM {
    protected $displaylist = false;

    // Força a tela a ler/salvar na mesma tabela exata que o hook criou, ignorando plurais
    public static function getTable($classname = '') {
        return 'glpi_plugin_solutionapprovalguard_configs';
    }

    static function getTypeName($nb = 0) {
        return __('Solution Approval Guard', 'solutionapprovalguard');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == 'Config') {
            return self::getTypeName();
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'Config') {
            $config = new self();
            $config->getFromDB(1);
            $config->showForm(1);
        }
        return true;
    }

    function showForm($id, array $options = []) {
        $valor_atual = $this->fields['allow_comments'] ?? 0;

        TemplateRenderer::getInstance()->display(
            '@solutionapprovalguard/config.html.twig',
            [
                'current_value' => $valor_atual,
            ]
        );

        return true;
    }
}