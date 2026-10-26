<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\Element;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TemplateManager
{
    protected array $templates;

    public function __construct()
    {
        $this->templates = $this->defaultTemplates();
    }

    public function all(): array
    {
        return $this->templates;
    }

    public function list(): array
    {
        $list = [];
        foreach ($this->templates as $key => $tmpl) {
            $list[$key] = ['name' => $tmpl['name'], 'description' => $tmpl['description']];
        }
        return $list;
    }

    public function has(string $key): bool
    {
        return isset($this->templates[$key]);
    }

    public function get(string $key): ?array
    {
        return $this->templates[$key] ?? null;
    }

    public function apply(Page $page, string $templateKey): Page
    {
        $template = $this->get($templateKey);

        if (!$template) {
            throw new \InvalidArgumentException("Template '{$templateKey}' not found");
        }

        DB::beginTransaction();
        try {
            $page->elements()->delete();
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();

            $this->importElements($page, $template['elements']);

            DB::commit();

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importToPage(Page $page, string $templateKey): void
    {
        $template = $this->get($templateKey);
        if (!$template) return;

        $page->settings = array_merge($page->settings ?? [], $template['settings']);
        $page->save();

        $this->importElements($page, $template['elements']);
    }

    public function themeTemplateList(): array
    {
        $list = [];
        foreach ($this->themeTemplates() as $key => $tmpl) {
            $list[$key] = ['name' => $tmpl['name'], 'description' => $tmpl['description']];
        }
        return $list;
    }

    public function themeTemplate(string $type): ?array
    {
        return $this->themeTemplates()[$type] ?? null;
    }

    public function hasThemeTemplate(string $type): bool
    {
        return isset($this->themeTemplates()[$type]);
    }

    public function applyThemeTemplate(Page $page, string $type): void
    {
        $template = $this->themeTemplate($type);
        if (!$template) return;

        $page->settings = array_merge($page->settings ?? [], $template['settings']);
        $page->save();

        $this->importElements($page, $template['elements']);
    }

    public function popupTemplateList(): array
    {
        $list = [];
        foreach ($this->popupTemplates() as $key => $tmpl) {
            $list[$key] = ['name' => $tmpl['name'], 'description' => $tmpl['description']];
        }
        return $list;
    }

    public function popupTemplate(string $key): ?array
    {
        return $this->popupTemplates()[$key] ?? null;
    }

    public function hasPopupTemplate(string $key): bool
    {
        return isset($this->popupTemplates()[$key]);
    }

    public function applyPopupTemplate(Page $page, string $key): void
    {
        $template = $this->popupTemplate($key);
        if (!$template) return;

        $page->settings = array_merge($page->settings ?? [], $template['settings']);
        $page->save();

        $this->importElements($page, $template['elements']);
    }

    protected function importElements(Page $page, array $elements, ?int $parentId = null, ?string $widgetType = null): void
    {
        foreach ($elements as $index => $elData) {
            $type = $widgetType ?? $elData['type'];
            $children = $elData['children'] ?? [];

            $element = new Element();
            $element->page_id = $page->id;
            $element->parent_id = $parentId;
            $element->type = $type;
            $element->uuid = (string) Str::uuid();
            $element->name = $elData['settings']['name'] ?? ucfirst($type);
            $element->settings = $elData['settings'] ?? [];
            $element->content = [];
            $element->styles = [];
            $element->order = $index;
            $element->save();

            if ($children) {
                $childType = $type === 'section' ? 'column' : null;
                $this->importElements($page, $children, $element->id, $childType);
            }
        }
    }

    protected function defaultTemplates(): array
    {
        return [
            'blank' => [
                'name' => 'Página em branco',
                'description' => 'Comece do zero com uma tela vazia',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [],
            ],
            'landing' => [
                'name' => 'Página de vendas',
                'description' => 'Topo com fundo em degradê, recursos e chamada para ação',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => '135', 'color1' => '#0f172a', 'color2' => '#1e1b4b', 'position1' => '0', 'position2' => '100'], 'padding_top' => '120px', 'padding_bottom' => '120px', 'min_height' => '90vh', 'align_items' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Crie algo incrível', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '16px', 'letter_spacing' => '-1px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.25rem;color:#94a3b8;max-width:600px;margin:0 auto;">Crie sites incríveis com o nosso construtor visual de arrastar e soltar. Sem precisar programar.</p>', 'alignment' => 'center', 'font_size' => '20px', 'line_height' => '1.7', 'margin_bottom' => '32px']],
                            ['type' => 'button', 'settings' => ['text' => 'Comece grátis', 'link' => '#', 'background_color' => '#6366f1', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '600', 'box_shadow' => '0 4px 20px rgba(99,102,241,.4)']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '60px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Recursos poderosos', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Tudo o que você precisa para criar sites profissionais em minutos</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px', 'padding_left' => '20px', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-bolt', 'icon_size' => '36', 'icon_color' => '#6366f1', 'title' => 'Super rápido', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Otimizado para velocidade, com cache automático e carregamento sob demanda.</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px', 'padding_left' => '20px', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-mobile-alt', 'icon_size' => '36', 'icon_color' => '#6366f1', 'title' => 'Totalmente responsivo', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Todo layout fica perfeito em qualquer aparelho ou tamanho de tela.</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px', 'padding_left' => '20px', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-puzzle-piece', 'icon_size' => '36', 'icon_color' => '#6366f1', 'title' => 'Mais de 50 widgets', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Uma biblioteca completa de widgets para qualquer tipo de conteúdo.</p>', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => '135', 'color1' => '#6366f1', 'color2' => '#8b5cf6', 'position1' => '0', 'position2' => '100'], 'padding_top' => '80px', 'padding_bottom' => '80px', 'border_radius' => '16px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Pronto para começar?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#e0e7ff;max-width:560px;margin:0 auto 32px;font-size:1.1rem;">Junte-se a milhares de pessoas que já criam sites incríveis.</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'button', 'settings' => ['text' => 'Começar a criar', 'link' => '#', 'background_color' => '#ffffff', 'text_color' => '#6366f1', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700', 'box_shadow' => '0 4px 16px rgba(0,0,0,.15)']],
                        ]],
                    ]],
                ],
            ],
            'about' => [
                'name' => 'Página Sobre',
                'description' => 'Apresentação da empresa com topo, equipe e números',
                'settings' => ['container_width' => '1140px', 'page_background' => '#f8fafc', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '80px', 'border_radius' => '0px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Nossa história', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-size:1.05rem;line-height:1.8;">Fundada em 2020, nossa missão é tornar a criação de sites acessível. Com o nosso construtor de arrastar e soltar, qualquer pessoa cria sites profissionais sem escrever uma linha de código.</p><p style="color:#475569;font-size:1.05rem;line-height:1.8;margin-top:16px;">Hoje, mais de 50 mil pessoas usam a plataforma para criar sites para seus negócios, portfólios e projetos.</p>', 'font_size' => '17px', 'line_height' => '1.8']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/about-us/600/500', 'alt' => 'Sobre nós', 'width' => '600', 'height' => '500'], 'width' => '100%', 'border_radius' => '12px', 'object_fit' => 'cover']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0f172a', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Usuários ativos', 'number' => '50000', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Páginas criadas', 'number' => '250000', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Pessoas na equipe', 'number' => '42', 'suffix' => '', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Países', 'number' => '85', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Conheça a equipe', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">As pessoas talentosas por trás do produto</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'image_box', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/team1/400/400', 'alt' => 'Sarah Johnson', 'width' => '400', 'height' => '400'], 'title' => 'Sarah Johnson', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">CEO e cofundadora</p>', 'alignment' => 'center', 'border_radius' => '50%']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'image_box', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/team2/400/400', 'alt' => 'Michael Chen', 'width' => '400', 'height' => '400'], 'title' => 'Michael Chen', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">CTO e cofundador</p>', 'alignment' => 'center', 'border_radius' => '50%']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'image_box', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/team3/400/400', 'alt' => 'Emily Davis', 'width' => '400', 'height' => '400'], 'title' => 'Emily Davis', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Diretora de design</p>', 'alignment' => 'center', 'border_radius' => '50%']],
                        ]],
                    ]],
                ],
            ],
            'contact' => [
                'name' => 'Página de contato',
                'description' => 'Formulário de contato com mapa',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Fale conosco', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">Tem alguma dúvida ou quer trabalhar com a gente? Adoraríamos ouvir você.</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_right' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Envie uma mensagem', 'tag' => 'h2', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '24px']],
                            ['type' => 'form', 'settings' => ['fields' => [['label' => 'Nome', 'placeholder' => 'Seu nome', 'type' => 'text', 'required' => true, 'width' => '100'], ['label' => 'E-mail', 'placeholder' => 'Seu e-mail', 'type' => 'email', 'required' => true, 'width' => '100'], ['label' => 'Assunto', 'placeholder' => 'Assunto', 'type' => 'text', 'required' => true, 'width' => '100'], ['label' => 'Mensagem', 'placeholder' => 'Sua mensagem', 'type' => 'textarea', 'required' => true, 'width' => '100']], 'submit_text' => 'Send Message', 'button_background' => '#6366f1', 'button_text_color' => '#ffffff', 'button_border_radius' => '8px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Onde estamos', 'tag' => 'h2', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '24px']],
                            ['type' => 'google_maps', 'settings' => ['address' => '123 Main Street, New York, NY 10001', 'zoom' => '14', 'height' => '400px', 'border_radius' => '12px']],
                        ]],
                    ]],
                ],
            ],
            'portfolio' => [
                'name' => 'Portfólio',
                'description' => 'Mostre seus trabalhos com galeria, depoimentos e mais',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Nossos trabalhos', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Conheça nossos projetos e estudos de caso mais recentes</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '20px', 'padding_bottom' => '60px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '30px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="display:inline-flex;gap:12px;flex-wrap:wrap;justify-content:center;"><span style="background:#6366f1;color:#fff;padding:8px 20px;border-radius:50px;font-weight:600;cursor:pointer;">All</span><span style="background:#f1f5f9;color:#475569;padding:8px 20px;border-radius:50px;cursor:pointer;">Web Design</span><span style="background:#f1f5f9;color:#475569;padding:8px 20px;border-radius:50px;cursor:pointer;">Branding</span><span style="background:#f1f5f9;color:#475569;padding:8px 20px;border-radius:50px;cursor:pointer;">Mobile</span></p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'gallery', 'settings' => ['images' => [['url' => 'https://picsum.photos/seed/port1/600/500', 'alt' => 'Projeto 1', 'width' => '600', 'height' => '500'], ['url' => 'https://picsum.photos/seed/port2/600/500', 'alt' => 'Projeto 2', 'width' => '600', 'height' => '500'], ['url' => 'https://picsum.photos/seed/port3/600/500', 'alt' => 'Projeto 3', 'width' => '600', 'height' => '500']], 'columns' => '3', 'gap' => '16', 'border_radius' => '12px', 'image_height' => '320px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'O que dizem nossos clientes', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Veja o que nossos clientes dizem</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Trabalhar com essa equipe foi um prazer. Eles deram vida à nossa ideia e superaram todas as expectativas."</p>', 'name' => 'Alex Rivera', 'position' => 'CEO', 'company' => 'TechVentures', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"A qualidade do design e a atenção aos detalhes são incomparáveis. Recomendo para qualquer projeto digital."</p>', 'name' => 'Lisa Park', 'position' => 'Diretora de marketing', 'company' => 'BrandStudio', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Mudou o nosso negócio. A plataforma é intuitiva e os resultados falam por si."</p>', 'name' => 'James Wilson', 'position' => 'Fundador', 'company' => 'GrowthLab', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                    ]],
                ],
            ],
            'showcase' => [
                'name' => 'Showcase Completo',
                'description' => 'Template futurista completo: hero com vídeo, botões com links, contadores, ícones, progress bars, countdown, galeria com lightbox, carousel, imagens com link, tabs, accordion, preços, depoimentos, animação Lottie, formulário, mapa e redes sociais',
                'settings' => ['container_width' => '1140px', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 160, 'color1' => '#020617', 'color2' => '#312e81', 'position1' => 0, 'position2' => 100], 'padding_top' => '100px', 'padding_bottom' => '100px', 'min_height' => '90vh', 'align_items' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_right' => '40px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'tip', 'title' => 'Nova versão 3.0', 'content' => '<p>Interface mais rápida, inteligente e futurista.</p>', 'show_icon' => true, 'border_radius' => '50px', 'margin_bottom' => '24px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Construa o Futuro com o Page Builder', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '800', 'letter_spacing' => '-1px', 'margin_bottom' => '16px', 'text_shadow' => '0 0 24px rgba(129,140,248,.55)']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.2rem;color:#a5b4fc;line-height:1.7;margin:0;">Uma plataforma visual que combina <strong style="color:#e0e7ff;">vídeos</strong>, <strong style="color:#e0e7ff;">imagens</strong>, <strong style="color:#e0e7ff;">animações</strong> e <strong style="color:#e0e7ff;">links</strong> — arraste, solte e publique sem escrever nada de código.</p>', 'alignment' => 'left', 'font_size' => '19px', 'line_height' => '1.7', 'margin_bottom' => '32px']],
                            ['type' => 'button', 'settings' => ['text' => 'Começar Agora', 'link' => '#precos', 'link_target' => '_self', 'size' => 'large', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#a855f7', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '32px', 'font_weight' => '600', 'box_shadow' => '0 8px 32px rgba(139,92,246,.5)', 'alignment' => 'left']],
                            ['type' => 'button', 'settings' => ['text' => '▶ Ver Demonstração', 'link' => '#video', 'link_target' => '_self', 'size' => 'large', 'background_type' => 'classic', 'background_color' => 'transparent', 'text_color' => '#e2e8f0', 'border_color' => '#6366f1', 'border_width' => '2px', 'border_radius' => '50px', 'padding_left_right' => '32px', 'font_weight' => '600', 'alignment' => 'left']],
                            ['type' => 'divider', 'settings' => ['width' => '100', 'thickness' => '1', 'color' => '#1e293b', 'space_before' => '32', 'space_after' => '16']],
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'github', 'url' => 'https://github.com', 'color' => '#94a3b8'], ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'color' => '#0A66C2'], ['platform' => 'twitter', 'url' => 'https://twitter.com', 'color' => '#1DA1F2'], ['platform' => 'youtube', 'url' => 'https://youtube.com', 'color' => '#FF0000']], 'columns' => '4', 'icon_size' => '36', 'gap' => '12', 'alignment' => 'left']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_left' => '40px'], 'children' => [
                            ['type' => 'video', 'settings' => ['video_type' => 'youtube', 'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4', 'aspect_ratio' => '16:9', 'width' => '100%', 'alignment' => 'center', 'controls' => true, 'autoplay' => false, 'loop' => false, 'mute' => false]],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0b1120', 'padding_top' => '40px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Números que Inspiram', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Contadores animados com prefixo e sufixo</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'divider', 'settings' => ['width' => '60', 'thickness' => '3', 'color' => '#22d3ee', 'space_before' => '24', 'space_after' => '0']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '60px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Usuários Ativos', 'suffix' => 'K+', 'number' => '250', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#22d3ee', 'font_size' => '2.6rem']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Páginas Criadas', 'suffix' => 'M', 'number' => '1.5', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#a78bfa', 'font_size' => '2.6rem']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Widgets', 'suffix' => '+', 'number' => '30', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#34d399', 'font_size' => '2.6rem']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Países', 'prefix' => 'R$', 'suffix' => 'M', 'number' => '12', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#f0abfc', 'font_size' => '2.6rem']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Recursos Futuristas', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Tudo que você precisa para construir páginas impressionantes</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'divider', 'settings' => ['width' => '60', 'thickness' => '3', 'color' => '#22d3ee', 'space_before' => '24', 'space_after' => '0']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#111827', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-bolt', 'size' => '44', 'color' => '#22d3ee', 'alignment' => 'center', 'margin_bottom' => '16px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Rápido & Leve', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Performance otimizada com cache inteligente e carregamento preguiçoso (lazy loading).</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'progress_bar', 'settings' => ['title' => 'Desempenho', 'percentage' => '98', 'color' => '#22d3ee', 'background_color' => '#1e293b', 'border_radius' => '10px', 'alignment' => 'left', 'margin_top' => '24px', 'animate' => true]],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#111827', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-mobile-alt', 'size' => '44', 'color' => '#a78bfa', 'alignment' => 'center', 'margin_bottom' => '16px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Responsivo', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Layout adaptável para celular (375px), tablet (768px) e desktop automaticamente.</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'progress_bar', 'settings' => ['title' => 'Cobertura', 'percentage' => '100', 'color' => '#a78bfa', 'background_color' => '#1e293b', 'border_radius' => '10px', 'alignment' => 'left', 'margin_top' => '24px', 'animate' => true]],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#111827', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-puzzle-piece', 'size' => '44', 'color' => '#34d399', 'alignment' => 'center', 'margin_bottom' => '16px']],
                            ['type' => 'heading', 'settings' => ['title' => '30+ Widgets', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Biblioteca completa de widgets para qualquer tipo de conteúdo: vídeo, imagem, preços, contagem regressiva e mais.</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'progress_bar', 'settings' => ['title' => 'Cobertura Visual', 'percentage' => '85', 'color' => '#34d399', 'background_color' => '#1e293b', 'border_radius' => '10px', 'alignment' => 'left', 'margin_top' => '24px', 'animate' => true]],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Galeria de Imagens', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Galeria em grade, carousel e lightbox</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'divider', 'settings' => ['width' => '60', 'thickness' => '3', 'color' => '#22d3ee', 'space_before' => '24', 'space_after' => '0']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                            ['type' => 'gallery', 'settings' => ['images' => [
                                ['url' => 'https://picsum.photos/seed/pb-gallery-1/600/400', 'alt' => 'Projeto futurista 1', 'caption' => 'Dashboard', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-2/600/400', 'alt' => 'Projeto futurista 2', 'caption' => 'App Mobile', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-3/600/400', 'alt' => 'Projeto futurista 3', 'caption' => 'Website', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-4/600/400', 'alt' => 'Projeto futurista 4', 'caption' => 'Branding', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-5/600/400', 'alt' => 'Projeto futurista 5', 'caption' => 'Identidade Visual', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-6/600/400', 'alt' => 'Projeto futurista 6', 'caption' => 'Social Media', 'width' => 600, 'height' => 400],
                            ], 'columns' => '3', 'gap' => '16', 'layout' => 'grid', 'show_caption' => true, 'border_radius' => '12', 'image_size' => 'cover']],
                            ['type' => 'carousel', 'settings' => ['images' => [
                                ['url' => 'https://picsum.photos/seed/pb-carousel-1/800/450', 'alt' => 'Slide 1', 'caption' => ''],
                                ['url' => 'https://picsum.photos/seed/pb-carousel-2/800/450', 'alt' => 'Slide 2', 'caption' => ''],
                                ['url' => 'https://picsum.photos/seed/pb-carousel-3/800/450', 'alt' => 'Slide 3', 'caption' => ''],
                            ], 'columns' => '2', 'autoplay' => true, 'loop' => true, 'show_dots' => true, 'show_arrows' => true, 'speed' => '3000', 'border_radius' => '16px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Imagens com Links', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Imagens com lightbox, link externo e efeitos de hover</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/pb-link-lightbox/600/400', 'alt' => 'Imagem com lightbox', 'width' => 600, 'height' => 400], 'caption' => 'Clique para abrir no lightbox', 'width' => '100%', 'border_radius' => '16px', 'enable_lightbox' => true, 'hover_animation' => 'zoom']],
                            ['type' => 'heading', 'settings' => ['title' => 'Saiba Mais', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '12px', 'link' => 'https://example.com', 'link_target' => '_blank']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Adicione <strong style="color:#e0e7ff;">links</strong> a qualquer imagem ou título: abra em nova aba, use âncoras da própria página ou aponte para qualquer URL.</p>', 'alignment' => 'left', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'button', 'settings' => ['text' => 'Visitar Site', 'link' => 'https://example.com', 'link_target' => '_blank', 'size' => 'medium', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#06b6d4', 'color2' => '#6366f1', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '24px', 'font_weight' => '600', 'alignment' => 'left']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'center', 'padding_left' => '20px'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/pb-link-external/600/400', 'alt' => 'Imagem com link externo', 'width' => 600, 'height' => 400], 'caption' => 'Abre em uma nova aba', 'width' => '100%', 'border_radius' => '16px', 'link' => 'https://example.com', 'link_target' => '_blank', 'hover_animation' => 'zoom']],
                            ['type' => 'video', 'settings' => ['video_type' => 'vimeo', 'video_url' => 'https://vimeo.com/76979871', 'aspect_ratio' => '16:9', 'width' => '100%', 'alignment' => 'center', 'controls' => true, 'autoplay' => false, 'loop' => false, 'mute' => false]],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Widgets Interativos', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Abas, accordion, progress bars e contagem regressiva</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'tabs', 'settings' => ['tabs' => [
                                ['title' => 'Descrição', 'content' => '<p style="color:#cbd5e1;">O Page Builder foi projetado para ser intuitivo. Com a interface drag-and-drop você cria páginas complexas em minutos, não em horas.</p>'],
                                ['title' => 'Funcionalidades', 'content' => '<ul style="color:#cbd5e1;"><li>30+ widgets</li><li>Estilos inline</li><li>Responsivo</li><li>Colaboração</li><li>Revisões</li></ul>'],
                                ['title' => 'Preços', 'content' => '<p style="color:#cbd5e1;">Gratuito para uso pessoal. Planos premium a partir de R$29/mês com recursos avançados.</p>'],
                            ], 'orientation' => 'horizontal', 'active_tab' => 0, 'tab_color' => '#22d3ee', 'border_color' => '#1e293b', 'content_padding' => 20]],
                            ['type' => 'countdown', 'settings' => ['target_date' => '2027-12-31', 'days_label' => 'Dias', 'hours_label' => 'Horas', 'minutes_label' => 'Min', 'seconds_label' => 'Seg', 'alignment' => 'center', 'color' => '#f0abfc', 'background_color' => '#1e293b']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'padding_left' => '20px'], 'children' => [
                            ['type' => 'accordion', 'settings' => ['items' => [
                                ['title' => 'Como funciona o construtor?', 'content' => '<p style="color:#cbd5e1;">Arraste widgets do painel esquerdo para a tela. Cada widget tem configurações de conteúdo, estilo e avançado no painel direito.</p>', 'open' => true],
                                ['title' => 'Preciso saber programar?', 'content' => '<p style="color:#cbd5e1;">Não! O Page Builder foi feito para não-programadores. Todo o desenvolvimento é visual, sem código.</p>'],
                                ['title' => 'Posso exportar o HTML?', 'content' => '<p style="color:#cbd5e1;">Sim! Use o botão "Copy HTML" no editor para copiar o HTML renderizado. Ideal para usar no Moodle, WordPress ou qualquer CMS.</p>'],
                            ], 'tab_color' => '#22d3ee', 'border_color' => '#1e293b']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Preços', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Tabelas de preço com botões de link</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Básico', 'price' => '29', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => '1 página', 'included' => true], ['text' => 'Widgets básicos', 'included' => true], ['text' => 'Suporte padrão', 'included' => true], ['text' => 'Domínio personalizado', 'included' => false], ['text' => 'Acesso à API', 'included' => false]], 'button_text' => 'Começar', 'button_link' => '#contato', 'featured' => false, 'featured_color' => '#22d3ee']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Pro', 'price' => '79', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => 'Páginas ilimitadas', 'included' => true], ['text' => 'Todos os widgets', 'included' => true], ['text' => 'Suporte prioritário', 'included' => true], ['text' => 'Domínio personalizado', 'included' => true], ['text' => 'Acesso à API', 'included' => true]], 'button_text' => 'Assinar Pro', 'button_link' => '#contato', 'featured' => true, 'featured_color' => '#a855f7']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Empresarial', 'price' => '199', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => 'Equipes ilimitadas', 'included' => true], ['text' => 'SSO e auditoria', 'included' => true], ['text' => 'Suporte dedicado', 'included' => true], ['text' => 'SLA garantido', 'included' => true], ['text' => 'Acesso à API', 'included' => true]], 'button_text' => 'Falar com Vendas', 'button_link' => '#contato', 'featured' => false, 'featured_color' => '#34d399']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'O Que Nossos Usuários Dizem', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Mais de 250 mil criadores confiam no Page Builder</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#1e293b', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.3)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"O Page Builder transformou a forma como criamos conteúdo. Intuitivo e poderoso."</p>', 'name' => 'Ana Silva', 'position' => 'CEO', 'company' => 'TechStart', 'avatar' => ['url' => 'https://picsum.photos/seed/avatar-ana/150/150', 'alt' => 'Ana Silva', 'width' => 150, 'height' => 150], 'rating' => '5', 'alignment' => 'center', 'text_color' => '#cbd5e1', 'name_color' => '#f8fafc']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#1e293b', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.3)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Finalmente um construtor que entrega qualidade profissional sem complicação."</p>', 'name' => 'Carlos Oliveira', 'position' => 'Diretora de marketing', 'company' => 'BrandLab', 'avatar' => ['url' => 'https://picsum.photos/seed/avatar-carlos/150/150', 'alt' => 'Carlos Oliveira', 'width' => 150, 'height' => 150], 'rating' => '5', 'alignment' => 'center', 'text_color' => '#cbd5e1', 'name_color' => '#f8fafc']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#1e293b', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.3)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Uso no Moodle para criar páginas educacionais. O Copy HTML é fantástico!"</p>', 'name' => 'Marina Costa', 'position' => 'Professora', 'company' => 'Universidade Federal', 'avatar' => ['url' => 'https://picsum.photos/seed/avatar-marina/150/150', 'alt' => 'Marina Costa', 'width' => 150, 'height' => 150], 'rating' => '5', 'alignment' => 'center', 'text_color' => '#cbd5e1', 'name_color' => '#f8fafc']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'radial', 'angle' => 0, 'color1' => '#1e1b4b', 'color2' => '#0b1120', 'position1' => 0, 'position2' => 100], 'padding_top' => '80px', 'padding_bottom' => '80px', 'align_items' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'lottie', 'settings' => ['lottie_url' => 'https://assets2.lottiefiles.com/packages/lf20_g1al0rje.json', 'loop' => true, 'autoplay' => true, 'speed' => '1', 'width' => '220', 'height' => '220', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Pronto para Começar?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f8fafc', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#a5b4fc;max-width:560px;margin:0 auto 32px;font-size:1.1rem;">Junte-se a milhares de criadores e construa sua página agora.</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'button', 'settings' => ['text' => 'Criar Página Grátis', 'link' => '#contato', 'link_target' => '_self', 'size' => 'large', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#ec4899', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700', 'box_shadow' => '0 8px 32px rgba(236,72,153,.45)', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Entre em Contato', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Tem alguma dúvida? Mande uma mensagem para nós</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'background_color' => '#111827', 'padding_top' => '32px', 'padding_bottom' => '32px', 'padding_left' => '32px', 'padding_right' => '32px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Formulário de Contato', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '24px']],
                            ['type' => 'form', 'settings' => ['form_name' => 'Contato Showcase', 'fields' => [['label' => 'Nome', 'placeholder' => 'Seu nome', 'type' => 'text', 'required' => true], ['label' => 'E-mail', 'placeholder' => 'seu@email.com', 'type' => 'email', 'required' => true], ['label' => 'Assunto', 'placeholder' => 'Assunto', 'type' => 'select', 'required' => true], ['label' => 'Mensagem', 'placeholder' => 'Sua mensagem', 'type' => 'textarea', 'required' => true, 'width' => '100']], 'button_text' => 'Enviar Mensagem', 'success_message' => 'Recebemos sua mensagem! Obrigado pelo contato.', 'button_color' => '#a855f7', 'button_text_color' => '#ffffff', 'button_width' => 'full', 'field_spacing' => 14, 'field_radius' => 8]],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_left' => '20px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Informações de Contato', 'content' => '<p style="color:#cbd5e1;"><strong style="color:#f1f5f9;">Endereço:</strong> Av. Paulista, 1000<br><strong style="color:#f1f5f9;">Telefone:</strong> (11) 99999-9999<br><strong style="color:#f1f5f9;">Email:</strong> contato@pagebuilder.com<br><strong style="color:#f1f5f9;">Horário:</strong> Seg-Sex, 9h-18h</p>', 'show_icon' => true, 'border_radius' => '12px']],
                            ['type' => 'google_maps', 'settings' => ['address' => 'Av. Paulista, 1000, São Paulo, SP', 'latitude' => '-23.5632', 'longitude' => '-46.6542', 'zoom' => '15', 'height' => '280px', 'map_type' => 'roadmap', 'border_radius' => '16px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#020617', 'padding_top' => '60px', 'padding_bottom' => '60px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'facebook', 'url' => 'https://facebook.com', 'color' => '#1877F2'], ['platform' => 'twitter', 'url' => 'https://twitter.com', 'color' => '#1DA1F2'], ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'color' => '#0A66C2'], ['platform' => 'instagram', 'url' => 'https://instagram.com', 'color' => '#E4405F'], ['platform' => 'youtube', 'url' => 'https://youtube.com', 'color' => '#FF0000'], ['platform' => 'github', 'url' => 'https://github.com', 'color' => '#94a3b8']], 'columns' => '6', 'icon_size' => '40', 'gap' => '12', 'alignment' => 'center']],
                            ['type' => 'divider', 'settings' => ['width' => '100', 'thickness' => '1', 'color' => '#1e293b', 'space_before' => '32', 'space_after' => '32']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;font-size:0.85rem;">&copy; 2026 Page Builder. Todos os direitos reservados. &bull; <a href="#" style="color:#a5b4fc;">Termos</a> &bull; <a href="#" style="color:#a5b4fc;">Privacidade</a></p>', 'alignment' => 'center', 'font_size' => '14px']],
                        ]],
                    ]],
                ],
            ],
            'blog-post' => [
                'name' => 'Artigo de blog',
                'description' => 'Artigo com imagem de destaque, conteúdo, autor e botões de compartilhar',
                'settings' => ['container_width' => '800px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '0px', 'padding_bottom' => '0px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/blog-hero/1200/600', 'alt' => 'Imagem de destaque', 'width' => '1200', 'height' => '600'], 'width' => '100%', 'border_radius' => '0px', 'object_fit' => 'cover', 'height' => '400px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Primeiros passos no design web moderno', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '800', 'margin_bottom' => '12px', 'letter_spacing' => '-.5px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;font-size:0.9rem;">Por Sarah Johnson &bull; Publicado em 15 de março de 2026 &bull; 5 min de leitura</p>', 'alignment' => 'left', 'font_size' => '14px', 'margin_bottom' => '32px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#334155;line-height:1.8;font-size:1.05rem;">O design de sites mudou muito na última década. Ficou para trás o tempo em que criar um site bonito exigia muito conhecimento de programação e meses de desenvolvimento. Hoje, os construtores de arrastar e soltar permitem que qualquer pessoa crie sites profissionais em uma fração do tempo.</p><p style="color:#334155;line-height:1.8;font-size:1.05rem;margin-top:20px;">Neste artigo, vamos ver os princípios essenciais do design web moderno e como usar o nosso construtor para criar layouts incríveis sem escrever uma linha de código.</p><h2 style="color:#0f172a;font-size:1.5rem;font-weight:700;margin-top:32px;margin-bottom:16px;">Por que a criação visual importa</h2><p style="color:#334155;line-height:1.8;font-size:1.05rem;">Os construtores visuais transformaram o desenvolvimento web. Eles mostram o resultado na hora, acabam com o vaivém entre design e programação e permitem que quem cria o conteúdo mantenha o próprio site.</p><p style="color:#334155;line-height:1.8;font-size:1.05rem;margin-top:16px;">Seja você dono de um negócio, profissional de marketing ou criativo, conseguir criar e ajustar o seu site rapidamente é um superpoder no mundo digital de hoje.</p><h2 style="color:#0f172a;font-size:1.5rem;font-weight:700;margin-top:32px;margin-bottom:16px;">Principais recursos do nosso construtor</h2><ul style="color:#334155;line-height:1.8;font-size:1.05rem;"><li>Interface intuitiva de arrastar e soltar, com visualização em tempo real</li><li>Mais de 50 widgets e elementos com design profissional</li><li>Layouts responsivos que ficam ótimos em qualquer aparelho</li><li>CSS e JavaScript personalizados para usuários avançados</li><li>Ferramentas de SEO e otimização de desempenho integradas</li></ul><p style="color:#334155;line-height:1.8;font-size:1.05rem;margin-top:20px;">Comece hoje a criar o site dos seus sonhos. Sem programação, só criatividade.</p>', 'font_size' => '16px', 'line_height' => '1.8']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '32px', 'padding_bottom' => '32px', 'border_radius' => '12px', 'margin_top' => '20px', 'margin_bottom' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/author/150/150', 'alt' => 'Sarah Johnson', 'width' => '150', 'height' => '150'], 'width' => '80px', 'border_radius' => '50%', 'object_fit' => 'cover']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-9', 'text_align' => 'left'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Sobre a autora', 'content' => '<p style="color:#475569;">Sarah Johnson é CEO e cofundadora da plataforma. Com mais de 15 anos de experiência em design e desenvolvimento web, é apaixonada por tornar a criação de sites acessível a todos.</p>']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '20px', 'padding_bottom' => '40px', 'border_top' => '1px solid #e2e8f0'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#0f172a;font-weight:600;margin-bottom:12px;">Compartilhe este artigo:</p>', 'alignment' => 'left', 'font_size' => '14px']],
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'facebook', 'url' => '#', 'color' => '#1877F2'], ['platform' => 'twitter', 'url' => '#', 'color' => '#1DA1F2'], ['platform' => 'linkedin', 'url' => '#', 'color' => '#0A66C2'], ['platform' => 'pinterest', 'url' => '#', 'color' => '#E60023'], ['platform' => 'whatsapp', 'url' => '#', 'color' => '#25D366']], 'columns' => '5', 'icon_size' => '40', 'gap' => '10', 'alignment' => 'left']],
                        ]],
                    ]],
                ],
            ],
        ];
    }

    protected function themeTemplates(): array
    {
        return [
            'header' => [
                'name' => 'Header Básico',
                'description' => 'Logo, navegação e botão de CTA',
                'settings' => ['container_width' => 'full', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0b1120', 'padding_top' => '16px', 'padding_bottom' => '16px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'left'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-layer-group', 'size' => '28', 'color' => '#6366f1', 'alignment' => 'left']],
                            ['type' => 'heading', 'settings' => ['title' => 'Sua Marca', 'tag' => 'h4', 'size' => 'small', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '700']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-5', 'text_align' => 'center'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#cbd5e1;">Início &nbsp;&bull;&nbsp; Sobre &nbsp;&bull;&nbsp; Serviços &nbsp;&bull;&nbsp; Contato</p>', 'alignment' => 'center', 'font_size' => '15px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'right'], 'children' => [
                            ['type' => 'button', 'settings' => ['text' => 'Criar Conta', 'link' => '#', 'link_target' => '_self', 'size' => 'small', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#a855f7', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '20px', 'font_weight' => '600', 'alignment' => 'right']],
                        ]],
                    ]],
                ],
            ],
            'footer' => [
                'name' => 'Rodapé Básico',
                'description' => 'Três colunas com marca, links e redes sociais',
                'settings' => ['container_width' => 'full', 'page_background' => '#020617', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#020617', 'padding_top' => '48px', 'padding_bottom' => '48px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Sua Marca', 'tag' => 'h4', 'size' => 'small', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;font-size:0.85rem;">&copy; 2026 Sua Empresa. Todos os direitos reservados.</p>', 'alignment' => 'left', 'font_size' => '14px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Links', 'tag' => 'h4', 'size' => 'small', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;line-height:1.9;">Página Inicial<br>Sobre nós<br>Termos de Uso<br>Privacidade</p>', 'alignment' => 'left', 'font_size' => '14px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Redes Sociais', 'tag' => 'h4', 'size' => 'small', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'facebook', 'url' => 'https://facebook.com', 'color' => '#1877F2'], ['platform' => 'twitter', 'url' => 'https://twitter.com', 'color' => '#1DA1F2'], ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'color' => '#0A66C2'], ['platform' => 'instagram', 'url' => 'https://instagram.com', 'color' => '#E4405F']], 'columns' => '4', 'icon_size' => '34', 'gap' => '10', 'alignment' => 'left']],
                        ]],
                    ]],
                ],
            ],
            'single' => [
                'name' => 'Artigo Simples',
                'description' => 'Cabeçalho, imagem de destaque, texto e autor',
                'settings' => ['container_width' => '800px', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '0px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/article-hero/1200/500', 'alt' => 'Imagem de destaque', 'width' => '1200', 'height' => '500'], 'width' => '100%', 'border_radius' => '0px', 'object_fit' => 'cover', 'height' => '360px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '32px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Título do Artigo', 'tag' => 'h1', 'size' => 'xl', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '800', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;font-size:0.9rem;">Por Autor Exemplo &bull; 5 min de leitura</p>', 'alignment' => 'left', 'font_size' => '14px', 'margin_bottom' => '24px']],
                            ['type' => 'divider', 'settings' => ['width' => '100', 'thickness' => '1', 'color' => '#1e293b', 'space_before' => '0', 'space_after' => '24']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#cbd5e1;font-size:1.05rem;line-height:1.8;">Este é o conteúdo do artigo. Substitua este texto pelo conteúdo real. O widget Texto aceita HTML, então você pode usar parágrafos, listas, destaques e mais.</p>', 'alignment' => 'left', 'font_size' => '17px', 'line_height' => '1.8']],
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Sobre o Autor', 'content' => '<p style="color:#475569;">Breve biografia do autor do artigo, com experiência e redes sociais.</p>']],
                        ]],
                    ]],
                ],
            ],
            'archive' => [
                'name' => 'Listagem de Posts',
                'description' => 'Cabeçalho e grade de cards de postagens',
                'settings' => ['container_width' => '1140px', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '64px', 'padding_bottom' => '16px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Arquivo de Posts', 'tag' => 'h1', 'size' => 'xl', 'color' => '#f8fafc', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Todos os artigos publicados</p>', 'alignment' => 'center', 'font_size' => '16px']],
                            ['type' => 'divider', 'settings' => ['width' => '60', 'thickness' => '3', 'color' => '#22d3ee', 'space_before' => '16', 'space_after' => '0']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '32px', 'padding_bottom' => '64px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'left'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/post1/600/380', 'alt' => 'Post 1', 'width' => '600', 'height' => '380'], 'width' => '100%', 'border_radius' => '12px', 'object_fit' => 'cover', 'height' => '200px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Post 1', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Resumo da postagem 1. Substitua pelos dados reais.</p>', 'alignment' => 'left', 'font_size' => '14px']],
                            ['type' => 'button', 'settings' => ['text' => 'Ler Mais', 'link' => '#', 'link_target' => '_self', 'size' => 'small', 'background_type' => 'classic', 'background_color' => 'transparent', 'text_color' => '#22d3ee', 'border_color' => '#22d3ee', 'border_width' => '1px', 'border_radius' => '50px', 'padding_left_right' => '16px', 'font_weight' => '600', 'alignment' => 'left']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'left'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/post2/600/380', 'alt' => 'Post 2', 'width' => '600', 'height' => '380'], 'width' => '100%', 'border_radius' => '12px', 'object_fit' => 'cover', 'height' => '200px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Post 2', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Resumo da postagem 2. Substitua pelos dados reais.</p>', 'alignment' => 'left', 'font_size' => '14px']],
                            ['type' => 'button', 'settings' => ['text' => 'Ler Mais', 'link' => '#', 'link_target' => '_self', 'size' => 'small', 'background_type' => 'classic', 'background_color' => 'transparent', 'text_color' => '#22d3ee', 'border_color' => '#22d3ee', 'border_width' => '1px', 'border_radius' => '50px', 'padding_left_right' => '16px', 'font_weight' => '600', 'alignment' => 'left']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'left'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/post3/600/380', 'alt' => 'Post 3', 'width' => '600', 'height' => '380'], 'width' => '100%', 'border_radius' => '12px', 'object_fit' => 'cover', 'height' => '200px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Post 3', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Resumo da postagem 3. Substitua pelos dados reais.</p>', 'alignment' => 'left', 'font_size' => '14px']],
                            ['type' => 'button', 'settings' => ['text' => 'Ler Mais', 'link' => '#', 'link_target' => '_self', 'size' => 'small', 'background_type' => 'classic', 'background_color' => 'transparent', 'text_color' => '#22d3ee', 'border_color' => '#22d3ee', 'border_width' => '1px', 'border_radius' => '50px', 'padding_left_right' => '16px', 'font_weight' => '600', 'alignment' => 'left']],
                        ]],
                    ]],
                ],
            ],
            '404' => [
                'name' => 'Página 404',
                'description' => 'Erro centralizado com botão para voltar',
                'settings' => ['container_width' => 'full', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0b1120', 'padding_top' => '120px', 'padding_bottom' => '120px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => '404', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#22d3ee', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px', 'text_shadow' => '0 0 24px rgba(34,211,238,.45)']],
                            ['type' => 'heading', 'settings' => ['title' => 'Oops! Página não encontrada', 'tag' => 'h2', 'size' => 'medium', 'color' => '#f8fafc', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">A página que você procura pode ter sido movida, renomeada ou não existe mais.</p>', 'alignment' => 'center', 'font_size' => '16px', 'margin_bottom' => '32px']],
                            ['type' => 'button', 'settings' => ['text' => 'Voltar para o início', 'link' => '/', 'link_target' => '_self', 'size' => 'large', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#a855f7', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '32px', 'font_weight' => '600', 'alignment' => 'center']],
                        ]],
                    ]],
                ],
            ],
            'search_results' => [
                'name' => 'Resultados de Busca',
                'description' => 'Cabeçalho e lista de resultados',
                'settings' => ['container_width' => '800px', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '64px', 'padding_bottom' => '16px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Resultados de Busca', 'tag' => 'h1', 'size' => 'xl', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '800', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Mostrando resultados para: <em>termo pesquisado</em></p>', 'alignment' => 'left', 'font_size' => '15px']],
                            ['type' => 'divider', 'settings' => ['width' => '100', 'thickness' => '1', 'color' => '#1e293b', 'space_before' => '16', 'space_after' => '24']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '64px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Resultado 1', 'content' => '<p style="color:#475569;">Resumo do resultado encontrado. Clique para abrir o conteúdo completo. Duplicar este bloco para mais resultados.</p>']],
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Resultado 2', 'content' => '<p style="color:#475569;">Resumo do segundo resultado. Substitua pelos resultados reais da busca.</p>']],
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Resultado 3', 'content' => '<p style="color:#475569;">Resumo do terceiro resultado. Use o Navigator para duplicar quantos resultados forem necessários.</p>']],
                        ]],
                    ]],
                ],
            ],
        ];
    }

    protected function popupTemplates(): array
    {
        return [
            'popup-newsletter' => [
                'name' => 'Newsletter',
                'description' => 'Assinatura de e-mail com formulário',
                'settings' => ['container_width' => '520px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'popup_settings' => ['width' => '480', 'position' => 'center', 'overlay' => '1', 'show_close_button' => '1'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-envelope-open-text', 'size' => '44', 'color' => '#6366f1', 'alignment' => 'center', 'margin_bottom' => '16px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Fique por dentro!', 'tag' => 'h2', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Receba novidades, dicas e ofertas exclusivas direto no seu e-mail.</p>', 'alignment' => 'center', 'font_size' => '15px', 'margin_bottom' => '24px']],
                            ['type' => 'form', 'settings' => ['form_name' => 'Newsletter', 'fields' => [['label' => 'E-mail', 'placeholder' => 'seu@email.com', 'type' => 'email', 'required' => true, 'width' => '100']], 'button_text' => 'Assinar Grátis', 'button_color' => '#6366f1', 'button_text_color' => '#ffffff', 'button_width' => 'full', 'field_spacing' => 12, 'field_radius' => 6, 'success_message' => 'Inscrição realizada com sucesso!']],
                        ]],
                    ]],
                ],
            ],
            'popup-announcement' => [
                'name' => 'Anúncio',
                'description' => 'Aviso central com callout e botão',
                'settings' => ['container_width' => '520px', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'popup_settings' => ['width' => '480', 'position' => 'center', 'overlay' => '1', 'show_close_button' => '1'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '40px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'warning', 'title' => 'Novidade!', 'content' => '<p>Não perca: atualização 3.0 já disponível.</p>', 'show_icon' => true, 'border_radius' => '12px', 'margin_bottom' => '24px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Grande novidade no Page Builder', 'tag' => 'h2', 'size' => 'medium', 'color' => '#f8fafc', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Confira as novas funcionalidades e comece a usar hoje mesmo.</p>', 'alignment' => 'center', 'font_size' => '15px', 'margin_bottom' => '24px']],
                            ['type' => 'button', 'settings' => ['text' => 'Saiba Mais', 'link' => '#', 'link_target' => '_self', 'size' => 'medium', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#ec4899', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '28px', 'font_weight' => '600', 'alignment' => 'center']],
                        ]],
                    ]],
                ],
            ],
        ];
    }
}
