# API Laravel

Backend do sistema Arcade NFC. Ele autentica a máquina pelo token, valida o UID do cartão, debita o valor da partida e registra a transação.

## Requisitos

- PHP 8.3 e Composer 2 ou superior
- Node.js e npm para compilar os assets web
- Redis
- Docker para executar com Laravel Sail

## Instalação com Sail/Docker (recomendavel)
Se você estiver usando linux ou WSL basta rodar make

No terminal, a partir da raiz do repositório:

```sh
cd nfc-system-api && make
```
### [Sail alias](https://laravel.com/framework/docs/master/sail#configuring-a-shell-alias)

## Instalação manual
Instale as dependencias e compile os assets e inicie os serviços:

```sh
cd nfc-system-api && cp .env.example .env
npm install && composer install --ignore-platform-reqs
npm run build
php artisan key:generate
php artisan serve
```

## Migrações: Banco de dados e dados para testes
```sh
php artisan migrate
php artisan db:seed
```

Os dados do MySQL ficam no volume Docker `sail-mysql`. **Não use `migrate:fresh` com dados que deseja manter**, pois esse comando apaga as tabelas.

## certifique-se de configurar o redis
exemplo com sail/docker

```sh
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Preparar máquina e cartão de teste

O seeder cria máquinas e cartões de exemplo, mas os tokens e alguns dados são gerados aleatoriamente. Crie um token para uma máquina e configure o mesmo valor no `.env` do firmware:

```sh
php artisan tinker
```

Para trocar os valores basta ir no Tinker:

```php
$machine = App\Models\ArcadeMachine::query()->firstOrFail();
$machine->cost_per_play = 1.50;
$machine->api_token = Illuminate\Support\Str::random(64);
$machine->save();
$machine->api_token;
```

Copie o token exibido para `MACHINE_TOKEN` em `esp32_nfc_display/.env`. Não inclua o prefixo `Bearer`; o firmware o adiciona ao cabeçalho HTTP.

Para testar, use uma tag cujo UID esteja cadastrado como cartão ativo e que tenha saldo suficiente. Os seeders incluem cartões de exemplo; confira ou ajuste o cartão no Tinker antes do teste:

```php
$card = App\Models\Card::where('uid', '7B19FC53')->firstOrFail();
$card->status = App\Enums\CardType::ACTIVE->value;
$card->balance = 50.00;
$card->save();
```

Esse UID só funcionará se a tag física tiver exatamente esse identificador. O ESP32 lê o UID; ele não grava nem provisiona cartões.

## API usada pelo firmware

Confira a rota registrada:

```sh
php artisan route:list --path=v1/arcade
```

O firmware faz `POST /v1/arcade/validate-play` com `Content-Type: application/json`, `Authorization: Bearer <token-da-maquina>` e este corpo:

```json
{
    "uid":"7B19FC53"
}
```

Ao ler um cartão, o firmware tenta `POST /v1/arcade/register-card`. Cadastro concluído retorna HTTP 201 e `REGISTERED`; quando não há cliente aguardando, a API responde HTTP 204 e o firmware segue para `POST /v1/arcade/validate-play`. O `GET /v1/arcade/reader-mode` permanece disponível para compatibilidade com firmware anterior. Partida aprovada retorna HTTP 200 com `APPROVED` e o novo saldo. Cartão inválido/inativo retorna HTTP 404; saldo insuficiente, HTTP 402; token ausente ou não autorizado, HTTP 401 ou 403. O firmware só aciona o relé para HTTP 200 e `APPROVED`.

No painel, edite os dados do cliente, altere o status do cartão (ativo, bloqueado ou suspenso) e use **Adicionar saldo** no cartão desejado. A recarga cria uma transação do tipo `credit`, sem máquina associada. Não há exclusão física de clientes ou cartões nesse fluxo, para preservar o histórico financeiro.

## Testes e logs

```sh
php artisan test
php artisan pail
```

Os logs Laravel também ficam em `storage/logs/laravel.log`.
