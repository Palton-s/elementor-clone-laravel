<?php

namespace App\Services\PageBuilder\Widgets;

class CarouselWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'carousel';
        $this->label = 'Carousel';
        $this->icon = '🎠';
        $this->categories = ['general', 'media'];
        $this->keywords = ['carousel', 'slider', 'slides', 'gallery'];

        $this->defaultSettings = [
            'images' => [],
            'autoplay' => true,
            'loop' => true,
            'show_dots' => true,
            'show_arrows' => true,
            'speed' => 3000,
            'columns' => '1',
            'border_radius' => '8px',
        ];

        $this->controls = [
            'images' => ['type' => 'gallery', 'label' => 'Images'],
            'autoplay' => ['type' => 'boolean', 'label' => 'Autoplay'],
            'loop' => ['type' => 'boolean', 'label' => 'Loop'],
            'show_dots' => ['type' => 'boolean', 'label' => 'Show Dots'],
            'show_arrows' => ['type' => 'boolean', 'label' => 'Show Arrows'],
            'speed' => ['type' => 'number', 'label' => 'Speed (ms)', 'min' => 1000, 'max' => 10000],
            'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => ['1', '2', '3', '4']],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'tab' => 'style'],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
            'dimensions' => ['type' => 'dimensions', 'label' => 'Padding & Margin', 'tab' => 'advanced'],
            'z_index' => ['type' => 'number', 'label' => 'Z-Index', 'tab' => 'advanced'],
            'css_classes' => ['type' => 'text', 'label' => 'CSS Classes', 'tab' => 'advanced'],
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'tab' => 'advanced'],
            'custom_css' => ['type' => 'custom_css', 'label' => 'Custom CSS', 'tab' => 'advanced'],
            'animation' => ['type' => 'animation', 'label' => 'Animation', 'tab' => 'advanced'],
            'visibility' => ['type' => 'visibility', 'label' => 'Responsive Visibility', 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $images = $settings['images'] ?? [];
        $autoplay = $settings['autoplay'];
        $loop = $settings['loop'];
        $showDots = $settings['show_dots'];
        $showArrows = $settings['show_arrows'];
        $speed = (int) $settings['speed'];
        $columns = (int) $settings['columns'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        if (empty($images)) {
            return '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px;">Nenhuma imagem selecionada</div>';
        }

        $scrollSnap = $columns === 1 ? 'mandatory' : 'proximity';

        $html = '<div class="pb-carousel" style="position: relative; width: 100%; overflow: hidden; border-radius: ' . $borderRadius . ';">';

        // Track
        $html .= '<div class="pb-carousel-track" data-speed="' . $speed . '" data-loop="' . ($loop ? '1' : '0') . '" data-autoplay="' . ($autoplay ? '1' : '0') . '" data-columns="' . $columns . '" style="display: flex; overflow-x: auto; scroll-snap-type: x ' . $scrollSnap . '; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: ' . ($columns > 1 ? '12px' : '0') . ';">';

        $items = $autoplay && $loop ? array_merge($images, $images) : $images;

        foreach ($items as $i => $img) {
            $url = htmlspecialchars($img['url'] ?? '', ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($img['alt'] ?? '', ENT_QUOTES, 'UTF-8');
            $slideWidth = (100 / $columns) . '%';

            $html .= '<div class="pb-carousel-slide" style="flex: 0 0 ' . $slideWidth . '; scroll-snap-align: ' . ($columns === 1 ? 'start' : 'center') . '; min-width: 0;">';
            $html .= '<img src="' . $url . '" alt="' . $alt . '" style="width: 100%; height: auto; display: block; border-radius: ' . $borderRadius . '; object-fit: cover;">';
            $html .= '</div>';
        }
        $html .= '</div>';

        // Arrows
        if ($showArrows) {
            $html .= '<button class="pb-carousel-prev" style="position: absolute; top: 50%; left: 8px; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.9); border: none; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 2;">◀</button>';
            $html .= '<button class="pb-carousel-next" style="position: absolute; top: 50%; right: 8px; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.9); border: none; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 2;">▶</button>';
        }

        // Dots
        if ($showDots) {
            $totalSlides = count($images);
            $html .= '<div class="pb-carousel-dots" style="display: flex; justify-content: center; gap: 8px; margin-top: 12px;">';
            for ($i = 0; $i < $totalSlides; $i++) {
                $active = $i === 0 ? 'background: #6366f1;' : 'background: #d1d5db;';
                $html .= '<span class="pb-carousel-dot" data-index="' . $i . '" style="width: 10px; height: 10px; border-radius: 50%; cursor: pointer; transition: background 0.3s; ' . $active . '"></span>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        $script = '<script>'
            . '(function(){'
            . 'var carousel=document.querySelector(".pb-carousel:not([data-bound])");'
            . 'if(!carousel)return;'
            . 'carousel.dataset.bound="1";'
            . 'var track=carousel.querySelector(".pb-carousel-track");'
            . 'var slides=track.querySelectorAll(".pb-carousel-slide");'
            . 'var cols=parseInt(track.dataset.columns)||1;'
            . 'var totalOriginal=slides.length/((track.dataset.loop==="1"&&track.dataset.autoplay==="1")?2:1);'
            . 'var idx=0;var interval=null;var speed=parseInt(track.dataset.speed)||3000;'
            . 'function goTo(i){'
            . 'idx=((i%totalOriginal)+totalOriginal)%totalOriginal;'
            . 'var slideW=track.scrollWidth/((track.dataset.loop==="1"&&track.dataset.autoplay==="1")?slides.length:totalOriginal);'
            . 'track.scrollTo({left:idx*slideW,behavior:"smooth"});'
            . 'updateDots();'
            . '}'
            . 'function updateDots(){'
            . 'var dots=carousel.querySelectorAll(".pb-carousel-dot");'
            . 'dots.forEach(function(d,i){d.style.background=i===idx?"#6366f1":"#d1d5db";});'
            . '}'
            . 'var prev=carousel.querySelector(".pb-carousel-prev");'
            . 'var next=carousel.querySelector(".pb-carousel-next");'
            . 'if(prev)prev.onclick=function(){goTo(idx-1);};'
            . 'if(next)next.onclick=function(){goTo(idx+1);};'
            . 'carousel.querySelectorAll(".pb-carousel-dot").forEach(function(d){'
            . 'd.onclick=function(){goTo(parseInt(d.dataset.index));};'
            . '});'
            . 'if(track.dataset.autoplay==="1"){'
            . 'interval=setInterval(function(){goTo(idx+1);},speed);'
            . 'track.addEventListener("mouseenter",function(){clearInterval(interval);});'
            . 'track.addEventListener("mouseleave",function(){interval=setInterval(function(){goTo(idx+1);},speed);});'
            . '}'
            . '})();'
            . '</script>';

        return $html . $script;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $images = $settings['images'] ?? [];
        $columns = (int) $settings['columns'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        if (empty($images)) {
            return '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px;cursor:pointer;">🎠 Clique para adicionar imagens</div>';
        }

        $html = '<div style="position:relative;border-radius:' . $borderRadius . ';overflow:hidden;">';
        $html .= '<div style="display:flex;overflow-x:auto;gap:8px;scroll-snap-type:x mandatory;scrollbar-width:none;">';

        foreach (array_slice($images, 0, 6) as $img) {
            $url = htmlspecialchars($img['url'] ?? '', ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($img['alt'] ?? '', ENT_QUOTES, 'UTF-8');
            $slideWidth = (100 / $columns) . '%';
            $html .= '<div style="flex:0 0 ' . $slideWidth . ';scroll-snap-align:center;min-width:0;">';
            $html .= '<img src="' . $url . '" alt="' . $alt . '" style="width:100%;height:auto;display:block;border-radius:' . $borderRadius . ';object-fit:cover;">';
            $html .= '</div>';
        }
        $html .= '</div>';

        if (count($images) > 1) {
            $html .= '<button style="position:absolute;top:50%;left:8px;transform:translateY(-50%);width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.9);border:none;cursor:pointer;font-size:14px;">◀</button>';
            $html .= '<button style="position:absolute;top:50%;right:8px;transform:translateY(-50%);width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.9);border:none;cursor:pointer;font-size:14px;">▶</button>';
        }
        $html .= '</div>';

        return $html;
    }
}
