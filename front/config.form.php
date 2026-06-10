<?php
include ("../../../inc/includes.php");

// Verifica se o usuário tem permissão de gerenciar configurações
Session::checkRight("config", UPDATE);

$config = new PluginSolutionapprovalguardConfig();

// Salva os dados se o form for enviado
if (isset($_POST["update"])) {
    $config->update($_POST);
    Html::back();
}

// Renderiza a página
Html::header(PluginSolutionapprovalguardConfig::getTypeName(1), $_SERVER['PHP_SELF'], "config", "plugins");
$config->display(['id' => 1]);
Html::footer();