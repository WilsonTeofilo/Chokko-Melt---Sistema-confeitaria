# 🍫 Chokko Melt — Sistema de Gestão de Pedidos para Confeitaria

<p align="center">
  <img src="https://img.shields.io/badge/TCC-Desenvolvimento_de_Sistemas-3b2313?style=for-the-badge" alt="TCC TADS">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Version">
  <img src="https://img.shields.io/badge/HTML5_&_CSS3-Vanilla-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5 & CSS3">
</p>

---

## 👥 Integrantes do Grupo
* **Wilson Teofilo** — Desenvolvimento Back-end, Engenharia de Banco de Dados, Regras de Negócio e Segurança.
* **Guilherme** — Desenvolvimento Front-end, Design UX/UI, Prototipação e Integração Visual.

---

## 📌 Sobre o Projeto (A Problemática e a Solução)

### ⚠️ A Problemática
Pequenos comércios de alimentação e confeitarias artesanais enfrentam sérios desafios em sua operação diária:
1. **Margem de Lucro Fantasma**: Dificuldade em precificar produtos que possuem adicionais customizáveis. Sem um controle de custo de insumos integrado à venda, o comerciante muitas vezes tem prejuízo sem perceber.
2. **Dependência e Taxas Altas**: Plataformas de entrega tradicionais (como iFood) cobram taxas que variam de 12% a 25% por transação, reduzindo drasticamente a viabilidade financeira do pequeno negócio.
3. **Erros Operacionais**: Pedidos anotados manualmente em papel ou via aplicativos de mensagens geram atrasos, erros de montagem e falhas na comunicação com o cliente.

### 💡 A Solução: Chokko Melt
A **Chokko Melt** é uma plataforma própria e integrada de e-commerce e gestão de confeitaria, criada especificamente para resolver essas dores de forma autônoma e inteligente:
* **Interface do Cliente**: Cardápio responsivo projetado com metodologia *Mobile First*, permitindo montagem personalizada de produtos com acompanhamentos/adicionais e acompanhamento de status em tempo real.
* **Painel Administrativo**: Painel de gerenciamento completo com controle de fila de pedidos por status (Pendente ➡️ Preparo ➡️ Despacho/Retirada ➡️ Entrega ➡️ Finalização/Estorno).
* **Módulo Financeiro Real**: Relatórios automáticos de faturamento bruto, custos acumulados e **lucro líquido real**, baseados no preço de custo de compra de cada insumo fixado no momento do pedido.

---

## 🚀 Funcionalidades de Destaque

### 📱 Área do Cliente (UX Focada em Conversão)
* **Customização de Produtos**: Seleção dinâmica de adicionais com cálculo de preço atualizado em tempo real antes de enviar à sacola.
* **Checkout Seguro com Validação de Troco**: Algoritmo robusto de validação de troco em dinheiro (tanto no front quanto no back-end) que impede que o cliente informe valores menores que o total do pedido ou envie dados inválidos/em branco.
* **Linha do Tempo em Tempo Real**: Linha do tempo visual exibindo o horário exato (`data/hora`) em que o pedido foi aceito, despachado e entregue.

### 🖥️ Painel Admin (Controle Operacional Total)
* **Notificação Sonora de Novos Pedidos**: Emissão automática de alerta sonoro (`notification fah.mp3`) no painel administrativo ao receber novos pedidos, reduzindo o tempo de resposta da cozinha.
* **Impressão de Comanda Térmica (Sem Cortes)**: Geração de cupom térmico otimizado (para bobinas de 58mm e 80mm) com margens de proteção lateral para garantir legibilidade completa em impressoras físicas.
* **Estorno vs. Cancelamento**: Diferenciação semântica da ação de cancelamento. Pedidos ativos são cancelados; pedidos já concluídos/entregues recebem a ação de **Estorno**, com alertas e modais customizados.
* **Gestão de Produtos e Acompanhamentos**: Cadastro de produtos por categoria, com opção de "Congelar" o produto (ocultando-o na vitrine do cliente sem excluí-lo dos relatórios financeiros).
* **Controle Granular de Permissões**: Cadastro de funcionários com níveis de acesso customizados por módulo.

---

## 🛠️ Tecnologias Utilizadas

