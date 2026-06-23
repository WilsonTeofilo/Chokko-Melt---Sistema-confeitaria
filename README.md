# 🍫 Chokko Melt — Sistema de Gestão Financeira e Delivery para MEIs

<p align="center">
  <img src="https://img.shields.io/badge/ETEC-Irm%C3%A3_Agostina-3b2313?style=for-the-badge" alt="ETEC Irmã Agostina">
  <img src="https://img.shields.io/badge/TCC-Desenvolvimento_de_Sistemas-e7a1b0?style=for-the-badge" alt="Curso Técnico">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Version">
</p>

---

## 👥 Autores (Integrantes do Grupo)
* **Guilherme Kawan Santos Ferreira**
* **Guilherme Lopes Vieira**
* **Gustavo Fonseca Ribeiro**
* **Homero Duarte Linakis**
* **Pedro Matos Anselmo**
* **Lohan Vieira Monte**
* **Wilson Teofilo De Souza**

**Orientador:** Prof. Marco Antonio Royo Felippe

---

## 📄 Resumo (Abstract)

### 🇧🇷 Resumo
O trabalho apresenta um sistema de gestão financeira e delivery desenvolvido para Microempreendedores Individuais (MEIs), utilizando como estudo de caso uma confeitaria cuja principal fonte de receita provém de um e-commerce focado em entregas. A pesquisa aborda a problemática da elevada taxa de mortalidade precoce de MEIs no Brasil, cenário frequentemente associado à ausência de planejamento, desorganização operacional e falta de controle sobre o fluxo de caixa. O objetivo do projeto consiste em oferecer uma solução web integrada capaz de centralizar o fluxo de pedidos, automatizar relatórios financeiros e mitigar a sobrecarga gerencial do empreendedor. A metodologia baseia-se no ciclo de desenvolvimento de software, resultando em uma plataforma dividida entre os módulos do cliente e do administrador. As funcionalidades implementadas abrangem o acompanhamento de pedidos em tempo real, categorização de produtos, gerenciamento dinâmico de cardápio e geração automatizada de demonstrativos financeiros estruturados por métodos de pagamento. Como resultados obtidos, projeta-se a otimização da eficiência no atendimento, a redução drástica de falhas logísticas e um controle rigoroso dos recursos financeiros do estabelecimento. Conclui-se que a solução tecnológica desenvolvida contribui diretamente para a organização sistêmica e a sustentabilidade do negócio, promovendo autonomia ao consumidor e uma gestão administrativa escalável e segura.

**Palavras-chave:** Gestão Financeira; Serviços de Delivery; Microempreendedor Individual; Automação; Sistemas Web.

### 🇺🇸 Abstract
This study presents a financial management and delivery system developed for Sole Microentrepreneurs (MEIs), using a confectionery as a case study whose main revenue stream comes from a delivery-focused e-commerce. The research addresses the issue of the high premature mortality rate of MEIs in Brazil, a scenario frequently associated with the lack of planning, operational disorganization, and lack of control over cash flow. The objective of the project is to offer an integrated web solution capable of centralizing order workflow, automating financial reports, and mitigating the entrepreneur's managerial overload. The methodology is based on the software development lifecycle, resulting in a platform divided into client and administrator modules. The implemented features include real-time order tracking, product categorization, dynamic menu management, and automated generation of financial statements structured by payment methods. As results, it projects the optimization of customer service efficiency, a drastic reduction in logistical failures, and a rigorous control of the establishment's financial resources. It concludes that the developed technological solution contributes directly to the systemic organization and sustainability of the business, promoting consumer autonomy and a scalable and secure administrative management.

**Keywords:** Financial Management; Delivery Services; Sole Microentrepreneur; Automation; Web Systems.

---

## 📌 1. Introdução e Contextualização do Tema

