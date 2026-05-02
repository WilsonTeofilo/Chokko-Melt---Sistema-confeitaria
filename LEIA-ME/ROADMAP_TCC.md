# Roadmap TCC — Chokko Melt (30 dias)
### Wilson (Loja) + Guilherme (Admin) | 3º Semestre

---

## Regra territorial — nunca violem isso

Wilson é dono absoluto de `/user/`.
Guilherme é dono absoluto de `/admin/`.
Os únicos arquivos compartilhados são `/config/db.php` e `/config/auth.php` — criados por Wilson e nunca alterados sem avisar o outro.

---

## Divisão de Responsabilidades

| | Wilson | Guilherme |
|---|---|---|
| **Pasta** | `/user/` | `/admin/` |
| **Arquivos** | `login.php` · `cadastro.php` · `index.php` · `carrinho.php` · `finalizarPedido.php` · `pedidos.php` · `detalhes_pedido.php` · `perfil.php` | `index.php` · `produtos.php` · `pedidos.php` · `usuarios.php` · `financeiro.php` · `config.php` |
| **Auth** | Faz o auth dos **dois lados** e entrega `/config/auth.php` | Só usa o que Wilson entregou |
| **Tabelas que cria dados** | `cliente` `endereco` `carrinho` `item_carrinho` `item_carrinho_adicional` `pedido` `item_pedido` `item_pedido_adicional` `pagamento` `avaliacao` | `usuario` `categoria` `produto` `adicional` `produto_adicional` `config_loja` |
| **Tabelas que só lê/atualiza** | `produto` `adicional` `status_pedido` `config_loja` | `pedido` `item_pedido` `pagamento` `cliente` `endereco` |
| **Sessão** | `$_SESSION['cliente']` | `$_SESSION['usuario']` (Wilson cria, Guilherme só usa) |

---

## Git — Como não se destruírem

> **CRÍTICO — Configurem no Dia 1, antes de qualquer código.**

**O que pesquisar juntos:** `git init` · `git clone` · `git branch` · `git checkout` · `git add` · `git commit` · `git push` · `git pull` · `git merge`

**Fluxo:**
- Branch `main` → sempre funciona. Nunca commitem direto aqui
- Branch `feature/wilson` → exclusivo do Wilson
- Branch `feature/guilherme` → exclusivo do Guilherme
- Toda **sexta-feira**: sentam juntos, fazem merge na `main` e resolvem conflitos

---

## SEMANA 1 — Dias 1 a 5 | BASE (fazem juntos)

Façam tudo desta semana juntos, sem dividir ainda. Sem esta base, tudo quebra depois.

### Dia 1 — Git
- Criar repositório no GitHub e clonar nos dois computadores
- Criar as branches `feature/wilson` e `feature/guilherme`
- Commitar o projeto atual como ponto de partida

### Dias 2 e 3 — Banco de dados
- Importar `chokko_melt.sql` no phpMyAdmin dos dois computadores
- Confirmar que as 18 tabelas foram criadas sem erro
- Testar o seed: `admin@chokkomelt.local` / `admin123` deve existir em `usuario`
- Criar juntos o arquivo `/config/db.php` com a conexão PDO

### Dias 4 e 5 — Teoria (sem escrever código ainda)
Ambos pesquisam e entendem:

| Conhecimento | Por que precisam |
|---|---|
| O que é PDO | Todas as consultas ao banco usam isso |
| Prepared Statements | Segurança — evita SQL Injection |
| `$_SESSION` | Controle de login em todas as páginas |
| `password_hash()` / `password_verify()` | Guardar e verificar senha com segurança |
| `header('Location:')` | Redirecionar o usuário entre páginas |
| `require` / `include` | Reaproveitar `db.php` e `auth.php` em todo lugar |

**Entregável da semana:**
- Repositório no GitHub com as duas branches criadas
- `/config/db.php` conectando no banco sem erro nos dois computadores
- Ambos entendem o básico de PDO e sessão

---

## SEMANA 2 — Dias 6 a 12 | AUTH + PRIMEIRO CRUD

---

### WILSON — Auth completo (cliente + admin)

Wilson faz **todo** o sistema de autenticação. É a mesma lógica dos dois lados (PDO + session + password_verify). Duplicar isso seria perda de tempo do Guilherme.

**Tabelas:** `cliente`, `usuario`

