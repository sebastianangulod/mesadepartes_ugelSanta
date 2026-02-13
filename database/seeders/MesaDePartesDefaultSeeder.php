<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Administrador;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use App\Models\UsuariosNiveles;
use App\Models\Procedimiento;
use App\Models\ProcedimientoUsuario;

class MesaDePartesDefaultSeeder extends Seeder
{
    /**
     * Seed de datos base para Mesa de Partes.
     *
     * - Crea un usuario administrador.
     * - Crea 4 usuarios internos de mesa de partes.
     * - Crea la cadena de niveles AGI, AGI 2, AGI 3, AGI 4.
     * - Asigna cada usuario interno a un nivel distinto (1–4) en usuarios_niveles.
     * - Crea el procedimiento "Rectificación" y lo asigna al usuario del nivel 4.
     *
     * Todos los usuarios usan la contraseña: UgelSanta_2025
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $passwordHash = Hash::make('UgelSanta_2025');

            // ADMINISTRADOR (será el primer registro en una BD vacía => id = 1)
            $admin = Usuario::create([
                'ruc' => null,
                'razon_social' => null,
                'passw' => $passwordHash,
                'email' => 'josecas35@hotmail.com',
                'nombres' => 'JOSE PAUL JUSSTER',
                'apellido_paterno' => 'CASTILLO',
                'apellido_materno' => 'VELASQUEZ',
                'dni' => '43711492',
                'direccion' => 'SIN DIRECCION',
                'distrito_id' => 1,
                'telefono' => '000000000',
                'tipo_user' => 2,      // interno
                'tipo_persona' => 1,   // natural
                'fecha_registro' => now(),
            ]);

            // Registrar como administrador (tabla administradores) usando el id generado
            Administrador::updateOrCreate(
                ['id_usuario' => $admin->id],
                ['fecha_asignacion' => now()]
            );

            // USUARIOS INTERNOS DE MESA DE PARTES (4 seleccionados de la lista)
            // El orden en una BD vacía hará que sus IDs sean 2,3,4,5.
            $usuariosInternosData = [
                [
                    'email' => 'mesadepartes1@gmail.com',
                    'nombres' => 'LEODAN',
                    'apellido_paterno' => 'SUAREZ',
                    'apellido_materno' => 'ZURITA',
                    'dni' => '71138961',
                    'telefono' => '71138961',
                ],
                [
                    'email' => 'mesadepartes2@gmail.com',
                    'nombres' => 'NORMAN ELI',
                    'apellido_paterno' => 'BERMEO',
                    'apellido_materno' => 'BERMEO',
                    'dni' => '71138962',
                    'telefono' => '71138962',
                ],
                [
                    'email' => 'mesadepartes3@gmail.com',
                    'nombres' => 'DARWIN',
                    'apellido_paterno' => 'ROJAS',
                    'apellido_materno' => 'GUEVARA',
                    'dni' => '71138963',
                    'telefono' => '71138963',
                ],
                [
                    'email' => 'ugel1@gmail.com',
                    'nombres' => 'BRAYAN',
                    'apellido_paterno' => 'RODRIGO',
                    'apellido_materno' => 'SANCHEZ',
                    'dni' => '71138981',
                    'telefono' => '71138981',
                ],
            ];

            $usuariosInternos = [];

            foreach ($usuariosInternosData as $datos) {
                $usuario = Usuario::create([
                    'ruc' => null,
                    'razon_social' => null,
                    'passw' => $passwordHash,
                    'email' => $datos['email'],
                    'nombres' => $datos['nombres'],
                    'apellido_paterno' => $datos['apellido_paterno'],
                    'apellido_materno' => $datos['apellido_materno'],
                    'dni' => $datos['dni'],
                    'direccion' => 'SIN DIRECCION',
                    'distrito_id' => 1,
                    'telefono' => $datos['telefono'],
                    'tipo_user' => 2,      // interno
                    'tipo_persona' => 1,   // natural
                    'fecha_registro' => now(),
                ]);

                $usuariosInternos[] = $usuario;
            }

            // NIVELES AGI 1–4
            // Creamos una cadena jerárquica:
            // Nivel1: AGI
            // Nivel2: AGI 2 (hijo de AGI)
            // Nivel3: AGI 3 (hijo de AGI 2)
            // Nivel4: AGI 4 (hijo de AGI 3)

            // En una BD vacía, estos serán id = 1 en cada nivel, pero sin forzar IDs.
            $nivel1 = Nivel1::create([
                'nombre' => 'AGI',
            ]);

            $nivel2 = Nivel2::create([
                'nombre' => 'AGI 2',
                'id_nivel1' => $nivel1->id,
            ]);

            $nivel3 = Nivel3::create([
                'nombre' => 'AGI 3',
                'id_nivel2' => $nivel2->id,
            ]);

            $nivel4 = Nivel4::create([
                'nombre' => 'AGI 4',
                'id_nivel3' => $nivel3->id,
            ]);

            // ASIGNAR USUARIOS A NIVELES EN usuarios_niveles
            // Map en orden de creación:
            // usuariosInternos[0] -> nivel 1 (AGI)
            // usuariosInternos[1] -> nivel 2 (AGI 2)
            // usuariosInternos[2] -> nivel 3 (AGI 3)
            // usuariosInternos[3] -> nivel 4 (AGI 4)

            $mapUsuarioNivel = [
                [$usuariosInternos[0]->id, 1, $nivel1->id],
                [$usuariosInternos[1]->id, 2, $nivel2->id],
                [$usuariosInternos[2]->id, 3, $nivel3->id],
                [$usuariosInternos[3]->id, 4, $nivel4->id],
            ];

            foreach ($mapUsuarioNivel as [$idUsuario, $nivelNumero, $idNivel]) {
                UsuariosNiveles::updateOrCreate(
                    [
                        'id_usuario' => $idUsuario,
                        'nivel' => $nivelNumero,
                    ],
                    [
                        'id_nivel' => $idNivel,
                    ]
                );
            }

            // PROCEDIMIENTO "Rectificación" asignado a AGI 4
            // Se crea como procedimiento cuyo nivel final es 4 y nivel final = AGI 4

            $procedimiento = Procedimiento::firstOrCreate(
                ['nombre' => 'Rectificación'],
                [
                    'id_area' => $nivel1->id,
                    'id_subarea' => $nivel2->id,
                    'nivel_final' => '4',
                    'id_nivel_final' => $nivel4->id,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );

            // Asignar el procedimiento al usuario que está en AGI 4 (el cuarto interno creado)
            $usuarioNivel4 = $usuariosInternos[3];

            ProcedimientoUsuario::updateOrCreate(
                [
                    'id_procedimiento' => $procedimiento->id,
                    'id_usuario' => $usuarioNivel4->id,
                ],
                []
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

