# Chokko Melt — Sistema de Pedidos para Confeitaria
**TCC — Desenvolvimento de Sistemas | 3º Semestre**
Desenvolvido por: Wilson Teofilo · Guilherme

---

## O que é este projeto

Sistema web completo para gestão de pedidos de uma confeitaria localizada no Grajaú – SP. O cliente acessa o cardápio, monta o carrinho, faz login e finaliza o pedido. O administrador gerencia produtos, acompanha pedidos em tempo real e consulta o faturamento.

---

## Como rodar localmente

**Requisitos:** XAMPP instalado (Apache + MySQL)

1. Clonar o repositório dentro de `C:\xampp\htdocs\ChokkoSemIA`
2. Iniciar Apache e MySQL no XAMPP Control Panel
3. Abrir o phpMyAdmin: `http://localhost/phpmyadmin`
4. Criar o banco: importar o arquivo `database/chokko_melt.sql`
5. Configurar a conexão: editar `/config/config.php` com seu usuário/senha do MySQL
6. Acessar no navegador: `http://localhost/ChokkoSemIA/`

**Login inicial do admin:**
- E-mail: `admin@chokkomelt.local`
- Senha: `admin123`
- ⚠️ Troque a senha após o primeiro acesso

---

## Estrutura de pastas

```
ChokkoSemIA/
├── admin/              → Painel administrativo
│   ├── assets/
│   │   ├── css/        → Estilos específicos do painel
│   │   └── js/         → Scripts do painel
│   ├── src/
│   │   └── auth_admin.php → Autenticação e ações do admin (login, cadastro, excluir, permissões)
│   ├── index.php       → Dashboard
│   ├── produtos.php    → CRUD de produtos e adicionais
│   ├── pedidos.php     → Gestão de pedidos e status
│   ├── usuarios.php    → Gestão de usuários/funcionários
│   ├── financeiro.php  → Extrato financeiro
│   └── config.php      → Configuração da loja
│
├── user/               → Interface do cliente
│   ├── assets/
│   │   ├── css/        → Estilos da loja
│   │   └── js/         → Scripts da loja
│   ├── src/
│   │   ├── auth.php    → Autenticação unificada (login cliente + admin, cadastro)
│   │   └── logout.php  → Encerramento de sessão
│   ├── index.php       → Cardápio
│   ├── login.php       → Login unificado (cliente e admin usam o mesmo formulário)
│   ├── cadastro.php    → Cadastro do cliente
│   ├── carrinho.php    → Sacola de compras
│   ├── finalizarPedido.php → Checkout (exige sessão de cliente)
│   ├── pedidos.php     → Histórico de pedidos (exige sessão de cliente)
│   ├── detalhes_pedido.php → Detalhe de um pedido (exige sessão de cliente)
│   └── perfil.php      → Perfil do cliente (exige sessão de cliente)
│
├── includes/           → Layouts compartilhados
│   ├── admin_header.php → Cabeçalho do painel (com guarda de sessão admin)
│   ├── admin_footer.php
│   ├── user_header.php
│   └── user_footer.php
│
├── config/
│   └── config.php      → Conexão MySQLi (usada por toda a aplicação)
│
├── database/
│   └── chokko_melt.sql → Script completo do banco de dados (19 tabelas)
│
└── LEIA-ME/            → Documentação do projeto
```

---

## Estado atual do projeto

### ✅ Concluído — Interface (HTML/CSS/JS)

**Área do Cliente (`/user/`)**
- [x] Cardápio com produtos, categorias e adicionais
- [x] Modal de produto com seleção de adicionais e observação
- [x] Sacola (carrinho) com edição de itens e adicionais
- [x] Checkout com validação de CPF, troco e tipo de entrega
- [x] Histórico de pedidos com status visual
- [x] Detalhe do pedido
- [x] Perfil do cliente
- [x] Login e Cadastro com medidor de força de senha e confirmação

**Área do Admin (`/admin/`)**
- [x] Dashboard com cards de resumo
- [x] Gestão de produtos (modal de criar/editar, adicionais, upload de imagem)
- [x] Gestão de pedidos com mudança de status
- [x] Extrato financeiro com filtros
- [x] Configuração da loja
- [x] Gestão de usuários com modal de permissões

