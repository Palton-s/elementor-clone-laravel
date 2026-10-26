<style>
:root {
    --pb-primary: #2563eb;
    --pb-primary-hover: #1d4ed8;
    --pb-primary-light: rgba(37,99,235,.12);
    --pb-bg: #0b1026;
    --pb-surface: #101624;
    --pb-surface2: #151b2c;
    --pb-surface3: #1d2438;
    --pb-border: rgba(148,163,184,.18);
    --pb-border-strong: rgba(148,163,184,.28);
    --pb-text: #e7e9ee;
    --pb-text2: #9aa3b5;
    --pb-accent: #3b82f6;
    --pb-danger: #ef4444;
    --pb-success: #22c55e;
    --pb-warning: #f59e0b;
    --pb-canvas-bg: #f7f9fc;
    --pb-radius-xs: 6px;
    --pb-radius-sm: 8px;
    --pb-radius: 10px;
    --pb-radius-lg: 12px;
    --pb-shadow-sm: 0 1px 1px rgba(15,23,42,.02), 0 4px 10px rgba(15,23,42,.06);
    --pb-shadow: 0 10px 30px rgba(15,23,42,.08);
    --pb-shadow-lg: 0 20px 40px rgba(15,23,42,.12);
    --pb-ease: cubic-bezier(.25,.8,.25,1);
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { height: 100%; }
body { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--pb-bg); color: var(--pb-text); overflow: hidden; height: 100vh; line-height: 1.6; text-rendering: optimizeLegibility; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

.pb-toolbar {
    height: 56px; background: linear-gradient(180deg, rgba(255,255,255,.01) 0%, transparent 100%), var(--pb-surface);
    border-bottom: 1px solid var(--pb-border);
    display: flex; align-items: center; padding: 0 1rem; gap: .4rem; flex-shrink: 0;
    backdrop-filter: blur(14px); position: relative; z-index: 100;
    box-shadow: 0 1px 0 rgba(255,255,255,.01);
}
.pb-toolbar-group { display: inline-flex; align-items: center; gap: .35rem; padding: 0 .25rem; }
.pb-toolbar-title { font-weight: 600; font-size: .88rem; display: flex; align-items: center; gap: .5rem; letter-spacing: -.01em; }
.pb-toolbar-title a { color: var(--pb-text2); text-decoration: none; font-size: 1rem; transition: color .2s var(--pb-ease); }
.pb-toolbar-title a:hover { color: var(--pb-text); }
.pb-toolbar-title a.btn-back { font-size: .76rem; color: var(--pb-text); background: transparent; border: 1px solid transparent; padding: .32rem .6rem; border-radius: var(--pb-radius-xs); transition: all .2s var(--pb-ease); display: inline-flex; align-items: center; gap: .4rem; }
.pb-toolbar-title a.btn-back:hover { background: var(--pb-surface2); border-color: var(--pb-border); color: var(--pb-text); }
.pb-toolbar-badge {
    font-size: .6rem; padding: .16rem .5rem; border-radius: 999px; font-weight: 600;
    letter-spacing: .4px; text-transform: uppercase;
}
.pb-toolbar-spacer { flex: 1; }
.pb-toolbar button, .pb-toolbar a.tb-link {
    background: transparent; color: var(--pb-text2); border: 1px solid transparent;
    padding: .38rem .6rem; border-radius: var(--pb-radius-xs); cursor: pointer; font-size: .76rem; text-decoration: none;
    transition: all .18s var(--pb-ease); display: inline-flex; align-items: center; gap: .38rem; white-space: nowrap;
    font-weight: 500;
}
.pb-toolbar button:hover, .pb-toolbar a.tb-link:hover { background: var(--pb-surface2); color: var(--pb-text); }
.pb-toolbar button.active, .pb-toolbar a.tb-link.active { background: var(--pb-primary-light); color: var(--pb-text); border-color: rgba(59,130,246,.45); }
.pb-toolbar button:focus-visible, .pb-toolbar a.tb-link:focus-visible { outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,.4); }
.pb-toolbar button:active { transform: translateY(0); }
.pb-toolbar .tb-divider { width: 1px; height: 20px; background: var(--pb-border); margin: 0 .4rem; }
.pb-toolbar .btn-save { background: var(--pb-primary); color: #fff; border-color: transparent; font-weight: 500; }
.pb-toolbar .btn-save:hover { background: var(--pb-primary-hover); color: #fff; box-shadow: 0 8px 18px rgba(37,99,235,.18); }
.pb-toolbar .btn-publish { background: linear-gradient(180deg, rgba(255,255,255,.08) 0%, transparent 100%), var(--pb-success); color: #fff; border-color: transparent; font-weight: 500; }
.pb-toolbar .btn-publish:hover { filter: brightness(1.02); box-shadow: 0 8px 18px rgba(34,197,94,.16); }

.pb-layout { display: flex; height: calc(100vh - 52px); overflow: hidden; }

.pb-panel {
    width: 300px; background: linear-gradient(180deg, rgba(255,255,255,.01) 0%, transparent 100%), var(--pb-surface);
    display: flex; flex-direction: column;
    border-right: 1px solid var(--pb-border); flex-shrink: 0; overflow: hidden;
}
.pb-panel-right { border-right: none; border-left: 1px solid var(--pb-border); }

.pb-panel-tabs { display: flex; align-items: center; gap: .25rem; border-bottom: 1px solid var(--pb-border); background: transparent; padding: .35rem .5rem; }
.pb-panel-tab {
    flex: 1; padding: .48rem .4rem; text-align: center; cursor: pointer; font-size: .75rem;
    color: var(--pb-text2); border: 1px solid transparent; background: transparent;
    border-radius: var(--pb-radius-xs); transition: all .18s var(--pb-ease); font-weight: 500; letter-spacing: -.01em;
}
.pb-panel-tab.active { color: var(--pb-text); background: var(--pb-primary-light); border-color: rgba(59,130,246,.45); }
.pb-panel-tab:hover { color: var(--pb-text); background: rgba(255,255,255,.01); }
.pb-panel-tab:focus-visible { outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,.4); }
.pb-panel-body { flex: 1; overflow-y: auto; padding: .8rem .9rem; }
.pb-panel-body::-webkit-scrollbar { width: 6px; }
.pb-panel-body::-webkit-scrollbar-track { background: transparent; }
.pb-panel-body::-webkit-scrollbar-thumb { background: rgba(148,163,184,.25); border-radius: 10px; }
.pb-panel-body::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,.4); }

