#include <Arduino.h>
#include <WiFi.h>
#include "config.h"
#include "perifericos.h"

void setup()
{
    Serial.begin(115200);

    inicializarPerifericos();

    nfc.begin();
    uint32_t versiondata = nfc.getFirmwareVersion();
    if (!versiondata)
    {
        Serial.println("Erro: PN532 em modo SPI nao respondeu!");
        mostrarMensagem("ERRO HARDWARE", "SPI NFC FALHOU", "CHECK CHAVES 0 1", "PINOS: 10,11,12,13");
        piscarErroNFC();
    }

    nfc.SAMConfig();

    mostrarMensagem("SISTEMA PRONTO", "APROXIME O CARD", "VELOCIDADE: SPI",
                    WiFi.status() == WL_CONNECTED ? "WIFI CONECTADO" : "WIFI OFFLINE");
}

void loop()
{
    manterWifiConectado();

    uint8_t success;
    uint8_t uid[] = {0, 0, 0, 0, 0, 0, 0};
    uint8_t uidLength;

    success = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLength, 1000);

    if (success)
    {
        String idString = "";
        for (uint8_t i = 0; i < uidLength; i++)
        {
            if (uid[i] < 0x10)
                idString += "0";
            idString += String(uid[i], HEX);
        }
        idString.toUpperCase();

        Serial.print("ID capturado via SPI: ");
        Serial.println(idString);

        acionarReleComFeedback(idString);
    }
}