O cenário empreendedor no Brasil passou por profundas transformações com a consolidação da figura jurídica do Microempreendedor Individual (MEI). Segundo dados oficiais do Ministério do Empreendedorismo, da Microempresa e da Empresa de Pequeno Porte (2024), o país ultrapassou a marca histórica de 15 milhões de registros ativos, tornando-se uma ferramenta crucial para a distribuição de renda e formalização do trabalho autônomo. Dentre as diversas atividades econômicas permitidas nesse regime, o setor de alimentação e bebidas destaca-se como um dos mais expressivos em volume de novos negócios, impulsionado pela facilidade inicial de produção caseira e comercialização direta.

No entanto, o ambiente de negócios para o microempreendedor é altamente competitivo e complexo. O perfil do MEI no setor de alimentação, frequentemente mapeado por estudos do SEBRAE (2023), revela que a grande maioria das iniciativas nasce por necessidade ou forte aptidão técnica do profissional em confeitaria ou panificação, mas carece de formação prévia em gestão de negócios. Essa característica mercadológica cria uma lacuna operacional, onde o tempo do empreendedor é absorvido quase em sua totalidade pela produção e pelo atendimento ao cliente, deixando em segundo plano o monitoramento administrativo e o controle do fluxo de caixa.

---

## ⚠️ 2. O Problema de Pesquisa

Apesar do expressivo contingente de mais de 15 milhões de MEIs formalizados no Brasil, o amadorismo na gestão administrativa e financeira ainda impera na rotina das microempresas. Levantamentos do SEBRAE indicam que a falta de ferramentas tecnológicas adequadas para o acompanhamento diário do capital de giro e custos operacionais é um dos fatores determinantes para que um terço dessas empresas feche as portas precocemente.

No segmento alimentício de confeitaria e delivery, a ausência de centralização de dados agrava a situação devido à natureza perecível dos insumos e à necessidade de respostas rápidas ao consumidor. Processos descentralizados geram gargalos que comprometem a experiência do usuário (UX) e impossibilitam o cálculo preciso do lucro líquido, uma vez que taxas de entrega, snapshots de custos adicionais e variações de preço dos insumos raramente são computados de forma integrada. Além disso, a incapacidade de rastreabilidade de dados impede que o empreendedor atenda de pronto às demandas por compliance fiscal, como o registro de CPF em notas ou controle de faturamento anual para permanência no regime simplificado do MEI.

**Pergunta-Problema:** *De que forma a arquitetura de um sistema web integrado de gestão financeira e delivery, projetado com persistência de dados escalável e suporte a parâmetros fiscais, pode otimizar a eficiência operacional e garantir o controle rigoroso do fluxo de caixa para microempreendedores individuais do ramo de confeitaria?*

---

## 🎯 3. Objetivos do Projeto

### 3.1 Objetivo Geral
Desenvolver um sistema de gestão financeira e delivery voltado para microempreendedores individuais (MEIs), com foco na organização de pedidos, automação de processos administrativos e comerciais e permitir o controle financeiro centralizado, tendo como estudo de caso a confeitaria **Chokko Melt**.

### 3.2 Objetivos Específicos
* Analisar as principais dificuldades administrativas e financeiras enfrentadas por microempreendedores individuais (MEIs) no ramo de confeitaria;
* Levantar e documentar os requisitos funcionais e não funcionais necessários para o desenvolvimento de um sistema web integrado;
* Modelar e projetar a arquitetura do sistema e a estrutura do banco de dados relacional, garantindo a consistência e a escalabilidade dos dados operacionais;
* Desenvolver os módulos do cliente e do administrador, englobando funcionalidades de controle de pedidos, gerenciamento dinâmico de cardápio e acompanhamento de status em tempo real;
* Implementar um módulo de gestão financeira capaz de automatizar relatórios de faturamento bruto, custos operacionais e margem de lucro líquido real segmentados por métodos de pagamento;
* Avaliar a eficácia do sistema Chokko Melt no ambiente de estudo de caso, identificando a redução de falhas operacionais e a contribuição da plataforma para a sustentabilidade do negócio.

