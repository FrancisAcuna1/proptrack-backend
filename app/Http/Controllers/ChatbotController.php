<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\KnowledgeBase;
use App\Models\Deliquent;
use App\Models\PaymentTransactions;
use App\Models\RentalAgreement;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    protected $maxInputTokens = 40; // Maximum token limit for user input
    protected $maxOutputTokens = 250; 
    protected $fallbackResponse = 'Pasensya na, hindi ko alam ang sagot sa tanong na iyan. May mga limitasyon ang aking kaalaman.';

    public function query(Request $request)
    {
        try {
            $apiKey = env('OPENAI_API_KEY');
            if (!$apiKey) {
                throw new \Exception('OpenAI API Key is not set.');
            }
            $validatedData = $request->validate([
                'query' => 'required|string|max:255'
            ]);

            // Log the validated request data
            Log::info('Chatbot Query Received:', [
                'query' => $validatedData['query'],
                'user_id' => $request->user()->id ?? null, // Log user ID if available
                'ip_address' => $request->ip(), // Log the IP address of the request
                'timestamp' => now() // Log the current timestamp
            ]);

            $userQuery = trim($validatedData['query']);

             // Check if the input exceeds the maximum token limit
            $inputTokenCount = $this->countTokens($userQuery);
            if ($inputTokenCount > $this->maxInputTokens) {
                return response()->json([
                    'response' => 'Ang iyong tanong ay masyadong mahaba. Mangyaring bawasan ito.'
                ], 400);
            }

             // Log the input token count
            Log::info('Input Token Count:', [
                'user_id' => $request->user()->id ?? null,
                'input_token_count' => $inputTokenCount
            ]);

            if($this->isProgrammingQuery($userQuery)){
                return response()->json([
                    'response' => "Ako'y isang chatbot na nakatuon sa property management. Maaari kitang matulungan sa mga tanong tungkol sa maintenance, rental payment, at iba pa patungkol sa inyong tirahan. Anuman ang iyong katanungan, huwag kang mag-atubiling itanong"
                ]);
            }
            
            if($this->isNextDuedateQuery($userQuery)){
                $userId = $request->user()->id ?? null; // Ensure you have a user ID
                if (!$userId) {
                    return response()->json([
                        'response' => 'Kailangan mong mag-log in upang suriin ang iyong balanse.'
                    ], 403);
                }
                $nextDuedateResponse = $this->checkNextDuedate($userId);
                return response()->json([
                    'response' => $nextDuedateResponse
                ], 200);
            }

            if($this->isServiceQuery($userQuery)){
                $introMessage = "Narito ang mga serbisyo na maaari kong ihandog sa iyo. Puwede kitang matulungan sa mga sumusunod na serbisyo:";
                $servicesArray = $this->Services(); // Ensure this returns an array

                // Return the response as JSON
                return response()->json([
                    'response' => [
                        'intro' => $introMessage,
                        'services' => $servicesArray,
                    ]
                ], 200);
            }
            
            if ($this->isBalanceQuery($userQuery)) {
                $userId = $request->user()->id ?? null; // Ensure you have a user ID
                if (!$userId) {
                    return response()->json([
                        'response' => 'Kailangan mong mag-log in upang suriin ang iyong balanse.'
                    ], 403);
                }
    
                $balanceResponse = $this->checkBalance($userId); // return query value

                $balanceOutputTokenCount = $this->countTokens($balanceResponse); // this code is for checking the output token
                Log::info('Local Output Token Count:', [
                    'user_id' => $request->user()->id ?? null,
                    'local_output_token_count' => $balanceOutputTokenCount
                ]);

                return response()->json(['response' => $balanceResponse]);
            }

            // If no local match, use AI model
            $aiResponse = $this->generateAIResponse($userQuery);

            $outputTokenCount = $this->countTokens($aiResponse);
            if ($outputTokenCount > $this->maxOutputTokens) {
                $aiResponse = substr($aiResponse, 0, $this->maxOutputTokens); // Truncate the response
            }
           
            Log::info('Output Token Count:', [
                'user_id' => $request->user()->id ?? null,
                'output_token_count' => $outputTokenCount
            ]);

            return response()->json(['response' => $aiResponse]);

        } catch (\Exception $e) {
            // Log the error and return a generic error response
            Log::error('Chatbot Query Error: ' . $e->getMessage());
            
            return response()->json([
                'response' => $this->fallbackResponse,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function Services()
    {
        try{
            $services = [
                ['id' => 1, 'name' => 'Paano mag request ng maintenance'],
                ['id' => 2, 'name' => 'Suriin ang balanse'],
                ['id' => 3, 'name' => 'Susunod na petsa ng bayarin'],
            ];
            return response()->json(['services' => $services], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching services: ' . $e->getMessage());
            return response()->json([
                'response' => $this->fallbackResponse,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Check local knowledge base for exact or partial match
     */
    protected function isServiceQuery($query){
        $serviceKeywords = ['serbisyo', 'service', 'services'];
        foreach($serviceKeywords as $keyword){
            if (stripos($query, $keyword) !== false){
                return true;
            }
        }
        return false;
    }

    protected function isNextDuedateQuery($query)
    {   // Define keywords related to next due date or payment queries
        $duedateKeywords = [
            'susunod na petsa ng bayarin', 'next payment', 'duedate', 'due date', 'next duedate', 'next due date', 'sunod na bayad',
            'kailan ang sunod na bayaran', 'kailan ang bayaran', 'kailan ang susunod na bayad', 'next bill date', 'next billing cycle',
            'kailan babayaran', 'deadline ng bayad', 'due date ng bayad', 'bayarin deadline', 
            'kailan ang deadline ng bayad', 'kailan ang takdang bayad', 'due date', 'upcoming payment'
        ];

        foreach ($duedateKeywords as $keyword) {
            if (stripos($query, $keyword) !== false) {
                return true; // Found a keyword related to the next due date
            }
        }

        return false; 
    }

    public function checkNextDuedate($id)
    {
        $paymentDetails = PaymentTransactions::where('tenant_id', $id)
        ->whereIn('transaction_type', ['Rental Fee', 'Initial Payment', 'Advance Payment'])
        ->orderBy('date', 'desc') // Order by latest payment date
        ->get();

        $rentalFee = RentalAgreement::where('tenant_id', $id)
        ->pluck('rental_fee')
        ->first();

        if(!$rentalFee){
            return response()->json([
                'response' => 'Walang mahanap na datos sa',
            ], 204);
        }

         // Check if there are any relevant payments
        if ($paymentDetails->isEmpty()) {
            return response()->json([
                'response' => 'Walang pang naitalang bayad'
            ], 204);
        }

        $lastPayment = $paymentDetails->first();
        $lastPaymentDate = Carbon::parse($lastPayment->date);
        $totalMonthsCovered = 0;

        $groupedPayments = $paymentDetails->groupBy(function ($payment) {
            return Carbon::parse($payment->date)->format('Y-m-d'); // Group by exact date
        });

        if ($lastPayment->transaction_type === 'Rental Fee') {
            $totalMonthsCovered = $lastPayment->months_covered ?? 1; 
        } else {

            $groupedPayments = $paymentDetails->groupBy(function ($payment) {
                return Carbon::parse($payment->date)->format('Y-m-d'); // Group by exact date
            });
    
            foreach ($groupedPayments as $date => $payments) {
                // Only consider the date of the last payment
                if ($date === $lastPaymentDate->format('Y-m-d')) {
                    foreach ($payments as $payment) {
                        if (in_array($payment->transaction_type, ['Initial Payment', 'Advance Payment'])) {
                            $totalMonthsCovered += $payment->months_covered ?? 0; // Add months covered
                        }
                    }
                    break; // Only process the last payment's date
                }
            }
        }

        $nextDuedate = $lastPaymentDate->addMonths($totalMonthsCovered);
        $formattedNextDuedate = $nextDuedate->format('F j, Y');

        Log::info('NextDuedate Data:', [
            'date' => $lastPayment ?? null,
        ]);
        Log::info('Months Covered:', [
            'total' => $totalMonthsCovered,
            'group' => $groupedPayments
        ]);


        Log::info('NextDuedate Data:', [
            'date' => $formattedNextDuedate ?? null,
            'amount' => $rentalFee
        ]);
        return 'Ang susunod na petsa ng bayarin ay: '.$formattedNextDuedate."\n".'Sa halaga na '.$rentalFee.' pesos';
    }

    protected function isBalanceQuery($query)
    {
        // Define keywords related to balance or payments
        $balanceKeywords = ['balance', 'balance', 'overdue', 'amount due', 'total', 'bill'];

        foreach ($balanceKeywords as $keyword) {
            if (stripos($query, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function checkBalance($Id)
    {
        $totalOverdue = Deliquent::where('tenant_id', $Id)
        ->where('status', 'Overdue')
        ->sum('amount_overdue'); 

      
        $dueDates = Deliquent::where('tenant_id', $Id)
        ->where('status', 'Overdue')
        ->pluck('month_overdue'); 

   
        $formattedDueDates = [];
        foreach ($dueDates as $dueDate) {
            $formattedDueDates[] = Carbon::parse($dueDate)->format('F j, Y'); 
        }   
        $dueDatesString = implode(', ', $formattedDueDates);
        return $totalOverdue > 0 ? 
        "Ang iyong kabuuang overdue na balanse ay: " . $totalOverdue  . " ito ay para sa mga petsa ng " . $dueDatesString : 
        "Sa ngayon Wala kang overdue na balanse.";
    }

    protected function isProgrammingQuery($query)
    {
        // Define keywords related to programming
        $programmingKeywords = [
            'programming', 'code', 'development', 'software', 'app', 'print', 'display', 'hello world',
            'loop', 'console.log', 'console', 'python', 'javascript', 'JavaScript', 'js', 'java', 'c++', 
            'c#', 'ruby', 'kotlin', 'mysql', 'database', 'mongodb', 'php', 'laravel', 'code igniter', 
            'swift', 'schema', 'nextjs', 'nodejs', 'typescript', 'api', 'backend', 'frontend', 'framework', 
            'algorithm', 'data structure', 'html', 'css', 'react', 'angular', 'vue', 'bootstrap', 
            'docker', 'kubernetes', 'linux', 'bash', 'shell', 'git', 'github', 'bitbucket', 'vscode', 
            'editor', 'ide', 'debug', 'compile', 'execute', 'query', 'stack', 'overflow', 'binary', 'json', 'xml',
            'how to display', 'how to code', 'write a script', 'how to console.log', 'display a hello world', 'fix this code'
        ];
    
        foreach ($programmingKeywords as $keyword) {
            if (stripos($query, $keyword) !== false) {
                return true; // Return true if a programming-related keyword is found
            }
        }
    }
    /**
     * Generate AI response with token limitation
     */
    protected function generateAIResponse($query)
    {
        try {
            if ($this->countTokens($query) > $this->maxInputTokens) {
                return 'Ang iyong tanong ay masyadong mahaba. Mangyaring bawasan mo ito o mas paikliin ng kaunti';
            }

            $result = OpenAI::chat()->create([
                'model' => 'ft:gpt-3.5-turbo-0125:the-lewis-college:smartlease-bot:Ahhb4bgi',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful chatbot assistant named SmartLease Bot for a property management system responding in Tagalog. You assist with property-related concerns, maintenance requests, tenant concerns, and portal guidance.'],
                    ['role' => 'user', 'content' => $query]
                ],
                'max_tokens' => $this->maxOutputTokens,
                'temperature' => 0.7,
            ]);

            $aiResponse = trim($result->choices[0]->message->content);

            // Fallback if no response generated
            return $aiResponse ?: $this->fallbackResponse;

        } catch (\Exception $e) {
            Log::error('AI Response Generation Error: ' . $e->getMessage());
            return $this->fallbackResponse;
        }
    }

    protected function countTokens($text)
    {
        // This is a simple implementation; you may want to use a more accurate tokenization method
        return str_word_count($text); // Count words as a proxy for tokens
    }

    // protected function checkLocalKnowledgeBase($query)
    // {
    //     $response = KnowledgeBase::where('tanong', 'LIKE', '%' . $query . '%')
    //         ->first();

    //     return $response ? $response->sagot : null;
    // }

    #old code
    // First, check local knowledge base
    // $localResponse = $this->checkLocalKnowledgeBase($userQuery);
            
    // if ($localResponse) {
    //     $localOutputTokenCount = $this->countTokens($localResponse);
    //     Log::info('Local Output Token Count:', [
    //         'user_id' => $request->user()->id ?? null,
    //         'local_output_token_count' => $localOutputTokenCount
    //     ]);
    //     return response()->json(['response' => $localResponse]);
    // }

}