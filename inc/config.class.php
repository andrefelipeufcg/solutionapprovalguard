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
        echo "<tr><th colspan='2'>" . __('Configurações de Aprovação de Solução', 'solutionapprovalguard') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Comentários durante aprovação de solução', 'solutionapprovalguard') . "</td>";
        echo "<td>";
        
        // Array com as opções em Radio Buttons
        $opcoes = [
            0 => __('Permitidos (comportamento padrão do GLPI)', 'solutionapprovalguard'),
            1 => __('Exibir aviso ao usuário, permitindo a aprovação da solução', 'solutionapprovalguard'),
            2 => __('Bloquear aprovação da solução quando houver comentário', 'solutionapprovalguard')
        ];

        // Resgata o valor salvo no banco (ou assume 0 como padrão)
        $valor_atual = $this->fields['allow_comments'] ?? 0;

        // Desenha os botões de rádio
        foreach ($opcoes as $valor => $rotulo) {
            $checked = ($valor == $valor_atual) ? "checked='checked'" : "";
            
            echo "<label style='display: block; margin-bottom: 8px; cursor: pointer;'>";
            echo "<input type='radio' name='allow_comments' value='$valor' $checked> ";
            echo $rotulo;
            echo "</label>";
        }

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