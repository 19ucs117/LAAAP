<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class migrateOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate_in_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $migrations = [
          '2021_07_18_091641_create_vission_missions_table.php',
          '2021_07_19_045336_create_schools_table.php',
          '2013_03_21_184320_create_departments_table.php',
          '2021_07_19_045516_create_programs_table.php',
          '2013_03_21_184448_create_roles_table.php',
          '2014_10_12_000000_create_users_table.php',
          '2021_07_02_062344_create_course_codes_table.php',
          '2021_07_22_192157_create_peos_table.php',
          '2021_07_24_061410_create_pos_table.php',
          '2021_08_31_164713_create_missionvisionpeos_table.php',
          '2021_08_30_101146_create_peopos_table.php',
          '2021_07_06_032216_create_syllabus_assigns_table.php',
          '2021_07_24_070343_create_psos_table.php',
          '2021_08_30_082625_create_psopos_table.php',
          '2021_09_01_031721_create_peopsos_table.php',
          '2021_07_30_055925_create__course_overviews_table.php',
          '2021_07_27_195403_create__course_objectives_table.php',
          '2021_07_27_195845_create_prerequisites_table.php',
          '2021_07_27_200014_create__add_units_table.php',
          '2021_07_27_200138_create__text_books_table.php',
          '2021_07_27_200244_create__reference_books_table.php',
          '2021_07_27_200343_create__web_references_table.php',
          '2021_09_26_183834_create_cos_table.php',
          '2021_08_30_082715_create_copsos_table.php',
          '2021_09_27_032705_create_justificationcopsos_table.php',
          '2021_08_30_082644_create_pocos_table.php',
          '2021_09_27_032559_create_justificationcopos_table.php',
          '2021_09_27_032020_create_copeos_table.php',
          '2021_09_27_032511_create_justificationcopeos_table.php',
          '2021_10_26_103802_create_dynamic_lesson_plans_table.php',
          '2022_03_16_030621_create_studentmarks_table',
          '2021_11_01_072844_create_batch_details_table.php',
          '2021_11_06_082717_create_student_details_table.php',
          '2021_11_01_073213_create_selected_subjects_table.php',
          '2021_11_01_072646_create_assignstaffs_table.php',
          '2021_11_01_072947_create_exammarks_table.php',

        ];
        foreach ($migrations as $migration) {
          $basePath = 'database/migrations/';
          $migrationName = trim($migration);
          $path = $basePath.$migrationName;
          $this->call('migrate',['--path' => $path]);
        }
    }
}
