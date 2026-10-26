<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Element;
use App\Models\Page;
use App\Contracts\PageBuilder\WidgetInterface;
use Illuminate\Support\Collection;

class ElementManager
{
    protected WidgetManager $widgetManager;

    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    public function create(Page $page, string $widgetType, array $settings = [], ?int $parentId = null, ?int $insertBeforeId = null): Element
    {
        $widget = $this->widgetManager->getWidget($widgetType);

        if (!$widget) {
            throw new \InvalidArgumentException("Widget type '{$widgetType}' not found");
        }

        $insertBefore = null;

        if ($insertBeforeId) {
            $insertBefore = Element::where('id', $insertBeforeId)->where('page_id', $page->id)->first();
            if ($insertBefore) {
                $parentId = $insertBefore->parent_id;
            }
        }

        $order = $insertBefore
            ? $insertBefore->order
            : $this->getNextOrder($page, $parentId);

        if ($insertBefore) {
            Element::where('page_id', $page->id)
                ->where('parent_id', $parentId)
                ->where('order', '>=', $order)
                ->increment('order');
        }

        $element = new Element();
        $element->page_id = $page->id;
        $element->type = $widgetType;
        $element->name = $widget->getLabel();
        $element->settings = array_merge($widget->getDefaultSettings(), $settings);
        $element->content = [];
        $element->styles = [];
        $element->order = $order;

        if ($parentId) {
            $element->parent_id = $parentId;
        }

        $element->save();

        return $element;
    }

    public function update(Element $element, array $data): Element
    {
        $element->fill($data);

        if (isset($data['settings'])) {
            $widget = $this->widgetManager->getWidget($element->type);
            if ($widget) {
                $element->settings = $widget->validateSettings($data['settings']);
            }
        }

        $element->save();

        return $element;
    }

    public function delete(Element $element): bool
    {
        // Depth first, so columns' contents go too, not only the direct children
        foreach ($element->children()->get() as $child) {
            $this->delete($child);
        }

        return (bool) $element->delete();
    }

    public function duplicate(Element $element): Element
    {
        $newElement = $element->replicate();
        $newElement->uuid = (string) \Illuminate\Support\Str::uuid();
        $newElement->name = $element->name . ' (cópia)';
        $newElement->order = $this->getNextOrder($element->page, $element->parent_id);
        $newElement->save();

        foreach ($element->children as $child) {
            $this->duplicateChild($child, $newElement->id);
        }

        return $newElement;
    }

    protected function duplicateChild(Element $element, int $newParentId): void
    {
        $newElement = $element->replicate();
        $newElement->uuid = (string) \Illuminate\Support\Str::uuid();
        $newElement->parent_id = $newParentId;
        $newElement->order = $element->order;
        $newElement->save();

        foreach ($element->children as $child) {
            $this->duplicateChild($child, $newElement->id);
        }
    }

    public function reorder(Page $page, array $order): void
    {
        foreach ($order as $index => $item) {
            if (isset($item['id'])) {
                Element::where('id', $item['id'])
                    ->where('page_id', $page->id)
                    ->update(['order' => $index]);

                if (isset($item['children'])) {
                    $this->reorderChildren($item['id'], $item['children']);
                }
            }
        }
    }

    protected function reorderChildren(int $parentId, array $children): void
    {
        foreach ($children as $index => $childId) {
            Element::where('id', $childId)
                ->where('parent_id', $parentId)
                ->update(['order' => $index]);
        }
    }

    public function getElementsTree(Page $page): Collection
    {
        return $page->elements()->with('children')->get();
    }

    public function getFlatElements(Page $page): Collection
    {
        return $page->allElements()->orderBy('order')->get();
    }

    public function moveElement(Element $element, ?int $newParentId, int $newOrder): void
    {
        $element->parent_id = $newParentId;
        $element->order = $newOrder;
        $element->save();
    }

    protected function getNextOrder(Page $page, ?int $parentId = null): int
    {
        $query = Element::where('page_id', $page->id);

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        return ($query->max('order') ?? -1) + 1;
    }

    public function getWidgetForElement(Element $element): ?WidgetInterface
    {
        return $this->widgetManager->getWidget($element->type);
    }

    public function buildTree($elements): array
    {
        $byParent = [];
        foreach ($elements as $e) {
            $byParent[(int) ($e->parent_id ?? 0)][] = $e;
        }

        $build = function ($parentId) use ($byParent, &$build) {
            $result = [];
            foreach ($byParent[$parentId] ?? [] as $element) {
                $widget = $this->widgetManager->getWidget($element->type);
                $node = [
                    'id' => $element->id,
                    'uuid' => $element->uuid,
                    'type' => $element->type,
                    'name' => $element->name,
                    'order' => $element->order,
                    'settings' => $element->settings,
                    'content' => $element->content,
                    'styles' => $element->styles,
                    'responsive_settings' => $element->responsive_settings,
                    'animation' => $element->animation,
                    'effects' => $element->effects,
                    'column_size' => $element->column_size,
                    'css_classes' => $element->css_classes,
                    'css_id' => $element->css_id,
                    'is_container' => $widget ? $widget->isContainer() : false,
                ];
                $children = $build($element->id);
                if ($children) {
                    $node['children'] = $children;
                }
                $result[] = $node;
            }
            return $result;
        };

        return $build(0);
    }
}
