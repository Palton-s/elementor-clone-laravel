<?php

namespace Tests\Unit\Core;

use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\DynamicTags\DynamicTagService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DynamicTagServiceTest extends TestCase
{
    private DynamicTagService $tags;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tags = new DynamicTagService();
    }

    public function test_groups_and_flat_list_expose_every_tag(): void
    {
        $this->assertSame(['page', 'site', 'user', 'date'], array_keys($this->tags->getTagGroups()));

        $flat = array_column($this->tags->getFlatTags(), 'tag');
        $this->assertContains('page.title', $flat);
        $this->assertContains('user.email', $flat);
        $this->assertContains('year', $flat);
        $this->assertCount(count(array_unique($flat)), $flat, 'tags are unique');

        // every advertised tag has a replacement
        $replacements = $this->tags->getReplacements([]);
        foreach ($flat as $tag) {
            $this->assertArrayHasKey($tag, $replacements);
        }
    }

    public function test_replaces_page_user_and_date_tags(): void
    {
        Carbon::setTestNow('2026-03-05 14:30:00');
        $page = new Page(['title' => 'Aula 1', 'slug' => 'aula-1', 'status' => 'published']);
        $page->id = 12;
        $user = new User(['name' => 'Ana', 'email' => 'ana@x.com']);

        $out = $this->tags->processTags('{{page.title}}|{{ page.slug }}|{{page.id}}|{{user.name}}|{{year}}|{{day}}', ['page' => $page, 'user' => $user]);

        $this->assertSame('Aula 1|aula-1|12|Ana|2026|5', $out);
        Carbon::setTestNow();
    }

    public function test_unknown_tags_and_missing_context_are_safe(): void
    {
        $this->assertSame('{{nao.existe}}', $this->tags->processTags('{{nao.existe}}'));
        $this->assertSame('Olá ', $this->tags->processTags('Olá {{user.name}}', []));
        $this->assertSame('sem tags', $this->tags->processTags('sem tags', null));
    }

    public function test_values_are_escaped_because_they_land_in_html(): void
    {
        $page = new Page(['title' => '<script>alert(1)</script>', 'slug' => 's']);
        $user = new User(['name' => '"><img src=x onerror=alert(1)>']);

        $out = $this->tags->processTags('<h1>{{page.title}}</h1><span title="{{user.name}}"></span>', ['page' => $page, 'user' => $user]);

        $this->assertStringNotContainsString('<script>', $out);
        $this->assertStringNotContainsString('<img', $out);
        $this->assertStringContainsString('&lt;script&gt;', $out);
    }
}