**`/user/cadastro.php`**
- O front já existe. Conectar o form ao PHP
- Ao submeter: `INSERT` na tabela `cliente` com `password_hash()` na senha
- Redirecionar para `login.php` após cadastro

**`/user/login.php`**
- O front já existe. Conectar o form ao PHP
- `SELECT` pelo email → `password_verify()` para conferir senha
- Se correto: gravar em `$_SESSION['cliente']` os campos `id_cliente`, `nome_cliente`, `email`
- Redirecionar para `index.php`

**`/admin/` login do admin**
- Mesma lógica, mas busca na tabela `usuario`
- Gravar em `$_SESSION['usuario']` os campos `id_usuario`, `nome`, `tipo_usuario`, `root`

**`/config/auth.php`** ← o mais importante desta semana
- Criar duas funções:
  - `exigirCliente()` → verifica `$_SESSION['cliente']`, redireciona para `login.php` se não existir
  - `exigirAdmin()` → verifica `$_SESSION['usuario']`, redireciona para login do admin se não existir
- Guilherme coloca `require '../config/auth.php'; exigirAdmin();` no topo de cada página dele

**Logout dos dois lados** — `session_destroy()` e redireciona

> **Entregar para o Guilherme até o Dia 8:** `/config/auth.php` funcionando + login do admin operacional.

**O que pesquisar/aprender:**
- `PHP PDO prepared statements INSERT SELECT`
- `PHP password_hash password_verify`
- `PHP session_start $_SESSION session_destroy`
- `PHP header Location redirect`
- `PHP funções reutilizáveis com require include`

---

### GUILHERME — CRUD de Produtos (começa no Dia 8)

**Dias 6 a 8:** Enquanto Wilson faz o auth, Guilherme estuda PDO e JOIN testando queries direto no phpMyAdmin. Não precisa do login ainda.

**Dia 8 em diante:** Wilson entrega o `auth.php`. Guilherme coloca `exigirAdmin()` no topo do `produtos.php` e começa a conectar ao banco.

**Tabelas:** `categoria`, `produto`, `adicional`, `produto_adicional`

**`produtos.php`** — o front já existe completo

O modal de produto já tem:
- `<select>` de categorias hardcoded (linha 112) → popular com dados reais da tabela `categoria`
- Checkboxes de adicionais hardcoded (linhas 143-149) → popular com dados reais da tabela `adicional`
- Upload de imagem (linha 132) → implementar `move_uploaded_file()` no PHP

O que implementar:
- Listar produtos com JOIN em `categoria`
- Modal de criar produto: `INSERT INTO produto` + `INSERT INTO produto_adicional` para cada adicional marcado
- Modal de editar: `SELECT` para preencher o form + `UPDATE`
- Ativar/desativar: `UPDATE produto SET disponibilidade = ? WHERE id_produto = ?`
- Criar novo adicional: `INSERT INTO adicional`

Como salvar adicionais marcados (`produto_adicional`): ao salvar o produto, apagar linhas antigas (`DELETE WHERE id_produto = ?`) e inserir as novas marcadas.

**O que pesquisar/aprender:**
- `PHP PDO SELECT JOIN múltiplas tabelas`
- `PHP PDO INSERT UPDATE DELETE`
- `PHP popular select options com banco de dados`
- `PHP popular checkboxes com banco de dados`
- `PHP move_uploaded_file upload imagem`
- `Deletar e reinserir relacionamento N:N PHP MySQL`

---

### Sincronização — Dia 12 (Sexta)
- Wilson testa login do admin — Guilherme confirma que `produtos.php` está protegido com `exigirAdmin()`
- Guilherme cadastra ao menos 3 categorias e 5 produtos no banco (Wilson vai precisar na semana 3)
- Merge na `main`

---

## SEMANA 3 — Dias 13 a 19 | CARDÁPIO + GESTÃO DE PEDIDOS

---

### WILSON — Cardápio e Carrinho conectados ao banco

**Tabelas:** `produto`, `categoria`, `adicional`, `produto_adicional`, `carrinho`, `item_carrinho`, `item_carrinho_adicional`

**`index.php`** (cardápio)
- Hoje lê produtos de dados mockados no JS
- PHP precisa gerar os produtos do banco e passar para o JavaScript
- Pesquise: `json_encode()` em PHP para o JS conseguir ler os dados
- Fazer JOIN de `produto` + `categoria` + adicionais disponíveis