.pb-widget-group { margin-bottom: .9rem; }
.pb-widget-group-title {
    font-size: .64rem; text-transform: uppercase; color: var(--pb-text2);
    margin-bottom: .55rem; font-weight: 600; letter-spacing: .9px; display: flex; align-items: center; gap: .4rem;
}
.pb-widget-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .45rem; }
.pb-widget-item {
    background: linear-gradient(180deg, rgba(255,255,255,.01) 0%, transparent 100%), var(--pb-surface2);
    border: 1px solid var(--pb-border); border-radius: var(--pb-radius); position: relative;
    padding: .7rem .5rem; text-align: center; cursor: grab; font-size: .73rem;
    transition: all .18s var(--pb-ease); display: flex; flex-direction: column; align-items: center; gap: .25rem;
}
.pb-widget-item:hover {
    border-color: rgba(59,130,246,.6); background: var(--pb-primary-light);
    transform: translateY(-1px); box-shadow: var(--pb-shadow-sm);
}
.pb-widget-item:focus-visible { outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,.4); }
.pb-widget-item:active { cursor: grabbing; transform: translateY(0); }
.pb-widget-item.dragging { opacity: .4; }
.pb-widget-icon { font-size: 1.18rem; display: block; opacity: .9; color: var(--pb-text); }
.pb-widget-label { display: block; font-weight: 500; color: var(--pb-text); letter-spacing: -.01em; }

