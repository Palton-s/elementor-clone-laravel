<?php

namespace Tests\Feature\PageBuilder;

use App\Models\CustomFont;
use App\Models\Element;
use App\Models\GlobalWidget;
use App\Models\Page;
use App\Models\ThemeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Theme templates, global widgets, fonts, uploads, find & replace and collaboration:
 * the owner can use them, another editor gets 403 on every route.
 */
class OwnershipAndResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $intruder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->intruder = User::factory()->create();
    }

    private function page(?User $user = null): Page
    {
        return Page::factory()->create(['user_id' => ($user ?? $this->owner)->id]);
    }

    private function resources(): array
    {
        $page = $this->page();

        return [
            'theme_template' => ThemeTemplate::create(['user_id' => $this->owner->id, 'page_id' => $this->page()->id, 'title' => 'T', 'slug' => 't-' . Str::random(6), 'type' => 'header']),
            'global_widget' => GlobalWidget::create(['user_id' => $this->owner->id, 'title' => 'G', 'type' => 'heading', 'settings' => ['title' => 'x']]),
            'custom_font' => CustomFont::create(['user_id' => $this->owner->id, 'name' => 'F', 'family' => 'Fam']),
        ];
    }

    public static function protectedRoutes(): array
    {
        return [
            ['GET', 'page-builder.themes.edit', 'theme_template'],
            ['GET', 'page-builder.themes.editor', 'theme_template'],
            ['GET', 'page-builder.themes.render', 'theme_template'],
            ['PUT', 'page-builder.themes.update', 'theme_template'],
            ['DELETE', 'page-builder.themes.destroy', 'theme_template'],
            ['GET', 'page-builder.global-widgets.show', 'global_widget'],
            ['PUT', 'page-builder.global-widgets.update', 'global_widget'],
            ['GET', 'page-builder.global-widgets.render', 'global_widget'],
            ['DELETE', 'page-builder.global-widgets.destroy', 'global_widget'],
            ['PUT', 'page-builder.fonts.update', 'custom_font'],
            ['DELETE', 'page-builder.fonts.destroy', 'custom_font'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_other_users_cannot_touch_resources(string $method, string $route, string $param): void
    {
        $this->assertTrue(Route::has($route), "route {$route} exists");
        $resource = $this->resources()[$param];

        $response = $this->actingAs($this->intruder)->json($method, route($route, [$param => $resource->id]), [
            'title' => 'hack', 'type' => $resource->type ?? 'heading', 'name' => 'hack', 'family' => 'hack',
        ]);

        $response->assertForbidden();
        $this->assertModelExists($resource);
    }

    #[DataProvider('protectedRoutes')]
    public function test_owner_is_not_forbidden(string $method, string $route, string $param): void
    {
        $resource = $this->resources()[$param];

        $response = $this->actingAs($this->owner)->json($method, route($route, [$param => $resource->id]), [
            'title' => 'Novo', 'type' => $resource->type ?? 'heading', 'name' => 'Novo', 'family' => 'Fam',
        ]);

        $this->assertNotSame(403, $response->status(), "{$route} must allow the owner");
        $this->assertLessThan(500, $response->status(), "{$route} must not crash: " . substr((string) $response->getContent(), 0, 300));
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('page-builder.themes.index'))->assertRedirect('/login');
    }

    public function test_index_pages_list_only_own_resources(): void
    {
        $this->resources();
        ThemeTemplate::create(['user_id' => $this->intruder->id, 'page_id' => $this->page($this->intruder)->id, 'title' => 'Cabeçalho Alheio', 'slug' => 'alheio', 'type' => 'header']);

        $this->actingAs($this->owner)->get(route('page-builder.themes.index'))->assertOk()->assertDontSee('Cabeçalho Alheio');
        $this->actingAs($this->intruder)->get(route('page-builder.themes.index'))->assertOk()->assertSee('Cabeçalho Alheio');
    }

    public function test_popups_feature_was_removed(): void
    {
        $this->assertFalse(Route::has('page-builder.popups.index'));
        $this->actingAs($this->owner)->get('/page-builder/popups')->assertNotFound();

        $page = $this->page();
        $page->update(['status' => 'published']);
        $html = app(\App\Services\PageBuilder\Theme\ThemeService::class)->renderPageWithTheme($page->fresh());
        $this->assertStringNotContainsString('pb-popup', $html);
        $this->assertStringNotContainsString('PopupManager', $html);
    }

    // ------------------------------------------------------------------- uploads

    public function test_image_upload_stores_file_and_returns_public_url(): void
    {
        Storage::fake('public');
        $page = $this->page();

        $response = $this->actingAs($this->owner)->postJson(route('page-builder.upload'), [
            'image' => UploadedFile::fake()->image('foto.jpg', 40, 40), 'page_id' => $page->id,
        ])->assertOk();

        $url = $response->json('url');
        $this->assertStringStartsWith('/storage/page-builder/', $url);
        Storage::disk('public')->assertExists(Str::after($url, '/storage/'));
    }

    public function test_image_upload_rejects_non_images_and_other_users_pages(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner)->postJson(route('page-builder.upload'), [
            'image' => UploadedFile::fake()->create('script.php', 1, 'application/x-php'), 'page_id' => $this->page()->id,
        ])->assertStatus(422);

        $this->actingAs($this->intruder)->postJson(route('page-builder.upload'), [
            'image' => UploadedFile::fake()->image('foto.jpg'), 'page_id' => $this->page()->id,
        ])->assertForbidden();
    }

    public function test_video_upload_validates_type_and_size(): void
    {
        Storage::fake('public');
        $page = $this->page();

        // Real MP4 signature (ftyp box): the server checks the content, not just the extension
        $mp4 = hex2bin('0000001866747970') . 'mp42' . str_repeat("\0", 4) . 'mp42isom' . str_repeat("\0", 2048);
        $this->actingAs($this->owner)->postJson(route('page-builder.upload-video'), [
            'video' => UploadedFile::fake()->createWithContent('aula.mp4', $mp4), 'page_id' => $page->id,
        ])->assertOk()->assertJsonStructure(['url']);

        // An executable renamed to .mp4 is refused
        $this->actingAs($this->owner)->postJson(route('page-builder.upload-video'), [
            'video' => UploadedFile::fake()->createWithContent('falso.mp4', "<?php echo 'x';"), 'page_id' => $page->id,
        ])->assertStatus(422);

        $this->actingAs($this->owner)->postJson(route('page-builder.upload-video'), [
            'video' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'), 'page_id' => $page->id,
        ])->assertStatus(422);

        $this->actingAs($this->owner)->postJson(route('page-builder.upload-video'), [
            'video' => UploadedFile::fake()->create('grande.mp4', 51201, 'video/mp4'), 'page_id' => $page->id,
        ])->assertStatus(422);
    }

    // ------------------------------------------------------------- find & replace

    public function test_find_only_searches_own_pages(): void
    {
        $mine = $this->page();
        $theirs = $this->page($this->intruder);
        Element::factory()->create(['page_id' => $mine->id, 'type' => 'heading', 'settings' => ['title' => 'palavra-unica minha']]);
        Element::factory()->create(['page_id' => $theirs->id, 'type' => 'heading', 'settings' => ['title' => 'palavra-unica deles']]);

        $results = $this->actingAs($this->owner)->postJson(route('page-builder.find.search'), ['query' => 'palavra-unica'])->assertOk()->json();

        $this->assertStringContainsString('minha', json_encode($results));
        $this->assertStringNotContainsString('deles', json_encode($results));
    }

    public function test_find_on_someone_elses_page_is_forbidden(): void
    {
        $this->actingAs($this->owner)->postJson(route('page-builder.find.search'), [
            'query' => 'x', 'page_id' => $this->page($this->intruder)->id,
        ])->assertForbidden();
    }

    public function test_replace_cannot_inject_script(): void
    {
        $page = $this->page();
        $el = Element::factory()->create(['page_id' => $page->id, 'type' => 'text', 'settings' => ['content' => '<p>trocar aqui</p>']]);

        $this->actingAs($this->owner)->postJson(route('page-builder.find.replace'), [
            'find' => 'trocar', 'replace' => '<img src=x onerror=alert(1)>', 'page_id' => $page->id,
        ]);

        $this->assertStringNotContainsString('onerror', json_encode($el->fresh()->settings));
    }

    // ------------------------------------------------------------- misc endpoints

    public function test_collaboration_is_limited_to_people_who_can_see_the_page(): void
    {
        $page = $this->page();

        $this->actingAs($this->owner)->postJson(route('page-builder.collab.join', $page))->assertOk();
        $this->actingAs($this->owner)->getJson(route('page-builder.collab.users', $page))->assertOk();
        $this->actingAs($this->intruder)->postJson(route('page-builder.collab.join', $page))->assertForbidden();
    }

    public function test_dynamic_tags_endpoint_lists_groups(): void
    {
        $this->actingAs($this->owner)->getJson(route('page-builder.dynamic-tags'))
            ->assertOk()
            ->assertJsonFragment(['tag' => 'page.title']);
    }

    public function test_pages_are_served_with_a_content_security_policy(): void
    {
        $response = $this->actingAs($this->owner)->get(route('page-builder.projects.index'));

        $response->assertOk();
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy') ?? $response->headers->get('Content-Security-Policy-Report-Only'));
    }
}
