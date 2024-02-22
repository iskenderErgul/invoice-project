<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function Laravel\Prompts\alert;

class InvoiceController extends Controller
{
    public function get_all_invoice(): JsonResponse
    {
        $invoices = Invoice::with('customer')->orderBy('id','DESC')->get();
        return response()->json([
            'invoices' => $invoices
        ],200);
    }
    public function search_invoice(Request $request): JsonResponse
    {
        $search = $request->get('s');
        if($search != null) {
            $invoices = Invoice::with('customer')
                ->where('id','LIKE',"%$search%")
                ->get();

            return response()->json([
                'invoices' => $invoices
            ],200);
        }else {
            return $this->get_all_invoice();
        }

    }

    public function create_invoice(Request $request): JsonResponse
    {

        $counter = Counter::where('key','invoice')->first();
        $random = Counter::where('key','invoice')->first();
        $invoice = Invoice::orderBy('id', 'DESC')->first();

        if($invoice) {
            $invoice= $invoice->id+1;
            $counters= $counter->value + $invoice;
        }else {
            $counters=$counter->value;
        }

        $formData = [
          'number' => $counter->prefix.$counters,
            'customer_id' => null,
            'customer' => null,
            'date' => date('Y-m-d'),
            'due_Date' => null,
            'reference' => null,
            'discount' => 0,
            'term_and_conditions' => 'Default Terms and Conditions',
            'items' => [
                [
                    'product_id' => null ,
                    'product ' => null,
                    'unit_price' => 0,
                    'quantity' => 1
                ]
            ]
        ];
        return response()->json($formData);

    }

    public function add_invoice(Request $request): void
    {


        $invoiceItem = $request->invoice_item;
        $invoicedata['sub_total'] = $request->sub_total;
        $invoicedata['total'] = $request->total;
        $invoicedata['customer_id'] = $request->customer_id;
        $invoicedata['number'] = $request->number;
        $invoicedata['date'] = $request->date;
        $invoicedata['due_date'] = $request->due_date;
        $invoicedata['discount'] = $request->discount;
        $invoicedata['reference'] = $request->reference;
        $invoicedata['terms_and_conditions'] = $request->terms_and_conditions;

        $invoice = Invoice::create($invoicedata);
        foreach (json_decode($invoiceItem) as $item){
            $itemdata ['product_id'] = $item->id;
            $itemdata ['invoice_id'] = $invoice->id;
            $itemdata ['quantity'] = $item->quantity;
            $itemdata ['unit_price'] = $item->unit_price;

            InvoiceItem::create($itemdata);

        }
    }

    public function show_invoice($id): JsonResponse
    {
        $invoice = Invoice::with('customer','invoice_items.product')->find($id);
        return response()->json([
            'invoice' => $invoice
        ],200);
    }

    public function edit_invoice($id): JsonResponse
    {
        $invoice = Invoice::with('customer','invoice_items.product')->find($id);
        return response()->json([
            'invoice' => $invoice
        ],200);
    }

    public function delete_invoice_items($id): void
    {
        $invoice_items = InvoiceItem::findOrFail($id);
        $invoice_items->delete();
    }

    public function update_invoice(Request $request, $id){
        $invoiceItems = json_decode($request->invoice_items, true);

        $invoicedata['sub_total'] = $request->sub_total;
        $invoicedata['total'] = $request->total;
        $invoicedata['customer_id'] = $request->customer_id;
        $invoicedata['number'] = $request->number;
        $invoicedata['date'] = $request->date;
        $invoicedata['due_date'] = $request->due_date;
        $invoicedata['discount'] = $request->discount;
        $invoicedata['reference'] = $request->reference;
        $invoicedata['terms_and_conditions'] = $request->terms_and_conditions;

        // Fatura güncelleme
        Invoice::where('id', $id)->update($invoicedata);

        // Fatura kalemleri güncelleme
        foreach ($invoiceItems as $item){
            $itemdata['product_id'] = $item['product_id'] ?? null;
            $itemdata['invoice_id'] = $id; // Güncellenen fatura ID'sini kullan
            $itemdata['quantity'] = $item['quantity'] ?? 0;
            $itemdata['unit_price'] = $item['unit_price'] ?? 0;

            // Fatura kalemi var mı kontrol et
            if (isset($item['id'])) {
                InvoiceItem::where('id', $item['id'])->update($itemdata);
            } else {
                // Yeni bir fatura kalemi oluştur
                InvoiceItem::create($itemdata);
            }
        }
    }

    public function delete_invoice($id) {
        $invoice = Invoice::findOrFail($id);
        $invoice->invoice_items()->delete();
         $invoice->delete();
    }


}
