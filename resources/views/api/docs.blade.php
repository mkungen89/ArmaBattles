<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - {{ config('app.name', 'Reforger Community') }}</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <link rel="icon" href="/favicon.ico">
    <style>
        /* Dark theme base */
        body {
            margin: 0;
            background: #111827;
            color: #e5e7eb;
        }

        /* Header bar */
        .docs-header {
            background: #1f2937;
            border-bottom: 1px solid #374151;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .docs-header-title {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #10b981;
        }
        .docs-header-title span {
            color: #9ca3af;
            font-weight: 400;
        }
        .docs-header a {
            color: #9ca3af;
            text-decoration: none;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            transition: color 0.15s;
        }
        .docs-header a:hover {
            color: #10b981;
        }

        /* Swagger UI dark overrides */
        .swagger-ui {
            color: #e5e7eb;
        }
        .swagger-ui .topbar {
            display: none;
        }
        .swagger-ui .info .title {
            color: #f9fafb;
        }
        .swagger-ui .info .description p,
        .swagger-ui .info .description li,
        .swagger-ui .info .description {
            color: #d1d5db;
        }
        .swagger-ui .info a {
            color: #10b981;
        }
        .swagger-ui .scheme-container {
            background: #1f2937;
            box-shadow: none;
        }
        .swagger-ui .opblock-tag {
            color: #f9fafb;
            border-bottom-color: #374151;
        }
        .swagger-ui .opblock-tag:hover {
            background: #1f2937;
        }
        .swagger-ui .opblock-tag small {
            color: #9ca3af;
        }
        .swagger-ui .opblock {
            background: #1f2937;
            border-color: #374151;
            box-shadow: none;
        }
        .swagger-ui .opblock .opblock-summary {
            border-bottom-color: #374151;
        }
        .swagger-ui .opblock .opblock-summary-description {
            color: #9ca3af;
        }
        .swagger-ui .opblock.opblock-get {
            background: rgba(16, 185, 129, 0.05);
            border-color: rgba(16, 185, 129, 0.3);
        }
        .swagger-ui .opblock.opblock-get .opblock-summary-method {
            background: #059669;
        }
        .swagger-ui .opblock.opblock-post {
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.3);
        }
        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background: #2563eb;
        }
        .swagger-ui .opblock-body pre {
            background: #111827;
            color: #e5e7eb;
        }
        .swagger-ui .opblock-description-wrapper,
        .swagger-ui .opblock-external-docs-wrapper {
            color: #d1d5db;
        }
        .swagger-ui .opblock-description-wrapper p {
            color: #d1d5db;
        }
        .swagger-ui table thead tr td,
        .swagger-ui table thead tr th,
        .swagger-ui .parameter__name,
        .swagger-ui .parameter__type {
            color: #d1d5db;
        }
        .swagger-ui .parameter__name.required::after {
            color: #ef4444;
        }
        .swagger-ui input[type=text],
        .swagger-ui textarea,
        .swagger-ui select {
            background: #111827;
            color: #e5e7eb;
            border-color: #374151;
        }
        .swagger-ui .btn {
            color: #e5e7eb;
            border-color: #6b7280;
        }
        .swagger-ui .btn:hover {
            background: #374151;
        }
        .swagger-ui .btn.authorize {
            color: #10b981;
            border-color: #10b981;
        }
        .swagger-ui .btn.authorize svg {
            fill: #10b981;
        }
        .swagger-ui .dialog-ux .modal-ux {
            background: #1f2937;
            border-color: #374151;
        }
        .swagger-ui .dialog-ux .modal-ux-header h3 {
            color: #f9fafb;
        }
        .swagger-ui .dialog-ux .modal-ux-content p,
        .swagger-ui .dialog-ux .modal-ux-content label {
            color: #d1d5db;
        }
        .swagger-ui .model-box {
            background: #111827;
        }
        .swagger-ui .model {
            color: #d1d5db;
        }
        .swagger-ui .model-title {
            color: #f9fafb;
        }
        .swagger-ui .model .property.primitive {
            color: #10b981;
        }
        .swagger-ui section.models {
            border-color: #374151;
        }
        .swagger-ui section.models h4 {
            color: #f9fafb;
            border-bottom-color: #374151;
        }
        .swagger-ui section.models .model-container {
            background: #1f2937;
            border-bottom-color: #374151;
        }
        .swagger-ui .responses-inner {
            background: transparent;
        }
        .swagger-ui .response-col_status {
            color: #d1d5db;
        }
        .swagger-ui .response-col_description__inner p {
            color: #d1d5db;
        }
        .swagger-ui .markdown code,
        .swagger-ui .renderedMarkdown code {
            background: #111827;
            color: #10b981;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .swagger-ui .markdown pre {
            background: #111827;
            border-radius: 6px;
        }
        .swagger-ui .copy-to-clipboard {
            background: #374151;
        }
        .swagger-ui .opblock .opblock-section-header {
            background: rgba(55, 65, 81, 0.5);
            box-shadow: none;
        }
        .swagger-ui .opblock .opblock-section-header h4 {
            color: #f9fafb;
        }
        .swagger-ui .tab li {
            color: #9ca3af;
        }
        .swagger-ui .tab li.active {
            color: #f9fafb;
        }
        .swagger-ui .loading-container .loading::after {
            color: #10b981;
        }
        .swagger-ui .wrapper {
            max-width: 1200px;
        }
        .swagger-ui .filter .operation-filter-input {
            background: #111827;
            border-color: #374151;
            color: #e5e7eb;
        }
        .swagger-ui .btn.execute {
            background: #059669;
            border-color: #059669;
            color: #fff;
        }
        .swagger-ui .btn.execute:hover {
            background: #047857;
        }
        .swagger-ui .btn-group .btn {
            background: transparent;
        }
        .swagger-ui .responses-table .response-col_description__inner div.markdown p {
            color: #d1d5db;
        }
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #111827;
        }
        ::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="docs-header">
        <div class="docs-header-title">
            {{ config('app.name', 'Reforger Community') }} <span>API Documentation</span>
        </div>
        <a href="/">Back to Site</a>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: '/docs/openapi.yaml',
            dom_id: '#swagger-ui',
            deepLinking: true,
            persistAuthorization: true,
            filter: true,
            docExpansion: 'list',
            defaultModelsExpandDepth: 1,
            defaultModelExpandDepth: 2,
            tryItOutEnabled: false,
        });
    </script>
</body>
</html>