.pb-structure-tree { list-style: none; }
.pb-structure-item {
    padding: .45rem .55rem; border-radius: 6px; cursor: pointer; font-size: .78rem;
    display: flex; align-items: center; gap: .4rem; margin-bottom: 2px;
    transition: all .15s;
}
.pb-structure-item:hover { background: var(--pb-surface2); }
.pb-structure-item.active { background: var(--pb-primary); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.pb-structure-item .si-icon { font-size: .85rem; }
.pb-structure-item .si-type { color: var(--pb-text2); font-size: .65rem; }
.pb-structure-item.active .si-type { color: rgba(255,255,255,.7); }
.pb-structure-children { padding-left: 1.2rem; list-style: none; }

.pb-canvas-wrap {
    flex: 1; overflow: auto; background: radial-gradient(1200px 800px at 50% 0, rgba(37,99,235,.05) 0%, transparent 60%), var(--pb-canvas-bg);
    display: flex; justify-content: center; align-items: flex-start; padding: 2.2rem 2rem;
    background-image: radial-gradient(circle, rgba(15,23,42,.04) 1px, transparent 1px);
    background-size: 18px 18px;
}
.pb-canvas {
    width: 100%; max-width: 1180px; background: #fff; min-height: 640px;
    box-shadow: var(--pb-shadow); border-radius: var(--pb-radius-lg);
    position: relative; transition: max-width .4s var(--pb-ease), box-shadow .4s var(--pb-ease);
    color: #1f2937; font-size: 15px; line-height: 1.7; display: flex; flex-direction: column;
    border: 1px solid rgba(15,23,42,.02);
    /* never squeezed to the viewport height: the white sheet follows the content */
    flex: 1 0 auto;
}
.pb-canvas.is-mobile { max-width: 375px; }
.pb-canvas.is-tablet { max-width: 768px; }
.pb-canvas-dropzone { flex: 1; padding: 1.2rem 1.4rem; }
.pb-drop-cap:first-letter { font-size: 3em; float: left; line-height: 1; margin-right: 10px; }

.pb-el {
    position: relative; padding: .5rem .5rem .5rem 1.6rem; min-height: 30px; border: 2px solid transparent;
    transition: border-color .2s, box-shadow .2s; border-radius: 4px;
}
.pb-el:hover { border-color: rgba(99,102,241,.25); background: rgba(99,102,241,.02); }
.pb-el.selected { border-color: var(--pb-primary); box-shadow: 0 0 0 1px var(--pb-primary), 0 4px 12px rgba(99,102,241,.12); }
.pb-el.drop-over { border-color: var(--pb-accent); background: var(--pb-primary-light); }
.pb-el.drop-target { border-color: var(--pb-success) !important; background: rgba(34,197,94,.06); }

.pb-el-drag {
    position: absolute; left: 0; top: 0; bottom: 0; width: 1.2rem;
    display: flex; align-items: center; justify-content: center;
    cursor: grab; color: var(--pb-text2); opacity: 0; transition: opacity .15s;
    font-size: .65rem; letter-spacing: 1px; user-select: none; z-index: 5;
}
.pb-el:hover > .pb-el-drag, .pb-el.selected > .pb-el-drag { opacity: .6; }
.pb-el-drag:hover { opacity: 1 !important; color: var(--pb-primary); }
.pb-el-drag:active { cursor: grabbing; }

.drop-before, .drop-after {
    position: relative; z-index: 10;
}
.drop-before::before, .drop-after::after {
    content: ''; display: block; height: 3px; background: var(--pb-primary);
    border-radius: 2px; margin: 2px 0; box-shadow: 0 0 6px rgba(99,102,241,.4);
    animation: dropLinePulse 1s ease-in-out infinite;
}
.drop-before::before { margin-bottom: 4px; }
.drop-after::after { margin-top: 4px; }
@keyframes dropLinePulse { 0%,100% { opacity: 1; } 50% { opacity: .5; } }

.pb-el-toolbar {
    display: none; position: absolute; top: -30px; left: 0; z-index: 50;
    background: var(--pb-primary); color: #fff; border-radius: 6px 6px 0 0;
    padding: 3px 8px; font-size: .68rem; gap: 2px; align-items: center;
    box-shadow: 0 -2px 8px rgba(99,102,241,.2);
}
.pb-el.selected > .pb-el-toolbar, .pb-el:hover > .pb-el-toolbar { display: inline-flex; }
.pb-el-name { font-weight: 600; margin-right: .5rem; }
.pb-el-type { opacity: .6; font-size: .6rem; text-transform: uppercase; letter-spacing: .5px; }
.pb-el-action {
    background: none; border: none; color: #fff; cursor: pointer; padding: 2px 6px;
    border-radius: 4px; font-size: 1rem; line-height: 1; transition: background .15s;
}
.pb-el-action:hover { background: rgba(255,255,255,.2); }
.pb-el-content { min-height: 20px; }

.pb-empty-canvas {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 400px; color: #94a3b8; border: 3px dashed #e2e8f0; border-radius: 16px;
    margin: 1rem; padding: 3rem 2rem; text-align: center; transition: all .3s;
    background: rgba(248,250,252,.4);
}
.pb-empty-canvas.drag-over { border-color: var(--pb-accent); background: rgba(99,102,241,.04); }
.pb-empty-canvas .pb-empty-icon { font-size: 3.5rem; margin-bottom: 1rem; opacity: .6; }
.pb-empty-canvas p { font-size: .9rem; line-height: 1.6; }
.pb-empty-canvas p strong { color: #64748b; }

.pb-layout-templates { padding: .5rem; }
.pb-layout-card {
    background: var(--pb-surface2); border: 1px solid var(--pb-border); border-radius: 10px;
    margin-bottom: .75rem; cursor: pointer; transition: all .2s; overflow: hidden;
}
.pb-layout-card:hover { border-color: var(--pb-accent); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,.15); }
.pb-layout-card.active { border-color: var(--pb-success); }
.pb-layout-card-preview {
    height: 90px; background: var(--pb-bg); display: flex; align-items: center;
    justify-content: center; font-size: 1.8rem; color: var(--pb-text2);
}
.pb-layout-card-info { padding: .6rem .75rem; }
.pb-layout-card-info h4 { font-size: .8rem; margin-bottom: .15rem; font-weight: 600; }
.pb-layout-card-info p { font-size: .68rem; color: var(--pb-text2); }
.pb-layout-card .pb-apply-btn {
    display: block; width: 100%; padding: .45rem; background: var(--pb-primary); color: #fff;
    border: none; cursor: pointer; font-size: .73rem; font-weight: 500;
    border-radius: 0 0 9px 9px; transition: background .2s;
}
.pb-layout-card .pb-apply-btn:hover { background: var(--pb-primary-hover); }
.pb-layout-card .pb-apply-btn:disabled { opacity: .5; cursor: not-allowed; }

.pb-settings { padding: 0; display: flex; flex-direction: column; height: 100%; overflow: hidden; }
.pb-settings-form { display: none; flex: 1; flex-direction: column; min-height: 0; overflow: hidden; }
.pb-settings-form.active { display: flex; }
.pb-settings-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    flex: 1; color: var(--pb-text2); text-align: center; padding: 2rem;
}
.pb-settings-empty .pse-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: .35; }
.pb-settings-header {
    padding: .75rem .85rem; border-bottom: 1px solid var(--pb-border);
    display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
    background: rgba(0,0,0,.1);
}
.pb-settings-header h3 { font-size: .82rem; font-weight: 600; }
.pb-settings-header .pb-sh-type { font-size: .65rem; color: var(--pb-text2); text-transform: uppercase; letter-spacing: .5px; }
.pb-settings-body { padding: .5rem; overflow-y: auto; flex: 1; min-height: 0; scroll-behavior: smooth; }
.pb-settings-body::-webkit-scrollbar { width: 6px; }
.pb-settings-body::-webkit-scrollbar-track { background: var(--pb-surface2); }
.pb-settings-body::-webkit-scrollbar-thumb { background: var(--pb-accent); border-radius: 10px; }
.pb-settings-body::-webkit-scrollbar-thumb:hover { background: var(--pb-accent); opacity: .8; }
.pb-editor-tabs {
    display: flex; border-bottom: 1px solid var(--pb-border); flex-shrink: 0; gap: 2px; padding: 4px;
}
.pb-editor-tab {
    flex: 1; padding: .45rem .5rem; text-align: center; font-size: .7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px; cursor: pointer;
    background: transparent; border: 1px solid transparent; color: var(--pb-text2);
    border-radius: 6px; transition: all .2s;
}
.pb-editor-tab:hover { color: var(--pb-text); background: rgba(255,255,255,.05); }
.pb-editor-tab.active { color: var(--pb-accent); background: rgba(99,102,241,.12); border-color: rgba(99,102,241,.2); }
.pb-responsive-tabs {
    display: flex; border-bottom: 1px solid var(--pb-border); flex-shrink: 0; gap: 2px; padding: 4px 8px;
}
.pb-resp-tab {
    flex: 1; padding: .35rem .5rem; text-align: center; font-size: .85rem; cursor: pointer;
    background: transparent; border: 1px solid transparent; color: var(--pb-text2);
    border-radius: 6px; transition: all .2s;
}
.pb-resp-tab:hover { color: var(--pb-text); background: rgba(255,255,255,.05); }
.pb-resp-tab.active { color: var(--pb-accent); background: rgba(99,102,241,.12); border-color: rgba(99,102,241,.2); }