* **Front-end**: HTML5 semântico, CSS3 Vanilla (com abordagem responsiva Mobile-First), JavaScript (Vanilla ES6).
* **Back-end**: PHP 8.2+ (estruturado com controle robusto de sessões, transações síncronas e segurança).
* **Banco de Dados**: MySQL 8.0+ (composto por 19 tabelas relacionais).
* **Conexão**: PDO (PHP Data Objects) utilizando *Prepared Statements* para proteção total contra vulnerabilidades.

---

## 📂 Estrutura de Diretórios

```bash
ChokkoSemIA/
├── admin/              # Painel administrativo
│   ├── assets/         # Estilos (CSS), scripts (JS) e áudio de notificação
│   ├── src/            # Controladores PHP (status do pedido, check de novos, autenticação)
│   ├── index.php       # Dashboard de pedidos
│   ├── produtos.php    # CRUD de produtos e adicionais
│   ├── pedidos.php     # Histórico de pedidos e status
│   ├── usuarios.php    # Gestão de usuários/funcionários
│   └── financeiro.php  # Painel de extrato financeiro e faturamento
│
├── user/               # Interface do cliente (vitrine)
│   ├── assets/         # CSS e JS do cliente (validador de troco, carrinho)
│   ├── src/            # Ações de gravação (carrinho_acao, pedido_acao, auth, logout)
│   ├── index.php       # Cardápio principal
│   ├── carrinho.php    # Sacola de compras
│   ├── finalizarPedido.php # Tela de fechamento de pedido (checkout)
│   ├── detalhes_pedido.php # Linha do tempo e detalhes do pedido
│   └── perfil.php      # Edição de endereço e dados pessoais
│
├── classes/            # Classes compartilhadas (Auth, Carrinho, Endereco, Produto)
├── includes/           # Cabeçalhos e rodapés estruturais (sessões e guards de acesso)
├── config/             # Configuração global de conexão com o banco de dados (PDO)
├── database/           # Schema SQL de instalação do banco de dados
└── LEIA-ME/            # Relatórios adicionais de TCC e histórico de atualizações
```

---

## 🛡️ Boas Práticas de Engenharia e Segurança
1. **Segurança de Entrada (Anti SQL-Injection)**: Todas as consultas de escrita e leitura utilizam parâmetros sanitizados e *prepared statements* via PDO.
2. **Defesa do Back-end**: O sistema valida e re-sanitiza todos os valores de pagamento e regras de checkout diretamente no servidor, impedindo bypasses causados por desativação de JavaScript ou modificação de HTML no navegador.
3. **Integridade Referencial Financeira (Snapshot)**: Preços e custos de produtos e adicionais são duplicados como cópia de auditoria na tabela do pedido. A exclusão de clientes/produtos não apaga as métricas e dados fiscais do financeiro (`LEFT JOIN`).
4. **BCrypt Hashing**: Todas as senhas de clientes e administradores são criptografadas no banco de dados usando o algoritmo robusto `PASSWORD_BCRYPT`.

---

## 🔧 Como Rodar o Projeto Localmente

### Pré-requisitos
* Ter o **XAMPP** (ou ambiente similar contendo Apache + MySQL + PHP 8.2+) instalado.

### Passo a Passo

1. **Clonar o Repositório**:
   Coloque a pasta do projeto dentro do diretório `htdocs` do seu XAMPP:
   ```bash
   C:\xampp\htdocs\ChokkoSemIA
   ```

2. **Iniciar os Serviços**:
   Abra o painel de controle do XAMPP e ative os módulos **Apache** e **MySQL**.

3. **Importar o Banco de Dados**:
   * Acesse o phpMyAdmin pelo navegador: `http://localhost/phpmyadmin/`
   * Crie uma nova base de dados chamada **`chokko_melt`**.
   * Vá na aba **Importar** e selecione o arquivo SQL localizado em:
     ```bash
     C:\xampp\htdocs\ChokkoSemIA\database\chokko_melt.sql
     ```
   * Clique em executar. O banco de dados de 19 tabelas será criado e populado.

4. **Configurar Conexão**:
   Caso o seu MySQL local possua usuário ou senha diferentes do padrão (usuário `root` e senha vazia), edite o arquivo de configuração:
   `config/config.php`

5. **Acessar o Sistema**:
   * **Área do Cliente**: `http://localhost/ChokkoSemIA/`
   * **Área do Admin (Login)**: `http://localhost/ChokkoSemIA/user/login.php`

### 🔑 Credenciais do Administrador Inicial:
* **E-mail**: `admin@chokkomelt.local`
* **Senha**: `admin123`