**`carrinho.php`**
- Hoje usa localStorage. Precisa migrar para banco
- Quando cliente adiciona item: `INSERT` em `carrinho` (se não existir para esse cliente) e em `item_carrinho`
- Se tem adicionais: `INSERT` em `item_carrinho_adicional`
- Mostrar carrinho lendo de `item_carrinho` com JOIN em `produto`
- Editar quantidade: `UPDATE item_carrinho SET quantidade = ?`
- Remover item: `DELETE FROM item_carrinho WHERE id_item_carrinho = ?`

**O que pesquisar/aprender:**
- `PHP json_encode passar array para JavaScript`
- `PHP PDO lastInsertId usar como chave estrangeira`
- `PHP PDO SELECT JOIN carrinho produtos`
- `Como JavaScript lê dados gerados pelo PHP via json_encode`

---

### GUILHERME — Gestão de Pedidos no Admin

**Tabelas:** `pedido`, `item_pedido`, `item_pedido_adicional`, `cliente`, `endereco`, `status_pedido`, `pagamento`

**`pedidos.php`** (admin)
- Listar todos os pedidos: número, data, cliente (nome), valor total, status
- Filtro por status (PENDENTE, EM_PREPARO, ENVIADO, etc.)
- Ver detalhes do pedido: itens, adicionais, endereço, forma de pagamento
- Botões de mudança de status: Aceitar → Em Preparo → Enviado → Entregue
- Cancelar pedido com campo de motivo

Como mudar status: `UPDATE pedido SET id_status_pedido = ? WHERE id_pedido = ?`

**`index.php`** (dashboard admin)
- Cards: pedidos hoje, pedidos pendentes, faturamento do dia
- Pesquise SQL: `COUNT(*)`, `SUM(valor_total)`, `WHERE DATE(data_hora) = CURDATE()`

**O que pesquisar/aprender:**
- `PHP PDO SELECT JOIN pedido cliente endereco`
- `PHP PDO UPDATE status com prepared statement`
- `SQL COUNT SUM GROUP BY faturamento por dia`
- `PHP filtro dinâmico WHERE com PDO`
- `SQL WHERE DATE(campo) = CURDATE()`

---

### Sincronização — Dia 19 (Sexta)
- Wilson precisa que Guilherme tenha produtos no banco para o cardápio funcionar. Confirmar
- Merge na `main`

---

## SEMANA 4 — Dias 20 a 26 | PEDIDOS + ADMIN COMPLETO

---

### WILSON — Checkout, Histórico e Perfil

**Tabelas:** `pedido`, `item_pedido`, `item_pedido_adicional`, `pagamento`, `endereco`, `avaliacao`

**`finalizarPedido.php`**
- O front já existe com validações de CPF e troco em JS
- Ao clicar "Fazer Pedido": sequência em **transação PDO**:
  1. `INSERT INTO pedido` → `lastInsertId()` vira `$id_pedido`
  2. Para cada item: `INSERT INTO item_pedido` → `lastInsertId()` vira `$id_item`
  3. Para cada adicional do item: `INSERT INTO item_pedido_adicional`
  4. `INSERT INTO pagamento`
  5. `DELETE` do carrinho do cliente
  6. Tudo OK: `commit()`. Qualquer erro: `rollBack()`
- Redirecionar para `pedidos.php` com mensagem de sucesso

**`pedidos.php`** (user)
- `SELECT pedido.*, status_pedido.descricao FROM pedido JOIN status_pedido ... WHERE id_cliente = ?`
- Ordenar por `data_hora DESC`
- Botão "Cancelar" visível só quando status for `PENDENTE`

**`detalhes_pedido.php`** (user)
- Recebe `?id=X` via `$_GET`
- Mostrar itens, adicionais, valor, forma de pagamento, status
- Cancelamento: `UPDATE pedido SET id_status_pedido = 6, cancelado_por = 'CLIENTE', cancelado_em = NOW(), motivo_cancelamento = ?`

**`perfil.php`** (user)
- Mostrar dados do cliente logado lendo do banco pelo `id_cliente` da sessão
- Editar nome e telefone: `UPDATE cliente SET ... WHERE id_cliente = ?`

**O que pesquisar/aprender:**
- `PHP PDO beginTransaction commit rollBack`
- `PHP PDO lastInsertId encadeamento de inserts`
- `PHP $_GET receber parâmetro pela URL`
- `PHP PDO UPDATE WHERE seguro prepared statement`