/* Per-device editing */
.pb-device-banner {
    display: flex; gap: .55rem; align-items: flex-start; margin: 0 0 .75rem; padding: .6rem .7rem;
    border-radius: 8px; background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.28);
    font-size: .7rem; line-height: 1.4; color: var(--pb-text2);
}
.pb-device-banner strong { display: block; color: var(--pb-accent); font-size: .74rem; margin-bottom: .1rem; }
.pb-device-banner-icon { color: var(--pb-accent); display: flex; padding-top: 1px; }
.pb-control label { display: flex; align-items: center; gap: .35rem; }
.pb-device-chip {
    display: inline-flex; align-items: center; gap: 2px; margin-left: auto; padding: 1px 4px;
    border-radius: 4px; color: var(--pb-text2); opacity: .55; border: 1px dashed transparent;
}
.pb-device-chip.is-set { opacity: 1; color: #fff; background: var(--pb-accent); }
.pb-device-reset {
    display: inline-flex; align-items: center; background: none; border: 0; padding: 0 0 0 2px;
    color: inherit; cursor: pointer; opacity: .85;
}
.pb-device-reset:hover { opacity: 1; }
.pb-settings-section { margin-bottom: .8rem; }
.pb-settings-section-title {
    font-size: .6rem; text-transform: uppercase; color: var(--pb-text2);
    font-weight: 700; letter-spacing: .8px; margin-bottom: .4rem; padding-bottom: .3rem;
    border-bottom: 1px solid var(--pb-border);
}
.pb-control { margin-bottom: .45rem; }
.pb-control label { display: block; font-size: .7rem; margin-bottom: .2rem; color: var(--pb-text2); font-weight: 500; }
.pb-control input, .pb-control select, .pb-control textarea {
    width: 100%; padding: .3rem .5rem; background: var(--pb-surface2); border: 1px solid var(--pb-border);
    border-radius: 4px; color: var(--pb-text); font-size: .75rem; transition: all .2s;
}
.pb-control input:focus, .pb-control select:focus, .pb-control textarea:focus {
    outline: none; border-color: var(--pb-accent); box-shadow: 0 0 0 2px var(--pb-primary-light);
}
.pb-control select { cursor: pointer; appearance: auto; }
.pb-control input[type="color"] { padding: 2px; height: 32px; cursor: pointer; border-radius: 4px; flex-shrink: 0; width: auto; }
.pb-control input[type="number"] { width: 100%; }
.pb-control textarea { resize: vertical; min-height: 50px; font-family: inherit; }

.pb-toast {
    position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%) translateY(20px);
    background: var(--pb-surface); border: 1px solid var(--pb-border); backdrop-filter: blur(12px);
    padding: .65rem 1.2rem; border-radius: 10px; font-size: .8rem;
    box-shadow: 0 8px 32px rgba(0,0,0,.4); z-index: 9999;
    display: flex; gap: .75rem; align-items: center;
    animation: toastIn .3s cubic-bezier(.4,0,.2,1) forwards;
}
.pb-toast.pb-toast-out { animation: toastOut .25s cubic-bezier(.4,0,.2,1) forwards; }
.pb-toast button { background: var(--pb-primary); color: #fff; border: none; padding: .3rem .8rem; border-radius: 6px; cursor: pointer; font-size: .73rem; font-weight: 500; }
@keyframes toastIn { to { transform: translateX(-50%) translateY(0); opacity: 1; } }
@keyframes toastOut { to { transform: translateX(-50%) translateY(20px); opacity: 0; } }

.saving-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.4);
    display: flex; align-items: center; justify-content: center; z-index: 99999;
    backdrop-filter: blur(4px); animation: fadeIn .2s;
}
.saving-overlay .saving-card {
    background: var(--pb-surface); border: 1px solid var(--pb-border);
    border-radius: 16px; padding: 2rem 2.5rem; display: flex; flex-direction: column;
    align-items: center; gap: .75rem; box-shadow: 0 16px 48px rgba(0,0,0,.3);
}
.saving-overlay .spinner { width: 36px; height: 36px; border: 3px solid var(--pb-border); border-top-color: var(--pb-accent); border-radius: 50%; animation: spin .6s linear infinite; }
.saving-overlay .saving-text { font-size: .85rem; color: var(--pb-text2); }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.pb-drag-ghost {
    position: fixed; pointer-events: none; z-index: 99999;
    background: var(--pb-primary); color: #fff; padding: .4rem .8rem;
    border-radius: 6px; font-size: .8rem; font-weight: 500;
    box-shadow: 0 8px 24px rgba(99,102,241,.4); transform: translate(-50%, -50%);
}

.pb-navigator-body { overflow-y: auto; flex: 1; padding: .4rem 0; }
.pb-navigator-body::-webkit-scrollbar { width: 4px; }
.pb-navigator-body::-webkit-scrollbar-thumb { background: var(--pb-border); border-radius: 10px; }

.pb-nav-item {
    display: flex; align-items: center; gap: .4rem; padding: .35rem .6rem;
    cursor: pointer; font-size: .75rem; transition: background .15s;
    border-left: 3px solid transparent; user-select: none;
}
.pb-nav-item:hover { background: var(--pb-surface2); }
.pb-nav-item.active { background: rgba(99,102,241,.1); border-left-color: var(--pb-accent); color: var(--pb-accent); font-weight: 600; }
.pb-nav-item .nav-icon { font-size: .8rem; width: 18px; text-align: center; flex-shrink: 0; }
.pb-nav-item .nav-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pb-nav-item .nav-type { font-size: .6rem; color: var(--pb-text2); flex-shrink: 0; }
.pb-nav-item .nav-toggle { font-size: .6rem; cursor: pointer; padding: 0 2px; color: var(--pb-text2); transition: transform .2s; flex-shrink: 0; }
.pb-nav-item .nav-toggle.expanded { transform: rotate(90deg); }
.pb-nav-children { padding-left: .9rem; min-height: 6px; }
.pb-nav-tree { min-height: 40px; padding-bottom: 1rem; }
.pb-nav-children:empty { min-height: 14px; border-left: 1px dashed var(--pb-border); margin-left: .9rem; }

.pb-nav-item.drag-over { background: rgba(99,102,241,.15); border-left-color: var(--pb-accent); }
.pb-nav-item.drag-indicator { border-top: 2px solid var(--pb-accent); }

