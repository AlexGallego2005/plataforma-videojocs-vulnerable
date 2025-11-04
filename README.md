# 🧭 Guía de administración de servidor LAMP — Ubuntu 24.04

## 📋 Componentes del stack

| Componente | Servicio | Puerto predeterminado | Descripción |
|-------------|-----------|-----------------------|--------------|
| **Apache**  | `apache2` | 80 / 443 | Servidor web HTTP/HTTPS |
| **MySQL**   | `mysql` | 3306 | Base de datos relacional |
| **PHP**     | (Integrado con Apache) | — | Procesador de scripts PHP |

---

## ⚙️ Gestión de servicios

### 🔹 Verificar estado
```bash
sudo systemctl status apache2
sudo systemctl status mysql
```

### 🔹 Iniciar servicios
```bash
sudo systemctl start apache2
sudo systemctl start mysql
```

### 🔹 Detener servicios
```bash
sudo systemctl stop apache2
sudo systemctl stop mysql
```

### 🔹 Reiniciar servicios
```bash
sudo systemctl restart apache2
sudo systemctl restart mysql
```

### 🔹 Recargar configuración sin reiniciar completamente
```bash
sudo systemctl reload apache2
```

---

## 🔄 Inicio automático al arrancar el sistema

### Habilitar:
```bash
sudo systemctl enable apache2
sudo systemctl enable mysql
```

### Deshabilitar:
```bash
sudo systemctl disable apache2
sudo systemctl disable mysql
```

---

## 🧩 Estructura de la base de datos
![Modelo entidad relación Plataforma_videojocs](assets/database.png)

---

## 🧹 Mantenimiento básico

### 🔸 Ver registros (logs)

**Apache:**
```bash
sudo tail -f /var/log/apache2/access.log
sudo tail -f /var/log/apache2/error.log
```

**MySQL:**
```bash
sudo tail -f /var/log/mysql/error.log
```

### 🔸 Verificar configuración de Apache
```bash
sudo apache2ctl configtest
```

### 🔸 Verificar configuración de PHP
Crea un archivo `/var/www/html/info.php` con el siguiente contenido:
```php
<?php phpinfo(); ?>
```

Luego abre en tu navegador:
```
http://localhost/info.php
```

---

## 🔐 Seguridad básica recomendada

### Actualizar sistema:
```bash
sudo apt update && sudo apt upgrade -y
```

### Asegurar MySQL:
```bash
sudo mysql_secure_installation
```

### Configurar firewall (UFW):
```bash
sudo ufw allow 'Apache Full'
sudo ufw enable
```

---

## ✅ Notas finales

- Reinicia los servicios tras cualquier cambio en la configuración.  
- El documento raíz por defecto de Apache se encuentra en:
  ```
  /var/www/html
  ```
- Archivos de configuración principales:
  - Apache → `/etc/apache2/`
  - PHP → `/etc/php/<versión>/apache2/php.ini`
  - MySQL → `/etc/mysql/`

---

# 💾 Automatización de commits y backups con Git

Para mantener una copia de seguridad del proyecto y de la base de datos **plataforma_videojocs**, se utilizan dos scripts Bash:

---

## 🧠 `giteameesta.sh`

Script manual para crear una copia rápida de la base de datos y subir los cambios a GitHub.

```bash
#!/bin/bash
sudo mysqldump plataforma_videojocs > backup.sql
git add .
git commit -m "giteadisima automatica - rapidito q me voy de clase"
git push
```

**Uso:**
```bash
bash giteameesta.sh
```

**Qué hace:**
1. Genera un *dump* de la base de datos en `backup.sql`.  
2. Añade los cambios al área de staging.  
3. Crea un commit automático.  
4. Envía los cambios al repositorio remoto.  

---

## ⏰ `Giteameestacron`

Script para ejecutar automáticamente mediante **cron**.

```bash
#!/bin/bash
sudo mysqldump plataforma_videojocs > backup.sql
git add .
git commit -m "cronometrica"
git push
```

**Configuración cron (ejemplo, cada día a las 3 AM):**
```bash
crontab -e
```
Y añadir:
```
0 3 * * * /ruta/a/Giteameestacron >> /ruta/a/logs/backup.log 2>&1
```

---

### 🧩 Notas recomendadas

