<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\PurchaseDetails;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $purchases = Purchase::query()
            ->latest()
            ->get();

        return view('admin_panel.purchases.index', compact('purchases'));
    }

    private function data(Purchase $purchase) {
        $suppliers = Supplier::query()
            ->orderBy('full_name', "asc")
            ->get();

        $warehouses = Warehouse::query()
            ->orderBy('name', "asc")
            ->get();

        $customers = Customer::query()
            ->orderBy('full_name', "asc")
            ->get();

        return [
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'customers' => $customers
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        return view('admin_panel.purchases.create', $this->data(new Purchase()) + [
            'last_paid_amount' => "0.00"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $request->validate([
            'date' => ['required', 'date'],
            'warehouse_id' => ['required', 'numeric'],
            'supplier_id' => ['required', 'numeric'],
            'tax_net' => ['nullable', 'numeric', 'max:255'],
            'discount' => ['nullable', 'numeric', 'max:255'],
            'shipping' => ['nullable', 'numeric', 'max:255'],
            'paid_amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:255'],
            'products' => ['required', 'array'],
            'input_status' => ['required', 'string', 'max:255']
        ]);

        $purchase = Purchase::create([
            'user_id' => Auth::user()->id,
            'reference_no' => $this->get_reference_no(),
            'date' => $request->date,
            'warehouse_id' => $request->warehouse_id,
            'supplier_id' => $request->supplier_id,
            'tax_rate' => $request->tax_rate,
            'tax_net' => $request->tax_net,
            'discount' => $request->discount,
            'shipping' => $request->shipping,
            'grand_total' => "0.00",
            'paid_amount' => $request->paid_amount,
            'payment_status' => "unpaid",
            'status' => $request->input_status,
            'notes' => $request->notes
        ]);

        if ($request->products) {
            $purchase_details = Array();
            $total_tax_net = 0;
            $grand_total = ($request->tax_net + $request->shipping + $total_tax_net) - ($request->discount ?? 0.00);
            
            foreach($request->products as $key => $selected_product) {
                $product = Product::find($selected_product['id']);
                $unit = Unit::find($product->purchase_unit_id);

                $data['purchase_id'] = $purchase->id;
                $data['product_id'] = $product->id;
                $data['product_variant_id'] = NULL;
                $data['purchase_price'] = $product->purchase_price ?? "0.00";
                $data['discount'] = NULL;
                $data['tax_method'] = $product->tax_method ?? NULL;
                $data['tax_net'] = $product->tax ?? NULL;
                $data['quantity'] = $selected_product['quantity'];
                $data['total_amount'] = $data['quantity'] * $data['purchase_price'];
                $data['imei_number'] = $selected_product['imei_number'] ?? NULL;

                $purchase_details[] = $data;

                $total_tax_net += $data['tax_net'] ?? 0.00;

                $grand_total += $data['total_amount'];

                if ($request->input_status == "received") {
                    $product_warehouse = ProductWarehouse::query()
                        ->where('warehouse_id', $purchase->warehouse_id)
                        ->where('product_id', $data['product_id'])
                        ->first();
    
                    if ($unit && $product_warehouse) {
                        if ($unit->operator == '/') {
                            $product_quantity = $product_warehouse->quantity + $selected_product['quantity'] / $unit->operation_value;
                        }
                        else {
                            $product_quantity = $product_warehouse->quantity + $selected_product['quantity'] * $unit->operation_value;
                        }
                        
                        $product_warehouse->update([
                            'quantity' => $product_quantity ?? 0
                        ]);
                    }
                }
            }

            PurchaseDetails::insert($purchase_details);

            if ($request->paid_amount && $request->paid_amount <= 0) {
                $payment_status = "unpaid";
            }
            elseif($grand_total > $request->paid_amount) {
                $payment_status = "partial";
            }
            elseif($grand_total <= $request->paid_amount) {
                $payment_status = "paid";
            }

            $purchase->update([
                'grand_total' => $grand_total,
                'payment_status' => $payment_status
            ]);

            if ($request->paid_amount > 0) {
                PurchasePayment::create([
                    'reference_no' => $this->get_rurchase_payment_reference_no(),
                    'user_id' => Auth::user()->id,
                    'purchase_id' => $purchase->id,
                    'date' => date("Y-m-d"),
                    'amount' => $request->paid_amount,
                    'change' => "0.00",
                    'reglement' => "Cash"
                ]);
            }
        }

        return redirect()->to('admin-panel/dashboard/purchases')
            ->with('success', 'Created Successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase) {
        return view('admin_panel.purchases.show', $this->data($purchase));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase) {
        $last_paid_amount = PurchasePayment::query()
            ->where('purchase_id', $purchase->id)
            ->latest()
            ->first() ?? [];
            
        return view('admin_panel.purchases.edit', $this->data($purchase) + [
            'last_paid_amount' => $last_paid_amount->amount ?? "0.00"
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase) {
        $request->validate([
            'date' => ['required', 'date'],
            'warehouse_id' => ['required', 'numeric'],
            'supplier_id' => ['required', 'numeric'],
            'tax_net' => ['nullable', 'numeric', 'max:255'],
            'discount' => ['nullable', 'numeric', 'max:255'],
            'shipping' => ['nullable', 'numeric', 'max:255'],
            'paid_amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:255'],
            'products' => ['required', 'array'],
            'input_status' => ['required', 'string', 'max:255']
        ]);

        $purchase->update([
            'user_id' => Auth::user()->id,
            'date' => $request->date,
            'warehouse_id' => $request->warehouse_id,
            'supplier_id' => $request->supplier_id,
            'tax_rate' => $request->tax_rate,
            'tax_net' => $request->tax_net,
            'discount' => $request->discount,
            'shipping' => $request->shipping,
            'grand_total' => "0.00",
            'paid_amount' => $request->paid_amount,
            'payment_status' => "unpaid",
            'status' => $request->input_status,
            'notes' => $request->notes
        ]);

        if ($request->products) {
            $old_purchase_details = $purchase->purchase_details;
            
            foreach($old_purchase_details as $opd) {
                $old_product_warehouse = ProductWarehouse::query()
                    ->where('warehouse_id', $request->old_warehouse_id)
                    ->where('product_id', $opd->product_id)
                    ->first();

                $old_product_warehouse->update([
                    'quantity' => ($old_product_warehouse->quantity ?? 0) - ($opd->quantity ?? 0)
                ]);
            }

            DB::table('purchase_details')->where('purchase_id', $purchase->id)->delete();

            $purchase_details = Array();
            $total_tax_net = 0;
            $grand_total = ($request->tax_net + $request->shipping + $total_tax_net) - ($request->discount ?? 0.00);
            
            foreach($request->products as $key => $selected_product) {
                $product = Product::find($selected_product['id']);
                $unit = Unit::find($product->purchase_unit_id);

                $data['purchase_id'] = $purchase->id;
                $data['product_id'] = $product->id;
                $data['product_variant_id'] = NULL;
                $data['purchase_price'] = $product->purchase_price ?? "0.00";
                $data['discount'] = NULL;
                $data['tax_method'] = $product->tax_method ?? NULL;
                $data['tax_net'] = $product->tax ?? NULL;
                $data['quantity'] = $selected_product['quantity'];
                $data['total_amount'] = $data['quantity'] * $data['purchase_price'];
                $data['imei_number'] = $selected_product['imei_number'] ?? NULL;

                $purchase_details[] = $data;

                $total_tax_net += $data['tax_net'] ?? 0.00;

                $grand_total += $data['total_amount'];

                if ($request->input_status == "received") {
                    $product_warehouse = ProductWarehouse::query()
                        ->where('warehouse_id', $purchase->warehouse_id)
                        ->where('product_id', $data['product_id'])
                        ->first();
    
                    if ($unit && $product_warehouse) {
                        if ($unit->operator == '/') {
                            $product_quantity = $product_warehouse->quantity + $selected_product['quantity'] / $unit->operation_value;
                        }
                        else {
                            $product_quantity = $product_warehouse->quantity + $selected_product['quantity'] * $unit->operation_value;
                        }
                        
                        $product_warehouse->update([
                            'quantity' => $product_quantity ?? 0
                        ]);
                    }
                }
            }

            PurchaseDetails::insert($purchase_details);

            if ($request->paid_amount && $request->paid_amount <= 0) {
                $payment_status = "unpaid";
            }
            elseif($grand_total > $request->paid_amount) {
                $payment_status = "partial";
            }
            elseif($grand_total <= $request->paid_amount) {
                $payment_status = "paid";
            }

            $purchase->update([
                'grand_total' => $grand_total,
                'payment_status' => $payment_status
            ]);

            if ($request->paid_amount > 0) {
                PurchasePayment::create([
                    'reference_no' => $this->get_rurchase_payment_reference_no(),
                    'user_id' => Auth::user()->id,
                    'purchase_id' => $purchase->id,
                    'date' => date("Y-m-d"),
                    'amount' => $request->paid_amount,
                    'change' => "0.00",
                    'reglement' => "Cash"
                ]);
            }
        }

        return redirect()->to('admin-panel/dashboard/purchases')
            ->with('success', 'Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase) {
        $old_purchase_details = $purchase->purchase_details;
            
        foreach($old_purchase_details as $opd) {
            $old_product_warehouse = ProductWarehouse::query()
                ->where('warehouse_id', $purchase->warehouse_id)
                ->where('product_id', $opd->product_id)
                ->first();

            $old_product_warehouse->update([
                'quantity' => ($old_product_warehouse->quantity ?? 0) - ($opd->quantity ?? 0)
            ]);
        }

        PurchaseDetails::where('purchase_id', $purchase->id)->delete();

        $purchase->delete();

        return redirect()->to('admin-panel/dashboard/purchases')
            ->with('success', 'Delete Successfully.');
    }

    public function get_reference_no() {
        $last = DB::table('purchases')->latest('id')->first();

        if ($last) {
            $item = $last->reference_no;
            $new_item = explode("_", $item);
            $in_item = $new_item[1] + 1;
            $code = $new_item[0] . '_' . $in_item;
        }
        else {
            $code = 'PRS_10001';
        }

        return $code;
    }

    public function fetch_previous_purchase_payment_information(Request $request) {
        return Purchase::where('id', $request->purchase_id)->first();
    }

    public function purchase_payment_create(Request $request) {
        $purchase = Purchase::findOrFail($request->purchase_id);

        if ($purchase) {
            $grand_total = $purchase->grand_total ?? 0;
            $old_paid_amount = $purchase->paid_amount ?? 0.00;

            $new_paid_amount = $old_paid_amount + $request->given_amount;

            if ($new_paid_amount < 0) {
                $payment_status = "unpaid";
            }
            elseif($new_paid_amount >= $grand_total) {
                $payment_status = "paid";
            }
            elseif($new_paid_amount > 0) {
                $payment_status = "partial";
            }
            
            $purchase->update([
                'paid_amount' => $new_paid_amount,
                'payment_status' => $payment_status
            ]);

            PurchasePayment::create([
                'reference_no' => $this->get_rurchase_payment_reference_no(),
                'user_id' => Auth::user()->id,
                'purchase_id' => $purchase->id,
                'date' => date("Y-m-d"),
                'amount' => $request->given_amount,
                'change' => "0.00",
                'reglement' => $request->payment_choice
            ]);
        }
        else {
            return abort(4040);
        }

        return redirect()->to('admin-panel/dashboard/purchases')
            ->with('success', 'Payment Created Successfully.');
    }

    public function get_rurchase_payment_reference_no() {
        $last = DB::table('purchase_payments')->latest('id')->first();

        if ($last) {
            $item = $last->reference_no;
            $new_item = explode("_", $item);
            $in_item = $new_item[1] + 1;
            $code = $new_item[0] . '_' . $in_item;
        }
        else {
            $code = 'PAY_10001';
        }

        return $code;
    }
}