.pb-nav-context, .pb-canvas-context {
    position: fixed; z-index: 99999; background: var(--pb-surface);
    border: 1px solid var(--pb-border); border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0,0,0,.3); padding: .3rem 0; min-width: 160px;
}
.pb-nav-context-item, .pb-canvas-context-item {
    display: flex; align-items: center; gap: .5rem; padding: .4rem .8rem;
    font-size: .75rem; cursor: pointer; transition: background .1s;
}
.pb-nav-context-item:hover, .pb-canvas-context-item:hover { background: var(--pb-surface2); }
.pb-nav-context-item.danger, .pb-canvas-context-item.danger { color: var(--pb-danger); }
.pb-nav-context-sep, .pb-canvas-context-sep { height: 1px; background: var(--pb-border); margin: .2rem 0; }

.pb-nav-rename-input {
    font-size: .75rem; padding: 1px 4px; background: var(--pb-surface2);
    border: 1px solid var(--pb-accent); border-radius: 3px; color: var(--pb-text);
    width: 120px; outline: none;
}

.pb-el-children {
    display: flex;
    flex-direction: column;
    gap: 0;
    min-height: 20px;
}

.pb-section-content > .pb-el-children,
.pb-column-content > .pb-el-children,
.pb-inner-section-editor > .pb-el-children {
    padding: .25rem;
    min-height: 40px;
}

/* Sections and inner sections lay their children out as a row of columns */
.pb-el-children.pb-row {
    flex-direction: row;
    flex-wrap: wrap;
    align-items: stretch;
}
.pb-el-children.pb-row > .pb-el[data-el-type="column"] {
    min-width: 0;
    box-sizing: border-box;
}
.pb-el-children.pb-row > .pb-el:not([data-el-type="column"]),
.pb-el-children.pb-row > .pb-empty-drop {
    flex: 1 1 100%;
}
.pb-el[data-el-type="column"] > .pb-el-content { height: 100%; }
.pb-inner-section-editor { padding-top: 14px !important; }

.pb-section-editor {
    border: 2px dashed rgba(99,102,241,.3);
    border-radius: 6px;
    min-height: 60px;
    position: relative;
}

.pb-section-header {
    font-size: .6rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.6);
    background: rgba(99,102,241,.5);
    padding: 2px 8px;
    border-radius: 4px 4px 0 0;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 2;
}

.pb-section-content {
    padding: .5rem;
    min-height: 40px;
}

.pb-column-editor {
    border: 1px dashed rgba(99,102,241,.25);
    border-radius: 4px;
    min-height: 40px;
    position: relative;
}

.pb-column-header {
    font-size: .55rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(99,102,241,.6);
    background: rgba(99,102,241,.08);
    padding: 1px 6px;
    border-radius: 3px 3px 0 0;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 2;
}

.pb-column-content {
    padding: .25rem;
    min-height: 30px;
}

[data-el-type="section"] {
    border-color: rgba(99,102,241,.15);
}

[data-el-type="column"] {
    border-color: rgba(99,102,241,.1);
}

.pb-empty-drop {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60px;
    border: 2px dashed var(--pb-border);
    border-radius: 6px;
    color: var(--pb-text2);
    font-size: 1.5rem;
    transition: all 0.2s;
}

/* "+" between two sections: an invisible 28px hover band centered on the gap */
.pb-section-inserter { position: relative; height: 0; z-index: 30; }
.pb-section-inserter::before {
    content: ''; position: absolute; left: 0; right: 0; top: -14px; height: 28px;
}
.pb-section-inserter::after {
    content: ''; position: absolute; left: 8%; right: 8%; top: -1px; height: 2px; border-radius: 2px;
    background: var(--pb-accent); opacity: 0; transition: opacity .15s; pointer-events: none;
}
.pb-section-inserter-btn {
    position: absolute; left: 50%; top: 0; transform: translate(-50%, -50%) scale(.85);
    width: 28px; height: 28px; border-radius: 50%; border: 2px solid #fff; padding: 0;
    background: var(--pb-accent); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 10px rgba(99,102,241,.45); opacity: 0; transition: opacity .15s, transform .15s; z-index: 1;
}
.pb-section-inserter:hover::after, .pb-section-inserter:hover .pb-section-inserter-btn { opacity: 1; }
.pb-section-inserter:hover .pb-section-inserter-btn { transform: translate(-50%, -50%) scale(1); }
.pb-section-inserter-btn:hover { background: var(--pb-primary); }
body.pb-is-dragging .pb-section-inserter { display: none; }

.pb-empty-drop { cursor: pointer; user-select: none; }
.pb-empty-drop:hover { border-color: var(--pb-accent); color: var(--pb-accent); background: var(--pb-primary-light); }

/* Small "+" at the end of filled containers, revealed on hover/selection */
.pb-empty-drop.pb-add-inside {
    min-height: 0; height: 22px; margin-top: 4px; border-width: 1px; border-radius: 4px;
    opacity: 0; transition: opacity .15s, border-color .15s, background .15s;
}
.pb-el-children:hover > .pb-add-inside,
.pb-add-inside:hover,
.pb-add-inside.pb-insert-target { opacity: 1; }
body.pb-is-dragging .pb-add-inside { display: none; }

/* Container chosen as insert target */
.pb-empty-drop.pb-insert-target {
    opacity: 1; border-style: solid; border-color: var(--pb-accent); color: #fff;
    background: var(--pb-accent); animation: pbInsertPulse 1.2s ease-in-out infinite;
}
.pb-el.pb-insert-parent { border-color: var(--pb-accent) !important; }
body.pb-has-insert-target #panel-widgets .pb-widget-item { box-shadow: 0 0 0 1px rgba(99,102,241,.35); }
@keyframes pbInsertPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,.45); } 50% { box-shadow: 0 0 0 6px rgba(99,102,241,0); } }

.drop-over .pb-empty-drop {
    border-color: var(--pb-accent);
    background: var(--pb-primary-light);
}

.drop-over {
    position: relative;
}

.drop-over::after {
    content: 'Solte aqui';
    position: absolute;
    bottom: -24px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--pb-primary);
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    white-space: nowrap;
    z-index: 10;
    pointer-events: none;
}

/* ========== SortableJS Styles - Elementor Style ========== */

