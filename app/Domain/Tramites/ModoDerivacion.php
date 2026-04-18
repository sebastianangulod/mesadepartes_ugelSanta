<?php

namespace App\Domain\Tramites;

/**
 * Modo de derivación por trámite (irreversible de AUTO a manual forzado).
 */
class ModoDerivacion
{
    public const AUTO = 'AUTO';

    public const MANUAL_FORZADO = 'MANUAL_FORZADO';

    /**
     * @return list<string>
     */
    public static function todos(): array
    {
        return [self::AUTO, self::MANUAL_FORZADO];
    }

    public static function esValido(string $valor): bool
    {
        return in_array($valor, self::todos(), true);
    }
}
