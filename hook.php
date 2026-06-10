<?php
function plugin_solutionapprovalguard_install() {
    global $DB;
    
    // Cria a tabela de configuração se não existir
    if (!$DB->tableExists('glpi_plugin_solutionapprovalguard_configs')) {
        $query = "CREATE TABLE `glpi_plugin_solutionapprovalguard_configs` (
            `id` int NOT NULL AUTO_INCREMENT,
            `allow_comments` tinyint NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        // Prepara a query (transforma a string em um objeto mysqli_stmt)
        $stmt = $DB->prepare($query);
        
        // Executa a query preparada
        $DB->executeStatement($stmt);

        // Insere o registro padrão (permitindo comentários)
        $DB->insert('glpi_plugin_solutionapprovalguard_configs', [
            'id' => 1,
            'allow_comments' => 1
        ]);
    }
    return true;
}

function plugin_solutionapprovalguard_uninstall() {
    global $DB;
    if ($DB->tableExists('glpi_plugin_solutionapprovalguard_configs')) {
        $DB->dropTable('glpi_plugin_solutionapprovalguard_configs');
    }
    return true;
}

function plugin_solutionapprovalguard_pre_item_add(CommonDBTM $item) {
    if ($item instanceof ITILFollowup) {
        // O GLPI 11 converte 'add_close' para '_close' = 1 no prepareInputForAdd
        if (isset($item->input['_close']) && $item->input['_close'] == 1) {
            global $DB;
            
            // Busca a configuração atual
            $iterator = $DB->request(['FROM' => 'glpi_plugin_solutionapprovalguard_configs', 'WHERE' => ['id' => 1]]);
            $allow_comments = 1; // Default fallback
            if (count($iterator)) {
                $allow_comments = $iterator->current()['allow_comments'];
            }

            // Se estiver bloqueado (0)
            if ($allow_comments == 0) {
                // Checamos o $_POST original porque o prepareInputForAdd do core 
                // já pode ter injetado "Solution approved" no $item->input['content']
                $raw_content = $_POST['content'] ?? '';
                $clean_content = trim(str_replace('&nbsp;', '', strip_tags(html_entity_decode($raw_content))));
                
                if (!empty($clean_content)) {
                    // Exibe a mensagem de erro
                    Session::addMessageAfterRedirect(
                        __('Não é permitido inserir comentários ao aprovar uma solução. Por favor, deixe a caixa vazia ou recuse a solução.', 'solutionapprovalguard'),
                        false,
                        ERROR
                    );
                    
                    // No hook pre_item_add, setar o input para false aborta a inserção no banco
                    $item->input = false;
                }
            }
        }
    }
}