<?php

namespace Tests\Unit;

use App\Models\Element;
use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\WidgetManager;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\Renderer;
use App\Contracts\PageBuilder\WidgetInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class PageBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PageBuilderService $service;
    protected WidgetManager $widgetManager;
    protected ElementManager $elementManager;
    protected Renderer $renderer;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->widgetManager = Mockery::mock(WidgetManager::class);
        $this->elementManager = Mockery::mock(ElementManager::class);
        $this->renderer = Mockery::mock(Renderer::class);

        $this->service = new PageBuilderService(
            $this->widgetManager,
            $this->elementManager,
            $this->renderer
        );

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_create_page(): void
    {
        $data = ['title' => 'Test Page'];
        $page = $this->service->createPage($data);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals($this->user->id, $page->user_id);
        $this->assertEquals('draft', $page->status);
        $this->assertNotEmpty($page->slug);
        $this->assertTrue($page->slug === 'test-page' || str_starts_with($page->slug, 'test-page'));
    }

    public function test_generates_unique_slug(): void
    {
        $this->service->createPage(['title' => 'Same Title']);
        $page2 = $this->service->createPage(['title' => 'Same Title']);

        $this->assertNotEquals($page2->slug, 'same-title');
        $this->assertStringStartsWith('same-title-', $page2->slug);
    }

    public function test_can_update_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $updated = $this->service->updatePage($page, ['title' => 'Updated Title']);

        $this->assertEquals('Updated Title', $updated->title);
    }

    public function test_sanitizes_content_on_update(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $maliciousContent = [
            'content' => [
                'text' => '<p>Hello</p><script>alert("xss")</script>',
            ],
        ];

        $updated = $this->service->updatePage($page, $maliciousContent);

        $saved = $updated->content['text'];
        $this->assertStringContainsString('<p>Hello</p>', $saved);
        $this->assertStringNotContainsString('<script>', $saved);
    }

    public function test_sanitizes_urls_in_content(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $content = [
            'text' => '<a href="javascript:alert(1)">Click</a> <img src="https://example.com/img.jpg">',
        ];

        $updated = $this->service->updatePage($page, ['content' => $content]);

        $saved = $updated->content['text'];
        $this->assertStringNotContainsString('javascript:', $saved);
        $this->assertStringContainsString('https://example.com/img.jpg', $saved);
    }

    public function test_sanitizes_settings(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $element = Element::factory()->create(['page_id' => $page->id]);

        $badSettings = [
            'title' => '<script>alert("xss")</script>',
            'background_color' => 'invalid-color',
            'url' => 'javascript:alert(1)',
            'count' => 42,
            'enabled' => true,
        ];

        $updated = $this->service->updateElement($element, ['settings' => $badSettings]);

        $settings = $updated->settings;
        $this->assertStringNotContainsString('<script>', $settings['title']);
        $this->assertEquals('#000000', $settings['background_color']);
        $this->assertNotEmpty($settings['count']);
        $this->assertTrue($settings['enabled']);
    }

    public function test_can_add_element(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $widget = Mockery::mock(WidgetInterface::class);
        $widget->shouldReceive('getLabel')->andReturn('Heading');
        $widget->shouldReceive('getDefaultSettings')->andReturn(['title' => 'Default Title']);

        $this->widgetManager->shouldReceive('getWidget')
            ->with('heading')
            ->andReturn($widget);

        $element = $this->service->addElement($page, 'heading');

        $this->assertInstanceOf(Element::class, $element);
        $this->assertEquals($page->id, $element->page_id);
        $this->assertEquals('heading', $element->type);
        $this->assertEquals('Heading', $element->name);
        $this->assertEquals(0, $element->order);
    }

    public function test_throws_exception_for_invalid_widget(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $this->widgetManager->shouldReceive('getWidget')
            ->with('nonexistent')
            ->andReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->addElement($page, 'nonexistent');
    }

    public function test_can_update_element(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $element = Element::factory()->create(['page_id' => $page->id]);

        $result = $this->service->updateElement($element, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $result->name);
    }

    public function test_can_remove_element(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $element = Element::factory()->create(['page_id' => $page->id]);

        $result = $this->service->removeElement($element);

        $this->assertTrue($result);
        $this->assertSoftDeleted($element);
    }

    public function test_removes_child_elements(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $parent = Element::factory()->create(['page_id' => $page->id]);
        $child = Element::factory()->create(['page_id' => $page->id, 'parent_id' => $parent->id]);

        $this->service->removeElement($parent);

        $this->assertSoftDeleted($parent);
        $this->assertSoftDeleted($child);
    }

    public function test_can_duplicate_element(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        $element = Element::factory()->create(['page_id' => $page->id]);

        $duplicate = $this->service->duplicateElement($element);

        $this->assertNotEquals($element->id, $duplicate->id);
        $this->assertEquals($element->type, $duplicate->type);
        $this->assertEquals($element->name . ' (cópia)', $duplicate->name);
    }

    public function test_creates_revision_on_page_creation(): void
    {
        $page = $this->service->createPage(['title' => 'Revision Test']);

        $this->assertDatabaseHas('revisions', [
            'page_id' => $page->id,
            'label' => 'Initial creation',
        ]);
    }

    public function test_restores_revision(): void
    {
        $page = $this->service->createPage(['title' => 'Revision Test']);
        $page->content = ['key' => 'interim'];
        $page->save();

        $revisionToRestore = new \App\Models\Revision();
        $revisionToRestore->page_id = $page->id;
        $revisionToRestore->user_id = $this->user->id;
        $revisionToRestore->content = ['key' => 'saved-state'];
        $revisionToRestore->version = '2.0.0';
        $revisionToRestore->label = 'Saved point';
        $revisionToRestore->type = 'manual';
        $revisionToRestore->save();
        $revisionToRestore->refresh();

        $page->content = ['key' => 'current-state'];
        $page->save();

        $restored = $this->service->restoreRevision($page, $revisionToRestore);

        $this->assertEquals(['key' => 'saved-state'], $restored->content);
    }

    public function test_exports_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);
        Element::factory()->count(2)->create(['page_id' => $page->id]);

        $export = $this->service->exportPage($page);

        $this->assertArrayHasKey('version', $export);
        $this->assertArrayHasKey('title', $export);
        $this->assertArrayHasKey('elements', $export);
        $this->assertCount(2, $export['elements']);
        $this->assertEquals($page->title, $export['title']);
    }

    public function test_imports_page(): void
    {
        $data = [
            'title' => 'Imported Page',
            'settings' => ['container_width' => '1200px'],
            'elements' => [
                ['type' => 'section', 'name' => 'Section', 'settings' => ['layout' => 'full_width'], 'children' => [
                    ['type' => 'column', 'name' => 'Column', 'settings' => ['column_width' => 'col-12']],
                ]],
            ],
        ];

        $widget = Mockery::mock(WidgetInterface::class);
        $widget->shouldReceive('getLabel')->andReturn('Section');
        $widget->shouldReceive('getDefaultSettings')->andReturn([]);

        $this->widgetManager->shouldReceive('getWidget')
            ->with('section')
            ->andReturn($widget);

        $widget2 = Mockery::mock(WidgetInterface::class);
        $widget2->shouldReceive('getLabel')->andReturn('Column');
        $widget2->shouldReceive('getDefaultSettings')->andReturn([]);

        $this->widgetManager->shouldReceive('getWidget')
            ->with('column')
            ->andReturn($widget2);

        $page = $this->service->importPage($data);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Imported Page', $page->title);
        $this->assertCount(1, $page->elements);
    }

    public function test_renders_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $this->renderer->shouldReceive('render')
            ->with($page, [])
            ->andReturn('<div>Rendered</div>');

        $html = $this->service->renderPage($page);

        $this->assertEquals('<div>Rendered</div>', $html);
    }

    public function test_can_publish_page(): void
    {
        $page = $this->service->createPage(['title' => 'Test Page']);
        $page->status = 'published';
        $page->save();

        $this->assertEquals('published', $page->status);
        $this->assertTrue($page->isPublished());
    }

    public function test_page_scopes(): void
    {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $published = $this->service->createPage(['title' => 'Published']);
        $published->status = 'published';
        $published->save();

        $draft = $this->service->createPage(['title' => 'Draft']);

        $this->assertCount(1, Page::published()->get());
        $this->assertCount(1, Page::draft()->get());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