---

## 💡 4. Justificativa

O crescimento dos MEIs no Brasil consolidou-se como um dos principais motores para a formalização jurídica e geração de renda. Segundo dados do Ministério do Empreendedorismo (2024), o país ultrapassou a marca de 15 milhões de registros ativos, representando mais de 56% do total de empresas abertas no território nacional. Desse montante, a fabricação de produtos de panificação e confeitaria (CNAE 5612-1/00) figura constantemente entre as dez atividades econômicas mais registradas.

No entanto, cerca de 29% das MEIs encerram suas atividades antes de completarem cinco anos de existência. Estudos do SEBRAE (2023) revelam que o fechamento precoce decorre da ausência de planejamento prévio, desorganização administrativa, falta de controle automatizado sobre o fluxo de caixa e mistificação do lucro real versus custo de produção.

No modelo de delivery, a gestão manual (por cadernos ou aplicativos de mensagens) intensifica as falhas operacionais, provocando duplicidade ou perda de pedidos, erros de precificação, atrasos na logística e vulnerabilidade na segurança dos dados. O desenvolvimento da plataforma **Chokko Melt** substitui processos empíricos por uma infraestrutura digital escalável, utilizando tecnologias modernas de banco de dados para mitigar erros operacionais, otimizar fluxos de atendimento e fornecer relatórios de rentabilidade real com base em snapshots de custos de compra históricos.

---

## ⚙️ 5. Engenharia de Requisitos

### 5.1 Requisitos Funcionais (Cliente)
1. **Visualização de cardápio**: Exibição de produtos organizados por categorias com imagem, descrição e valor.
2. **Gestão de carrinho**: Permissão para adicionar, remover, alterar quantidades e limpar a sacola.
3. **Cadastro e autenticação**: Cadastro de clientes (identificador único via telefone) e login obrigatório apenas no fechamento do pedido.
4. **Personalização do Pedido**: Escolha de adicionais/acompanhamentos e inserção de observações específicas por item.
5. **Acompanhamento de Status**: Visualização em tempo real da linha do tempo do pedido (Pendente, Em Produção, Pronto/Saiu para Entrega, Concluído).
6. **Seleção de entrega e pagamento**: Opção de entrega (delivery), consumo local ou retirada rápida, seguida da escolha de pagamento (Pix, Cartão de Crédito/Débito, Dinheiro com troco).

### 5.2 Requisitos Não Funcionais (Cliente)
1. **Responsividade (Mobile First)**: Interface do cliente projetada prioritariamente para dispositivos móveis.
2. **Disponibilidade Temporal**: Restrição na aceitação de pedidos fora do horário de funcionamento (15h às 22h).
3. **Restrição Geográfica**: Cadastro de endereço e entregas limitados exclusivamente ao distrito do Grajaú - SP.
4. **Persistência de Dados**: Carrinho mantido após atualização de página ou após a autenticação do usuário.
5. **Segurança**: Utilização de Prepared Statements (PDO) contra ataques de SQL Injection e gestão segura de sessões do usuário.
6. **Usabilidade (UX/UI)**: Seguir a paleta de cores institucional (Marrom `#4E342E`, Rosa `#E7A1B0`, Branco Quente `#FAF7F5`) e tipografias (Lily Script One e Segoe UI).

### 5.3 Requisitos Funcionais (Admin)
1. **Gestão de Fila de Pedidos**: Ações administrativas para aceitar, recusar (com justificativa) e atualizar status.
2. **Impressão de Comanda**: Geração automática de comanda para impressão térmica (80mm/58mm) com margens de proteção lateral contra cortes de papel.
3. **Gerenciamento de Cardápio (CRUD)**: Cadastrar, editar, excluir ou "congelar" (ocultar da vitrine sem perder histórico financeiro) produtos e acompanhamentos.
4. **Relatório Financeiro**: Demonstrativo automatizado de faturamento bruto, custos operacionais e margem de lucro líquido real por dia, semana ou períodos customizados.
5. **Controle de Acesso**: Níveis de permissões granulares de administrador e funcionário.

