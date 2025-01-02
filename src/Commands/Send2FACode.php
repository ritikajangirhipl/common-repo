<?php

namespace Vendor\CommonPackage\Commands;

use Illuminate\Console\Command;

class Send2FACode extends Command
{
    // The name and signature of the console command
    protected $signature = 'sending:2fa {email} {code}';

    // The console command description
    protected $description = 'Send a 2FA code to a specified email address';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->argument('email');
        $code  = $this->argument('code');

        $senderAddress = config('common.sender_address');
        $senderName    = config('common.sender_name');
        $bodyTemplate  = config('common.2fa_body');
        $subject       = config('common.2fa_subject');
        $body = sprintf($bodyTemplate, $code);
        \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($email, $subject, $senderAddress, $senderName) {
            $message->to($email)
                    ->subject($subject)
                    ->from($senderAddress, $senderName);
        });
        //gomail -t <to_email_address> -f <email_sender_address> -n <email_sender_name> -b '<email_2fa_body>'
        $command = escapeshellcmd("gomail -t $email -f $senderAddress -s \"$subject\" -n \"$senderName\" -b \"$body\"");

        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            $this->error('Error ' . $returnVar . ' sending 2FA email to ' . $email);
        } else {
            $this->info('2FA email sent to ' . $email);
        }
    }
}
