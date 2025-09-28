<?php

namespace App\Http\Controllers;

use App\Repository\Mail\SendGridRepositoryInterface;
use App\Services\SendGridService;
use Illuminate\Http\Request;

class SendGridController extends Controller
{

    protected SendGridRepositoryInterface $sendGridRepository;
    protected SendGridService $sg;

    public function __construct(SendGridRepositoryInterface $sendGridRepository, SendGridService $sg)
    {
        $this->sendGridRepository = $sendGridRepository;
        $this->sg = $sg;
    }

    public function getAllTemplate()
    {
        $response = $this->sendGridRepository->getAllTemplate();
        return $response;
    }
    public function getOneTemplate($id)
    {
        $response = $this->sendGridRepository->getOneTemplate($id);
        return $response;
    }
    public function Activate($template_id, $version_id)
    {
        $response = $this->sendGridRepository->Activate($template_id, $version_id);
        return $response;
    }
    public function AddTemplate(Request $request)
    {
        $key = $request->key;
        $template_id = $request->template_id;
        $response = $this->sendGridRepository->AddTemplate($key, $template_id);
        return $response;
    }
    public function addContact(Request $request)
    {
        return $this->sg->addContact($request->email, $request->source);
    }
}
