# Sistema de Registro, Login y Control de Sesiones en PHP

Sistema web completo de autenticación y gestión de usuarios desarrollado con **PHP**, **MySQL (PDO)** y **HTML5/CSS3**. Incluye control de perfiles (roles), protección contra fuerza bruta con bloqueo de cuentas y diseño responsive.

---

## 🚀 Características Principales

- **Registro de Usuarios (`registro.php`):**
  - Validación de campos obligatorios en cliente y servidor.
  - Almacenamiento seguro de contraseñas mediante `password_hash()` (algoritmo BCRYPT).
  - Subida y almacenamiento de fotografías en base de datos (`LONGBLOB`).
  - Asignación de roles (`usuario` o `admin`).
  - Generación automática de tokens aleatorios criptográficos.

- **Autenticación y Control de Intentos (`login.php`):**
  - Consulta segura mediante **sentencias preparadas (PDO)** para prevenir inyecciones SQL.
  - Verificación de contraseñas con `password_verify()`.
  - **Protección contra fuerza bruta:** Contador de intentos fallidos. Al 3ᵉʳ intento consecutivo, la cuenta se bloquea automáticamente (`Usuario_bloqueado = 1`).
  - Registro de la última fecha de conexión al iniciar sesión.

- **Gestión de Sesiones y Redirección por Roles:**
  - Inicialización de variables de sesión (`$_SESSION['id']`, `$_SESSION['nombre']`, `$_SESSION['email']`, `$_SESSION['perfil']`).
  - Regeneración de ID de sesión (`session_regenerate_id(true)`) para mitigar ataques de fijación de sesión.
  - Redirección automática según el perfil:
    - `admin` ➔ Redirige a **`admin.php`**.
    - `usuario` ➔ Redirige a **`bienvenida.php`**.

- **Áreas Protegidas:**
  - **`bienvenida.php`:** Panel privado para el usuario con sus datos de sesión.
  - **`admin.php`:** Panel restringido para administradores con tabla en vivo de todos los usuarios registrados y su estado (activo/bloqueado).
  - **`logout.php`:** Cierre de sesión seguro, eliminación de cookies y destrucción de sesión.

- **Portal de Navegación (`index.html`):**
  - Menú interactivo moderno y responsive para acceder a cada sección.

---

## 📁 Estructura del Proyecto

```text
├── index.html               # Menú principal y acceso rápido a cada apartado
├── registro.php             # Formulario y procesamiento de registro
├── FormularioRegistro.php   # Copia del formulario de registro
├── login.php                # Formulario y autenticación con control de intentos
├── bienvenida.php           # Vista protegida para usuarios estándar
├── admin.php                # Panel de control protegido para administradores
├── logout.php               # Script de cierre y destrucción de sesiones
├── .gitignore               # Archivos omitidos en el control de versiones
└── README.md                # Documentación del proyecto
```

---

## 🗄️ Base de Datos

El sistema utiliza una base de datos MySQL llamada **`registrouser`**.

### Script de creación de la tabla `usuarios`

```sql
CREATE DATABASE IF NOT EXISTS `registrouser` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;
USE `registrouser`;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `Usuario_id` int(15) NOT NULL AUTO_INCREMENT,
  `Usuario_nombre` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Usuario_apellido1` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Usuario_apellido2` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Usuario_nick` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Usuario_clave` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Usuario_fecha_alta` date NOT NULL,
  `Usuario_email` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Usuario_bloqueado` tinyint(1) NOT NULL,
  `Usuario_fecha_bloqueo` date DEFAULT NULL,
  `Usuario_numero_intentos` int(1) NOT NULL,
  `Usuario_fecha_ultima_conexion` date DEFAULT NULL,
  `Usuario_token_aleatorio` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `Usuario_domicilio` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `Usuario_poblacion` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `Usuario_provincia` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `Usuario_perfil` varchar(45) DEFAULT NULL,
  `Usuario_nif` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `Usuario_numero_telefono` int(12) DEFAULT NULL,
  `Usuario_fotografia` longblob,
  `Usuario_fecha_contratacion` date DEFAULT NULL,
  PRIMARY KEY (`Usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
```

> **Nota:** La columna `Usuario_clave` utiliza `VARCHAR(255)` para almacenar de forma segura las cadenas generadas por `password_hash()`.

---

## ⚙️ Instalación y Puesta en Marcha

1. **Requisitos:**
   - Servidor local con **Apache** y **MySQL** (ej. [XAMPP](https://www.apachefriends.org/es/index.html) o Laragon).
   - PHP 8.0 o superior.

2. **Clonar o descargar:**
   ```bash
   git clone https://github.com/AVargas-CDPJCabrera/Login-PHP.git
   ```

3. **Colocar en el servidor:**
   Copia los archivos dentro de la carpeta `htdocs` de XAMPP (por ejemplo en `C:/xampp/htdocs/Login-PHP/`).

4. **Crear la Base de Datos:**
   - Abre [phpMyAdmin](http://localhost/phpmyadmin/).
   - Crea la base de datos `registrouser`.
   - Ejecuta el script SQL indicado arriba.

5. **Acceder a la aplicación:**
   Abre tu navegador e ingresa a:
   ```text
   http://localhost/Login-PHP/index.html
   ```

---

## 🛡️ Buenas Prácticas de Seguridad Implementadas

- **Protección contra Inyección SQL:** Todas las consultas se realizan con `PDO::prepare` y parámetros vinculados (`bindParam` / `execute`).
- **Cifrado de Credenciales:** No se almacenan contraseñas en texto plano; se utiliza `PASSWORD_DEFAULT`.
- **Prevención de XSS:** Salidas sanitizadas con `htmlspecialchars()`.
- **Control de Acceso Basado en Roles (RBAC):** `admin.php` valida activamente la existencia de la sesión y el perfil `admin`. Si no coincide, retorna un error HTTP `403 Prohibido`.
- **Fijación de Sesión:** Empleo de `session_regenerate_id(true)` tras una autenticación exitosa.

---

## 👤 Autor

- **Abraham Vargas** - [@AVargas-CDPJCabrera](https://github.com/AVargas-CDPJCabrera)
- Proyecto desarrollado para **2º DAW (Desarrollo de Aplicaciones Web)**.
