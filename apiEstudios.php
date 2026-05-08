<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "config/database.php";

$database = new Database();
$connection = $database->connect();

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "OPTIONS") {
    http_response_code(200);
    exit;
}

$pathInfo = $_SERVER["PATH_INFO"] ?? "";
$pathParts = explode("/", trim($pathInfo, "/"));

$resource = $pathParts[0] ?? "";
$id = $pathParts[1] ?? null;

if ($resource !== "estudios") {
    http_response_code(404);
    echo json_encode([
        "error" => "Ruta no encontrada",
        "detalle" => "La ruta solicitada no existe en la API"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($method) {
    case "GET":
        if ($id === null || $id === "") {
            listarEstudios($connection);
        } else {
            consultarEstudio($connection, $id);
        }
        break;

    case "POST":
        if ($id === null || $id === "") {
            crearEstudio($connection);
        } else {
            responderError(400, "Petición incorrecta", "No se puede crear un estudio indicando ID en la URL");
        }
        break;

    case "PATCH":
        if ($id !== null && $id !== "") {
            actualizarEstudioParcial($connection, $id);
        } else {
            responderError(400, "ID incorrecto", "Debes indicar el ID del estudio a modificar");
        }
        break;

    case "DELETE":
        if ($id !== null && $id !== "") {
            eliminarEstudio($connection, $id);
        } else {
            responderError(400, "ID incorrecto", "Debes indicar el ID del estudio a eliminar");
        }
        break;

    default:
        responderError(405, "Método no permitido", "El método HTTP utilizado no está permitido");
        break;
}

function listarEstudios(PDO $connection): void
{
    try {
        $conditions = [];
        $params = [];

        if (isset($_GET["pais"]) && $_GET["pais"] !== "") {
            $conditions[] = "pais = :pais";
            $params[":pais"] = $_GET["pais"];
        }

        if (isset($_GET["ciudad"]) && $_GET["ciudad"] !== "") {
            $conditions[] = "ciudad = :ciudad";
            $params[":ciudad"] = $_GET["ciudad"];
        }

        if (isset($_GET["activo"]) && $_GET["activo"] !== "") {
            $activo = strtolower($_GET["activo"]);

            if (!in_array($activo, ["true", "false", "1", "0"], true)) {
                responderError(400, "Parámetros de filtro incorrectos", "El parámetro activo debe ser true, false, 1 o 0");
            }

            $conditions[] = "activo = :activo";
            $params[":activo"] = in_array($activo, ["true", "1"], true) ? 1 : 0;
        }

        $sql = "SELECT id, nombre, pais, ciudad, fecha_fundacion, activo FROM estudios";

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY id ASC";

        $statement = $connection->prepare($sql);
        $statement->execute($params);

        $estudios = $statement->fetchAll();

        foreach ($estudios as &$estudio) {
            $estudio["id"] = (int) $estudio["id"];
            $estudio["activo"] = (bool) $estudio["activo"];
        }

        http_response_code(200);
        echo json_encode([
            "total" => count($estudios),
            "estudios" => $estudios
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } catch (PDOException $exception) {
        responderError(500, "Error interno del servidor", $exception->getMessage());
    }
}

function consultarEstudio(PDO $connection, mixed $id): void
{
    validarId($id);

    try {
        $statement = $connection->prepare(
            "SELECT id, nombre, pais, ciudad, fecha_fundacion, activo FROM estudios WHERE id = :id"
        );

        $statement->execute([":id" => $id]);
        $estudio = $statement->fetch();

        if (!$estudio) {
            responderError(404, "Estudio no encontrado", "No existe ningún estudio con el ID indicado");
        }

        $estudio["id"] = (int) $estudio["id"];
        $estudio["activo"] = (bool) $estudio["activo"];

        http_response_code(200);
        echo json_encode($estudio, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } catch (PDOException $exception) {
        responderError(500, "Error interno del servidor", $exception->getMessage());
    }
}

function crearEstudio(PDO $connection): void
{
    $data = obtenerJson();

    if (!isset($data["nombre"]) || trim($data["nombre"]) === "") {
        responderError(400, "Datos incorrectos o incompletos", "El campo nombre es obligatorio");
    }

    try {
        $statement = $connection->prepare(
            "INSERT INTO estudios (nombre, pais, ciudad, fecha_fundacion, activo)
             VALUES (:nombre, :pais, :ciudad, :fecha_fundacion, :activo)"
        );

        $statement->execute([
            ":nombre" => $data["nombre"],
            ":pais" => $data["pais"] ?? null,
            ":ciudad" => $data["ciudad"] ?? null,
            ":fecha_fundacion" => $data["fecha_fundacion"] ?? null,
            ":activo" => isset($data["activo"]) ? (int) (bool) $data["activo"] : 1
        ]);

        $nuevoId = $connection->lastInsertId();

        http_response_code(201);
        consultarEstudio($connection, $nuevoId);

    } catch (PDOException $exception) {
        responderError(500, "Error interno del servidor", $exception->getMessage());
    }
}

function actualizarEstudioParcial(PDO $connection, mixed $id): void
{
    validarId($id);

    $data = obtenerJson();

    if (empty($data)) {
        responderError(400, "Datos incorrectos", "Debes enviar al menos un campo para actualizar");
    }

    $camposPermitidos = ["nombre", "pais", "ciudad", "fecha_fundacion", "activo"];
    $setParts = [];
    $params = [":id" => $id];

    foreach ($camposPermitidos as $campo) {
        if (array_key_exists($campo, $data)) {
            $setParts[] = "$campo = :$campo";

            if ($campo === "activo") {
                $params[":$campo"] = (int) (bool) $data[$campo];
            } else {
                $params[":$campo"] = $data[$campo];
            }
        }
    }

    if (count($setParts) === 0) {
        responderError(400, "Datos incorrectos", "No se ha enviado ningún campo válido para actualizar");
    }

    try {
        $existe = $connection->prepare("SELECT id FROM estudios WHERE id = :id");
        $existe->execute([":id" => $id]);

        if (!$existe->fetch()) {
            responderError(404, "Estudio no encontrado", "No existe ningún estudio con el ID indicado");
        }

        $sql = "UPDATE estudios SET " . implode(", ", $setParts) . " WHERE id = :id";
        $statement = $connection->prepare($sql);
        $statement->execute($params);

        consultarEstudio($connection, $id);

    } catch (PDOException $exception) {
        responderError(500, "Error interno del servidor", $exception->getMessage());
    }
}

function eliminarEstudio(PDO $connection, mixed $id): void
{
    validarId($id);

    try {
        $existe = $connection->prepare("SELECT id FROM estudios WHERE id = :id");
        $existe->execute([":id" => $id]);

        if (!$existe->fetch()) {
            responderError(404, "Estudio no encontrado", "No existe ningún estudio con el ID indicado");
        }

        $statement = $connection->prepare("DELETE FROM estudios WHERE id = :id");
        $statement->execute([":id" => $id]);

        http_response_code(204);
        exit;

    } catch (PDOException $exception) {
        responderError(500, "Error interno del servidor", $exception->getMessage());
    }
}

function obtenerJson(): array
{
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        responderError(400, "JSON incorrecto", "El cuerpo de la petición no contiene un JSON válido");
    }

    return $data ?? [];
}

function validarId(mixed $id): void
{
    if (!filter_var($id, FILTER_VALIDATE_INT) || (int) $id <= 0) {
        responderError(400, "ID incorrecto", "El ID debe ser un número entero positivo");
    }
}

function responderError(int $codigo, string $error, string $detalle): void
{
    http_response_code($codigo);

    echo json_encode([
        "error" => $error,
        "detalle" => $detalle
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}