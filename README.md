# Fintech Wallet — Desafio Técnico Pleno

Carteira digital pessoal com depósitos, saques e histórico de transações. Desenvolvida com Laravel 13 (API REST) e Vue.js 3 (SPA).

- **Repositório:** https://github.com/gui-vncarvalho/fintech-wallet-pleno
- **Deploy Público:** https://guilherme-vila-fintech-production.up.railway.app

---

## Decisões Técnicas

- **Service Layer:** toda a lógica de depósito e saque está isolada em `WalletService`, mantendo os controllers finos e responsáveis apenas por receber a requisição e retornar a resposta.
- **Integridade financeira:** valores são armazenados em centavos (inteiro) para eliminar erros de ponto flutuante. Toda conversão reais ↔ centavos passa pelo `Money` helper (`App\Support\Money`), centralizando a lógica em um único lugar.
- **Atomicidade:** depósito e saque executam dentro de `DB::transaction` — o incremento/decremento do saldo e o registro da transação acontecem juntos ou não acontecem. Em caso de falha, o banco reverte automaticamente.
- **Auditabilidade:** cada transação grava o `balance_after` no momento da operação, formando um histórico imutável e auditável independente do saldo atual.
- **Proteção contra submissões duplicadas:** as rotas de depósito e saque têm throttle de 10 req/min por usuário, barrando double-submits acidentais e disparos automatizados. É uma solução simples mas suficiente para a proposta desse projeto; em produção real, acredito que a abordagem correta seria usarmos chaves de idempotência, pra garantirmos que a mesma operação nunca seja processada duas vezes mesmo em cenários de retry.
- **Autenticação:** Laravel Sanctum com tokens de API (stateless), sem uso de sessão/cookie.
- **Validação:** Form Requests para todas as entradas, com mensagens de erro padronizadas.
- **Frontend:** Vue 3 com Composition API, Pinia para estado global e Vue Router para navegação. Axios configurado com interceptor para injetar o token e redirecionar em caso de 401.
- **Deploy:** API containerizada via Docker (PHP 8.4 Alpine) e hospedada no Railway junto com PostgreSQL gerenciado. Frontend servido como SPA estática.

---

## Pré-requisitos (local)

| Ferramenta   | Versão mínima |
|--------------|---------------|
| PHP          | 8.4           |
| Composer     | 2.x           |
| Node.js      | 20.x          |
| npm          | 10.x          |
| PostgreSQL   | 15            |

---

## Rodando localmente

### 1. Clone o repositório

```bash
git clone https://github.com/gui-vncarvalho/fintech-wallet-pleno.git
cd fintech-wallet-pleno
```

### 2. Backend (API)

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
```

Configure o `.env` com suas credenciais do banco:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=wallet
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Rode as migrations e os seeders:

```bash
php artisan migrate
php artisan db:seed
```

Inicie o servidor:

```bash
php artisan serve
```

A API ficará disponível por padrão em `http://localhost:8000`.

### 3. Frontend (Panel)

```bash
cd ../panel
npm install
```

Crie um arquivo `.env` na pasta `panel`:

```env
VITE_API_URL=http://localhost:8000/api
```

Inicie o servidor de desenvolvimento:

```bash
npm run dev
```

O frontend ficará disponível por padrão em `http://localhost:5173`.

---

## Credenciais do usuário seed

| Campo | Valor             |
|-------|-------------------|
| Email | teste@wallet.com  |
| Senha | password          |

O usuário já possui transações de exemplo para demonstrar o dashboard e o histórico de forma mais prática.

---

## Testes

```bash
cd api
php artisan test
```
## Principais cenários cobertos

- Depósito com sucesso
- Saque com saldo suficiente
- Bloqueio de saque sem saldo
- Persistência correta do saldo
- Registro consistente do histórico
- Validação de valores inválidos
