<?php

declare(strict_types=1);

namespace MiniProject;

/**
 * Helper para respuestas JSON en la API REST.
 *
 * Proporciona metodos estaticos para generar respuestas HTTP consistentes
 * con el Content-Type correcto y codigos de estado apropiados.
 *
 * Caracteristicas PHP 8: named arguments, match
 */
class JsonResponse
{
    /**
     * Envia una respuesta JSON exitosa.
     *
     * @param mixed $data Datos a incluir en la respuesta
     * @param int $code Codigo de estado HTTP (200 por defecto)
     * @param string $message Mensaje descriptivo opcional
     * @return never Termina la ejecucion despues de enviar la respuesta
     */
    public static function success(
        mixed $data = null,
        int $code = 200,
        string $message = 'OK',
    ): never {
        self::enviar(
            body: [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
            code: $code,
        );
    }

    /**
     * Envia una respuesta JSON de error.
     *
     * @param string $message Mensaje descriptivo del error
     * @param int $code Codigo de estado HTTP (400 por defecto)
     * @param array<string, mixed>|null $errors Detalles adicionales del error
     * @return never Termina la ejecucion despues de enviar la respuesta
     */
    public static function error(
        string $message,
        int $code = 400,
        ?array $errors = null,
    ): never {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        // Incluir detalles de error si se proporcionan
        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        self::enviar(body: $body, code: $code);
    }

    /**
     * Envia la respuesta HTTP con cabeceras JSON y codigo de estado.
     *
     * Establece el Content-Type como application/json, codifica el cuerpo
     * con soporte Unicode y pretty print, y termina la ejecucion.
     *
     * @param array<string, mixed> $body Cuerpo de la respuesta
     * @param int $code Codigo de estado HTTP
     * @return never Termina la ejecucion con exit()
     */
    private static function enviar(array $body, int $code): never
    {
        // Establecer codigo de estado HTTP
        http_response_code($code);

        // Establecer cabecera de tipo de contenido
        header('Content-Type: application/json; charset=utf-8');

        // Codificar y enviar el JSON
        echo json_encode(
            value: $body,
            flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        exit;
    }

    /**
     * Traduce un codigo de estado HTTP a su texto descriptivo.
     *
     * @param int $code Codigo de estado HTTP
     * @return string Texto descriptivo del codigo
     */
    public static function textoEstado(int $code): string
    {
        return match ($code) {
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            default => 'Unknown',
        };
    }
}