- Asegura permisos para ejecutar `mysqldump` y Git.  
- Considera variables de entorno o `.env` para credenciales.  
- Excluye el dump del control de versiones agregando a `.gitignore`:
  ```
  backup.sql
  ```
- Para copias con fecha/hora:
  ```bash
  sudo mysqldump plataforma_videojocs > backups/backup_$(date +%F_%H-%M).sql
  ```

---

# 🎮 Documentación de la API — Plataforma Videojocs

La API gestiona usuarios, juegos y partidas.  
Responde siempre en formato **JSON**.

---

## ⚙️ Configuración general

```php
require_once __DIR__ . '/secret/db.php';       // Conexión PDO
require_once __DIR__ . '/secret/auth.php';     // Autenticación
require_once __DIR__ . '/secret/games_model.php'; // Modelo de juegos
```

Todas las respuestas incluyen:
```http
Content-Type: application/json
```

---

## 🔹 Endpoints disponibles

### 🧩 `GET /jocs`
Lista todos los juegos o uno específico.

**Parámetros opcionales:**
- `id` — ID del juego

**Ejemplo:**
```bash
GET /jocs?id=3
```

**Respuesta:**
```json
[
  {
    "id": 3,
    "nom_joc": "Space Invaders",
    "descripcio": "Clàssic de naus",
    "puntuacio_maxima": 9999
  }
]
```

---

### 🔐 `POST /login`
Inicia sesión de usuario.

**Body:**
```json
{ "user": "nom_usuari", "password": "contrasenya" }
```

**Respuesta:**
```json
{ "ok": true, "session_id": "abcdef1234567890" }
```

---

### ➕ `POST /jocs`
Crea un nuevo juego (requiere sesión iniciada).

**Body:**
```json
{
  "nom_joc": "Pacman",
  "descripcio": "Menja punts i evita fantasmes",
  "puntuacio_maxima": 5000,
  "nivells_totals": 10
}
```

---

### 💾 `POST /api` — `"action": "save_game"`
Guarda una partida finalizada.

**Body:**
```json
{
  "action": "save_game",
  "usuari_id": 1,
  "joc_id": 3,
  "nivell": 4,
  "puntuacio": 3200,
  "durada": 95,
  "guanyat": true
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Partida guardada correctament",
  "partida_id": 58
}
```

---

### 📊 `POST /api` — `"action": "get_stats"`
Obtiene estadísticas personales.

**Body:**
```json
{ "action": "get_stats", "joc_id": 3 }
```

---

### 🏆 `POST /api` — `"action": "get_ranking"`
Obtiene el ranking global o de un juego.

**Body:**
```json
{ "action": "get_ranking", "joc_id": 3 }
```

---

## ⚠️ Códigos de respuesta HTTP

| Código | Significado |
|--------|--------------|
| **200** | OK |
| **400** | Petición inválida |
| **401** | No autorizado |
| **404** | No encontrado |
| **500** | Error interno |

---

# 🧩 games_model.php — Documentación del modelo

Define las funciones de lógica de juegos, progreso y estadísticas.

| Función | Descripción |
|----------|--------------|
| `getAllJocs($pdo)` | Devuelve todos los juegos |
| `getJoc($pdo, $id)` | Obtiene un juego por ID |
| `getGameById($id)` | Devuelve un juego activo |
| `getGameLevels($id)` | Niveles del juego |
| `getGameLevel($id, $nivell)` | Nivel específico |
| `getUserProgress($uid, $jid)` | Progreso del usuario |
| `createUserProgress($uid, $jid)` | Inicializa progreso |
| `saveGameMatch(...)` | Guarda una partida |
| `updateUserProgress(...)` | Actualiza progreso |
| `getGameStats(...)` | Estadísticas del juego |
| `getGameRanking(...)` | Ranking de jugadores |
| `getRecentMatches(...)` | Últimas partidas |
| `getRanking(...)` | Ranking global |
| `updateUserMaxScore(...)` | Actualiza puntuación máxima |

---

## ⚠️ Buenas prácticas

- Sustituir `$_GET` por parámetros de función.  
- Usar consultas **preparadas** para evitar inyección SQL.  
- Evitar `global $pdo` → usar **inyección de dependencias**.  

---
