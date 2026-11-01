<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Membuat akun admin
        |--------------------------------------------------------------------------
        */

        $admin = User::updateOrCreate(
            ['email' => 'admin@ecochallenge.test'],
            [
                'name' => 'Admin EcoChallenge',
                'username' => 'admin',
                'password' => 'Admin123!',
                'role' => 'admin',
                'is_active' => true,
                'total_points' => 0,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Membuat kategori awal
        |--------------------------------------------------------------------------
        */

        $categories = [
            [
                'name' => 'Transportasi',
                'description' => 'Aktivitas ramah lingkungan dalam transportasi.',
            ],
            [
                'name' => 'Energi',
                'description' => 'Aktivitas untuk menghemat penggunaan energi.',
            ],
            [
                'name' => 'Pengelolaan Sampah',
                'description' => 'Aktivitas mengurangi dan mengelola sampah.',
            ],
            [
                'name' => 'Gaya Hidup',
                'description' => 'Kebiasaan sehari-hari yang lebih ramah lingkungan.',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Membuat challenge awal
        |--------------------------------------------------------------------------
        */

        $challenges = [
            [
                'category' => 'Transportasi',
                'title' => 'Berjalan Kaki ke Tujuan Terdekat',
                'description' => 'Pilih berjalan kaki untuk mencapai tujuan yang dekat.',
                'difficulty' => 'mudah',
                'points' => 20,
                'proof_criteria' => 'Unggah foto sebagai bukti aktivitas.',
                'estimated_co2e_gram' => 500,
                'emission_source' => 'Estimasi perjalanan kendaraan yang dihindari.',
            ],
            [
                'category' => 'Energi',
                'title' => 'Matikan Lampu Saat Tidak Digunakan',
                'description' => 'Matikan lampu ketika meninggalkan ruangan.',
                'difficulty' => 'mudah',
                'points' => 10,
                'proof_criteria' => 'Unggah foto kondisi lampu atau ruangan.',
                'estimated_co2e_gram' => 100,
                'emission_source' => 'Estimasi penghematan energi listrik.',
            ],
            [
                'category' => 'Pengelolaan Sampah',
                'title' => 'Pisahkan Sampah Berdasarkan Jenisnya',
                'description' => 'Pisahkan sampah organik dan anorganik.',
                'difficulty' => 'sedang',
                'points' => 25,
                'proof_criteria' => 'Unggah foto sampah yang sudah dipilah.',
                'estimated_co2e_gram' => 200,
                'emission_source' => 'Estimasi dampak pengelolaan sampah.',
            ],
            [
                'category' => 'Gaya Hidup',
                'title' => 'Gunakan Botol Minum Isi Ulang',
                'description' => 'Bawa botol minum sendiri untuk mengurangi sampah plastik.',
                'difficulty' => 'mudah',
                'points' => 15,
                'proof_criteria' => 'Unggah foto botol minum yang digunakan.',
                'estimated_co2e_gram' => 50,
                'emission_source' => 'Estimasi pengurangan penggunaan botol sekali pakai.',
            ],
        ];

        foreach ($challenges as $challenge) {
            $category = DB::table('categories')
                ->where('name', $challenge['category'])
                ->first();

            DB::table('challenges')->updateOrInsert(
                ['title' => $challenge['title']],
                [
                    'category_id' => $category->id,
                    'created_by' => $admin->id,
                    'description' => $challenge['description'],
                    'difficulty' => $challenge['difficulty'],
                    'points' => $challenge['points'],
                    'is_active' => true,
                    'proof_criteria' => $challenge['proof_criteria'],
                    'estimated_co2e_gram' => $challenge['estimated_co2e_gram'],
                    'emission_source' => $challenge['emission_source'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Membuat artikel awal
        |--------------------------------------------------------------------------
        */

        DB::table('articles')->updateOrInsert(
            ['slug' => 'mulai-hidup-ramah-lingkungan'],
            [
                'author_id' => $admin->id,
                'title' => 'Mulai Hidup Ramah Lingkungan dari Kebiasaan Kecil',
                'excerpt' => 'Langkah sederhana untuk membangun kebiasaan yang lebih ramah lingkungan.',
                'content' => 'Mulailah dengan kebiasaan sederhana, seperti menghemat listrik, membawa botol minum sendiri, memilah sampah, dan memilih transportasi yang lebih ramah lingkungan.',
                'thumbnail_path' => null,
                'is_published' => true,
                'published_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
