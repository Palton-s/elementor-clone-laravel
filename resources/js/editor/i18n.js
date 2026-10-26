// Portuguese display texts used by the editor UI. Stored values (option values, widget types)
// stay as they are; only what is shown on screen goes through these maps.

export const WIDGET_LABELS = {
    section: 'Seção', inner_section: 'Seção Interna', column: 'Coluna', heading: 'Título', text: 'Texto', image: 'Imagem',
    button: 'Botão', video: 'Vídeo', divider: 'Divisor', spacer: 'Espaçador', icon: 'Ícone', gallery: 'Galeria',
    form: 'Formulário', tabs: 'Abas', accordion: 'Sanfona', icon_box: 'Caixa com Ícone', image_box: 'Caixa com Imagem',
    carousel: 'Carrossel', counter: 'Contador', progress_bar: 'Barra de Progresso', social_icons: 'Redes Sociais',
    testimonial: 'Depoimento', price_table: 'Tabela de Preços', countdown: 'Contagem Regressiva', google_maps: 'Mapa',
    mega_menu: 'Menu de Navegação', lottie: 'Animação Lottie', callout: 'Destaque', table: 'Tabela', math: 'Fórmula Matemática',
};

// Names elements got when they were created before the editor was translated
const LEGACY_NAMES = {
    'Section': 'section', 'Inner Section': 'inner_section', 'Column': 'column', 'Heading': 'heading', 'Text Editor': 'text',
    'Image': 'image', 'Button': 'button', 'Video': 'video', 'Divider': 'divider', 'Spacer': 'spacer', 'Icon': 'icon',
    'Gallery': 'gallery', 'Form': 'form', 'Tabs': 'tabs', 'Accordion': 'accordion', 'Icon Box': 'icon_box',
    'Image Box': 'image_box', 'Carousel': 'carousel', 'Counter': 'counter', 'Progress Bar': 'progress_bar',
    'Social Icons': 'social_icons', 'Testimonial': 'testimonial', 'Price Table': 'price_table', 'Countdown': 'countdown',
    'Google Maps': 'google_maps', 'Mega Menu': 'mega_menu', 'Lottie Animation': 'lottie', 'Callout': 'callout',
    'Table': 'table', 'Math Formula': 'math',
};

export function widgetLabel(type) {
    return WIDGET_LABELS[type] || type;
}

/** Element name to show: a custom name as typed, an old English default name translated. */
export function elementName(el) {
    const name = (el && el.name) || '';
    if (!name) return widgetLabel(el && el.type);
    const copy = name.match(/^(.*?)((?: \((?:copy|cópia)\))+)$/);
    const base = copy ? copy[1] : name;
    const suffix = copy ? copy[2].replace(/\(copy\)/g, '(cópia)') : '';
    return LEGACY_NAMES[base] ? widgetLabel(LEGACY_NAMES[base]) + suffix : name;
}

const OPTION_TEXT = {
    // typography / background / border / hover / gradient
    none: 'Nenhum', uppercase: 'MAIÚSCULAS', lowercase: 'minúsculas', capitalize: 'Primeira Maiúscula',
    auto: 'Automático', cover: 'Cobrir', contain: 'Conter',
    'no-repeat': 'Não repetir', repeat: 'Repetir', 'repeat-x': 'Repetir na horizontal', 'repeat-y': 'Repetir na vertical',
    solid: 'Sólida', dashed: 'Tracejada', dotted: 'Pontilhada', double: 'Dupla',
    'scale(1.05)': 'Aumentar', 'scale(0.98)': 'Diminuir', 'translateY(-2px)': 'Subir', 'translateY(2px)': 'Descer',
    linear: 'Linear', radial: 'Radial',
    // scroll animation
    'fade-up': 'Surgir de baixo', 'fade-down': 'Surgir de cima', 'fade-left': 'Surgir da direita', 'fade-right': 'Surgir da esquerda',
    'zoom-in': 'Aproximar', 'zoom-out': 'Afastar', 'slide-up': 'Deslizar para cima', 'slide-down': 'Deslizar para baixo',
    // entrance animation
    fadeIn: 'Aparecer', fadeInUp: 'Aparecer de baixo', fadeInDown: 'Aparecer de cima', fadeInLeft: 'Aparecer da esquerda',
    fadeInRight: 'Aparecer da direita', slideInUp: 'Deslizar de baixo', slideInDown: 'Deslizar de cima',
    slideInLeft: 'Deslizar da esquerda', slideInRight: 'Deslizar da direita', zoomIn: 'Zoom', bounceIn: 'Quicar',
    rotateIn: 'Girar', lightSpeedIn: 'Entrada rápida',
    slow: 'Lenta', normal: 'Normal', fast: 'Rápida',
};

export function optionText(value) {
    return OPTION_TEXT[value] ?? value;
}
