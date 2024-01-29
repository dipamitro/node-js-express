<x-app-layout>
    <x-slot name="page_title">{{ $page_title ?? 'Purchase Show |' }}</x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}"> {{ config('app.name', 'Laravel') }} </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('admin-panel/dashboard') }}"> Dashboard </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('admin-panel/dashboard/purchases') }}"> Purchases </a></li>
                            <li class="breadcrumb-item active"> Show </li>
                        </ol>
                    </div>

                    <h4 class="page-title"> Purchase Show </h4>
                </div>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success" id="notification_alert">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-2 col-md-12">
                                        <a href="{{ url('admin-panel/dashboard/purchases/'. $purchase->id .'/edit') }}" class="btn btn-success btn-icon ripple btn-sm" title="Edit">
                                            <i class="i-Edit"></i>
                                            <span>Edit Purchase</span>
                                        </a>

                                        <button class="btn btn-info btn-icon ripple btn-sm">
                                            <i class="i-Envelope-2"></i> Email </button>
                                        <button class="btn btn-secondary btn-icon ripple btn-sm">
                                            <i class="i-Speach-Bubble"></i> SMS </button>
                                        <button class="btn btn-primary btn-icon ripple btn-sm">
                                            <i class="i-File-TXT"></i> PDF </button>
                                        <button class="btn btn-warning btn-icon ripple btn-sm">
                                            <i class="i-Billing"></i> Print </button>
                                        <button class="btn btn-danger btn-icon ripple btn-sm">
                                            <i class="i-Close-Window"></i> Delete </button>
                                    </div>
                                </div>

                                <div id="print_Invoice" class="invoice mt-2">
                                    <div class="invoice-print">
                                        <div class="row text-center">
                                            <h4 class="font-weight-bold">Purchase Detail : {{ $purchase->reference_no ?? "" }}</h4>
                                        </div>

                                        <hr>

                                        <div class="row mt-5">
                                            <div class="mb-4 col-sm-12 col-md-4 col-lg-4">
                                                <h5 class="font-weight-bold">Supplier Info</h5>
                                                <div> {{ $purchase->supplier->full_name ?? "" }} </div>
                                                <div> {{ $purchase->supplier->mobile_number ??"" }} </div>
                                                <div> {{ $purchase->supplier->email ?? "" }} </div>
                                                <div> {{ $purchase->supplier->address ?? "" }} </div>
                                            </div>

                                            <div class="mb-4 col-sm-12 col-md-4 col-lg-4">
                                                <h5 class="font-weight-bold">Company Info</h5>
                                                <div> {{ config('app.name', 'Laravel') }} </div>
                                                <div> {{ $purchase->user->name ?? "" }} </div>
                                                <div> {{ $purchase->user->mobile_number ?? "" }} </div>
                                                <div> {{ $purchase->user->email ?? "" }} </div>
                                                <div> {{ $purchase->user->address ?? "" }} </div>
                                            </div>

                                            <div class="mb-4 col-sm-12 col-md-4 col-lg-4">
                                                <h5 class="font-weight-bold">Purchase Info</h5>
                                                <div>Reference : {{ $purchase->reference_no ?? "" }}</div>
                                                <div> Status : 
                                                    @if ($purchase->status == "received")
                                                        <span class="badge badge-outline-success">Received</span>
                                                    @elseif ($purchase->status == "ordered")
                                                        <span class="badge badge-outline-warning">Ordered</span>
                                                    @elseif ($purchase->status == "pending")
                                                        <span class="badge badge-outline-danger">Pending</span>
                                                    @endif
                                                </div>

                                                <div>Warehouse : {{ $purchase->warehouse->name ?? "" }}</div>

                                                <div> Payment Status : 
                                                    @if ($purchase->payment_status == "paid")
                                                        <span class="badge badge-outline-success">Paid</span>
                                                    @elseif ($purchase->payment_status == "partial")
                                                        <span class="badge badge-outline-warning">Partial</span>
                                                    @elseif ($purchase->payment_status == "unpaid")
                                                        <span class="badge badge-outline-danger">Unpaid</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <h5 class="font-weight-bold">Order Summary</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-md">
                                                        <thead class="bg-gray-300">
                                                            <tr>
                                                                <th scope="col">Product</th>
                                                                <th scope="col">Unit Cost</th>
                                                                <th scope="col">Quantity</th>
                                                                <th scope="col">Discount</th>
                                                                <th scope="col">Tax</th>
                                                                <th scope="col">Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($purchase->purchase_details as $purchase_detail)
                                                                <tr>
                                                                    <td>
                                                                        <span>{{ $purchase_detail->product->code ?? "" }} ({{ $purchase_detail->product->name ?? "" }})</span>
                                                                        {{-- <p style="display: none;">IMEI/SN : </p> --}}
                                                                    </td>
                                                                    <td>{{ $purchase_detail->purchase_price ?? "" }}</td>
                                                                    <td>{{ $purchase_detail->quantity ?? "0" }}</td>
                                                                    <td>{{ $purchase_detail->discount ?? "0.00" }}</td>
                                                                    <td>{{ $purchase_detail->tax_net ?? "0.00" }}</td>
                                                                    <td>{{ $purchase_detail->total_amount ?? "0.00" }}</td>
                                                                </tr> 
                                                            @empty
                                                                <td colspan="7">No data Available</td>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="offset-md-9 col-md-3 mt-4">
                                                <table class="table table-striped table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td class="bold">Order Tax</td>
                                                            <td>
                                                                <span>{{ $purchase->tax_net ?? "0.00" }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bold">Discount</td>
                                                            <td>{{ $purchase->discount ?? "0.00" }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bold">Shipping</td>
                                                            <td>{{ $purchase->shipping ?? "0.00" }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span class="font-weight-bold">Grand Total</span>
                                                            </td>
                                                            <td>
                                                                <span class="font-weight-bold">{{ $purchase->grand_total ?? "0.00" }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span class="font-weight-bold">Paid</span>
                                                            </td>
                                                            <td>
                                                                <span class="font-weight-bold">{{ $purchase->paid_amount ?? "0.00" }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <span class="font-weight-bold">Due</span>
                                                            </td>
                                                            <td>
                                                                <span class="font-weight-bold">{{ (($purchase->grand_total ?? "0.00") - ($purchase->paid_amount ?? "0.00")) }}</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <hr>

                                        @if ($purchase->purchase_payments)
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <h3>Payment History</h3>

                                                    <table class="table table-hover table-centered mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>SL</th>
                                                                <th>Reference No</th>
                                                                <th>Date</th>
                                                                <th>Amount</th>
                                                                <th>Reglement</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($purchase->purchase_payments as $key => $purchase_payment)
                                                                <tr>
                                                                    <td>{{ ++$key }}</td>
                                                                    <td>{{ $purchase_payment->reference_no ?? "" }}</td>
                                                                    <td>{{ $purchase_payment->date ?? "" }}</td>
                                                                    <td>{{ $purchase_payment->amount ?? "" }}</td>
                                                                    <td>{{ $purchase_payment->reglement ?? "" }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
