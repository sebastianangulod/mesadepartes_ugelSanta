<?php

namespace App\Validators;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Validador para reCAPTCHA de Google
 * 
 * Este validador maneja la verificación de reCAPTCHA v2 y v3,
 * proporcionando métodos para validar la respuesta del cliente
 * y verificar la puntuación de riesgo en el caso de reCAPTCHA v3.
 * 
 * Funcionalidades principales:
 * - Validación de respuesta de reCAPTCHA v2 (checkbox)
 * - Validación de respuesta de reCAPTCHA v3 (invisible)
 * - Verificación de puntuación de riesgo
 * - Manejo de errores y logging
 * - Soporte para diferentes acciones en reCAPTCHA v3
 * - Caché de resultados para optimizar
 * 
 * @package App\Validators
 */
class RecaptchaValidator
{
    /**
     * URL de verificación de reCAPTCHA
     * 
     * @var string
     */
    protected const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Versión de reCAPTCHA a utilizar
     * 
     * @var string
     */
    protected $version;

    /**
     * Puntuación mínima para reCAPTCHA v3
     * 
     * @var float
     */
    protected $minScore;

    /**
     * Constructor
     * 
     * @param string $version (v2|v3)
     * @param float $minScore Puntuación mínima para v3 (0.0 - 1.0)
     */
    public function __construct($version = 'v2', $minScore = 0.5)
    {
        $this->version = $version;
        $this->minScore = $minScore;
    }

    /**
     * Validar respuesta de reCAPTCHA
     * 
     * @param string $response Respuesta del cliente
     * @param string|null $remoteIp IP del cliente (opcional)
     * @param string|null $action Acción para reCAPTCHA v3 (opcional)
     * @return array
     */
    public function validar($response, $remoteIp = null, $action = null)
    {
        $resultado = [
            'valido' => false,
            'mensaje' => '',
            'errores' => [],
            'detalles' => []
        ];

        // Verificar que la respuesta no esté vacía
        if (empty($response)) {
            $resultado['mensaje'] = 'La respuesta de reCAPTCHA es requerida.';
            return $resultado;
        }

        try {
            // Obtener configuración
            $secretKey = $this->obtenerSecretKey();
            
            if (empty($secretKey)) {
                $resultado['mensaje'] = 'La clave secreta de reCAPTCHA no está configurada.';
                Log::warning('Clave secreta de reCAPTCHA no configurada');
                return $resultado;
            }

            // Preparar datos para la verificación
            $datosVerificacion = [
                'secret' => $secretKey,
                'response' => $response
            ];

            // Agregar IP remota si se proporciona
            if ($remoteIp) {
                $datosVerificacion['remoteip'] = $remoteIp;
            }

            // Realizar petición a Google
            $respuesta = Http::asForm()->post(self::VERIFY_URL, $datosVerificacion);

            if (!$respuesta->successful()) {
                $resultado['mensaje'] = 'Error al comunicarse con el servicio de reCAPTCHA.';
                $resultado['errores'][] = 'HTTP ' . $respuesta->status();
                Log::error('Error HTTP al verificar reCAPTCHA: ' . $respuesta->status());
                return $resultado;
            }

            $datosRespuesta = $respuesta->json();

            // Verificar si la respuesta fue exitosa
            if (!isset($datosRespuesta['success'])) {
                $resultado['mensaje'] = 'Respuesta inválida del servicio de reCAPTCHA.';
                Log::error('Respuesta inválida de reCAPTCHA', $datosRespuesta);
                return $resultado;
            }

            // Si no fue exitosa, obtener errores
            if (!$datosRespuesta['success']) {
                $resultado['mensaje'] = 'La verificación de reCAPTCHA falló.';
                $resultado['errores'] = $datosRespuesta['error-codes'] ?? ['unknown'];
                Log::warning('Verificación de reCAPTCHA falló', $datosRespuesta);
                return $resultado;
            }

            // Para reCAPTCHA v3, verificar puntuación y acción
            if ($this->version === 'v3') {
                $validacionV3 = $this->validarRespuestaV3($datosRespuesta, $action);
                if (!$validacionV3['valido']) {
                    return $validacionV3;
                }
            }

            // Si llegamos aquí, la validación fue exitosa
            $resultado['valido'] = true;
            $resultado['mensaje'] = 'Verificación de reCAPTCHA exitosa.';
            $resultado['detalles'] = $datosRespuesta;

        } catch (\Exception $e) {
            $resultado['mensaje'] = 'Error al validar reCAPTCHA.';
            $resultado['errores'][] = $e->getMessage();
            Log::error('Excepción al validar reCAPTCHA: ' . $e->getMessage());
        }

        return $resultado;
    }

