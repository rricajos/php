<?php

declare(strict_types=1);

namespace MiniProject;

/**
 * Jerarquia de excepciones personalizadas para la aplicacion.
 *
 * Proporciona excepciones especificas para distintos tipos de errores:
 * - AppException: excepcion base de la aplicacion
 * - ValidationException: errores de validacion de datos de entrada
 * - NotFoundException: recurso no encontrado
 */

// --- Excepcion base de la aplicacion ---
class AppException extends \RuntimeException
{
    /** Codigos de error generales de la aplicacion */
    public const ERROR_GENERAL = 1000;
    public const ERROR_DATABASE = 1001;
    public const ERROR_FILESYSTEM = 1002;

    public function __construct(
        string $message,
        int $code = self::ERROR_GENERAL,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

// --- Excepcion para errores de validacion ---
class ValidationException extends AppException
{
    /** Codigos de error de validacion */
    public const ERROR_CAMPO_VACIO = 2000;
    public const ERROR_PRIORIDAD_INVALIDA = 2001;
    public const ERROR_ESTADO_INVALIDO = 2002;
    public const ERROR_ID_INVALIDO = 2003;

    /**
     * @param string $message Mensaje descriptivo del error de validacion
     * @param int $code Codigo de error especifico
     * @param string $campo Nombre del campo que fallo la validacion
     */
    public function __construct(
        string $message,
        int $code = self::ERROR_CAMPO_VACIO,
        public readonly string $campo = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

// --- Excepcion cuando un recurso no se encuentra ---
class NotFoundException extends AppException
{
    /** Codigos de error de recurso no encontrado */
    public const ERROR_TAREA_NO_ENCONTRADA = 3000;

    /**
     * @param string $recurso Tipo de recurso que no se encontro
     * @param int|string $identificador Identificador del recurso buscado
     */
    public function __construct(
        public readonly string $recurso,
        public readonly int|string $identificador,
        ?\Throwable $previous = null,
    ) {
        $message = "No se encontro {$recurso} con identificador: {$identificador}";
        parent::__construct($message, self::ERROR_TAREA_NO_ENCONTRADA, $previous);
    }
}
