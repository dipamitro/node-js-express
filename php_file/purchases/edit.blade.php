<x-app-layout>
    <x-slot name="page_title">{{ $page_title ?? 'Purchase Edit |' }}</x-slot>

    <x-slot name="style">
        <link href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}"> {{ config('app.name', 'Laravel') }} </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('admin-panel/dashboard') }}"> Dashboard </a></li>
                            <li class="breadcrumb-item"><a href="{{ url('admin-panel/dashboard/purchases') }}"> Purchases </a></li>
                            <li class="breadcrumb-item active"> Edit </li>
                        </ol>
                    </div>

                    <h4 class="page-title"> Purchase Edit </h4>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input. <br><br>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="text-start mt-3">
                                <form action="{{ url('admin-panel/dashboard/purchases', $purchase->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
            
                                    @include('admin_panel.purchases.form')
                                    
                                    <div class="float-end">
                                        <a href="{{ url('admin-panel/dashboard/purchases') }}" class="btn btn-primary button-last"> Go Back </a>
                                        <button type="submit" class="btn btn-success button-last"> Save </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $is_able_payment_status = ($purchase->paid_amount > 0) ? true : false;
        @endphp
    </div>

    <!-- script -->
    <x-slot name="script">
        <script src="{{ asset('assets/vendor/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/demo.timepicker.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

        <script type="text/javascript">
            calculateTaxTotal();
            calculateProductGrandTotal();

            document.getElementById('search_product').addEventListener('keyup', function () {
                var search_product = this.value;

                if(search_product.length > 2) {
                    document.getElementById('product_list').innerHTML = '';

                    axios.get("{{ url('api/fetch-products-by-code-name') }}", {
                        params: {
                            search_product: search_product,
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(function (response) {
                        response.data.products.forEach(function (value) {
                            var option = `<button type="button" class="dropdown-item notify-item" onclick="set_product_in_order_table(${value.id})">
                                            <span>${value.name}</span>
                                        </button>`;
                                        
                            document.getElementById('product_list').insertAdjacentHTML('beforeend', option);
                        });
                    })
                    .catch(function (error) {
                        console.error('Error fetching products:', error);
                    });
                }
                else {
                    document.getElementById('product_list').innerHTML = '';
                }
            });

            function set_product_in_order_table(product_id) {
                document.getElementById('search_product').innerHTML = '';
                document.getElementById('product_list').innerHTML = '';

                axios.get("{{ url('api/fetch-single-product-by-id') }}", {
                    params: {
                        product_id: product_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(function (response) {
                    var product = response.data.product;

                    var lastSerialNumber = document.querySelectorAll('#order_table tbody tr').length + 1;

                    var newRow = document.createElement('tr');

                    newRow.innerHTML = `
                        <td>${lastSerialNumber}</td>
                        <td>${product.name} <input type="text" name="products[${lastSerialNumber}][id]" value="${product.id}" hidden></td>
                        <td>${product.purchase_price}</td>
                        <td><input type="number" min="0" name="products[${lastSerialNumber}][quantity]" value="1" id="product${lastSerialNumber}_quantity" onchange="calculatePurchasePrice(${lastSerialNumber}, ${product.purchase_price})"></td>
                        <td id="product${lastSerialNumber}_discount">${product.discount || 0.00}</td>
                        <td class="total_tax_price" id="product${lastSerialNumber}_tax">${product.tax || 0.00}</td>
                        <td class="total_purchase_price" id="product${lastSerialNumber}_total_purchase_price">${product.purchase_price}</td>
                        <td><button class="btn action-icon" onclick="removeRow(this)"><i class="mdi mdi-delete"></i></button></td>
                    `;

                    document.querySelector('#order_table tbody').appendChild(newRow);

                    calculateTaxTotal();
                    calculateProductGrandTotal();
                })
                .catch(function (error) {
                    console.error('Error fetching products:', error);
                });
            }

            function removeRow(button) {
                var row = button.closest('tr');
                row.remove();

                calculateProductGrandTotal();
                calculateTaxTotal();

                updateSerialNumbers();
            }

            function calculateTaxTotal() {
                var totalTaxPriceCells = document.querySelectorAll('.total_tax_price');
                var taxTotal = 0;

                totalTaxPriceCells.forEach(function (cell) {
                    taxTotal += parseFloat(cell.textContent);
                });

                document.getElementById('total_tax').textContent = taxTotal.toFixed(2);
                document.getElementById('product_tax').textContent = taxTotal.toFixed(2);

                calculateGrandTotal();
            }

            function updateSerialNumbers() {
                var rows = document.querySelectorAll('#order_table tbody tr');
                rows.forEach(function (row, index) {
                    row.querySelector('td:first-child').textContent = index + 1;
                });
            }

            function calculatePurchasePrice(product_id, purchase_price) {
                var quantity = parseInt(document.getElementById(`product${product_id}_quantity`).value, 10);
                var totalPurchasePrice = quantity * purchase_price;

                document.getElementById(`product${product_id}_total_purchase_price`).textContent = totalPurchasePrice.toFixed(2);

                calculateProductGrandTotal();
            }

            function calculateProductGrandTotal() {
                var totalPurchasePriceCells = document.querySelectorAll('.total_purchase_price');
                var grandTotal = 0;

                totalPurchasePriceCells.forEach(function (cell) {
                    grandTotal += parseFloat(cell.textContent);
                });

                document.getElementById('total_amount').textContent = grandTotal.toFixed(2);
                document.getElementById('total_product_cost').textContent = grandTotal.toFixed(2);

                calculateGrandTotal();
            }

            function updateTotalOrderTax() {
                var TaxValue = parseFloat(document.getElementById('tax_net').value) || 0;

                var totalTaxElement = document.getElementById('order_tax');

                totalTaxElement.textContent = TaxValue.toFixed(2);

                calculateGrandTotal();
            }

            function updateTotalDiscount() {
                var discountValue = parseFloat(document.getElementById('discount').value) || 0;

                var totalDiscountElement = document.getElementById('total_discount');

                totalDiscountElement.textContent = discountValue.toFixed(2);

                calculateGrandTotal();
            }

            function updateTotalShippingCost() {
                var shipping_value = parseFloat(document.getElementById('shipping').value) || 0;

                var shipping_cost = document.getElementById('shipping_cost');

                shipping_cost.textContent = shipping_value.toFixed(2);

                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                const orderTax = parseFloat(document.getElementById('order_tax').textContent);
                const productTax = parseFloat(document.getElementById('product_tax').textContent); 
                const totalDiscount = parseFloat(document.getElementById('total_discount').textContent);
                const shippingCost = parseFloat(document.getElementById('shipping_cost').textContent);
                const totalProductCost = parseFloat(document.getElementById('total_product_cost').textContent);
                
                const grandTotal = orderTax + productTax + totalProductCost + shippingCost - totalDiscount;
                
                document.getElementById('grand_total').textContent = grandTotal.toFixed(2);
                document.getElementById('payable_amount').value = grandTotal.toFixed(2);
            }

            $(document).ready(function () {
                $("#payment_section").show();

                $("#payment_status").on("change", function () {
                    var selectedStatus = $(this).val();
                    
                    if (selectedStatus === "partial" || selectedStatus === "paid") {
                        $("#payment_section").show(300);
                    }
                    else {
                        $("#payment_section").hide(300);
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
