<div class="row g-2">
    <div class="mb-2 col-md-4">
        <label> Date <span class="text-danger">*</span></label>
        <input type="text" id="basic-datepicker" name="date" value="{{ old('date', $purchase->date ?? "") }}" class="form-control" placeholder="" required>
    </div>

    <div class="mb-2 col-md-4">
        <label for="supplier_id"> Supplier <span class="text-danger">*</span></label>
        <select class="form-select select2" id="supplier_id" name="supplier_id" required data-toggle="select2" required>
            <option value="" selected disabled> Choose Supplier </option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ (old('supplier_id') ?? ($purchase->supplier_id ?? "")) == $supplier->id ? "selected" : "" }}>
                    {{ $supplier->full_name ?? "" }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-2 col-md-4">
        <label for="warehouse_id"> Warehouse <span class="text-danger">*</span></label>
        <select class="form-select select2" id="warehouse_id" name="warehouse_id" required data-toggle="select2" required>
            <option value="" selected disabled> Choose Warehouse </option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" {{ (old('warehouse_id') ?? ($purchase->warehouse_id ?? "")) == $warehouse->id ? "selected" : "" }}>
                    {{ $warehouse->name ?? "" }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-2">
    <div class="mb-4 col-md-12">
        <label for="search_product"> Choose Product <span class="text-danger">Minimum 3 characters for searching products</span></label>
        <div class="input-group">
            <input type="search" class="form-control dropdown-toggle" placeholder="Scan/Search by Code Or Name..." id="search_product">
        </div>

        <div class="dropdown-menu-animated dropdown-lg" id="product_list" style="background:#acacac; color:#000000;"></div>
    </div>
</div>

<div class="row g-2">
    <div class="mb-2 col-md-12">
        <div class="table-responsive">
            <table id="order_table" class="table table-centered table-striped dt-responsive nowrap w-100">
                <thead style="background:#D1D5DB; color:#000;">
                    <tr>
                        <th> SL </th>
                        <th> Product </th>
                        <th> Net Unit Price </th>
                        <th> Qty </th>
                        <th> Discount </th>
                        <th> Tax </th>
                        <th> Subtotal </th>
                        <th style="width: 75px;"> Action </th>
                    </tr>
                </thead>
                <tbody>
                    @if (Request::segment(4) == "create")
                        {{-- <td colspan="9">No data Available</td> --}}
                    @else
                        <input type="number" name="old_warehouse_id" value="{{ $purchase->warehouse_id }}" hidden>
                        @foreach ($purchase->purchase_details as $key => $purchase_details)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $purchase_details->product->name ?? "" }} <input type="text" name="products[{{ $key }}][id]" value="{{ $purchase_details->product_id }}" hidden></td>
                                <td>{{ $purchase_details->product->purchase_price ?? "0.00" }}</td>
                                <td><input type="number" min="0" name="products[{{ $key }}][quantity]" value="{{ $purchase_details->quantity ?? "0" }}" id="product{{ $key }}_quantity" onchange="calculatePurchasePrice({{ $key }}, {{ $purchase_details->product->purchase_price ?? '0.00' }})"></td>
                                <td id="product{{ $key }}_discount">{{ $purchase_details->product->discount ?? "0.00" }}</td>
                                <td class="total_tax_price" id="product{{ $key }}_tax">{{ $purchase_details->product->tax ?? "0.00" }}</td>
                                <td class="total_purchase_price" id="product{{ $key }}_total_purchase_price">{{ $purchase_details->total_amount ?? "0.00" }}</td>
                                <td><button class="btn action-icon" onclick="removeRow(this)"><i class="mdi mdi-delete"></i></button></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot class="tfoot active">
                    <th colspan="2"> Total </th>
                    <th id="total_qty"> 0 </th>
                    <th></th>
                    <th>Total Discount <span id="total_product_discount">0.00</span></th>
                    <th>Total Tax <span id="total_tax">0.00</span></th>
                    <th>Total Amount <span id="total_amount">0.00</span></th>
                    <th></th>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="row g-2 mt-3 d-flex justify-content-end ">
    <div class="mb-2 col-md-4">
        <table class="table table-striped table-sm">
            <tbody>
                <tr>
                    <td class="bold">Order Tax</td>
                    <td>
                        <span class="final_cost" id="order_tax">0.00</span> + (<span class="final_cost" id="product_tax">0.00</span>)
                    </td>
                </tr>
                <tr>
                    <td class="bold">Discount</td>
                    <td> <span class="final_cost" id="total_discount">0.00</span></td>
                </tr>
                <tr>
                    <td class="bold">Shipping</td>
                    <td> <span class="final_cost" id="shipping_cost">0.00</span></td>
                </tr>
                <tr>
                    <td class="bold">Total Product Cost</td>
                    <td> <span class="final_cost" id="total_product_cost">0.00</span></td>
                </tr>
                <tr>
                    <td><span class="font-weight-bold">Grand Total</span></td>
                    <td><span class="font-weight-bold" id="grand_total">0.00</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-2">
    <div class="mb-2 col-md-4">
        <label for="tax_net"> Order Tax </label>
        <div class="input-group">
            <input type="number" min="0" step="0.01" value="{{ old('tax_net', $purchase->tax_net ?? "0.00") }}" name="tax_net" id="tax_net" class="form-control" aria-label="" onchange="updateTotalOrderTax()">
            <span class="input-group-text">৳</span>
          </div>
    </div>

    <div class="mb-2 col-md-4">
        <label for="discount"> Discount </label>
        <div class="input-group">
            <input type="number" min="0" step="0.01" value="{{ old('discount', $purchase->discount ?? "0.00") }}" name="discount" id="discount" class="form-control" aria-label="" onchange="updateTotalDiscount()">
            <span class="input-group-text">৳</span>
        </div>
    </div>

    <div class="mb-2 col-md-4">
        <label for="shipping"> Shipping </label>
        <div class="input-group">
            <input type="number" min="0" step="0.01" value="{{ old('shipping', $purchase->shipping ?? "0.00") }}" name="shipping" id="shipping" class="form-control" aria-label="" onchange="updateTotalShippingCost()">
            <span class="input-group-text">৳</span>
        </div>
    </div>
