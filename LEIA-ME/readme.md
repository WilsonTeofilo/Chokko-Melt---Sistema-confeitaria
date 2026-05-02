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
5. Configurar a conexão: editar `/config/db.php` com seu usuário/senha do MySQL
6. Acessar no navegador: `http://localhost/ChokkoSemIA/`

**Login inicial do admin:**
- E-mail: `admin@chokkomelt.local`
- Senha: `admin123`
- ⚠️ Troque a senha após o primeiro acesso

---

## Estrutura de pastas

```
ChokkoSemIA/
├── admin/              → Painel administrativo (Guilherme)
│   ├── assets/
│   ├── index.php       → Dashboard
│   ├── produtos.php    → CRUD de produtos e adicionais
│   ├── pedidos.php     → Gestão de pedidos e status
│   ├── usuarios.php    → Gestão de usuários/funcionários
│   ├── financeiro.php  → Extrato financeiro
│   └── config.php      → Configuração da loja
│
├── user/               → Interface do cliente (Wilson)
│   ├── assets/
│   ├── index.php       → Cardápio
│   ├── login.php       → Login do cliente
│   ├── cadastro.php    → Cadastro do cliente
│   ├── carrinho.php    → Sacola de compras
│   ├── finalizarPedido.php → Checkout
│   ├── pedidos.php     → Histórico de pedidos
│   ├── detalhes_pedido.php → Detalhe de um pedido
│   └── perfil.php      → Perfil do cliente
│
├── config/             → Arquivos compartilhados
│   ├── db.php          → Conexão PDO (Wilson cria, todos usam)
│   └── auth.php        → Guards de autenticação (Wilson cria, todos usam)
│
├── database/
│   └── chokko_melt.sql → Script completo do banco de dados
│
└── LEIA-ME/            → Documentação do projeto
```

---

## Estado atual do projeto (Frontend concluído)

### ✅ Concluído — Interface (HTML/CSS/JS)

**Área do Cliente (`/user/`)**
- [x] Cardápio com produtos, categorias e adicionais
- [x] Modal de produto com seleção de adicionais e observação
- [x] Sacola (carrinho) com edição de itens e adicionais
- [x] Checkout com validação de CPF, troco e tipo de entrega
- [x] Histórico de pedidos com status visual
- [x] Detalhe do pedido
- [x] Perfil do cliente
- [x] Login e Cadastro
- [x] Design responsivo (Mobile First) com identidade visual marrom/bege

**Área do Admin (`/admin/`)**
- [x] Dashboard com cards de resumo
- [x] Gestão de produtos (modal de criar/editar, adicionais, upload de imagem)
- [x] Gestão de pedidos com mudança de status
- [x] Extrato financeiro com filtros
- [x] Configuração da loja
- [x] Gestão de usuários

### 🔄 Pendente — Backend (PHP + MySQL)

**Wilson (`/user/`)**
- [ ] Auth completo: login/cadastro cliente + auth.php compartilhado
- [ ] Cardápio lendo produtos do banco
- [ ] Carrinho persistido no banco (migrar do localStorage)
- [ ] Checkout gravando pedido em transação PDO
- [ ] Histórico de pedidos lendo do banco
- [ ] Perfil editável

**Guilherme (`/admin/`)**
- [ ] CRUD de produtos/categorias/adicionais conectado ao banco
- [ ] Gestão de pedidos e mudança de status
- [ ] Dashboard com dados reais (COUNT, SUM do MySQL)
- [ ] Configuração da loja salva no banco
- [ ] Gestão de usuários/funcionários

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3 (Vanilla), JavaScript |
| Backend | PHP 8+ |
| Banco de dados | MySQL 8+ |
| Conexão BD | PDO com Prepared Statements |
| Servidor local | XAMPP (Apache + MySQL) |

---

## Regras de negócio principais

- Loja funciona das **15h às 22h** — fora desse horário não aceita pedidos
- Atendimento restrito ao **distrito do Grajaú – SP**
- Login obrigatório só na **finalização do pedido** (carrinho é público)
- Pagamentos: **Pix, Débito, Crédito, Dinheiro** (sem gateway online)
- Pedido pode ser **cancelado pelo cliente** apenas se estiver `PENDENTE`
- Produto pode ser **congelado** (oculto) sem ser excluído do banco
- Máximo de **10 produtos distintos** por pedido

---

## Documentação completa na pasta LEIA-ME

| Arquivo | Conteúdo |
|---|---|
| `1_README.md` | Este arquivo — visão geral e como rodar |
| `2_ROADMAP_TCC.md` | Cronograma de 30 dias Wilson + Guilherme |
| `3_ROADMAP_CSS_HTML.md` | O que foi feito no frontend |
| `4_INTEGRACAO_JS_PHP.md` | Como o JavaScript se comunica com o PHP |
| `5_REGRAS_DE_NEGOCIO.md` | Regras e fluxo completo do sistema |