.pb-sortable-ghost {
    opacity: .4;
    border: 2px dashed var(--pb-accent) !important;
    background: var(--pb-primary-light) !important;
    border-radius: 4px;
}

.pb-sortable-chosen {
    cursor: grabbing !important;
}

.pb-sortable-drag {
    opacity: .9 !important;
    box-shadow: 0 8px 32px rgba(99,102,241,.3) !important;
    border: 2px solid var(--pb-primary) !important;
    border-radius: 4px;
    z-index: 10000 !important;
}

/* Widget panel ghost when dragging */
.pb-widget-ghost {
    opacity: .3;
    border: 2px dashed var(--pb-accent) !important;
    background: var(--pb-primary-light) !important;
}

.pb-widget-chosen {
    border-color: var(--pb-accent) !important;
    background: var(--pb-primary-light) !important;
    transform: scale(1.02);
}

.pb-widget-dragging {
    opacity: .8 !important;
    transform: rotate(2deg) scale(1.05) !important;
    box-shadow: 0 12px 40px rgba(99,102,241,.4) !important;
    z-index: 100000 !important;
    border-color: var(--pb-primary) !important;
}

/* Body state when dragging */
body.pb-is-dragging {
    cursor: grabbing !important;
}
body.pb-is-dragging * {
    cursor: grabbing !important;
}

/* Empty canvas drop zone when dragging */
body.pb-is-dragging .pb-empty-canvas {
    border-color: var(--pb-accent);
    background: rgba(99,102,241,.06);
    animation: emptyCanvasPulse 1.5s ease-in-out infinite;
}
body.pb-is-dragging .pb-empty-canvas .pb-empty-icon {
    animation: emptyIconBounce .8s ease-in-out infinite;
}

@keyframes emptyCanvasPulse {
    0%, 100% { border-color: var(--pb-accent); }
    50% { border-color: var(--pb-primary); background: rgba(99,102,241,.1); }
}

@keyframes emptyIconBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

/* Container highlight when dragging over */
body.pb-is-dragging .pb-el[data-is-container="true"] {
    transition: all .2s;
}
body.pb-is-dragging .pb-el[data-is-container="true"].pb-sortable-ghost {
    border: 2px dashed var(--pb-accent) !important;
    background: rgba(99,102,241,.08) !important;
    min-height: 80px;
}

/* Navigator sortable styles */
.pb-nav-ghost {
    opacity: .4;
    background: rgba(99,102,241,.15) !important;
    border-left: 3px solid var(--pb-accent) !important;
}

.pb-nav-chosen {
    background: rgba(99,102,241,.1) !important;
    border-left-color: var(--pb-accent) !important;
    color: var(--pb-accent) !important;
    font-weight: 600;
}

/* Drop indicator line (Elementor style) */
.sortable-drag .pb-el {
    box-shadow: 0 4px 16px rgba(99,102,241,.2) !important;
}

/* Smooth animations for all sortable containers */
.pb-el-children,
#canvas-dropzone,
.pb-nav-children {
    transition: min-height .2s ease;
}

/* Widget panel hover effect improvement */
body.pb-is-dragging .pb-panel:not(.pb-panel-right) {
    background: rgba(99,102,241,.05);
    border-right-color: var(--pb-accent);
}

/* ========== Widget Search ========== */
.pb-widget-search {
    position: relative; padding: .5rem .75rem; border-bottom: 1px solid var(--pb-border);
    background: rgba(0,0,0,.1);
}
.pb-widget-search input {
    width: 100%; padding: .45rem .6rem .45rem 1.8rem; background: var(--pb-surface2);
    border: 1px solid var(--pb-border); border-radius: 6px; color: var(--pb-text);
    font-size: .75rem; transition: all .2s; outline: none;
}
.pb-widget-search input:focus { border-color: var(--pb-accent); box-shadow: 0 0 0 2px var(--pb-primary-light); }
.pb-widget-search input::placeholder { color: var(--pb-text2); opacity: .6; }
.pb-widget-search-icon {
    position: absolute; left: 1.1rem; top: 50%; transform: translateY(-50%);
    font-size: .7rem; color: var(--pb-text2); pointer-events: none; opacity: .5;
}
.pb-widget-item.pb-widget-hidden { display: none !important; }
.pb-widget-group.pb-widget-hidden { display: none !important; }

/* ========== Device Frames ========== */
.pb-device-frame {
    position: relative; display: flex; flex-direction: column; align-items: center; width: 100%;
    /* grows with the page; at least as tall as the visible editor area */
    min-height: 100%; flex-shrink: 0;
    transition: all .4s cubic-bezier(.4,0,.2,1);
}
.pb-device-notch {
    position: absolute; top: 0; left: 50%; transform: translateX(-50%);
    width: 90px; height: 6px; background: #1a1a2e; border-radius: 0 0 8px 8px; z-index: 3;
}
.pb-device-frame.tablet .pb-device-notch { width: 60px; height: 4px; }
.pb-device-label {
    position: absolute; top: calc(100% + 18px); left: 50%; transform: translateX(-50%); white-space: nowrap;
    font-size: .65rem; color: var(--pb-text2); padding: .2rem .6rem;
    background: var(--pb-surface2); border-radius: 4px; font-weight: 500;
    letter-spacing: .3px; text-transform: uppercase;
}

/* Tablet / mobile: the frame takes the device width and grows with the content, so the bezel always wraps the whole page */
body.responsive-tablet .pb-device-frame,
body.responsive-mobile .pb-device-frame {
    box-sizing: border-box; flex: 0 0 auto; align-self: flex-start;
    height: auto; min-height: 0; max-width: 100%;
    background: #1a1a2e; border: 0;
    box-shadow: 0 0 0 1px rgba(255,255,255,.06) inset, 0 18px 50px rgba(15,23,42,.22);
    margin-bottom: 2.5rem; /* room for the label below */
}
body.responsive-tablet .pb-device-frame { width: calc(768px + 28px); padding: 14px; border-radius: 22px; }
body.responsive-mobile .pb-device-frame { width: calc(375px + 20px); padding: 22px 10px; border-radius: 34px; }

