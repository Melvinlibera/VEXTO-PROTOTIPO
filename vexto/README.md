# VEXTO - Sistema de Gestión Inmobiliaria

**Versión:** 1.4.0  
**Última Actualización:** Abril 2026

## Descripción del Proyecto

**VEXTO** es una plataforma web profesional de gestión inmobiliaria que permite a usuarios y empresas publicar, gestionar y buscar propiedades de forma eficiente. El sistema está diseñado para facilitar transacciones inmobiliarias con una interfaz moderna, segura y escalable.

### Características Principales

- **Autenticación Segura**: Sistema de registro e inicio de sesión con contraseñas hasheadas (bcrypt).
- **Gestión de Propiedades**: Crear, editar, eliminar y listar propiedades con detalles completos.
- **Perfiles de Usuario**: Soporte para usuarios individuales y empresas con límites de publicaciones diferenciados.
- **Favoritos**: Sistema para guardar propiedades favoritas.
- **Citas/Appointments**: Agendar citas para visitar propiedades.
- **Valoraciones**: Sistema de reseñas y calificaciones entre usuarios.
- **Búsqueda y Filtros**: Búsqueda avanzada por tipo de propiedad, operación, ubicación, etc.
- **Tema Personalizable**: Soporte para tema claro/oscuro.
- **Interfaz Responsiva**: Diseño adaptable a dispositivos móviles y de escritorio.

## Tecnologías Utilizadas

| Categoría | Tecnología |
|-----------|-----------|
| **Backend** | PHP 7.4+ |
| **Base de Datos** | MySQL 5.7+ / MariaDB |
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla) |
| **Librerías JS** | Anime.js (animaciones), Font Awesome (iconos) |
| **Tipografía** | Google Fonts (Inter) |
| **Seguridad** | PDO (Prepared Statements), Password Hashing (bcrypt) |

## Estructura del Proyecto

```
vexto/
├── config/                    # Configuración de la aplicación
│   ├── constants.php         # Constantes globales
│   ├── db.php                # Configuración de base de datos
│   └── database.sql          # Script de inicialización de BD
├── core/                      # Clases y lógica de negocio
│   ├── User.php              # Clase para manejo de usuarios
│   ├── Property.php          # Clase para manejo de propiedades
│   └── helpers.php           # Funciones de utilidad
├── public/                    # Punto de entrada de la aplicación
│   ├── index.php             # Página de autenticación
│   └── auth.php              # Controlador de autenticación
├── views/                     # Vistas/Páginas de la aplicación
│   ├── dashboard.php         # Panel de control
│   ├── properties.php        # Listado de propiedades
│   ├── property_details.php  # Detalles de propiedad
│   ├── profile.php           # Perfil de usuario
│   ├── my_publications.php   # Mis publicaciones
│   ├── publish.php           # Publicar propiedad
│   ├── favorites.php         # Propiedades favoritas
│   ├── stats.php             # Estadísticas
│   └── logout.php            # Cerrar sesión
├── assets/                    # Recursos estáticos
│   ├── css/                  # Hojas de estilos
│   ├── js/                   # Scripts JavaScript
│   └── img/                  # Imágenes
├── uploads/                   # Fotos de perfil de usuarios
├── publicaciones/            # Imágenes de propiedades
├── includes/                 # Componentes reutilizables
│   └── header.php            # Encabezado común
├── .env.example              # Plantilla de variables de entorno
└── README.md                 # Este archivo

```

## Requisitos del Sistema

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior (o MariaDB equivalente)
- **Servidor Web**: Apache o Nginx con soporte para PHP
- **Extensiones PHP**: PDO, PDO_MySQL, GD (para procesamiento de imágenes)

## Instalación

### Paso 1: Clonar o Descargar el Proyecto

```bash
# Si usas Git
git clone <repository-url> vexto
cd vexto

# O descargar el archivo ZIP y extraerlo
unzip vexto.zip
cd vexto
```

### Paso 2: Configurar la Base de Datos

1. Accede a tu cliente MySQL (phpMyAdmin, MySQL Workbench, etc.)
2. Crea una nueva base de datos (opcional, el script lo hace):
   ```sql
   CREATE DATABASE vexto_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Importa el archivo `config/database.sql`:
   ```bash
   mysql -u root -p vexto_db < config/database.sql
   ```

### Paso 3: Configurar Variables de Entorno

1. Copia el archivo `.env.example` a `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edita el archivo `.env` con tus credenciales:
   ```env
   DB_HOST=localhost
   DB_NAME=vexto_db
   DB_USER=root
   DB_PASS=tu_contraseña
   APP_ENV=production
   ```

### Paso 4: Configurar Permisos de Carpetas

Asegúrate de que las carpetas de carga tengan permisos de escritura:

```bash
chmod 755 uploads/
chmod 755 publicaciones/
chmod 755 logs/
```

### Paso 5: Configurar el Servidor Web

**Para Apache:**
- Asegúrate de que `mod_rewrite` esté habilitado
- Coloca el proyecto en la carpeta `htdocs` o configura un virtual host

