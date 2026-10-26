import {
    AppWindow, ArrowDown, ArrowUp, BookmarkPlus, ChartNoAxesColumnIncreasing, ChevronRight, ChevronsUpDown,
    CircleAlert, CircleCheck, ClipboardCopy, ClipboardPaste, Code, Columns3, Component, Copy, Download, Eye,
    Film, GalleryHorizontal, Globe, GripVertical, Heading, Heading2, History, Image, ImagePlus, Images,
    LayoutDashboard, Layers, Link, Map, Maximize2, Megaphone, MessageSquareQuote, Minus, Monitor,
    MousePointerClick, MoveVertical, Paintbrush, PaintRoller, PanelTop, Pencil, Plus, RectangleHorizontal,
    RefreshCw, Rocket, Rows3, Save, Search, Settings, Share2, Sigma, SlidersHorizontal, Smartphone, Sparkles,
    SquareChartGantt, SquareMousePointer, Star, Table, Tablet, Tag, TextCursorInput, Timer, Trash2, Type,
    Undo2, Redo2, Unlink, Upload, Video, X, ZoomIn, ArrowLeft, Circle, Hash, SquareDashed, LayoutTemplate, User, Mail, Palette,
} from 'lucide';

const REGISTRY = {
    'arrow-down': ArrowDown, 'arrow-up': ArrowUp, 'arrow-left': ArrowLeft, 'bookmark-plus': BookmarkPlus,
    'chevron-right': ChevronRight, 'alert': CircleAlert, 'check': CircleCheck, 'clipboard-copy': ClipboardCopy,
    'clipboard-paste': ClipboardPaste, 'code': Code, 'component': Component, 'copy': Copy, 'download': Download,
    'eye': Eye, 'globe': Globe, 'grip': GripVertical, 'heading-2': Heading2, 'history': History,
    'image-plus': ImagePlus, 'layers': Layers, 'link': Link, 'unlink': Unlink, 'maximize': Maximize2,
    'minus': Minus, 'monitor': Monitor, 'tablet': Tablet, 'smartphone': Smartphone, 'paintbrush': Paintbrush,
    'paint-roller': PaintRoller, 'pencil': Pencil, 'plus': Plus, 'refresh': RefreshCw, 'rocket': Rocket,
    'save': Save, 'search': Search, 'settings': Settings, 'sliders': SlidersHorizontal, 'trash': Trash2,
    'undo': Undo2, 'redo': Redo2, 'upload': Upload, 'video': Video, 'x': X, 'zoom-in': ZoomIn,
    'pointer': MousePointerClick, 'circle': Circle, 'square-dashed': SquareDashed,
    'layout-template': LayoutTemplate, 'user': User, 'mail': Mail, 'palette': Palette,
};

// Widget type -> icon, used by the widget panel, navigator and structure tree
const WIDGET_ICONS = {
    section: Rows3, inner_section: LayoutDashboard, column: Columns3, heading: Heading, text: Type,
    image: Image, button: RectangleHorizontal, video: Video, divider: Minus, spacer: MoveVertical,
    icon: Star, gallery: Images, form: TextCursorInput, tabs: AppWindow, accordion: ChevronsUpDown,
    icon_box: Sparkles, image_box: SquareMousePointer, carousel: GalleryHorizontal,
    counter: Hash, progress_bar: SquareChartGantt, social_icons: Share2, testimonial: MessageSquareQuote,
    price_table: Tag, countdown: Timer, google_maps: Map, mega_menu: PanelTop, lottie: Film,
    callout: Megaphone, table: Table, math: Sigma, chart: ChartNoAxesColumnIncreasing,
};

function attrs(obj) {
    return Object.entries(obj).map(([k, v]) => `${k}="${v}"`).join(' ');
}

function toSvg(node, size, cls) {
    if (!node) return '';
    // lucide exports ['svg', attrs, children]
    const [, baseAttrs, children] = node;
    const a = { ...baseAttrs, width: size, height: size, 'stroke-width': 1.75, class: ('pb-icon ' + (cls || '')).trim(), 'aria-hidden': 'true' };
    const inner = (children || []).map(([tag, at]) => `<${tag} ${attrs(at)}/>`).join('');
    return `<svg ${attrs(a)}>${inner}</svg>`;
}

export function icon(name, size = 16, cls = '') {
    return toSvg(REGISTRY[name], size, cls);
}

export function widgetIcon(type, size = 16) {
    return toSvg(WIDGET_ICONS[type] || Circle, size, '');
}

// Fills static markup: <i data-icon="trash"></i> and widget panel items
export function hydrateIcons(root = document) {
    root.querySelectorAll('[data-icon]').forEach(el => {
        const size = parseInt(el.dataset.iconSize) || 16;
        el.outerHTML = icon(el.dataset.icon, size);
    });
    root.querySelectorAll('.pb-widget-item[data-type] .pb-widget-icon').forEach(el => {
        el.innerHTML = widgetIcon(el.closest('.pb-widget-item').dataset.type, 20);
    });
}
