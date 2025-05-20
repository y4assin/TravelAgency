# 🧳 Sistema de Gestión de Paquetes Turísticos

Este plugin permite a una agencia de viajes gestionar sus paquetes turísticos, reservas, pasajeros e itinerarios.

---

## 📦 Funcionalidades Principales

- Administración de **paquetes turísticos** con origen, destino, fechas, precios y disponibilidad.
- Definición detallada de **itinerarios** diarios por paquete.
- Gestión de **habitaciones disponibles** por tipo y cupo.
- **Reservas** vinculadas a clientes existentes.
- Integración nativa con los módulos de **clientes, usuarios, formas de pago y facturación** de FacturaScripts.
- hay que crear la gestión de pasajeros

---

## 🏙️ Ciudades de origen y destino

El sistema utiliza la tabla de **ciudades nativas de FacturaScripts** (`ciudades`) para definir tanto el **origen** como el **destino** de cada paquete.

---

## 💳 Integraciones con módulos de FS

Los siguientes elementos son reutilizados de FacturaScripts:

- **Clientes**: asociados a cada reserva.
- **Formas de pago**: seleccionadas al momento de realizar una reserva.
- **Usuarios**: se registran como creadores o responsables de reservas.
- **Facturas**: permiten facturar automáticamente reservas confirmadas.


## 📡 API

Para habilitar el acceso vía API REST:

- Ves al Panel de Control → Activar API.
- Luego, ves al menú API REST para generar un token de acceso.
- crear Api KEY.