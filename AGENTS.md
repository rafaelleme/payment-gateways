# AGENTS.md

Este documento define as regras e diretrizes para qualquer agente de IA (Copilot, ChatGPT, Cursor, etc.) que contribua com este repositório.

O objetivo é garantir que o código gerado respeite a arquitetura e os princípios do projeto.

---

# Visão Geral do Projeto

Este projeto é uma biblioteca PHP para integração com múltiplos gateways de pagamento.

Objetivo da biblioteca:

Fornecer uma **abstração unificada e extensível** para integração com diferentes provedores de pagamento.

A biblioteca deve ser:

- independente de framework
- extensível
- fácil de testar
- fácil de integrar

O projeto segue os princípios de:

- Domain Driven Design (DDD)
- Ports and Adapters (Hexagonal Architecture)

---

# Estrutura do Projeto

src/

Core/
Domain/
Contracts/
Entities/
Enums/
ValueObjects/

Application/
Services/

Infrastructure/
Gateways/

Support/

Laravel/

Cada camada possui responsabilidades específicas.

Agentes de IA devem respeitar essa separação.

---

# Camada de Domínio

Localização:

src/Core/Domain

A camada de domínio contém as regras de negócio centrais.

Ela não pode depender de:

- frameworks
- clientes HTTP
- bibliotecas externas
- helpers de Laravel

Conteúdo permitido no domínio:

Contracts  
Entities  
Enums  
ValueObjects

Exemplos:

Payment  
Customer  
BillingType  
Money

As entidades devem representar conceitos de negócio.

---

# Contracts

Os contratos definem os **ports** da arquitetura.

Exemplo:

PaymentGateway

Todos os gateways devem implementar esse contrato.

Exemplo de métodos esperados:

createPayment(Payment $payment)

getPayment(string $paymentId)

Os contratos pertencem ao domínio.

---

# Entidades

Entidades representam objetos de negócio com identidade.

Exemplo:

Payment

Entidades devem conter dados relevantes ao domínio e podem conter comportamento de negócio.

Evitar lógica relacionada a infraestrutura.

A entidade deve ser usada tanto para representar o comando de entrada quanto o resultado retornado pelo gateway.

Exemplo: Payment é enviado sem `id` e `status` e retornado com esses campos preenchidos.

Quando um conceito envolver múltiplas entidades relacionadas com consistência de negócio entre elas, utilizar um **Agregado** (Aggregate Root).

---

# DTOs

**DTOs são proibidos neste projeto.**

Não criar classes em pastas como `DTOs/`, `DataTransferObjects/` ou similares.

Sempre utilizar entidades do domínio ou Value Objects para transportar dados entre camadas.

Se um retorno de gateway precisar carregar informações adicionais, enriquecer a entidade existente ou criar um novo Agregado no domínio.

---

# Enums

Localização:

src/Core/Domain/Enums

Enums representam conjuntos fechados de valores nomeados do domínio.

Exemplos:

BillingType  
PaymentStatus  
SubscriptionCycle  
SubscriptionStatus

Regras:

- Usar PHP 8.1+ backed enums (`enum Foo: string`)
- Podem conter métodos de comportamento: `label()`, `isPaid()`, `isActive()`
- Devem conter método estático `fromAsaas()` (ou `from{Gateway}()`) para isolar o mapeamento de cada gateway dentro do próprio enum
- **Não misturar com Value Objects**

Diferença entre Enum e Value Object:

| Conceito | Tipo | Exemplo |
|---|---|---|
| Conjunto fechado de casos nomeados | Enum | `BillingType`, `PaymentStatus` |
| Objeto imutável com valor e comportamento | Value Object | `Money`, `CustomerId` |

---

# Value Objects

Localização:

src/Core/Domain/ValueObjects

Value Objects representam valores imutáveis do domínio que possuem identidade baseada no próprio valor.

Exemplos:

Money  
CustomerId

Regras:

- Sempre classes (`final class`), nunca enums
- Devem ser imutáveis (propriedades `readonly` ou sem setters)
- Podem conter validação no construtor
- Podem conter comportamento: `equals()`, `__toString()`
- **Não colocar enums nesta pasta**

---

# Camada de Aplicação

Localização:

src/Core/Application

Esta camada contém os **casos de uso da aplicação**.

Exemplo:

PaymentService

Responsabilidades:

- orquestrar operações
- utilizar gateways através dos contratos
- não conter lógica de infraestrutura

Os métodos da camada de aplicação recebem e retornam **entidades do domínio**.

Não utilizar DTOs, arrays associativos ou classes de resultado intermediárias.

