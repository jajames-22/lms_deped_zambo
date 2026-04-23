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
        // 1. Assign the injected variables to the class properties FIRST
        $this->material = $material;
        $this->email = $email;
        
        // 2. Create the signed URL using the fixed syntax
        $this->enrollmentUrl = URL::temporarySignedRoute(
            'student.materials.enroll', 
            now()->addDays(7), 
            [
                // FIXED: Removed the extra $ before material
                'hashid' => \Vinkla\Hashids\Facades\Hashids::encode($this->material->id), 
                'email' => $this->email
            ]
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
            view: 'emails.material-invite', 
        );
    }
}