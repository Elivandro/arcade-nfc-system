#include "config.h"
#include "perifericos.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Arduino.h>

const char *ssid = WIFI_SSID;
const char *password = WIFI_PASS;
const char *apiUrl = API_URL;
const char *machineToken = MACHINE_TOKEN;
const char *apiPath = "/v1/arcade/validate-play";

namespace
{
    constexpr uint32_t WIFI_RETRY_INTERVAL_MS = 10000;
    uint32_t ultimaTentativaWifiMs = 0;

    String construirEndpoint(const char *path)
    {
        String endpoint = apiUrl;
        while (endpoint.endsWith("/"))
        {
            endpoint.remove(endpoint.length() - 1);
        }

        const String legacyPath = apiPath;
        if (endpoint.endsWith(legacyPath))
        {
            endpoint.remove(endpoint.length() - legacyPath.length());
        }

        return endpoint + path;
    }

    const char *descricaoStatusWifi(wl_status_t status)
    {
        switch (status)
        {
        case WL_NO_SSID_AVAIL:
            return "REDE NAO ENCONTRADA";
        case WL_CONNECT_FAILED:
            return "FALHA AUTENTICACAO";
        case WL_CONNECTION_LOST:
            return "CONEXAO PERDIDA";
        case WL_DISCONNECTED:
            return "DESCONECTADO";
        case WL_IDLE_STATUS:
            return "CONECTANDO";
        case WL_CONNECTED:
            return "CONECTADO";
        default:
            return "STATUS WIFI DESCONHECIDO";
        }
    }

    void iniciarTentativaWifi()
    {
        ultimaTentativaWifiMs = millis();
        Serial.printf("Conectando ao Wi-Fi (status inicial: %d)\n", WiFi.status());
        WiFi.begin(ssid, password);
    }
}

Adafruit_SSD1306 display(128, 32, &Wire, -1);
Adafruit_PN532 nfc(PN532_SCK, PN532_MISO, PN532_MOSI, PN532_SS);
Adafruit_NeoPixel pixels(NUM_PIXELS, PIN_LED, NEO_GRB + NEO_KHZ800);

void mostrarMensagem(String l1, String l2, String l3, String l4)
{
    display.clearDisplay();
    display.setCursor(0, 0);
    display.println(l1);
    if (l2 != "")
    {
        display.setCursor(0, 8);
        display.println(l2);
    }
    if (l3 != "")
    {
        display.setCursor(0, 16);
        display.println(l3);
    }
    if (l4 != "")
    {
        display.setCursor(0, 24);
        display.println(l4);
    }
    display.display();
}

void inicializarPerifericos()
{
    pinMode(pinoRele, INPUT_PULLUP);

    pixels.begin();
    pixels.setBrightness(30);
    pixels.setPixelColor(0, pixels.Color(0, 0, 255));
    pixels.show();

    Wire.begin(OLED_SDA, OLED_SCL);
    if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C))
    {
        Serial.println("Erro: Display OLED nao encontrado!");
        while (1)
            ;
    }

    display.ssd1306_command(SSD1306_SETCONTRAST);
    display.ssd1306_command(10);
    display.setTextSize(1);
    display.setTextColor(SSD1306_WHITE);

    mostrarMensagem("SISTEMA ONLINE", "INICIANDO...", "CONECTANDO WIFI", "AGUARDE...");

    WiFi.mode(WIFI_STA);
    WiFi.setAutoReconnect(true);
    WiFi.persistent(false);
    WiFi.onEvent([](WiFiEvent_t event, WiFiEventInfo_t info)
                 {
        if (event == ARDUINO_EVENT_WIFI_STA_DISCONNECTED) {
            Serial.printf("Wi-Fi desconectado. Motivo do ESP32: %u\n",
                          info.wifi_sta_disconnected.reason);
        } else if (event == ARDUINO_EVENT_WIFI_STA_GOT_IP) {
            Serial.printf("Wi-Fi conectado. IP: %s\n", WiFi.localIP().toString().c_str());
        } });

    iniciarTentativaWifi();
    if (conectarWifi(15000))
    {
        Serial.println("Wi-Fi pronto para uso.");
        mostrarMensagem("WIFI CONECTADO!", "SISTEMA ONLINE", "AGUARDANDO CARD...", "PRODUCAO");
    }
    else
    {
        Serial.printf("Wi-Fi indisponivel apos inicializacao: %s (%d)\n",
                      descricaoStatusWifi(WiFi.status()), WiFi.status());
        mostrarMensagem("WIFI INDISPONIVEL", descricaoStatusWifi(WiFi.status()),
                        "TENTANDO NOVAMENTE", "SERIAL 115200");
    }
}

bool conectarWifi(uint32_t timeoutMs)
{
    const uint32_t inicioMs = millis();

    while (WiFi.status() != WL_CONNECTED && millis() - inicioMs < timeoutMs)
    {
        if (millis() - ultimaTentativaWifiMs >= WIFI_RETRY_INTERVAL_MS)
        {
            iniciarTentativaWifi();
        }
        delay(250);
    }

    if (WiFi.status() == WL_CONNECTED)
    {
        Serial.printf("Wi-Fi OK. IP: %s | RSSI: %d dBm\n",
                      WiFi.localIP().toString().c_str(), WiFi.RSSI());
        return true;
    }

    Serial.printf("Wi-Fi ainda desconectado: %s (%d)\n",
                  descricaoStatusWifi(WiFi.status()), WiFi.status());
    return false;
}

void manterWifiConectado()
{
    if (WiFi.status() == WL_CONNECTED)
    {
        return;
    }

    if (millis() - ultimaTentativaWifiMs >= WIFI_RETRY_INTERVAL_MS)
    {
        Serial.printf("Tentando reconectar ao Wi-Fi: %s (%d)\n",
                      descricaoStatusWifi(WiFi.status()), WiFi.status());
        iniciarTentativaWifi();
    }
}