### ✅ Concluído — Backend (PHP + MySQL)

**Auth e Segurança**
- [x] Login unificado: mesmo formulário autentica cliente e admin — `user/src/auth/auth.php`
- [x] Cadastro de cliente com validação de e-mail e telefone duplicado (verifica em `cliente` e `usuario`)
- [x] Cadastro de admin com validação de senha forte e confirmação
- [x] Todas as páginas do `/user/` protegidas: `perfil.php`, `pedidos.php`, `detalhes_pedido.php`, `finalizarPedido.php`
- [x] Painel `/admin/` protegido via `admin_header.php` — sem sessão, redireciona para login
- [x] Redirecionamento inteligente pós-login: volta para a página de origem (ex: carrinho → checkout)
- [x] Logout com `session_destroy()`

**Painel Admin**
- [x] CRUD de usuários administrativos (criar, listar, excluir, alterar permissões)
- [x] Permissões granulares por módulo salvas na coluna `permissoes` do banco
- [x] Proteção contra auto-exclusão (admin não consegue excluir a própria conta)

### 🔄 Pendente — Backend

**Wilson (`/user/`)**
- [ ] Cardápio lendo produtos do banco
- [ ] Carrinho persistido no banco (`item_carrinho`)
- [ ] Checkout gravando pedido em transação (todas as tabelas de pedido de uma vez)
- [ ] Histórico de pedidos lendo do banco
- [ ] Perfil editável salvando no banco

**Guilherme (`/admin/`)**
- [ ] CRUD de produtos/categorias/adicionais conectado ao banco
- [ ] Gestão de pedidos e mudança de status
- [ ] Dashboard com dados reais (COUNT, SUM do MySQL)
- [ ] Configuração da loja salva no banco

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3 (Vanilla), JavaScript |
| Backend | PHP 8.2+ |
| Banco de dados | MySQL 8+ |
| Conexão BD | MySQLi (padrão adotado no projeto) |
| Servidor local | XAMPP (Apache + MySQL) |

---

## Arquitetura de Autenticação

O sistema usa **um único formulário de login** (`user/login.php`) para clientes e administradores:

1. O `user/src/auth/auth.php` busca o e-mail primeiro na tabela `cliente`
2. Se achar → cria sessão de cliente (`$_SESSION['idlogado']`, `$_SESSION['userlogado']`) e vai para `user/index.php`
3. Se não achar em `cliente` → busca na tabela `usuario`
4. Se achar → cria sessão de admin (`$_SESSION['admin_id']`, `$_SESSION['admin_nome']`, `$_SESSION['admin_permissoes']`) e redireciona para `admin/index.php`

As sessões usam nomes diferentes para evitar conflito. As permissões do admin são carregadas da coluna `permissoes` do banco no momento do login.

---

## Regras de negócio principais

- Loja funciona das **15h às 22h** — fora desse horário não aceita pedidos
- Atendimento restrito ao **distrito do Grajaú – SP**
- Login obrigatório só na **finalização do pedido** — carrinho é acessível sem login, mas ao clicar em "Finalizar" o sistema verifica sessão e redireciona para login se necessário
- Pagamentos: **Pix, Débito, Crédito, Dinheiro** (sem gateway online)
- Pedido pode ser **cancelado pelo cliente** apenas se estiver `PENDENTE`
- Produto pode ser **congelado** (oculto) sem ser excluído do banco
- Máximo de **10 produtos distintos** por pedido
- E-mail e telefone são **únicos** — validados contra as duas tabelas (`cliente` e `usuario`) no cadastro

---

## Documentação completa na pasta LEIA-ME

| Arquivo | Conteúdo |
|---|---|
| `readme.md` | Este arquivo — visão geral e como rodar |
| `ROADMAP_TCC.md` | Cronograma de desenvolvimento Wilson + Guilherme |
| `INTEGRACAO_JS_PHP.txt` | Como o JavaScript se comunica com o PHP |
| `6_BANCO_ATT.md` | Evolução completa do banco de dados (banco obsoleto vs atual) |
| `_doc_extract_DOCUMENTACAO_MELT.txt` | Regras de negócio e funcionamento completo |
| `_doc_extract_regra_de_negocio.txt` | Documentação geral do TCC |
