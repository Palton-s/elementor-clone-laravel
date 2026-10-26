<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Element;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use App\Services\PageBuilder\Core\WidgetManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class SiteExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['page-builder.export.download_remote' => false]);
        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    private function element(Page $page, string $type, array $settings, int $order = 0): Element
    {
        $defaults = app(WidgetManager::class)->getWidget($type)->getDefaultSettings();

        return Element::factory()->create([
            'page_id' => $page->id, 'type' => $type, 'order' => $order,
            'settings' => array_replace_recursive($defaults, $settings), 'styles' => [], 'content' => [],
        ]);
    }

    private function openZip($response): ZipArchive
    {
        $response->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        // The test client never sends the response, so deleteFileAfterSend does not run
        $this->beforeApplicationDestroyed(function () use ($zip, $path) {
            $zip->close();
            @unlink($path);
        });

        return $zip;
    }

    private function names(ZipArchive $zip): array
    {
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }

        return $names;
    }

    public function test_page_zip_contains_html_and_uploaded_media(): void
    {
        Storage::disk('public')->put('page-builder/foto.png', 'png-bytes');
        Storage::disk('public')->put('page-builder/videos/aula.mp4', 'mp4-bytes');

        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $this->element($page, 'image', ['image' => ['url' => '/storage/page-builder/foto.png', 'alt' => 'Foto']]);
        $this->element($page, 'video', ['video_type' => 'upload', 'video_file' => '/storage/page-builder/videos/aula.mp4'], 1);

        $zip = $this->openZip($this->actingAs($this->user)->get(route('page-builder.pages.export-zip', $page)));
        $names = $this->names($zip);

        $this->assertContains('index.html', $names);
        $this->assertContains('LEIA-ME.txt', $names);
        $this->assertContains('assets/images/page-builder/foto.png', $names);
        $this->assertContains('assets/videos/page-builder/videos/aula.mp4', $names);
        $this->assertSame('png-bytes', $zip->getFromName('assets/images/page-builder/foto.png'));

        $html = $zip->getFromName('index.html');
        $this->assertStringContainsString('src="assets/images/page-builder/foto.png"', $html);
        $this->assertStringContainsString('assets/videos/page-builder/videos/aula.mp4', $html);
        $this->assertStringNotContainsString('/storage/page-builder', $html);
        $this->assertStringNotContainsString('Content-Security-Policy', $html);
    }

    public function test_uploaded_video_plays_even_when_type_was_left_as_youtube(): void
    {
        // Uploads made before the editor switched the type automatically kept the default "youtube"
        Storage::disk('public')->put('page-builder/videos/aula.mp4', 'mp4-bytes');
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $this->element($page, 'video', ['video_type' => 'youtube', 'video_url' => '', 'video_file' => '/storage/page-builder/videos/aula.mp4']);
        $this->element($page, 'video', ['video_type' => 'custom', 'video_url' => '/storage/page-builder/videos/aula.mp4'], 1);

        $preview = $this->actingAs($this->user)->get(route('page-builder.render', $page))->assertOk()->getContent();
        $this->assertSame(2, substr_count($preview, '<video src="/storage/page-builder/videos/aula.mp4"'));
        $this->assertStringNotContainsString('No video selected', $preview);
        $this->assertStringNotContainsString('<iframe', $preview);

        $zip = $this->openZip($this->actingAs($this->user)->get(route('page-builder.pages.export-zip', $page)));
        $this->assertContains('assets/videos/page-builder/videos/aula.mp4', $this->names($zip));
        $this->assertStringContainsString('<video src="assets/videos/page-builder/videos/aula.mp4"', $zip->getFromName('index.html'));
    }

    public function test_project_zip_puts_pages_in_folders_with_relative_links(): void
    {
        Storage::disk('public')->put('page-builder/logo.svg', '<svg/>');

        $project = Project::factory()->create(['user_id' => $this->user->id]);
        $home = Page::factory()->create(['user_id' => $this->user->id, 'project_id' => $project->id, 'slug' => 'home', 'title' => 'Início']);
        $about = Page::factory()->create(['user_id' => $this->user->id, 'project_id' => $project->id, 'slug' => 'sobre', 'title' => 'Sobre']);

        $this->element($home, 'button', ['text' => 'Sobre nós', 'link' => '/p/sobre']);
        $this->element($about, 'image', ['image' => ['url' => '/storage/page-builder/logo.svg', 'alt' => 'Logo']]);

        $zip = $this->openZip($this->actingAs($this->user)->get(route('page-builder.projects.export-zip', $project)));
        $names = $this->names($zip);

        $this->assertContains('index.html', $names);
        $this->assertContains('sobre/index.html', $names);
        $this->assertContains('assets/images/page-builder/logo.svg', $names);

        $this->assertStringContainsString('href="sobre/index.html"', $zip->getFromName('index.html'));
        // Pages in sub-folders reach the shared assets with ../
        $this->assertStringContainsString('src="../assets/images/page-builder/logo.svg"', $zip->getFromName('sobre/index.html'));
    }

    public function test_local_files_outside_public_folders_are_never_exported(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $this->element($page, 'image', ['image' => ['url' => '/storage/../../../.env.png', 'alt' => 'x']]);

        $zip = $this->openZip($this->actingAs($this->user)->get(route('page-builder.pages.export-zip', $page)));

        $this->assertSame(['index.html', 'LEIA-ME.txt'], $this->names($zip));
    }

    public function test_cannot_export_other_users_page_or_project(): void
    {
        $other = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $other->id]);
        $project = Project::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->user)->get(route('page-builder.pages.export-zip', $page))->assertForbidden();
        $this->actingAs($this->user)->get(route('page-builder.projects.export-zip', $project))->assertForbidden();
    }
}
