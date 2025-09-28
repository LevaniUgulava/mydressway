<?php

namespace App\Repository\Mail;

use App\Models\Sendgridtemplate;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use SendGrid;

class SendGridRepository implements SendGridRepositoryInterface
{
    protected SendGrid $sg;
    public function __construct()
    {
        $this->sg = new SendGrid(Config::get("services.sendgrid.key"));
    }
    public function getAllTemplate()
    {
        try {
            $resp = $this->sg->client->templates()->get(null, [
                'page_size'   => 200,
                'generations' => 'dynamic',
            ]);
            $templates = json_decode($resp->body(), true)['result'] ?? [];

            $existing = Sendgridtemplate::pluck('template_id')->toArray();

            $mapped = collect($templates)->map(function ($tpl) use ($existing) {
                $tpl['is_added'] = in_array($tpl['id'], $existing);
                return $tpl;
            });


            return $mapped;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function getOneTemplate($template_id)
    {
        try {
            $resp = $this->sg->client->templates()->_($template_id)
                ->get(null, [
                    'page_size'   => 200,
                    'generations' => 'dynamic',
                ]);
            return json_decode($resp->body(), true);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function Activate($template_id, $version_id)
    {
        try {
            $resp = $this->sg->client->templates()->_($template_id)->versions()->_($version_id)->activate()->post();
            return $resp->statusCode();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function AddTemplate($key, $template_id)
    {
        try {

            $response = Sendgridtemplate::create([
                'key' => $key,
                'template_id' => $template_id
            ]);

            return response()->json([
                'data' => $response
            ], 201);
        } catch (Exception $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Key ან Template ID უკვე არსებობს ბაზაში.'
                ], 409);
            }

            return response()->json([
                'message' => 'დამატების შეცდომა',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
