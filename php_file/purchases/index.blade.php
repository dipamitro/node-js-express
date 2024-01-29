<x-app-layout>
    <x-slot name="page_title">{{ $page_title ?? 'Purchases |' }}</x-slot>

    <x-slot name="style">
        <link href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}"> {{ config('app.name', 'Laravel') }} </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('admin-panel/dashboard') }}"> Dashboard </a></li>
                            <li class="breadcrumb-item active"> Purchases </li>
                        </ol>
                    </div>

                    <h4 class="page-title"> Purchase List </h4>
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
                            <div class="col-sm-5">
                                <a href="{{ url('admin-panel/dashboard/purchases/create') }}" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle me-2"></i>  Add Purchase </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="basic-datatable" class="table table-centered table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th> SL </th>
                                        <th> Date </th>
                                        <th> Reference NO </th>
                                        <th> Supplier </th>
                                        <th> Total </th>
                                        <th> Paid Amount </th>
                                        <th> Payment Status </th>
                                        <th> Status </th>
                                        <th style="width: 75px;"> Action </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($purchases as $key => $purchase)
                                        <tr>
                                            <td> {{ ++$key }} </td>
                                            
                                            <td> {{ $purchase->date ?? "" }} </td>

                                            <td> {{ $purchase->reference_no ?? "" }} </td>

                                            <td> {{ $purchase->supplier->full_name ?? "" }} </td>

                                            <td> {{ $purchase->grand_total ?? "" }} </td>

                                            <td> {{ $purchase->paid_amount ?? "" }} </td>

                                            <td>
                                                @if ($purchase->payment_status == "paid")
                                                    <span class="badge badge-success-lighten"> Paid </span>
                                                @elseif ($purchase->payment_status == "partial")
                                                    <span class="badge badge-warning-lighten"> Partial </span>
                                                @elseif ($purchase->payment_status == "unpaid")
                                                    <span class="badge badge-danger-lighten"> Unpaid </span>
                                                @endif
                                            </td>

                                            <td>
                                                @if ($purchase->status == "received")
                                                    <span class="badge badge-success-lighten"> Received </span>
                                                @elseif ($purchase->status == "ordered")
                                                    <span class="badge badge-warning-lighten"> Ordered </span>
                                                @elseif ($purchase->status == "pending")
                                                    <span class="badge badge-danger-lighten"> Pending </span>
                                                @endif
                                            </td>

                                            <td>
                                                <form action="{{ url('admin-panel/dashboard/purchases', $purchase->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    
                                                    <a href="{{ url('admin-panel/dashboard/purchases', $purchase->id) }}" class="action-icon"> <i class="mdi mdi-eye"></i></a>
                                                    <a href="{{ url('admin-panel/dashboard/purchases/'. $purchase->id . '/edit') }}" class="action-icon"> <i class="mdi mdi-square-edit-outline"></i></a>

                                                    <input name="_method" type="hidden" value="DELETE">

                                                    <button type="submit" class="btn action-icon show_confirm" data-toggle="tooltip" title='Delete'><i class="mdi mdi-delete"></i></button>

                                                    @if ($purchase->grand_total - $purchase->paid_amount != 0)
                                                        <button type="button" data-id="{{ $purchase->id }}" class="dropdown-item purchase_payment_create" data-bs-toggle="modal" data-bs-target="#bs-example-modal-lg"><i class="uil-money-withdraw me-1"></i>Make Payment</button>
                                                    @endif
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Payment</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('admin-panel/dashboard/purchase-payment-create') }}" method="POST">
                        @csrf

                        <div class="row g-2">
                            <div class="mb-3 col-md-4">
                                <label for="purchase_id">Purchase ID</label>
                                <input type="number" name="purchase_id" class="form-control" id="purchase_id" readonly>
                            </div>

                            <div class="mb-3 col-md-4">
                                <label for="grand_total">Grand Total</label>
                                <input type="text" name="grand_total" class="form-control" id="grand_total" readonly>
                            </div>

                            <div class="mb-3 col-md-4">
                                <label for="paying_amount">Paying Amount / Due</label>
                                <input type="number" name="paying_amount" class="form-control" id="paying_amount" placeholder="0.00" readonly>
                            </div>
                        </div>
                        
                        <div class="row g-2">
                            <div class="mb-1 col-md-6">
                                <label for="payment_choice">Payment Choice</label>
                                <select id="payment_choice" name="payment_choice" class="form-select" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="given_amount">Given Amount</label>
                                <input type="number" name="given_amount" class="form-control given_amount" min="1" id="given_amount" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- script -->
    <x-slot name="script">
        <script src="{{ asset('assets/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/demo.datatable-init.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#DataTable').DataTable();

                $('#notification_alert').delay(3000).fadeOut('slow');

                $('.show_confirm').click(function(event) {
                    var form =  $(this).closest("form");
                    var name = $(this).data("name");

                    event.preventDefault();

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to delete this item ?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    })
                    .then((willDelete) => {
                        if (willDelete.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                $('body').on('click', '.purchase_payment_create', function () {
                    var purchase_id = $(this).data('id');

                    axios.get('/api/fetch-previous-purchase-payment-information', {
                        params: {
                            purchase_id: purchase_id
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(function (response) {
                        var previous_purchase = response.data;

                        console.log(previous_purchase);

                        document.getElementById("purchase_id").value = previous_purchase.id ? previous_purchase.id : '';
                        document.getElementById("grand_total").value = previous_purchase.grand_total ? previous_purchase.grand_total : '';
                        document.getElementById("paying_amount").value = previous_purchase.grand_total - previous_purchase.paid_amount;
                        // document.getElementById("given_amount").max = previous_purchase.grand_total - previous_purchase.paid_amount;
                    })
                    .catch(function (error) {
                        console.log(error);
                    })
                });
            });
        </script>
    </x-slot>
</x-app-layout>
