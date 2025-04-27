#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "LOL-2.4";         // Reemplaza con tu red WiFi
const char* password = "00425174082"; // Reemplaza con tu contraseña WiFi

const char* webhookUrl = "http://192.168.0.95/webhook/webhook.php";

const int botonPin = 15; 
bool ultimoEstado = HIGH; // Estado anterior del botón (con pull-up)

void mandar(bool estadoActivo){
  Serial.println("Botón presionado");
    HTTPClient http;

    http.begin(webhookUrl);
    http.addHeader("Content-Type", "application/json");

    String estado = estadoActivo ? "suspendido" : "activo"; 
    String jsonPayload = "{\"id\": 1, \"nuevo_estado\": \"" + estado + "\"}";

    int httpResponseCode = http.POST(jsonPayload);

    if (httpResponseCode > 0) {
      Serial.print("Estado enviado: ");
      Serial.println(estado);
      Serial.print("Código de respuesta: ");
      Serial.println(httpResponseCode);
    } else {
      Serial.print("Error al enviar el POST: ");
      Serial.println(httpResponseCode);
    }

    http.end();
}

void setup() {
  pinMode(botonPin, INPUT_PULLUP);

  Serial.begin(115200);
  WiFi.begin(ssid, password);

  Serial.print("Conectando a WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConectado a WiFi!");
}

void loop() {
  bool estadoActual = digitalRead(botonPin);

  if (estadoActual != ultimoEstado) {
  ultimoEstado = estadoActual;
  delay(50);
  if (estadoActual == LOW) { // solo cuando se presiona
    mandar(false); // manda activo
  } else {mandar(true);}
}
}
