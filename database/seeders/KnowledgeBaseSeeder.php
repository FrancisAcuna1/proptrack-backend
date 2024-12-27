<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KnowledgeBase;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KnowledgeBase::create(['tanong' => 'Paano mag-request ng maintenance?', 'sagot' => 'Para mag-request ng maintenance, pumunta sa aming property management system at i-click ang "Request Maintenance". Sundin ang mga hakbang na ipapakita.']);
        KnowledgeBase::create(['tanong' => 'Paano ayusin ang isyu sa isang item?', 'sagot' => 'Para ayusin ang isyu sa isang item, i-log ang detalye sa property management system at i-submit ang request para sa aming team.']);
    }
}
