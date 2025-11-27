<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Datos de prueba - se pueden modificar según necesidades
        
        // Insertar niveles
        DB::table('niveles')->insert([
            ['codigo' => 'NIV01', 'nombre' => 'Nivel 1 - Atención Inicial', 'descripcion' => 'Primer nivel de atención', 'orden' => 1, 'activo' => true],
            ['codigo' => 'NIV02', 'nombre' => 'Nivel 2 - Procesamiento', 'descripcion' => 'Segundo nivel de procesamiento', 'orden' => 2, 'activo' => true],
            ['codigo' => 'NIV03', 'nombre' => 'Nivel 3 - Aprobación', 'descripcion' => 'Tercer nivel de aprobación', 'orden' => 3, 'activo' => true],
        ]);
        
        // Insertar áreas
        DB::table('areas')->insert([
            ['codigo' => 'ARE01', 'nombre' => 'Recepción', 'descripcion' => 'Área de recepción de documentos', 'nivel' => 1, 'area_padre_id' => null, 'activo' => true],
            ['codigo' => 'ARE02', 'nombre' => 'Archivo', 'descripcion' => 'Área de archivo y registro', 'nivel' => 1, 'area_padre_id' => null, 'activo' => true],
            ['codigo' => 'ARE03', 'nombre' => 'Evaluación', 'descripcion' => 'Área de evaluación de trámites', 'nivel' => 2, 'area_padre_id' => null, 'activo' => true],
            ['codigo' => 'ARE04', 'nombre' => 'Aprobación', 'descripcion' => 'Área de aprobación final', 'nivel' => 3, 'area_padre_id' => null, 'activo' => true],
        ]);
        
        // Insertar procedimientos de ejemplo
        DB::table('procedimientos')->insert([
            [
                'codigo' => 'PROC001', 
                'nombre' => 'Registro de Documentos', 
                'descripcion' => 'Procedimiento para el registro inicial de documentos',
                'tipo' => 'ADMINISTRATIVO',
                'dias_tiempo_limite' => 5,
                'costo' => 0.00,
                'moneda' => 'PEN',
                'requiere_digital' => false,
                'activo' => true
            ],
            [
                'codigo' => 'PROC002', 
                'nombre' => 'Evaluación Técnica', 
                'descripcion' => 'Procedimiento de evaluación técnica de documentos',
                'tipo' => 'TECNICO',
                'dias_tiempo_limite' => 15,
                'costo' => 50.00,
                'moneda' => 'PEN',
                'requiere_digital' => true,
                'activo' => true
            ],
            [
                'codigo' => 'PROC003', 
                'nombre' => 'Aprobación Final', 
                'descripcion' => 'Procedimiento de aprobación final de trámites',
                'tipo' => 'ADMINISTRATIVO',
                'dias_tiempo_limite' => 3,
                'costo' => 0.00,
                'moneda' => 'PEN',
                'requiere_digital' => false,
                'activo' => true
            ],
        ]);
        
        // Insertar usuarios de ejemplo
        DB::table('usuarios')->insert([
            [
                'dni' => '12345678',
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'correo' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'rol' => 'ADMINISTRADOR',
                'area_id' => 1,
                'nivel_id' => 1,
                'activo' => true,
                'email_verified_at' => now(),
            ],
            [
                'dni' => '87654321',
                'nombre' => 'Operador',
                'apellido' => 'Sistema',
                'correo' => 'operador@example.com',
                'password' => Hash::make('password123'),
                'rol' => 'OPERADOR',
                'area_id' => 2,
                'nivel_id' => 2,
                'activo' => true,
                'email_verified_at' => now(),
            ],
            [
                'dni' => '11111111',
                'nombre' => 'Usuario',
                'apellido' => 'Normal',
                'correo' => 'usuario@example.com',
                'password' => Hash::make('password123'),
                'rol' => 'USUARIO',
                'area_id' => 3,
                'nivel_id' => 2,
                'activo' => true,
                'email_verified_at' => now(),
            ],
        ]);
        
        $this->command->info('Datos de prueba insertados correctamente.');
        $this->command->warn('Usuarios de prueba:');
        $this->command->table(
            ['Email', 'Rol', 'Contraseña'],
            [
                ['admin@example.com', 'ADMINISTRADOR', 'password123'],
                ['operador@example.com', 'OPERADOR', 'password123'],
                ['usuario@example.com', 'USUARIO', 'password123'],
            ]
        );
    }
}
