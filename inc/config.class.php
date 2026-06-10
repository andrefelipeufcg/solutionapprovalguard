<?php
class PluginSolutionapprovalguardConfig extends CommonDBTM {
    protected $displaylist = false;

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
        echo "<form name='form' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "' method='post'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2'>" . __('Configurações de Aprovação', 'solutionapprovalguard') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Permitir comentários ao aprovar solução?', 'solutionapprovalguard') . "</td>";
        echo "<td>";
        // Usa o dropdown nativo de Sim/Não do GLPI
        Dropdown::showYesNo('allow_comments', $this->fields['allow_comments'] ?? 1);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='2' class='center'>";
        echo "<input type='hidden' name='id' value='1'>";
        echo "<input type='submit' name='update' class='btn btn-primary' value='" . _sx('button', 'Save') . "'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
        return true;
    }
}