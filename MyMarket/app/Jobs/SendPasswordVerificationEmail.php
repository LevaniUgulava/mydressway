<?php

namespace App\Jobs;

use App\Services\SendGridService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPasswordVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $otp;
    protected $user;
    /**
     * Create a new job instance.
     */
    public function __construct($otp, $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(SendGridService $sg): void
    {
        $sg->sendEmail($this->user->email, $this->user->name, ['code' => $this->otp], 'verification-password');
    }
}
