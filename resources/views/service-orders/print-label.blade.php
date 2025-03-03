<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Етикет за поръчка № {{ $serviceOrder->order_number }}</title>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .label-container {
            width: 100mm;
            min-height: 150mm;
            border: 1px solid #000;
            padding: 5mm;
            box-sizing: border-box;
        }
        
        .company-info {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 5mm;
            margin-bottom: 5mm;
        }
        
        .company-name {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        .company-details {
            font-size: 8pt;
        }
        
        .order-number {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin: 5mm 0;
            padding: 2mm;
            border: 1px solid #000;
            background-color: #f0f0f0;
        }
        
        .customer-info, .scooter-info, .service-info {
            margin-bottom: 5mm;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 2mm;
            border-bottom: 1px solid #ccc;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 1mm;
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
            margin: 5mm 0;
        }
        
        .barcode svg {
            max-width: 80mm;
            height: auto;
        }
        
        .qr-code {
            text-align: center;
            margin-top: 5mm;
        }
        
        .qr-code img {
            width: 25mm;
            height: 25mm;
        }
        
        @media print {
            .print-button {
                display: none;
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
                Тел: 0888 123 456, Email: service@example.com
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
                <div class="info-label">Сериен номер:</div>
                <div class="info-value">{{ $serviceOrder->scooter->serial_number }}</div>
            </div>
        </div>
        
        <div class="service-info">
            <div class="section-title">Информация за сервиза</div>
            <div class="info-row">
                <div class="info-label">Дата на приемане:</div>
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
            {!! DNS1D::getBarcodeHTML($serviceOrder->order_number, 'C128', 2, 30) !!}
        </div>
        
        <!-- <div class="qr-code">
            {!! DNS2D::getBarcodeHTML(route('service-orders.print-label', $serviceOrder), 'QRCODE', 5, 5) !!}
        </div> -->
    </div>
    
    <button class="print-button" onclick="window.print()">Принтиране на етикета</button>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Wait a moment to ensure the page is fully rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>