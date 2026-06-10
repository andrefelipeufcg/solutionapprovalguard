<?php
include ("../../../inc/includes.php");

// Verifica se o usuário tem permissão de gerenciar configurações
Session::checkRight("config", UPDATE);

$config = new PluginSolutionapprovalguardConfig();

// Carrega os dados atuais do banco de dados (ID 1)
$config->getFromDB(1);

// Salva os dados se o form for enviado
if (isset($_POST["update"])) {
    $config->update($_POST);
    Html::back();
}

// Renderiza a página
Html::header(PluginSolutionapprovalguardConfig::getTypeName(1), $_SERVER['PHP_SELF'], "config", "plugins");

// Chama o formulário diretamente em vez de tentar montar as abas
$config->showForm(1);

Html::footer();