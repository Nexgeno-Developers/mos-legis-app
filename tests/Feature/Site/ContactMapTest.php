<?php

namespace Tests\Feature\Site;

use App\Enums\PageTemplate;
use App\Models\Page;
use App\Support\GoogleMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactMapTest extends TestCase
{
    private const EMBED = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3773.8!2d72.83!3d18.93';

    #[Test]
    public function only_google_maps_embeds_are_accepted(): void
    {
        $this->assertSame(self::EMBED, GoogleMap::embedUrl(self::EMBED));
        $this->assertSame(self::EMBED, GoogleMap::embedUrl('<iframe src="'.self::EMBED.'" width="600" height="450"></iframe>'));
        $this->assertNotNull(GoogleMap::embedUrl('https://www.google.com/maps?q=Mumbai&output=embed'));

        $this->assertNull(GoogleMap::embedUrl('https://evil.example.com/maps/embed?pb=1'));
        $this->assertNull(GoogleMap::embedUrl('http://www.google.com/maps/embed?pb=1'));
        $this->assertNull(GoogleMap::embedUrl('https://www.google.com/search?q=x'));
        $this->assertNull(GoogleMap::embedUrl('javascript:alert(1)'));
    }

    #[Test]
    public function contact_page_has_no_map_until_one_is_set_and_shows_full_width_faqs(): void
    {
        $page = Page::factory()->create(['template' => PageTemplate::Contact, 'slug' => 'contact']);
        $page->metas()->createMany([
            ['meta_key' => 'office_address', 'meta_value' => 'Fort Chambers, Mumbai 400001', 'meta_type' => 'text'],
            ['meta_key' => 'faqs', 'meta_value' => json_encode([['question' => 'Is the fee refundable?', 'answer' => 'No.']]), 'meta_type' => 'json'],
        ]);

        // No map field: no map, even though there is an office address.
        $this->get(page_url('contact'))->assertOk()
            ->assertDontSee('<iframe', false)
            ->assertDontSee('Get directions')
            ->assertSee('Fort Chambers, Mumbai 400001')
            ->assertSee('Is the fee refundable?');

        // Map set: it is shown, with the address card.
        $page->metas()->create(['meta_key' => 'map_embed_url', 'meta_value' => 'https://www.google.com/maps/embed?pb=abc', 'meta_type' => 'string']);
        $this->get(page_url('contact'))->assertOk()
            ->assertSee('https://www.google.com/maps/embed?pb=abc', false)
            ->assertSee('Get directions');
    }

    #[Test]
    public function admin_can_set_map_by_pasting_iframe_code_and_bad_urls_are_rejected(): void
    {
        $admin = $this->superadmin();
        $page = Page::factory()->create(['template' => PageTemplate::Contact, 'slug' => 'contact']);
        $payload = ['title' => 'Contact Us', 'slug' => 'contact', 'status' => 'Published'];

        $this->actingAs($admin)->put(route('admin.pages.update', $page), $payload + ['meta' => ['map_embed_url' => 'https://evil.example.com/x']])
            ->assertSessionHasErrors('meta.map_embed_url');

        $this->actingAs($admin)->put(route('admin.pages.update', $page), $payload + ['meta' => ['map_embed_url' => '<iframe src="'.self::EMBED.'"></iframe>']])
            ->assertSessionHasNoErrors();

        $this->assertSame(self::EMBED, $page->fresh()->load('metas')->meta('map_embed_url'));
        $this->get(page_url('contact'))->assertSee(self::EMBED, false);
    }
}