---

## 📂 6. Estrutura de Diretórios do Sistema

```bash
ChokkoSemIA/
├── admin/              # Módulo Administrativo (Fila de pedidos, Financeiro, Usuários)
│   ├── assets/         # CSS do painel, scripts de controle e alertas sonoros
│   ├── src/            # Controladores de status de pedido e checagens Fetch AJAX
│   ├── index.php       # Dashboard operacional de pedidos
│   ├── produtos.php    # CRUD de produtos e adicionais
│   ├── pedidos.php     # Histórico estruturado de pedidos
│   ├── usuarios.php    # Gestão de funcionários e privilégios
│   └── financeiro.php  # Demonstrativo financeiro de faturamento e lucro líquido
│
├── user/               # Módulo do Cliente (Vitrine e Checkout)
│   ├── assets/         # CSS e JS do cliente (validação de troco, carrinho responsivo)
│   ├── src/            # Ações de gravação, persistência de carrinho, login e logout
│   ├── index.php       # Cardápio principal
│   ├── carrinho.php    # Sacola de compras
│   ├── finalizarPedido.php # Tela de checkout (delivery/retirada/local)
│   ├── detalhes_pedido.php # Acompanhamento com linha do tempo e horas exatas
│   └── perfil.php      # Gerenciamento de endereços e dados pessoais
│
├── classes/            # Classes encapsuladas (Auth, Carrinho, Endereco, Produto)
├── config/             # Configuração global de conexão PDO com o MySQL
├── database/           # Script .sql do banco de dados (19 tabelas relacionais)
└── LEIA-ME/            # Relatórios acadêmicos do TCC e históricos de alterações
```

---

## 🛠️ Tecnologias Adotadas

* **Frontend**: HTML5 Semântico, CSS3 (Vanilla com Flexbox e Grid) e JavaScript ES6+ (Fetch API/AJAX).
* **Backend**: PHP 8.2+ (Estruturado, controle de sessões e transações transacionais).
* **Banco de Dados**: MySQL 8.0+ (InnoDB Engine para integridade referencial com `ON DELETE SET NULL` / `ON DELETE CASCADE`).
* **Segurança**: Criptografia de senhas via `PASSWORD_BCRYPT` e Prepared Statements nativos do PDO.

---

## 💾 7. Como Configurar e Executar Localmente

### Requisitos
* XAMPP (Apache + MySQL + PHP 8.2+) instalado.

### Configuração
1. **Copiar os Arquivos**: Mova a pasta do projeto para o diretório `htdocs` do seu XAMPP:
   ```bash
   C:\xampp\htdocs\ChokkoSemIA
   ```
2. **Iniciar o Apache e MySQL**: Abra o painel de controle do XAMPP e ative ambos os serviços.
3. **Instalar o Banco de Dados**:
   * Acesse `http://localhost/phpmyadmin/` no navegador.
   * Crie uma base de dados com o nome **`chokko_melt`**.
   * Vá em **Importar** e escolha o arquivo SQL localizado em:
     `database/chokko_melt.sql`
   * Execute a importação.
4. **Configurar Conexão**:
   Caso necessário, edite o arquivo `config/config.php` informando as credenciais locais do seu servidor.
5. **Acessar o Painel**:
   * Vitrine do cliente: `http://localhost/ChokkoSemIA/`
   * Tela de Login (Admin/Cliente): `http://localhost/ChokkoSemIA/user/login.php`

### 🔑 Credenciais do Administrador Inicial:
* **E-mail**: `admin@chokkomelt.local`
* **Senha**: `admin123`
