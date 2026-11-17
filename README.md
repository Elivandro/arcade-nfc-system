# Sistema Arcade NFC

Projeto de controle de créditos para máquinas de arcade. O firmware lê o UID de um cartão NFC com um ESP32-S3 e um PN532, consulta a API Laravel por Wi-Fi e, quando a API aprova a partida, aciona um relé.

## Por onde começar

1. Prepare e inicie a API seguindo [`Guia API Laravel`](./arcade-nfc-api/README.md).
2. Configure e grave o firmware seguindo [`Guia firmware ESP32`](./arcade-nfc-esp/README.md).

O fluxo recomendado é iniciar o banco e a API, cadastrar um token de máquina e um cartão de teste, compilar e gravar o ESP32 e então aproximar o cartão com o monitor serial aberto.

## Componentes principais

- Backend: Laravel 13, PHP 8.3+ e banco MySQL quando executado com Sail.
- Firmware: ESP32-S3 DevKitC-1, compilado com PlatformIO.
- Leitor: PN532 em SPI.
- Atuador: módulo relé 3v no GPIO 5.
- Display: OLED SSD1306 em I²C. (opcional)

## Escopo e limitações atuais

O firmware envia o UID lido e um token da máquina. A API autentica a máquina, procura o cartão pelo UID, debita o custo da partida e registra a transação. O firmware aciona o relé apenas para uma resposta HTTP 200 com `status: "APPROVED"`.

No firmware atual a validação do cartão é baseada no UID. O modo TLS do firmware também não verifica o certificado do servidor. Consulte os guias antes de usar o protótipo em produção.

## Segurança

Não publique arquivos `.env`, tokens de máquina, senhas de Wi-Fi ou credenciais do banco. Use HTTPS e configure validação de certificado antes de expor o sistema a redes não confiáveis.
