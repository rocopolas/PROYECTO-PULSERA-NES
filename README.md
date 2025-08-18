# Proyecto Pulsera NES

Este repositorio contiene una plataforma web y un ejemplo de firmware para gestionar el uso de **pulseras inteligentes** dentro de equipos. El sistema permite registrar usuarios, administrar pulseras, asignarlas a equipos y registrar eventos generados por las pulseras a través de un _webhook_.

## Estructura del proyecto

- `index.html` – Página de inicio con el formulario de inicio de sesión.
- `auth/` – Endpoints PHP para registro, inicio de sesión y cierre de sesión de usuarios.
- `pulseras/` – Panel para usuarios registrados. Permite seleccionar un equipo, ver el estado de una pulsera y revisar el historial de eventos.
- `admin/` – Herramientas de administración: registro de equipos y pulseras, asociación de pulseras a equipos, gestión de accesos y generación de códigos de invitación.
- `webhook/` – Punto de entrada que recibe eventos HTTP desde dispositivos externos y los almacena en la base de datos.
- `arduinoide/` – Ejemplo de sketch para ESP32 que envía un evento al webhook al presionar un botón.
- `sql/nes.sql` – Script con la estructura de la base de datos.
- `config/config.php` – Configuración de conexión a la base de datos.
- `colors.css` – Hoja de estilos utilizada por la interfaz.

## Requisitos

- Servidor con **PHP 8** o superior.
- **MySQL/MariaDB** para el almacenamiento de datos.
- Servidor web (Apache, Nginx o el servidor embebido de PHP).
- Opcional: **Arduino IDE** y un ESP32/ESP8266 para ejecutar el ejemplo de firmware.

## Instalación

1. Clona este repositorio en el directorio público de tu servidor web:
   ```bash
   git clone <url-del-repo>
   cd PROYECTO-PULSERA-NES
   ```
2. Configura la conexión a la base de datos editando `config/config.php` con tus credenciales.
3. Importa la estructura de la base de datos:
   ```bash
   mysql -u <usuario> -p < sql/nes.sql
   ```
4. Inicia el servidor web. Para pruebas locales puedes usar el servidor integrado de PHP:
   ```bash
   php -S localhost:8000 -t .
   ```
5. Accede a `http://localhost:8000` y crea un usuario desde `auth/register.php` o utiliza un usuario existente.

## Uso de la plataforma

1. **Inicio de sesión:** navega a `index.html`, introduce tus credenciales y accede al panel.
2. **Selector de pulsera:** tras iniciar sesión, el usuario ve sus equipos y las pulseras asociadas (`pulseras/selector_pulsera.php`).
3. **Dashboard:** al elegir una pulsera se muestra el estado actual, información de la pulsera y el historial de eventos (`pulseras/dashboard.php`).
4. **Administración:** los usuarios con permiso de administrador pueden:
   - Registrar nuevas pulseras (`pulseras/register_pulsera.php`).
   - Registrar equipos y asignar pulseras (`admin/registrar_equipo.php`, `admin/asociar_pulsera_equipo.php`).
   - Gestionar accesos de otros usuarios, cambiar permisos y generar códigos de invitación (`admin/*.php`).
5. **Webhooks:** los dispositivos externos envían eventos con una petición POST en formato JSON a `webhook/webhook.php`. Ejemplo:
   ```bash
   curl -X POST http://tu-servidor/webhook/webhook.php \
        -H "Content-Type: application/json" \
        -d '{"id_pulsera": 1}'
   ```
   El endpoint registra el `id_pulsera` y la marca de tiempo en la tabla `historialxpulseras`.

## Firmware de ejemplo

El directorio `arduinoide/` incluye un sketch para ESP32 que envía un POST al webhook cuando se presiona un botón.

1. Abre `arduinoide/arduinoide.ino` en el Arduino IDE.
2. Ajusta `ssid`, `password` y `webhookUrl` a tu red y URL de servidor.
3. Compila y sube el sketch a tu dispositivo.
4. Cada pulsación del botón enviará un evento que aparecerá en el historial del dashboard.

## Personalización de estilos

La interfaz utiliza `colors.css` para definir colores y fuentes. Puedes modificar los valores en `:root` para adaptar la paleta de colores a tus necesidades.

## Contribuciones

Las contribuciones son bienvenidas. Abre un _issue_ o envía un _pull request_ con tus mejoras.

## Licencia

Este proyecto se distribuye bajo la licencia MIT. Consulta el archivo `LICENSE` si está disponible.

