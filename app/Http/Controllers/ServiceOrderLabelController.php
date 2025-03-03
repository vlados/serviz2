<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ServiceOrderLabelController extends Controller
{
    /**
     * Generate and display a printable label for a service order
     *
     * @param ServiceOrder $serviceOrder
     * @return \Illuminate\Contracts\View\View
     */
    public function printLabel(ServiceOrder $serviceOrder)
    {
        return view('service-orders.print-label', [
            'serviceOrder' => $serviceOrder,
        ]);
    }
    
    /**
     * Generate and display multiple printable labels for service orders
     *
     * @param string $batchId
     * @return \Illuminate\Contracts\View\View
     */
    public function printBulkLabels($batchId)
    {
        // Get the stored record IDs from the session
        $orderIds = Session::get('print_batch_' . $batchId, []);
        
        // Fetch the orders
        $serviceOrders = ServiceOrder::whereIn('id', $orderIds)->get();
        
        // Return the view
        return view('service-orders.print-bulk-labels', [
            'serviceOrders' => $serviceOrders,
        ]);
    }
}