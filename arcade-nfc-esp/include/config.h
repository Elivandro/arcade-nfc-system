#ifndef CONFIG_H
#define CONFIG_H

#include <Adafruit_SSD1306.h>
#include <Adafruit_PN532.h>
#include <Adafruit_NeoPixel.h>

extern const char* ssid;
extern const char* password;

extern const char* apiUrl;

#define PN532_SCK  12
#define PN532_MISO 13
#define PN532_MOSI 11
#define PN532_SS   10

#define OLED_SDA   17
#define OLED_SCL   18

#define PIN_LED    48 
#define NUM_PIXELS  1

const int pinoRele = 5;

extern Adafruit_SSD1306 display;
extern Adafruit_PN532 nfc;
extern Adafruit_NeoPixel pixels;

#endif
