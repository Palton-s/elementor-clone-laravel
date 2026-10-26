<?php

namespace Tests\Unit\Core;

use App\Models\Element;
use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\Core\ElementManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElementManagerTest extends TestCase
{
    use RefreshDatabase;

    private ElementManager $manager;
    private Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(ElementManager::class);
        $this->page = Page::factory()->create(['user_id' => User::factory()]);
    }

    private function orderOf(?int $parentId): array
    {
        return Element::where('page_id', $this->page->id)->where('parent_id', $parentId)->orderBy('order')->pluck('id')->all();
    }

    public function test_create_appends_with_widget_defaults(): void
    {
        $a = $this->manager->create($this->page, 'heading', ['title' => 'A']);
        $b = $this->manager->create($this->page, 'heading');

        $this->assertSame([0, 1], [$a->order, $b->order]);
        $this->assertSame('A', $a->settings['title']);
        $this->assertSame('h2', $a->settings['tag'], 'missing settings come from the widget defaults');
        $this->assertNotEmpty($a->uuid);
    }

    public function test_create_rejects_unknown_widget(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->manager->create($this->page, 'does_not_exist');
    }

    public function test_insert_before_shifts_siblings_and_inherits_their_parent(): void
    {
        $section = $this->manager->create($this->page, 'section');
        $col = $this->manager->create($this->page, 'column', [], $section->id);
        $first = $this->manager->create($this->page, 'heading', [], $col->id);
        $second = $this->manager->create($this->page, 'heading', [], $col->id);

        // no parent given: it is taken from the element it goes before
        $inserted = $this->manager->create($this->page, 'text', [], null, $second->id);

        $this->assertSame($col->id, $inserted->parent_id);
        $this->assertSame([$first->id, $inserted->id, $second->id], $this->orderOf($col->id));
    }

    public function test_children_order_is_independent_per_parent(): void
    {
        $s1 = $this->manager->create($this->page, 'section');
        $s2 = $this->manager->create($this->page, 'section');
        $c1 = $this->manager->create($this->page, 'column', [], $s1->id);
        $c2 = $this->manager->create($this->page, 'column', [], $s2->id);

        $this->assertSame(0, $c1->order);
        $this->assertSame(0, $c2->order);
    }

    public function test_duplicate_copies_the_whole_subtree(): void
    {
        $section = $this->manager->create($this->page, 'section');
        $col = $this->manager->create($this->page, 'column', [], $section->id);
        $this->manager->create($this->page, 'heading', ['title' => 'Filho'], $col->id);

        $copy = $this->manager->duplicate($section->fresh());

        $this->assertNotSame($section->id, $copy->id);
        $this->assertNotSame($section->uuid, $copy->uuid);
        $this->assertSame(1, $copy->order, 'copy goes after the original');
        $copyCol = $copy->children()->first();
        $this->assertNotNull($copyCol);
        $this->assertSame('Filho', $copyCol->children()->first()?->settings['title']);
    }

    public function test_delete_removes_every_descendant(): void
    {
        $section = $this->manager->create($this->page, 'section');
        $col = $this->manager->create($this->page, 'column', [], $section->id);
        $leaf = $this->manager->create($this->page, 'heading', [], $col->id);

        $this->manager->delete($section->fresh());

        $this->assertNull(Element::find($section->id));
        $this->assertNull(Element::find($col->id));
        $this->assertNull(Element::find($leaf->id), 'grandchildren must not be left behind');
    }

    public function test_build_tree_nests_children_in_order_with_container_flag(): void
    {
        $section = $this->manager->create($this->page, 'section');
        $c1 = $this->manager->create($this->page, 'column', [], $section->id);
        $c2 = $this->manager->create($this->page, 'column', [], $section->id);
        $this->manager->create($this->page, 'heading', [], $c2->id);

        $tree = $this->manager->buildTree($this->page->allElements()->orderBy('order')->get());

        $this->assertCount(1, $tree);
        $this->assertTrue($tree[0]['is_container']);
        $this->assertSame([$c1->id, $c2->id], array_column($tree[0]['children'], 'id'));
        $this->assertFalse($tree[0]['children'][1]['children'][0]['is_container']);
        $this->assertArrayHasKey('responsive_settings', $tree[0]);
    }

    public function test_move_element_changes_parent_and_order(): void
    {
        $s1 = $this->manager->create($this->page, 'section');
        $s2 = $this->manager->create($this->page, 'section');
        $h = $this->manager->create($this->page, 'heading', [], $s1->id);

        $this->manager->moveElement($h, $s2->id, 3);

        $this->assertSame($s2->id, $h->fresh()->parent_id);
        $this->assertSame(3, $h->fresh()->order);
    }
}
