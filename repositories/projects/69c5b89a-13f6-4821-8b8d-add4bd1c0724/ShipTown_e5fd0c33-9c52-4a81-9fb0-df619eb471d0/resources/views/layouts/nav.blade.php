@auth
    <nav class="navbar navbar-expand-md navbar-light mb-2 p-0 bg-primary" style="z-index: 1021">
        <div class="container text-white text-nowrap flex-nowrap">
            <div class="d-flex mb-0 navbar-dark w-100 text-nowrap flex-nowrap">
                <!-- Products -->
                <div class="dropdown">
                    <a id="products_link" class="dropdown-toggle btn btn-primary px-2" href="#" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ __('Products') }}
                    </a>

                    <div class="dropdown-menu dropdown-menu-left w-auto text-left bg-primary "
                        aria-labelledby="navbarDropdown">
                        <a class="dropdown-item text-white lightHover mt-1" id="inventory_link"
                            href="{{ route('products.inventory') }}">{{ __('Inventory') }}</a>
                        <a class="dropdown-item text-white lightHover mt-1" id="transfers_in_link"
                            href="{{ route('products.transfers-in', ['filter[type]' => 'App\\Models\\DataCollectionTransferIn']) }}">{{ __('Transfers In') }}</a>
                        <a class="dropdown-item text-white lightHover mt-1" id="transfers_out_link"
                            href="{{ route('products.transfers-out', ['filter[type]' => 'App\\Models\\DataCollectionTransferOut']) }}">{{ __('Transfers Out') }}</a>
                        <a class="dropdown-item text-white lightHover mt-1" id="offline_inventory_link"
                            href="{{ route('products.offline-inventory', ['filter[type]' => 'App\\Models\\DataCollectionOfflineInventory']) }}">{{ __('Offline Inventory') }}</a>
                        <a class="dropdown-item text-white lightHover mt-1" id="purchases_order_link"
                            href="{{ route('products.purchase-orders', ['filter[type]' => 'App\\Models\\DataCollectionPurchaseOrder']) }}">{{ __('Purchase Orders') }}</a>
                        {{--                        <a class="dropdown-item text-white lightHover mt-1" id="transactions_link" href="{{ route('data-collector', ['filter[type]' => 'App\\Models\\DataCollectionTransaction']) }}">{{ __('Transactions') }}</a> --}}
                        <a class="dropdown-item text-white lightHover mt-1" id="stocktaking_link"
                            href="{{ route('products.stocktaking') }}">{{ __('Stocktaking') }}</a>
                    </div>
                </div>

                <a id="orders_link" class="btn btn-primary px-2" href="{{ route('orders') }}">{{ __('Orders') }}</a>

                @if (Auth::user()->warehouse_id)
                    <!-- Tools -->
                    <div class="dropdown">
                        <a id="tools_link" class="dropdown-toggle btn btn-primary px-2" href="#" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ __('Tools') }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-left w-auto text-left bg-primary "
                            aria-labelledby="navbarDropdown">
                            {{--                            <a class="dropdown-item text-white lightHover mt-1" id="point_of_sale_link" href="{{ route('tools.point_of_sale') }}">{{ __('Point Of Sale') }}</a> --}}
                            <a class="dropdown-item text-white lightHover mt-1 mb-1" id="picklist_link"
                                href="{{ route('tools.picklist', ['step' => 'select']) }}">{{ __('Picklist') }}</a>
                            <a class="dropdown-item text-white lightHover mt-1 mb-1" id="packlist_link"
                                href="{{ route('tools.packlist', ['step' => 'select']) }}">{{ __('Packlist') }}</a>
                            <a class="dropdown-item text-white lightHover mt-1 mb-2" id="restocking_link"
                                href="{{ route('tools.restocking', ['sort' => '-quantity_required', 'cache_name' => 'restocking_page']) }}">{{ __('Restocking') }}</a>

                            <a class="dropdown-item text-white lightHover mt-1 mb-0" id="data_collector_link"
                                href="{{ route('tools.data-collector', ['filter[without_transactions]' => true]) }}">{{ __('Data Collector') }}</a>
                            <a class="dropdown-item text-white lightHover mt-1 mb-0" id="shelf_label_printing"
                                href="{{ route('tools.shelf-labels') }}">{{ __('Shelf Labels') }}</a>
                        </div>
                    </div>
                @endif

                <!-- Middle empty fill -->
                <div class="flex-fill"></div>

                <!-- Reports Dropdown -->
                <div class="dropdown">
                    <a id="reports_link" class="dropdown-toggle btn btn-primary px-2" href="#" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Reports
                    </a>

                    <div class="dropdown-menu dropdown-menu-right bg-primary" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('inventory-dashboard') }}">{{ __('Inventory Dashboard') }}</a>
                        <a class="dropdown-item text-white lightHover mb-1"
                            href="{{ route('fulfillment-dashboard') }}">{{ __('Orders Dashboard') }}</a>
                        <hr class="m-2">
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('fulfillment-statistics') . '?between_dates=-7days,now' }}">{{ __('Fulfillment Statistics') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.orders.index', ['sort' => '-order_placed_at']) }}">{{ __('Orders') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.picks.index', ['sort' => '-picked_at']) }}">{{ __('Order Picks') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.shipments.index', ['sort' => '-created_at']) }}">{{ __('Order Shipments') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.order-fulfillment-time.index') }}">{{ __('Orders Fulfillment Time') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.inventory.index', ['filter[warehouse_code]' => data_get(Auth::user(), 'warehouse.code'), 'sort' => '-quantity']) }}">{{ __('Inventory') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.inventory-movements.index', ['filter[warehouse_code]' => data_get(Auth::user(), 'warehouse.code'), 'sort' => '-occurred_at,-sequence_number']) }}">{{ __('Inventory Movements') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.inventory-transfers.index', ['filter[warehouse_code]' => data_get(Auth::user(), 'warehouse.code'), 'sort' => '-created_at']) }}">{{ __('Inventory Transfers') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.inventory-movements-summary.index', ['per_page' => '200', 'sort' => 'type']) }}">{{ __('Inventory Movements Summary') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.inventory-reservations.index', ['filter[warehouse_code]' => data_get(Auth::user(), 'warehouse.code'), 'sort' => '-created_at']) }}">{{ __('Inventory Reservations') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.restocking.index') }}">{{ __('Restocking') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.stocktake-suggestions.index', ['filter[warehouse_code]' => data_get(Auth::user(), 'warehouse.code'), 'sort' => '-points']) }}">{{ __('Stocktake Suggestions') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('reports.activity-log.index', ['sort' => '-id']) }}">{{ __('Activity Log') }}</a>

                        @if (count($navigationMenuReports) > 0)
                            <hr v-if='{{ count($navigationMenuReports) > 0 }}' class="mb-1 mt-1">
                            @foreach ($navigationMenuReports as $menu)
                                <a class="dropdown-item text-white lightHover" href="{{ $menu->url }}">
                                    {{ $menu->name }}
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Menu -->
                <div class="dropdown dropdown-menu-right">
                    <a style="height: 37px; width: 40px; position: relative; top: -2px; right: 2px" id="dropdownMenu"
                        class="btn btn-primary px-2" href="#" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20" width="16"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                            <path fill="#ffffff"
                                d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z" />
                        </svg>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right w-auto text-left bg-primary"
                        aria-labelledby="navbarDropdown">
                        {{-- Profile --}}
                        <a class="dropdown-item text-white lightHover"
                            href="{{ route('setting-profile') }}">{{ __('Profile') }}</a>

                        @if (auth()->user()->hasRole('admin'))
                            <a class="dropdown-item text-white lightHover"
                                href="{{ route('settings') }}">{{ __('Settings') }}</a>
                            <a class="dropdown-item text-white lightHover"
                                href="/quick-connect">{{ __('Quick Connect') }}</a>
                            <a class="dropdown-item text-white lightHover"
                                href="{{ route('settings.chatgpt') }}">{{ __('ChatGPT') }}</a>
                        @endif

                        <hr class="m-1">
                        <a class="dropdown-item text-white lightHover" href="https://ship.town/academy"
                            target="_blank">{{ __('Academy') }}</a>
                        <a class="dropdown-item text-white lightHover"
                            href="https://www.youtube.com/channel/UCl04S5dRXop1ZdZsOqY3OnA"
                            target="_blank">{{ __('YouTube') }}</a>
                        <a class="dropdown-item text-white lightHover" href="/docs/index.html"
                            target="_blank">{{ __('API Docs') }}</a>

                        <!-- Logout -->
                        <a class="dropdown-item text-white lightHover" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <language-selector></language-selector>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
@endauth
