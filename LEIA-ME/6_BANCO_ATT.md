# Banco de Dados — Evolução da Modelagem Relacional
## Banco antigo = banco do Pedro e Lohan

As relações originais foram preservadas em sua estrutura base, porém com flexibilização de obrigatoriedade (NULL) e extensão do modelo através de novas entidades e relacionamentos.
Além disso, ENUMs foram alterados e cinco novas tabelas foram criadas — destacando a inserção da relação estrutural **N:N entre produto e adicional**.

> ⚠️ Os diagramas (DER, Modelo Lógico, Esquema Relacional) precisam ser atualizados para refletir essas mudanças.









## 1. Mudanças de NULL / NOT NULL (impacto no backend)

| Coluna | Antes | Agora | Por quê |
|---|---|---|---|
| `cliente.senha` | NOT NULL | **NULL** | Suporte a autenticação via OAuth (Google) — cliente pode não ter senha local |
| `usuario.senha` | NOT NULL | **NULL** | Suporte a autenticação via OAuth (Google) — admin pode não ter senha local |
| `pedido.subtotal` | — | NULL | Campo novo, opcional |
| `pedido.custo_total` | — | NULL | Campo novo, opcional |
| `pedido.lucro` | — | NULL | Campo novo, opcional |
| vários FKs em `pedido` | obrigatórios | **NULL** | Permite maior flexibilidade, porém desloca a responsabilidade de integridade parcial para a camada de aplicação. |

> Isso muda validação no backend — PHP não pode assumir que as chaves terão valor sempre. O banco agora permite `NULL` por flexibilidade, mas a **regra de obrigatoriedade vive no backend**. Exemplo prático:
> - Pedido `LOCAL` ou `RETIRADA` → Não precisa de endereço (banco aceita `NULL`).
> - Pedido `DELIVERY` → Precisa obrigatoriamente (backend valida antes de inserir).

---

## 2. UNIQUE novos (restrições de integridade)

