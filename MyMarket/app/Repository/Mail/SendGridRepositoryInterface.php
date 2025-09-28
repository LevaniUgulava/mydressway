<?php

namespace App\Repository\Mail;


interface SendGridRepositoryInterface
{
    public function getAllTemplate();
    public function getOneTemplate($template_id);
    public function Activate($template_id, $version_id);
    public function AddTemplate($name, $id);
}