**Para Nginx:**
- Configura un bloque `server` que apunte a la carpeta `public/`

### Paso 6: Acceder a la Aplicación

Abre tu navegador y accede a:
```
http://localhost/vexto/public/index.php
```

O si configuraste un dominio:
```
http://tu-dominio.com
```

## Uso de la Aplicación

### Registro de Usuario

1. Haz clic en "Regístrate aquí" en la página de inicio
2. Completa el formulario con tus datos:
   - Nombre y apellido
   - Género y cédula
   - Tipo de cuenta (Usuario o Empresa)
   - Foto de perfil
   - Correo electrónico y contraseña
3. Haz clic en "Crear Cuenta"

### Inicio de Sesión

1. Ingresa tu correo electrónico
2. Ingresa tu contraseña
3. Haz clic en "Iniciar Sesión"

### Publicar una Propiedad

1. Desde el dashboard, haz clic en "Publicar Propiedad"
2. Completa los detalles:
   - Título y descripción
   - Precio y tipo de operación (venta/alquiler)
   - Tipo de propiedad
   - Ubicación, habitaciones, baños, área
   - Sube imágenes
3. Haz clic en "Publicar"

### Buscar Propiedades

1. Ve a la sección "Propiedades"
2. Usa los filtros para buscar por:
   - Tipo de operación
   - Tipo de propiedad
   - Rango de precio
   - Ubicación
3. Haz clic en una propiedad para ver detalles

## Seguridad

### Prácticas Implementadas

- **Prepared Statements**: Todas las consultas SQL usan prepared statements para prevenir SQL injection
- **Password Hashing**: Las contraseñas se hashean con bcrypt (PASSWORD_DEFAULT)
- **Sanitización**: Todas las entradas de usuario se sanitizan con `htmlspecialchars()`
- **Validación**: Validación de correos, contraseñas y tipos de archivo
- **Sesiones Seguras**: Uso de sesiones PHP con timeout configurable
- **HTTPS Recomendado**: Se recomienda usar HTTPS en producción

### Mejoras Futuras de Seguridad

- Implementar CSRF tokens
- Agregar rate limiting en login
- Usar HTTPS obligatorio
- Implementar 2FA (autenticación de dos factores)
- Auditoría de acciones críticas

## Configuración Avanzada

### Variables de Entorno

Edita el archivo `.env` para personalizar:

```env
# Base de Datos
DB_HOST=localhost
DB_NAME=vexto_db
DB_USER=root
DB_PASS=

# Aplicación
APP_NAME=VEXTO
APP_ENV=development
APP_DEBUG=true

# Directorios
UPLOAD_DIR=uploads/
PUBLICATIONS_DIR=publicaciones/
```

### Constantes Globales

Edita `config/constants.php` para modificar:

- Límites de propiedades por tipo de usuario
- Tipos de propiedades permitidas
- Tamaño máximo de archivo
- Mensajes de error/éxito
- Rutas de la aplicación

## Troubleshooting

### Error: "Database Connection Error"

**Solución:**
- Verifica que MySQL esté corriendo
- Comprueba las credenciales en `.env`
- Asegúrate de que la base de datos existe

### Error: "Permission Denied" en carpetas de carga

**Solución:**
```bash
chmod 755 uploads/
chmod 755 publicaciones/
chmod 755 logs/
```

### Las imágenes no se cargan

**Solución:**
- Verifica que la carpeta `uploads/` tenga permisos de escritura
- Comprueba que la ruta en `config/constants.php` sea correcta
- Revisa los logs en `logs/`

### Sesión expira rápidamente

**Solución:**
- Aumenta `SESSION_TIMEOUT` en `config/constants.php`
- Configura `session.gc_maxlifetime` en `php.ini`

## API Endpoints (Futuro)

Se planea implementar una API REST para:
- Búsqueda de propiedades
- Gestión de favoritos
- Sistema de citas
- Valoraciones y reseñas

## Contribución

Para contribuir al proyecto:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Contacto y Soporte

Para reportar bugs, sugerencias o preguntas:

- **Email**: support@vexto.com
- **Issues**: Abre un issue en el repositorio
- **Documentación**: Consulta la wiki del proyecto

## Roadmap

### v1.5.0 (Próxima)
- [ ] API REST completa
- [ ] Notificaciones por email
- [ ] Sistema de mensajería entre usuarios
- [ ] Mapas interactivos

### v2.0.0
- [ ] Aplicación móvil (React Native)
- [ ] Pagos integrados (Stripe/PayPal)
- [ ] Verificación de identidad
- [ ] Seguros y garantías

## Changelog

### v1.4.0 (Actual)
- Reestructuración profesional del proyecto
- Implementación de clases (User, Property)
- Sistema de configuración mejorado
- Mejor manejo de errores
- Documentación completa

### v1.3.0
- Agregar sistema de citas
- Mejorar interfaz de búsqueda

### v1.0.0
- Lanzamiento inicial

---

**Desarrollado con ❤️ por el equipo de VEXTO**

*Última actualización: Abril 2026*
