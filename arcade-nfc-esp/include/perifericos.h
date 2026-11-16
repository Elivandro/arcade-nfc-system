#ifndef PERIFERICOS_H
#define PERIFERICOS_H

#include <Arduino.h>

void mostrarMensagem(String l1, String l2 = "", String l3 = "", String l4 = "");
void inicializarPerifericos();
bool conectarWifi(uint32_t timeoutMs);
void manterWifiConectado();
void piscarErroNFC();
void acionarReleComFeedback(String idString);

#endif
