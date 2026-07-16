<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MailTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The recipient email address}';

    protected $description = 'Send a test email to verify mail configuration';

    public function handle(): void
    {
        $email = $this->argument('email');

        $this->info("Sending test email to {$email}...");

        try {
            \Illuminate\Support\Facades\Mail::raw('This is a test email sent from the '.config('app.name').' application to verify that the mail configuration is working correctly.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email from '.config('app.name'));
            });

            $this->info('Test email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send test email: '.$e->getMessage());
        }
    }
}
