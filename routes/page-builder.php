<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageBuilder\PageController;
use App\Http\Controllers\PageBuilder\ElementController;
use App\Http\Controllers\PageBuilder\RevisionController;
use App\Http\Controllers\PageBuilder\FormController;
use App\Http\Controllers\PageBuilder\HtmlImportController;
use App\Http\Controllers\PageBuilder\CollaborationController;
use App\Http\Controllers\PageBuilder\ThemeTemplateController;
use App\Http\Controllers\PageBuilder\CustomFontController;
use App\Http\Controllers\PageBuilder\DynamicTagController;
use App\Http\Controllers\PageBuilder\FindReplaceController;
use App\Http\Controllers\PageBuilder\GlobalWidgetController;
use App\Http\Controllers\PageBuilder\ProjectController;

Route::middleware(['web', 'auth'])->prefix('page-builder')->name('page-builder.')->group(function () {
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{project}/dashboard', [ProjectController::class, 'dashboard'])->name('projects.dashboard');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('projects/{project}/move', [ProjectController::class, 'move'])->name('projects.move');
    Route::get('projects/{project}/export-zip', [ProjectController::class, 'exportZip'])->name('projects.export-zip');

    Route::resource('pages', PageController::class)->except(['show', 'edit']);
    Route::get('pages/{page}/editor', [PageController::class, 'edit'])->name('editor');
    Route::get('pages/{page}/render', [PageController::class, 'render'])->name('render');
    Route::get('pages/{page}/data', [PageController::class, 'getData'])->name('pages.data');
    Route::post('pages/{page}/publish', [PageController::class, 'publish'])->name('pages.publish');
    Route::post('pages/{page}/unpublish', [PageController::class, 'unpublish'])->name('pages.unpublish');
    Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::get('pages/{page}/export', [PageController::class, 'export'])->name('pages.export');
    Route::get('pages/{page}/export-zip', [PageController::class, 'exportZip'])->name('pages.export-zip');
    Route::post('pages/import', [PageController::class, 'import'])->name('pages.import');

    Route::post('html-import', [HtmlImportController::class, 'import'])->name('html-import')->middleware('throttle:html-import');
    Route::get('html-import/fetch', [HtmlImportController::class, 'fetch'])->name('html-import.fetch')->middleware('throttle:html-import');
    Route::get('templates', [PageController::class, 'listTemplates'])->name('templates.list');
    Route::post('pages/{page}/apply-template', [PageController::class, 'applyTemplate'])->name('pages.apply-template');
    Route::put('pages/{page}/layout', [PageController::class, 'updateLayout'])->name('pages.layout');
    Route::get('pages/{page}/global-settings', [PageController::class, 'getGlobalSettings'])->name('pages.global-settings');
    Route::put('pages/{page}/global-settings', [PageController::class, 'updateGlobalSettings'])->name('pages.global-settings.update');

    Route::get('pages/{page}/elements', [ElementController::class, 'index'])->name('elements.index');
    Route::post('pages/{page}/elements', [ElementController::class, 'store'])->name('elements.store');
    Route::post('pages/{page}/elements/reorder', [ElementController::class, 'reorder'])->name('elements.reorder');
    Route::post('pages/{page}/elements/restore-snapshot', [ElementController::class, 'restoreSnapshot'])->name('elements.restore-snapshot');
    Route::get('elements/{element}', [ElementController::class, 'show'])->name('elements.show');
    Route::put('elements/{element}', [ElementController::class, 'update'])->name('elements.update');
    Route::delete('elements/{element}', [ElementController::class, 'destroy'])->name('elements.destroy');
    Route::post('elements/{element}/duplicate', [ElementController::class, 'duplicate'])->name('elements.duplicate');
    Route::post('elements/{element}/move', [ElementController::class, 'move'])->name('elements.move');
    Route::put('elements/{element}/settings', [ElementController::class, 'updateSettings'])->name('elements.settings');
    Route::put('elements/{element}/styles', [ElementController::class, 'updateStyles'])->name('elements.styles');
    Route::get('elements/{element}/render', [ElementController::class, 'renderElement'])->name('elements.render');
    Route::get('elements/{element}/controls', [ElementController::class, 'controls'])->name('elements.controls');
    Route::get('widgets/{type}/controls', [ElementController::class, 'widgetControls'])->name('widgets.controls');
    Route::post('upload', [ElementController::class, 'uploadImage'])->name('upload')->middleware('throttle:upload');
    Route::post('upload-video', [ElementController::class, 'uploadVideo'])->name('upload-video')->middleware('throttle:upload');

    Route::get('pages/{page}/revisions', [RevisionController::class, 'index'])->name('revisions.index');
    Route::get('revisions/{revision}', [RevisionController::class, 'show'])->name('revisions.show');
    Route::post('pages/{page}/revisions/{revision}/restore', [RevisionController::class, 'restore'])->name('revisions.restore');
    Route::get('pages/{page}/revisions/{revision}/diff', [RevisionController::class, 'diff'])->name('revisions.diff');
    Route::delete('revisions/{revision}', [RevisionController::class, 'destroy'])->name('revisions.destroy');
    Route::post('pages/{page}/revisions/prune', [RevisionController::class, 'prune'])->name('revisions.prune');
    Route::post('pages/{page}/revisions/auto-save', [RevisionController::class, 'autoSave'])->name('revisions.auto-save');

    Route::get('pages/{page}/form/submissions', [FormController::class, 'submissions'])->name('form.submissions');

    Route::post('pages/{page}/collab/join', [CollaborationController::class, 'join'])->name('collab.join');
    Route::post('pages/{page}/collab/leave', [CollaborationController::class, 'leave'])->name('collab.leave');
    Route::post('pages/{page}/collab/heartbeat', [CollaborationController::class, 'heartbeat'])->name('collab.heartbeat');
    Route::get('pages/{page}/collab/users', [CollaborationController::class, 'activeUsers'])->name('collab.users');
    Route::post('pages/{page}/elements/{elementId}/lock', [CollaborationController::class, 'lockElement'])->name('collab.lock');
    Route::post('pages/{page}/elements/{elementId}/unlock', [CollaborationController::class, 'unlockElement'])->name('collab.unlock');

    Route::get('dynamic-tags', [DynamicTagController::class, 'index'])->name('dynamic-tags');

    Route::post('find-replace/search', [FindReplaceController::class, 'search'])->name('find.search');
    Route::post('find-replace/replace', [FindReplaceController::class, 'replace'])->name('find.replace');

    Route::get('fonts', [CustomFontController::class, 'index'])->name('fonts.index');
    Route::post('fonts', [CustomFontController::class, 'store'])->name('fonts.store');
    Route::put('fonts/{custom_font}', [CustomFontController::class, 'update'])->name('fonts.update');
    Route::delete('fonts/{custom_font}', [CustomFontController::class, 'destroy'])->name('fonts.destroy');
    Route::get('fonts/{custom_font}/download/{format}', [CustomFontController::class, 'download'])->name('fonts.download');

    Route::get('themes', [ThemeTemplateController::class, 'index'])->name('themes.index');
    Route::get('themes/create', [ThemeTemplateController::class, 'create'])->name('themes.create');
    Route::post('themes', [ThemeTemplateController::class, 'store'])->name('themes.store');
    Route::get('themes/{theme_template}/edit', [ThemeTemplateController::class, 'edit'])->name('themes.edit');
    Route::put('themes/{theme_template}', [ThemeTemplateController::class, 'update'])->name('themes.update');
    Route::delete('themes/{theme_template}', [ThemeTemplateController::class, 'destroy'])->name('themes.destroy');
    Route::get('themes/{theme_template}/editor', [ThemeTemplateController::class, 'editor'])->name('themes.editor');
    Route::get('themes/{theme_template}/render', [ThemeTemplateController::class, 'render'])->name('themes.render');
    Route::get('themes/{theme_template}/conditions', [ThemeTemplateController::class, 'editConditions'])->name('themes.conditions');
    Route::get('themes/{theme_template}/conditions-data', [ThemeTemplateController::class, 'getConditions'])->name('themes.conditions.data');
    Route::put('themes/{theme_template}/conditions', [ThemeTemplateController::class, 'updateConditions'])->name('themes.conditions.update');
    Route::post('themes/{theme_template}/publish', [ThemeTemplateController::class, 'publish'])->name('themes.publish');
    Route::post('themes/{theme_template}/unpublish', [ThemeTemplateController::class, 'unpublish'])->name('themes.unpublish');

    Route::get('global-widgets', [GlobalWidgetController::class, 'index'])->name('global-widgets.index');
    Route::post('global-widgets', [GlobalWidgetController::class, 'store'])->name('global-widgets.store');
    Route::get('global-widgets/{global_widget}', [GlobalWidgetController::class, 'show'])->name('global-widgets.show');
    Route::put('global-widgets/{global_widget}', [GlobalWidgetController::class, 'update'])->name('global-widgets.update');
    Route::delete('global-widgets/{global_widget}', [GlobalWidgetController::class, 'destroy'])->name('global-widgets.destroy');
    Route::get('global-widgets/{global_widget}/render', [GlobalWidgetController::class, 'render'])->name('global-widgets.render');

});

Route::middleware(['web'])->prefix('page-builder')->name('page-builder.')->group(function () {
    Route::post('pages/{page}/form/submit', [FormController::class, 'submit'])->name('form.submit')->middleware('throttle:form-submit');
});
