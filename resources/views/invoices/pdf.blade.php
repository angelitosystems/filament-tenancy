<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Factura {{ $invoice_number ?? 'F001-00000123' }}</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Verdana, Arial, sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
            background: #fff;
            padding: 40px 30px;
        }

        .invoice-container {
            width: 100%;
            max-width: 100%;
        }

        /* Header Section */
        .header-section {
            margin-bottom: 15px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .company-info {
            vertical-align: top;
            width: 60%;
            padding-right: 10px;
        }

        .company-logo {
            font-size: 18pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 8pt;
            line-height: 1.5;
            color: #555;
        }

        .company-details p {
            margin: 2px 0;
        }

        .invoice-info {
            vertical-align: top;
            width: 40%;
            text-align: right;
            padding-left: 10px;
        }

        .invoice-box {
            display: inline-block;
            text-align: left;
            background: #f8fafc;
            padding: 10px;
            border: 2px solid #1e40af;
            border-radius: 4px;
            min-width: 160px;
        }

        .invoice-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .invoice-number {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .invoice-date {
            font-size: 8pt;
            color: #555;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .status-paid {
            background: #10b981;
            color: white;
        }

        .status-pending {
            background: #f59e0b;
            color: white;
        }

        .status-canceled {
            background: #ef4444;
            color: white;
        }

        /* Client and Service Info */
        .info-section {
            margin-bottom: 12px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-cell {
            width: 50%;
            vertical-align: top;
            padding: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .info-cell:first-child {
            border-right: none;
            background: #fff;
        }

        .info-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid #1e40af;
            display: block;
        }

        .info-content {
            font-size: 8.5pt;
            line-height: 1.5;
        }

        .info-content strong {
            color: #0f172a;
            font-size: 9pt;
            display: block;
            margin-bottom: 3px;
        }

        .info-content p {
            margin: 3px 0;
            color: #475569;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 12px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            table-layout: fixed;
        }

        .items-table thead {
            background: #1e40af;
            color: white;
        }

        .items-table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #1e3a8a;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            text-align: center;
            width: 10%;
        }

        .items-table th:first-child,
        .items-table td:first-child {
            width: 52%;
        }

        .items-table th:nth-child(3),
        .items-table td:nth-child(3),
        .items-table th:nth-child(4),
        .items-table td:nth-child(4) {
            text-align: right;
            width: 19%;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .items-table td {
            padding: 6px;
            font-size: 8.5pt;
            color: #334155;
        }

        /* Totals Section */
        .totals-section {
            margin-top: 10px;
            margin-bottom: 12px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .totals-row {
            border-bottom: 1px solid #e2e8f0;
        }

        .totals-label {
            text-align: right;
            padding: 6px 10px;
            font-size: 8.5pt;
            color: #64748b;
            font-weight: 500;
            background: #f8fafc;
            width: 70%;
        }

        .totals-value {
            text-align: right;
            padding: 6px 10px;
            font-size: 8.5pt;
            color: #1e293b;
            font-weight: bold;
            width: 30%;
        }

        .total-final {
            background: #1e40af;
            color: white;
            font-size: 10pt;
            font-weight: bold;
            border-top: 2px solid #3b82f6;
        }

        .total-final .totals-label,
        .total-final .totals-value {
            background: #1e40af;
            color: white;
            padding: 8px 10px;
        }

        /* Footer */
        .footer-section {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }

        .footer-content {
            font-size: 7.5pt;
            color: #64748b;
            line-height: 1.5;
        }

        .footer-content p {
            margin: 4px 0;
        }

        .verification-link {
            font-family: 'Courier New', monospace;
            background: #f1f5f9;
            padding: 6px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 8px;
            font-size: 7pt;
            color: #1e40af;
            word-break: break-all;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header-section">
            <table class="header-table">
                <tr>
                    <td class="company-info">
                        <div class="company-logo">{{ $company_name ?? 'ANGELITO SYSTEMS S.A.C.' }}</div>
                        <div class="company-details">
                            <p><strong>RUC:</strong> {{ $company_ruc ?? '20123456789' }}</p>
                            <p>{{ $company_address ?? 'Av. Javier Prado Este 4567, Surco, Lima, Perú' }}</p>
                            <p><strong>Email:</strong> {{ $company_email ?? 'facturacion@angelitosystems.com' }}</p>
                            <p><strong>Teléfono:</strong> {{ $company_phone ?? '+51 987 654 321' }}</p>
                        </div>
                    </td>
                    <td class="invoice-info">
                        <div class="invoice-box">
                            <div class="invoice-title">Factura Electrónica</div>
                            <div class="invoice-number">{{ $invoice_number ?? 'F001-00000123' }}</div>
                            <div class="invoice-date">
                                <strong>Fecha de Emisión:</strong><br>
                                {{ $invoice_date ?? date('d/m/Y') }}
                            </div>
                            <div class="status-badge status-{{ strtolower($status ?? 'paid') }}">
                                {{ strtoupper($status ?? 'Pagado') }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Client and Service Information -->
        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td class="info-cell">
                        <span class="info-label">Datos del Cliente</span>
                        <div class="info-content">
                            <strong>{{ $client_name ?? 'Cliente' }}</strong>
                            <p><strong>RUC:</strong> {{ $client_ruc ?? 'N/A' }}</p>
                            <p>{{ $client_address ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $client_email ?? 'N/A' }}</p>
                        </div>
                    </td>
                    <td class="info-cell">
                        <span class="info-label">Información del Servicio</span>
                        <div class="info-content">
                            <p><strong>Plan:</strong> {{ $plan_name ?? 'N/A' }}</p>
                            @if (isset($period_start) && isset($period_end) && $period_start && $period_end)
                                <p><strong>Período:</strong> {{ $period_start }} - {{ $period_end }}</p>
                            @endif
                            <p><strong>Método de Pago:</strong> {{ $payment_method ?? 'N/A' }}</p>
                            <p><strong>Condición:</strong> {{ $payment_terms ?? 'Mensual' }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Descripción del Servicio</th>
                        <th>Cant.</th>
                        <th>Precio Unit.</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items ?? [] as $item)
                        <tr>
                            <td>{{ $item['description'] ?? 'Servicio' }}</td>
                            <td>{{ number_format($item['qty'] ?? 1, 0) }}</td>
                            <td>{{ $currency ?? 'USD' }} {{ number_format($item['price'] ?? 0, 2) }}</td>
                            <td>{{ $currency ?? 'USD' }} {{ number_format($item['total'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center" style="padding: 20px; color: #64748b;">
                                No hay items registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr class="totals-row">
                    <td class="totals-label">Subtotal:</td>
                    <td class="totals-value">{{ $currency ?? 'USD' }} {{ number_format($subtotal ?? 0, 2) }}</td>
                </tr>
                @if (isset($discount) && $discount > 0)
                    <tr class="totals-row">
                        <td class="totals-label">Descuento:</td>
                        <td class="totals-value">- {{ $currency ?? 'USD' }} {{ number_format($discount, 2) }}</td>
                    </tr>
                @endif
                @if (isset($igv) && $igv > 0)
                    <tr class="totals-row">
                        <td class="totals-label">IGV (18%):</td>
                        <td class="totals-value">{{ $currency ?? 'USD' }} {{ number_format($igv, 2) }}</td>
                    </tr>
                @endif
                <tr class="totals-row total-final">
                    <td class="totals-label">TOTAL A PAGAR:</td>
                    <td class="totals-value">{{ $currency ?? 'USD' }} {{ number_format($total ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-content">
                <p><strong>Representación Impresa de la Factura Electrónica</strong></p>
                <p>Autorizado mediante Resolución de Intendencia SUNAT</p>
                @if (isset($verification_url) && $verification_url)
                    <p>Consulta y verifica este documento en:</p>
                    <p class="verification-link">{{ $verification_url }}</p>
                @endif
                <p style="margin-top: 10px; font-style: italic; color: #94a3b8;">
                    Generado automáticamente por Angelito Systems SaaS Platform
                </p>
            </div>
        </div>
    </div>
</body>

</html>
