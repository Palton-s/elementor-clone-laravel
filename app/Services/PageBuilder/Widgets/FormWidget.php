<?php

namespace App\Services\PageBuilder\Widgets;

class FormWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'form';
        $this->label = 'Form';
        $this->icon = '📋';
        $this->categories = ['general'];
        $this->keywords = ['form', 'contact', 'submit', 'input'];

        $this->defaultSettings = [
            'form_name' => 'Formulário de contato',
            'fields' => [
                ['label' => 'Nome', 'type' => 'text', 'required' => true, 'placeholder' => 'Seu nome'],
                ['label' => 'E-mail', 'type' => 'email', 'required' => true, 'placeholder' => 'seu@email.com'],
                ['label' => 'Mensagem', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Sua mensagem'],
            ],
            'button_text' => 'Enviar mensagem',
            'button_color' => '#6366f1',
            'button_text_color' => '#ffffff',
            'button_width' => 'auto',
            'success_message' => 'Obrigado! Sua mensagem foi enviada.',
            'field_spacing' => 12,
            'field_radius' => 6,
        ];

        $this->controls = [
            'form_name' => ['type' => 'text', 'label' => 'Form Name'],
            'fields' => ['type' => 'repeater', 'label' => 'Fields', 'fields' => [
                'label' => ['type' => 'text', 'label' => 'Label'],
                'type' => ['type' => 'select', 'label' => 'Type', 'options' => ['text', 'email', 'tel', 'number', 'textarea', 'select', 'checkbox', 'radio']],
                'required' => ['type' => 'boolean', 'label' => 'Required'],
                'placeholder' => ['type' => 'text', 'label' => 'Placeholder'],
            ]],
            'button_text' => ['type' => 'text', 'label' => 'Button Text'],
            'success_message' => ['type' => 'text', 'label' => 'Success Message'],
            'button_color' => ['type' => 'color', 'label' => 'Button Color', 'tab' => 'style'],
            'button_text_color' => ['type' => 'color', 'label' => 'Button Text Color', 'tab' => 'style'],
            'button_width' => ['type' => 'select', 'label' => 'Button Width', 'options' => ['auto', 'full'], 'tab' => 'style'],
            'field_spacing' => ['type' => 'number', 'label' => 'Field Spacing (px)', 'min' => 0, 'max' => 40, 'tab' => 'style'],
            'field_radius' => ['type' => 'number', 'label' => 'Field Border Radius (px)', 'min' => 0, 'max' => 20, 'tab' => 'style'],
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
        $fields = $settings['fields'] ?? [];
        $formName = htmlspecialchars($settings['form_name'], ENT_QUOTES, 'UTF-8');
        $btnText = htmlspecialchars($settings['button_text'], ENT_QUOTES, 'UTF-8');
        $btnColor = htmlspecialchars($settings['button_color'], ENT_QUOTES, 'UTF-8');
        $btnTextColor = htmlspecialchars($settings['button_text_color'], ENT_QUOTES, 'UTF-8');
        $btnWidth = $settings['button_width'] === 'full' ? 'width: 100%;' : '';
        $spacing = (int) $settings['field_spacing'];
        $radius = (int) $settings['field_radius'];

        $fieldsHtml = '';
        foreach ($fields as $i => $field) {
            $label = htmlspecialchars($field['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $type = htmlspecialchars($field['type'] ?? 'text', ENT_QUOTES, 'UTF-8');
            $required = !empty($field['required']);
            $requiredAttr = $required ? ' required' : '';
            $requiredStar = $required ? ' <span style="color:#ef4444">*</span>' : '';
            $placeholder = htmlspecialchars($field['placeholder'] ?? '', ENT_QUOTES, 'UTF-8');
            $fieldName = 'pb_form_' . $i;
            $fieldStyle = "width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: {$radius}px; font-size: 14px; font-family: inherit; color: #374151; background: #fff; box-sizing: border-box;";

            $fieldsHtml .= "<div style=\"margin-bottom: {$spacing}px;\">";
            $fieldsHtml .= "<label style=\"display: block; margin-bottom: 4px; font-size: 14px; font-weight: 500; color: #374151;\">{$label}{$requiredStar}</label>";

            if ($type === 'textarea') {
                $fieldsHtml .= "<textarea name=\"{$fieldName}\" placeholder=\"{$placeholder}\"{$requiredAttr} style=\"{$fieldStyle} min-height: 100px; resize: vertical;\"></textarea>";
            } elseif ($type === 'select') {
                $fieldsHtml .= "<select name=\"{$fieldName}\"{$requiredAttr} style=\"{$fieldStyle}\"><option value=\"\">Selecione...</option></select>";
            } elseif ($type === 'checkbox' || $type === 'radio') {
                $fieldsHtml .= "<div style=\"display: flex; align-items: center; gap: 8px;\"><input type=\"{$type}\" name=\"{$fieldName}\"{$requiredAttr} style=\"width: auto;\"><span style=\"font-size: 14px; color: #374151;\">{$label}</span></div>";
            } else {
                $fieldsHtml .= "<input type=\"{$type}\" name=\"{$fieldName}\" placeholder=\"{$placeholder}\"{$requiredAttr} style=\"{$fieldStyle}\">";
            }

            $fieldsHtml .= "</div>";
        }

        $successMsg = htmlspecialchars($settings['success_message'], ENT_QUOTES, 'UTF-8');

        $form = '<form class="pb-form" data-form-name="' . $formName . '" data-success="' . $successMsg . '" style="max-width: 100%;">'
            . $fieldsHtml
            . '<button type="submit" style="padding: 10px 24px; background: ' . $btnColor . '; color: ' . $btnTextColor . '; border: none; border-radius: ' . $radius . 'px; font-size: 14px; font-weight: 500; cursor: pointer; ' . $btnWidth . '">' . $btnText . '</button>'
            . '<div class="pb-form-success" style="display: none; margin-top: 12px; padding: 12px; background: #dcfce7; color: #166534; border-radius: ' . $radius . 'px; font-size: 14px;">' . $successMsg . '</div>'
            . '<div class="pb-form-error" style="display: none; margin-top: 12px; padding: 12px; background: #fee2e2; color: #991b1b; border-radius: ' . $radius . 'px; font-size: 14px;"></div>'
            . '</form>'
            . '<script>'
            . '(function(){'
            . 'function getCsrf(){'
            . 'var m=document.querySelector(\'meta[name="csrf-token"]\');'
            . 'if(m&&m.content)return m.content;'
            . 'var c=document.cookie.match(/XSRF-TOKEN=([^;]+)/);'
            . 'if(c)return decodeURIComponent(c[1]);'
            . 'return "";'
            . '}'
            . 'function refreshCsrf(cb){'
            . 'fetch(window.location.href,{method:"HEAD",credentials:"same-origin"}).then(function(r){'
            . 'var t=r.headers.get("X-CSRF-TOKEN")||getCsrf();'
            . 'if(cb)cb(t);'
            . '}).catch(function(){cb(getCsrf());});'
            . '}'
            . 'var forms=document.querySelectorAll(".pb-form:not([data-bound])");'
            . 'forms.forEach(function(form){'
            . 'form.dataset.bound="1";'
            . 'form.onsubmit=function(e){'
            . 'e.preventDefault();'
            . 'var data={};'
            . 'var inputs=form.querySelectorAll("input,textarea,select");'
            . 'inputs.forEach(function(inp){if(inp.name)data[inp.name]=inp.type==="checkbox"?inp.checked:inp.value;});'
            . 'var pageRoot=document.querySelector("[data-page-id]");'
            . 'var pageId=pageRoot?pageRoot.getAttribute("data-page-id"):(window.location.pathname.match(/\\/pages\\/(\\d+)/)?window.location.pathname.match(/\\/pages\\/(\\d+)/)[1]:"");'
            . 'var success=form.querySelector(".pb-form-success");'
            . 'var error=form.querySelector(".pb-form-error");'
            . 'if(success)success.style.display="none";'
            . 'if(error)error.style.display="none";'
            . 'function doSubmit(csrfToken){'
            . 'fetch("/page-builder/pages/"+pageId+"/form/submit",{'
            . 'method:"POST",'
            . 'headers:{"Content-Type":"application/json","X-CSRF-TOKEN":csrfToken,"Accept":"application/json"},'
            . 'credentials:"same-origin",'
            . 'body:JSON.stringify({form_name:form.dataset.formName,data:data})'
            . '}).then(function(r){'
            . 'if(r.status===419){return refreshCsrf(function(newToken){doSubmit(newToken);});}'
            . 'return r.json();'
            . '}).then(function(res){'
            . 'if(!res)return;'
            . 'if(res.success){if(success)success.style.display="block";form.reset();}'
            . 'else{if(error){error.textContent=res.message||"Error";error.style.display="block";}}'
            . '}).catch(function(){if(error){error.textContent="Network error";error.style.display="block";}});'
            . '}'
            . 'doSubmit(getCsrf());'
            . '};'
            . '});'
            . '})();'
            . '</script>';

        return $form;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $fields = $settings['fields'] ?? [];
        $btnText = htmlspecialchars($settings['button_text'], ENT_QUOTES, 'UTF-8');
        $btnColor = htmlspecialchars($settings['button_color'], ENT_QUOTES, 'UTF-8');
        $btnTextColor = htmlspecialchars($settings['button_text_color'], ENT_QUOTES, 'UTF-8');
        $btnWidth = $settings['button_width'] === 'full' ? 'width: 100%;' : '';
        $spacing = (int) $settings['field_spacing'];
        $radius = (int) $settings['field_radius'];

        $fieldsHtml = '';
        foreach ($fields as $field) {
            $label = htmlspecialchars($field['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $type = $field['type'] ?? 'text';
            $required = !empty($field['required']);
            $requiredStar = $required ? ' <span style="color:#ef4444">*</span>' : '';

            $fieldsHtml .= "<div style=\"margin-bottom: {$spacing}px;\">";
            $fieldsHtml .= "<label style=\"display: block; margin-bottom: 4px; font-size: 14px; font-weight: 500; color: #374151;\">{$label}{$requiredStar}</label>";

            if ($type === 'textarea') {
                $fieldsHtml .= "<div style=\"width:100%;min-height:60px;padding:8px 10px;border:1px solid #d1d5db;border-radius:{$radius}px;background:#f9fafb;font-size:13px;color:#9ca3af\">Texto longo</div>";
            } elseif ($type === 'select') {
                $fieldsHtml .= "<div style=\"width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:{$radius}px;background:#fff;font-size:13px;color:#9ca3af;display:flex;justify-content:space-between;align-items:center\"><span>Selecione...</span><span>▼</span></div>";
            } elseif ($type === 'checkbox' || $type === 'radio') {
                $fieldsHtml .= "<div style=\"display:flex;align-items:center;gap:8px\"><input type=\"{$type}\" disabled style=\"width:auto\"><span style=\"font-size:13px;color:#374151\">{$label}</span></div>";
            } else {
                $fieldsHtml .= "<div style=\"width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:{$radius}px;background:#f9fafb;font-size:13px;color:#9ca3af\">{$type}</div>";
            }

            $fieldsHtml .= "</div>";
        }

        return <<<HTML
<div class="pb-form-editor" style="padding:8px">
    {$fieldsHtml}
    <button type="button" style="padding:10px 24px;background:{$btnColor};color:{$btnTextColor};border:none;border-radius:{$radius}px;font-size:14px;font-weight:500;cursor:default;{$btnWidth}">{$btnText}</button>
</div>
HTML;
    }
}
