<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Seed;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Verifica e cadastra os arquivos Seeders
        $path = base_path('database/seeders');
        $files = File::AllFiles($path);
        foreach ($files as $file) {
            $file_name = str_replace('.php', '', $file->getFilename());
            if ($file_name === 'DatabaseSeeder') {
                continue;
            }
            $seeds = Seed::all()->pluck('seed')->toArray();
            if (in_array($file_name, $seeds)) {
                continue;
            }
            $class = "Database\\Seeders\\" . $file_name;
            $seeder = new $class;
            $order = $seeder->executionOrder();

            $seed = new Seed;
            $seed->seed = $file_name;
            $seed->order = $order;
            $seed->run = 0;
            $seed->save();
        }

        // Pesquisa os seeds não executados
        $seeds = Seed::where('run', 0)->get();
        
        // Inicia o registro de transações no banco de dados
        DB::beginTransaction();

        try {

            if (count($seeds) > 0) {
                // Ordena os seeds
                $seeds = $seeds->sortBy('order');
    
                // Determino qual será o número do batch
                $batch = Seed::max('batch');
                $batch++;
    
                // Executa os seeds
                foreach ($seeds as $seed) {
                    $name = "Database\\Seeders\\" . $seed->seed;
                    $this->call($name);
                    $seed->run = 1;
                    $seed->batch = $batch;
                    $seed->save();
                }
            }

            // Se não houver erros, confirma as alterações no banco de dados
            DB::commit();

        } catch (\Throwable $e) {

            //Se houver erro, reverte as alterações no banco de dados
            DB::rollBack();

            //Retorna o erro
            throw $e;
        }
        
    }
}