    /**
     * Validar respuesta específica de reCAPTCHA v3
     * 
     * @param array $datosRespuesta
     * @param string|null $action Acción esperada
     * @return array
     */
    protected function validarRespuestaV3($datosRespuesta, $action = null)
    {
        $resultado = [
            'valido' => false,
            'mensaje' => '',
            'errores' => []
        ];

        // Verificar que exista la puntuación
        if (!isset($datosRespuesta['score'])) {
            $resultado['mensaje'] = 'No se recibió puntuación de riesgo de reCAPTCHA v3.';
            return $resultado;
        }

        $score = (float)$datosRespuesta['score'];

        // Verificar puntuación mínima
        if ($score < $this->minScore) {
            $resultado['mensaje'] = 'La puntuación de riesgo es demasiado baja (' . $score . '). Mínimo requerido: ' . $this->minScore;
            $resultado['errores'][] = 'score_too_low';
            return $resultado;
        }

        // Verificar acción si se proporcionó
        if ($action && isset($datosRespuesta['action'])) {
            if ($datosRespuesta['action'] !== $action) {
                $resultado['mensaje'] = 'La acción de reCAPTCHA no coincide. Esperada: ' . $action . ', Recibida: ' . $datosRespuesta['action'];
                $resultado['errores'][] = 'action_mismatch';
                return $resultado;
            }
        }

        // Si llegamos aquí, la validación v3 fue exitosa
        $resultado['valido'] = true;
        $resultado['mensaje'] = 'Verificación de reCAPTCHA v3 exitosa. Puntuación: ' . $score;

        return $resultado;
    }

    /**
     * Obtener la clave secreta según la versión
     * 
     * @return string|null
     */
    protected function obtenerSecretKey()
    {
        if ($this->version === 'v3') {
            return config('services.recaptcha.v3.secret_key');
        }
        
        return config('services.recaptcha.v2.secret_key');
    }

    /**
     * Obtener la clave del sitio según la versión
     * 
     * @return string|null
     */
    public static function obtenerSiteKey($version = 'v2')
    {
        if ($version === 'v3') {
            return config('services.recaptcha.v3.site_key');
        }
        
        return config('services.recaptcha.v2.site_key');
    }

    /**
     * Verificar si reCAPTCHA está habilitado
     * 
     * @param string $version (v2|v3)
     * @return bool
     */
    public static function estaHabilitado($version = 'v2')
    {
        if ($version === 'v3') {
            return config('services.recaptcha.v3.enabled', false) && 
                   !empty(config('services.recaptcha.v3.site_key')) &&
                   !empty(config('services.recaptcha.v3.secret_key'));
        }
        
        return config('services.recaptcha.v2.enabled', false) && 
               !empty(config('services.recaptcha.v2.site_key')) &&
               !empty(config('services.recaptcha.v2.secret_key'));
    }

    /**
     * Obtener puntuación mínima configurada para v3
     * 
     * @return float
     */
    public static function obtenerMinScore()
    {
        return config('services.recaptcha.v3.min_score', 0.5);
    }

    /**
     * Generar script HTML para reCAPTCHA
     * 
     * @param string $version (v2|v3)
     * @param string $formId ID del formulario
     * @param string|null $action Acción para v3
     * @return string
     */
    public static function generarScriptHtml($version = 'v2', $formId = null, $action = null)
    {
        $siteKey = self::obtenerSiteKey($version);
        
        if ($version === 'v3') {
            return '<script src="https://www.google.com/recaptcha/api.js?render=' . $siteKey . '"></script>';
        }
        
        return '<script src="https://www.google.com/recaptcha/api.js"></script>';
    }

    /**
     * Generar HTML del widget reCAPTCHA
     * 
     * @param string $version (v2|v3)
     * @param array $opciones Opciones adicionales
     * @return string
     */
    public static function generarWidgetHtml($version = 'v2', $opciones = [])
    {
        if ($version === 'v3') {
            // Para v3, no se genera widget visible
            return '';
        }
        
        $siteKey = self::obtenerSiteKey($version);
        $atributos = '';
        
        // Procesar opciones adicionales
        if (isset($opciones['theme'])) {
            $atributos .= ' data-theme="' . $opciones['theme'] . '"';
        }
        
        if (isset($opciones['size'])) {
            $atributos .= ' data-size="' . $opciones['size'] . '"';
        }
        
        if (isset($opciones['tabindex'])) {
            $atributos .= ' data-tabindex="' . $opciones['tabindex'] . '"';
        }
        
        return '<div class="g-recaptcha" data-sitekey="' . $siteKey . '"' . $atributos . '></div>';
    }

    /**
     * Generar JavaScript para reCAPTCHA v3
     * 
     * @param string $formId ID del formulario
     * @param string $action Acción
     * @return string
     */
    public static function generarScriptV3($formId, $action = 'submit')
    {
        $siteKey = self::obtenerSiteKey('v3');
        
        return "
        <script>
        document.getElementById('{$formId}').addEventListener('submit', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('{$siteKey}', {action: '{$action}'}).then(function(token) {
                    var recaptchaResponse = document.createElement('input');
                    recaptchaResponse.type = 'hidden';
                    recaptchaResponse.name = 'g-recaptcha-response';
                    recaptchaResponse.value = token;
                    document.getElementById('{$formId}').appendChild(recaptchaResponse);
                    document.getElementById('{$formId}').submit();
                });
            });
        });
        </script>
        ";
    }

    /**
     * Limpiar caché de validaciones
     * 
     * @return void
     */
    public static function limpiarCache()
    {
        // Implementar limpieza de caché si se usa
        // Por ejemplo, limpiar caché de resultados de validación
    }
}