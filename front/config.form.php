<?php
include ("../../../inc/includes.php");

Session::checkRight("config", UPDATE);

$config = new \GlpiPlugin\Solutionapprovalguard\Config();

// Tenta carregar do banco. Se não existir (porque o plugin não foi reinstalado), ele cria na hora
if (!$config->getFromDB(1)) {
    $config->add([
        'id' => 1,
        'allow_comments' => 0
    ]);
    $config->getFromDB(1);
}

// Salva os dados se o form for enviado
if (isset($_POST["update"])) {
    $result = $config->update([
        'id'             => 1,
        'allow_comments' => (int)($_POST['allow_comments'] ?? 0),
    ]);
    if ($result) {
        Session::addMessageAfterRedirect(
            __('Configuration saved successfully.', 'solutionapprovalguard'),
            false,
            INFO
        );
    } else {
        Session::addMessageAfterRedirect(
            __('Error saving configuration.', 'solutionapprovalguard'),
            false,
            ERROR
        );
    }
    Html::back();
}

// Renderiza a página
Html::header(\GlpiPlugin\Solutionapprovalguard\Config::getTypeName(1), $_SERVER['PHP_SELF'], "config", "plugins");

// Chama o formulário diretamente em vez de tentar montar as abas
$config->showForm(1);

Html::footer();