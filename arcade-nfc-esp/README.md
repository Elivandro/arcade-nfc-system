# Firmware ESP32-S3

Firmware PlatformIO para ler cartões com PN532, consultar a API Laravel e acionar um relé quando a partida é aprovada.

## Hardware e pinagem usada no código

- ESP32-S3-N16R8
- PN532
- Rele 3v
- Display 0.91" (usado para feedback, completamente opcional ou substituivel)

O projeto está configurado para ESP32-S3 DevKitC-1. O PN532 usa SPI e o OLED SSD1306 usa I²C:

| Módulo | Pino do módulo | GPIO / alimentação do ESP32-S3 |
|---|---|---|
| PN532 | SCK | GPIO 12 |
| PN532 | MISO | GPIO 13 |
| PN532 | MOSI | GPIO 11 |
| PN532 | NSS / SS | GPIO 10 |
| PN532 | GND | GND comum |
| PN532 | VCC | Conforme a tensão aceita pelo seu módulo |
| OLED SSD1306 | SDA | GPIO 17 |
| OLED SSD1306 | SCL | GPIO 18 |
| OLED SSD1306 | VCC / GND | Conforme o módulo / GND comum |
| Relé | IN | GPIO 5 |
| Relé | VCC / GND | Fonte adequada ao módulo / GND comum |
| NeoPixel integrado | Dados | GPIO 48 |

Configure as chaves do PN532 no modo SPI de acordo com a tabela impressa no módulo específico. Confira a pinagem da sua placa antes de energizar: módulos PN532 e placas ESP32 podem variar. Não alimente a bobina do relé diretamente por um GPIO.

O sinal do relé no firmware é **ativo em nível baixo**: o código coloca GPIO 5 em `LOW` por 200 ms e depois retorna o pino para `INPUT_PULLUP`. Use a entrada `IN` do módulo de relé e, para uma carga que deva ficar desligada em repouso, normalmente os contatos `COM` e `NO` (confirme a identificação no seu módulo).

## Configurar Wi-Fi e API

O script `load_env.py` lê as variáveis do ambiente ou de `.env` durante a compilação. Copie o modelo e preencha as quatro variáveis:

```sh
cp .env.example .env
```

```dotenv
WIFI_SSID="nome-da-rede-2.4GHz"
WIFI_PASS="senha-da-rede"
API_URL="https://endereco-de-acesso"
MACHINE_TOKEN="token-configurado-no-Laravel"
```

- O ESP32-S3 requer uma rede Wi-Fi de 2,4 GHz.
- `API_URL` é a URL base para acessar o projeto laravel.
- API local, `localhost` não funciona na placa.
- `MACHINE_TOKEN` deve corresponder a `arcade_machines.api_token` e não deve conter `Bearer`.
- O `.env` é ignorado pelo Git. Nunca o publique ou compartilhe.
- Variáveis exportadas no ambiente do processo de compilação têm precedência sobre as do arquivo `.env`.

Siga [`../arcade-nfc-api/README.md`](../arcade-nfc-api/README.md) para iniciar a API e preparar uma máquina e um cartão de teste.

## Compilar, gravar e abrir o monitor serial

1. Instale o **VS Code** e a extensão **PlatformIO IDE**, ou instale o **PlatformIO CLI**.
2. Abra a pasta `arcade-nfc-esp` no VS Code.
3. Conecte o ESP32-S3 por um cabo USB-C que suporte dados e selecione a porta serial correta.
4. Compile e grave

Com o **PlatformIO CLI**

```sh
pio run -e esp32-s3-devkitc-1
pio run -e esp32-s3-devkitc-1 -t upload
pio device monitor -b 115200
```

No PlatformIO do VS Code, as ações equivalentes são **Build** (✓), **Upload** (→) e **Monitor** (plugue/terminal). A primeira compilação baixa automaticamente as bibliotecas listadas em `platformio.ini`.

Se a porta não for detectada, confira o cabo USB, a porta selecionada e as permissões do sistema. Se o upload falhar, coloque a placa em modo de gravação conforme o modelo do ESP32-S3 e tente novamente.

## Funcionamento e diagnóstico

Ao detectar um cartão, o firmware envia primeiro o UID em hexadecimal maiúsculo para o endpoint de cadastro:

```http
Authorization: Bearer <MACHINE_TOKEN>
```

Se a API retornar HTTP 201, o cartão foi vinculado ao cliente que está aguardando cadastro. Sem cadastro pendente, a API responde HTTP 204 e o firmware encaminha a leitura para `POST /v1/arcade/validate-play`. O corpo das duas requisições é:

```json
{
    "uid":"7B19FC53"
}
```

O relé só recebe o pulso se a resposta for HTTP 200 e contiver `status: "APPROVED"`. UID inexistente/inativo, saldo insuficiente, token inválido ou erro de rede não devem liberar a partida.

Abra o monitor serial a 115200 baud. Ele informa o IP do ESP32, o estado do Wi-Fi e o resultado HTTP. Mensagens de PN532 sem resposta indicam que se deve conferir modo SPI, alimentação e pinos. Erros de Wi-Fi normalmente indicam SSID/senha, rede indisponível ou incompatibilidade com a rede de 2,4 GHz.

## Nota de segurança

O firmware atual autentica a máquina com token e identifica o cartão apenas pelo UID; O cliente HTTPS chama `setInsecure()`, que cifra a conexão mas **não valida o certificado do servidor**. Isso não deve ser considerado validação TLS segura para produção. Configure validação por CA antes de usar em uma rede não confiável.
