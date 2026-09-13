<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        $basePath = database_path('data/ubigeo');

        DB::transaction(function () use ($basePath) {
            // DEPARTAMENTOS
            $departamentos = $this->leerCsv("{$basePath}/1_ubigeo_departamentos.csv", 'departamento', []);
            foreach (array_chunk($departamentos, 500) as $chunk) {
                DB::table('departamentos')->upsert($chunk, ['id'], ['nombre']);
            }

            // PROVINCIAS
            $provincias = $this->leerCsv("{$basePath}/2_ubigeo_provincias.csv", 'provincia', ['departamento_id']);
            foreach (array_chunk($provincias, 500) as $chunk) {
                DB::table('provincias')->upsert($chunk, ['id'], ['nombre', 'departamento_id']);
            }

            // DISTRITOS (solo id, nombre y provincia_id; se ignora departamento_id del CSV)
            $distritos = $this->leerCsv("{$basePath}/3_ubigeo_distritos.csv", 'distrito', ['provincia_id']);
            foreach (array_chunk($distritos, 1000) as $chunk) {
                DB::table('distritos')->upsert($chunk, ['id'], ['nombre', 'provincia_id']);
            }

            // Limpieza: borra registros que ya no existen en el CSV (los descendientes se van por cascade)
            DB::table('distritos')->whereNotIn('id', $this->ids($distritos))->delete();
            DB::table('provincias')->whereNotIn('id', $this->ids($provincias))->delete();
            DB::table('departamentos')->whereNotIn('id', $this->ids($departamentos))->delete();

            $this->command->info('Departamentos: ' . count($departamentos) . ' procesados');
            $this->command->info('Provincias: ' . count($provincias) . ' procesadas');
            $this->command->info('Distritos: ' . count($distritos) . ' procesados');
        });
    }

    /**
     * Lee un CSV y devuelve filas con las columnas que existen en el esquema.
     */
    private function leerCsv(string $path, string $nombreColumna, array $extraCols): array
    {
        $filas = [];
        $file = fopen($path, 'r');
        $headers = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($headers, $row);
            $fila = ['id' => (int) $data['id'], 'nombre' => $data[$nombreColumna]];
            foreach ($extraCols as $col) {
                $fila[$col] = (int) $data[$col];
            }
            $filas[] = $fila;
        }
        fclose($file);

        return $filas;
    }

    private function ids(array $filas): array
    {
        return array_map(fn ($f) => $f['id'], $filas);
    }
}