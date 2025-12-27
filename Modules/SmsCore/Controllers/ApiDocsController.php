<?php

namespace Modules\SmsCore\Controllers;

use Parsedown;

class ApiDocsController
{
    /**
     * Display API Documentation
     */
    public function index()
    {
        $docsPath = dirname(__DIR__) . '/Documentation/API_DOCUMENTATION.md';

        if (!file_exists($docsPath)) {
            flash('error', 'Documentation file not found');
            redirect('/admin/sms');
            return;
        }

        $markdown = file_get_contents($docsPath);

        // Convert Markdown to HTML (si Parsedown est disponible)
        $htmlContent = $markdown;
        if (class_exists('Parsedown')) {
            $parsedown = new Parsedown();
            $htmlContent = $parsedown->text($markdown);
        }

        return view('SmsCore/api/docs', [
            'title' => 'Documentation API SMS',
            'markdown' => $markdown,
            'html' => $htmlContent,
            'has_parsedown' => class_exists('Parsedown')
        ]);
    }

    /**
     * Redirect to API Keys module
     */
    public function keys()
    {
        // Rediriger vers le module ApiKeys qui gère déjà les clés API
        redirect('/admin/apikeys');
    }
}