---

# Camada de Infraestrutura

Localização:

src/Infrastructure

Contém implementações concretas dos contratos do domínio.

Exemplo:

Infrastructure/Gateways/Asaas/AsaasGateway.php

Responsabilidades:

- comunicação com APIs externas
- uso de clientes HTTP
- mapeamento entre entidades do domínio e APIs externas

Bibliotecas externas são permitidas nesta camada.

---

# Gateway Manager

Localização:

src/Support/GatewayManager.php

Responsável por:

- registrar gateways
- resolver drivers

Exemplo de uso:

$manager->driver('asaas')

O GatewayManager não deve conter lógica de negócio.

---

# Integração com Laravel

Localização:

src/Laravel

Esta camada contém código específico para integração com Laravel.

Exemplos:

ServiceProvider  
bindings no container

O código Laravel deve ficar isolado nesta pasta.

O Core da biblioteca deve permanecer independente de framework.

---

# Adicionando um novo Gateway

Para adicionar um novo gateway de pagamento:

1. Criar uma pasta em:

Infrastructure/Gateways/{Gateway}

Exemplo:

Infrastructure/Gateways/Stripe

2. Criar a implementação do contrato:

PaymentGateway

3. Registrar o gateway no GatewayManager.

---

# Qualidade de Código

Este projeto utiliza duas ferramentas obrigatórias de qualidade:

## PHPStan

Análise estática configurada no nível máximo (level 8).

Configuração em `phpstan.neon`.

A pasta `src/Laravel` é excluída da análise pois depende do framework.

Rodar:

```bash
docker compose run --rm php ./vendor/bin/phpstan analyse --memory-limit=256M
```

Regras:

- Todos os arrays devem ter tipos declarados: `array<string, mixed>`, `array<int, mixed>`, etc.
- Nenhum erro é aceitável antes de um commit.

## PHP-CS-Fixer

Formatação e padronização automática de código.

Configuração em `.php-cs-fixer.php`.

Padrão base: PSR-12 + PHP 8.1 Migration.

Regras relevantes:

- `declare(strict_types=1)` obrigatório em todos os arquivos
- Imports ordenados alfabeticamente
- Trailing comma em arrays, parâmetros e argumentos multiline
- Single quotes para strings
- Sem imports não utilizados

Verificar sem aplicar:

```bash
docker compose run --rm php ./vendor/bin/php-cs-fixer fix --dry-run --diff
```

Aplicar correções:

```bash
docker compose run --rm php ./vendor/bin/php-cs-fixer fix
```

Regra: sempre rodar o CS-Fixer antes de um commit.

---

# Ambiente de Execução

Este projeto roda em Docker. Não execute comandos como `composer`, `phpunit` ou `php` diretamente no terminal do host.

O ambiente é definido em `docker-compose.yml`.

Serviço PHP:

- nome do serviço: `php`
- nome do container: `payment-gateways.dev`
- diretório de trabalho dentro do container: `/app`

Sempre que precisar rodar comandos, utilize:

```bash
docker compose run --rm php composer <comando>
```

```bash
docker compose run --rm php ./vendor/bin/phpunit
```

```bash
docker compose run --rm php php <script.php>
```

Exemplos práticos:

Instalar dependências:

```bash
docker compose run --rm php composer install
```

Atualizar dependências:

```bash
docker compose run --rm php composer update
```

Rodar os testes:

```bash
docker compose run --rm php ./vendor/bin/phpunit
```

Rodar um teste específico:

```bash
docker compose run --rm php ./vendor/bin/phpunit --filter NomeDoTeste
```

Dump do autoload:

```bash
docker compose run --rm php composer dump-autoload
```

Regra: antes de executar qualquer comando de terminal, verificar o `docker-compose.yml` para confirmar o nome do serviço PHP correto.

---

# Convenções de Código

Agentes devem seguir estas convenções:

- PHP 8.1 ou superior
- utilizar tipagem explícita
- preferir objetos de domínio ao invés de arrays
- evitar funções estáticas quando possível
- preferir injeção de dependência via construtor
- manter Value Objects imutáveis

---

# Testes

Os testes devem validar:

- entidades do domínio
- serviços de aplicação
- gateways

Sempre que possível evitar chamadas reais a APIs externas.

Preferir mocks para clientes HTTP.

---

# Objetivo da Arquitetura

A arquitetura deve permitir:

- adicionar novos gateways facilmente
- manter o domínio independente
- facilitar testes
- evitar acoplamento com frameworks

Toda nova funcionalidade deve respeitar essas regras.