<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de integración con APIs peruanas (RENIEC - SUNAT)
 * 
 * Este servicio maneja la comunicación con servicios externos para:
 * - Validación y consulta de DNI en RENIEC
 * - Validación y consulta de RUC en SUNAT
 * - Gestión de límites de API y caché
 * - Manejo de errores y reintentos
 * - Auditoría de consultas
 * 
 * @package App\Services\Api
 */
class ApiPeruService
{
    /**
     * @var array Configuración de RENIEC
     */
    protected $reniecConfig;

    /**
     * @var array Configuración de SUNAT
     */
    protected $sunatConfig;

    /**
     * @var int Límite de reintentos por defecto
     */
    protected $maxRetries = 3;

    /**
     * Constructor del servicio
     */
    public function __construct()
    {
        $this->reniecConfig = config('services.reniec');
        $this->sunatConfig = config('services.sunat');
    }

    /**
     * Consultar DNI en RENIEC
     * 
     * @param string $dni Número de DNI (8 dígitos)
     * @return array|null Datos de la persona o null si falla
     */
    public function consultarDni(string $dni): ?array
    {
        // Validar formato de DNI
        if (!$this->validarFormatoDni($dni)) {
            Log::warning("Formato de DNI inválido: {$dni}");
            return null;
        }

        // Verificar si el servicio está habilitado
        if (!$this->reniecConfig['enabled']) {
            Log::info("Servicio RENIEC deshabilitado");
            return $this->getDatosSimuladosDni($dni);
        }

        // Verificar caché primero
        $cacheKey = "reniec_dni_{$dni}";
        if (Cache::has($cacheKey)) {
            Log::info("DNI encontrado en caché: {$dni}");
            return Cache::get($cacheKey);
        }

        try {
            // Realizar consulta a la API
            $response = $this->realizarConsultaApi(
                $this->reniecConfig['api_url'] . "/{$dni}",
                $this->reniecConfig['api_token'],
                'GET'
            );

            if ($response && isset($response['success']) && $response['success']) {
                // Procesar y normalizar datos
                $datos = $this->procesarDatosReniec($response['data'] ?? $response);
                
                // Guardar en caché
                Cache::put($cacheKey, $datos, $this->reniecConfig['cache_ttl']);
                
                // Registrar auditoría
                $this->registrarAuditoria('reniec', $dni, 'success', $datos);
                
                return $datos;
            }

            Log::warning("RENIEC no encontró el DNI: {$dni}");
            return null;

        } catch (\Exception $e) {
            Log::error("Error al consultar RENIEC: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Consultar RUC en SUNAT
     * 
     * @param string $ruc Número de RUC (11 dígitos)
     * @return array|null Datos de la empresa o null si falla
     */
    public function consultarRuc(string $ruc): ?array
    {
        // Validar formato de RUC
        if (!$this->validarFormatoRuc($ruc)) {
            Log::warning("Formato de RUC inválido: {$ruc}");
            return null;
        }

        // Verificar si el servicio está habilitado
        if (!$this->sunatConfig['enabled']) {
            Log::info("Servicio SUNAT deshabilitado");
            return $this->getDatosSimuladosRuc($ruc);
        }

        // Verificar caché primero
        $cacheKey = "sunat_ruc_{$ruc}";
        if (Cache::has($cacheKey)) {
            Log::info("RUC encontrado en caché: {$ruc}");
            return Cache::get($cacheKey);
        }

        try {
            // Realizar consulta a la API
            $response = $this->realizarConsultaApi(
                $this->sunatConfig['api_url'] . "/{$ruc}",
                $this->sunatConfig['api_token'],
                'GET'
            );

            if ($response && isset($response['success']) && $response['success']) {
                // Procesar y normalizar datos
                $datos = $this->procesarDatosSunat($response['data'] ?? $response);
                
                // Guardar en caché
                Cache::put($cacheKey, $datos, $this->sunatConfig['cache_ttl']);
                
                // Registrar auditoría
                $this->registrarAuditoria('sunat', $ruc, 'success', $datos);
                
                return $datos;
            }

            Log::warning("SUNAT no encontró el RUC: {$ruc}");
            return null;

        } catch (\Exception $e) {
            Log::error("Error al consultar SUNAT: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Realizar consulta a API externa
     * 
     * @param string $url URL de la API
     * @param string $token Token de autenticación
     * @param string $method Método HTTP
     * @param array $data Datos a enviar
     * @return array|null Respuesta de la API
     */
    protected function realizarConsultaApi(string $url, string $token, string $method = 'GET', array $data = []): ?array
    {
        $intentos = 0;
        $maxIntentos = $this->reniecConfig['retry_attempts'] ?? $this->maxRetries;

        while ($intentos < $maxIntentos) {
            try {
                $response = Http::timeout($this->reniecConfig['timeout'] ?? 30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->{$method === 'GET' ? 'get' : 'post'}($url, $data);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning("API respondió con código: " . $response->status());
                
                // Si es error 404, no reintentar
                if ($response->status() === 404) {
                    return null;
                }

            } catch (\Exception $e) {
                Log::error("Intento " . ($intentos + 1) . " falló: " . $e->getMessage());
            }

            $intentos++;
            
            // Esperar antes del siguiente intento (exponencial backoff)
            if ($intentos < $maxIntentos) {
                usleep(pow(2, $intentos) * 100000); // 0.2s, 0.4s, 0.8s...
            }
        }

        return null;
    }

    /**
     * Validar formato de DNI
     * 
     * @param string $dni
     * @return bool
     */
    protected function validarFormatoDni(string $dni): bool
    {
        return preg_match('/^\\d{8}$/', $dni) === 1;
    }

    /**
     * Validar formato de RUC
     * 
     * @param string $ruc
     * @return bool
     */
    protected function validarFormatoRuc(string $ruc): bool
    {
        if (preg_match('/^\\d{11}$/', $ruc) !== 1) {
            return false;
        }

        // Validar dígito verificador
        return $this->validarDigitoVerificadorRuc($ruc);
    }

    /**
     * Validar dígito verificador de RUC
     * 
     * @param string $ruc
     * @return bool
     */
    protected function validarDigitoVerificadorRuc(string $ruc): bool
    {
        $factor = '5432765432';
        $suma = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $suma += intval($ruc[$i]) * intval($factor[$i]);
        }
        
        $residuo = $suma % 11;
        $verificador = 11 - $residuo;
        
        if ($verificador == 10) {
            $verificador = 0;
        } elseif ($verificador == 11) {
            $verificador = 1;
        }
        
        return $verificador === intval($ruc[10]);
    }

    /**
     * Procesar datos de RENIEC
     * 
     * @param array $datos
     * @return array Datos normalizados
     */
    protected function procesarDatosReniec(array $datos): array
    {
        return [
            'dni' => $datos['dni'] ?? $datos['numeroDocumento'] ?? '',
            'nombres' => $datos['nombres'] ?? '',
            'apellido_paterno' => $datos['apellidoPaterno'] ?? $datos['apellido_paterno'] ?? '',
            'apellido_materno' => $datos['apellidoMaterno'] ?? $datos['apellido_materno'] ?? '',
            'nombre_completo' => trim(($datos['apellidoPaterno'] ?? $datos['apellido_paterno'] ?? '') . ' ' . 
                                 ($datos['apellidoMaterno'] ?? $datos['apellido_materno'] ?? '') . ' ' . 
                                 ($datos['nombres'] ?? '')),
            'fecha_nacimiento' => $datos['fechaNacimiento'] ?? $datos['fecha_nacimiento'] ?? null,
            'sexo' => $datos['sexo'] ?? null,
            'direccion' => $datos['direccion'] ?? $datos['direccionCompleta'] ?? '',
            'ubigeo' => $datos['ubigeo'] ?? null,
            'estado' => $datos['estado'] ?? 'ACTIVO',
            'fuente' => 'reniec'
        ];
    }

    /**
     * Procesar datos de SUNAT
     * 
     * @param array $datos
     * @return array Datos normalizados
     */
    protected function procesarDatosSunat(array $datos): array
    {
        return [
            'ruc' => $datos['ruc'] ?? $datos['numeroDocumento'] ?? '',
            'razon_social' => $datos['razonSocial'] ?? $datos['razon_social'] ?? '',
            'nombre_comercial' => $datos['nombreComercial'] ?? $datos['nombre_comercial'] ?? '',
            'direccion' => $datos['direccion'] ?? $datos['direccionCompleta'] ?? '',
            'ubigeo' => $datos['ubigeo'] ?? null,
            'estado' => $datos['estado'] ?? 'ACTIVO',
            'condicion' => $datos['condicion'] ?? 'HABIDO',
            'departamento' => $datos['departamento'] ?? '',
            'provincia' => $datos['provincia'] ?? '',
            'distrito' => $datos['distrito'] ?? '',
            'fuente' => 'sunat'
        ];
    }

    /**
     * Obtener datos simulados para DNI (modo desarrollo)
     * 
     * @param string $dni
     * @return array
     */
    protected function getDatosSimuladosDni(string $dni): array
    {
        $nombres = [
            'JUAN', 'MARIA', 'PEDRO', 'ANA', 'CARLOS', 'LUISA', 'JORGE', 'ROSA', 'MANUEL', 'TERESA',
            'ALBERTO', 'GLORIA', 'FERNANDO', 'CARMEN', 'RICARDO', 'ISABEL', 'EDUARDO', 'PATRICIA'
        ];
        
        $apellidos = [
            'PEREZ', 'GARCIA', 'LOPEZ', 'MARTINEZ', 'RODRIGUEZ', 'HERNANDEZ', 'GONZALEZ', 'FERNANDEZ',
            'TORRES', 'DIAZ', 'RAMIREZ', 'FLORES', 'MORALES', 'JIMENEZ', 'VAZQUEZ', 'CASTRO'
        ];
        
        $nombre = $nombres[array_rand($nombres)];
        $apellidoPaterno = $apellidos[array_rand($apellidos)];
        $apellidoMaterno = $apellidos[array_rand($apellidos)];
        
        return [
            'dni' => $dni,
            'nombres' => $nombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'nombre_completo' => "{$apellidoPaterno} {$apellidoMaterno} {$nombre}",
            'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'M',
            'direccion' => 'DIRECCIÓN SIMULADA',
            'ubigeo' => '150101',
            'estado' => 'ACTIVO',
            'fuente' => 'simulado'
        ];
    }

    /**
     * Obtener datos simulados para RUC (modo desarrollo)
     * 
     * @param string $ruc
     * @return array
     */
    protected function getDatosSimuladosRuc(string $ruc): array
    {
        $razonesSociales = [
            'CORPORACION ACME S.A.C.',
            'EMPRESA DE SERVICIOS GENERALES S.R.L.',
            'COMERCIAL IMPORTADORA S.A.C.',
            'CONSTRUCTORA Y DESARROLLO INMOBILIARIO S.A.',
            'CONSULTORIA Y ASESORAMIENTO EMPRESARIAL E.I.R.L.'
        ];
        
        return [
            'ruc' => $ruc,
            'razon_social' => $razonesSociales[array_rand($razonesSociales)],
            'nombre_comercial' => 'EMPRESA SIMULADA',
            'direccion' => 'AV. SIMULADA 123 - LIMA - LIMA - LIMA',
            'ubigeo' => '150101',
            'estado' => 'ACTIVO',
            'condicion' => 'HABIDO',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
            'fuente' => 'simulado'
        ];
    }

    /**
     * Registrar auditoría de consultas
     * 
     * @param string $servicio
     * @param string $documento
     * @param string $resultado
     * @param array $datos
     * @return void
     */
    protected function registrarAuditoria(string $servicio, string $documento, string $resultado, array $datos = []): void
    {
        Log::info("API Peru - Auditoría", [
            'servicio' => $servicio,
            'documento' => $documento,
            'resultado' => $resultado,
            'ip' => request()->ip(),
            'usuario' => auth()->user()->id ?? 'invitado',
            'timestamp' => now()->toDateTimeString(),
            'datos' => $resultado === 'success' ? array_keys($datos) : []
        ]);
    }

    /**
     * Obtener estadísticas de uso
     * 
     * @return array
     */
    public function getEstadisticas(): array
    {
        return [
            'reniec' => [
                'consultas_cache' => Cache::get('reniec_consultas_cache', 0),
                'consultas_api' => Cache::get('reniec_consultas_api', 0),
                'errores' => Cache::get('reniec_errores', 0)
            ],
            'sunat' => [
                'consultas_cache' => Cache::get('sunat_consultas_cache', 0),
                'consultas_api' => Cache::get('sunat_consultas_api', 0),
                'errores' => Cache::get('sunat_errores', 0)
            ]
        ];
    }
}