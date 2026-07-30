<?php
function plugin_solutionapprovalguard_install() {
    global $DB;

    $default_charset   = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();
    $migration         = new Migration(PLUGIN_SOLUTIONAPPROVALGUARD_VERSION);

    // Cria a tabela de configuração se não existir
    if (!$DB->tableExists('glpi_plugin_solutionapprovalguard_configs')) {
        $query = "CREATE TABLE `glpi_plugin_solutionapprovalguard_configs` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `allow_comments` tinyint NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB
        DEFAULT CHARSET={$default_charset}
        COLLATE={$default_collation}";
        $DB->doQuery($query);

        // Insere o registro padrão (permitindo comentários = 0)
        $DB->insert('glpi_plugin_solutionapprovalguard_configs', [
            'id' => 1,
            'allow_comments' => 0
        ]);
    }

    $migration->executeMigration();
    return true;
}

function plugin_solutionapprovalguard_uninstall() {
    global $DB;
    // Remove a tabela corretamente utilizando a função nativa do GLPI 11
    if ($DB->tableExists('glpi_plugin_solutionapprovalguard_configs')) {
        $DB->dropTable('glpi_plugin_solutionapprovalguard_configs');
    }
    return true;
}

// LÊ A CONFIGURAÇÃO USANDO A CLASSE (Garante que tela e hook usem os mesmos dados)
function plugin_solutionapprovalguard_get_config() {
    $config = new \GlpiPlugin\Solutionapprovalguard\Config();
    if ($config->getFromDB(1)) {
        return (int)$config->fields['allow_comments'];
    }
    return 0;
}

function plugin_solutionapprovalguard_get_content($input_array = null) {
    if (is_array($input_array) && isset($input_array['content'])) {
        $raw = $input_array['content'];
    } else {
        $raw = $_POST['content'] ?? $_GET['content'] ?? '';
        if (empty($raw)) {
            $json = file_get_contents('php://input');
            if (!empty($json)) {
                $data = json_decode($json, true);
                $raw = $data['content'] ?? '';
            }
        }
    }
    return trim(str_replace('&nbsp;', '', strip_tags(html_entity_decode($raw))));
}

// Gatilho Principal (Timeline Adicionar e Fechar)
function plugin_solutionapprovalguard_pre_item_add(CommonDBTM $item) {
    if ($item instanceof ITILFollowup) {
        $is_approval = (isset($item->input['add_close'])) || (isset($item->input['_close']) && $item->input['_close'] == 1);
        
        if ($is_approval) {
            // Agora sim ele vai ler o "2" que você salvou na tela!
            $allow_comments = plugin_solutionapprovalguard_get_config();
            
            $clean_content = plugin_solutionapprovalguard_get_content($item->input);
            $default_msg = __('Solution approved', 'solutionapprovalguard');
            
            if (!empty($clean_content) && $clean_content !== $default_msg) {
                if ($allow_comments == 2) {
                    Session::addMessageAfterRedirect(
                        __('It is not allowed to enter comments when approving a solution. Please leave the text box empty or refuse the solution.', 'solutionapprovalguard'),
                        false,
                        ERROR
                    );
                    
                    // Com a config correta, isto vai abortar a gravação com sucesso.
                    $item->input = false;
                    return false;
                    
                } elseif ($allow_comments == 1) {
                    Session::addMessageAfterRedirect(
                        __('Warning: Your solution was approved, but to report pending issues you must refuse the solution or open a new ticket.', 'solutionapprovalguard'),
                        false,
                        WARNING
                    );
                }
            } else {
                $item->input['content'] = $default_msg;
            }
        }
    }
    return true;
}

// Gatilho Secundário (Mudança de Status Direta para "Aprovada")
function plugin_solutionapprovalguard_pre_item_update(CommonDBTM $item) {
    if ($item instanceof ITILSolution) {
        if (isset($item->input['status']) && $item->input['status'] == 3) {
            $allow_comments = plugin_solutionapprovalguard_get_config();
            
            if ($allow_comments == 2) {
                $clean_content = plugin_solutionapprovalguard_get_content($item->input);
                $default_msg = __('Solution approved', 'solutionapprovalguard');
                
                if (!empty($clean_content) && $clean_content !== $default_msg) {
                    Session::addMessageAfterRedirect(
                        __('It is not allowed to enter comments when approving a solution. Please leave the text box empty or refuse the solution.', 'solutionapprovalguard'),
                        false,
                        ERROR
                    );
                    $item->input = false;
                    return false;
                }
            }
        }
    }
    return true;
}