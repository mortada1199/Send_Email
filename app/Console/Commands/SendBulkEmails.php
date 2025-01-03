<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\BulkEmail;
use App\Mail\ONB;
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
        $emails = DB::table('emails1')
            ->where('sent', 0)
            ->limit(2000)
            ->get();

        if ($emails->isEmpty()) {
            $this->info('No emails to send.');
            return;
        }
    

        foreach ($emails as $email) {
            try {
                // إرسال الإيميل
                Mail::to($email->EMAIL)->send(new ONB([
                    'EMAIL' => $email->EMAIL,
                    'CUS_NUM' => $email->CUS_NUM,
                   

                ]));

                // تحديث حالة الإيميل إلى sent = true
                DB::table('emails1')
                    ->where('id', $email->id)
                    ->update(['sent' => 1]);

                $this->info("Email sent to: {$email->EMAIL}");
            } catch (\Exception $e) {
                $this->error("Failed to send email to: {$email->EMAIL}. Error: " . $e->getMessage());
            }
        }

        $this->info('Batch email sending completed.');
    }
}