void piscarErroNFC()
{
    while (1)
    {
        pixels.setPixelColor(0, pixels.Color(255, 0, 0));
        pixels.show();
        delay(300);
        pixels.setPixelColor(0, pixels.Color(0, 0, 0));
        pixels.show();
        delay(300);
    }
}

void acionarReleComFeedback(String idString)
{
    if (conectarWifi(15000))
    {
        mostrarMensagem("NFC DETECTADO!", "ID: " + idString, "ENVIANDO UID...", "AGUARDE SSL...");

        WiFiClientSecure clienteSeguro;
        clienteSeguro.setInsecure();

        JsonDocument doc;
        doc["uid"] = idString;

        String jsonString;
        serializeJson(doc, jsonString);

        HTTPClient http;
        http.setConnectTimeout(10000);
        http.setTimeout(15000);
        http.begin(clienteSeguro, construirEndpoint("/v1/arcade/register-card"));
        http.addHeader("Content-Type", "application/json");
        http.addHeader("Authorization", "Bearer " + String(machineToken));

        Serial.printf("POST API /v1/arcade/register-card | UID: %s\n", idString.c_str());
        int httpResponseCode = http.POST(jsonString);

        if (httpResponseCode == HTTP_CODE_NO_CONTENT)
        {
            http.end();
            http.setConnectTimeout(10000);
            http.setTimeout(15000);
            http.begin(clienteSeguro, construirEndpoint(apiPath));
            http.addHeader("Content-Type", "application/json");
            http.addHeader("Authorization", "Bearer " + String(machineToken));
            Serial.printf("Sem cadastro pendente; POST API %s | UID: %s\n",
                          apiPath, idString.c_str());
            httpResponseCode = http.POST(jsonString);
        }

        if (httpResponseCode > 0)
        {
            String responseBody = http.getString();
            Serial.printf("Resposta API HTTP %d: %s\n",
                          httpResponseCode, responseBody.c_str());
            JsonDocument respostaDoc;
            DeserializationError error = deserializeJson(respostaDoc, responseBody);

            if (!error)
            {
                const char *status = respostaDoc["status"] | "";

                if (httpResponseCode == HTTP_CODE_OK && strcmp(status, "APPROVED") == 0)
                {
                    pixels.setPixelColor(0, pixels.Color(0, 255, 0));
                    pixels.show();

                    float novoSaldo = respostaDoc["new_balance"] | 0.0f;
                    mostrarMensagem("ACESSO LIBERADO", "CREDITO COLETADO", "SALDO: R$ " + String(novoSaldo, 2), "BOM JOGO!");
                    delay(1500);

                    pinMode(pinoRele, OUTPUT);
                    digitalWrite(pinoRele, LOW);
                    delay(200);
                    pinMode(pinoRele, INPUT_PULLUP);
                }
                else if (httpResponseCode == HTTP_CODE_CREATED && strcmp(status, "REGISTERED") == 0)
                {
                    pixels.setPixelColor(0, pixels.Color(0, 255, 0));
                    pixels.show();
                    mostrarMensagem("CARTAO VINCULADO", "CLIENTE CADASTRADO", "ATUALIZE O PAINEL", "");
                    delay(2500);
                }
                else if (strcmp(status, "REJECTED") == 0)
                {
                    const char *motivo = respostaDoc["message"] | "Cartao nao autorizado";
                    pixels.setPixelColor(0, pixels.Color(255, 0, 0));
                    pixels.show(); // Vermelho
                    mostrarMensagem("ACESSO NEGADO", motivo, "VERIFIQUE BALCÃO", "");
                    delay(2500);
                }
                else
                {
                    const char *motivo = respostaDoc["message"] | "Erro retornado pela API";
                    pixels.setPixelColor(0, pixels.Color(255, 255, 0));
                    pixels.show();
                    mostrarMensagem("ERRO API " + String(httpResponseCode), motivo, "", "");
                    delay(2500);
                }
            }
            else
            {
                pixels.setPixelColor(0, pixels.Color(255, 255, 0));
                pixels.show();
                Serial.printf("Resposta da API nao e JSON valido: %s\n", error.c_str());
                mostrarMensagem("ERRO RESPOSTA API", error.c_str(), "", "");
                delay(2500);
            }
        }
        else
        {
            String error = http.errorToString(httpResponseCode);
            Serial.printf("Falha HTTP %d: %s\n", httpResponseCode, error.c_str());
            pixels.setPixelColor(0, pixels.Color(255, 255, 0));
            pixels.show(); // Amarelo
            mostrarMensagem("ERRO DE REDE", error, "CODE: " + String(httpResponseCode), "");
            delay(2500);
        }
        http.end();
    }
    else
    {
        pixels.setPixelColor(0, pixels.Color(255, 255, 0));
        pixels.show();
        mostrarMensagem("ERRO WIFI", descricaoStatusWifi(WiFi.status()),
                        "RELE PROTEGIDO", "SERIAL 115200");
    }

    nfc.reset();
    nfc.begin();
    nfc.setPassiveActivationRetries(0x01);
    nfc.SAMConfig();

    pixels.setPixelColor(0, pixels.Color(10, 10, 10));
    pixels.show();
    pinMode(pinoRele, INPUT_PULLUP);
    manterWifiConectado();
    mostrarMensagem("SISTEMA PRONTO", "APROXIME O CARD", "VELOCIDADE: SPI",
                    WiFi.status() == WL_CONNECTED ? "WIFI CONECTADO" : "WIFI OFFLINE");
}
