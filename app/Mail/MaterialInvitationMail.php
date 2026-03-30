<?php

namespace App\Mail;

use App\Models\Material;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class MaterialInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $material;
    public $email;
    public $enrollmentUrl;

    public function __construct(Material $material, $email)
    {
        $this->material = $material;
        $this->email = $email;
        
        // Create a signed URL that expires in 7 days for security
        $this->enrollmentUrl = URL::temporarySignedRoute(
            'student.materials.enroll', 
            now()->addDays(7), 
            ['material' => $material->id, 'email' => $email]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to a new Module: ' . $this->material->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            // Make sure to create this blade view in resources/views/emails/material_invite.blade.php
            view: 'emails.material-invite', 
        );
    }
}