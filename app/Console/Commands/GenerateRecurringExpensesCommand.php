<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RecurringController;
use Illuminate\Support\Facades\Log;


class GenerateRecurringExpensesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:generate-recurring-expenses-command';
    protected $signature = 'recurring:generate-expenses';
    protected $description = 'Generate expenses for all active recurring expenses';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(RecurringController $recurringController)
    {
        try{
            $this->info('Starting recurring expenses generation...');
        
            $result = $recurringController->Generate_Pending_Expenses();
            
            if ($result->getStatusCode() == 200) {
                $this->info('Recurring expenses generated successfully');
                return Command::SUCCESS;
            }

            // Log successful generation
            Log::info('Recurring Expenses Generated', [
            'processed_count' => count($data['results'] ?? [])
            ]);

            // Log failure if status is not 200
            Log::error('Failed to generate recurring expenses', [
                'status' => $result->getStatusCode(),
                'message' => $result->getData(true)['message'] ?? 'Unknown error'
            ]);
            
            $this->error('Failed to generate recurring expenses');
            return Command::FAILURE;

        }catch (\Exception $e) {
            // Catch any unexpected errors
            Log::error('Unexpected error in recurring expenses generation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Unexpected error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