---

### GUILHERME — Admin Completo

**`usuarios.php`**
- Listar usuários da tabela `usuario`
- Criar novo funcionário: `INSERT INTO usuario` com `password_hash()`
- Editar tipo (ADMIN/FUNCIONARIO)

**`config.php`**
- Ler linha da `config_loja` (sempre `id_config = 1`)
- Formulário para atualizar `hora_abre`, `hora_fecha`, `whatsapp`, `taxa_entrega_padrao`
- `UPDATE config_loja SET ... WHERE id_config = 1`

**`financeiro.php`**
- Relatório de vendas por período (filtro de data)
- Total de pedidos, faturamento, cancelamentos
- Usar `SUM`, `COUNT`, `GROUP BY DATE(data_hora)`

**O que pesquisar/aprender:**
- `PHP PDO SELECT UPDATE tabela configuração singleton`
- `PHP PDO INSERT usuario com password_hash`
- `SQL SUM COUNT GROUP BY DATE relatório`
- `PHP filtro de data PDO BETWEEN`

---

### Sincronização — Dia 26 (Sexta) — TESTE DE INTEGRAÇÃO REAL

1. Wilson faz login como cliente e realiza um pedido completo
2. Guilherme vê o pedido aparecer no painel admin
3. Guilherme muda status para "ACEITO" e depois "EM_PREPARO"
4. Wilson recarrega `pedidos.php` do user e vê o status atualizado

Se isso funcionar, o TCC está 90% pronto. Merge final na `main`.

---

## SEMANA 5 — Dias 27 a 30 | TESTES E ENTREGA

**Ambos juntos:**
- Testar todos os fluxos do início ao fim
- Corrigir bugs do teste de integração
- Verificar: todas as páginas sensíveis têm `exigirCliente()` ou `exigirAdmin()`?
- Testar casos extremos: produto indisponível, retirada sem endereço, troco menor que o total
- Escrever `README.md` explicando como rodar (importar SQL, configurar db.php, iniciar XAMPP)

**Wilson (se der tempo):**
- Avaliação: formulário com estrelas em `detalhes_pedido.php` quando status = ENTREGUE → `INSERT INTO avaliacao`

**Guilherme (se der tempo):**
- Horário de funcionamento: PHP lê `hora_abre`/`hora_fecha` da `config_loja` e compara com `date('H:i:s')` — bloqueia pedidos fora do horário

---

## Armadilhas — Leiam isso antes de acontecer

| Armadilha | Como evitar |
|---|---|
| Esquecer `session_start()` no topo | Colocar no primeiro `require` de cada página |
| Salvar senha em texto puro | Sempre `password_hash()`. Nunca guardar direta |
| JS valida mas PHP não valida | PHP deve revalidar CPF, troco e estoque no servidor |
| Inserir pedido sem transação | Se der erro na metade o banco fica partido. Use `beginTransaction()` |
| Wilson testar cardápio sem produtos | Guilherme precisa cadastrar produtos **antes** |
| Alterar pasta do outro | Nunca. Git resolve conflito mas não resolve desrespeito ao território |
| Reimportar SQL e perder dados | Fazer backup antes ou usar `INSERT IGNORE` no seed |

---

## Checklist Final — Antes de Entregar

**Auth**
- [ ] Login do cliente com senha criptografada
- [ ] Login do admin com senha criptografada
- [ ] Páginas do `/user/` bloqueadas sem sessão de cliente
- [ ] Páginas do `/admin/` bloqueadas sem sessão de admin
- [ ] Logout funciona nos dois lados

**Loja — Wilson**
- [ ] Cardápio carrega produtos reais do banco
- [ ] Produto com `disponibilidade = FALSE` não aparece
- [ ] Carrinho salva no banco (não só localStorage)
- [ ] Finalizar pedido grava em todas as tabelas em transação
- [ ] Cliente vê pedidos com status atual
- [ ] Cliente consegue cancelar pedido PENDENTE
- [ ] Perfil mostra e permite editar dados reais

**Admin — Guilherme**
- [ ] Produtos criados, editados e desativados
- [ ] Adicionais nos checkboxes salvos corretamente
- [ ] Admin vê pedidos e muda status
- [ ] Config da loja salva horário e taxa

**Integração**
- [ ] Wilson faz pedido → Guilherme vê no painel
- [ ] Guilherme muda status → Wilson vê atualizado
