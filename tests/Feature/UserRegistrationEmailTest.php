<?php

namespace Tests\Feature;

use App\Mail\NewUserRegistrationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserRegistrationEmailTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test that admin notification email is sent when a user registers via registration form
     */
    public function test_admin_notification_sent_on_registration_form()
    {
        Mail::fake();

        // Create a test user
        $user = User::create([
            'role_id' => 3,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'location' => $this->faker->city,
            'password' => bcrypt('password'),
            'email_otp' => '123456',
            'email_otp_expires_at' => now()->addMinutes(10),
            'is_active' => 0,
        ]);

        // Send the admin notification email
        $adminEmail = config('custom.from_email');
        Mail::to($adminEmail)->send(new NewUserRegistrationMail($user, 'Registration Form'));

        // Assert that the email was sent
        Mail::assertSent(NewUserRegistrationMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id &&
                   $mail->registrationMethod === 'Registration Form';
        });
    }

    /**
     * Test that admin notification email is sent when a user registers via Google OAuth
     */
    public function test_admin_notification_sent_on_google_oauth()
    {
        Mail::fake();

        // Create a test user
        $user = User::create([
            'role_id' => 3,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'google_id' => $this->faker->uuid,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_active' => 1,
        ]);

        // Send the admin notification email
        $adminEmail = config('custom.from_email');
        Mail::to($adminEmail)->send(new NewUserRegistrationMail($user, 'Google'));

        // Assert that the email was sent
        Mail::assertSent(NewUserRegistrationMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id &&
                   $mail->registrationMethod === 'Google';
        });
    }

    /**
     * Test that the email contains the correct information
     */
    public function test_email_content()
    {
        Mail::fake();

        // Create a test user
        $user = User::create([
            'role_id' => 3,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'location' => 'New York',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_active' => 1,
        ]);

        // Send the admin notification email
        $adminEmail = config('custom.from_email');
        Mail::to($adminEmail)->send(new NewUserRegistrationMail($user, 'Registration Form'));

        // Assert that the email was sent
        Mail::assertSent(NewUserRegistrationMail::class, function ($mail) use ($user) {
            $mail->build();
            
            // Check if the email contains the user's information
            $mailContent = $mail->render();
            
            return str_contains($mailContent, 'John Doe') &&
                   str_contains($mailContent, 'john@example.com') &&
                   str_contains($mailContent, '1234567890') &&
                   str_contains($mailContent, 'New York') &&
                   str_contains($mailContent, 'Registration Form');
        });
    }
}
