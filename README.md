# 🏥 VetTrack

<div align="center">

[![Symfony](https://img.shields.io/badge/Symfony-black?style=for-the-badge&logo=symfony)](https://symfony.com/)
[![Angular](https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white)](https://angular.io/)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

**Proyecto Intermodular - Desarrollo de Aplicaciones Web (DAW)**  
[BIRT](https://birt.eus) | Curso 2025/2026

</div>

---

## 🚀 Demo en vivo

**[vettrack.proyectozero.org](https://vettrack.proyectozero.org)**

Cuentas de acceso de prueba (visibles también en la propia pantalla de login):

| Rol | Email | Contraseña |
|---|---|---|
| Veterinario | `vet@vettrack.com` | `demo1234` |
| Cliente | `cliente@vettrack.com` | `demo1234` |

> La base de datos de la demo se restaura automáticamente cada 12 horas.

---

## 📋 Descripción

Sistema de gestión veterinaria desarrollado como Trabajo de Fin de Ciclo (TFC) para el ciclo superior de Desarrollo de Aplicaciones Web en BIRT. 

VetTrack permite la gestión integral de clínicas veterinarias, incluyendo control de pacientes, citas, historiales médicos y administración de usuarios.

---

## 🛠️ Stack Tecnológico

### Backend
- **PHP 8.3** con **Symfony 7.4 LTS** - Lógica de negocio y API REST
- **Twig** - Motor de plantillas (emails)
- **MySQL 8** - Sistema de gestión de base de datos

### Frontend
- **Angular 22** - Aplicación web interactiva (standalone components, control flow nativo)
- **TypeScript** - Lógica de la aplicación
- **Tailwind CSS** - Estilos y diseño responsivo

### Infraestructura
- **Docker** - Contenedorización de servicios (frontend y backend separados)
- **Docker Compose** - Orquestación de contenedores

---

## ✨ Características Principales

🔐 **Autenticación y Autorización**  
Gestión segura de usuarios con diferentes roles (administrador, veterinario, recepcionista)

📅 **Gestión de Citas**  
Sistema de reservas y calendario para organizar consultas

🐾 **Registro de Pacientes**  
Base de datos completa de mascotas con historiales clínicos

📊 **Panel de Control**  
Dashboard interactivo con estadísticas y métricas relevantes

📱 **Diseño Responsivo**  
Interfaz adaptada para dispositivos móviles, tablets y escritorio

---

## 🏗️ Arquitectura

El proyecto utiliza una **arquitectura basada en microservicios con Docker**, donde: 

- **Frontend**:  Contenedor independiente con la aplicación TypeScript
- **Backend**: Contenedor separado con la API PHP
- Cada servicio tiene su propia estructura y configuración

Esta separación permite escalabilidad, mantenimiento independiente y despliegue flexible.

---

## 👨‍💻 Coautor

**Sergio** - Estudiante de DAW
Especialización:  Desarrollo Web Full Stack

+ Junto con Amaiur, Sandra y Markel (todos estudiantes de BirtLH)

---

## 📄 Licencia

Este proyecto es un trabajo académico desarrollado en [BIRT](https://birt.eus).

---

<div align="center">
Hecho con ❤️ como Proyecto Intermodular DAW 2025/2026
</div>
