INSTRUCCIONES PARA CONFIGURAR EL SISTEMA DE FARMACIA

1. INSTALAR DEPENDENCIAS
Ejecuta en la raíz del proyecto:
composer require phpmailer/phpmailer

2. CONFIGURAR CORREO SMTP
Edita config_correo.php con tus datos reales:
- SMTP_USER: tu correo Gmail
- SMTP_PASS: contraseña de aplicación de Gmail (no tu contraseña normal)
- Para generar contraseña de aplicación:
  - Ve a https://myaccount.google.com/security
  - Activa verificación en 2 pasos
  - Genera contraseña de aplicación para "Correo"

3. BASE DE DATOS
- Ejecuta seed_farmacia.php para crear tablas y datos iniciales
- Admin hardcodeado: admin@farmacia.com / Admin123 (entra directo sin 2FA)
- Clientes: se registran y usan 2FA por correo

4. FLUJO DE LOGIN
- Admin: entra directo a dashboard_admin.php
- Clientes: reciben código 2FA por correo, validan en verificar_2fa.php

5. NOTAS DE SEGURIDAD
- Admin no depende de base de datos
- Clientes usan password_hash y 2FA real
- Código 2FA expira en 5 minutos
- Reenvío disponible

6. FUNCIONALIDADES
- CRUD productos, categorías, usuarios
- Carrito, favoritos, ventas
- Dashboard admin con estadísticas
- Diseño responsive con Bootstrap y colores farmacia

¡Listo para usar!