| Coluna | Tabela | Efeito |
|---|---|---|
| `google_subject` | `usuario` | Um Google account por usuário admin |
| `google_subject` | `cliente` | Um Google account por cliente |
| ``email` | `telefone` | email e telefone único por cliente |
| `id_cliente` | `carrinho` | **1 carrinho por cliente** (era possível ter mais de 1 antes) |
| `id_pedido` | `avaliacao` | **1 avaliação por pedido** |

---

## 3. Colunas novas em tabelas que já existiam

### `usuario`
| Coluna | Tipo |
|---|---|
| `auth_provider` | ENUM('LOCAL','GOOGLE') |
| `google_subject` | VARCHAR(191) UNIQUE |

### `cliente`
| Coluna | Tipo |
|---|---|
| `auth_provider` | ENUM('LOCAL','GOOGLE') |
| `google_subject` | VARCHAR(191) UNIQUE |

### `endereco`
| Coluna | Tipo |
|---|---|
| `ponto_referencia` | VARCHAR(120) |

### `pedido`
| Coluna | Tipo | Impacto |
|---|---|---|
| `subtotal` | DECIMAL(8,2) | Valor dos itens sem taxa de entrega |
| `custo_total` | DECIMAL(10,2) | Custo total de produção do pedido |
| `lucro` | DECIMAL(10,2) | lucro = valor_total - custo_total |
| `cpf_nota` | VARCHAR(14) | CPF do cliente para nota fiscal |
| `motivo_cancelamento` | VARCHAR(500) | Justificativa do cancelamento |
| `cancelado_por` | ENUM('CLIENTE','ADMIN') | Quem cancelou |
| `cancelado_em` | TIMESTAMP | Quando foi cancelado |

### `pagamento`
| Coluna | Tipo | Impacto |
|---|---|---|
| `valor_entregue` | DECIMAL(8,2) | Quanto o cliente entregou em dinheiro |
| `troco` | DECIMAL(8,2) | troco = valor_entregue - valor_pago |

---

## 4. Tabelas novas — colunas e relacionamentos

### `adicional` — catálogo de complementos disponíveis
| Coluna | Tipo |
|---|---|
| `id_adicional` PK | INT |
| `nome` UNIQUE | VARCHAR(80) |
| `preco` | DECIMAL(8,2) |
| `custo` | DECIMAL(8,2) NULL |
| `ativo` | BOOLEAN |

Relaciona com: `produto_adicional`, `item_pedido_adicional`, `item_carrinho_adicional`

---

### `produto_adicional` — quais adicionais cada produto aceita (N:N)
| Coluna | Tipo |
|---|---|
| `id_produto` FK | INT → `produto` |
| `id_adicional` FK | INT → `adicional` |

---

### `item_pedido_adicional` — adicionais escolhidos num item de pedido (com snapshot)
| Coluna | Tipo |
|---|---|
| `id_item_pedido_adicional` PK | INT |
| `id_item_pedido` FK | INT → `item_pedido` |
| `id_adicional` FK NULL | INT → `adicional` |
| `nome_snapshot` | VARCHAR(80) |
| `preco_unitario_snapshot` | DECIMAL(8,2) |
| `custo_unitario_snapshot` | DECIMAL(8,2) NULL |
| `quantidade` | INT |

---

### `item_carrinho_adicional` — adicionais no carrinho (com snapshot)
| Coluna | Tipo |
|---|---|
| `id_item_carrinho_adicional` PK | INT |
| `id_item_carrinho` FK | INT → `item_carrinho` |
| `id_adicional` FK NULL | INT → `adicional` |
| `nome_snapshot` | VARCHAR(80) |
| `preco_unitario_snapshot` | DECIMAL(8,2) |
| `custo_unitario_snapshot` | DECIMAL(8,2) NULL |
| `quantidade` | INT |

---

### `config_loja` — configurações da loja (singleton — sempre 1 linha, id = 1)
| Coluna | Tipo |
|---|---|
| `id_config` PK | TINYINT (sempre = 1) |
| `hora_abre` | TIME |
| `hora_fecha` | TIME |
| `whatsapp` | VARCHAR(20) NULL |
| `taxa_entrega_padrao` | DECIMAL(8,2) |

Tabela sem relacionamentos diretos (independente) — lida por qualquer parte do sistema. Caracteriza uma entidade singleton controlada por regra de aplicação (não por constraint de banco). A restrição é controlada pela aplicação por simplicidade, embora pudesse ser reforçada com constraints adicionais ou triggers no banco.

**Como o singleton é garantido na prática:**
- A PK `id_config` é fixa em `1` (definida pelo `DEFAULT 1` e pelo seed)
- O `INSERT` do seed usa `id_config = 1` explicitamente
- A aplicação nunca faz INSERT nessa tabela — sempre UPDATE
- Para ler: `SELECT * FROM config_loja WHERE id_config = 1`

---

## 5. Conceito de Snapshot (importante para TCC)

Nas tabelas `item_pedido_adicional` e `item_carrinho_adicional`, os campos `nome_snapshot` e `preco_unitario_snapshot` guardam uma **cópia** do nome e preço do adicional no momento da venda.

**Por quê isso importa:** se o dono mudar o preço ou nome de um adicional no futuro, os pedidos antigos continuam mostrando os valores corretos da época da compra. Sem o snapshot, o histórico seria corrompido automaticamente. Isso preserva a consistência histórica dos dados transacionais. Essa abordagem evita anomalias de atualização e garante independência estrutural entre dados transacionais e dados de catálogo.

**Detalhe crítico:** o FK `id_adicional` pode ser `NULL` nessas tabelas. Isso garante que mesmo se o adicional for **deletado** do sistema, o histórico continua válido — os dados do snapshot são autossuficientes e não dependem da existência do registro original.

---

## 6. Resumo do que o banco evoluiu

| Antes (banco Pedro/Lohan) | Agora |
|---|---|
| CRUD simples | Sistema transacional com controle financeiro e rastreabilidade |
| Sem controle de lucro | Rastreio financeiro completo (subtotal, custo, lucro) |
| Cancelamento genérico | Cancelamento separado por quem cancelou e quando |
| Sem adicionais | Modelagem N:N produto ↔ adicional |
| Histórico vulnerável a mudanças | Histórico imutável via snapshot |
| Sem login social | Suporte a Google OAuth (auth_provider + google_subject) |
| Sem configuração da loja | config_loja singleton no banco |
| 1 tipo de cancelamento | 2 tipos: CANCELADO_CLIENTE e CANCELADO_LOJA |
