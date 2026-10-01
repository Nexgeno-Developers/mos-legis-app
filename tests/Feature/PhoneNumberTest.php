<?php

namespace Tests\Feature;

use App\Mail\OtpCodeMail;
use App\Models\AuthorCategory;
use App\Models\Enquiry;
use App\Support\PhoneNumbers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Phone numbers are collected with a country code and stored in E.164 format.
 */
class PhoneNumberTest extends TestCase
{
    #[Test]
    public function numbers_are_normalised_to_international_format(): void
    {
        $this->assertSame('+919876543210', PhoneNumbers::normalize('98765 43210'));
        $this->assertSame('+919876543210', PhoneNumbers::normalize('+91 98765-43210'));
        $this->assertSame('+447911123456', PhoneNumbers::normalize('+44 7911 123456'));
        $this->assertSame('12345', PhoneNumbers::normalize('12345'), 'Invalid input is left for validation to reject.');
        $this->assertNull(PhoneNumbers::normalize(''));
        $this->assertSame('+91 98765 43210', PhoneNumbers::display('+919876543210'));
    }

    #[Test]
    public function registration_stores_the_number_with_its_country_code(): void
    {
        Mail::fake();
        $this->post(route('register.store'), [
            'name' => 'Asha', 'email' => 'asha@example.com', 'phone' => '+44 7911 123456', 'phone__display' => '07911 123456',
            'author_category_id' => AuthorCategory::factory()->create()->id, 'institution' => 'King’s College London',
            'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1', 'terms' => '1',
        ])->assertRedirect(route('register.verify'));

        $this->assertSame('+447911123456', session('pending_registration.phone'));
        Mail::assertSent(OtpCodeMail::class);
    }

    #[Test]
    public function an_invalid_number_is_rejected(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Asha', 'email' => 'asha@example.com', 'phone' => '+91 12345',
            'author_category_id' => AuthorCategory::factory()->create()->id, 'institution' => 'NLU',
            'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1', 'terms' => '1',
        ])->assertSessionHasErrors(['phone' => 'Enter a valid phone number for the selected country.']);
    }

    #[Test]
    public function profile_and_admin_user_forms_save_international_numbers(): void
    {
        $author = $this->author();
        $this->actingAs($author)->put(route('account.profile.update'), [
            'name' => $author->name, 'author_category_id' => AuthorCategory::factory()->create()->id, 'institution' => 'NLSIU', 'phone' => '9876543210',
        ])->assertSessionHasNoErrors();
        $this->assertSame('+919876543210', $author->fresh()->phone);

        $this->actingAs($this->superadmin())->get(route('admin.users.edit', $author))->assertOk()->assertSee('data-phone="phone"', false);
    }

    #[Test]
    public function career_applications_require_a_valid_phone(): void
    {
        $data = [
            'name' => 'Ravi', 'email' => 'ravi@example.com', 'position' => 'Editorial Intern',
            'resume' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'),
        ];

        $this->post(route('careers.store'), $data)->assertSessionHasErrors('phone');
        $this->post(route('careers.store'), $data + ['phone' => '+1 415 555 2671'])->assertSessionHasNoErrors();

        $this->assertSame('+14155552671', Enquiry::latest('id')->value('phone'));
    }

    #[Test]
    public function phone_fields_render_with_the_country_picker(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('data-phone="phone"', false)->assertSee('data-phone-country="in"', false);
        $this->get(route('careers'))->assertOk()->assertSee('data-phone="phone"', false);
        $this->actingAs($this->author())->get(route('account.profile.edit'))->assertOk()->assertSee('id="billing_phone"', false);
    }
}
