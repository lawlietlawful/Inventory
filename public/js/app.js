/**
 * APEX INTEGRATED INVENTORY MANAGEMENT SYSTEM (IMS)
 * Frontend Application Engine & Enterprise Controller with Lucide Icons
 */

const App = {
    currentUser: {
        id: 1,
        employee_id: 'EMP-001',
        role_id: 1,
        role_name: 'Administrator',
        full_name: 'System Administrator'
    },
    activeView: 'dashboard',
    charts: {},

    // SVG icon templates for offline / fallback rendering
    svgIcons: {
        'layout-dashboard': '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        'package': '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'arrow-down-to-line': '<path d="M12 17V3"/><path d="m6 11 6 6 6-6"/><path d="M19 21H5"/>',
        'arrow-up-from-line': '<path d="m18 9-6-6-6 6"/><path d="M12 3v14"/><path d="M5 21h14"/>',
        'sliders-horizontal': '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>',
        'calendar-clock': '<path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5"/><circle cx="16" cy="16" r="6"/><path d="M16 14v2l1 1"/>',
        'chef-hat': '<path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" x2="18" y1="17" y2="17"/>',
        'truck': '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/>',
        'clipboard-list': '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'shopping-cart': '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
        'users': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'bar-chart-3': '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
        'file-text': '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
        'settings': '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
        'search': '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'user-circle': '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/>',
        'refresh-cw': '<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>',
        'plus': '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'check': '<path d="M20 6 9 17l-5-5"/>',
        'x': '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'download': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
        'printer': '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
        'credit-card': '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
        'undo-2': '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/>',
        'edit': '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
        'eye': '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'check-circle-2': '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        'package-check': '<path d="m16 16 2 2 4-4"/><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/>',
        'history': '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
        'user-x': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>',
        'user-check': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>',
        'dollar-sign': '<line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'alert-triangle': '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>',
        'alert-octagon': '<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
        'clock': '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'bar-chart-2': '<line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/>',
        'pie-chart': '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
        'bell': '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        'rotate-ccw': '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
        'grid': '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
        'coffee': '<path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/>',
        'utensils': '<path d="M18 2v6a3 3 0 0 1-3 3 3 3 0 0 1-3-3V2"/><path d="M15 11v11"/><path d="M5 2v17a3 3 0 0 0 3 3h0a3 3 0 0 0 3-3V2"/>',
        'cookie': '<path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/>',
        'receipt': '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-8"/><path d="M16 12h-8"/><path d="M12 16h-4"/>',
        'trash-2': '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
        'database': '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>',
        'shield-check': '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/>',
        'minus': '<line x1="5" x2="19" y1="12" y2="12"/>'
    },

    init() {
        console.log('[IMS] Initializing Enterprise Application with Lucide Icons...');
        this.setupNavigation();
        this.setupPersonaSwitcher();
        this.checkSystemStatus();
        this.loadDashboard();

        const hash = window.location.hash.replace('#', '');
        if (hash) this.switchView(hash);
        this.refreshIcons();
    },

    refreshIcons() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        } else {
            this.renderSvgIcons();
        }
    },

    renderSvgIcons() {
        document.querySelectorAll('[data-lucide]').forEach(el => {
            const iconName = el.getAttribute('data-lucide');
            const svgContent = this.svgIcons[iconName];
            if (svgContent) {
                const style = el.getAttribute('style') || '';
                const classes = el.className || 'lucide';
                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.setAttribute('viewBox', '0 0 24 24');
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', 'currentColor');
                svg.setAttribute('stroke-width', '1.85');
                svg.setAttribute('stroke-linecap', 'round');
                svg.setAttribute('stroke-linejoin', 'round');
                svg.setAttribute('class', `lucide lucide-${iconName} ${classes}`);
                if (style) svg.setAttribute('style', style);
                svg.innerHTML = svgContent;
                el.replaceWith(svg);
            }
        });
    },

    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-User-Id': String(this.currentUser.id),
            'X-Employee-Id': this.currentUser.employee_id
        };
    },

    setupNavigation() {
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', () => {
                const view = item.getAttribute('data-view');
                if (view) {
                    this.switchView(view);
                }
            });
        });
    },

    switchView(viewName) {
        this.activeView = viewName;
        window.location.hash = viewName;

        document.querySelectorAll('.menu-item').forEach(item => {
            if (item.getAttribute('data-view') === viewName) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        document.querySelectorAll('.app-view').forEach(view => {
            view.style.display = 'none';
        });

        const target = document.getElementById(`view-${viewName}`);
        if (target) {
            target.style.display = 'block';
            this.onViewLoaded(viewName);
        }
        this.refreshIcons();
    },

    onViewLoaded(viewName) {
        switch (viewName) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'products':
                ProductsUI.init();
                break;
            case 'stock-in':
                InventoryUI.initStockIn();
                break;
            case 'stock-out':
                InventoryUI.initStockOut();
                break;
            case 'adjustments':
                InventoryUI.loadAdjustments();
                break;
            case 'batches':
                InventoryUI.loadBatches();
                break;
            case 'recipes':
                RecipesUI.loadRecipes();
                break;
            case 'suppliers':
                SuppliersUI.loadSuppliers();
                break;
            case 'purchase-orders':
                PurchaseOrdersUI.loadOrders();
                break;
            case 'pos':
                PosUI.init();
                break;
            case 'employees':
                EmployeesUI.loadEmployees();
                break;
            case 'reports':
                ReportsUI.loadActiveReport();
                break;
            case 'audit-logs':
                this.loadAuditLogs();
                break;
            case 'settings':
                this.loadSettings();
                break;
        }
    },

    setupPersonaSwitcher() {
        const select = document.getElementById('user-role-select');
        select.addEventListener('change', (e) => {
            const val = e.target.value;
            const personas = {
                '1': { id: 1, employee_id: 'EMP-001', role_id: 1, role_name: 'Administrator', full_name: 'System Administrator' },
                '2': { id: 2, employee_id: 'EMP-002', role_id: 2, role_name: 'Inventory Manager', full_name: 'Sarah Connor' },
                '3': { id: 3, employee_id: 'EMP-003', role_id: 3, role_name: 'Inventory Staff', full_name: 'Marcus Vance' },
                '4': { id: 4, employee_id: 'EMP-004', role_id: 4, role_name: 'Canteen Cashier', full_name: 'Elena Rostova' },
                '5': { id: 5, employee_id: 'EMP-005', role_id: 5, role_name: 'Employee', full_name: 'John Doe' }
            };

            this.currentUser = personas[val] || personas['1'];
            App.notify(`Switched persona to ${this.currentUser.full_name} (${this.currentUser.role_name})`);
            this.onViewLoaded(this.activeView);
        });
    },

    async checkSystemStatus() {
        try {
            const res = await fetch('/api/system/status');
            const data = await res.json();
            if (data.success) {
                const text = document.getElementById('db-status-text');
                const isLive = data.database.status === 'CONNECTED_LIVE';
                text.textContent = isLive ? 'PostgreSQL Live' : 'PostgreSQL Engine (Active)';
            }
        } catch (e) {
            console.warn('[Status] Database status check unavailable');
        }
    },

    async loadDashboard() {
        try {
            const res = await fetch('/api/reports/dashboard', { headers: this.getHeaders() });
            const data = await res.json();
            if (!data.success) return;

            const sc = data.summary_cards;
            document.getElementById('stat-products').textContent = sc.total_products;
            document.getElementById('stat-categories').textContent = `${sc.total_categories} Categories`;
            document.getElementById('stat-suppliers').textContent = sc.total_suppliers;
            document.getElementById('stat-valuation').textContent = `$${sc.total_inventory_value.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
            document.getElementById('stat-low-stock').textContent = sc.low_stock_products;
            document.getElementById('stat-out-stock').textContent = sc.out_of_stock_products;
            document.getElementById('stat-expiring').textContent = sc.expiring_products;
            document.getElementById('stat-pending-po').textContent = sc.pending_purchase_orders;

            if (sc.expiring_products > 0) {
                const b = document.getElementById('badge-expiring');
                b.textContent = sc.expiring_products;
                b.style.display = 'inline';
            }
            if (sc.pending_purchase_orders > 0) {
                const b = document.getElementById('badge-pending-pos');
                b.textContent = sc.pending_purchase_orders;
                b.style.display = 'inline';
            }

            this.renderCharts(data.charts);

            const tbody = document.querySelector('#table-recent-txns tbody');
            tbody.innerHTML = '';
            if (data.recent_activities.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--color-text-subtle);">No recent transactions recorded.</td></tr>`;
            } else {
                data.recent_activities.forEach(t => {
                    const tr = document.createElement('tr');
                    const badgeClass = t.quantity > 0 ? 'badge-success' : 'badge-danger';
                    const sign = t.quantity > 0 ? '+' : '';
                    tr.innerHTML = `
                        <td><code>${t.transaction_number}</code></td>
                        <td><span class="badge ${badgeClass}">${t.transaction_type}</span></td>
                        <td><strong>${t.product_name || `Product #${t.product_id}`}</strong></td>
                        <td style="font-weight: 600;">${sign}${t.quantity}</td>
                        <td>${t.reference_type || '-'} (${t.reference_id || '-'})</td>
                        <td>${t.employee_id || 'SYSTEM'}</td>
                        <td style="font-size: 0.75rem; color: var(--color-text-muted);">${new Date(t.created_at).toLocaleString()}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            const notifContainer = document.getElementById('list-notifications');
            notifContainer.innerHTML = '';
            if (data.notifications.length === 0) {
                notifContainer.innerHTML = `<div style="text-align: center; color: var(--color-text-subtle); padding: 1.5rem; font-size: 0.825rem;">All systems operational. No active warnings.</div>`;
            } else {
                data.notifications.forEach(n => {
                    const item = document.createElement('div');
                    item.className = `alert-item ${n.priority === 'HIGH' ? 'priority-high' : 'priority-normal'}`;
                    const iconName = n.priority === 'HIGH' ? 'alert-triangle' : 'bell';
                    item.innerHTML = `
                        <div style="font-weight: 700; font-size: 0.78rem; color: var(--color-text-main); display: flex; align-items: center; gap: 4px;">
                            <i data-lucide="${iconName}" style="width: 14px; height: 14px;"></i>
                            ${n.notification_type}
                        </div>
                        <div style="font-size: 0.8rem; color: var(--color-text-body); margin-top: 0.2rem;">${n.message}</div>
                    `;
                    notifContainer.appendChild(item);
                });
            }

            this.refreshIcons();
        } catch (err) {
            console.error('[Dashboard Error]', err);
        }
    },

    renderCharts(chartsData) {
        if (typeof Chart === 'undefined') return;

        // Chart 1: Stock Movement by Category
        const ctxM = document.getElementById('chart-movement');
        if (ctxM) {
            if (this.charts.movement) this.charts.movement.destroy();
            const labels = chartsData.movement_by_category.map(c => c.category_name);
            const inData = chartsData.movement_by_category.map(c => c.stock_in);
            const outData = chartsData.movement_by_category.map(c => c.stock_out);

            this.charts.movement = new Chart(ctxM, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Stock-In', data: inData, backgroundColor: '#0284c7' },
                        { label: 'Stock-Out', data: outData, backgroundColor: '#94a3b8' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }

        // Chart 2: Inventory Value Distribution
        const ctxV = document.getElementById('chart-valuation');
        if (ctxV) {
            if (this.charts.valuation) this.charts.valuation.destroy();
            const labels = chartsData.value_by_category.map(c => c.category_name);
            const valData = chartsData.value_by_category.map(c => c.category_value);

            this.charts.valuation = new Chart(ctxV, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: valData,
                        backgroundColor: ['#0f172a', '#0284c7', '#38bdf8', '#0d9488', '#64748b', '#94a3b8', '#cbd5e1']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    }
                }
            });
        }
    },

    async loadAuditLogs() {
        try {
            const res = await fetch('/api/reports/employee-activities', { headers: this.getHeaders() });
            const data = await res.json();
            const tbody = document.querySelector('#table-audit-logs tbody');
            tbody.innerHTML = '';
            if (!data.rows || data.rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--color-text-subtle);">No audit logs found.</td></tr>`;
                return;
            }
            data.rows.forEach(l => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${l.id}</td>
                    <td><code>${l.employee_id || 'SYSTEM'}</code></td>
                    <td><strong>${l.full_name || l.username || 'System Engine'}</strong></td>
                    <td><span class="badge badge-secondary">${l.action}</span></td>
                    <td><span class="badge badge-secondary">${l.module}</span></td>
                    <td>${l.description}</td>
                    <td><code>${l.ip_address || '127.0.0.1'}</code></td>
                    <td style="font-size: 0.75rem; color: var(--color-text-muted);">${new Date(l.created_at).toLocaleString()}</td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) {
            console.error('[Audit Log Error]', e);
        }
    },

    async loadSettings() {
        try {
            const res = await fetch('/api/system/status');
            const data = await res.json();
            const container = document.getElementById('settings-db-status');
            container.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.85rem;">
                    <div><strong>Connected Engine:</strong> <span class="badge badge-success">${data.database.engine}</span></div>
                    <div><strong>Status:</strong> <code>${data.database.status}</code></div>
                    <div><strong>Host & Port:</strong> <code>${data.database.config.host}:${data.database.config.port}</code></div>
                    <div><strong>Database Name:</strong> <code>${data.database.config.database}</code></div>
                    <div><strong>Timestamp:</strong> <code>${new Date(data.timestamp).toLocaleString()}</code></div>
                </div>
            `;
        } catch (e) {
            console.error('[Settings Error]', e);
        }
    },

    notify(message) {
        const div = document.createElement('div');
        div.className = 'app-toast';
        div.innerHTML = `<i data-lucide="check-circle-2" style="width:16px;height:16px;color:#38bdf8;"></i> <span>${message}</span>`;

        document.body.appendChild(div);
        App.refreshIcons();

        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transform = 'translateY(8px)';
            setTimeout(() => div.remove(), 250);
        }, 3000);
    }
};

/* ==============================================================================
   PRODUCT MANAGEMENT CONTROLLER
   ============================================================================== */
const ProductsUI = {
    products: [],
    categories: [],
    units: [],

    async init() {
        await this.loadLookups();
        await this.loadProducts();
    },

    async loadLookups() {
        try {
            const [catRes, unitRes] = await Promise.all([
                fetch('/api/products/categories', { headers: App.getHeaders() }).then(r => r.json()),
                fetch('/api/products/units', { headers: App.getHeaders() }).then(r => r.json())
            ]);
            this.categories = catRes.categories || [];
            this.units = unitRes.units || [];

            const catSelect = document.getElementById('prod-filter-category');
            if (catSelect) {
                catSelect.innerHTML = '<option value="">All Categories</option>';
                this.categories.forEach(c => {
                    catSelect.innerHTML += `<option value="${c.id}">${c.category_name}</option>`;
                });
            }
        } catch (e) {
            console.error('[Lookups Error]', e);
        }
    },

    async loadProducts() {
        const search = document.getElementById('prod-filter-search')?.value || '';
        const categoryId = document.getElementById('prod-filter-category')?.value || '';
        const availability = document.getElementById('prod-filter-availability')?.value || '';

        let url = `/api/products?search=${encodeURIComponent(search)}`;
        if (categoryId) url += `&category_id=${categoryId}`;
        if (availability) url += `&availability=${availability}`;

        try {
            const res = await fetch(url, { headers: App.getHeaders() });
            const data = await res.json();
            this.products = data.products || [];
            this.renderTable();
        } catch (e) {
            console.error('[Products Load Error]', e);
        }
    },

    resetFilters() {
        document.getElementById('prod-filter-search').value = '';
        document.getElementById('prod-filter-category').value = '';
        document.getElementById('prod-filter-availability').value = '';
        this.loadProducts();
    },

    renderTable() {
        const tbody = document.querySelector('#table-products tbody');
        tbody.innerHTML = '';
        if (this.products.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align: center; color: var(--color-text-subtle);">No matching products found.</td></tr>`;
            return;
        }

        this.products.forEach(p => {
            const tr = document.createElement('tr');
            let stockBadge = 'badge-success';
            if (p.current_stock <= 0) stockBadge = 'badge-danger';
            else if (p.current_stock <= p.minimum_stock) stockBadge = 'badge-warning';

            tr.innerHTML = `
                <td><code>${p.product_code}</code></td>
                <td><small style="color: var(--color-text-muted);">${p.barcode || '-'}</small></td>
                <td>
                    <strong>${p.product_name}</strong>
                    ${p.is_recipe_item ? '<span class="badge badge-info" style="font-size:0.68rem; margin-left:4px;">Recipe BOM</span>' : ''}
                    ${p.is_ingredient ? '<span class="badge badge-secondary" style="font-size:0.68rem; margin-left:4px;">Ingredient</span>' : ''}
                </td>
                <td>${p.category_name || '-'}</td>
                <td>${p.unit_abbr || 'pcs'}</td>
                <td><span class="badge ${stockBadge}">${p.current_stock}</span></td>
                <td>${p.minimum_stock}</td>
                <td>$${parseFloat(p.cost_price).toFixed(2)}</td>
                <td><strong>$${parseFloat(p.selling_price).toFixed(2)}</strong></td>
                <td><span class="badge ${p.status === 'ACTIVE' ? 'badge-success' : 'badge-secondary'}">${p.status}</span></td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="ProductsUI.openEditModal(${p.id})"><i data-lucide="edit"></i> Edit</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        App.refreshIcons();
    },

    openNewModal() {
        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="package" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Add New Product</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="ProductsUI.handleSave(event)">
                        <div class="modal-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Product Code *</label>
                                    <input type="text" id="m-prod-code" class="form-control" required placeholder="e.g. PRD-FOD-099">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Barcode</label>
                                    <input type="text" id="m-barcode" class="form-control" placeholder="e.g. 8901009999">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Product Name *</label>
                                <input type="text" id="m-prod-name" class="form-control" required placeholder="e.g. Roasted Chicken Wrap">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Category *</label>
                                    <select id="m-category" class="form-control" required>
                                        ${this.categories.map(c => `<option value="${c.id}">${c.category_name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Unit of Measure *</label>
                                    <select id="m-unit" class="form-control" required>
                                        ${this.units.map(u => `<option value="${u.id}">${u.unit_name} (${u.abbreviation})</option>`).join('')}
                                    </select>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Cost Price ($) *</label>
                                    <input type="number" step="0.0001" id="m-cost" class="form-control" required value="0.00">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Selling Price ($) *</label>
                                    <input type="number" step="0.0001" id="m-price" class="form-control" required value="0.00">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Initial Stock</label>
                                    <input type="number" step="0.01" id="m-stock" class="form-control" value="0.00">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Min Stock Level</label>
                                    <input type="number" step="0.01" id="m-min" class="form-control" value="10.00">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Reorder Point</label>
                                    <input type="number" step="0.01" id="m-reorder" class="form-control" value="15.00">
                                </div>
                            </div>
                            <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem; font-size: 0.825rem;">
                                <label><input type="checkbox" id="m-exp-track"> Track Expiration</label>
                                <label><input type="checkbox" id="m-is-recipe"> Finished Recipe Item</label>
                                <label><input type="checkbox" id="m-is-ingredient"> Raw Ingredient</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    openEditModal(id) {
        const prod = this.products.find(p => p.id === id);
        if (!prod) return;

        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="edit" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Edit Product: ${prod.product_name}</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="ProductsUI.handleUpdate(event, ${prod.id})">
                        <div class="modal-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Product Code *</label>
                                    <input type="text" id="edit-prod-code" class="form-control" required value="${prod.product_code}" disabled>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Barcode</label>
                                    <input type="text" id="edit-barcode" class="form-control" value="${prod.barcode || ''}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Product Name *</label>
                                <input type="text" id="edit-prod-name" class="form-control" required value="${prod.product_name}">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Cost Price ($) *</label>
                                    <input type="number" step="0.0001" id="edit-cost" class="form-control" required value="${prod.cost_price}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Selling Price ($) *</label>
                                    <input type="number" step="0.0001" id="edit-price" class="form-control" required value="${prod.selling_price}">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Min Stock Level</label>
                                    <input type="number" step="0.01" id="edit-min" class="form-control" value="${prod.minimum_stock}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Reorder Point</label>
                                    <input type="number" step="0.01" id="edit-reorder" class="form-control" value="${prod.reorder_level}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    async handleUpdate(e, id) {
        e.preventDefault();
        const payload = {
            product_name: document.getElementById('edit-prod-name').value.trim(),
            barcode: document.getElementById('edit-barcode').value.trim() || null,
            cost_price: parseFloat(document.getElementById('edit-cost').value),
            selling_price: parseFloat(document.getElementById('edit-price').value),
            minimum_stock: parseFloat(document.getElementById('edit-min').value),
            reorder_level: parseFloat(document.getElementById('edit-reorder').value)
        };

        try {
            const res = await fetch(`/api/products/${id}`, {
                method: 'PUT',
                headers: App.getHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                App.notify('Product updated successfully.');
                this.closeModal();
                this.loadProducts();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (err) {
            alert(`Update error: ${err.message}`);
        }
    },

    async handleSave(e) {
        e.preventDefault();
        const payload = {
            product_code: document.getElementById('m-prod-code').value.trim(),
            barcode: document.getElementById('m-barcode').value.trim() || null,
            product_name: document.getElementById('m-prod-name').value.trim(),
            category_id: parseInt(document.getElementById('m-category').value, 10),
            unit_id: parseInt(document.getElementById('m-unit').value, 10),
            cost_price: parseFloat(document.getElementById('m-cost').value),
            selling_price: parseFloat(document.getElementById('m-price').value),
            current_stock: parseFloat(document.getElementById('m-stock').value),
            minimum_stock: parseFloat(document.getElementById('m-min').value),
            reorder_level: parseFloat(document.getElementById('m-reorder').value),
            expiration_tracking: document.getElementById('m-exp-track').checked,
            is_recipe_item: document.getElementById('m-is-recipe').checked,
            is_ingredient: document.getElementById('m-is-ingredient').checked
        };

        try {
            const res = await fetch('/api/products', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                App.notify('Product created successfully.');
                this.closeModal();
                this.loadProducts();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (err) {
            alert(`Save error: ${err.message}`);
        }
    },

    closeModal() {
        document.getElementById('modal-container').innerHTML = '';
    }
};

/* ==============================================================================
   INVENTORY CONTROLLER (Stock-In, Stock-Out, Adjustments, Batches)
   ============================================================================== */
const InventoryUI = {
    async initStockIn() {
        await this.populateProductSelects('stockin-product');
        this.loadStockInHistory();
    },

    async initStockOut() {
        await this.populateProductSelects('stockout-product');
        this.loadStockOutHistory();
    },

    async populateProductSelects(selectId) {
        const res = await fetch('/api/products', { headers: App.getHeaders() });
        const data = await res.json();
        const select = document.getElementById(selectId);
        if (select) {
            select.innerHTML = '<option value="">-- Choose Product --</option>';
            data.products.forEach(p => {
                select.innerHTML += `<option value="${p.id}">${p.product_code} - ${p.product_name} (Current: ${p.current_stock} ${p.unit_abbr})</option>`;
            });
        }
    },

    async handleStockIn(e) {
        e.preventDefault();
        const payload = {
            product_id: parseInt(document.getElementById('stockin-product').value, 10),
            quantity: parseFloat(document.getElementById('stockin-quantity').value),
            unit_cost: parseFloat(document.getElementById('stockin-cost').value),
            batch_number: document.getElementById('stockin-batch').value.trim() || null,
            expiration_date: document.getElementById('stockin-expiration').value || null,
            reference_type: 'MANUAL_RECEIPT',
            reference_id: document.getElementById('stockin-reference').value.trim() || null,
            remarks: document.getElementById('stockin-remarks').value.trim()
        };

        try {
            const res = await fetch('/api/inventory/stock-in', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                App.notify(`Stock-in recorded. New balance: ${data.new_stock}`);
                document.getElementById('form-stock-in').reset();
                this.loadStockInHistory();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (err) {
            alert(err.message);
        }
    },

    async handleStockOut(e) {
        e.preventDefault();
        const payload = {
            product_id: parseInt(document.getElementById('stockout-product').value, 10),
            quantity: parseFloat(document.getElementById('stockout-quantity').value),
            reason: document.getElementById('stockout-reason').value,
            remarks: document.getElementById('stockout-remarks').value.trim(),
            reference_type: 'DISPATCH'
        };

        try {
            const res = await fetch('/api/inventory/stock-out', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                App.notify(`Stock-out recorded. Remaining balance: ${data.new_stock}`);
                document.getElementById('form-stock-out').reset();
                this.loadStockOutHistory();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (err) {
            alert(err.message);
        }
    },

    async loadStockInHistory() {
        const res = await fetch('/api/reports/stock-in', { headers: App.getHeaders() });
        const data = await res.json();
        const tbody = document.querySelector('#table-stockin-history tbody');
        tbody.innerHTML = '';
        (data.rows || []).slice(0, 15).forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><code>${t.transaction_number}</code></td>
                <td><strong>${t.product_name}</strong></td>
                <td style="font-weight:700; color:var(--color-success-text);">+${t.quantity}</td>
                <td>${t.new_stock}</td>
                <td>${t.reference_id || '-'}</td>
                <td>${t.employee_id}</td>
                <td style="font-size:0.75rem; color:var(--color-text-muted);">${new Date(t.created_at).toLocaleDateString()}</td>
            `;
            tbody.appendChild(tr);
        });
    },

    async loadStockOutHistory() {
        const res = await fetch('/api/reports/stock-out', { headers: App.getHeaders() });
        const data = await res.json();
        const tbody = document.querySelector('#table-stockout-history tbody');
        tbody.innerHTML = '';
        (data.rows || []).slice(0, 15).forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><code>${t.transaction_number}</code></td>
                <td><strong>${t.product_name}</strong></td>
                <td style="font-weight:700; color:var(--color-danger-text);">-${t.quantity}</td>
                <td><span class="badge badge-secondary">${t.transaction_type}</span></td>
                <td>${t.remarks}</td>
                <td>${t.employee_id}</td>
                <td style="font-size:0.75rem; color:var(--color-text-muted);">${new Date(t.created_at).toLocaleDateString()}</td>
            `;
            tbody.appendChild(tr);
        });
    },

    async loadBatches() {
        const res = await fetch('/api/reports/expiration', { headers: App.getHeaders() });
        const data = await res.json();
        const tbody = document.querySelector('#table-batches tbody');
        tbody.innerHTML = '';
        (data.rows || []).forEach(b => {
            const tr = document.createElement('tr');
            let riskBadge = 'badge-success';
            if (b.expiration_risk.includes('EXPIRED')) riskBadge = 'badge-danger';
            else if (b.expiration_risk.includes('3_DAYS') || b.expiration_risk.includes('7_DAYS')) riskBadge = 'badge-warning';

            tr.innerHTML = `
                <td><code>${b.batch_number}</code></td>
                <td><strong>${b.product_name}</strong></td>
                <td>${b.category_name}</td>
                <td style="font-weight: 700;">${b.remaining_quantity}</td>
                <td>${b.expiration_date}</td>
                <td>${b.days_until_expiration} days</td>
                <td><span class="badge ${riskBadge}">${b.expiration_risk}</span></td>
            `;
            tbody.appendChild(tr);
        });
    },

    async loadAdjustments() {
        const res = await fetch('/api/inventory/adjustments', { headers: App.getHeaders() });
        const data = await res.json();
        const tbody = document.querySelector('#table-adjustments tbody');
        tbody.innerHTML = '';
        (data.adjustments || []).forEach(a => {
            const tr = document.createElement('tr');
            const canApprove = App.currentUser.role_id <= 2 && a.status === 'PENDING';
            tr.innerHTML = `
                <td><code>${a.adjustment_number}</code></td>
                <td><strong>${a.product_name}</strong></td>
                <td>${a.system_quantity} ${a.unit}</td>
                <td>${a.actual_quantity} ${a.unit}</td>
                <td style="font-weight: 700;">${a.difference > 0 ? '+' : ''}${a.difference}</td>
                <td>${a.reason}</td>
                <td>${a.requested_by}</td>
                <td><span class="badge ${a.status === 'APPROVED' ? 'badge-success' : 'badge-warning'}">${a.status}</span></td>
                <td>
                    ${canApprove ? `<button class="btn btn-primary btn-sm" onclick="InventoryUI.approveAdj(${a.id})"><i data-lucide="check-circle-2"></i> Approve</button>` : '-'}
                </td>
            `;
            tbody.appendChild(tr);
        });

        App.refreshIcons();
    },

    openAdjustmentModal() {
        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="sliders-horizontal" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Request Stock Adjustment</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="InventoryUI.submitAdjustment(event)">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Product</label>
                                <select id="adj-m-product" class="form-control" required></select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Physical Count (Actual Stock)</label>
                                <input type="number" step="0.01" id="adj-m-actual" class="form-control" required min="0">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Reason for Discrepancy</label>
                                <textarea id="adj-m-reason" class="form-control" rows="3" required placeholder="Describe discrepancy findings..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        this.populateProductSelects('adj-m-product');
        App.refreshIcons();
    },

    async submitAdjustment(e) {
        e.preventDefault();
        const payload = {
            product_id: parseInt(document.getElementById('adj-m-product').value, 10),
            actual_quantity: parseFloat(document.getElementById('adj-m-actual').value),
            reason: document.getElementById('adj-m-reason').value.trim()
        };

        const res = await fetch('/api/inventory/adjustments', {
            method: 'POST',
            headers: App.getHeaders(),
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            App.notify('Adjustment request submitted.');
            ProductsUI.closeModal();
            this.loadAdjustments();
        } else {
            alert(`Error: ${data.error}`);
        }
    },

    async approveAdj(id) {
        if (!confirm('Authorize this inventory adjustment and update current stock balance?')) return;
        const res = await fetch(`/api/inventory/adjustments/${id}/approve`, {
            method: 'POST',
            headers: App.getHeaders()
        });
        const data = await res.json();
        if (data.success) {
            App.notify('Adjustment approved and applied.');
            this.loadAdjustments();
        } else {
            alert(`Error: ${data.error}`);
        }
    }
};

/* ==============================================================================
   RECIPE & BOM BUILDER CONTROLLER
   ============================================================================== */
const RecipesUI = {
    async loadRecipes() {
        const res = await fetch('/api/recipes', { headers: App.getHeaders() });
        const data = await res.json();
        const container = document.getElementById('recipes-cards-container');
        container.innerHTML = '';

        (data.recipes || []).forEach(r => {
            const card = document.createElement('div');
            card.className = 'card';
            card.innerHTML = `
                <div class="card-header">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--color-text-main); display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="chef-hat" style="width: 16px; height: 16px; color: var(--color-accent);"></i>
                            ${r.product_name}
                        </div>
                        <div style="font-size: 0.72rem; color: var(--color-text-muted);">Formula: ${r.recipe_name}</div>
                    </div>
                    <span class="badge badge-success">Selling: $${parseFloat(r.selling_price).toFixed(2)}</span>
                </div>
                <div class="card-body">
                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--color-text-muted); margin-bottom: 0.4rem; text-transform: uppercase;">
                        Required Ingredients (Bill of Materials):
                    </div>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.825rem;">
                        ${(r.ingredients || []).map(ing => `
                            <li style="display: flex; justify-content: space-between; padding: 0.25rem 0; border-bottom: 1px solid var(--color-border-subtle);">
                                <span>${ing.ingredient_name}</span>
                                <strong>${ing.quantity_required} ${ing.unit}</strong>
                            </li>
                        `).join('')}
                    </ul>
                    <div style="margin-top: 0.85rem; padding-top: 0.65rem; border-top: 1px solid var(--color-border); display: flex; justify-content: space-between; font-size: 0.825rem;">
                        <span style="color: var(--color-text-muted);">Estimated Ingredient Cost:</span>
                        <strong style="color: var(--color-text-main);">$${r.estimated_cost.toFixed(2)}</strong>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        App.refreshIcons();
    },

    openNewModal() {
        alert('Recipe Builder: Finished recipes can be authored via the REST API (/api/recipes) or database migrations.');
    }
};

/* ==============================================================================
   SUPPLIERS & PURCHASE ORDERS
   ============================================================================== */
const SuppliersUI = {
    async loadSuppliers() {
        const res = await fetch('/api/suppliers', { headers: App.getHeaders() });
        const data = await res.json();
        const tbody = document.querySelector('#table-suppliers tbody');
        tbody.innerHTML = '';
        (data.suppliers || []).forEach(s => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><code>${s.supplier_code}</code></td>
                <td><strong>${s.supplier_name}</strong></td>
                <td>${s.contact_person || '-'}</td>
                <td>${s.contact_number || '-'}</td>
                <td>${s.email || '-'}</td>
                <td><small>${s.address || '-'}</small></td>
                <td><span class="badge ${s.status === 'ACTIVE' ? 'badge-success' : 'badge-secondary'}">${s.status}</span></td>
                <td><button class="btn btn-secondary btn-sm" onclick="alert('Supplier profile: ${s.supplier_name}\\nEmail: ${s.email || \'N/A\'}\\nPhone: ${s.contact_number || \'N/A\'}')"><i data-lucide="eye"></i> Details</button></td>
            `;
            tbody.appendChild(tr);
        });

        App.refreshIcons();
    },

    openNewModal() {
        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="truck" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Add New Supplier</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="SuppliersUI.handleSave(event)">
                        <div class="modal-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Supplier Code *</label>
                                    <input type="text" id="sup-code" class="form-control" required placeholder="e.g. SUP-004">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Supplier Name *</label>
                                    <input type="text" id="sup-name" class="form-control" required placeholder="e.g. Apex Farm & Dairy Supplies">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" id="sup-contact" class="form-control" placeholder="e.g. Robert Smith">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="text" id="sup-phone" class="form-control" placeholder="e.g. +1 555-0199">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" id="sup-email" class="form-control" placeholder="e.g. sales@apexfarm.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <input type="text" id="sup-address" class="form-control" placeholder="e.g. 100 Industrial Parkway, Suite 4">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Save Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    async handleSave(e) {
        e.preventDefault();
        const payload = {
            supplier_code: document.getElementById('sup-code').value.trim(),
            supplier_name: document.getElementById('sup-name').value.trim(),
            contact_person: document.getElementById('sup-contact').value.trim() || null,
            contact_number: document.getElementById('sup-phone').value.trim() || null,
            email: document.getElementById('sup-email').value.trim() || null,
            address: document.getElementById('sup-address').value.trim() || null
        };

        try {
            const res = await fetch('/api/suppliers', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                App.notify('Supplier registered successfully.');
                ProductsUI.closeModal();
                this.loadSuppliers();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (err) {
            alert(`Save error: ${err.message}`);
        }
    }
};

const PurchaseOrdersUI = {
    async loadOrders() {
        const res = await fetch('/api/purchase-orders', { headers: App.getHeaders() });
        const data = await res.json();
        const tbody = document.querySelector('#table-purchase-orders tbody');
        tbody.innerHTML = '';
        (data.orders || []).forEach(po => {
            const tr = document.createElement('tr');
            let badgeClass = 'badge-secondary';
            if (po.status === 'COMPLETED') badgeClass = 'badge-success';
            else if (po.status === 'APPROVED') badgeClass = 'badge-info';
            else if (po.status === 'PENDING APPROVAL') badgeClass = 'badge-warning';

            const canApprove = App.currentUser.role_id <= 2 && po.status === 'PENDING APPROVAL';
            const canReceive = App.currentUser.role_id <= 3 && ['APPROVED', 'ORDERED', 'PARTIALLY RECEIVED'].includes(po.status);

            tr.innerHTML = `
                <td><strong>${po.purchase_order_number}</strong></td>
                <td>${po.supplier_name}</td>
                <td>${po.requested_by}</td>
                <td>${po.approved_by || '-'}</td>
                <td>${po.order_date}</td>
                <td><strong>$${parseFloat(po.total_amount).toFixed(2)}</strong></td>
                <td><span class="badge ${badgeClass}">${po.status}</span></td>
                <td>
                    ${canApprove ? `<button class="btn btn-primary btn-sm" onclick="PurchaseOrdersUI.approvePO(${po.id})"><i data-lucide="check-circle-2"></i> Approve</button>` : ''}
                    ${canReceive ? `<button class="btn btn-secondary btn-sm" onclick="PurchaseOrdersUI.openReceiveModal(${po.id})"><i data-lucide="package-check"></i> Receive</button>` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });

        App.refreshIcons();
    },

    async approvePO(id) {
        if (!confirm('Approve this purchase order?')) return;
        const res = await fetch(`/api/purchase-orders/${id}/approve`, {
            method: 'POST',
            headers: App.getHeaders()
        });
        const data = await res.json();
        if (data.success) {
            App.notify('Purchase Order approved.');
            this.loadOrders();
        }
    },

    async openReceiveModal(id) {
        const res = await fetch(`/api/purchase-orders/${id}`, { headers: App.getHeaders() });
        const data = await res.json();
        const po = data.purchase_order;

        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog" style="max-width: 640px;">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="package-check" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Receive Deliveries: ${po.purchase_order_number}</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="PurchaseOrdersUI.submitDelivery(event, ${po.id})">
                        <div class="modal-body">
                            <p style="color: var(--color-text-muted); font-size: 0.825rem; margin-bottom: 0.85rem;">
                                Enter inspected quantities and batch expiration dates. Stock balances and Weighted Average Cost will recalculate upon confirmation.
                            </p>
                            ${(po.items || []).map((it, idx) => `
                                <div style="background-color: #f8fafc; border: 1px solid var(--color-border); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 0.65rem;">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;">${it.product_name} (${it.product_code})</div>
                                    <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-bottom: 0.4rem;">
                                        Ordered: <strong>${it.quantity_ordered} ${it.unit}</strong> | Received to Date: <strong>${it.quantity_received}</strong>
                                    </div>
                                    <input type="hidden" class="rx-poi-id" value="${it.id}">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                                        <div>
                                            <label style="font-size: 0.72rem; color: var(--color-text-muted);">Receiving Qty</label>
                                            <input type="number" step="0.01" class="form-control rx-qty" value="${Math.max(0, it.quantity_ordered - it.quantity_received)}">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.72rem; color: var(--color-text-muted);">Batch Number</label>
                                            <input type="text" class="form-control rx-batch" placeholder="BAT-${po.purchase_order_number}-${idx+1}">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.72rem; color: var(--color-text-muted);">Expiration Date</label>
                                            <input type="date" class="form-control rx-exp">
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Process Delivery</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    async submitDelivery(e, poId) {
        e.preventDefault();
        const items = [];
        const poiCards = document.querySelectorAll('.rx-poi-id');
        poiCards.forEach((el, idx) => {
            const poiId = parseInt(el.value, 10);
            const qty = parseFloat(document.querySelectorAll('.rx-qty')[idx].value || 0);
            const batch = document.querySelectorAll('.rx-batch')[idx].value.trim();
            const exp = document.querySelectorAll('.rx-exp')[idx].value;

            if (qty > 0) {
                items.push({
                    po_item_id: poiId,
                    quantity_receiving: qty,
                    batch_number: batch || null,
                    expiration_date: exp || null
                });
            }
        });

        if (items.length === 0) {
            alert('Specify at least one quantity to receive.');
            return;
        }

        const res = await fetch(`/api/purchase-orders/${poId}/receive`, {
            method: 'POST',
            headers: App.getHeaders(),
            body: JSON.stringify({ received_items: items })
        });
        const data = await res.json();
        if (data.success) {
            App.notify(data.message);
            ProductsUI.closeModal();
            this.loadOrders();
        } else {
            alert(`Error: ${data.error}`);
        }
    },

    async openNewModal() {
        const [supRes, prodRes] = await Promise.all([
            fetch('/api/suppliers', { headers: App.getHeaders() }).then(r => r.json()),
            fetch('/api/products', { headers: App.getHeaders() }).then(r => r.json())
        ]);

        const suppliers = supRes.suppliers || [];
        const products = prodRes.products || [];

        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog" style="max-width: 600px;">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="clipboard-list" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Create Purchase Order</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="PurchaseOrdersUI.handleSave(event)">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Supplier *</label>
                                <select id="po-supplier" class="form-control" required>
                                    ${suppliers.map(s => `<option value="${s.id}">${s.supplier_name} (${s.supplier_code})</option>`).join('')}
                                </select>
                            </div>
                            <div style="font-weight: 600; font-size: 0.8rem; margin: 0.75rem 0 0.35rem 0;">Primary Ordered Item:</div>
                            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 0.5rem;">
                                <div>
                                    <label class="form-label">Product *</label>
                                    <select id="po-prod-id" class="form-control" required>
                                        ${products.map(p => `<option value="${p.id}" data-cost="${p.cost_price}">${p.product_name} ($${p.cost_price})</option>`).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" step="0.01" id="po-qty" class="form-control" required value="50" min="0.1">
                                </div>
                                <div>
                                    <label class="form-label">Unit Cost ($) *</label>
                                    <input type="number" step="0.0001" id="po-cost" class="form-control" required value="2.50" min="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Submit Purchase Order</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    async handleSave(e) {
        e.preventDefault();
        const supplierId = parseInt(document.getElementById('po-supplier').value, 10);
        const prodId = parseInt(document.getElementById('po-prod-id').value, 10);
        const qty = parseFloat(document.getElementById('po-qty').value);
        const cost = parseFloat(document.getElementById('po-cost').value);

        const payload = {
            supplier_id: supplierId,
            items: [{
                product_id: prodId,
                quantity: qty,
                unit_price: cost
            }]
        };

        try {
            const res = await fetch('/api/purchase-orders', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                App.notify('Purchase Order created.');
                ProductsUI.closeModal();
                this.loadOrders();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (err) {
            alert(`PO create error: ${err.message}`);
        }
    }
};

/* ==============================================================================
   CANTEEN CASHIER POS CONTROLLER
   ============================================================================== */
const PosUI = {
    catalog: [],
    cart: [],
    activeCategory: 'ALL',

    init() {
        document.getElementById('pos-cashier-label').textContent = App.currentUser.full_name;
        document.getElementById('pos-txn-id').textContent = String(Math.floor(Math.random() * 9000) + 1000);
        this.loadCatalog();
    },

    async loadCatalog() {
        try {
            const res = await fetch('/api/integration/canteen/product-availability');
            const data = await res.json();
            this.catalog = data.products || [];
            this.renderMenu();
        } catch (e) {
            console.error('[POS Error]', e);
        }
    },

    filterCategory(cat) {
        this.activeCategory = cat;
        document.querySelectorAll('.pos-cat-btn').forEach(btn => {
            if (btn.getAttribute('data-cat') === cat) {
                btn.className = 'btn btn-sm btn-primary pos-cat-btn';
            } else {
                btn.className = 'btn btn-sm btn-secondary pos-cat-btn';
            }
        });
        this.renderMenu();
    },

    renderMenu() {
        const grid = document.getElementById('pos-items-grid');
        grid.innerHTML = '';

        const filtered = this.catalog.filter(p => {
            if (this.activeCategory === 'ALL') return true;
            return p.category_name === this.activeCategory;
        });

        filtered.forEach(p => {
            const card = document.createElement('div');
            const isOutOfStock = p.availability_status === 'OUT_OF_STOCK';
            let pillClass = 'badge-success';
            if (p.availability_status === 'LOW_STOCK') pillClass = 'badge-warning';
            if (isOutOfStock) pillClass = 'badge-danger';

            card.className = `pos-card ${isOutOfStock ? 'out-of-stock' : ''}`;
            card.innerHTML = `
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
                        <span class="badge ${pillClass}">${p.availability_status}</span>
                        ${p.is_recipe_item ? '<span class="badge badge-secondary"><i data-lucide="chef-hat" style="width:11px;height:11px;margin-right:2px;"></i> Recipe</span>' : ''}
                    </div>
                    <div class="pos-card-title">${p.product_name}</div>
                    <div class="pos-card-category">${p.category_name}</div>
                </div>
                <div style="margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: baseline;">
                    <div class="pos-card-price">$${parseFloat(p.selling_price).toFixed(2)}</div>
                    <div class="pos-card-stock">Stock: ${p.current_stock}</div>
                </div>
            `;

            if (!isOutOfStock) {
                card.addEventListener('click', () => this.addToCart(p));
            }

            grid.appendChild(card);
        });

        App.refreshIcons();
    },

    addToCart(product) {
        const existing = this.cart.find(it => it.product_id === product.product_id);
        if (existing) {
            existing.quantity += 1;
        } else {
            this.cart.push({
                product_id: product.product_id,
                product_name: product.product_name,
                unit_price: parseFloat(product.selling_price),
                quantity: 1
            });
        }
        this.renderCart();
    },

    modifyQty(productId, delta) {
        const item = this.cart.find(it => it.product_id === productId);
        if (!item) return;
        item.quantity += delta;
        if (item.quantity <= 0) {
            this.cart = this.cart.filter(it => it.product_id !== productId);
        }
        this.renderCart();
    },

    clearCart() {
        this.cart = [];
        this.renderCart();
    },

    renderCart() {
        const container = document.getElementById('pos-cart-items');
        if (this.cart.length === 0) {
            container.innerHTML = `<div style="text-align: center; color: var(--color-text-subtle); padding: 3rem 1rem; font-size: 0.825rem;">Select products from the catalog to build an order.</div>`;
            document.getElementById('pos-subtotal').textContent = '$0.00';
            document.getElementById('pos-total').textContent = '$0.00';
            return;
        }

        container.innerHTML = '';
        let total = 0;

        this.cart.forEach(it => {
            const itemTotal = it.quantity * it.unit_price;
            total += itemTotal;

            const div = document.createElement('div');
            div.style.display = 'flex';
            div.style.justifyContent = 'space-between';
            div.style.alignItems = 'center';
            div.style.padding = '0.45rem 0';
            div.style.borderBottom = '1px solid var(--color-border-subtle)';

            div.innerHTML = `
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 0.825rem; color: var(--color-text-main);">${it.product_name}</div>
                    <div style="font-size: 0.72rem; color: var(--color-text-muted);">$${it.unit_price.toFixed(2)} each</div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <button class="btn btn-secondary btn-sm" onclick="PosUI.modifyQty(${it.product_id}, -1)"><i data-lucide="minus"></i></button>
                    <span style="font-weight: 700; min-width: 18px; text-align: center; font-size: 0.825rem;">${it.quantity}</span>
                    <button class="btn btn-secondary btn-sm" onclick="PosUI.modifyQty(${it.product_id}, 1)"><i data-lucide="plus"></i></button>
                    <div style="font-weight: 600; width: 55px; text-align: right; font-size: 0.825rem;">$${itemTotal.toFixed(2)}</div>
                </div>
            `;
            container.appendChild(div);
        });

        document.getElementById('pos-subtotal').textContent = `$${total.toFixed(2)}`;
        document.getElementById('pos-total').textContent = `$${total.toFixed(2)}`;
        App.refreshIcons();
    },

    async processCheckout() {
        if (this.cart.length === 0) {
            alert('Cart is empty. Select items to process.');
            return;
        }

        const txnId = `POS-${document.getElementById('pos-txn-id').textContent}`;
        const total = parseFloat(document.getElementById('pos-total').textContent.replace('$', ''));

        const payload = {
            transaction_id: txnId,
            cashier_employee_id: App.currentUser.employee_id,
            idempotency_key: `POS_SALE_${txnId}_${Date.now()}`,
            items: this.cart.map(c => ({
                product_id: c.product_id,
                quantity: c.quantity,
                unit_price: c.unit_price
            })),
            payment_method: 'CASH',
            total_amount: total
        };

        const btn = document.getElementById('btn-process-sale');
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="refresh-cw"></i> Processing...`;
        App.refreshIcons();

        try {
            const res = await fetch('/api/integration/canteen/sales', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.status === 'SUCCESS') {
                this.showSaleSuccessModal(txnId, total, data.deductions);
                this.clearCart();
                document.getElementById('pos-txn-id').textContent = String(Math.floor(Math.random() * 9000) + 1000);
                this.loadCatalog();
            } else {
                alert(`POS Sale Failed: ${data.error}`);
            }
        } catch (err) {
            alert(`Network/POS error: ${err.message}`);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="credit-card"></i> Complete Sale`;
            App.refreshIcons();
        }
    },

    showSaleSuccessModal(txnId, total, deductions = []) {
        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <div>
                            <h3 class="card-title" style="color: #166534;"><i data-lucide="check-circle-2" style="margin-right:6px;width:18px;height:18px;color:#16a34a;"></i> Sale Completed: ${txnId}</h3>
                            <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 2px;">
                                Total: $${total.toFixed(2)} | Cashier: ${App.currentUser.full_name}
                            </div>
                        </div>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <div class="modal-body">
                        <div style="font-weight: 600; font-size: 0.825rem; color: var(--color-text-main); margin-bottom: 0.5rem;">
                            Inventory & Ingredient Deductions:
                        </div>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem;">
                            ${deductions.map(d => `
                                <li style="display: flex; justify-content: space-between; padding: 0.45rem 0.65rem; background-color: #f8fafc; border: 1px solid var(--color-border); border-radius: var(--radius-xs);">
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.825rem;">${d.product_name}</div>
                                        <div style="font-size: 0.72rem; color: var(--color-text-muted);">${d.type === 'INGREDIENT' ? 'Recipe Ingredient' : 'Direct Stock'}</div>
                                    </div>
                                    <div style="text-align: right;">
                                        <strong style="color: var(--color-danger-text); font-size: 0.85rem;">-${d.deducted_quantity} ${d.unit || 'pcs'}</strong>
                                        <div style="font-size: 0.72rem; color: var(--color-text-muted);">Remaining: ${d.remaining_stock}</div>
                                    </div>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" onclick="ProductsUI.closeModal()"><i data-lucide="check"></i> Done</button>
                    </div>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    openRefundModal() {
        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="undo-2" style="margin-right:6px;width:18px;height:18px;color:var(--color-danger-text);"></i> POS Refund & Return</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="PosUI.submitRefund(event)">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Transaction ID *</label>
                                <input type="text" id="refund-txnid" class="form-control" required placeholder="e.g. POS-1001">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Reason *</label>
                                <input type="text" id="refund-reason" class="form-control" required placeholder="e.g. Customer returned sealed item">
                            </div>
                            <div class="form-group" style="margin-top: 0.75rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; font-weight: 500; cursor: pointer;">
                                    <input type="checkbox" id="refund-restock" checked>
                                    Physically return and restock inventory into storage
                                </label>
                                <small style="color: var(--color-text-muted); display: block; margin-top: 0.25rem;">
                                    If unchecked, refund will be processed financially without restoring perishable food.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-danger"><i data-lucide="check"></i> Confirm Refund</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    async submitRefund(e) {
        e.preventDefault();
        const payload = {
            transaction_id: document.getElementById('refund-txnid').value.trim(),
            cashier_employee_id: App.currentUser.employee_id,
            reason: document.getElementById('refund-reason').value.trim(),
            restock_inventory: document.getElementById('refund-restock').checked
        };

        const res = await fetch('/api/integration/canteen/refund', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            App.notify(data.message);
            ProductsUI.closeModal();
            this.loadCatalog();
        } else {
            alert(`Refund Error: ${data.error}`);
        }
    }
};

/* ==============================================================================
   EMPLOYEE MANAGEMENT & EMS INTEGRATION CONTROLLER
   ============================================================================== */
const EmployeesUI = {
    async loadEmployees() {
        const res = await fetch('/api/integration/employees', { headers: App.getHeaders() });
        const data = await res.json();
        const tbody = document.querySelector('#table-employees tbody');
        tbody.innerHTML = '';

        (data.employees || []).forEach(emp => {
            const tr = document.createElement('tr');
            const isActive = emp.account_status === 'ACTIVE';

            tr.innerHTML = `
                <td><code>${emp.employee_id}</code></td>
                <td><strong>${emp.full_name}</strong></td>
                <td>${emp.username}</td>
                <td>${emp.department_name || '-'}</td>
                <td><span class="badge badge-secondary">${emp.role_name}</span></td>
                <td>${emp.email || '-'}</td>
                <td>
                    <span class="badge ${isActive ? 'badge-success' : 'badge-danger'}">
                        ${emp.account_status}
                    </span>
                </td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="EmployeesUI.viewActivities('${emp.employee_id}')"><i data-lucide="history"></i> Audit</button>
                    <button class="btn btn-secondary btn-sm" onclick="EmployeesUI.toggleStatus('${emp.employee_id}', '${isActive ? 'INACTIVE' : 'ACTIVE'}')">
                        <i data-lucide="${isActive ? 'user-x' : 'user-check'}"></i> ${isActive ? 'Deactivate' : 'Activate'}
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        App.refreshIcons();
    },

    async toggleStatus(employeeId, newStatus) {
        if (!confirm(`Change account status of ${employeeId} to ${newStatus}?`)) return;
        const res = await fetch('/api/integration/employees/sync', {
            method: 'POST',
            headers: App.getHeaders(),
            body: JSON.stringify({ employee_id: employeeId, account_status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            App.notify(`Employee ${employeeId} status set to ${newStatus}.`);
            this.loadEmployees();
        } else {
            alert(`Error: ${data.error}`);
        }
    },

    async viewActivities(employeeId) {
        const res = await fetch(`/api/integration/employees/${employeeId}`, { headers: App.getHeaders() });
        const data = await res.json();
        const emp = data.employee;

        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog" style="max-width: 720px;">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="history" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Employee Accountability: ${emp.full_name} (${emp.employee_id})</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <div class="modal-body">
                        <div style="font-weight: 600; font-size: 0.825rem; margin-bottom: 0.5rem;">Associated Inventory Transactions (${data.transactions.length}):</div>
                        <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Transaction</th>
                                        <th>Type</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.transactions.length === 0 ? '<tr><td colspan="6" style="text-align:center; color: var(--color-text-subtle);">No transactions recorded.</td></tr>' : ''}
                                    ${data.transactions.map(t => `
                                        <tr>
                                            <td><code>${t.transaction_number}</code></td>
                                            <td><span class="badge ${t.quantity > 0 ? 'badge-success' : 'badge-danger'}">${t.transaction_type}</span></td>
                                            <td>${t.product_name}</td>
                                            <td>${t.quantity}</td>
                                            <td>${t.reference_id || '-'}</td>
                                            <td>${new Date(t.created_at).toLocaleString()}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" onclick="ProductsUI.closeModal()"><i data-lucide="check"></i> Done</button>
                    </div>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    openSyncModal() {
        const modal = document.getElementById('modal-container');
        modal.innerHTML = `
            <div class="modal-backdrop">
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h3 class="card-title"><i data-lucide="refresh-cw" style="margin-right:6px;width:18px;height:18px;color:var(--color-accent);"></i> Synchronize Employee from EMS</h3>
                        <button class="btn btn-secondary btn-sm" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i></button>
                    </div>
                    <form onsubmit="EmployeesUI.submitSync(event)">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Employee ID *</label>
                                <input type="text" id="sync-empid" class="form-control" required placeholder="e.g. EMP-099">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" id="sync-name" class="form-control" required placeholder="e.g. Alexander Stone">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Department *</label>
                                <input type="text" id="sync-dept" class="form-control" required placeholder="Food & Beverage (Canteen)">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Role Assignment *</label>
                                <select id="sync-role" class="form-control" required>
                                    <option value="Employee">Employee (Self-Service)</option>
                                    <option value="Canteen Cashier">Canteen Cashier</option>
                                    <option value="Inventory Staff">Inventory Staff</option>
                                    <option value="Inventory Manager">Inventory Manager</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" id="sync-email" class="form-control" placeholder="alex.stone@canteen-ims.local">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Employment Status *</label>
                                <select id="sync-status" class="form-control" required>
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                    <option value="SUSPENDED">SUSPENDED</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="ProductsUI.closeModal()"><i data-lucide="x"></i> Cancel</button>
                            <button type="submit" class="btn btn-primary"><i data-lucide="check"></i> Synchronize Account</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        App.refreshIcons();
    },

    async submitSync(e) {
        e.preventDefault();
        const payload = {
            employee_id: document.getElementById('sync-empid').value.trim(),
            full_name: document.getElementById('sync-name').value.trim(),
            department_name: document.getElementById('sync-dept').value.trim(),
            role_name: document.getElementById('sync-role').value,
            email: document.getElementById('sync-email').value.trim() || null,
            account_status: document.getElementById('sync-status').value
        };

        const res = await fetch('/api/integration/employees/sync', {
            method: 'POST',
            headers: App.getHeaders(),
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            App.notify(data.message);
            ProductsUI.closeModal();
            this.loadEmployees();
        } else {
            alert(`Sync error: ${data.error}`);
        }
    }
};

/* ==============================================================================
   REPORTS & ANALYTICS CONTROLLER (15 Reports & Minimal Exporter)
   ============================================================================== */
const ReportsUI = {
    currentReportData: [],
    currentReportType: 'current-inventory',

    async loadActiveReport() {
        const type = document.getElementById('report-type-select').value;
        this.currentReportType = type;
        const wrapper = document.getElementById('report-table-wrapper');
        wrapper.innerHTML = `<div style="text-align: center; color: var(--color-text-subtle); padding: 2rem;">Loading report data...</div>`;

        try {
            const res = await fetch(`/api/reports/${type}`, { headers: App.getHeaders() });
            const data = await res.json();
            const rows = data.rows || data.items || [];
            this.currentReportData = rows;
            this.renderReportTable(type, rows, data);
        } catch (e) {
            wrapper.innerHTML = `<div style="color: var(--color-danger-text); padding: 1rem;">Failed to load report: ${e.message}</div>`;
        }
    },

    renderReportTable(type, rows, fullData) {
        const wrapper = document.getElementById('report-table-wrapper');
        if (rows.length === 0) {
            wrapper.innerHTML = `<div style="text-align: center; color: var(--color-text-subtle); padding: 2rem;">No records found for this report.</div>`;
            return;
        }

        const keys = Object.keys(rows[0]);
        let summaryBanner = '';
        if (type === 'valuation') {
            summaryBanner = `
                <div style="background-color: var(--color-success-bg); border: 1px solid var(--color-success-border); padding: 0.65rem 1rem; border-radius: var(--radius-sm); margin-bottom: 0.85rem; display: flex; justify-content: space-between; font-size: 0.825rem;">
                    <div><strong>Valuation Methodology:</strong> ${fullData.valuation_method}</div>
                    <div><strong>Total Valuation:</strong> <span style="font-weight: 700; color: var(--color-success-text);">$${fullData.total_valuation.toLocaleString()}</span></div>
                </div>
            `;
        }

        let tableHtml = `
            ${summaryBanner}
            <table class="table">
                <thead>
                    <tr>
                        ${keys.map(k => `<th>${k.replace(/_/g, ' ').toUpperCase()}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${rows.map(r => `
                        <tr>
                            ${keys.map(k => `<td>${r[k] !== null && r[k] !== undefined ? r[k] : '-'}</td>`).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        wrapper.innerHTML = tableHtml;
    },

    exportCSV() {
        if (!this.currentReportData || this.currentReportData.length === 0) {
            alert('No data available to export.');
            return;
        }
        const keys = Object.keys(this.currentReportData[0]);
        let csvContent = 'data:text/csv;charset=utf-8,';
        csvContent += keys.join(',') + '\r\n';

        this.currentReportData.forEach(row => {
            const line = keys.map(k => {
                let cell = row[k] === null || row[k] === undefined ? '' : String(row[k]);
                if (cell.includes(',') || cell.includes('\n')) {
                    cell = `"${cell.replace(/"/g, '""')}"`;
                }
                return cell;
            }).join(',');
            csvContent += line + '\r\n';
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `${this.currentReportType}_report_${Date.now()}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
