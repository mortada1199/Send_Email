<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\BulkEmail;
use Illuminate\Support\Facades\DB;

class SendBulkEmails extends Command
{
    protected $signature = 'emails:send-bulk';
    protected $description = 'Send bulk emails to 10 users at a time';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // جلب أول 10 إيميلات لم يتم إرسالها
        $emails = DB::table('emails')
            ->where('sent', false)
            ->limit(10)
            ->get();

        if ($emails->isEmpty()) {
            $this->info('No emails to send.');
            return;
        }
    

        foreach ($emails as $email) {
            try {
                // إرسال الإيميل
                Mail::to($email->email)->send(new BulkEmail([
                    'email' => $email->email,
                    'customer_number' => $email->customer_number,
                ]));

                // تحديث حالة الإيميل إلى sent = true
                DB::table('emails')
                    ->where('id', $email->id)
                    ->update(['sent' => true]);

                $this->info("Email sent to: {$email->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send email to: {$email->email}. Error: " . $e->getMessage());
            }
        }

        $this->info('Batch email sending completed.');
    }
}
