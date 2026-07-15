@extends('layouts.app')

@section('title', 'ERP Inventory Management System - Alerts & Reorders')

@section('content')
    <!-- Top Metrics Cards Group -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div onclick="applyQuickCardFilter('Out of Stock')" class="bg-white p-5 rounded-xl border-2 border-transparent hover:border-red-500 cursor-pointer shadow-sm flex items-center justify-between transition group">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-red-500">Out of Stock</p>
                <h3 class="text-2xl font-bold text-navyBlue mt-1" id="out-of-stock-count">0</h3>
                <p class="text-xs text-red-500 mt-1 font-medium underline">Click to filter table</p>
            </div>
            <div class="p-3 bg-red-50 text-red-500 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>

        <div onclick="applyQuickCardFilter('Low Stock')" class="bg-white p-5 rounded-xl border-2 border-transparent hover:border-amber-500 cursor-pointer shadow-sm flex items-center justify-between transition group">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-amber-500">Low Stock</p>
                <h3 class="text-2xl font-bold text-navyBlue mt-1" id="low-stock-count">0</h3>
                <p class="text-xs text-amber-500 mt-1 font-medium underline">Click to filter table</p>
            </div>
            <div class="p-3 bg-amber-50 text-amber-500 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>

        <div onclick="applyQuickCardFilter('Overstock')" class="bg-white p-5 rounded-xl border-2 border-transparent hover:border-blue-500 cursor-pointer shadow-sm flex items-center justify-between transition group">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-blue-500">Overstock</p>
                <h3 class="text-2xl font-bold text-navyBlue mt-1" id="overstock-count">0</h3>
                <p class="text-xs text-blue-500 mt-1 font-medium underline">Click to filter table</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-500 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
    </section>

    <!-- Charts and Logs Layout -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Expandable Chart Block -->
        <div id="pie-chart-card" class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-between h-48 transition-all duration-300">
            <div class="flex justify-between items-center w-full">
                <h3 class="text-xs font-bold text-navyBlue uppercase tracking-wider">Stock Summary</h3>
                <button id="expand-chart-btn" onclick="toggleChartExpansion()" class="text-xs font-bold text-blue-600 hover:underline">[Expand View]</button>
            </div>
            <div class="flex items-center justify-around w-full flex-1 py-2">
                <div id="pie-chart-render" class="w-24 h-24 rounded-full border border-gray-100 shadow-inner transition-all duration-500"></div>
                <div class="text-[10px] space-y-0.5 font-medium ml-2">
                    <div class="flex items-center space-x-1.5"><span class="w-2 h-2 rounded-full bg-emeraldGreen inline-block"></span><span id="legend-normal">Normal</span></div>
                    <div class="flex items-center space-x-1.5"><span class="w-2 h-2 rounded-full bg-navyBlue inline-block"></span><span id="legend-overstock">Overstock</span></div>
                    <div class="flex items-center space-x-1.5"><span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span><span id="legend-low">Low Stock</span></div>
                    <div class="flex items-center space-x-1.5"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span><span id="legend-out">Out of Stock</span></div>
                </div>
            </div>
            <div id="chart-extended-details" class="hidden w-full border-t border-gray-100 pt-3 mt-1 grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="block text-[10px] uppercase font-bold text-gray-400">Warehouse Capacity</span>
                    <div class="w-full bg-gray-200 h-2 rounded-full mt-1"><div class="bg-navyBlue h-2 rounded-full w-[64%]"></div></div>
                </div>
                <div>
                    <span class="block text-[10px] uppercase font-bold text-gray-400">Supply Health</span>
                    <span class="text-emeraldGreen font-bold block mt-0.5">Optimal (88%)</span>
                </div>
            </div>
        </div>

        <!-- Activity History Log -->
        <div id="log-card" class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm lg:col-span-2 flex flex-col h-48 transition-all duration-300">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-xs font-bold text-navyBlue uppercase tracking-wider">Activity Log</h3>
                <button id="expand-log-btn" onclick="toggleLogExpansion()" class="text-xs font-bold text-blue-600 hover:underline">[Expand View]</button>
            </div>
            <div id="transaction-log" class="flex-1 overflow-y-auto space-y-1 pr-1 text-xs font-mono">
                <div class="text-gray-400 italic">No history found.</div>
            </div>
        </div>
    </section>

    <!-- Order Approvals Pipeline Board -->
    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xs font-bold text-navyBlue uppercase tracking-wider">Order Approvals Pipeline</h3>
            <div class="flex items-center gap-2">
                <span class="text-[10px] bg-amber-500 text-white px-2 py-0.5 rounded font-bold hidden" id="draft-count">0 Auto-Drafts Awaiting Review</span>
                <span class="text-[10px] bg-navyBlue text-white px-2 py-0.5 rounded font-bold" id="pipeline-count">0 Active</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase font-semibold text-[10px]">
                        <th class="py-3 px-4">Date / Time</th>
                        <th class="py-3 px-4">Requested By</th>
                        <th class="py-3 px-4">Order Details</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="approval-pipeline-body" class="divide-y divide-gray-100 text-gray-700">
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-400 italic">No orders currently pending review.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Search and Action Controls Strip -->
    <section class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto flex-1">
                <label for="search-input" class="sr-only">Search item name or ID</label>
                <input type="text" id="search-input" name="search" placeholder="Search item name or ID..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navyBlue text-sm w-full sm:w-80">
                <label for="status-filter" class="sr-only">Filter by status</label>
                <select id="status-filter" name="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navyBlue text-sm bg-white">
                    <option value="all">All Statuses</option>
                    <option value="Normal">Normal Stock</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                    <option value="Overstock">Overstock</option>
                </select>
            </div>
            <button onclick="openBatchPOModal()" class="w-full sm:w-auto px-5 py-2.5 bg-emeraldGreen text-white font-semibold rounded-lg hover:bg-emeraldGreen/90 transition text-sm shadow-sm flex items-center justify-center space-x-2">
                <span>Configure & Run Batch PO</span>
            </button>
        </div>

        <!-- Manual Filter Quick-Tabs -->
        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 text-xs">
            <span class="text-gray-400 font-semibold uppercase tracking-wider mr-2">Filter Badges:</span>
            <button onclick="applyQuickCardFilter('all')" id="btn-filter-all" class="px-3 py-1 rounded-full border bg-navyBlue text-white font-medium shadow-xs">Show All</button>
            <button onclick="applyQuickCardFilter('Normal')" id="btn-filter-normal" class="px-3 py-1 rounded-full border bg-white text-gray-600 hover:bg-gray-100 font-medium">Normal</button>
            <button onclick="applyQuickCardFilter('Low Stock')" id="btn-filter-low" class="px-3 py-1 rounded-full border bg-white text-gray-600 hover:bg-amber-50 font-medium">Low Stock</button>
            <button onclick="applyQuickCardFilter('Out of Stock')" id="btn-filter-out" class="px-3 py-1 rounded-full border bg-white text-gray-600 hover:bg-red-50 font-medium">Out of Stock</button>
            <button onclick="applyQuickCardFilter('Overstock')" id="btn-filter-over" class="px-3 py-1 rounded-full border bg-white text-gray-600 hover:bg-blue-50 font-medium">Overstock</button>
        </div>
    </section>

    <!-- Master Data Inventory Table -->
    <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-200 text-navyBlue text-xs uppercase font-semibold">
                        <th class="py-4 px-4 w-12 text-center">Select</th>
                        <th class="py-4 px-4">Item Details</th>
                        <th class="py-4 px-2 text-center">Quantity</th>
                        <th class="py-4 px-3 text-center w-28">Min Limit</th>
                        <th class="py-4 px-3 text-center w-28">Max Limit</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-3 text-center w-24">Auto-Reorder</th>
                        <th class="py-4 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="inventory-table-body" class="divide-y divide-gray-100 text-sm text-gray-700">
                    <!-- Content Rows Injected via JavaScript -->
                </tbody>
            </table>
        </div>
        <div id="no-results" class="hidden p-8 text-center text-gray-400">No records found.</div>
    </section>

    <!-- Purchase Order Modal Form -->
    <div id="po-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl border border-gray-100 overflow-hidden">
            <div class="bg-navyBlue text-white px-6 py-4 flex justify-between items-center">
                <h4 id="po-modal-title" class="font-bold text-xs tracking-wide uppercase">New Order Form</h4>
                <button onclick="closePOModal()" class="text-white hover:text-gray-300 text-2xl font-semibold leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="modal-item-id" name="modalItemId">
                
                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase mb-1">Target Product Line</span>
                    <p id="modal-item-name" class="font-semibold text-gray-800 text-xs bg-gray-50 p-2.5 rounded border border-gray-200"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modal-item-qty" class="block text-xs font-bold text-gray-500 mb-1">Order Quantity</label>
                        <input type="number" id="modal-item-qty" name="orderQty" value="10" min="1" class="w-full border border-gray-300 rounded px-3 py-1.5 text-xs focus:ring-1 focus:ring-navyBlue focus:outline-none">
                    </div>
                    <div>
                        <label for="modal-item-supplier" class="block text-xs font-bold text-gray-500 mb-1">Supplier</label>
                        <select id="modal-item-supplier" name="supplier" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-white focus:ring-1 focus:ring-navyBlue focus:outline-none">
                            <option value="Global Logistics">Global Logistics</option>
                            <option value="Nexus Distribution">Nexus Distribution</option>
                            <option value="Apex Supplies">Apex Supplies</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modal-item-urgency" class="block text-xs font-bold text-gray-500 mb-1">Urgency Status</label>
                        <select id="modal-item-urgency" name="urgency" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-white focus:ring-1 focus:ring-navyBlue focus:outline-none">
                            <option value="Standard Delivery">Standard Delivery</option>
                            <option value="Express Air">Express Air</option>
                        </select>
                    </div>
                    <div>
                        <label for="modal-item-warehouse" class="block text-xs font-bold text-gray-500 mb-1">Destination Warehouse</label>
                        <select id="modal-item-warehouse" name="warehouse" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-white focus:ring-1 focus:ring-navyBlue focus:outline-none">
                            <option value="Alpha Warehouse">Alpha Warehouse</option>
                            <option value="Beta Hub Facility">Beta Hub Facility</option>
                        </select>
                    </div>
                </div>

                <div class="bg-blue-50 text-navyBlue p-3 rounded text-xs leading-normal border border-blue-100">
                    <strong>Notice:</strong> This order requires supervisor authorization in the <strong>Pipeline Board</strong> before stock adjustments apply.
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button onclick="closePOModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                    <button id="po-submit-btn" onclick="submitPOForm()" class="px-4 py-2 bg-emeraldGreen text-white rounded-lg text-xs font-semibold shadow hover:bg-emeraldGreen/90 transition">Submit to Pipeline</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Center Alerts Notification Window Frame -->
    <div id="centered-alert" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl p-6 text-center border border-gray-100 transform scale-95 transition-all duration-200">
            <div id="alert-icon-zone" class="mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-3"></div>
            <h3 id="alert-heading" class="text-base font-bold text-slate-900 mb-1">Notification</h3>
            <p id="alert-body" class="text-xs text-gray-600 leading-relaxed mb-5"></p>
            <button onclick="closeCenteredAlert()" class="px-6 py-2 bg-navyBlue hover:bg-blue-800 text-white font-semibold text-xs rounded-lg transition shadow">Dismiss</button>
        </div>
    </div>

    <script>
        let inventoryItems = [];
        let approvalRequests = [];
        let currentActiveStatusFilter = "all";
        let adminIndex = 1;
        let isBatchMode = false;
        let batchSelectedIds = [];

        // Global headers configuration setup for handling all AJAX operations securely
        const ajaxHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        async function fetchSystemData() {
            try {
                const response = await fetch("{{ route('alerts.data') }}");
                const data = await response.json();
                inventoryItems = data.inventoryItems;
                approvalRequests = data.approvalRequests;

                refreshDashboardUI();
                applyFilters();
                renderPipelineTable();
                renderActivityLog(data.activityLogs || []); // real, persisted logs
            } catch (error) {
                console.error("Failed loading structural data:", error);
            }
        }

        // NEW: renders logs pulled from the activity_logs table — these
        // survive a page refresh, unlike the old addTransactionLog()-only
        // approach which only lived in browser memory and vanished on reload.
        function renderActivityLog(logs) {
            const logContainer = document.getElementById("transaction-log");

            if (!logs.length) {
                logContainer.innerHTML = `<div class="text-gray-400 italic">No history found.</div>`;
                return;
            }

            logContainer.innerHTML = logs.map(log => {
                let color = "text-slate-600";
                if (log.type === 'success') color = "text-emeraldGreen font-semibold";
                if (log.type === 'error') color = "text-red-500 font-bold";
                if (log.type === 'warning') color = "text-amber-600 font-semibold";

                const time = new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                const adminName = log.user ? log.user.name : 'System';

                return `<div class="py-1 border-b border-gray-100 ${color}">[${time}] <span class="text-navyBlue font-bold">[${adminName}]</span> ${log.description}</div>`;
            }).join('');
        }

        function getNextAdminSignature() {
            const currentAdmin = `Admin ${adminIndex}`;
            adminIndex = (adminIndex % 3) + 1;
            return currentAdmin;
        }

        function getStockStatus(item) {
            if (parseInt(item.currentQty) === 0) return "Out of Stock";
            if (parseInt(item.currentQty) < parseInt(item.minLimit)) return "Low Stock";
            if (parseInt(item.currentQty) > parseInt(item.maxLimit)) return "Overstock";
            return "Normal";
        }

        function getStatusBadge(status) {
            switch(status) {
                case "Out of Stock": return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Out of Stock</span>`;
                case "Low Stock": return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Low Stock</span>`;
                case "Overstock": return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Overstock</span>`;
                default: return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Normal</span>`;
            }
        }

        function addTransactionLog(message, type = 'info', userSignature = 'System') {
            const logContainer = document.getElementById("transaction-log");
            if (logContainer.innerHTML.includes("No history found")) logContainer.innerHTML = "";
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            let color = "text-slate-600";
            if (type === 'success') color = "text-emeraldGreen font-semibold";
            if (type === 'error') color = "text-red-500 font-bold";
            if (type === 'warning') color = "text-amber-600 font-semibold";

            logContainer.innerHTML = `<div class="py-1 border-b border-gray-100 ${color}">[${time}] <span class="text-navyBlue font-bold">[${userSignature}]</span> ${message}</div>` + logContainer.innerHTML;
        }

        function toggleLogExpansion() {
            const logCard = document.getElementById("log-card");
            const expandBtn = document.getElementById("expand-log-btn");
            logCard.classList.toggle("h-48");
            logCard.classList.toggle("h-96");
            expandBtn.innerText = logCard.classList.contains("h-48") ? "[Expand View]" : "[Minimize View]";
        }

        function toggleChartExpansion() {
            const chartCard = document.getElementById("pie-chart-card");
            const extraDetails = document.getElementById("chart-extended-details");
            const expandBtn = document.getElementById("expand-chart-btn");
            
            chartCard.classList.toggle("h-48");
            chartCard.classList.toggle("h-72");
            extraDetails.classList.toggle("hidden");
            expandBtn.innerText = chartCard.classList.contains("h-48") ? "[Expand View]" : "[Minimize View]";
        }

        function refreshDashboardUI() {
            let outOfStock = 0, lowStock = 0, overStock = 0, normal = 0;
            inventoryItems.forEach(item => {
                const status = getStockStatus(item);
                if (status === "Out of Stock") outOfStock++;
                else if (status === "Low Stock") lowStock++;
                else if (status === "Overstock") overStock++;
                else normal++;
            });

            document.getElementById("out-of-stock-count").innerText = outOfStock;
            document.getElementById("low-stock-count").innerText = lowStock;
            document.getElementById("overstock-count").innerText = overStock;

            const total = inventoryItems.length || 1;
            const slice1 = (normal / total) * 100;
            const slice2 = slice1 + ((overStock / total) * 100);
            const slice3 = slice2 + ((lowStock / total) * 100);

            document.getElementById("pie-chart-render").style.background = `
                conic-gradient(#10B981 0% ${slice1}%, #1E3A8A ${slice1}% ${slice2}%, #F59E0B ${slice2}% ${slice3}%, #EF4444 ${slice3}% 100%)
            `;
        }

        function renderTable(filteredData) {
            const tbody = document.getElementById("inventory-table-body");
            const noResults = document.getElementById("no-results");
            tbody.innerHTML = "";

            if (filteredData.length === 0) {
                noResults.classList.remove("hidden");
                return;
            }
            noResults.classList.add("hidden");

            filteredData.forEach(item => {
                const status = getStockStatus(item);
                const row = document.createElement("tr");
                row.className = "hover:bg-gray-50 border-b border-gray-100 transition";
                row.innerHTML = `
                    <td class="py-3 px-4 text-center">
                        <label for="select-${item.id}" class="sr-only">Select ${item.name}</label>
                        <input type="checkbox" id="select-${item.id}" name="select-${item.id}" value="${item.id}" class="part-checkbox rounded border-gray-300">
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-semibold text-gray-900">${item.name}</div>
                        <div class="text-xs text-gray-400">${item.category} | ${item.id}</div>
                    </td>
                    <td class="py-3 px-2 text-center font-bold text-gray-900">${item.currentQty}</td>
                    <td class="py-3 px-3 text-center">
                        <label for="min-limit-${item.id}" class="sr-only">Min limit for ${item.name}</label>
                        <input type="number" id="min-limit-${item.id}" name="min-limit-${item.id}" value="${item.minLimit}" min="0" onchange="updateLimits('${item.id}', 'min', this.value)" class="w-16 border border-gray-300 rounded text-center px-1 py-0.5 text-xs">
                    </td>
                    <td class="py-3 px-3 text-center">
                        <label for="max-limit-${item.id}" class="sr-only">Max limit for ${item.name}</label>
                        <input type="number" id="max-limit-${item.id}" name="max-limit-${item.id}" value="${item.maxLimit}" min="1" onchange="updateLimits('${item.id}', 'max', this.value)" class="w-16 border border-gray-300 rounded text-center px-1 py-0.5 text-xs">
                    </td>
                    <td class="py-3 px-4 text-center">${getStatusBadge(status)}</td>
                    <td class="py-3 px-3 text-center">
                        <label class="relative inline-flex items-center cursor-pointer" title="Auto-create a draft PO when this item hits Low/Out of Stock">
                            <span class="sr-only">Auto-reorder for ${item.name}</span>
                            <input type="checkbox" id="auto-reorder-${item.id}" name="auto-reorder-${item.id}" class="sr-only peer" ${item.auto_reorder ? 'checked' : ''} onchange="toggleAutoReorder('${item.id}', this.checked)">
                            <div class="w-9 h-5 bg-gray-200 peer-checked:bg-emeraldGreen rounded-full transition-colors"></div>
                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                        </label>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <button onclick="openPOModal('${item.id}', '${item.name}')" class="px-2.5 py-1 text-xs font-semibold rounded bg-blue-50 text-navyBlue hover:bg-navyBlue hover:text-white border border-blue-200 transition">
                            Create PO
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function renderPipelineTable() {
            const tbody = document.getElementById("approval-pipeline-body");
            const counter = document.getElementById("pipeline-count");
            tbody.innerHTML = "";

            const pendingRequests = approvalRequests.filter(r => r.status === "Pending");
            const draftRequests = approvalRequests.filter(r => r.status === "Draft");
            counter.innerText = `${pendingRequests.length} Active`;

            const draftCounter = document.getElementById("draft-count");
            if (draftRequests.length > 0) {
                draftCounter.innerText = `${draftRequests.length} Auto-Draft${draftRequests.length > 1 ? 's' : ''} Awaiting Review`;
                draftCounter.classList.remove("hidden");
            } else {
                draftCounter.classList.add("hidden");
            }

            if(approvalRequests.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-gray-400 italic">No orders currently pending review.</td></tr>`;
                return;
            }

            approvalRequests.forEach(req => {
                let statusStyle = "text-amber-600 font-semibold"; // Pending
                if(req.status === "Ordered") statusStyle = "text-blue-600 font-bold";
                if(req.status === "Received") statusStyle = "text-emeraldGreen font-bold";
                if(req.status === "Voided") statusStyle = "text-gray-400 line-through";
                if(req.status === "Draft") statusStyle = "text-amber-700 font-bold";

                let actionTray = `<span class="text-xs text-gray-400 italic">Completed</span>`;

                if (req.status === "Draft") {
                    // Auto-generated by the reorder engine — needs a human
                    // to review before it enters the real approval pipeline.
                    actionTray = `
                        <button onclick="submitDraftAction(${req.reqId})" class="px-2 py-0.5 bg-navyBlue text-white rounded font-bold hover:bg-blue-800 text-[11px]">Submit to Pipeline</button>
                        <button onclick="discardDraftAction(${req.reqId})" class="px-2 py-0.5 bg-red-500 text-white rounded font-bold hover:bg-red-600 text-[11px] ml-1">Discard</button>
                    `;
                } else if (req.status === "Pending") {
                    actionTray = `
                        <button onclick="processPipelineAction(${req.reqId}, 'Approved')" class="px-2 py-0.5 bg-emeraldGreen text-white rounded font-bold hover:bg-emeraldGreen/90 text-[11px]">Approve</button>
                        <button onclick="processPipelineAction(${req.reqId}, 'Voided')" class="px-2 py-0.5 bg-red-500 text-white rounded font-bold hover:bg-red-600 text-[11px] ml-1">Void</button>
                    `;
                } else if (req.status === "Ordered") {
                    // FIX: Approving used to instantly add stock. Now "Ordered" just means
                    // the PO was approved — stock only changes once you confirm the
                    // shipment actually arrived, via this separate action.
                    actionTray = `
                        <button onclick="markReceivedAction(${req.reqId})" class="px-2 py-0.5 bg-blue-600 text-white rounded font-bold hover:bg-blue-700 text-[11px]">Mark as Received</button>
                    `;
                }

                const row = document.createElement("tr");
                row.className = req.status === "Draft" ? "hover:bg-amber-50/50 bg-amber-50/30" : "hover:bg-gray-50/50";
                row.innerHTML = `
                    <td class="py-3 px-4 text-gray-500">${req.timestamp}</td>
                    <td class="py-3 px-4 font-bold text-navyBlue">
                        ${req.requester}
                        ${req.source === 'auto' ? '<span class="ml-1 px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[9px] font-bold uppercase align-middle">Auto</span>' : ''}
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-medium text-gray-900">${req.details}</div>
                        <div class="text-[10px] text-gray-400">Supplier: ${req.supplier} | Route: ${req.warehouse}</div>
                    </td>
                    <td class="py-3 px-4 text-center ${statusStyle}">${req.status}</td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">${actionTray}</td>
                `;
                tbody.appendChild(row);
            });
        }

        async function submitDraftAction(requestId) {
            const reviewingAdmin = getNextAdminSignature();
            try {
                const response = await fetch(`/alerts-reorders/draft/${requestId}/submit`, { method: 'POST', headers: ajaxHeaders });
                const result = await response.json();
                if (response.ok) {
                    addTransactionLog(`Draft Reviewed: Submitted auto-draft #${requestId} into the pipeline.`, 'info', reviewingAdmin);
                    triggerAlertWindow("Draft Submitted", "The draft order is now pending approval in the pipeline.", "success");
                    fetchSystemData();
                } else {
                    triggerAlertWindow("Action Blocked", result.message || "Operation failed", "error");
                }
            } catch (error) {
                console.error("Submit draft failed:", error);
                triggerAlertWindow("System Error", "Could not process this request. Please try again.", "error");
            }
        }

        async function discardDraftAction(requestId) {
            const reviewingAdmin = getNextAdminSignature();
            try {
                const response = await fetch(`/alerts-reorders/draft/${requestId}/discard`, { method: 'POST', headers: ajaxHeaders });
                const result = await response.json();
                if (response.ok) {
                    addTransactionLog(`Draft Discarded: Removed auto-draft #${requestId}.`, 'error', reviewingAdmin);

                    if (result.autoReorderTurnedOff) {
                        triggerAlertWindow(
                            "Draft Discarded",
                            "This draft was declined, and auto-reorder has been turned OFF for this item so it won't be redrafted automatically. Toggle it back on if you want it to keep auto-ordering.",
                            "info"
                        );
                    }

                    fetchSystemData();
                } else {
                    triggerAlertWindow("Action Blocked", result.message || "Operation failed", "error");
                }
            } catch (error) {
                console.error("Discard draft failed:", error);
                triggerAlertWindow("System Error", "Could not process this request. Please try again.", "error");
            }
        }

        async function toggleAutoReorder(id, enabled) {
            const adminUser = getNextAdminSignature();
            try {
                const response = await fetch(`/alerts-reorders/auto-reorder/${id}`, {
                    method: 'POST',
                    headers: ajaxHeaders,
                    body: JSON.stringify({ enabled: enabled })
                });
                const result = await response.json();
                if (response.ok) {
                    addTransactionLog(`Auto-Reorder ${enabled ? 'Enabled' : 'Disabled'}: ${id}.`, 'info', adminUser);
                    if (result.draftsCreated > 0) {
                        triggerAlertWindow("Draft Order Created", `${id} was already on an active alert — a draft PO was created automatically.`, "warning");
                    }
                    fetchSystemData();
                }
            } catch (error) {
                console.error("Toggle auto-reorder failed:", error);
            }
        }


        async function processPipelineAction(requestId, targetStatus) {
            const reviewingAdmin = getNextAdminSignature();
            
            try {
                const response = await fetch(`/alerts-reorders/pipeline/${requestId}`, {
                    method: 'POST',
                    headers: ajaxHeaders,
                    body: JSON.stringify({ status: targetStatus })
                });

                const result = await response.json();

                if(response.ok) {
                    if(targetStatus === "Voided") {
                        addTransactionLog(`Void Action: Cancelled order #${requestId}.`, 'error', reviewingAdmin);
                        triggerAlertWindow("Order Canceled", "The order has been removed from the pipeline.", "error");
                    } else {
                        // FIX: approving no longer touches stock — it just marks the PO
                        // as ordered. Message updated so it doesn't imply stock changed.
                        addTransactionLog(`Order Approved: Authorized order #${requestId}. Awaiting delivery.`, 'success', reviewingAdmin);
                        triggerAlertWindow("Order Approved", "The order has been placed with the supplier. Stock will update once marked as Received.", "success");
                    }
                    fetchSystemData();
                } else {
                    addTransactionLog(`Approval Denied: Order #${requestId} blocked.`, 'error', reviewingAdmin);
                    triggerAlertWindow("Approval Blocked", result.message || "Operation failed", "error");
                }
            } catch (error) {
                console.error("Pipeline action failed:", error);
                triggerAlertWindow("System Error", "Could not process this request. Please try again.", "error");
            }
        }

        // NEW: separate action for confirming a shipment actually arrived.
        // This is the ONLY action that changes currentQty for a purchase order now.
        async function markReceivedAction(requestId) {
            const reviewingAdmin = getNextAdminSignature();

            try {
                const response = await fetch(`/alerts-reorders/pipeline/${requestId}/receive`, {
                    method: 'POST',
                    headers: ajaxHeaders,
                });

                const result = await response.json();

                if (response.ok) {
                    addTransactionLog(`Shipment Received: Order #${requestId} recorded for Stock Movements.`, 'success', reviewingAdmin);
                    triggerAlertWindow("Shipment Received", "Marked as received. The stock movement has been recorded and is now ready for Stock Movements to apply.", "success");
                    fetchSystemData();
                } else {
                    addTransactionLog(`Receive Blocked: Order #${requestId} could not be received.`, 'error', reviewingAdmin);
                    triggerAlertWindow("Receive Blocked", result.message || "Operation failed", "error");
                }
            } catch (error) {
                console.error("Mark as received failed:", error);
                triggerAlertWindow("System Error", "Could not process this request. Please try again.", "error");
            }
        }

        function openPOModal(id, name) {
            isBatchMode = false;
            document.getElementById("po-modal-title").innerText = "New Order Form";
            document.getElementById("modal-item-id").value = id;
            document.getElementById("modal-item-name").innerText = name;
            document.getElementById("po-modal").classList.remove("hidden");
        }

        function openBatchPOModal() {
            const checkboxes = document.querySelectorAll(".part-checkbox:checked");
            if(checkboxes.length === 0) {
                triggerAlertWindow("No Selection", "Please check at least one item from the table to configure a batch order.", "warning");
                return;
            }

            isBatchMode = true;
            batchSelectedIds = Array.from(checkboxes).map(cb => cb.value);

            document.getElementById("po-modal-title").innerText = "Configure Batch PO";
            document.getElementById("modal-item-id").value = "";
            document.getElementById("modal-item-name").innerText = `Batch Order: (${batchSelectedIds.length} items selected)`;
            document.getElementById("po-modal").classList.remove("hidden");
        }

        function closePOModal() {
            document.getElementById("po-modal").classList.add("hidden");
        }

        async function submitPOForm() {
            const quantity = parseInt(document.getElementById("modal-item-qty").value) || 0;
            const supplier = document.getElementById("modal-item-supplier").value;
            const urgency = document.getElementById("modal-item-urgency").value;
            const warehouse = document.getElementById("modal-item-warehouse").value;

            if (quantity <= 0) {
                alert("Please enter a valid operational quantity.");
                return;
            }

            const creator = getNextAdminSignature();
            let payload = {};
            
            if (!isBatchMode) {
                const id = document.getElementById("modal-item-id").value;
                const item = inventoryItems.find(i => i.id === id);
                if(item) {
                    payload = {
                        requester: creator,
                        details: `Purchase ${quantity}x units of ${item.name}`,
                        supplier: supplier,
                        warehouse: `${warehouse} (${urgency})`,
                        itemsArray: [{ id: item.id, qty: quantity }]
                    };
                }
            } else {
                let detailsString = `Batch Order: Buy ${quantity}x units of each for items: `;
                let itemsArray = [];

                batchSelectedIds.forEach(id => {
                    const item = inventoryItems.find(i => i.id === id);
                    if(item) {
                        itemsArray.push({ id: item.id, qty: quantity });
                        detailsString += `[${item.id}] `;
                    }
                });

                payload = {
                    requester: creator,
                    details: detailsString,
                    supplier: supplier,
                    warehouse: `${warehouse} (${urgency})`,
                    itemsArray: itemsArray
                };
                
                document.querySelectorAll(".part-checkbox:checked").forEach(cb => cb.checked = false);
            }

            try {
                const response = await fetch('/alerts-reorders/submit-po', {
                    method: 'POST',
                    headers: ajaxHeaders,
                    body: JSON.stringify(payload)
                });

                if(response.ok) {
                    addTransactionLog(isBatchMode ? `Batch Order Submitted to pipeline verification board.` : `Order Submitted: Created new pending order.`, 'info', creator);
                    closePOModal();
                    fetchSystemData();
                }
            } catch (error) {
                console.error("Mutation process broken down:", error);
            }
        }

        async function updateLimits(id, target, targetValue) {
            const parsedValue = parseInt(targetValue) || 0;
            const adminUser = getNextAdminSignature();

            try {
                const response = await fetch(`/alerts-reorders/update-limits/${id}`, {
                    method: 'POST',
                    headers: ajaxHeaders,
                    body: JSON.stringify({ target: target, value: parsedValue })
                });

                if(response.ok) {
                    addTransactionLog(`Limits Modified: Adjusted stock limit parameters for ${id}.`, 'info', adminUser);
                    fetchSystemData();
                }
            } catch (error) {
                console.error("Failed executing limits sync mutation:", error);
            }
        }

        function applyQuickCardFilter(statusType) {
            currentActiveStatusFilter = statusType;
            document.getElementById("status-filter").value = statusType === 'all' ? 'all' : statusType;
            
            const buttons = {
                'all': 'btn-filter-all', 'Normal': 'btn-filter-normal', 
                'Low Stock': 'btn-filter-low', 'Out of Stock': 'btn-filter-out', 'Overstock': 'btn-filter-over'
            };
            
            Object.keys(buttons).forEach(key => {
                const el = document.getElementById(buttons[key]);
                if(key === statusType) {
                    el.className = "px-3 py-1 rounded-full border bg-navyBlue text-white font-medium shadow-xs";
                } else {
                    el.className = "px-3 py-1 rounded-full border bg-white text-gray-600 hover:bg-gray-100 font-medium";
                }
            });

            applyFilters();
        }

        function clearAllFilters() {
            document.getElementById("search-input").value = "";
            applyQuickCardFilter('all');
        }

        function applyFilters() {
            const searchQuery = document.getElementById("search-input").value.toLowerCase();
            const selectedStatus = document.getElementById("status-filter").value;

            const filtered = inventoryItems.filter(item => {
                const status = getStockStatus(item);
                const matchesSearch = item.name.toLowerCase().includes(searchQuery) || item.id.toLowerCase().includes(searchQuery);
                
                let targetFilter = (currentActiveStatusFilter !== 'all') ? currentActiveStatusFilter : selectedStatus;
                if (selectedStatus !== 'all' && currentActiveStatusFilter === 'all') targetFilter = selectedStatus;

                const matchesStatus = (targetFilter === "all") || (status === targetFilter);
                return matchesSearch && matchesStatus;
            });

            renderTable(filtered);
        }

        function triggerAlertWindow(title, msg, type) {
            const bgZone = document.getElementById("alert-icon-zone");
            const heading = document.getElementById("alert-heading");
            const body = document.getElementById("alert-body");
            
            heading.innerText = title;
            body.innerText = msg;

            if(type === "success") {
                bgZone.className = "mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-3 bg-green-100 text-green-600";
                bgZone.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
            } else if (type === "warning") {
                bgZone.className = "mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-3 bg-amber-100 text-amber-600";
                bgZone.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
            } else {
                bgZone.className = "mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-3 bg-red-100 text-red-600";
                bgZone.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
            }

            document.getElementById("centered-alert").classList.remove("hidden");
        }

        function closeCenteredAlert() {
            document.getElementById("centered-alert").classList.add("hidden");
        }

        document.getElementById("search-input").addEventListener("input", applyFilters);
        document.getElementById("status-filter").addEventListener("change", function(e) {
            currentActiveStatusFilter = e.target.value;
            applyQuickCardFilter(e.target.value);
        });

        // Initialize App & Fetch real runtime tracking points asynchronously
        document.addEventListener("DOMContentLoaded", () => {
            fetchSystemData();
            addTransactionLog("System initialized and dynamic sync established.", "info", "System");
        });
    </script>
@endsection