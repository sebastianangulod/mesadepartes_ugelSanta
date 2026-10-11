<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

/**
 * Bandeja de notificaciones del usuario autenticado.
 */
class NotificacionController extends Controller
{
    /**
     * Vista del apartado de notificaciones.
     */
    public function index()
    {
        $usuario = auth()->user();

        $noLeidas = Notificacion::noLeidas($usuario->id)->count();

        return view('notificaciones.index', [
            'noLeidas' => $noLeidas,
            'tramitesPendientes' => $usuario->esInterno()
                ? \App\Models\Tramite::where('estado', 'PENDIENTE')->count()
                : 0,
        ]);
    }

    /**
     * Lista paginada para la bandeja y el desplegable de la campana.
     */
    public function lista(Request $request)
    {
        $usuario = auth()->user();
        $filtro = $request->input('filtro', 'todas'); // todas | no-leidas
        $limite = min((int) $request->input('limite', 15), 50);

        $query = Notificacion::where('id_usuario', $usuario->id)
            ->orderByDesc('created_at');

        if ($filtro === 'no-leidas') {
            $query->where('leida', false);
        }

        $totalNoLeidas = Notificacion::noLeidas($usuario->id)->count();
        $notificaciones = $query->limit($limite)->get();

        return response()->json([
            'status' => 'success',
            'no_leidas' => $totalNoLeidas,
            'notificaciones' => $notificaciones->map(fn ($n) => [
                'id' => $n->id,
                'titulo' => $n->titulo,
                'mensaje' => $n->mensaje,
                'tipo' => $n->tipo,
                'url' => $n->url,
                'leida' => (bool) $n->leida,
                'fecha' => $n->created_at ? $n->created_at->format('d/m/Y H:i') : '',
            ]),
        ]);
    }

    /**
     * Contador para la campana del encabezado.
     */
    public function noLeidas()
    {
        return response()->json([
            'status' => 'success',
            'no_leidas' => Notificacion::noLeidas(auth()->id())->count(),
        ]);
    }

    /**
     * Marca una notificación como leída (solo del propio usuario).
     */
    public function leer(int $id)
    {
        $notificacion = Notificacion::where('id', $id)
            ->where('id_usuario', auth()->id())
            ->firstOrFail();

        $notificacion->update(['leida' => true]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Marca todas como leídas.
     */
    public function leerTodas()
    {
        Notificacion::where('id_usuario', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['status' => 'success']);
    }
}