</div>

<div class="row g-2">
    <div class="mb-2 col-md-4">
        <label for="input_status"> Status <span class="text-danger">*</span></label>
        <select class="form-select" id="input_status" name="input_status" required>
            <option value="" selected disabled> Choose Status </option>
            <option value="received" {{ (old('input_status') ?? ($purchase->status ?? "")) == "received" ? 'selected' : "" }}> Received </option>
            <option value="pending" {{ (old('input_status') ?? ($purchase->status ?? "")) == "pending" ? 'selected' : "" }}> Pending </option>
            <option value="ordered" {{ (old('input_status') ?? ($purchase->status ?? "")) == "ordered" ? 'selected' : "" }}> Ordered </option>
        </select>
    </div>

    <div class="mb-2 col-md-4">
        <label for="payment_status"> Payment Status <span class="text-danger">*</span></label>
        <select class="form-select" id="payment_status" name="payment_status" required>
            <option value="" selected disabled> Choose Status </option>
            <option value="paid" {{ (old('payment_status') ?? ($purchase->payment_status ?? "")) == "paid" ? 'selected' : "" }}> Paid </option>
            <option value="partial" {{ (old('payment_status') ?? ($purchase->payment_status ?? "")) == "partial" ? 'selected' : "" }}> Partial </option>
            <option value="unpaid" {{ (old('payment_status') ?? ($purchase->payment_status ?? "")) == "unpaid" ? 'selected' : "" }}> Unpaid </option>
        </select>
    </div>
</div>

<div class="row g-2" id="payment_section">
    <div class="mb-2 col-md-4">
        <label for="payable_amount"> Payable Amount <span class="text-danger">*</span></label>
        <input type="number" min="0" step="0.01" class="form-control" id="payable_amount" name="payable_amount" value="{{ old('payable_amount', (($purchase->grand_total ?? 0.00) - ($purchase->paid_amount ?? 0.00)) ?? "0.00") }}" placeholder="">
    </div>

    <div class="mb-2 col-md-4">
        <label for="paid_amount"> Paid Amount <span class="text-danger">*</span></label>
        <input type="number" min="0" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', $purchase->paid_amount ?? "0.00") }}" placeholder="">
    </div>
</div>

<div class="row g-2">
    <div class="mb-2 col-md-12">
        <label for="notes"> Note </label>
            <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="A few words......">{{ $purchase->notes ?? "" }}</textarea>
        </div>
    </div>
</div>