body.responsive-tablet .pb-device-frame .pb-canvas,
body.responsive-mobile .pb-device-frame .pb-canvas {
    max-width: none; width: 100%; box-shadow: none; border: 0;
}
body.responsive-tablet .pb-device-frame .pb-canvas { border-radius: 8px; min-height: 900px; }
body.responsive-mobile .pb-device-frame .pb-canvas { border-radius: 18px; min-height: 667px; }
body.responsive-mobile .pb-device-notch { top: 8px; width: 70px; height: 6px; border-radius: 6px; background: #0b0b16; }
body.responsive-tablet .pb-device-notch { top: 5px; width: 6px; height: 6px; border-radius: 50%; background: #0b0b16; }

/* ========== Onboarding Tour ========== */
.pb-tour-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 99998;
    backdrop-filter: blur(2px); animation: fadeIn .3s;
}
.pb-tour-tooltip {
    position: fixed; z-index: 99999; background: var(--pb-surface);
    border: 1px solid var(--pb-accent); border-radius: 12px;
    padding: 1.2rem 1.4rem; max-width: 320px; min-width: 240px;
    box-shadow: 0 16px 48px rgba(99,102,241,.25), 0 0 0 1px rgba(99,102,241,.1);
    animation: tourPop .3s cubic-bezier(.34,1.56,.64,1);
}
.pb-tour-tooltip::before {
    content: ''; position: absolute; width: 12px; height: 12px;
    background: var(--pb-surface); border: 1px solid var(--pb-accent);
    transform: rotate(45deg); z-index: -1;
}
.pb-tour-tooltip.arrow-bottom::before { top: -7px; left: 24px; border-right: none; border-bottom: none; }
.pb-tour-tooltip.arrow-top::before { bottom: -7px; left: 24px; border-left: none; border-top: none; }
.pb-tour-tooltip.arrow-left::before { right: -7px; top: 24px; border-left: none; border-bottom: none; }
.pb-tour-tooltip.arrow-right::before { left: -7px; top: 24px; border-right: none; border-top: none; }
.pb-tour-step { font-size: .6rem; color: var(--pb-accent); text-transform: uppercase; letter-spacing: .8px; font-weight: 700; margin-bottom: .4rem; }
.pb-tour-title { font-size: .95rem; font-weight: 600; margin-bottom: .35rem; color: var(--pb-text); }
.pb-tour-desc { font-size: .78rem; color: var(--pb-text2); line-height: 1.5; margin-bottom: 1rem; }
.pb-tour-actions { display: flex; align-items: center; gap: .5rem; }
.pb-tour-btn {
    padding: .4rem .9rem; border-radius: 6px; font-size: .73rem; font-weight: 600;
    cursor: pointer; border: none; transition: all .2s;
}
.pb-tour-btn-primary { background: var(--pb-primary); color: #fff; }
.pb-tour-btn-primary:hover { background: var(--pb-primary-hover); transform: translateY(-1px); }
.pb-tour-btn-secondary { background: transparent; color: var(--pb-text2); border: 1px solid var(--pb-border); }
.pb-tour-btn-secondary:hover { color: var(--pb-text); border-color: var(--pb-text2); }
.pb-tour-skip { margin-left: auto; font-size: .68rem; color: var(--pb-text2); cursor: pointer; background: none; border: none; opacity: .6; transition: opacity .2s; }
.pb-tour-skip:hover { opacity: 1; }
@keyframes tourPop { from { opacity: 0; transform: scale(.9) translateY(8px); } to { opacity: 1; transform: scale(1) translateY(0); } }

/* ========== Resizable Panels ========== */
.pb-panel-resize {
    width: 4px; cursor: col-resize; background: transparent; position: relative;
    flex-shrink: 0; z-index: 10; transition: background .15s;
}
.pb-panel-resize:hover, .pb-panel-resize.active {
    background: var(--pb-accent);
}
.pb-panel-resize::after {
    content: ''; position: absolute; top: 0; bottom: 0; left: -4px; right: -4px;
}
.pb-layout { position: relative; }

/* ========== Multi-Select ========== */
.pb-el.multi-selected {
    border-color: var(--pb-warning) !important;
    box-shadow: 0 0 0 1px var(--pb-warning), 0 4px 12px rgba(245,158,11,.12) !important;
}
.pb-multi-toolbar {
    position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
    background: var(--pb-surface); border: 1px solid var(--pb-border);
    border-radius: 12px; padding: .5rem .75rem; display: flex; align-items: center;
    gap: .5rem; z-index: 9999; box-shadow: 0 8px 32px rgba(0,0,0,.3);
    animation: toastIn .25s cubic-bezier(.4,0,.2,1) forwards;
    backdrop-filter: blur(12px);
}
.pb-multi-count {
    font-size: .73rem; font-weight: 600; color: var(--pb-warning);
    padding-right: .5rem; border-right: 1px solid var(--pb-border);
}
.pb-multi-btn {
    padding: .35rem .65rem; border-radius: 6px; font-size: .7rem; font-weight: 500;
    cursor: pointer; border: 1px solid var(--pb-border); background: var(--pb-surface2);
    color: var(--pb-text); transition: all .15s; display: flex; align-items: center; gap: .3rem;
}
.pb-multi-btn:hover { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); }
.pb-multi-btn.danger:hover { background: var(--pb-danger); border-color: var(--pb-danger); }

/* ========== Shape Dividers ========== */
.pb-shape-divider {
    pointer-events: none;
    line-height: 0;
}
.pb-shape-divider svg {
    display: block;
    width: 100%;
    height: 100%;
}
.pb-shape-divider-top {
    transform: rotate(180deg);
}

/* ========== Background Overlay ========== */
.pb-section-overlay {
    pointer-events: none;
}

/* ========== Section overflow for dividers ========== */
[data-el-type="section"] > .pb-section-editor {
    overflow: hidden;
}

/* ========== Navigator Search ========== */
.pb-nav-search {
    position: relative; padding: .5rem .75rem; border-bottom: 1px solid var(--pb-border);
    background: rgba(0,0,0,.1);
}
.pb-nav-search input {
    width: 100%; padding: .45rem .6rem .45rem 1.8rem; background: var(--pb-surface2);
    border: 1px solid var(--pb-border); border-radius: 6px; color: var(--pb-text);
    font-size: .75rem; transition: all .2s; outline: none;
}
.pb-nav-search input:focus { border-color: var(--pb-accent); box-shadow: 0 0 0 2px var(--pb-primary-light); }
.pb-nav-search-icon {
    position: absolute; left: 1.1rem; top: 50%; transform: translateY(-50%);
    font-size: .7rem; color: var(--pb-text2); pointer-events: none; opacity: .5;
}

/* ========== Scroll Animations ========== */
.pb-scroll-animated.pb-scroll-fade-up { opacity: 1 !important; transform: translateY(0) !important; }
.pb-scroll-animated.pb-scroll-fade-down { opacity: 1 !important; transform: translateY(0) !important; }
.pb-scroll-animated.pb-scroll-fade-left { opacity: 1 !important; transform: translateX(0) !important; }
.pb-scroll-animated.pb-scroll-fade-right { opacity: 1 !important; transform: translateX(0) !important; }
.pb-scroll-animated.pb-scroll-zoom-in { opacity: 1 !important; transform: scale(1) !important; }
.pb-scroll-animated.pb-scroll-zoom-out { opacity: 1 !important; transform: scale(1) !important; }
.pb-scroll-animated.pb-scroll-slide-up { opacity: 1 !important; transform: translateY(0) !important; }
.pb-scroll-animated.pb-scroll-slide-down { opacity: 1 !important; transform: translateY(0) !important; }
[data-scroll-animation="fade-up"] { transform: translateY(30px); }
[data-scroll-animation="fade-down"] { transform: translateY(-30px); }
[data-scroll-animation="fade-left"] { transform: translateX(30px); }
[data-scroll-animation="fade-right"] { transform: translateX(-30px); }
[data-scroll-animation="zoom-in"] { transform: scale(0.8); }
[data-scroll-animation="zoom-out"] { transform: scale(1.2); }
[data-scroll-animation="slide-up"] { transform: translateY(50px); }
[data-scroll-animation="slide-down"] { transform: translateY(-50px); }

/* ========== Video Background ========== */
.pb-section-video { pointer-events: none; }

/* ========== Parallax ========== */
.pb-parallax { will-change: transform; }
.pb-parallax .pb-section-inner { will-change: transform; transition: transform .1s linear; }

/* ========== Gradient Preview ========== */
.pb-gradient-preview { height: 32px; border-radius: 6px; border: 1px solid var(--pb-border); }

/* ========== Column Structure Picker ========== */
#pb-col-picker { font-family: 'Inter', system-ui, sans-serif; }

/* ========== Finder / Command Palette ========== */
#pb-finder-overlay input::placeholder { color: var(--pb-text2); }

/* ========== Lightbox ========== */
.pb-lightbox-trigger { cursor: pointer; }

/* ========== Element Hover Handles (Elementor Style) ========== */
.pb-el:hover > .pb-el-toolbar { display: inline-flex; }
.pb-el-toolbar .pb-el-action { transition: all .15s; }
.pb-el-toolbar .pb-el-action:hover { background: rgba(255,255,255,.25); transform: scale(1.1); }

/* ========== Widget Favorites ========== */
.pb-widget-item.pb-widget-favorited { border-color: var(--pb-warning); }
.pb-widget-fav-btn {
    position: absolute; top: 2px; right: 2px; background: none; border: none;
    font-size: 10px; cursor: pointer; opacity: 0; transition: opacity .15s; padding: 2px;
    color: var(--pb-text2);
}
.pb-widget-item:hover .pb-widget-fav-btn { opacity: 1; }
.pb-widget-item.pb-widget-favorited .pb-widget-fav-btn { opacity: 1; color: var(--pb-warning); }

/* ========== Counter Widget ========== */
.pb-counter { padding: 8px 0; }

/* ========== Progress Bar ========== */
.pb-progress-fill { transition: width 1.5s cubic-bezier(.4,0,.2,1); }

/* ========== Carousel ========== */
.pb-carousel::-webkit-scrollbar { height: 4px; }
.pb-carousel::-webkit-scrollbar-thumb { background: var(--pb-border); border-radius: 4px; }
.pb-carousel { scrollbar-width: thin; scrollbar-color: var(--pb-border) transparent; }

/* ========== Accessibility ========== */
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
.pb-el:focus-visible { outline: 2px solid var(--pb-primary); outline-offset: 2px; }
/* ========== Icons (Lucide, inline SVG) ========== */
.pb-icon { display: inline-block; flex-shrink: 0; vertical-align: -0.125em; }
.pb-widget-icon { display: flex; justify-content: center; margin-bottom: .2rem; }
.pb-widget-icon .pb-icon { opacity: .85; }
.pb-el-action { display: inline-flex; align-items: center; justify-content: center; padding: 4px; }
.pb-el-drag .pb-icon { opacity: .9; }
.pb-nav-item .nav-icon { display: inline-flex; align-items: center; justify-content: center; opacity: .75; }
.pb-nav-item .nav-toggle { display: inline-flex; align-items: center; }
.pb-nav-context-item .pb-icon, .pb-canvas-context-item .pb-icon { opacity: .7; }
.pb-nav-context-item.danger .pb-icon, .pb-canvas-context-item.danger .pb-icon { opacity: 1; }
.pb-widget-search-icon, .pb-nav-search-icon { display: flex; align-items: center; }
.pb-empty-canvas .pb-empty-icon, .pb-settings-empty .pse-icon { display: flex; justify-content: center; }
.pb-layout-card-preview { display: flex; align-items: center; justify-content: center; }
.pb-toast .pb-icon { margin-right: -.35rem; }

</style>