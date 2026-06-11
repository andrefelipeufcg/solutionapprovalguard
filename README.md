# Solution Approval Guard (Plugin para GLPI 11)

[![GLPI 11.0+](https://img.shields.io/badge/GLPI-11.0%2B-blue.svg)](https://glpi-project.org/)
[![License: GPL v3+](https://img.shields.io/badge/License-GPL%20v3%2B-green.svg)](https://www.gnu.org/licenses/gpl-3.0.html)

O **Solution Approval Guard** é um plugin de usabilidade e integridade de processos desenvolvido especificamente para o **GLPI 11**. Ele permite que administradores controlem se os usuários finais podem ou não inserir comentários de texto no momento em que decidem **aprovar** a solução de um chamado.

---

## 📋 Sumário
- [O Problema (Motivação)](#-o-problema-motivação)
- [✨ Funcionalidades](#-funcionalidades)
- [⚙️ Modos de Configuração](#%EF%B8%8F-modos-de-configuração)
- [📂 Estrutura do Plugin](#-estrutura-do-plugin)
- [🚀 Instalação e Ativação](#-instalação-e-ativação)
- [🛠️ Limpeza de Cache](#%EF%B8%8F-limpeza-de-cache)
- [🧠 Arquitetura Técnica](#-arquitetura-técnica)
- [📄 Licença](#-licença)

---

## 🔍 O Problema (Motivação)

No comportamento padrão do GLPI, quando um técnico propõe uma solução para um chamado, o requerente tem duas ações possíveis: **Aprovar** ou **Recusar**. 

Contudo, é comum encontrar um desvio de comportamento de usabilidade no ambiente de produção: alguns usuários escrevem feedbacks indicando pendências, mas clicam equivocadamente no botão **Aprovar Solução**.

**Exemplo real:**
> *"O problema foi parcialmente resolvido, mas ainda estou sem acesso a um dos sistemas secundários."* > *(O usuário escreve isso e clica em **Aprovar** em vez de Recusar).*

### Consequências:
1. O chamado é encerrado e arquivado normalmente pelo sistema.
2. A equipe de suporte assume que o incidente foi 100% mitigado (visto que o status mudou para Fechado).
3. O feedback do usuário fica esquecido no histórico do chamado, gerando retrabalho futuro e impactando negativamente os indicadores de SLA e a satisfação real do cliente.

O **Solution Approval Guard** resolve essa contradição forçando o usuário a tomar uma decisão linear: se há o que comentar/reclamar, a solução deve ser recusada; se a solução resolveu o problema, ela deve ser aprovada com a caixa de texto limpa.

---

## ✨ Funcionalidades

- **Sanitização Inteligente de Rich Text:** O plugin limpa automaticamente tags HTML vazias (`<p></p>`, `<br>`) e entidades de espaço (`&nbsp;`) geradas nativamente pelo editor visual (TinyMCE) do GLPI, garantindo que o bloqueio só ocorra se o usuário tiver digitado texto real.
- **Interceptação Agnóstica de Requisições:** Capaz de capturar aprovações feitas tanto pelo formulário tradicional (`$_POST`) quanto por payloads assíncronos (`application/json`) enviados via AJAX pela nova linha do tempo (Timeline) do GLPI 11.
- **Painel Administrativo Modular:** Interface nativa integrada em *Configurar > Plugins*, utilizando seletores em botões de rádio para fácil manuseio.
- **Segurança para APIs:** Validações tratadas para não interromper ou quebrar integrações via API REST ou chamadas CLI (Command Line Interface).

---

## ⚙️ Modos de Configuração

O administrador pode definir três níveis de rigor no painel do plugin:

- **Permitidos (comportamento padrão do GLPI):** O plugin permanece neutro. Os usuários podem aprovar soluções e adicionar comentários simultaneamente.
- **Exibir aviso ao usuário:** Caso o usuário digite um comentário e clique em aprovar, o GLPI exibirá um balão de aviso amarelo (`WARNING`) no topo da tela, mas permitirá o fechamento do chamado. Ideal para fases de transição e aculturamento de usuários.
- **Bloquear aprovação da solução quando houver comentário:** O nível mais estrito. Se houver texto digitado, a gravação no banco de dados é **abortada imediatamente** e uma notificação vermelha de erro (`ERROR`) é exibida na tela, obrigando o usuário a apagar o texto para aprovar, ou usar o botão de recusa.

---

## 📂 Estrutura do Plugin

O repositório está estruturado seguindo rigorosamente as boas práticas e os padrões de desenvolvimento do ecossistema do GLPI 11:

```text
solutionapprovalguard/
├── README.md               # Este arquivo de documentação
├── setup.php               # Inicialização, ganchos (hooks) e metadados do plugin
├── hook.php                # Rotinas de instalação, desinstalação e regras de negócio
├── inc/
│   └── config.class.php    # Definição do Objeto de Banco de Dados e Formuário Gráfico
└── front/
    └── config.form.php     # Controlador de exibição e processamento de dados do painel
```

---

## 🚀 Instalação e Ativação

**Passo 1:** Baixe ou clone este repositório para dentro da pasta de plugins do seu GLPI:

```bash
cd /var/www/html/glpi/plugins/
git clone https://github.com/seu-usuario/solutionapprovalguard.git
```

**Passo 2:** Certifique-se de que as permissões de escrita e leitura pertençam ao usuário do seu servidor web (ex: `www-data` ou `apache`):

```bash
chown -R www-data:www-data solutionapprovalguard/
```

**Passo 3:** Acesse o painel do seu GLPI como Administrador.

**Passo 4:** Vá em **Configurar > Plugins**.

**Passo 5:** Localize o **Solution Approval Guard** na lista, clique em **Instalar** e, em seguida, em **Ativar** (ícone da chave verde).

**Passo 6:** Clique sobre o nome do plugin para definir o comportamento desejado e clique em **Salvar**.

---

## 🛠️ Limpeza de Cache

Como o GLPI 11 armazena pesadamente estruturas de plugins e views em cache (via Symfony/Twig), é **altamente recomendável** limpar o cache do sistema após a instalação ou atualização de arquivos do plugin.

Execute o comando a partir da pasta raiz do seu GLPI no terminal:

```bash
php bin/console cache:clear
```

*Ou, caso precise rodar com o usuário do servidor web:*

```bash
sudo -u www-data php bin/console cache:clear
```

Se o seu servidor web possuir cache de opcode ativo (OPcache), pode ser necessário reiniciar o serviço web para atualizar os arquivos em memória:

```bash
sudo systemctl restart apache2
# ou se usar nginx com php-fpm:
sudo systemctl restart php8.2-fpm
```

---

## 🧠 Arquitetura Técnica

### Ganchos de Escopo (Hooks)
O plugin utiliza o ecossistema de hooks do GLPI 11 para garantir cobertura total:
- `pre_item_update` amarrado à classe `ITILSolution`: Intercepta o clique direto no botão de aprovação assíncrono dentro da nova Timeline.
- `pre_item_add` amarrado à classe `ITILFollowup`: Atua como fallback de segurança caso a aprovação ocorra por meio de formulários legados ou ações estruturadas do tipo "Adicionar acompanhamento e fechar".

### Sincronização Estrita de Tabelas (`getTable`)
Para evitar que o motor ORM do GLPI pluralizasse erroneamente o nome da tabela no banco de dados (gerando incompatibilidades entre o salvamento da interface e a leitura do hook), a classe injeta explicitamente o método estático:

```php
public static function getTable($classname = '') {
    return 'glpi_plugin_solutionapprovalguard_configs';
}
```

Isso força o núcleo do sistema a ler e gravar rigorosamente na mesma tabela, garantindo que as alterações salvas pelo administrador entrem em vigor instantaneamente no motor de validação.

---

## 📄 Licença

Este plugin é software livre, distribuído sob os termos da **GNU General Public License** versão 3 ou posterior (GPLv3+). Sinta-se livre para clonar, modificar e contribuir para o projeto.

---
*Desenvolvido com 💙 para a comunidade GLPI.*