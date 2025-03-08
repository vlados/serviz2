<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Етикет за поръчка № {{ $serviceOrder->order_number }}</title>
    <style>
        @page {
            size: 62mm 100mm; /* Brother QL-820NWB with 62mm x 100mm label size */
            margin: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: white;
        }
        
        .label-container {
            width: 62mm;
            min-height: 100mm;
            padding: 2mm;
            box-sizing: border-box;
        }
        
        .company-info {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        
        .company-name {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .company-details {
            font-size: 7pt;
        }
        
        .order-number {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 2mm 0;
            padding: 1mm;
            border: 1px solid #000;
            background-color: #f0f0f0;
        }
        
        .customer-info, .scooter-info, .service-info {
            margin-bottom: 2mm;
            font-size: 8pt;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 1mm;
            border-bottom: 1px solid #ccc;
            font-size: 9pt;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 0.5mm;
        }
        
        .info-label {
            font-weight: bold;
            width: 40%;
        }
        
        .info-value {
            width: 60%;
        }
        
        .barcode {
            text-align: center;
            margin: 2mm 0;
        }
        
        .barcode svg {
            max-width: 56mm;
            height: auto;
        }
        
        .qr-code {
            text-align: center;
            margin-top: 2mm;
        }
        
        .qr-code img {
            width: 20mm;
            height: 20mm;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            body {
                width: 62mm;
                margin: 0;
                padding: 0;
            }
            
            /* Brother printer specific settings */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        
        .print-button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            margin-top: 10px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="label-container">
        <div class="company-info">
            <div class="company-name">СЕРВИЗ ЗА ТРОТИНЕТКИ</div>
            <div class="company-details">
                гр. София, ул. "Примерна" 123<br>
                Тел: 0888 123 456
            </div>
        </div>
        
        <div class="order-number">
            Поръчка № {{ $serviceOrder->order_number }}
        </div>
        
        <div class="customer-info">
            <div class="section-title">Клиент</div>
            <div class="info-row">
                <div class="info-label">Име:</div>
                <div class="info-value">{{ $serviceOrder->customer->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Телефон:</div>
                <div class="info-value">{{ $serviceOrder->customer->phone }}</div>
            </div>
        </div>
        
        <div class="scooter-info">
            <div class="section-title">Тротинетка</div>
            <div class="info-row">
                <div class="info-label">Модел:</div>
                <div class="info-value">{{ $serviceOrder->scooter->model }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">S/N:</div>
                <div class="info-value">{{ $serviceOrder->scooter->serial_number }}</div>
            </div>
        </div>
        
        <div class="service-info">
            <div class="section-title">Информация за сервиза</div>
            <div class="info-row">
                <div class="info-label">Дата:</div>
                <div class="info-value">{{ $serviceOrder->received_at->format('d.m.Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Статус:</div>
                <div class="info-value">
                    @switch($serviceOrder->status)
                        @case('pending')
                            В очакване
                            @break
                        @case('in_progress')
                            В процес
                            @break
                        @case('completed')
                            Завършена
                            @break
                        @case('cancelled')
                            Отказана
                            @break
                        @default
                            {{ $serviceOrder->status }}
                    @endswitch
                </div>
            </div>
            @if($serviceOrder->technician)
            <div class="info-row">
                <div class="info-label">Техник:</div>
                <div class="info-value">{{ $serviceOrder->technician->name }}</div>
            </div>
            @endif
        </div>
        
        <div class="barcode">
            {!! DNS1D::getBarcodeHTML($serviceOrder->order_number, 'C128', 1.5, 25) !!}
            <div style="text-align: center; font-size: 9pt;">{{ $serviceOrder->order_number }}</div>
        </div>
        
        <div class="qr-code">
            {!! DNS2D::getBarcodeHTML(route('service-orders.print-label', $serviceOrder), 'QRCODE', 3, 3) !!}
        </div>
    </div>
    
    <button class="print-button" onclick="window.print()">Принтиране на етикета</button>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Wait a moment to ensure the page is fully rendered
            setTimeout(function() {
                // Configure print settings for Brother QL-820NWB
                const mediaSize = {
                    name: 'Custom',
                    width_microns: 62000,
                    height_microns: 100000,
                    custom_display_name: 'Brother 62mm x 100mm'
                };
                
                if (window.print) {
                    window.print();
                }
            }, 750);
        };
    </script>
</body>
</html>