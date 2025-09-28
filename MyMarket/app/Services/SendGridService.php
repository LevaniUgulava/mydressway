<?php

namespace App\Services;

use App\Models\Marketing;
use App\Models\Sendgridtemplate;
use Exception;
use SendGrid;
use Illuminate\Support\Facades\Config;
use SendGrid\Mail\Mail;

class SendGridService
{

    protected SendGrid $client;

    public function __construct()
    {
        $this->client = new SendGrid(Config::get("services.sendgrid.key"));
    }

    public function sendEmail(string $to_email, string $to_name, array $data, string $templateKey)
    {
        $template = Sendgridtemplate::where('key', $templateKey)->firstOrFail();

        $email = new Mail();
        $email->setFrom(config('services.sendgrid.from'), config('services.sendgrid.from_name'));
        $email->addTo($to_email, $to_name);
        $email->setTemplateId($template->template_id);

        $email->addDynamicTemplateDatas($data);

        $email->setMailSettings([
            'sandbox_mode' => ['enable' => false]
        ]);

        try {
            $response = $this->client->send($email);
            return response()->json([
                'response' => $response->body(),
                'statusCode' => $response->statusCode(),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function addContact($email, $source)
    {
        $request_body = [
            "contacts" => [
                [
                    "email" => $email,
                ]
            ]
        ];

        try {
            if (Marketing::where('email', $email)->exists()) {
                return response()->noContent(409);
            }
            Marketing::create([
                'email' => $email,
                'source' => $source,
                'consented_at' => now()
            ]);
            $response = $this->client->client->marketing()->contacts()->put($request_body);
            return $response->statusCode();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
