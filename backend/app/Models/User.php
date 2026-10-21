<?php

namespace App\Models;

use App\Notifications\CommerceNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = ['locale' => 'fr', 'is_admin' => false];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $url = URL::temporarySignedRoute('api.v1.verification.verify', now()->addHour(), ['id' => $this->id, 'hash' => sha1($this->getEmailForVerification())]);
        $this->notify((new CommerceNotification($this->locale === 'en' ? 'Verify email' : 'Vérifier votre e-mail', $this->locale === 'en' ? 'Confirm your email address.' : 'Confirmez votre adresse e-mail.', $url))->locale($this->locale));
    }

    public function sendPasswordResetNotification(mixed $token): void
    {
        $url = rtrim(config('commerce.frontend_url'), '/').'/reinitialiser-mot-de-passe/'.rawurlencode($token).'?email='.rawurlencode($this->email);
        $this->notify((new CommerceNotification($this->locale === 'en' ? 'Reset password' : 'Réinitialiser le mot de passe', $this->locale === 'en' ? 'Use this temporary link to reset your password.' : 'Utilisez ce lien temporaire pour réinitialiser votre mot de passe.', $url))->locale($this->locale));
    }
}
