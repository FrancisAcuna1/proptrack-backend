<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\RecurringExpenses;
use App\Http\Controllers\RecurringController;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\GenerateRecurringExpensesCommand::class,
    ];
    /**
     * Define the application's command schedule.
     */
    // protected function schedule(Schedule $schedule): void
    // { 
    //     $schedule->command('recurring:generate-expenses')
    //     ->monthly(); // Changed from daily to monthly as per your requirement
    //     // $schedule->call([RecurringController::class, 'Generate_Pending_Expenses'])
    //     // ->daily();
    //     // $schedule->command('inspire')->hourly();
    // }
    protected function schedule(Schedule $schedule): void
    {
        // Retrieve active recurring expenses
        $recurringExpenses = RecurringExpenses::where('status', 'active')->get();

        foreach ($recurringExpenses as $expense) {
            switch ($expense->frequency) {
                case 'daily':
                    $schedule->command('recurring:generate-expenses')
                        ->daily()
                        ->when(fn() => $this->shouldGenerate($expense));
                    break;

                case 'weekly':
                    $schedule->command('recurring:generate-expenses')
                        ->weekly()
                        ->when(fn() => $this->shouldGenerate($expense));
                    break;

                case 'monthly':
                    $schedule->command('recurring:generate-expenses')
                        ->monthly()
                        ->when(fn() => $this->shouldGenerate($expense));
                    break;

                case 'yearly':
                    $schedule->command('recurring:generate-expenses')
                        ->yearly()
                        ->when(fn() => $this->shouldGenerate($expense));
                    break;

                default:
                    // Handle invalid frequency if necessary
                    break;
            }
        }
    }

    protected function shouldGenerate($expense): bool
    {
        $currentDate = now();
        $lastGenerated = $expense->last_generated_date 
            ? \Carbon\Carbon::parse($expense->last_generated_date) 
            : \Carbon\Carbon::parse($expense->startDate);

        switch ($expense->frequency) {
            case 'daily':
                return $lastGenerated->diffInDays($currentDate) >= 1;

            case 'weekly':
                return $lastGenerated->diffInWeeks($currentDate) >= 1;

            case 'monthly':
                return $lastGenerated->diffInMonths($currentDate) >= 1;

            case 'yearly':
                return $lastGenerated->diffInYears($currentDate) >= 1;

            default:
                return false;
        }
    }

  

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
