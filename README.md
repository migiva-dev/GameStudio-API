# GameStudio API

GameStudio API es un proyecto académico desarrollado para la asignatura de Desarrollo Web en Entorno Servidor.

El objetivo del proyecto es crear una API REST local para consultar, crear, modificar y eliminar estudios de videojuegos, siguiendo la especificación definida en `openApiEstudios.yaml`.

La API está desarrollada en PHP, consume datos desde una base de datos MySQL y se ejecuta en local mediante XAMPP. Además, incluye un cliente web desarrollado con HTML, CSS y JavaScript que consume los servicios de la API.

## Tecnologías utilizadas

- PHP
- MySQL
- Apache
- XAMPP
- JavaScript
- HTML
- CSS
- OpenAPI 3.0.3

## Funcionalidades principales

- Listar estudios de videojuegos.
- Filtrar estudios por país.
- Filtrar estudios por ciudad.
- Filtrar estudios por estado activo.
- Consultar un estudio por ID.
- Crear un nuevo estudio.
- Actualizar parcialmente un estudio.
- Eliminar un estudio.
- Consumir la API desde un cliente web.

## Estructura del proyecto

```text
GameStudio-API/
│
├── apiEstudios.php
│
├── config/
│   └── database.php
│
├── client/
│   └── index.html
│
├── database/
│   └── gamestudio.sql
│
├── docs/
│   └── openApiEstudios.yaml
│
├── README.md
├── .gitignore
└── LICENSE