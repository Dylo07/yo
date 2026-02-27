@extends('layouts.app')

@section('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .staff-card {
        cursor: grab;
        transition: all 0.2s;
    }
    .staff-card:active {
        cursor: grabbing;
    }
    .staff-card.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    .section-box {
        transition: all 0.2s;
    }
    .section-box.drag-over {
        transform: scale(1.03);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.6), 0 0 20px rgba(59, 130, 246, 0.3);
    }
    .map-container {
        position: relative;
        width: 100%;
        max-width: 1400px;
        aspect-ratio: 1400 / 900;
        margin: 0 auto;
    }
    .sidebar-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .sidebar-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .staff-name-tag {
        font-size: 9px;
        padding: 2px 6px;
        background: rgba(255,255,255,0.95);
        border-radius: 4px;
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @keyframes pulse-wedding {
        0%, 100% {
            box-shadow: 0 0 0 4px #ef4444, 0 0 20px #ef444490;
        }
        50% {
            box-shadow: 0 0 0 6px #ef4444, 0 0 30px #ef4444;
        }
    }
    @keyframes pulse-turnover {
        0%, 100% {
            outline-color: #f59e0b;
            outline-offset: 6px;
        }
        50% {
            outline-color: #fbbf24;
            outline-offset: 8px;
        }
    }
    .departed-room {
        opacity: 0.7;
    }
    .turnover-room {
        position: relative;
    }
    .turnover-room::before {
        content: '⟳';
        position: absolute;
        top: -8px;
        right: -8px;
        background: #f59e0b;
        color: white;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        animation: spin-icon 2s linear infinite;
    }
    @keyframes spin-icon {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .staff-list-item {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        background: rgba(255,255,255,0.9);
        border-radius: 4px;
        margin: 2px;
        font-size: 10px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.15);
    }
    .staff-list-item .avatar {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: bold;
        flex-shrink: 0;
    }
    .staff-list-item .name {
        color: #374151;
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .section-staff-list {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        max-height: 100%;
        overflow-y: auto;
        padding: 4px;
    }
    .section-staff-list::-webkit-scrollbar {
        width: 3px;
    }
    .section-staff-list::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.5);
        border-radius: 2px;
    }
    .staff-card.on-leave {
        opacity: 0.5;
        background: #fef2f2 !important;
        border-color: #fca5a5 !important;
    }
    .staff-card.on-leave::after {
        content: 'ON LEAVE';
        position: absolute;
        top: 2px;
        right: 2px;
        background: #ef4444;
        color: white;
        font-size: 8px;
        padding: 1px 4px;
        border-radius: 3px;
        font-weight: bold;
    }
    .leave-badge {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #dc2626;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
    }
    /* Category color coding for staff avatars */
    .category-front_office { background: linear-gradient(135deg, #22c55e, #16a34a) !important; }
    .category-kitchen { background: linear-gradient(135deg, #3b82f6, #2563eb) !important; }
    .category-restaurant { background: linear-gradient(135deg, #f97316, #ea580c) !important; }
    .category-housekeeping { background: linear-gradient(135deg, #ec4899, #db2777) !important; }
    .category-maintenance { background: linear-gradient(135deg, #eab308, #ca8a04) !important; }
    .category-garden { background: linear-gradient(135deg, #84cc16, #65a30d) !important; }
    .category-pool { background: linear-gradient(135deg, #06b6d4, #0891b2) !important; }
    .category-laundry { background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important; }
    .category-uncategorized { background: linear-gradient(135deg, #6b7280, #4b5563) !important; }
    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        body { background: white !important; }
        .map-container { box-shadow: none !important; border: 1px solid #ccc !important; }
    }
    .tooltip {
        position: absolute;
        background: #1f2937;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        max-width: 200px;
    }
    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: #1f2937;
    }
    .remove-btn {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #ef4444;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        cursor: pointer;
        flex-shrink: 0;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .staff-list-item:hover .remove-btn {
        opacity: 1;
    }
    .section-label {
        position: absolute;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
        text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
    }
    .section-label.label-top {
        top: -16px;
        left: 2px;
    }
    .section-label.label-left {
        top: 50%;
        right: 102%;
        transform: translateY(-50%);
        text-align: right;
    }
</style>
@endsection

@section('content')
<div class="flex h-[calc(100vh-80px)]" style="background-color: #f8fafc;">
    <!-- Sidebar -->
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col shadow-lg">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200" style="background: linear-gradient(to right, #2563eb, #1d4ed8);">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-users"></i> Staff Members
            </h2>
            <p class="text-blue-100 text-sm mt-1">Drag staff to assign locations</p>
        </div>

        <!-- Search -->
        <div class="p-3 border-b border-gray-200">
            <div class="relative">
                <input type="text" id="staffSearch" placeholder="Search staff..." 
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </div>

        <!-- My Priority List (Owner's Personal ToDo) -->
        @if(Auth::user()->role === 'admin')
        <div class="border-b border-gray-200" style="background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);">
            <div class="p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                        <i class="fas fa-thumbtack"></i> My Priority List
                    </h3>
                    <div class="flex items-center gap-1">
                        <span id="ownerTaskCount" class="text-xs bg-amber-600 text-white px-2 py-0.5 rounded-full font-medium">0</span>
                        <button onclick="toggleOwnerTaskList()" class="text-amber-700 hover:text-amber-900 text-xs p-1">
                            <i id="ownerTaskArrow" class="fas fa-chevron-down transition-transform"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Quick Add Input -->
                <div class="flex gap-1 mb-2">
                    <input type="text" id="quickTaskInput" placeholder="Add reminder..." 
                        class="flex-1 px-3 py-1.5 text-xs border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white/80"
                        onkeypress="if(event.key==='Enter') addQuickTask()">
                    <button onclick="addQuickTask()" class="px-2 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs rounded-lg transition-colors">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <!-- Task List -->
                <div id="ownerTaskList" class="space-y-1 max-h-40 overflow-y-auto">
                    <div class="text-center py-2 text-amber-600 text-xs">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Staff List -->
        <div class="flex-1 overflow-y-auto sidebar-scroll" id="staffList">
            @foreach($staffByCategory as $category => $members)
                @if($members->count() > 0)
                <div class="border-b border-gray-100 category-section" data-category="{{ $category }}">
                    <button onclick="toggleCategory('{{ $category }}')" 
                        class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chevron-right text-gray-500 transition-transform category-arrow" id="arrow-{{ $category }}"></i>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium 
                                @switch($category)
                                    @case('front_office') bg-green-100 text-green-800 @break
                                    @case('kitchen') bg-blue-100 text-blue-800 @break
                                    @case('restaurant') bg-orange-100 text-orange-800 @break
                                    @case('housekeeping') bg-pink-100 text-pink-800 @break
                                    @case('maintenance') bg-yellow-100 text-yellow-800 @break
                                    @case('garden') bg-lime-100 text-lime-800 @break
                                    @case('pool') bg-cyan-100 text-cyan-800 @break
                                    @case('laundry') bg-indigo-100 text-indigo-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">
                                {{ $categoryNames[$category] ?? ucfirst(str_replace('_', ' ', $category)) }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-500">
                            <span class="assigned-count" data-category="{{ $category }}">0</span>/{{ $members->count() }}
                        </span>
                    </button>
                    <div class="px-3 pb-3 space-y-2 category-content" id="content-{{ $category }}" style="display: none;">
                        @foreach($members as $member)
                        <div class="staff-card relative flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-blue-300"
                            draggable="true"
                            data-staff-id="{{ $member->id }}"
                            data-staff-name="{{ $member->name }}"
                            data-category="{{ $category }}"
                            id="staff-{{ $member->id }}">
                            <div class="relative flex-shrink-0">
                                <div class="w-10 h-10 rounded-full category-{{ $category }} flex items-center justify-center text-white font-semibold text-sm shadow-inner">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div class="assigned-indicator absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white hidden"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm truncate">{{ $member->name }}</p>
                                <span class="text-xs text-gray-500">{{ $categoryNames[$category] ?? ucfirst(str_replace('_', ' ', $category)) }}</span>
                            </div>
                            <div class="assignment-info hidden flex-col items-end gap-1">
                                <span class="location-badge text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full truncate max-w-[80px]"></span>
                                <button onclick="unassignStaff({{ $member->id }})" class="text-xs text-red-500 hover:text-red-700 hover:underline">
                                    Unassign
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Staff On Leave Section -->
        <div class="p-3 border-t border-gray-200 bg-red-50" id="staffOnLeaveSection" style="display: none;">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-user-slash text-red-500"></i>
                <span class="text-sm font-medium text-red-700">Staff On Leave (<span id="leaveCount">0</span>)</span>
            </div>
            <div class="space-y-1 text-xs max-h-32 overflow-y-auto" id="staffOnLeaveList">
                <!-- Staff on leave will be listed here -->
            </div>
        </div>

        <!-- Mini Calendar Widget -->
        <div class="px-3 py-2 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-gray-600"><i class="fas fa-calendar-alt mr-1"></i>This Month</p>
                <div class="flex gap-1">
                    <button onclick="changeCalendarMonth(-1)" class="text-xs text-gray-500 hover:text-gray-700 px-1"><i class="fas fa-chevron-left"></i></button>
                    <span id="miniCalendarMonth" class="text-xs font-medium text-gray-600"></span>
                    <button onclick="changeCalendarMonth(1)" class="text-xs text-gray-500 hover:text-gray-700 px-1"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <div id="miniCalendar" class="grid grid-cols-7 gap-0.5 text-center">
                <!-- Calendar will be rendered here -->
            </div>
        </div>

        <!-- Footer Stats -->
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="text-xs text-gray-500 space-y-1">
                <div class="flex justify-between">
                    <span>Total Staff:</span>
                    <span class="font-medium" id="totalStaff">{{ $staff->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Assigned:</span>
                    <span class="font-medium text-green-600" id="totalAssigned">0</span>
                </div>
                <div class="flex justify-between">
                    <span>On Leave:</span>
                    <span class="font-medium text-red-600" id="totalOnLeave">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MIDDLE COLUMN: Live Status / Command Center -->
    <div class="flex-1 p-4 overflow-auto" style="background-color: #f8fafc;">
        <!-- Command Center Header -->
        <div class="mb-3 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-xl p-4 shadow-lg">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center shadow-inner">
                        <i class="fas fa-satellite-dish text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white tracking-wide">Daily Operations Command Center</h1>
                        <p class="text-slate-400 text-xs">Real-time hotel operations at a glance</p>
                    </div>
                    <!-- Date Selection -->
                    <div class="flex items-center gap-1 bg-white/10 backdrop-blur px-2 py-1 rounded-lg text-xs ml-2">
                        <i class="fas fa-calendar-alt text-amber-400"></i>
                        <button onclick="goToPreviousDay()" class="text-gray-300 hover:text-white font-medium px-1.5 py-0.5 rounded hover:bg-white/10">←</button>
                        <input type="date" id="allocationDate" 
                            class="border-0 focus:outline-none focus:ring-0 text-white font-medium text-xs w-28 bg-transparent"
                            value="{{ date('Y-m-d') }}"
                            onchange="handleDateChange(this.value)">
                        <button onclick="goToToday()" class="text-amber-400 hover:text-amber-300 font-medium px-1.5 py-0.5 rounded hover:bg-white/10">Today</button>
                        <button onclick="goToTomorrow()" class="text-emerald-400 hover:text-emerald-300 font-medium px-1.5 py-0.5 rounded hover:bg-white/10">Tomorrow</button>
                        <button onclick="goToNextDay()" class="text-gray-300 hover:text-white font-medium px-1.5 py-0.5 rounded hover:bg-white/10">→</button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="clearAllAssignments()" class="px-2 py-1.5 bg-red-500/80 hover:bg-red-600 text-white text-xs rounded-lg shadow-sm transition-colors flex items-center gap-1 no-print">
                        <i class="fas fa-trash-alt"></i> Clear
                    </button>
                    <button onclick="printRoster()" class="px-2 py-1.5 bg-indigo-500/80 hover:bg-indigo-600 text-white text-xs rounded-lg shadow-sm transition-colors flex items-center gap-1 no-print">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <a href="{{ route('duty.roster.assign.tasks') }}?date={{ date('Y-m-d') }}" id="assignTasksBtn" class="px-2 py-1.5 bg-yellow-500/80 hover:bg-yellow-600 text-white text-xs rounded-lg shadow-sm transition-colors flex items-center gap-1 no-print text-decoration-none">
                        <i class="fas fa-tasks"></i> Tasks
                    </a>
                </div>
            </div>

            <!-- Quick Stats Bar - Row 1 -->
            <div class="grid grid-cols-8 gap-1.5 mt-3">
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="scrollToWidget('arrivalsWidget')">
                    <div class="text-emerald-400 text-base font-bold" id="ccArrivals">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">Arrivals</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="scrollToWidget('arrivalsWidget')">
                    <div class="text-orange-400 text-base font-bold" id="ccDepartures">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">Departures</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="scrollToWidget('arrivalsWidget')">
                    <div class="text-cyan-400 text-base font-bold" id="ccInHouse">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">In-House</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="scrollToWidget('housekeepingWidget')">
                    <div class="text-red-400 text-base font-bold" id="ccDirtyRooms">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">Needs Clean</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="scrollToWidget('todayTasksWidget')">
                    <div class="text-violet-400 text-base font-bold" id="ccTasksDue">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">Tasks Due</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="document.getElementById('inventoryWarningsWidget')?.scrollIntoView({behavior:'smooth'})">
                    <div class="text-yellow-400 text-base font-bold" id="ccLowStock">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">Low Stock</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="document.getElementById('crmLeadsWidget')?.scrollIntoView({behavior:'smooth'})">
                    <div class="text-blue-400 text-base font-bold" id="ccPendingLeads">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">CRM Leads</div>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-lg p-1.5 text-center cursor-pointer hover:bg-white/20 transition" onclick="document.getElementById('feedbackWidget')?.scrollIntoView({behavior:'smooth'})">
                    <div class="text-pink-400 text-base font-bold" id="ccFeedback">-</div>
                    <div class="text-slate-400 text-[9px] uppercase tracking-wider">Feedback</div>
                </div>
            </div>
        </div>

        @if(auth()->user() && auth()->user()->role === 'admin')
        <!-- 🚨 FRAUD ALERT WIDGET (Security Monitor) - ADMIN ONLY -->
        <div class="mb-4 bg-gradient-to-r from-red-600 to-orange-600 rounded-xl shadow-lg border-2 border-red-300" id="fraudAlertWidget">
            <div class="p-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fas fa-shield-alt"></i> Security Alert: Suspicious Activity
                </h3>
                <span class="text-xs bg-white/30 text-white px-2 py-1 rounded-full font-bold" id="fraudCount">0</span>
            </div>
            <div class="bg-white/95 p-3 rounded-b-xl">
                <div id="fraudAlertContent">
                    <div class="text-center py-2 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Checking...</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Arrivals & Departures Widget -->
        <div class="mb-4 grid grid-cols-2 gap-3" id="arrivalsWidget">
            <!-- Arrivals -->
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="p-2.5 bg-gradient-to-r from-emerald-600 to-green-600 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-plane-arrival"></i> Arrivals Today
                    </h3>
                    <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded-full font-medium" id="arrivalsCount">0</span>
                </div>
                <div class="p-2 max-h-48 overflow-y-auto" id="arrivalsList">
                    <div class="text-center py-3 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                </div>
            </div>
            <!-- Departures -->
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="p-2.5 bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-plane-departure"></i> Departures Today
                    </h3>
                    <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded-full font-medium" id="departuresCount">0</span>
                </div>
                <div class="p-2 max-h-48 overflow-y-auto" id="departuresList">
                    <div class="text-center py-3 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                </div>
            </div>
        </div>

        <!-- Housekeeping Status Widget -->
        <div class="mb-4 bg-white rounded-lg shadow-sm border overflow-hidden" id="housekeepingWidget">
            <div class="p-2.5 bg-gradient-to-r from-pink-600 to-rose-600 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fas fa-broom"></i> Housekeeping Status
                </h3>
                <div class="flex items-center gap-2">
                    <button onclick="showManageRoomsModal()" class="text-white hover:text-pink-200 text-xs px-2 py-1 rounded hover:bg-white/20" title="Manage Rooms">
                        <i class="fas fa-cog"></i>
                    </button>
                    <button onclick="showHousekeepingLogs()" class="text-white hover:text-pink-200 text-xs px-2 py-1 rounded hover:bg-white/20" title="View History">
                        <i class="fas fa-history"></i>
                    </button>
                    <button onclick="refreshHousekeeping()" class="text-white hover:text-pink-200 text-xs px-2 py-1 rounded hover:bg-white/20" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="p-2">
                <!-- Housekeeping Stats -->
                <div class="grid grid-cols-4 gap-2 mb-2">
                    <div class="text-center bg-gray-50 rounded-lg p-2">
                        <div class="text-xs text-gray-500">Total</div>
                        <div class="text-lg font-bold text-gray-700" id="hkTotal">0</div>
                    </div>
                    <div class="text-center bg-green-50 rounded-lg p-2">
                        <div class="text-xs text-green-600">Available</div>
                        <div class="text-lg font-bold text-green-600" id="hkAvailable">0</div>
                    </div>
                    <div class="text-center bg-yellow-50 rounded-lg p-2">
                        <div class="text-xs text-yellow-600">Occupied</div>
                        <div class="text-lg font-bold text-yellow-600" id="hkOccupied">0</div>
                    </div>
                    <div class="text-center bg-red-50 rounded-lg p-2">
                        <div class="text-xs text-red-600">Needs Cleaning</div>
                        <div class="text-lg font-bold text-red-600" id="hkNeedsCleaning">0</div>
                    </div>
                </div>
                <!-- Room Grid -->
                <div id="hkRoomGrid" class="flex flex-wrap gap-1.5">
                    <div class="text-center py-2 text-gray-400 text-xs w-full"><i class="fas fa-spinner fa-spin"></i> Loading rooms...</div>
                </div>
            </div>
        </div>

        <!-- Today's Tasks Widget -->
        <div class="mb-4 bg-white rounded-lg shadow-sm border overflow-hidden" id="todayTasksWidget">
            <div class="p-2.5 bg-gradient-to-r from-violet-600 to-purple-600 flex items-center justify-between cursor-pointer" onclick="toggleWidgetBody('todayTasksBody')">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fas fa-clipboard-list"></i> Today's Tasks
                </h3>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] bg-white/20 text-white px-1.5 py-0.5 rounded-full font-medium" id="tasksDueCount">0</span>
                        <span class="text-[10px] text-purple-200">due</span>
                        <span class="text-[10px] bg-red-500/80 text-white px-1.5 py-0.5 rounded-full font-medium" id="tasksOverdueCount">0</span>
                        <span class="text-[10px] text-purple-200">overdue</span>
                    </div>
                    <i class="fas fa-chevron-down text-white/60 text-xs transition-transform" id="todayTasksBodyIcon"></i>
                </div>
            </div>
            <div id="todayTasksBody">
                <div class="p-2 grid grid-cols-4 gap-2 bg-purple-50 border-b">
                    <div class="text-center">
                        <div class="text-xs font-bold text-violet-700" id="taskStatDue">0</div>
                        <div class="text-[9px] text-violet-500">Due Today</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs font-bold text-red-600" id="taskStatOverdue">0</div>
                        <div class="text-[9px] text-red-500">Overdue</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs font-bold text-green-600" id="taskStatCompleted">0</div>
                        <div class="text-[9px] text-green-500">Done Today</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs font-bold text-gray-600" id="taskStatPending">0</div>
                        <div class="text-[9px] text-gray-500">Total Pending</div>
                    </div>
                </div>
                <div class="p-2 max-h-56 overflow-y-auto" id="todayTasksList">
                    <div class="text-center py-3 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading tasks...</div>
                </div>
            </div>
        </div>

        <!-- Booking Timeline View -->
        <div class="mb-4 bg-white rounded-lg shadow-sm border p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-gray-700"><i class="fas fa-stream mr-1"></i>Today's Booking Timeline</p>
                <span class="text-xs text-gray-400">Scroll to see full day</span>
            </div>
            <div class="relative overflow-x-auto" id="timelineContainer">
                <div class="relative h-auto min-h-[60px]" id="bookingTimeline" style="min-width: 100%;">
                    <div class="text-center py-4 text-gray-400 text-xs">
                        <i class="fas fa-spinner fa-spin"></i> Loading timeline...
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Summary Widget -->
        <div class="mb-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-gradient-to-r from-violet-600 to-purple-600">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-user-check"></i> Staff Attendance Summary
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshAttendanceSummary()" class="text-white hover:text-purple-200 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <a href="{{ route('attendance.manual.index') }}" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                            <i class="fas fa-external-link-alt"></i> Mark Attendance
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-6 gap-2 p-3 bg-gray-50 border-b">
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Total Staff</div>
                    <div class="text-lg font-bold text-gray-800" id="attTotalStaff">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Present</div>
                    <div class="text-lg font-bold text-green-600" id="attPresent">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Half Day</div>
                    <div class="text-lg font-bold text-yellow-600" id="attHalfDay">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Absent</div>
                    <div class="text-lg font-bold text-red-600" id="attAbsent">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">On Leave</div>
                    <div class="text-lg font-bold text-blue-600" id="attOnLeave">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Attendance Rate</div>
                    <div class="text-lg font-bold text-purple-600" id="attRate">0%</div>
                </div>
            </div>

            <!-- Attendance Progress Bar -->
            <div class="px-3 py-2 border-b">
                <div class="flex items-center gap-2 text-xs mb-1">
                    <span class="text-gray-500">Attendance Overview:</span>
                    <span id="attNotMarkedBadge" class="hidden bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-medium">
                        <i class="fas fa-exclamation-circle"></i> <span id="attNotMarkedCount">0</span> not marked
                    </span>
                </div>
                <div class="w-full h-4 bg-gray-200 rounded-full overflow-hidden flex">
                    <div id="attProgressPresent" class="h-full bg-green-500 transition-all" style="width: 0%"></div>
                    <div id="attProgressHalf" class="h-full bg-yellow-500 transition-all" style="width: 0%"></div>
                    <div id="attProgressAbsent" class="h-full bg-red-500 transition-all" style="width: 0%"></div>
                    <div id="attProgressLeave" class="h-full bg-blue-500 transition-all" style="width: 0%"></div>
                    <div id="attProgressNotMarked" class="h-full bg-gray-400 transition-all" style="width: 0%"></div>
                </div>
                <div class="flex justify-between text-xs mt-1 text-gray-500">
                    <div class="flex items-center gap-3">
                        <span><span class="inline-block w-2 h-2 rounded-full bg-green-500 mr-1"></span>Present</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>Half</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1"></span>Absent</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-blue-500 mr-1"></span>Leave</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-gray-400 mr-1"></span>Not Marked</span>
                    </div>
                </div>
            </div>

            <!-- Category Breakdown (Collapsible) -->
            <div class="p-3">
                <button onclick="toggleAttendanceBreakdown()" class="flex items-center justify-between w-full text-left text-xs font-semibold text-gray-600 mb-2">
                    <span><i class="fas fa-chart-pie mr-1"></i> Department Breakdown</span>
                    <i id="attBreakdownArrow" class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                </button>
                <div id="attCategoryBreakdown" class="hidden space-y-2 max-h-48 overflow-y-auto">
                    <div class="text-center py-2 text-gray-400 text-xs">Loading...</div>
                </div>
            </div>

            <!-- Absent & On Leave Staff (Collapsible) -->
            <div class="p-3 border-t bg-gray-50">
                <button onclick="toggleAbsentStaffList()" class="flex items-center justify-between w-full text-left text-xs font-semibold text-gray-600 mb-2">
                    <span><i class="fas fa-user-times mr-1 text-red-500"></i> Absent & On Leave Details</span>
                    <i id="absentListArrow" class="fas fa-chevron-down text-gray-400 transition-transform"></i>
                </button>
                <div id="absentStaffList" class="hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Absent Staff -->
                        <div class="bg-red-50 rounded-lg p-2">
                            <p class="text-xs font-medium text-red-700 mb-1"><i class="fas fa-times-circle mr-1"></i> Absent Today</p>
                            <div id="attAbsentList" class="text-xs space-y-1 max-h-32 overflow-y-auto">
                                <p class="text-gray-400">No absent staff</p>
                            </div>
                        </div>
                        <!-- On Leave Staff -->
                        <div class="bg-blue-50 rounded-lg p-2">
                            <p class="text-xs font-medium text-blue-700 mb-1"><i class="fas fa-calendar-minus mr-1"></i> On Leave Today</p>
                            <div id="attOnLeaveList" class="text-xs space-y-1 max-h-32 overflow-y-auto">
                                <p class="text-gray-400">No staff on leave</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div class="map-container bg-white rounded-xl shadow-lg border border-gray-200 p-6" id="mapContainer">
            <!-- Sections will be rendered here -->
        </div>

        <!-- Today's Bills Report (Admin Only) -->
        @if(Auth::user()->role === 'admin')
        
        <!-- Net Profit/Loss Today Card -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-gradient-to-r from-indigo-900 to-slate-800 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-chart-line text-yellow-400"></i> Net Profit/Loss Today
                    </h3>
                    <div class="flex items-center gap-2">
                        <span id="nplStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-700 text-gray-300">CALCULATING</span>
                        <button onclick="refreshNetProfitSummary()" class="text-white hover:text-indigo-200 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Net Result</div>
                        <div class="text-3xl font-extrabold flex items-baseline gap-2" id="nplTargetValue">
                            Rs 0.00
                        </div>
                        <div class="text-xs font-medium mt-1" id="nplMarginContainer">
                            Margin: <span id="nplMarginValue" class="text-gray-600">0%</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500 mb-1">Status</div>
                        <div id="nplStatusIcon" class="text-4xl text-gray-300">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Breakdown -->
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm border-t pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span> Income</span>
                        <span class="font-bold text-gray-800" id="nplIncome">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-red-500 inline-block mr-1"></span> Expenses ( No MD )</span>
                        <span class="font-bold text-gray-800" id="nplExpenses">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block mr-1"></span> Staff Costs</span>
                        <span class="font-bold text-gray-800" id="nplStaffCost">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-blue-400 inline-block mr-1"></span> Inventory COGS</span>
                        <span class="font-bold text-gray-800" id="nplCogs">Rs 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Profit/Loss Report (Admin Only) -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden" id="monthlyProfitWidget">
            <div class="p-3 bg-gradient-to-r from-purple-900 to-indigo-900 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-purple-300"></i> Monthly Profit/Loss
                    </h3>
                    <div class="flex items-center gap-2">
                        <span id="mplStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-700 text-gray-300">CALCULATING</span>
                        <button onclick="refreshMonthlyProfit()" class="text-white hover:text-purple-200 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <!-- Month Navigation -->
                <div class="flex items-center gap-2 mt-2">
                    <button onclick="changeMonth(-1)" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <span id="mplMonthLabel" class="text-xs font-medium bg-white/10 px-3 py-1.5 rounded flex-1 text-center">{{ date('F Y') }}</span>
                    <button onclick="changeMonth(1)" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="p-4">
                <!-- Net Result -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Monthly Net Result</div>
                        <div class="text-3xl font-extrabold flex items-baseline gap-2" id="mplNetValue">Rs 0.00</div>
                        <div class="text-xs font-medium mt-1" id="mplMarginContainer">
                            Margin: <span id="mplMarginValue" class="text-gray-600">0%</span>
                            <span class="text-gray-400 ml-2" id="mplDaysCounted"></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500 mb-1">Status</div>
                        <div id="mplStatusIcon" class="text-4xl text-gray-300">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </div>
                    </div>
                </div>

                <!-- Income Breakdown (Cash / Card / Bank) -->
                <div class="bg-emerald-50 rounded-lg p-3 mb-3 border border-emerald-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-emerald-700"><i class="fas fa-money-bill-wave mr-1"></i> Total Income</span>
                        <span class="text-sm font-extrabold text-emerald-700" id="mplTotalIncome">Rs 0.00</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-white rounded p-1.5">
                            <div class="text-[9px] text-gray-500">Cash</div>
                            <div class="text-xs font-bold text-green-700" id="mplCash">Rs 0</div>
                        </div>
                        <div class="bg-white rounded p-1.5">
                            <div class="text-[9px] text-gray-500">Card</div>
                            <div class="text-xs font-bold text-blue-700" id="mplCard">Rs 0</div>
                        </div>
                        <div class="bg-white rounded p-1.5">
                            <div class="text-[9px] text-gray-500">Bank</div>
                            <div class="text-xs font-bold text-purple-700" id="mplBank">Rs 0</div>
                        </div>
                    </div>
                </div>

                <!-- Expenses Breakdown -->
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm border-t pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span> Income</span>
                        <span class="font-bold text-gray-800" id="mplIncome">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-red-500 inline-block mr-1"></span> Expenses (No MD)</span>
                        <span class="font-bold text-gray-800" id="mplExpenses">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block mr-1"></span> Staff Costs</span>
                        <span class="font-bold text-gray-800" id="mplStaffCost">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><span class="w-2 h-2 rounded-full bg-blue-400 inline-block mr-1"></span> Inventory COGS</span>
                        <span class="font-bold text-gray-800" id="mplCogs">Rs 0.00</span>
                    </div>
                </div>

                <!-- Daily Breakdown Chart -->
                <div class="mt-4 border-t pt-3">
                    <p class="text-xs font-semibold text-gray-600 mb-2"><i class="fas fa-chart-bar mr-1"></i> Daily Net Profit/Loss</p>
                    <div id="mplDailyChart" class="h-28 flex items-end gap-[2px] overflow-x-auto">
                        <div class="text-center py-2 text-gray-400 text-xs w-full"><i class="fas fa-spinner fa-spin"></i></div>
                    </div>
                </div>

                <!-- Daily Breakdown Table (Collapsible) -->
                <div class="mt-3">
                    <button onclick="toggleMonthlyTable()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                        <i class="fas fa-table"></i> <span id="mplTableToggleText">Show Daily Details</span>
                        <i class="fas fa-chevron-down text-[8px]" id="mplTableIcon"></i>
                    </button>
                    <div id="mplDailyTable" class="hidden mt-2 max-h-64 overflow-y-auto">
                        <table class="w-full text-[10px]">
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-2 py-1 text-left">Date</th>
                                    <th class="px-2 py-1 text-right">Income</th>
                                    <th class="px-2 py-1 text-right">Expenses</th>
                                    <th class="px-2 py-1 text-right">Staff</th>
                                    <th class="px-2 py-1 text-right">COGS</th>
                                    <th class="px-2 py-1 text-right font-bold">Net</th>
                                </tr>
                            </thead>
                            <tbody id="mplTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Summary Widget (Admin Only) -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden" id="salarySummaryWidget">
            <div class="p-3 bg-gradient-to-r from-slate-800 to-gray-900 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-money-check-alt text-amber-400"></i> Salary Summary
                    </h3>
                    <div class="flex items-center gap-2">
                        <a href="/salary" class="text-xs text-gray-300 hover:text-white"><i class="fas fa-external-link-alt"></i></a>
                        <button onclick="refreshSalarySummary()" class="text-white hover:text-gray-300 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <!-- Month Navigation -->
                <div class="flex items-center gap-2 mt-2">
                    <button onclick="changeSalaryMonth(-1)" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <span id="salMonthLabel" class="text-xs font-medium bg-white/10 px-3 py-1.5 rounded flex-1 text-center">{{ date('F Y') }}</span>
                    <button onclick="changeSalaryMonth(1)" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="p-4">
                <!-- Key Totals -->
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div class="bg-gray-50 rounded-lg p-2 text-center border">
                        <div class="text-[9px] text-gray-500 uppercase font-semibold">Total Basic</div>
                        <div class="text-sm font-extrabold text-gray-800" id="salTotalBasic">Rs 0</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-2 text-center border border-red-200">
                        <div class="text-[9px] text-red-500 uppercase font-semibold">Advances</div>
                        <div class="text-sm font-extrabold text-red-700" id="salTotalAdvance">Rs 0</div>
                    </div>
                    <div class="bg-emerald-50 rounded-lg p-2 text-center border border-emerald-200">
                        <div class="text-[9px] text-emerald-500 uppercase font-semibold">Final Salary</div>
                        <div class="text-sm font-extrabold text-emerald-700" id="salTotalFinal">Rs 0</div>
                    </div>
                </div>

                <!-- Attendance Summary -->
                <div class="flex items-center justify-between text-xs border-t pt-2 mb-3">
                    <span class="text-gray-500"><i class="fas fa-users mr-1"></i> <span id="salStaffCount">0</span> Staff</span>
                    <span class="text-emerald-600"><i class="fas fa-check-circle mr-1"></i> Present: <strong id="salPresentDays">0</strong></span>
                    <span class="text-red-500"><i class="fas fa-times-circle mr-1"></i> Absent: <strong id="salAbsentDays">0</strong></span>
                </div>

                <!-- Advance Period -->
                <div class="text-[10px] text-gray-400 mb-2">
                    <i class="fas fa-calendar-week mr-1"></i> Advance Period: <span id="salAdvancePeriod">-</span>
                </div>

                <!-- Salary Advances by Person -->
                <div class="border-t pt-2">
                    <button onclick="toggleSalaryAdvances()" class="text-xs text-amber-600 hover:text-amber-800 font-medium flex items-center gap-1 mb-1">
                        <i class="fas fa-hand-holding-usd"></i> <span id="salAdvToggleText">Show Advances</span>
                        <i class="fas fa-chevron-down text-[8px]" id="salAdvIcon"></i>
                    </button>
                    <div id="salAdvancesList" class="hidden max-h-40 overflow-y-auto">
                        <div class="text-center py-2 text-gray-400 text-xs">No advances</div>
                    </div>
                </div>

                <!-- Employee Table (Collapsible) -->
                <div class="mt-2 border-t pt-2">
                    <button onclick="toggleSalaryTable()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                        <i class="fas fa-table"></i> <span id="salTableToggleText">Show Staff Details</span>
                        <i class="fas fa-chevron-down text-[8px]" id="salTableIcon"></i>
                    </button>
                    <div id="salEmployeeTable" class="hidden mt-2 max-h-72 overflow-y-auto">
                        <table class="w-full text-[10px]">
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-2 py-1 text-left">Employee</th>
                                    <th class="px-2 py-1 text-right">Basic</th>
                                    <th class="px-2 py-1 text-right">Advance</th>
                                    <th class="px-2 py-1 text-center">Present</th>
                                    <th class="px-2 py-1 text-center">Absent</th>
                                    <th class="px-2 py-1 text-right font-bold">Final</th>
                                </tr>
                            </thead>
                            <tbody id="salTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kitchen Summary Widget (Daily Sales + Main Kitchen Issues) -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden" id="kitchenSummaryWidget">
            <div class="p-3 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <i class="fas fa-utensils text-yellow-300"></i> Kitchen Summary
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="printKitchenSummary()" class="text-white hover:text-blue-200 text-xs px-2 py-1 rounded hover:bg-white/20" title="Print">
                            <i class="fas fa-print"></i>
                        </button>
                        <a href="/kitchen/comparison" class="text-xs text-blue-200 hover:text-white"><i class="fas fa-external-link-alt"></i></a>
                        <button onclick="loadKitchenSummary()" class="text-white hover:text-blue-200 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <!-- Date Range Selection -->
                <div class="flex items-center gap-2 mt-2">
                    <input type="date" id="kitchenStartDate" value="{{ date('Y-m-d') }}" class="text-[11px] bg-white/20 text-white border-0 rounded px-2 py-1.5 flex-1" onchange="loadKitchenSummary()">
                    <span class="text-xs text-blue-200">to</span>
                    <input type="date" id="kitchenEndDate" value="{{ date('Y-m-d') }}" class="text-[11px] bg-white/20 text-white border-0 rounded px-2 py-1.5 flex-1" onchange="loadKitchenSummary()">
                </div>
                <!-- Quick Date Buttons -->
                <div class="flex gap-1 mt-2">
                    <button onclick="setKitchenDate('today')" class="text-[9px] bg-white/20 hover:bg-white/30 text-white px-2 py-1 rounded">Today</button>
                    <button onclick="setKitchenDate('yesterday')" class="text-[9px] bg-white/20 hover:bg-white/30 text-white px-2 py-1 rounded">Yesterday</button>
                    <button onclick="setKitchenDate('week')" class="text-[9px] bg-white/20 hover:bg-white/30 text-white px-2 py-1 rounded">Last 7 Days</button>
                    <button onclick="setKitchenDate('month')" class="text-[9px] bg-white/20 hover:bg-white/30 text-white px-2 py-1 rounded">This Month</button>
                </div>
            </div>

            <div class="p-3">
                <!-- Summary Stats Row -->
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <div class="bg-blue-50 rounded-lg p-2 text-center border border-blue-200">
                        <div class="text-[9px] text-blue-500 uppercase font-semibold">Sales Items</div>
                        <div class="text-sm font-extrabold text-blue-700" id="ksTotalItems">0</div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-2 text-center border border-blue-200">
                        <div class="text-[9px] text-blue-500 uppercase font-semibold">Total Bills</div>
                        <div class="text-sm font-extrabold text-blue-700" id="ksTotalSales">0</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2 text-center border border-green-200">
                        <div class="text-[9px] text-green-500 uppercase font-semibold">Kitchen Qty</div>
                        <div class="text-sm font-extrabold text-green-700" id="ksTotalKitchenQty">0</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2 text-center border border-green-200">
                        <div class="text-[9px] text-green-500 uppercase font-semibold">Transactions</div>
                        <div class="text-sm font-extrabold text-green-700" id="ksTotalTransactions">0</div>
                    </div>
                </div>

                <!-- Two Column Layout: Daily Sales | Inventory Issues -->
                <div class="grid grid-cols-2 gap-3">
                    <!-- Daily Sales -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-xs font-bold text-blue-700 flex items-center gap-1">
                                <i class="fas fa-chart-line"></i> Daily Sales
                            </h4>
                            <div class="relative">
                                <button onclick="toggleKsDropdown('salesFilter')" class="text-[10px] border border-blue-300 rounded px-2 py-0.5 text-blue-700 bg-blue-50 hover:bg-blue-100 flex items-center gap-1">
                                    <i class="fas fa-filter text-[8px]"></i> Filter <i class="fas fa-caret-down text-[8px]"></i>
                                </button>
                                <div id="salesFilterDropdown" class="hidden absolute right-0 top-full mt-1 bg-white border border-blue-200 rounded shadow-lg z-50 w-48 max-h-52 overflow-y-auto">
                                    <div class="px-2 py-1 border-b border-blue-100">
                                        <label class="flex items-center gap-1 text-[10px] font-bold text-blue-700 cursor-pointer">
                                            <input type="checkbox" id="ksSalesAll" checked onchange="toggleAllSalesCategories(this)" class="w-3 h-3"> All Categories
                                        </label>
                                    </div>
                                    <div id="ksSalesCatList"></div>
                                </div>
                            </div>
                        </div>
                        <div id="ksSalesContent" class="max-h-80 overflow-y-auto border rounded">
                            <div class="text-center py-4 text-gray-400 text-xs">Loading...</div>
                        </div>
                    </div>

                    <!-- Inventory Issues -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-xs font-bold text-green-700 flex items-center gap-1">
                                <i class="fas fa-fire"></i> Inventory Issues
                            </h4>
                            <div class="relative">
                                <button onclick="toggleKsDropdown('issuesFilter')" class="text-[10px] border border-green-300 rounded px-2 py-0.5 text-green-700 bg-green-50 hover:bg-green-100 flex items-center gap-1">
                                    <i class="fas fa-filter text-[8px]"></i> Filter <i class="fas fa-caret-down text-[8px]"></i>
                                </button>
                                <div id="issuesFilterDropdown" class="hidden absolute right-0 top-full mt-1 bg-white border border-green-200 rounded shadow-lg z-50 w-52 max-h-52 overflow-y-auto">
                                    <div id="ksIssuesActionList"></div>
                                </div>
                            </div>
                        </div>
                        <div id="ksKitchenContent" class="max-h-80 overflow-y-auto border rounded">
                            <div class="text-center py-4 text-gray-400 text-xs">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-gradient-to-r from-emerald-600 to-teal-600">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-receipt"></i> Bills Report
                    </h3>
                    <div class="flex items-center gap-2">
                        <span id="billsTimeRange" class="text-xs text-emerald-100"></span>
                        <button onclick="refreshBillsReport()" class="text-white hover:text-emerald-100 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadBillsForYesterday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Yesterday
                    </button>
                    <input type="date" id="billsDatePicker" 
                        class="text-xs px-3 py-1.5 rounded border-0 focus:outline-none focus:ring-2 focus:ring-white/50"
                        value="{{ date('Y-m-d') }}"
                        onchange="loadBillsForDate(this.value)">
                    <button onclick="loadBillsForToday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded">
                        Today
                    </button>
                </div>
            </div>
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-3 gap-2 p-3 bg-gray-50 border-b">
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Total Sales</div>
                    <div class="text-lg font-bold text-emerald-600" id="totalSales">Rs 0.00</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Bills Count</div>
                    <div class="text-lg font-bold text-blue-600" id="billsCount">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Avg Bill</div>
                    <div class="text-lg font-bold text-purple-600" id="avgBill">Rs 0.00</div>
                </div>
            </div>

            <!-- Hourly Chart -->
            <div class="p-3 border-b">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-gray-600"><i class="fas fa-chart-bar mr-1"></i>Hourly Sales</p>
                </div>
                <div id="hourlyChart" class="h-24 flex items-end gap-0.5">
                    <!-- Chart bars will be rendered here -->
                </div>
            </div>

            <!-- Bills with Items -->
            <div class="p-3 max-h-96 overflow-y-auto">
                <p class="text-xs font-semibold text-gray-600 mb-2"><i class="fas fa-receipt mr-1"></i>Recent Bills</p>
                <div id="recentBillsList" class="space-y-2">
                    <div class="text-center py-2 text-gray-400 text-xs">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Daily Costs Report (Admin Only) -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-gradient-to-r from-red-600 to-orange-600">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-money-bill-wave"></i> Daily Costs Report
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshCostsReport()" class="text-white hover:text-red-100 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadCostsForYesterday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Yesterday
                    </button>
                    <input type="date" id="costsDatePicker" 
                        class="text-xs px-3 py-1.5 rounded border-0 focus:outline-none focus:ring-2 focus:ring-white/50"
                        value="{{ date('Y-m-d') }}"
                        onchange="loadCostsForDate(this.value)">
                    <button onclick="loadCostsForToday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded">
                        Today
                    </button>
                </div>
            </div>
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-2 gap-2 p-3 bg-gray-50 border-b">
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Total Costs</div>
                    <div class="text-lg font-bold text-red-600" id="totalCosts">Rs 0.00</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Transactions</div>
                    <div class="text-lg font-bold text-orange-600" id="costsCount">0</div>
                </div>
            </div>

            <!-- Expenses by Category (Collapsible) -->
            <div class="p-3 max-h-[600px] overflow-y-auto">
                <p class="text-xs font-semibold text-gray-600 mb-2"><i class="fas fa-list mr-1"></i>Expenses by Category</p>
                <div id="categorizedCostsList" class="space-y-2">
                    <div class="text-center py-2 text-gray-400 text-xs">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Daily Vehicle Security Summary (Admin Only) -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-gradient-to-r from-blue-600 to-indigo-600">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-car"></i> Vehicle Security Summary
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshVehicleSummary()" class="text-white hover:text-blue-100 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadVehicleSummaryForYesterday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Yesterday
                    </button>
                    <input type="date" id="vehicleSummaryDatePicker" 
                        class="text-xs px-3 py-1.5 rounded border-0 focus:outline-none focus:ring-2 focus:ring-white/50"
                        value="{{ date('Y-m-d') }}"
                        onchange="loadVehicleSummaryForDate(this.value)">
                    <button onclick="loadVehicleSummaryForToday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded">
                        Today
                    </button>
                </div>
            </div>
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-4 gap-2 p-3 bg-gray-50 border-b">
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Total Vehicles</div>
                    <div class="text-lg font-bold text-blue-600" id="vehicleTotalCount">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">On Property</div>
                    <div class="text-lg font-bold text-green-600" id="vehicleCheckedIn">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Pool Usage</div>
                    <div class="text-lg font-bold text-cyan-600" id="vehiclePoolUsage">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Checked Out</div>
                    <div class="text-lg font-bold text-gray-600" id="vehicleCheckedOut">0</div>
                </div>
            </div>

            <!-- Vehicles by Purpose (Collapsible) -->
            <div class="p-3" style="max-height: 600px; overflow-y: auto;">
                <p class="text-xs font-semibold text-gray-600 mb-2"><i class="fas fa-list mr-1"></i>Vehicles by Purpose</p>
                <div id="vehiclesByPurposeList">
                    <div class="text-center py-2 text-gray-400 text-xs">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Inventory Changes Report (Admin Only) -->
        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center p-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 me-3"><i class="fas fa-boxes me-2"></i>Inventory Changes</h5>
                    <input type="date" id="inventoryDatePicker" 
                        class="form-control form-control-sm bg-secondary text-white border-0"
                        style="width: auto;"
                        value="{{ date('Y-m-d') }}"
                        onchange="loadInventoryForDate(this.value)">
                    <button onclick="loadInventoryForToday()" class="btn btn-sm btn-outline-light">Today</button>
                </div>
                <a href="{{ route('stock.index') }}" class="btn btn-sm btn-outline-light">
                    Manage Inventory
                </a>
            </div>
            <div class="card-body">
                <!-- Summary Stats -->
                <div class="row mb-3">
                    <div class="col-4 text-center">
                        <div class="small text-muted">Total Changes</div>
                        <div class="h5 fw-bold" id="invTotalChanges">0</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="small text-muted">Items Added</div>
                        <div class="h5 fw-bold text-success" id="invItemsAdded">0</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="small text-muted">Items Removed</div>
                        <div class="h5 fw-bold text-danger" id="invItemsRemoved">0</div>
                    </div>
                </div>

                <!-- Inventory List -->
                <div id="inventoryList">
                    <div class="text-center py-4 text-muted">Loading...</div>
                </div>
                
                <!-- Cost Summary -->
                <div id="inventoryCostSummary" class="alert alert-warning border mt-3 hidden">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <strong><i class="fas fa-plus-circle text-success me-1"></i>Cost Added:</strong> 
                            <span class="text-success fw-bold" id="invCostAdded">Rs 0</span>
                        </div>
                        <div class="col-md-4 text-center">
                            <strong><i class="fas fa-minus-circle text-danger me-1"></i>Cost Used:</strong> 
                            <span class="text-danger fw-bold" id="invCostUsed">Rs 0</span>
                        </div>
                        <div class="col-md-4 text-center">
                            <strong><i class="fas fa-calculator me-1"></i>Total Daily Cost:</strong> 
                            <span class="fw-bold text-danger fs-5" id="invTotalDailyCost">Rs 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Water Bottle Summary (Admin Only) -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-cyan-600 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-wine-bottle"></i> Water Bottle Summary
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshWaterBottleReport()" class="text-white hover:text-cyan-200 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadWaterBottleForYesterday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Yesterday
                    </button>
                    <input type="date" id="waterBottleDatePicker" 
                        class="text-xs px-3 py-1.5 rounded border-0 focus:outline-none focus:ring-2 focus:ring-white/50 text-black"
                        value="{{ date('Y-m-d') }}"
                        onchange="loadWaterBottleForDate(this.value)">
                    <button onclick="loadWaterBottleForToday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded">
                        Today
                    </button>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-4 gap-2 p-3 bg-gray-50 border-b">
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Current Stock</div>
                    <div class="text-lg font-bold text-gray-800" id="wbCurrentStock">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Added</div>
                    <div class="text-lg font-bold text-emerald-600" id="wbAdded">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Issued</div>
                    <div class="text-lg font-bold text-red-600" id="wbIssued">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Net Change</div>
                    <div class="text-lg font-bold text-blue-600" id="wbNetChange">0</div>
                </div>
            </div>

            <!-- History List -->
            <div class="p-3" style="max-height: 500px; overflow-y: auto;">
                <div id="waterBottleList" class="space-y-2">
                    <div class="text-center py-2 text-gray-400 text-xs">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Active Orders Summary (Admin Only) -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-orange-600 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-utensils"></i> Active Orders
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshActiveOrders()" class="text-white hover:text-orange-200 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-3 gap-2 p-3 bg-gray-50 border-b">
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Active Tables</div>
                    <div class="text-lg font-bold text-gray-800" id="aoTotalOrders">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Total Items</div>
                    <div class="text-lg font-bold text-blue-600" id="aoTotalItems">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Total Amount</div>
                    <div class="text-lg font-bold text-emerald-600" id="aoTotalAmount">Rs 0</div>
                </div>
            </div>

            <!-- Active Orders List -->
            <div class="p-3" style="max-height: 500px; overflow-y: auto;">
                <div id="activeOrdersList" class="space-y-2">
                    <div class="text-center py-2 text-gray-400 text-xs">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Staff Out (Gate Pass) Summary -->
        <div class="mt-4 bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="p-3 bg-gradient-to-r from-rose-600 to-pink-600">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-door-open"></i> Staff Out (Gate Pass)
                    </h3>
                    <div class="flex items-center gap-2">
                        <button onclick="refreshStaffOut()" class="text-white hover:text-rose-200 text-xs px-2 py-1 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadStaffOutForYesterday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-left"></i> Yesterday
                    </button>
                    <input type="date" id="staffOutDatePicker" 
                        class="text-xs px-3 py-1.5 rounded border-0 focus:outline-none focus:ring-2 focus:ring-white/50"
                        value="{{ date('Y-m-d') }}"
                        onchange="loadStaffOutForDate(this.value)">
                    <button onclick="loadStaffOutForToday()" class="text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded">
                        Today
                    </button>
                </div>
            </div>
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-4 gap-2 p-3 bg-gray-50 border-b">
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Total Today</div>
                    <div class="text-lg font-bold text-gray-700" id="staffOutTotal">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Currently Out</div>
                    <div class="text-lg font-bold text-rose-600" id="staffOutCurrently">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Returned</div>
                    <div class="text-lg font-bold text-green-600" id="staffOutReturned">0</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Overdue</div>
                    <div class="text-lg font-bold text-red-600" id="staffOutOverdue">0</div>
                </div>
            </div>

            <!-- Staff Out List -->
            <div class="p-3" style="max-height: 400px; overflow-y: auto;">
                <div id="staffOutList" class="space-y-2">
                    <div class="text-center py-2 text-gray-400 text-xs">Loading...</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- RIGHT COLUMN: Alerts & Intelligence -->
    <div class="w-96 bg-white border-l border-gray-200 flex flex-col shadow-lg">
        <!-- Quick Navigation Buttons -->
        <div class="flex">
            <a href="/room-visualizer" class="flex-1 py-2 text-center text-white font-bold text-xs" style="background: #f97316;">
                <i class="fas fa-bed"></i> Rooms
            </a>
            <a href="/calendar" class="flex-1 py-2 text-center text-white font-bold text-xs" style="background: #ea580c;">
                <i class="fas fa-calendar"></i> Calendar
            </a>
            <a href="/leads" class="flex-1 py-2 text-center text-white font-bold text-xs" style="background: #2563eb;">
                <i class="fas fa-headset"></i> CRM
            </a>
        </div>

        <!-- Alerts Header -->
        <div class="p-3 border-b border-gray-200" style="background: linear-gradient(to right, #dc2626, #b91c1c);">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fas fa-bell"></i> Alerts & Intelligence
            </h2>
            <p class="text-red-200 text-xs mt-0.5">Items requiring immediate attention</p>
        </div>

        <!-- Scrollable Alerts Area -->
        <div class="flex-1 overflow-y-auto sidebar-scroll">

            <!-- Inventory Warnings Widget -->
            <div class="border-b border-gray-200" id="inventoryWarningsWidget">
                <div class="p-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 flex items-center justify-between cursor-pointer" onclick="toggleWidgetBody('inventoryBody')">
                    <h3 class="text-xs font-bold text-white flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i> Inventory Warnings
                    </h3>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] bg-white/20 text-white px-1.5 py-0.5 rounded-full font-medium" id="invWarningCount">0</span>
                        <button onclick="event.stopPropagation(); refreshInventoryWarnings()" class="text-white hover:text-yellow-200 text-xs px-1 py-0.5 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <i class="fas fa-chevron-down text-white/60 text-xs transition-transform" id="inventoryBodyIcon"></i>
                    </div>
                </div>
                <div class="p-2 max-h-52 overflow-y-auto" id="inventoryBody">
                    <div class="text-center py-2 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                </div>
            </div>

            <!-- CRM Leads Widget -->
            <div class="border-b border-gray-200" id="crmLeadsWidget">
                <div class="p-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between cursor-pointer" onclick="toggleWidgetBody('crmBody')">
                    <h3 class="text-xs font-bold text-white flex items-center gap-2">
                        <i class="fas fa-headset"></i> Pending CRM Leads
                    </h3>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] bg-white/20 text-white px-1.5 py-0.5 rounded-full font-medium" id="crmLeadCount">0</span>
                        <button onclick="event.stopPropagation(); refreshPendingLeads()" class="text-white hover:text-blue-200 text-xs px-1 py-0.5 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <i class="fas fa-chevron-down text-white/60 text-xs transition-transform" id="crmBodyIcon"></i>
                    </div>
                </div>
                <div id="crmBody">
                    <!-- CRM Quick Stats -->
                    <div class="grid grid-cols-4 gap-1 p-2 bg-blue-50 border-b text-center">
                        <div>
                            <div class="text-xs font-bold text-blue-700" id="crmTodayNew">0</div>
                            <div class="text-[9px] text-blue-500">New Today</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-amber-600" id="crmPendingCalls">0</div>
                            <div class="text-[9px] text-amber-500">Pending</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-red-600" id="crmOverdue">0</div>
                            <div class="text-[9px] text-red-500">Overdue</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-green-600" id="crmConversion">0%</div>
                            <div class="text-[9px] text-green-500">Conv. Rate</div>
                        </div>
                    </div>
                    <div class="p-2 max-h-52 overflow-y-auto" id="crmLeadsList">
                        <div class="text-center py-2 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Tickets Widget -->
            <div class="border-b border-gray-200" id="maintenanceWidget">
                <div class="p-2.5 bg-gradient-to-r from-rose-700 to-red-600 flex items-center justify-between cursor-pointer" onclick="toggleWidgetBody('maintenanceBody')">
                    <h3 class="text-xs font-bold text-white flex items-center gap-2">
                        <i class="fas fa-tools"></i> Maintenance & Damage
                    </h3>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] bg-white/20 text-white px-1.5 py-0.5 rounded-full font-medium" id="maintenanceCount">0</span>
                        <button onclick="event.stopPropagation(); refreshMaintenanceTickets()" class="text-white hover:text-rose-200 text-xs px-1 py-0.5 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <i class="fas fa-chevron-down text-white/60 text-xs transition-transform" id="maintenanceBodyIcon"></i>
                    </div>
                </div>
                <div class="p-2 max-h-52 overflow-y-auto" id="maintenanceBody">
                    <div class="text-center py-2 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                </div>
            </div>

            <!-- Customer Feedback Widget -->
            <div class="border-b border-gray-200" id="feedbackWidget">
                <div class="p-2.5 bg-gradient-to-r from-pink-600 to-fuchsia-600 flex items-center justify-between cursor-pointer" onclick="toggleWidgetBody('feedbackBody')">
                    <h3 class="text-xs font-bold text-white flex items-center gap-2">
                        <i class="fas fa-comment-dots"></i> Pending Feedback
                    </h3>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] bg-white/20 text-white px-1.5 py-0.5 rounded-full font-medium" id="feedbackCount">0</span>
                        <button onclick="event.stopPropagation(); refreshPendingFeedback()" class="text-white hover:text-pink-200 text-xs px-1 py-0.5 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <i class="fas fa-chevron-down text-white/60 text-xs transition-transform" id="feedbackBodyIcon"></i>
                    </div>
                </div>
                <div class="p-2" id="feedbackBody">
                    <div class="grid grid-cols-2 gap-1 mb-2 bg-pink-50 rounded p-1.5 text-center">
                        <div>
                            <div class="text-xs font-bold text-pink-700" id="fbPending">0</div>
                            <div class="text-[9px] text-pink-500">Pending Calls</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-green-600" id="fbCompletedToday">0</div>
                            <div class="text-[9px] text-green-500">Done Today</div>
                        </div>
                    </div>
                    <div class="max-h-48 overflow-y-auto" id="feedbackList">
                        <div class="text-center py-2 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Online Users Widget (Admin Only) -->
            @if(Auth::user()->role === 'admin')
            <div class="border-b border-gray-200" id="onlineUsersWidget">
                <div class="p-2.5 bg-gradient-to-r from-slate-700 to-gray-800 flex items-center justify-between cursor-pointer" onclick="toggleWidgetBody('onlineUsersBody')">
                    <h3 class="text-xs font-bold text-white flex items-center gap-2">
                        <i class="fas fa-users-cog"></i> User Activity
                    </h3>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] bg-green-500/30 text-green-300 px-1.5 py-0.5 rounded-full font-medium" id="onlineUserCount">0 online</span>
                        <button onclick="event.stopPropagation(); loadOnlineUsers()" class="text-white hover:text-gray-300 text-xs px-1 py-0.5 rounded hover:bg-white/20">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <i class="fas fa-chevron-down text-white/60 text-xs transition-transform" id="onlineUsersBodyIcon"></i>
                    </div>
                </div>
                <div id="onlineUsersBody">
                    <div class="grid grid-cols-3 gap-1 p-2 bg-slate-50 border-b text-center">
                        <div>
                            <div class="text-xs font-bold text-green-600" id="usOnline">0</div>
                            <div class="text-[9px] text-green-500">Online</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-yellow-600" id="usIdle">0</div>
                            <div class="text-[9px] text-yellow-500">Idle</div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400" id="usOffline">0</div>
                            <div class="text-[9px] text-gray-400">Offline</div>
                        </div>
                    </div>
                    <div class="p-2 max-h-60 overflow-y-auto" id="onlineUsersList">
                        <div class="text-center py-2 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Divider -->
            <div class="px-3 py-2 bg-gray-100 border-b">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider"><i class="fas fa-calendar-check mr-1"></i> Bookings & Room Status</p>
            </div>

            <!-- Bookings Section Header -->
            <div class="p-3 border-b border-gray-200" style="background: linear-gradient(to right, #059669, #047857);">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fas fa-calendar-check"></i> <span id="bookingsDateTitle">Today's Bookings</span>
                    </h2>
                    <span id="currentTimeDisplay" class="text-white text-xs font-medium bg-white/20 px-2 py-0.5 rounded"></span>
                </div>
            </div>
        <!-- Time Slider -->
        <div class="px-3 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-gray-600"><i class="fas fa-clock mr-1"></i>View Time:</p>
                <div class="flex items-center gap-2">
                    <span id="selectedTimeDisplay" class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Now</span>
                    <button onclick="resetToCurrentTime()" class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50" title="Reset to current time">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">12AM</span>
                <input type="range" id="timeSlider" min="0" max="1439" step="15" 
                    class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                    style="background: linear-gradient(to right, #6b7280 0%, #059669 50%, #f59e0b 100%);"
                    oninput="handleTimeSliderChange(this.value)">
                <span class="text-xs text-gray-400">11:59PM</span>
            </div>
            <div class="flex justify-between text-[10px] text-gray-400 mt-1 px-6">
                <span>6AM</span>
                <span>12PM</span>
                <span>6PM</span>
            </div>
        </div>
        <!-- Room Status Legend -->
        <div class="px-3 py-2 bg-gray-100 border-b border-gray-200">
            <p class="text-xs font-semibold text-gray-600 mb-1">Floor Plan Legend:</p>
            <div class="flex flex-wrap gap-2 text-xs">
                <div class="flex items-center gap-1">
                    <div class="w-4 h-4 rounded border-2 border-green-500" style="box-shadow: 0 0 0 2px #22c55e;"></div>
                    <span class="text-gray-600">Active</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-4 h-4 rounded opacity-70" style="outline: 2px dashed #6b7280; outline-offset: 1px;"></div>
                    <span class="text-gray-600">Departed</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-4 h-4 rounded border-2 border-green-500 relative" style="box-shadow: 0 0 0 2px #22c55e; outline: 2px dashed #f59e0b; outline-offset: 3px;">
                        <span class="absolute -top-1 -right-1 bg-amber-500 text-white rounded-full text-[6px] w-2 h-2 flex items-center justify-center">⟳</span>
                    </div>
                    <span class="text-gray-600">Turnover</span>
                </div>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="p-3" id="bookingsList">
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p class="text-sm">Loading bookings...</p>
            </div>
        </div>

        </div><!-- end scrollable alerts area -->

        <!-- Footer Stats -->
        <div class="p-3 border-t border-gray-200 bg-gray-50">
            <div class="text-xs text-gray-500 space-y-1">
                <div class="flex justify-between">
                    <span>Total Bookings:</span>
                    <span class="font-medium" id="totalBookings">0</span>
                </div>
                <div class="flex justify-between">
                    <span>Rooms Booked:</span>
                    <span class="font-medium text-green-600" id="totalRoomsBooked">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="bookingDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between" id="modalHeader" style="background: linear-gradient(to right, #059669, #047857);">
            <h3 class="text-lg font-bold text-white" id="modalBookingTitle">Booking Details</h3>
            <button onclick="closeBookingModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4 overflow-y-auto max-h-[70vh]" id="modalBookingContent">
            <!-- Content will be injected here -->
        </div>
        <div class="p-4 border-t bg-gray-50 flex justify-end gap-2">
            <button id="editBookingBtn" onclick="editCurrentBooking()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-1">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button onclick="closeBookingModal()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">
                Close
            </button>
            <button id="printConfirmationBtn" onclick="printBookingConfirmation()" class="px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-1">
                <i class="fas fa-print"></i> Print Confirmation
            </button>
        </div>
    </div>
</div>

<!-- Housekeeping Logs Modal -->
<div class="modal fade" id="housekeepingLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-pink-600 text-white p-3">
                <h5 class="modal-title text-sm font-bold"><i class="fas fa-history mr-2"></i>Housekeeping Status History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="hkLogsContainer" class="p-3">
                    <div class="text-center py-4 text-gray-500 text-xs">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Loading history...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manage Rooms Modal -->
<div class="modal fade" id="manageRoomsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gray-800 text-white p-3">
                <h5 class="modal-title text-sm font-bold"><i class="fas fa-cog mr-2"></i>Manage Rooms</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Add Room Form -->
                <div class="bg-white border border-gray-200 rounded-lg mb-3 overflow-hidden">
                    <div class="bg-blue-600 text-white p-2">
                        <h6 class="text-xs font-bold mb-0"><i class="fas fa-plus mr-2"></i>Add New Room</h6>
                    </div>
                    <div class="p-3">
                        <form id="addRoomForm" onsubmit="addRoom(event)" class="flex gap-2">
                            <input type="text" id="newRoomName" class="form-control text-sm" placeholder="Enter room number or name" required>
                            <button type="submit" class="btn btn-primary text-xs">
                                <i class="fas fa-plus mr-1"></i> Add Room
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Existing Rooms List -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-600 text-white p-2">
                        <h6 class="text-xs font-bold mb-0"><i class="fas fa-list mr-2"></i>Existing Rooms</h6>
                    </div>
                    <div id="roomsListContainer" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center py-4 text-gray-500 text-xs">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading rooms...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Section data based on hotel layout (room names match /calendar)
const sections = [
    // Top row rooms
    { id: 'ahala', name: 'Ahala', type: 'ROOM', top: 3, left: 17, width: 4, height: 6 },
    // ... rest of your code remains the same ...
    { id: 'orchid', name: 'Orchid', type: 'ROOM', top: 8, left: 17, width: 4, height: 6 },
    { id: 'sudu-araliya', name: 'Sudu Araliya', type: 'ROOM', top: 6, left: 30, width: 6, height: 6 },
    { id: 'sepalika', name: 'Sepalika', type: 'ROOM', top: 6, left: 38, width: 6, height: 6 },
    
    // Room numbers 121-124
    { id: 'room-121', name: '121', type: 'ROOM', top: 3, left: 52, width: 3.5, height: 5 },
    { id: 'room-122', name: '122', type: 'ROOM', top: 3, left: 56, width: 3.5, height: 5 },
    { id: 'room-123', name: '123', type: 'ROOM', top: 3, left: 60, width: 3.5, height: 5 },
    { id: 'room-124', name: '124', type: 'ROOM', top: 3, left: 64, width: 3.5, height: 5 },
    
    // Room numbers 106-109
    { id: 'room-109', name: '109', type: 'ROOM', top: 11, left: 52, width: 3.5, height: 5 },
    { id: 'room-108', name: '108', type: 'ROOM', top: 11, left: 56, width: 3.5, height: 5 },
    { id: 'room-107', name: '107', type: 'ROOM', top: 11, left: 60, width: 3.5, height: 5 },
    { id: 'room-106', name: '106', type: 'ROOM', top: 11, left: 64, width: 3.5, height: 5 },
    
    // Hansa
    { id: 'hansa', name: 'Hansa', type: 'ROOM', top: 6, left: 88, width: 6, height: 8 },
    
    // Olu & Nelum
    { id: 'olu', name: 'Olu', type: 'ROOM', top: 18, left: 22, width: 6, height: 6 },
    { id: 'nelum', name: 'Nelum', type: 'ROOM', top: 24, left: 22, width: 6, height: 6 },
    
    // Kitchen-1
    { id: 'kitchen-1', name: 'Kitchen-1', type: 'KITCHEN', top: 18, left: 56, width: 8, height: 12 },
    
    // Villa
    { id: 'villa', name: 'Villa', type: 'ROOM', top: 18, left: 72, width: 10, height: 12 },
    
    // Maintenance Work
    { id: 'maintenance', name: 'Maintenance', type: 'MAINTENANCE', top: 18, left: 1, width: 10, height: 12 },
    
    // Swimming Pool
    { id: 'swimming-pool', name: 'Swimming Pool', type: 'POOL', top: 34, left: 14, width: 18, height: 10 },
    
    // Hut-1
    { id: 'hut-1', name: 'Hut-1', type: 'ROOM', top: 34, left: 44, width: 5, height: 6 },
    
    // Main Restaurant
    { id: 'main-restaurant', name: 'Main Restaurant', type: 'DINING', top: 34, left: 56, width: 14, height: 14 },
    
    // Hut-3 & Hut-2
    { id: 'hut-3', name: 'Hut-3', type: 'ROOM', top: 50, left: 16, width: 5, height: 6 },
    { id: 'hut-2', name: 'Hut-2', type: 'ROOM', top: 50, left: 28, width: 5, height: 6 },
    
    // Room numbers 130-134
    { id: 'room-130', name: '130', type: 'ROOM', top: 60, left: 1, width: 3.5, height: 5 },
    { id: 'room-131', name: '131', type: 'ROOM', top: 60, left: 5, width: 3.5, height: 5 },
    { id: 'room-132', name: '132', type: 'ROOM', top: 60, left: 9, width: 3.5, height: 5 },
    { id: 'room-133', name: '133', type: 'ROOM', top: 60, left: 13, width: 3.5, height: 5 },
    { id: 'room-134', name: '134', type: 'ROOM', top: 60, left: 17, width: 3.5, height: 5 },
    
    // Orchid Hall
    { id: 'orchid-hall', name: 'Orchid Hall', type: 'HALL', top: 60, left: 86, width: 10, height: 18 },
    
    // Kitchen-2
    { id: 'kitchen-2', name: 'Kitchen-2', type: 'KITCHEN', top: 70, left: 1, width: 4, height: 16 },
    
    // Banquet Hall
    { id: 'banquet-hall', name: 'Banquet Hall', type: 'HALL', top: 70, left: 6, width: 18, height: 16 },
    
    // CH Room
    { id: 'ch-room', name: 'CH Room', type: 'ROOM', top: 72, left: 26, width: 6, height: 6 },
    
    // Lihini & Mayura (side by side)
    { id: 'lihini', name: 'Lihini', type: 'ROOM', top: 70, left: 34, width: 5, height: 6 },
    { id: 'mayura', name: 'Mayura', type: 'ROOM', top: 70, left: 41, width: 6, height: 6 },
    
    // Front Office
    { id: 'front-office', name: 'Front Office', type: 'OFFICE', top: 64, left: 52, width: 24, height: 12 },
    
    // Garden Quadrants (4 zones covering the whole property)
    { id: 'garden-a', name: 'Garden A', type: 'GARDEN', top: 1, left: 1, width: 48, height: 48 },
    { id: 'garden-b', name: 'Garden B', type: 'GARDEN', top: 1, left: 51, width: 48, height: 48 },
    { id: 'garden-c', name: 'Garden C', type: 'GARDEN', top: 51, left: 51, width: 48, height: 48 },
    { id: 'garden-d', name: 'Garden D', type: 'GARDEN', top: 51, left: 1, width: 48, height: 48 },
];

const sectionColors = {
    ROOM: { bg: 'bg-slate-600', label: 'text-slate-700' },
    KITCHEN: { bg: 'bg-blue-500', label: 'text-blue-600' },
    DINING: { bg: 'bg-orange-500', label: 'text-orange-600' },
    HALL: { bg: 'bg-purple-500', label: 'text-purple-600' },
    OFFICE: { bg: 'bg-green-500', label: 'text-green-600' },
    POOL: { bg: 'bg-cyan-400', label: 'text-cyan-600' },
    GARDEN: { bg: 'bg-lime-500', label: 'text-lime-700' },
    MAINTENANCE: { bg: 'bg-yellow-600', label: 'text-yellow-700' },
};

// State
let assignments = {}; // { staffId: sectionId }
let draggedStaffId = null;

// State for staff on leave
let staffOnLeave = [];

// State for time slider - stores the selected viewing time (null = current time)
let selectedViewTime = null;
let cachedBookings = []; // Store loaded bookings for re-rendering with different times

// Get the effective viewing time (slider time or current time)
function getViewingTime() {
    if (selectedViewTime !== null) {
        // Create a date object for today with the selected time
        const today = new Date(currentDate);
        const hours = Math.floor(selectedViewTime / 60);
        const minutes = selectedViewTime % 60;
        today.setHours(hours, minutes, 0, 0);
        return today;
    }
    return new Date();
}

// Convert minutes since midnight to formatted time string
function formatSliderTime(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours === 0 ? 12 : (hours > 12 ? hours - 12 : hours);
    return `${displayHours}:${mins.toString().padStart(2, '0')} ${period}`;
}

// Handle time slider change
function handleTimeSliderChange(value) {
    const minutes = parseInt(value);
    selectedViewTime = minutes;
    
    // Update the display
    const display = document.getElementById('selectedTimeDisplay');
    if (display) {
        display.textContent = formatSliderTime(minutes);
        display.classList.remove('text-emerald-600', 'bg-emerald-50');
        display.classList.add('text-amber-600', 'bg-amber-50');
    }
    
    // Re-render bookings and floor plan with the new time
    if (cachedBookings.length > 0) {
        renderBookings(cachedBookings);
        highlightBookedRooms(cachedBookings);
        renderBookingTimeline(cachedBookings);
    }
}

// Reset to current time
function resetToCurrentTime() {
    selectedViewTime = null;
    
    // Reset slider to current time
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    const slider = document.getElementById('timeSlider');
    if (slider) {
        slider.value = currentMinutes;
    }
    
    // Update display
    const display = document.getElementById('selectedTimeDisplay');
    if (display) {
        display.textContent = 'Now';
        display.classList.remove('text-amber-600', 'bg-amber-50');
        display.classList.add('text-emerald-600', 'bg-emerald-50');
    }
    
    // Re-render with current time
    if (cachedBookings.length > 0) {
        renderBookings(cachedBookings);
        highlightBookedRooms(cachedBookings);
        renderBookingTimeline(cachedBookings);
    }
}

// Initialize time slider to current time
function initTimeSlider() {
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    const slider = document.getElementById('timeSlider');
    if (slider) {
        slider.value = currentMinutes;
    }
}

// Update current time display
function updateCurrentTime() {
    const timeDisplay = document.getElementById('currentTimeDisplay');
    if (timeDisplay) {
        const now = new Date();
        timeDisplay.textContent = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
    }
    
    // If viewing current time (not using slider), update slider position too
    if (selectedViewTime === null) {
        const now = new Date();
        const currentMinutes = now.getHours() * 60 + now.getMinutes();
        const slider = document.getElementById('timeSlider');
        if (slider) {
            slider.value = currentMinutes;
        }
    }
}

// ============ BOOKING TIMELINE VIEW ============
function renderBookingTimeline(bookings) {
    const timeline = document.getElementById('bookingTimeline');
    if (!timeline || bookings.length === 0) {
        timeline.innerHTML = '<div class="text-center py-3 text-gray-400 text-xs">No bookings to display</div>';
        return;
    }
    
    const viewTime = getViewingTime();
    
    // Time labels for full 24 hours
    const timeLabels = [
        { hour: 0, label: '12AM' },
        { hour: 3, label: '3AM' },
        { hour: 6, label: '6AM' },
        { hour: 9, label: '9AM' },
        { hour: 12, label: '12PM' },
        { hour: 15, label: '3PM' },
        { hour: 18, label: '6PM' },
        { hour: 21, label: '9PM' },
        { hour: 24, label: '12AM' }
    ];
    
    // Create timeline HTML
    let html = `
        <div class="relative" style="min-width: 600px;">
            <!-- Time axis -->
            <div class="flex border-b border-gray-200 mb-1">
                ${timeLabels.map((t, i) => `
                    <div class="flex-1 text-[9px] text-gray-400 text-center border-l border-gray-100" style="min-width: 60px;">
                        ${i < timeLabels.length - 1 ? t.label : ''}
                    </div>
                `).join('')}
            </div>
            <!-- Current time indicator -->
            <div class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-10" style="left: ${(viewTime.getHours() * 60 + viewTime.getMinutes()) / 1440 * 100}%;">
                <div class="absolute -top-1 -left-1 w-2 h-2 bg-red-500 rounded-full"></div>
            </div>
            <!-- Bookings -->
            <div class="relative space-y-1 py-1">
    `;
    
    // Get the selected date for comparison
    const selectedDateStr = currentDate;
    const selectedDate = new Date(selectedDateStr);
    selectedDate.setHours(0, 0, 0, 0);
    
    bookings.forEach((booking, index) => {
        const checkIn = booking.start ? new Date(booking.start) : null;
        const checkOut = booking.end ? new Date(booking.end) : null;
        if (!checkIn) return;
        
        // Get just the date parts for comparison
        const checkInDate = new Date(checkIn);
        checkInDate.setHours(0, 0, 0, 0);
        const checkOutDate = checkOut ? new Date(checkOut) : null;
        if (checkOutDate) checkOutDate.setHours(0, 0, 0, 0);
        
        // Calculate start and end minutes for the SELECTED day
        let startMinutes, endMinutes;
        
        // If booking starts before selected date, start from midnight (0)
        if (checkInDate < selectedDate) {
            startMinutes = 0;
        } else {
            startMinutes = checkIn.getHours() * 60 + checkIn.getMinutes();
        }
        
        // If booking ends after selected date (or on a different day), end at midnight (1439)
        if (!checkOut || (checkOutDate && checkOutDate > selectedDate)) {
            endMinutes = 1439;
        } else {
            endMinutes = checkOut.getHours() * 60 + checkOut.getMinutes();
        }
        
        // Handle edge case: if start and end are same time but different days
        if (endMinutes <= startMinutes && checkOutDate && checkOutDate > checkInDate) {
            endMinutes = 1439; // Extend to end of day
        }
        
        const startPercent = (startMinutes / 1440) * 100;
        const widthPercent = ((endMinutes - startMinutes) / 1440) * 100;
        
        const color = getBookingColor(booking);
        const isDeparted = checkOut && viewTime > checkOut;
        const opacity = isDeparted ? '0.5' : '1';
        
        // Get room count
        let rooms = [];
        try {
            rooms = JSON.parse(booking.room_numbers);
            if (!Array.isArray(rooms)) rooms = [rooms];
        } catch (e) {
            rooms = booking.room_numbers ? [booking.room_numbers] : [];
        }
        
        // Determine if this is a multi-day booking
        const isMultiDay = checkOutDate && checkOutDate.getTime() !== checkInDate.getTime();
        const dayIndicator = isMultiDay ? '↔' : '';
        
        html += `
            <div class="relative h-5 group" style="opacity: ${opacity};">
                <div class="absolute h-full rounded-sm flex items-center px-1 overflow-hidden cursor-pointer hover:opacity-80 transition-opacity"
                    style="left: ${startPercent}%; width: ${Math.max(widthPercent, 5)}%; background-color: ${color};"
                    onclick='showBookingDetails(${JSON.stringify(booking).replace(/'/g, "&#39;")}, "${color}")'
                    title="${booking.name || 'Guest'} - ${rooms.length} room(s)${isMultiDay ? ' (Multi-day)' : ''}">
                    <span class="text-[8px] text-white font-medium truncate">${dayIndicator}${booking.name || 'Guest'} (${rooms.length})</span>
                </div>
            </div>
        `;
    });
    
    html += '</div></div>';
    timeline.innerHTML = html;
}

// ============ MINI CALENDAR WIDGET ============
let calendarMonth = new Date().getMonth();
let calendarYear = new Date().getFullYear();
let allBookingsForCalendar = [];

function changeCalendarMonth(delta) {
    calendarMonth += delta;
    if (calendarMonth > 11) {
        calendarMonth = 0;
        calendarYear++;
    } else if (calendarMonth < 0) {
        calendarMonth = 11;
        calendarYear--;
    }
    renderMiniCalendar();
}

function renderMiniCalendar() {
    const calendar = document.getElementById('miniCalendar');
    const monthLabel = document.getElementById('miniCalendarMonth');
    if (!calendar) return;
    
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    monthLabel.textContent = `${monthNames[calendarMonth]} ${calendarYear}`;
    
    const firstDay = new Date(calendarYear, calendarMonth, 1);
    const lastDay = new Date(calendarYear, calendarMonth + 1, 0);
    const startDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    const selectedDateStr = currentDate;
    
    // Get booking counts per day
    const bookingCounts = {};
    allBookingsForCalendar.forEach(booking => {
        const start = new Date(booking.start);
        const end = booking.end ? new Date(booking.end) : start;
        
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dateStr = d.toISOString().split('T')[0];
            bookingCounts[dateStr] = (bookingCounts[dateStr] || 0) + 1;
        }
    });
    
    // Day headers
    let html = ['S','M','T','W','T','F','S'].map(d => 
        `<div class="text-[9px] text-gray-400 font-medium py-0.5">${d}</div>`
    ).join('');
    
    // Empty cells before first day
    for (let i = 0; i < startDayOfWeek; i++) {
        html += '<div></div>';
    }
    
    // Days
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${calendarYear}-${String(calendarMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = dateStr === todayStr;
        const isSelected = dateStr === selectedDateStr;
        const bookingCount = bookingCounts[dateStr] || 0;
        
        let bgClass = 'bg-transparent hover:bg-gray-100';
        let textClass = 'text-gray-600';
        let dotHtml = '';
        
        if (isSelected) {
            bgClass = 'bg-emerald-500';
            textClass = 'text-white';
        } else if (isToday) {
            bgClass = 'bg-blue-100';
            textClass = 'text-blue-700 font-bold';
        }
        
        if (bookingCount > 0 && !isSelected) {
            const dotColor = bookingCount >= 3 ? 'bg-red-500' : bookingCount >= 2 ? 'bg-amber-500' : 'bg-green-500';
            dotHtml = `<div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full ${dotColor}"></div>`;
        }
        
        html += `
            <div class="relative cursor-pointer rounded ${bgClass} ${textClass} text-[10px] py-1 transition-colors"
                onclick="selectCalendarDate('${dateStr}')"
                title="${bookingCount} booking(s)">
                ${day}
                ${dotHtml}
            </div>
        `;
    }
    
    calendar.innerHTML = html;
}

function selectCalendarDate(dateStr) {
    document.getElementById('allocationDate').value = dateStr;
    handleDateChange(dateStr);
    renderMiniCalendar();
}

async function loadAllBookingsForCalendar() {
    try {
        const response = await fetch('/bookings');
        allBookingsForCalendar = await response.json();
        renderMiniCalendar();
    } catch (error) {
        console.error('Error loading bookings for calendar:', error);
    }
}

// ============ ROOM HOVER DETAILS ============
let roomTooltip = null;

function showRoomTooltip(element, roomName) {
    hideRoomTooltip();
    
    // Find booking for this room
    const booking = cachedBookings.find(b => {
        let rooms = [];
        try {
            rooms = JSON.parse(b.room_numbers);
            if (!Array.isArray(rooms)) rooms = [rooms];
        } catch (e) {
            rooms = b.room_numbers ? [b.room_numbers] : [];
        }
        return rooms.map(r => r.trim()).includes(roomName);
    });
    
    if (!booking) return;
    
    const viewTime = getViewingTime();
    const checkOut = booking.end ? new Date(booking.end) : null;
    const isDeparted = checkOut && viewTime > checkOut;
    const status = isDeparted ? 'Departed' : 'Active';
    const statusColor = isDeparted ? '#6b7280' : '#22c55e';
    
    const checkIn = booking.start ? new Date(booking.start) : null;
    const formatTime = (date) => date ? date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : 'N/A';
    
    roomTooltip = document.createElement('div');
    roomTooltip.className = 'fixed z-50 bg-white rounded-lg shadow-xl border border-gray-200 p-3 max-w-xs';
    roomTooltip.innerHTML = `
        <div class="flex items-center gap-2 mb-2 pb-2 border-b">
            <div class="w-2 h-2 rounded-full" style="background-color: ${statusColor};"></div>
            <span class="font-bold text-gray-800">${roomName}</span>
            <span class="text-xs px-1.5 py-0.5 rounded text-white ml-auto" style="background-color: ${statusColor};">${status}</span>
        </div>
        <div class="space-y-1 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-500">Guest:</span>
                <span class="font-medium text-gray-800">${booking.name || 'N/A'}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Type:</span>
                <span class="font-medium text-gray-800">${booking.function_type || 'N/A'}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Check In:</span>
                <span class="font-medium text-green-600">${formatTime(checkIn)}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Check Out:</span>
                <span class="font-medium text-red-600">${formatTime(checkOut)}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Guests:</span>
                <span class="font-medium text-gray-800">${booking.guest_count || 'N/A'}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Contact:</span>
                <span class="font-medium text-gray-800">${booking.contact_number || 'N/A'}</span>
            </div>
        </div>
    `;
    
    document.body.appendChild(roomTooltip);
    
    const rect = element.getBoundingClientRect();
    const tooltipRect = roomTooltip.getBoundingClientRect();
    
    let left = rect.left + rect.width / 2 - tooltipRect.width / 2;
    let top = rect.top - tooltipRect.height - 10;
    
    // Keep within viewport
    if (left < 10) left = 10;
    if (left + tooltipRect.width > window.innerWidth - 10) left = window.innerWidth - tooltipRect.width - 10;
    if (top < 10) top = rect.bottom + 10;
    
    roomTooltip.style.left = left + 'px';
    roomTooltip.style.top = top + 'px';
}

function hideRoomTooltip() {
    if (roomTooltip) {
        roomTooltip.remove();
        roomTooltip = null;
    }
}

// ============ TODAY'S BILLS REPORT ============
let currentBillsDate = '{{ date("Y-m-d") }}';

async function loadTodayBills(date = null) {
    if (date) {
        currentBillsDate = date;
    }
    
    try {
        const url = `/api/duty-roster/today-bills?date=${currentBillsDate}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Update key metrics
            document.getElementById('totalSales').textContent = `Rs ${parseFloat(data.total_sale).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('billsCount').textContent = data.total_bills;
            const avgBill = data.total_bills > 0 ? data.total_sale / data.total_bills : 0;
            document.getElementById('avgBill').textContent = `Rs ${parseFloat(avgBill).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            // Update time range
            const startTime = new Date(data.period_start).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            const endTime = new Date(data.period_end).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            document.getElementById('billsTimeRange').textContent = `${startTime} - ${endTime}`;
            
            // Render hourly chart
            renderHourlyChart(data.hourly_breakdown);
            
            // Render recent bills
            renderRecentBills(data.recent_bills);
        }
    } catch (error) {
        console.error('Error loading bills report:', error);
    }
}

function renderHourlyChart(hourlyData) {
    const chart = document.getElementById('hourlyChart');
    if (!hourlyData || hourlyData.length === 0) {
        chart.innerHTML = '<div class="text-center text-gray-400 text-xs py-4">No data available</div>';
        return;
    }
    
    const maxAmount = Math.max(...hourlyData.map(h => parseFloat(h.total_amount)));
    const currentHour = new Date().getHours();
    
    let html = '';
    for (let hour = 0; hour < 24; hour++) {
        const hourData = hourlyData.find(h => parseInt(h.hour) === hour);
        const amount = hourData ? parseFloat(hourData.total_amount) : 0;
        const height = maxAmount > 0 ? (amount / maxAmount) * 100 : 0;
        const isCurrentHour = hour === currentHour;
        const barColor = isCurrentHour ? '#059669' : '#d1d5db';
        
        html += `
            <div class="flex-1 flex flex-col items-center justify-end group relative" style="min-width: 8px;">
                <div class="w-full rounded-t transition-all" 
                     style="height: ${height}%; background-color: ${barColor}; min-height: ${amount > 0 ? '2px' : '0'};"
                     title="${hour}:00 - Rs ${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}">
                </div>
                ${hour % 3 === 0 ? `<div class="text-[8px] text-gray-400 mt-1">${hour}</div>` : ''}
            </div>
        `;
    }
    chart.innerHTML = html;
}

function renderTopItems(items) {
    const list = document.getElementById('topItemsList');
    if (!items || items.length === 0) {
        list.innerHTML = '<div class="text-center text-gray-400 text-xs py-2">No items sold yet</div>';
        return;
    }
    
    let html = '';
    items.forEach((item, index) => {
        const revenue = parseFloat(item.total_revenue);
        html += `
            <div class="flex items-center justify-between p-2 rounded hover:bg-gray-50 text-xs">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] font-bold">${index + 1}</span>
                    <span class="font-medium text-gray-800 truncate">${item.menu_name}</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-gray-500">×${item.total_qty}</span>
                    <span class="font-bold text-emerald-600">Rs ${revenue.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
            </div>
        `;
    });
    list.innerHTML = html;
}

function renderRecentBills(bills) {
    const list = document.getElementById('recentBillsList');
    if (!bills || bills.length === 0) {
        list.innerHTML = '<div class="text-center text-gray-400 text-xs py-2">No bills yet</div>';
        return;
    }
    
    let html = '';
    bills.forEach(bill => {
        const time = new Date(bill.updated_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        const amount = parseFloat(bill.total_price) + parseFloat(bill.total_recieved || 0);
        const billId = `bill-content-${bill.id}`;
        
        // Get bill items
        let itemsHtml = '';
        if (bill.sale_details && bill.sale_details.length > 0) {
            bill.sale_details.forEach(item => {
                const itemTotal = parseFloat(item.menu_price) * parseInt(item.quantity);
                // Format the updated time for each item
                const itemUpdatedTime = item.created_at ? new Date(item.created_at).toLocaleString('en-GB', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: false 
                }).replace(',', '') : 'N/A';
                itemsHtml += `
                    <div class="flex justify-between items-center text-[11px] text-gray-600 py-1 border-b border-gray-100 last:border-0">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="font-medium text-gray-800 truncate">${item.menu_name}</span>
                            <span class="text-gray-400 text-[10px]">× ${item.quantity}</span>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="text-[10px] text-blue-600" title="Updated Time"><i class="fas fa-clock mr-1"></i>${itemUpdatedTime}</span>
                            <span class="font-medium">Rs ${itemTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                        </div>
                    </div>
                `;
            });
        } else {
            itemsHtml = '<div class="text-center text-gray-400 text-[10px] py-1">No items found</div>';
        }
        
        html += `
            <div class="border border-gray-200 rounded-lg overflow-hidden mb-2 transition-shadow hover:shadow-sm">
                <div class="flex items-center justify-between p-3 bg-white cursor-pointer hover:bg-gray-50 transition-colors" onclick="toggleBillDetails('${billId}')">
                    <div class="flex flex-col gap-0.5">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform duration-200" id="${billId}-icon"></i>
                            <span class="font-bold text-gray-800 text-sm">#${bill.id}</span>
                        </div>
                        <span class="text-gray-500 text-[10px] pl-5 flex items-center gap-1">
                            <span>${time}</span>
                            <span>•</span>
                            <span>${bill.table_name || 'Table N/A'}</span>
                            <span>•</span>
                            <span>${bill.user_name || 'N/A'}</span>
                        </span>
                    </div>
                    <div class="text-right pl-2">
                        <div class="font-bold text-emerald-600 text-sm">Rs ${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                    </div>
                </div>
                <div id="${billId}" class="hidden bg-gray-50 border-t border-gray-100 px-3 py-2 transition-all duration-300">
                    <div class="pl-2">
                        ${itemsHtml}
                    </div>
                </div>
            </div>
        `;
    });
    list.innerHTML = html;
}

function toggleBillDetails(billId) {
    const content = document.getElementById(billId);
    const icon = document.getElementById(`${billId}-icon`);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
    } else {
        content.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function refreshBillsReport() {
    loadTodayBills();
}

function loadBillsForDate(date) {
    currentBillsDate = date;
    document.getElementById('billsDatePicker').value = date;
    loadTodayBills(date);
}

function loadBillsForToday() {
    const today = new Date().toISOString().split('T')[0];
    loadBillsForDate(today);
}

function loadBillsForYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    loadBillsForDate(yesterdayStr);
}

// ============ DAILY COSTS REPORT ============
let currentCostsDate = '{{ date("Y-m-d") }}';

async function loadDailyCosts(date = null) {
    if (date) {
        currentCostsDate = date;
    }
    
    try {
        const url = `/api/duty-roster/daily-costs?date=${currentCostsDate}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Update key metrics
            document.getElementById('totalCosts').textContent = `Rs ${parseFloat(data.total_costs).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('costsCount').textContent = data.total_transactions;
            
            // Render categorized costs (collapsible)
            renderCategorizedCosts(data.category_breakdown);
        }
    } catch (error) {
        console.error('Error loading costs report:', error);
    }
}

function renderCategorizedCosts(categories) {
    const list = document.getElementById('categorizedCostsList');
    if (!categories || categories.length === 0) {
        list.innerHTML = '<div class="text-center text-gray-400 text-xs py-2">No expenses yet</div>';
        return;
    }
    
    let html = '';
    categories.forEach((category, index) => {
        const total = parseFloat(category.total);
        const percentage = categories.reduce((sum, c) => sum + parseFloat(c.total), 0);
        const percent = percentage > 0 ? ((total / percentage) * 100).toFixed(1) : 0;
        const categoryId = `category-${index}`;
        
        html += `
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="flex items-center justify-between p-3 cursor-pointer hover:bg-gray-50 transition-colors" onclick="toggleCostCategory('${categoryId}')">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform" id="${categoryId}-icon"></i>
                        <span class="font-bold text-gray-800 text-sm">${category.name}</span>
                        <span class="text-[10px] text-gray-500">${category.count} transaction${category.count !== 1 ? 's' : ''}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-gray-500">${percent}%</span>
                        <span class="font-bold text-red-600 text-sm">Rs ${total.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                    </div>
                </div>
                <div id="${categoryId}" class="hidden bg-gray-50 border-t border-gray-200">
                    ${category.items && category.items.length > 0 ? `
                        <div class="divide-y divide-gray-200">
                            ${category.items.map(item => `
                                <div class="p-3 hover:bg-white transition-colors">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-gray-700 text-xs">${item.person}</span>
                                        <span class="font-bold text-red-600 text-sm">Rs ${parseFloat(item.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    ${item.description ? `
                                        <div class="text-[10px] text-gray-600 mb-1">${item.description}</div>
                                    ` : ''}
                                    <div class="text-[10px] text-gray-500">${item.time} • ${item.user}</div>
                                </div>
                            `).join('')}
                        </div>
                    ` : '<div class="p-3 text-center text-gray-400 text-xs">No items</div>'}
                </div>
            </div>
        `;
    });
    list.innerHTML = html;
}

function toggleCostCategory(categoryId) {
    const content = document.getElementById(categoryId);
    const icon = document.getElementById(`${categoryId}-icon`);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
    } else {
        content.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function refreshCostsReport() {
    loadDailyCosts();
}

function loadCostsForDate(date) {
    currentCostsDate = date;
    document.getElementById('costsDatePicker').value = date;
    loadDailyCosts(date);
}

function loadCostsForToday() {
    const today = new Date().toISOString().split('T')[0];
    loadCostsForDate(today);
}

function loadCostsForYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    loadCostsForDate(yesterdayStr);
}

// ============ DAILY VEHICLE SECURITY SUMMARY ============
let currentVehicleSummaryDate = '{{ date("Y-m-d") }}';

function renderVehiclesByPurpose(matters) {
    const list = document.getElementById('vehiclesByPurposeList');
    if (!matters || matters.length === 0) {
        list.innerHTML = '<div class="text-center text-gray-400 text-xs py-2">No vehicles yet</div>';
        return;
    }
    
    let html = '';
    matters.forEach((matter, index) => {
        const matterId = `vehicle-matter-${index}`;
        
        html += `
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="flex items-center justify-between p-3 cursor-pointer hover:bg-gray-50 transition-colors" onclick="toggleVehicleMatter('${matterId}')">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform" id="${matterId}-icon"></i>
                        <span class="font-bold text-gray-800 text-sm">${matter.name || 'Unknown'}</span>
                        <span class="text-[10px] text-gray-500">${matter.count} vehicle${matter.count !== 1 ? 's' : ''}</span>
                    </div>
                </div>
                <div id="${matterId}" class="hidden bg-gray-50 border-t border-gray-200">
                    ${matter.vehicles && matter.vehicles.length > 0 ? `
                        <div class="divide-y divide-gray-200">
                            ${matter.vehicles.map(vehicle => `
                                <div class="p-3 hover:bg-white transition-colors">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-blue-600 text-sm">${vehicle.vehicle_number}</span>
                                        <span class="text-xs px-2 py-0.5 rounded ${
                                            vehicle.status === 'Checked Out' ? 'bg-gray-200 text-gray-700' : 
                                            (vehicle.status === 'Temp Out' ? 'bg-yellow-100 text-yellow-700' : 
                                            (vehicle.status === 'Note' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700'))
                                        }">${vehicle.status}</span>
                                    </div>
                                    ${vehicle.description ? `
                                        <div class="text-[10px] text-gray-600 mb-1">${vehicle.description}</div>
                                    ` : ''}
                                    <div class="flex flex-wrap gap-2 text-[10px] text-gray-500">
                                        <span><i class="fas fa-sign-in-alt mr-1"></i>In: ${vehicle.check_in || vehicle.time}</span>
                                        ${vehicle.check_out ? `<span><i class="fas fa-sign-out-alt mr-1"></i>Out: ${vehicle.check_out}</span>` : ''}
                                        ${(() => {
                                            let rooms = [];
                                            if (Array.isArray(vehicle.room_numbers)) {
                                                rooms = vehicle.room_numbers;
                                            } else if (typeof vehicle.room_numbers === 'string') {
                                                try {
                                                    rooms = JSON.parse(vehicle.room_numbers);
                                                } catch (e) {
                                                    rooms = [vehicle.room_numbers];
                                                }
                                            }
                                            return rooms && rooms.length > 0 ? 
                                                `<span><i class="fas fa-bed mr-1"></i>${rooms.join(', ')}</span>` : '';
                                        })()}
                                        ${vehicle.adult_pool_count > 0 || vehicle.kids_pool_count > 0 ? `
                                            <span><i class="fas fa-swimming-pool mr-1"></i>A:${vehicle.adult_pool_count} K:${vehicle.kids_pool_count}</span>
                                        ` : ''}
                                        ${vehicle.team ? `
                                            <span><i class="fas fa-users mr-1"></i>${vehicle.team}</span>
                                        ` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : '<div class="p-3 text-center text-gray-400 text-xs">No vehicles</div>'}
                </div>
            </div>
        `;
    });
    list.innerHTML = html;
}

function toggleVehicleMatter(matterId) {
    const content = document.getElementById(matterId);
    const icon = document.getElementById(`${matterId}-icon`);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
    } else {
        content.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

async function loadVehicleSummary(date = null) {
    if (date) {
        currentVehicleSummaryDate = date;
    }
    
    try {
        const url = `/vehicle-security/daily-summary?date=${currentVehicleSummaryDate}`;
        console.log('Fetching vehicle summary from:', url);
        const response = await fetch(url);
        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Vehicle summary data:', result);
        
        if (result.success) {
            const data = result.data;
            
            // Update key metrics
            document.getElementById('vehicleTotalCount').textContent = data.total_vehicles;
            document.getElementById('vehicleCheckedIn').textContent = data.checked_in;
            document.getElementById('vehiclePoolUsage').textContent = data.pool_usage.adults + data.pool_usage.kids;
            document.getElementById('vehicleCheckedOut').textContent = data.checked_out;
            
            // Render vehicles by purpose
            renderVehiclesByPurpose(data.by_matter);
        } else {
            console.error('API returned success: false');
            document.getElementById('vehiclesByPurposeList').innerHTML = '<div class="text-center text-red-500 text-xs py-2">Failed to load data</div>';
        }
    } catch (error) {
        console.error('Error loading vehicle summary:', error);
        document.getElementById('vehiclesByPurposeList').innerHTML = '<div class="text-center text-red-500 text-xs py-2">Error loading data</div>';
    }
}

function refreshVehicleSummary() {
    loadVehicleSummary();
}

function loadVehicleSummaryForDate(date) {
    currentVehicleSummaryDate = date;
    document.getElementById('vehicleSummaryDatePicker').value = date;
    loadVehicleSummary(date);
}

function loadVehicleSummaryForToday() {
    const today = new Date().toISOString().split('T')[0];
    loadVehicleSummaryForDate(today);
}

function loadVehicleSummaryForYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    loadVehicleSummaryForDate(yesterdayStr);
}

// ============ INVENTORY CHANGES REPORT ============
let currentInventoryDate = '{{ date("Y-m-d") }}';

async function loadInventoryReport(date = null) {
    if (date) {
        currentInventoryDate = date;
    }
    
    try {
        const url = `/api/duty-roster/inventory-changes?date=${currentInventoryDate}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            const summary = data.summary;
            
            // Update key metrics
            document.getElementById('invTotalChanges').textContent = summary.total_changes;
            document.getElementById('invItemsAdded').textContent = summary.items_added;
            document.getElementById('invItemsRemoved').textContent = summary.items_removed;
            
            // Render inventory list
            renderInventoryList(data.grouped_changes);
        }
    } catch (error) {
        console.error('Error loading inventory report:', error);
        document.getElementById('inventoryList').innerHTML = '<div class="text-center text-red-500 text-xs py-2">Error loading data</div>';
    }
}

function renderInventoryList(groupedChanges) {
    const list = document.getElementById('inventoryList');
    const costSummary = document.getElementById('inventoryCostSummary');
    
    if (!groupedChanges || groupedChanges.length === 0) {
        list.innerHTML = '<div class="text-center text-gray-400 text-xs py-4">No inventory changes</div>';
        costSummary.classList.add('hidden');
        return;
    }
    
    let totalCostAdded = 0;
    let totalCostUsed = 0;
    
    let html = '<div class="accordion" id="inventoryAccordion">';
    groupedChanges.forEach((group, index) => {
        const groupId = `inv_group_${index}`;
        
        // Calculate category cost
        let categoryCostUsed = 0;
        group.items.forEach(log => {
            const cost = log.cost || 0;
            if (log.type === 'added') {
                totalCostAdded += cost;
            } else {
                totalCostUsed += cost;
                categoryCostUsed += cost;
            }
        });
        
        const costBadge = categoryCostUsed > 0 ? 
            `<span class="badge bg-danger ms-2">Rs ${categoryCostUsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>` : '';
        
        html += `
            <div class="accordion-item border mb-2 rounded">
                <div class="accordion-header bg-light p-2 rounded-top d-flex justify-content-between align-items-center" 
                     style="cursor: pointer;" 
                     onclick="toggleInvAccordion('${groupId}')">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-right me-2 text-muted" id="icon_${groupId}" style="transition: transform 0.2s;"></i>
                        <strong class="small">${group.name}</strong>
                    </div>
                    <div>
                        <span class="badge bg-secondary me-1">${group.count} items</span>
                        ${costBadge}
                    </div>
                </div>
                <div id="collapse_${groupId}" class="border-top" style="display: none;">
                    <div class="p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Item</th>
                                        <th>Action</th>
                                        <th>Location</th>
                                        <th>Qty</th>
                                        <th>Cost</th>
                                        <th>Stock</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${group.items.map(log => {
                                        const isAdded = log.type === 'added';
                                        const itemCost = log.cost || 0;
                                        const costDisplay = itemCost > 0 ? 
                                            `<span class="${isAdded ? 'text-success' : 'text-danger'}">Rs ${itemCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>` : 
                                            '<span class="text-muted">-</span>';
                                        const locationBadge = getLocationBadge(log.action);
                                        
                                        return `
                                            <tr>
                                                <td>${log.time}</td>
                                                <td><strong>${log.item_name}</strong></td>
                                                <td>
                                                    ${isAdded ? 
                                                        '<span class="badge bg-success"><i class="fas fa-plus"></i></span>' : 
                                                        '<span class="badge bg-danger"><i class="fas fa-minus"></i></span>'}
                                                </td>
                                                <td>${locationBadge}</td>
                                                <td class="${isAdded ? 'text-success' : 'text-danger'} fw-bold">
                                                    ${isAdded ? '+' : '-'}${Math.abs(log.quantity)}
                                                </td>
                                                <td>${costDisplay}</td>
                                                <td>${log.current_stock}</td>
                                                <td><small>${log.user}</small></td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    list.innerHTML = html;
    
    // Update cost summary
    document.getElementById('invCostAdded').textContent = 'Rs ' + totalCostAdded.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('invCostUsed').textContent = 'Rs ' + totalCostUsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('invTotalDailyCost').textContent = 'Rs ' + totalCostUsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Show cost summary
    costSummary.classList.remove('hidden');
}

function getLocationBadge(action) {
    switch(action) {
        case 'add': return '<span class="badge bg-success">Stock</span>';
        case 'remove_main_kitchen': return '<span class="badge bg-primary">Main Kitchen</span>';
        case 'remove_banquet_hall_kitchen': return '<span class="badge bg-info">Banquet Kitchen</span>';
        case 'remove_banquet_hall': return '<span class="badge bg-warning text-dark">Banquet Hall</span>';
        case 'remove_restaurant': return '<span class="badge bg-success">Restaurant</span>';
        case 'remove_rooms': return '<span class="badge bg-secondary">Rooms</span>';
        case 'remove_garden': return '<span class="badge bg-dark">Garden</span>';
        case 'remove_other': return '<span class="badge bg-danger">Other</span>';
        default: return `<span class="badge bg-light text-dark">${action}</span>`;
    }
}

function toggleInvAccordion(groupId) {
    const content = document.getElementById('collapse_' + groupId);
    const icon = document.getElementById('icon_' + groupId);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(90deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function refreshInventoryReport() {
    loadInventoryReport();
}

function loadInventoryForDate(date) {
    currentInventoryDate = date;
    document.getElementById('inventoryDatePicker').value = date;
    loadInventoryReport(date);
}

function loadInventoryForToday() {
    const today = new Date().toISOString().split('T')[0];
    loadInventoryForDate(today);
}

function loadInventoryForYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    loadInventoryForDate(yesterdayStr);
}

// ============ WATER BOTTLE SUMMARY REPORT ============
let currentWaterBottleDate = '{{ date("Y-m-d") }}';

async function loadWaterBottleReport(date = null) {
    if (date) {
        currentWaterBottleDate = date;
    }
    
    try {
        const url = `/api/duty-roster/water-bottle-summary?date=${currentWaterBottleDate}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Update key metrics
            document.getElementById('wbCurrentStock').textContent = data.current_stock;
            document.getElementById('wbAdded').textContent = data.added;
            document.getElementById('wbIssued').textContent = data.issued;
            document.getElementById('wbNetChange').textContent = (data.net_change > 0 ? '+' : '') + data.net_change;
            
            // Render history list
            renderWaterBottleList(data.history);
        }
    } catch (error) {
        console.error('Error loading water bottle report:', error);
        document.getElementById('waterBottleList').innerHTML = '<div class="text-center text-red-500 text-xs py-2">Error loading data</div>';
    }
}

function renderWaterBottleList(history) {
    const list = document.getElementById('waterBottleList');
    if (!history || history.length === 0) {
        list.innerHTML = '<div class="text-center text-gray-400 text-xs py-2">No activity</div>';
        return;
    }
    
    let html = '';
    history.forEach(record => {
        const isAdded = record.type === 'added';
        const badgeColor = isAdded ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
        const qtyPrefix = isAdded ? '+' : '';
        const cleanNote = record.notes ? record.notes.replace('Room: ', '') : '';
        
        html += `
            <div class="border border-gray-200 rounded-lg p-2 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] px-1.5 py-0.5 rounded ${badgeColor} font-bold">
                            ${qtyPrefix}${record.quantity}
                        </span>
                        <span class="font-bold text-gray-800 text-sm">${isAdded ? 'Stock Added' : 'Issued'}</span>
                    </div>
                    <span class="text-[10px] text-gray-500">${record.time}</span>
                </div>
                <div class="flex items-center justify-between text-[10px] text-gray-600">
                    <div class="flex items-center gap-1">
                        ${cleanNote ? `<span class="bg-cyan-100 text-cyan-700 px-1.5 py-0.5 rounded font-medium">${cleanNote}</span>` : ''}
                        ${record.sale_id ? `<span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-medium">BILL #${record.sale_id}</span>` : ''}
                        ${!cleanNote && !record.sale_id ? '<span class="text-gray-300">-</span>' : ''}
                    </div>
                    <div class="text-right">
                        <span class="text-gray-400">By: ${record.user}</span>
                    </div>
                </div>
                ${record.description ? `
                    <div class="text-[10px] text-gray-500 mt-1 italic border-t border-gray-100 pt-1">
                        "${record.description}"
                    </div>
                ` : ''}
            </div>
        `;
    });
    list.innerHTML = html;
}

function refreshWaterBottleReport() {
    loadWaterBottleReport();
}

function loadWaterBottleForDate(date) {
    currentWaterBottleDate = date;
    document.getElementById('waterBottleDatePicker').value = date;
    loadWaterBottleReport(date);
}

function loadWaterBottleForToday() {
    const today = new Date().toISOString().split('T')[0];
    loadWaterBottleForDate(today);
}

function loadWaterBottleForYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    loadWaterBottleForDate(yesterdayStr);
}

// ============ ACTIVE ORDERS SUMMARY ============
async function loadActiveOrders() {
    try {
        const response = await fetch('/api/duty-roster/active-orders');
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Update summary stats
            document.getElementById('aoTotalOrders').textContent = data.summary.total_orders;
            document.getElementById('aoTotalItems').textContent = data.summary.total_items;
            document.getElementById('aoTotalAmount').textContent = 'Rs ' + Number(data.summary.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Render orders list
            renderActiveOrdersList(data.orders);
        }
    } catch (error) {
        console.error('Error loading active orders:', error);
        document.getElementById('activeOrdersList').innerHTML = '<div class="text-center text-red-500 text-xs py-2">Error loading data</div>';
    }
}

function renderActiveOrdersList(orders) {
    const list = document.getElementById('activeOrdersList');
    
    if (!orders || orders.length === 0) {
        list.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs">No active orders</div>';
        return;
    }
    
    let html = '';
    orders.forEach((order, index) => {
        const totalAmount = Number(order.total_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const accordionId = `order-${order.id}`;
        
        html += `
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                <!-- Accordion Header (Always Visible) -->
                <div class="p-3 cursor-pointer hover:bg-gray-50 transition-colors" onclick="toggleOrderDetails('${accordionId}')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 flex-1">
                            <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform duration-200" id="${accordionId}-icon"></i>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-gray-800">${order.table_name}</span>
                                    <span class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full font-medium">
                                        Sale #${order.id}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    <i class="fas fa-user text-gray-400"></i> ${order.user_name}
                                </div>
                            </div>
                        </div>
                        <div class="text-right ml-2">
                            <div class="text-sm font-bold text-emerald-600">Rs ${totalAmount}</div>
                            <div class="text-xs text-gray-500">${order.item_count} items</div>
                        </div>
                    </div>
                </div>
                
                <!-- Accordion Content (Expandable) -->
                <div id="${accordionId}" class="accordion-content" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out;">
                    <div class="px-3 pb-3 border-t border-gray-100">
                        <div class="text-xs text-gray-500 mb-2 mt-2">
                            <i class="far fa-clock"></i> ${order.last_updated}
                        </div>
                        
                        <!-- All Order Items -->
                        <div class="space-y-1">`;
        
        // Show ALL items when expanded
        order.items.forEach(item => {
            const itemTotal = Number(item.total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            const statusBadge = item.status === 'confirm' 
                ? '<span class="text-xs px-1.5 py-0.5 bg-green-100 text-green-700 rounded">✓</span>'
                : '<span class="text-xs px-1.5 py-0.5 bg-yellow-100 text-yellow-700 rounded">⏳</span>';
            
            html += `
                <div class="flex items-center justify-between text-xs bg-gray-50 rounded px-2 py-1.5">
                    <div class="flex items-center gap-2 flex-1">
                        ${statusBadge}
                        <span class="text-gray-700">${item.menu_name}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">×${item.quantity}</span>
                        <span class="text-gray-700 font-medium">Rs ${itemTotal}</span>
                    </div>
                </div>`;
        });
        
        html += `
                        </div>
                        
                        <div class="mt-3 pt-2 border-t border-gray-100">
                            <a href="/cashier" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                                <i class="fas fa-external-link-alt"></i> View in Cashier
                            </a>
                        </div>
                    </div>
                </div>
            </div>`;
    });
    
    list.innerHTML = html;
}

function toggleOrderDetails(accordionId) {
    const content = document.getElementById(accordionId);
    const icon = document.getElementById(accordionId + '-icon');
    
    if (content.style.maxHeight && content.style.maxHeight !== '0px') {
        // Collapse
        content.style.maxHeight = '0px';
        icon.style.transform = 'rotate(0deg)';
    } else {
        // Expand
        content.style.maxHeight = content.scrollHeight + 'px';
        icon.style.transform = 'rotate(90deg)';
    }
}

function refreshActiveOrders() {
    loadActiveOrders();
}

// ============ ATTENDANCE SUMMARY ============
let currentAttendanceDate = '{{ date("Y-m-d") }}';

async function loadAttendanceSummary(date = null) {
    if (date) {
        currentAttendanceDate = date;
    }
    
    try {
        const url = `/api/duty-roster/attendance-summary?date=${currentAttendanceDate}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Update key metrics
            document.getElementById('attTotalStaff').textContent = data.total_staff;
            document.getElementById('attPresent').textContent = data.present;
            document.getElementById('attHalfDay').textContent = data.half_day;
            document.getElementById('attAbsent').textContent = data.absent;
            document.getElementById('attOnLeave').textContent = data.on_leave;
            document.getElementById('attRate').textContent = data.attendance_rate + '%';
            
            // Update progress bar
            const total = data.total_staff;
            if (total > 0) {
                const presentPercent = (data.present / total) * 100;
                const halfPercent = (data.half_day / total) * 100;
                const absentPercent = (data.absent / total) * 100;
                const leavePercent = (data.on_leave / total) * 100;
                const notMarkedPercent = (data.not_marked / total) * 100;
                
                document.getElementById('attProgressPresent').style.width = presentPercent + '%';
                document.getElementById('attProgressHalf').style.width = halfPercent + '%';
                document.getElementById('attProgressAbsent').style.width = absentPercent + '%';
                document.getElementById('attProgressLeave').style.width = leavePercent + '%';
                document.getElementById('attProgressNotMarked').style.width = notMarkedPercent + '%';
            }
            
            // Update not marked badge
            const notMarkedBadge = document.getElementById('attNotMarkedBadge');
            const notMarkedCount = document.getElementById('attNotMarkedCount');
            if (data.not_marked > 0) {
                notMarkedBadge.classList.remove('hidden');
                notMarkedCount.textContent = data.not_marked;
            } else {
                notMarkedBadge.classList.add('hidden');
            }
            
            // Render category breakdown
            renderAttendanceCategoryBreakdown(data.category_breakdown);
            
            // Render absent and on leave lists
            renderAbsentStaffList(data.absent_staff);
            renderOnLeaveStaffList(data.staff_on_leave);
        }
    } catch (error) {
        console.error('Error loading attendance summary:', error);
    }
}

function renderAttendanceCategoryBreakdown(categories) {
    const container = document.getElementById('attCategoryBreakdown');
    if (!categories || categories.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-400 text-xs py-2">No category data</div>';
        return;
    }
    
    let html = '';
    categories.forEach(cat => {
        const total = cat.total;
        const presentWidth = total > 0 ? (cat.present / total) * 100 : 0;
        const halfWidth = total > 0 ? (cat.half / total) * 100 : 0;
        const absentWidth = total > 0 ? (cat.absent / total) * 100 : 0;
        const notMarkedWidth = 100 - presentWidth - halfWidth - absentWidth;
        
        html += `
            <div class="bg-gray-50 rounded-lg p-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-700">${cat.name}</span>
                    <span class="text-xs text-gray-500">${cat.present}/${cat.total}</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden flex">
                    <div class="h-full bg-green-500" style="width: ${presentWidth}%"></div>
                    <div class="h-full bg-yellow-500" style="width: ${halfWidth}%"></div>
                    <div class="h-full bg-red-500" style="width: ${absentWidth}%"></div>
                    <div class="h-full bg-gray-400" style="width: ${notMarkedWidth}%"></div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function renderAbsentStaffList(absentStaff) {
    const container = document.getElementById('attAbsentList');
    if (!absentStaff || absentStaff.length === 0) {
        container.innerHTML = '<p class="text-gray-400">No absent staff</p>';
        return;
    }
    
    let html = '';
    absentStaff.forEach(staff => {
        const category = staff.category ? staff.category.replace('_', ' ') : 'N/A';
        html += `
            <div class="flex items-center justify-between py-1 border-b border-red-100 last:border-0">
                <span class="font-medium text-red-800">${staff.person_name}</span>
                <span class="text-red-500 capitalize">${category}</span>
            </div>
        `;
    });
    container.innerHTML = html;
}

function renderOnLeaveStaffList(staffOnLeave) {
    const container = document.getElementById('attOnLeaveList');
    if (!staffOnLeave || staffOnLeave.length === 0) {
        container.innerHTML = '<p class="text-gray-400">No staff on leave</p>';
        return;
    }
    
    let html = '';
    staffOnLeave.forEach(staff => {
        const leaveType = staff.leave_type ? staff.leave_type.replace('_', ' ') : 'Leave';
        html += `
            <div class="flex items-center justify-between py-1 border-b border-blue-100 last:border-0">
                <span class="font-medium text-blue-800">${staff.person_name}</span>
                <span class="text-blue-500 capitalize">${leaveType}</span>
            </div>
        `;
    });
    container.innerHTML = html;
}

function toggleAttendanceBreakdown() {
    const content = document.getElementById('attCategoryBreakdown');
    const arrow = document.getElementById('attBreakdownArrow');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

function toggleAbsentStaffList() {
    const content = document.getElementById('absentStaffList');
    const arrow = document.getElementById('absentListArrow');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

function refreshAttendanceSummary() {
    loadAttendanceSummary();
}

// ============ NET PROFIT/LOSS SUMMARY ============
let currentProfitDate = '{{ date("Y-m-d") }}';

async function loadNetProfitSummary(date = null) {
    if (date) {
        currentProfitDate = date;
    }

    try {
        const url = `/api/duty-roster/net-profit?date=${currentProfitDate}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            updateNetProfitUI(result.data);
        }
    } catch (error) {
        console.error('Error loading net profit summary:', error);
        document.getElementById('nplStatusBadge').textContent = 'ERROR';
        document.getElementById('nplStatusBadge').className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800';
    }
}

function updateNetProfitUI(data) {
    // 1. Update Key Figures
    const netProfit = parseFloat(data.net_profit);
    const income = parseFloat(data.income.total);
    
    // Format Currency
    const formatCurrency = (amount) => {
        return 'Rs ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };

    document.getElementById('nplTargetValue').textContent = formatCurrency(netProfit);
    document.getElementById('nplMarginValue').textContent = data.profit_margin + '%';
    
    // Breakdown
    document.getElementById('nplIncome').textContent = formatCurrency(data.income.total);
    document.getElementById('nplExpenses').textContent = formatCurrency(data.expenses.operational);
    document.getElementById('nplStaffCost').textContent = formatCurrency(data.expenses.staff_cost);
    document.getElementById('nplCogs').textContent = formatCurrency(data.expenses.cogs);

    // 2. Update Status Styling
    const statusBadge = document.getElementById('nplStatusBadge');
    const statusIcon = document.getElementById('nplStatusIcon');
    const targetValue = document.getElementById('nplTargetValue');
    const marginContainer = document.getElementById('nplMarginContainer');

    if (netProfit >= 0) {
        // PROFIT STATE
        statusBadge.textContent = 'PROFIT';
        statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800';
        
        targetValue.className = 'text-3xl font-extrabold flex items-baseline gap-2 text-emerald-600';
        marginContainer.className = 'text-xs font-medium mt-1 text-emerald-600';
        
        statusIcon.innerHTML = '<i class="fas fa-arrow-trend-up text-emerald-500"></i>';
    } else {
        // LOSS STATE
        statusBadge.textContent = 'LOSS';
        statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800';
        
        targetValue.className = 'text-3xl font-extrabold flex items-baseline gap-2 text-red-600';
        marginContainer.className = 'text-xs font-medium mt-1 text-red-600';
        
        statusIcon.innerHTML = '<i class="fas fa-arrow-trend-down text-red-500"></i>';
    }
}

function refreshNetProfitSummary() {
    loadNetProfitSummary();
}

// ============ MONTHLY PROFIT/LOSS SUMMARY ============
let currentMplMonth = {{ date('m') }};
let currentMplYear = {{ date('Y') }};

function changeMonth(delta) {
    currentMplMonth += delta;
    if (currentMplMonth > 12) { currentMplMonth = 1; currentMplYear++; }
    if (currentMplMonth < 1) { currentMplMonth = 12; currentMplYear--; }
    loadMonthlyProfit();
}

async function loadMonthlyProfit() {
    try {
        document.getElementById('mplStatusBadge').textContent = 'LOADING';
        document.getElementById('mplStatusBadge').className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-gray-700 text-gray-300';
        
        const url = `/api/duty-roster/monthly-profit?month=${currentMplMonth}&year=${currentMplYear}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            updateMonthlyProfitUI(result.data);
        } else {
            document.getElementById('mplStatusBadge').textContent = 'ERROR';
            document.getElementById('mplStatusBadge').className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800';
        }
    } catch (error) {
        console.error('Error loading monthly profit:', error);
        document.getElementById('mplStatusBadge').textContent = 'ERROR';
        document.getElementById('mplStatusBadge').className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800';
    }
}

function updateMonthlyProfitUI(data) {
    const fmt = (amount) => 'Rs ' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const netProfit = parseFloat(data.net_profit);

    // Month label
    document.getElementById('mplMonthLabel').textContent = data.month_name;

    // Net result
    document.getElementById('mplNetValue').textContent = fmt(netProfit);
    document.getElementById('mplMarginValue').textContent = data.profit_margin + '%';
    document.getElementById('mplDaysCounted').textContent = '(' + data.days_counted + ' days)';

    // Income breakdown
    document.getElementById('mplTotalIncome').textContent = fmt(data.income.total);
    document.getElementById('mplCash').textContent = fmt(data.income.cash);
    document.getElementById('mplCard').textContent = fmt(data.income.card);
    document.getElementById('mplBank').textContent = fmt(data.income.bank);

    // Summary breakdown
    document.getElementById('mplIncome').textContent = fmt(data.income.total);
    document.getElementById('mplExpenses').textContent = fmt(data.expenses.operational);
    document.getElementById('mplStaffCost').textContent = fmt(data.expenses.staff_cost);
    document.getElementById('mplCogs').textContent = fmt(data.expenses.cogs);

    // Status styling
    const statusBadge = document.getElementById('mplStatusBadge');
    const statusIcon = document.getElementById('mplStatusIcon');
    const netValue = document.getElementById('mplNetValue');
    const marginContainer = document.getElementById('mplMarginContainer');

    if (netProfit >= 0) {
        statusBadge.textContent = 'PROFIT';
        statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800';
        netValue.className = 'text-3xl font-extrabold flex items-baseline gap-2 text-emerald-600';
        marginContainer.className = 'text-xs font-medium mt-1 text-emerald-600';
        statusIcon.innerHTML = '<i class="fas fa-arrow-trend-up text-emerald-500"></i>';
    } else {
        statusBadge.textContent = 'LOSS';
        statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800';
        netValue.className = 'text-3xl font-extrabold flex items-baseline gap-2 text-red-600';
        marginContainer.className = 'text-xs font-medium mt-1 text-red-600';
        statusIcon.innerHTML = '<i class="fas fa-arrow-trend-down text-red-500"></i>';
    }

    // Daily chart
    renderMonthlyChart(data.daily_breakdown);

    // Daily table
    renderMonthlyTable(data.daily_breakdown);
}

function renderMonthlyChart(breakdown) {
    const container = document.getElementById('mplDailyChart');
    if (!breakdown || breakdown.length === 0) {
        container.innerHTML = '<div class="text-center py-2 text-gray-400 text-xs w-full">No data</div>';
        return;
    }

    const maxAbs = Math.max(...breakdown.map(d => Math.abs(d.net)), 1);
    const chartHeight = 112; // h-28 = 7rem = 112px
    const halfHeight = chartHeight / 2;

    container.innerHTML = breakdown.map(d => {
        const net = d.net;
        const barHeight = Math.max(Math.abs(net) / maxAbs * halfHeight, 2);
        const isProfit = net >= 0;
        const color = isProfit ? 'bg-emerald-400' : 'bg-red-400';
        const position = isProfit ? `bottom: ${halfHeight}px;` : `top: ${halfHeight}px;`;
        const fmt = (n) => 'Rs ' + parseFloat(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});

        return `
            <div class="flex-1 min-w-[8px] relative" style="height: ${chartHeight}px;" title="Day ${d.day}: ${fmt(d.net)}">
                <div class="absolute left-0 right-0 ${color} rounded-sm opacity-80 hover:opacity-100 transition-opacity" 
                     style="${position} height: ${barHeight}px;"></div>
                <div class="absolute left-0 right-0 border-t border-gray-300" style="top: ${halfHeight}px;"></div>
            </div>
        `;
    }).join('');
}

function renderMonthlyTable(breakdown) {
    const tbody = document.getElementById('mplTableBody');
    const fmt = (n) => parseFloat(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});

    tbody.innerHTML = breakdown.map(d => {
        const netClass = d.net >= 0 ? 'text-emerald-600' : 'text-red-600';
        const netPrefix = d.net >= 0 ? '+' : '';
        const dateObj = new Date(d.date);
        const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="px-2 py-1 text-left">${d.day} ${dayName}</td>
                <td class="px-2 py-1 text-right text-emerald-700">${fmt(d.income)}</td>
                <td class="px-2 py-1 text-right text-red-600">${fmt(d.expenses)}</td>
                <td class="px-2 py-1 text-right text-orange-600">${fmt(d.staff_cost)}</td>
                <td class="px-2 py-1 text-right text-blue-600">${fmt(d.cogs)}</td>
                <td class="px-2 py-1 text-right font-bold ${netClass}">${netPrefix}${fmt(d.net)}</td>
            </tr>
        `;
    }).join('');
}

function toggleMonthlyTable() {
    const table = document.getElementById('mplDailyTable');
    const text = document.getElementById('mplTableToggleText');
    const icon = document.getElementById('mplTableIcon');
    if (table.classList.contains('hidden')) {
        table.classList.remove('hidden');
        text.textContent = 'Hide Daily Details';
        icon.style.transform = 'rotate(180deg)';
    } else {
        table.classList.add('hidden');
        text.textContent = 'Show Daily Details';
        icon.style.transform = 'rotate(0deg)';
    }
}

function refreshMonthlyProfit() {
    loadMonthlyProfit();
}

// ============ SALARY SUMMARY ============
let currentSalMonth = {{ date('m') }};
let currentSalYear = {{ date('Y') }};

function changeSalaryMonth(delta) {
    currentSalMonth += delta;
    if (currentSalMonth > 12) { currentSalMonth = 1; currentSalYear++; }
    if (currentSalMonth < 1) { currentSalMonth = 12; currentSalYear--; }
    loadSalarySummary();
}

async function loadSalarySummary() {
    try {
        const url = `/api/duty-roster/salary-summary?month=${currentSalMonth}&year=${currentSalYear}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            updateSalaryUI(result.data);
        }
    } catch (error) {
        console.error('Error loading salary summary:', error);
    }
}

function updateSalaryUI(data) {
    const fmt = (n) => 'Rs ' + parseFloat(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    document.getElementById('salMonthLabel').textContent = data.month_name;
    document.getElementById('salTotalBasic').textContent = fmt(data.totals.basic_salary);
    document.getElementById('salTotalAdvance').textContent = fmt(data.totals.salary_advance);
    document.getElementById('salTotalFinal').textContent = fmt(data.totals.final_salary);
    document.getElementById('salStaffCount').textContent = data.staff_count;
    document.getElementById('salPresentDays').textContent = data.totals.present_days;
    document.getElementById('salAbsentDays').textContent = data.totals.absent_days;
    document.getElementById('salAdvancePeriod').textContent = data.advance_period;

    // Render advances by person
    renderSalaryAdvances(data.advances_by_person);

    // Render employee table
    renderSalaryTable(data.employees);
}

function renderSalaryAdvances(advancesByPerson) {
    const container = document.getElementById('salAdvancesList');
    const entries = Object.entries(advancesByPerson);

    if (!entries || entries.length === 0) {
        container.innerHTML = '<div class="text-center py-2 text-gray-400 text-xs">No advances this period</div>';
        return;
    }

    const fmt = (n) => parseFloat(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    container.innerHTML = entries.map(([name, info]) => `
        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0 text-[11px]">
            <span class="text-gray-700 font-medium">${name}</span>
            <div class="flex items-center gap-3">
                <span class="text-gray-400">${info.count}x</span>
                <span class="text-red-600 font-bold">Rs ${fmt(info.total)}</span>
            </div>
        </div>
    `).join('');
}

function renderSalaryTable(employees) {
    const tbody = document.getElementById('salTableBody');
    const fmt = (n) => parseFloat(n).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});

    tbody.innerHTML = employees.map(emp => {
        const finalClass = emp.final_salary >= 0 ? 'text-emerald-700' : 'text-red-600';
        const advClass = emp.salary_advance > 0 ? 'text-red-600' : 'text-gray-400';
        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="px-2 py-1 text-left font-medium text-gray-700">${emp.name}</td>
                <td class="px-2 py-1 text-right">${fmt(emp.basic_salary)}</td>
                <td class="px-2 py-1 text-right ${advClass}">${emp.salary_advance > 0 ? '-' + fmt(emp.salary_advance) : '-'}</td>
                <td class="px-2 py-1 text-center text-emerald-600">${emp.present_days !== null ? emp.present_days : '-'}</td>
                <td class="px-2 py-1 text-center text-red-500">${emp.absent_days !== null ? emp.absent_days : '-'}</td>
                <td class="px-2 py-1 text-right font-bold ${finalClass}">${fmt(emp.final_salary)}</td>
            </tr>
        `;
    }).join('');
}

function toggleSalaryAdvances() {
    const list = document.getElementById('salAdvancesList');
    const text = document.getElementById('salAdvToggleText');
    const icon = document.getElementById('salAdvIcon');
    if (list.classList.contains('hidden')) {
        list.classList.remove('hidden');
        text.textContent = 'Hide Advances';
        icon.style.transform = 'rotate(180deg)';
    } else {
        list.classList.add('hidden');
        text.textContent = 'Show Advances';
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleSalaryTable() {
    const table = document.getElementById('salEmployeeTable');
    const text = document.getElementById('salTableToggleText');
    const icon = document.getElementById('salTableIcon');
    if (table.classList.contains('hidden')) {
        table.classList.remove('hidden');
        text.textContent = 'Hide Staff Details';
        icon.style.transform = 'rotate(180deg)';
    } else {
        table.classList.add('hidden');
        text.textContent = 'Show Staff Details';
        icon.style.transform = 'rotate(0deg)';
    }
}

function refreshSalarySummary() {
    loadSalarySummary();
}

// ============ KITCHEN SUMMARY ============
function printKitchenSummary() {
    const startDate = document.getElementById('kitchenStartDate').value;
    const endDate = document.getElementById('kitchenEndDate').value;
    const salesCats = [...document.querySelectorAll('.ksSalesCatCb:checked')].map(cb => cb.value).join(',');
    const issueActions = [...document.querySelectorAll('.ksIssuesActionCb:checked')].map(cb => cb.value).join(',');
    window.open(`/api/duty-roster/kitchen-summary/print?start_date=${startDate}&end_date=${endDate}&sales_categories=${encodeURIComponent(salesCats)}&issue_actions=${encodeURIComponent(issueActions)}`, '_blank');
}

function setKitchenDate(preset) {
    const today = new Date();
    const fmt = (d) => d.toISOString().split('T')[0];
    let start, end;

    switch (preset) {
        case 'today':
            start = end = fmt(today);
            break;
        case 'yesterday':
            const y = new Date(today); y.setDate(y.getDate() - 1);
            start = end = fmt(y);
            break;
        case 'week':
            const w = new Date(today); w.setDate(w.getDate() - 6);
            start = fmt(w); end = fmt(today);
            break;
        case 'month':
            start = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            end = fmt(today);
            break;
    }

    document.getElementById('kitchenStartDate').value = start;
    document.getElementById('kitchenEndDate').value = end;
    loadKitchenSummary();
}

async function loadKitchenSummary() {
    const startDate = document.getElementById('kitchenStartDate').value;
    const endDate = document.getElementById('kitchenEndDate').value;

    try {
        const url = `/api/duty-roster/kitchen-summary?start_date=${startDate}&end_date=${endDate}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            updateKitchenSummaryUI(result.data);
        }
    } catch (error) {
        console.error('Error loading kitchen summary:', error);
    }
}

let _ksSalesData = null;
let _ksInventoryIssues = null;

function updateKitchenSummaryUI(data) {
    document.getElementById('ksTotalItems').textContent = data.daily_sales.total_items;
    document.getElementById('ksTotalSales').textContent = data.daily_sales.total_sales;
    document.getElementById('ksTotalKitchenQty').textContent = parseFloat(data.main_kitchen.total_quantity).toFixed(1);
    document.getElementById('ksTotalTransactions').textContent = data.main_kitchen.total_transactions;

    _ksSalesData = data.daily_sales;
    _ksInventoryIssues = data.inventory_issues || {};
    populateKsSalesFilter(data.daily_sales);
    populateKsIssuesFilter(data.inventory_issues || {});
    applyKsSalesFilter();
    applyKsIssuesFilter();
}

// ---- Dropdown toggle & close on outside click ----
function toggleKsDropdown(type) {
    const dd = document.getElementById(type + 'Dropdown');
    dd.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    ['salesFilterDropdown', 'issuesFilterDropdown'].forEach(id => {
        const dd = document.getElementById(id);
        if (dd && !dd.classList.contains('hidden') && !dd.contains(e.target) && !e.target.closest('[onclick*="toggleKsDropdown"]')) {
            dd.classList.add('hidden');
        }
    });
});

// ---- DAILY SALES FILTER (multi-select checkboxes) ----
function populateKsSalesFilter(salesData) {
    const list = document.getElementById('ksSalesCatList');
    const categories = salesData.by_category || {};
    // Remember which were checked
    const prevChecked = {};
    list.querySelectorAll('input[type=checkbox]').forEach(cb => { prevChecked[cb.value] = cb.checked; });
    const isFirstLoad = Object.keys(prevChecked).length === 0;

    list.innerHTML = '';
    Object.keys(categories).forEach(catId => {
        const checked = isFirstLoad ? true : (prevChecked[catId] !== undefined ? prevChecked[catId] : true);
        list.innerHTML += `<div class="px-2 py-0.5">
            <label class="flex items-center gap-1 text-[10px] text-gray-700 cursor-pointer">
                <input type="checkbox" value="${catId}" ${checked ? 'checked' : ''} onchange="onKsSalesCatChange()" class="w-3 h-3 ksSalesCatCb"> ${categories[catId].name}
            </label>
        </div>`;
    });
    // Sync "All" checkbox
    syncSalesAllCheckbox();
}

function toggleAllSalesCategories(allCb) {
    document.querySelectorAll('.ksSalesCatCb').forEach(cb => { cb.checked = allCb.checked; });
    applyKsSalesFilter();
}

function onKsSalesCatChange() {
    syncSalesAllCheckbox();
    applyKsSalesFilter();
}

function syncSalesAllCheckbox() {
    const all = document.querySelectorAll('.ksSalesCatCb');
    const checked = document.querySelectorAll('.ksSalesCatCb:checked');
    const allCb = document.getElementById('ksSalesAll');
    if (allCb) allCb.checked = all.length === checked.length;
}

function applyKsSalesFilter() {
    if (!_ksSalesData) return;
    const checked = [...document.querySelectorAll('.ksSalesCatCb:checked')].map(cb => cb.value);
    renderKitchenSales(_ksSalesData, checked);
}

function renderKitchenSales(salesData, selectedCatIds) {
    const container = document.getElementById('ksSalesContent');
    const allCategories = salesData.by_category || {};
    let categories;

    if (!selectedCatIds || selectedCatIds.length === 0) {
        categories = [];
    } else {
        categories = selectedCatIds.map(id => allCategories[id]).filter(Boolean);
    }

    if (categories.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs">No sales data</div>';
        return;
    }

    let html = `<div class="text-[10px] px-2 py-1 bg-gray-50 border-b flex justify-between">
        <span>Total Items: <strong>${salesData.total_items}</strong></span>
        <span>Total Sales: <strong>${salesData.total_sales}</strong></span>
    </div>`;

    categories.forEach(cat => {
        html += `<div class="bg-blue-500 text-white text-[10px] font-bold px-2 py-1 flex justify-between items-center">
            <span>${cat.name}</span>
            <span class="bg-white text-blue-600 rounded-full px-2 py-0.5 text-[9px]">${cat.total} items</span>
        </div>`;

        if (cat.category_summary && cat.category_summary.trim() !== '') {
            html += `<div class="bg-blue-50 px-2 py-1 border-b border-blue-200">
                <div class="text-[9px] text-blue-800 font-medium">
                    <i class="fas fa-boxes text-[8px] mr-1 text-blue-500"></i>${cat.category_summary}
                </div>
            </div>`;
        }

        cat.items.forEach(item => {
            html += `<div class="border-b border-gray-100 px-2 py-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] text-gray-700">${item.name}</span>
                    <span class="bg-blue-100 text-blue-700 rounded-full px-2 py-0.5 text-[9px] font-bold">${item.quantity}</span>
                </div>`;
            if (item.item_summary && item.item_summary.trim() !== '') {
                html += `<div class="text-[9px] text-gray-500 mt-0.5 italic">
                    <i class="fas fa-mortar-pestle text-[8px] mr-1 text-amber-500"></i>${item.item_summary}
                </div>`;
            }
            html += `</div>`;
        });
    });

    container.innerHTML = html;
}

// ---- INVENTORY ISSUES FILTER (multi-select by action type, default Main Kitchen) ----
function populateKsIssuesFilter(issuesData) {
    const list = document.getElementById('ksIssuesActionList');
    const prevChecked = {};
    list.querySelectorAll('input[type=checkbox]').forEach(cb => { prevChecked[cb.value] = cb.checked; });
    const isFirstLoad = Object.keys(prevChecked).length === 0;

    list.innerHTML = '';
    Object.keys(issuesData).forEach(action => {
        const checked = isFirstLoad ? (action === 'remove_main_kitchen') : (prevChecked[action] !== undefined ? prevChecked[action] : false);
        list.innerHTML += `<div class="px-2 py-0.5">
            <label class="flex items-center gap-1 text-[10px] text-gray-700 cursor-pointer">
                <input type="checkbox" value="${action}" ${checked ? 'checked' : ''} onchange="applyKsIssuesFilter()" class="w-3 h-3 ksIssuesActionCb"> ${issuesData[action].label}
            </label>
        </div>`;
    });
}

function applyKsIssuesFilter() {
    if (!_ksInventoryIssues) return;
    const checked = [...document.querySelectorAll('.ksIssuesActionCb:checked')].map(cb => cb.value);
    renderKitchenIssues(checked);
}

function renderKitchenIssues(selectedActions) {
    const container = document.getElementById('ksKitchenContent');
    if (!_ksInventoryIssues || !selectedActions || selectedActions.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs">No issues selected</div>';
        return;
    }

    // Merge data from all selected actions
    let mergedCategories = {};
    let totalQty = 0, totalTxns = 0, totalCost = 0;

    selectedActions.forEach(action => {
        const actionData = _ksInventoryIssues[action];
        if (!actionData) return;
        totalQty += actionData.total_quantity;
        totalTxns += actionData.total_transactions;
        totalCost += actionData.total_cost;

        Object.entries(actionData.by_category || {}).forEach(([catId, cat]) => {
            if (!mergedCategories[catId]) {
                mergedCategories[catId] = { name: cat.name, items: {}, total_quantity: 0, total_cost: 0 };
            }
            mergedCategories[catId].total_quantity += cat.total_quantity;
            mergedCategories[catId].total_cost += cat.total_cost;
            cat.items.forEach(item => {
                if (!mergedCategories[catId].items[item.name]) {
                    mergedCategories[catId].items[item.name] = { name: item.name, quantity: 0, cost_per_unit: item.cost_per_unit, total_cost: 0 };
                }
                mergedCategories[catId].items[item.name].quantity += item.quantity;
                mergedCategories[catId].items[item.name].total_cost += item.total_cost;
            });
        });
    });

    const categories = Object.values(mergedCategories);
    if (categories.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs">No issues data</div>';
        return;
    }

    let html = `<div class="text-[10px] px-2 py-1 bg-gray-50 border-b flex justify-between">
        <span>Qty: <strong>${parseFloat(totalQty).toFixed(1)}</strong></span>
        <span>Cost: <strong class="text-red-600">Rs ${parseFloat(totalCost).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong></span>
    </div>`;

    categories.forEach(cat => {
        const catItems = Object.values(cat.items);
        html += `<div class="bg-green-500 text-white text-[10px] font-bold px-2 py-1 flex justify-between items-center">
            <span>${cat.name}</span>
            <div class="flex gap-1">
                <span class="bg-white text-green-600 rounded-full px-2 py-0.5 text-[9px]">${parseFloat(cat.total_quantity).toFixed(1)}</span>
                ${cat.total_cost > 0 ? `<span class="bg-red-100 text-red-700 rounded-full px-2 py-0.5 text-[9px]">Rs ${parseFloat(cat.total_cost).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</span>` : ''}
            </div>
        </div>`;

        catItems.forEach(item => {
            const costStr = item.total_cost > 0 ? `<span class="text-[8px] text-red-500 ml-1">Rs ${parseFloat(item.total_cost).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</span>` : '';
            html += `<div class="border-b border-gray-100 px-2 py-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] text-gray-700 font-medium">${item.name}</span>
                    <div class="flex items-center gap-1">
                        ${costStr}
                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-[9px] font-bold">${parseFloat(item.quantity).toFixed(1)}</span>
                    </div>
                </div>
            </div>`;
        });
    });

    container.innerHTML = html;

    // Update header stats
    document.getElementById('ksTotalKitchenQty').textContent = parseFloat(totalQty).toFixed(1);
    document.getElementById('ksTotalTransactions').textContent = totalTxns;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderSections();
    setupDragAndDrop();
    setupSearch();
    loadBookings(currentDate); // Load bookings for today
    loadAllocations(currentDate); // Load saved allocations for today
    loadStaffOnLeave(currentDate); // Load staff on leave for today
    
    // Initialize time slider to current time
    initTimeSlider();
    
    // Update time display immediately and every minute
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000);
    
    // Load all bookings for mini calendar
    loadAllBookingsForCalendar();
    
    // Load today's bills report
    loadTodayBills();
    // Refresh bills report every 5 minutes
    setInterval(loadTodayBills, 300000);
    
    // Load daily costs report
    loadDailyCosts();
    // Refresh costs report every 5 minutes
    setInterval(loadDailyCosts, 300000);
    
    // Load vehicle security summary
    loadVehicleSummary();
    // Refresh vehicle summary every 5 minutes
    setInterval(loadVehicleSummary, 300000);
    
    // Load inventory changes report
    loadInventoryReport();
    // Refresh inventory report every 5 minutes
    setInterval(loadInventoryReport, 300000);
    
    // Load water bottle summary report
    loadWaterBottleReport();
    // Refresh water bottle report every 5 minutes
    setInterval(loadWaterBottleReport, 300000);
    
    // Load active orders summary
    loadActiveOrders();
    // Refresh active orders every 2 minutes (more frequent for real-time updates)
    setInterval(loadActiveOrders, 120000);
    
    // Load attendance summary
    loadAttendanceSummary();
    // Refresh attendance summary every 5 minutes
    setInterval(loadAttendanceSummary, 300000);
    
    // Load Net Profit Summary (Admin Only - check performed by API)
    // We can call it safely; if not admin, API returns error or empty, but UI is protected by Blade if check
    if (document.getElementById('nplTargetValue')) {
        loadNetProfitSummary();
        setInterval(loadNetProfitSummary, 300000);
    }

    // Load Monthly Profit/Loss (Admin Only)
    if (document.getElementById('monthlyProfitWidget')) {
        loadMonthlyProfit();
    }

    // Load Salary Summary (Admin Only)
    if (document.getElementById('salarySummaryWidget')) {
        loadSalarySummary();
    }

    // Load Kitchen Summary (Admin Only)
    if (document.getElementById('kitchenSummaryWidget')) {
        loadKitchenSummary();
    }
    
    // Load Staff Out (Gate Pass) summary
    loadStaffOut();
    // Refresh every 3 minutes
    setInterval(loadStaffOut, 180000);

    // ===== COMMAND CENTER WIDGETS =====
    // Load Command Center summary (quick stats bar)
    loadCommandCenterData();
    setInterval(loadCommandCenterData, 120000);

    // Load Fraud Report (Security Monitor)
    loadFraudReport();
    setInterval(loadFraudReport, 60000); // Refresh every 1 minute

    // Load Arrivals & Departures
    loadArrivalsAndDepartures();
    setInterval(loadArrivalsAndDepartures, 300000);

    // Load Housekeeping Status
    loadHousekeepingStatus();
    setInterval(loadHousekeepingStatus, 300000);

    // Load Inventory Warnings (right sidebar)
    loadInventoryWarnings();
    setInterval(loadInventoryWarnings, 300000);

    // Load Pending CRM Leads (right sidebar)
    loadPendingLeads();
    setInterval(loadPendingLeads, 300000);

    // Load Maintenance Tickets (right sidebar)
    loadMaintenanceTickets();
    setInterval(loadMaintenanceTickets, 300000);

    // Load Pending Customer Feedback (right sidebar)
    loadPendingFeedback();
    setInterval(loadPendingFeedback, 300000);

    // Load Today's Tasks (middle column)
    loadTodayTasks();
    setInterval(loadTodayTasks, 300000);

    // Load Online Users (admin only - API checks permission)
    if (document.getElementById('onlineUsersWidget')) {
        loadOnlineUsers();
        setInterval(loadOnlineUsers, 60000);
    }
});


function renderSections() {
    const container = document.getElementById('mapContainer');
    
    // Sections that should have labels on the left side (stacked vertically)
    const leftLabelSections = ['orchid', 'nelum', 'olu', 'ahala'];
    
    // Render garden sections first (as background, z-index: 1)
    const gardenSections = sections.filter(s => s.type === 'GARDEN');
    const otherSections = sections.filter(s => s.type !== 'GARDEN');
    
    // Render gardens first (background layer) - subtle white with thin gray border
    // Position labels and staff list in empty corners based on quadrant
    const gardenPositions = {
        'garden-a': { labelPos: 'items-start justify-end', labelClass: 'absolute bottom-4 left-4' },  // Bottom-left corner
        'garden-b': { labelPos: 'items-end justify-end', labelClass: 'absolute bottom-4 right-4' },   // Bottom-right corner
        'garden-c': { labelPos: 'items-end justify-end', labelClass: 'absolute bottom-4 right-4' },   // Bottom-right corner
        'garden-d': { labelPos: 'items-start justify-end', labelClass: 'absolute bottom-4 left-4' },  // Bottom-left corner (center area)
    };
    
    gardenSections.forEach(section => {
        const pos = gardenPositions[section.id] || { labelPos: 'items-center justify-center', labelClass: '' };
        
        const sectionEl = document.createElement('div');
        sectionEl.className = 'absolute';
        sectionEl.style.cssText = `top: ${section.top}%; left: ${section.left}%; width: ${section.width}%; height: ${section.height}%; z-index: 1;`;
        sectionEl.innerHTML = `
            <div class="section-box w-full h-full rounded-lg border border-gray-200 bg-white flex flex-col overflow-hidden relative"
                data-section-id="${section.id}"
                data-section-name="${section.name}"
                data-section-size="large">
                <div class="${pos.labelClass} flex flex-col items-center">
                    <span class="text-gray-500 font-bold text-xl mb-2">${section.name}</span>
                    <div class="section-staff-list flex flex-col items-center gap-1"></div>
                </div>
            </div>
        `;
        container.appendChild(sectionEl);
    });
    
    // Render other sections on top (z-index: 10)
    otherSections.forEach(section => {
        const colors = sectionColors[section.type];
        const isSmall = section.width < 5 || section.height < 6;
        const isMedium = section.width < 8 || section.height < 10;
        const useLeftLabel = leftLabelSections.includes(section.id);
        
        const sectionEl = document.createElement('div');
        sectionEl.className = 'absolute';
        sectionEl.style.cssText = `top: ${section.top}%; left: ${section.left}%; width: ${section.width}%; height: ${section.height}%; z-index: 10;`;
        sectionEl.innerHTML = `
            <span class="section-label ${colors.label} ${useLeftLabel ? 'label-left' : 'label-top'}">
                ${section.name}
            </span>
            <div class="section-box w-full h-full rounded-lg border-2 ${colors.bg} flex flex-col items-center justify-center overflow-hidden"
                data-section-id="${section.id}"
                data-section-name="${section.name}"
                data-section-size="${isSmall ? 'small' : (isMedium ? 'medium' : 'large')}">
                <div class="section-staff-list w-full h-full"></div>
                            </div>
        `;
        container.appendChild(sectionEl);
    });
}

function setupDragAndDrop() {
    // Staff cards drag events
    document.querySelectorAll('.staff-card').forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });
    
    // Section drop events and room hover events
    document.querySelectorAll('.section-box').forEach(section => {
        section.addEventListener('dragover', handleDragOver);
        section.addEventListener('dragleave', handleDragLeave);
        section.addEventListener('drop', handleDrop);
        
        // Room hover details - only for ROOM type sections
        const sectionName = section.dataset.sectionName;
        if (sectionName && !sectionName.includes('Garden') && !sectionName.includes('Kitchen') && 
            !sectionName.includes('Restaurant') && !sectionName.includes('Office') && 
            !sectionName.includes('Pool') && !sectionName.includes('Maintenance')) {
            section.addEventListener('mouseenter', function(e) {
                showRoomTooltip(this, sectionName);
            });
            section.addEventListener('mouseleave', hideRoomTooltip);
        }
    });
}

function handleDragStart(e) {
    draggedStaffId = e.target.dataset.staffId;
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'copyMove';
    e.dataTransfer.setData('text/plain', draggedStaffId);
}

function handleDragEnd(e) {
    e.target.classList.remove('dragging');
    draggedStaffId = null;
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    
    if (draggedStaffId) {
        const sectionId = e.currentTarget.dataset.sectionId;
        const sectionName = e.currentTarget.dataset.sectionName;
        assignStaff(draggedStaffId, sectionId, sectionName);
    }
}

function assignStaff(staffId, sectionId, sectionName) {
    // Remove from previous section if exists
    const previousSection = assignments[staffId];
    if (previousSection) {
        updateSectionAvatars(previousSection);
    }
    
    // Assign to new section
    assignments[staffId] = sectionId;
    
    // Update staff card UI
    const staffCard = document.getElementById(`staff-${staffId}`);
    if (staffCard) {
        staffCard.querySelector('.assigned-indicator').classList.remove('hidden');
        staffCard.querySelector('.assignment-info').classList.remove('hidden');
        staffCard.querySelector('.location-badge').textContent = sectionName;
    }
    
    // Update section avatars
    updateSectionAvatars(sectionId);
    updateStats();
    
    // Save to database
    saveAllocationToDb(staffId, sectionId, sectionName);
}

function unassignStaff(staffId) {
    const sectionId = assignments[staffId];
    if (sectionId) {
        delete assignments[staffId];
        
        // Update staff card UI
        const staffCard = document.getElementById(`staff-${staffId}`);
        if (staffCard) {
            staffCard.querySelector('.assigned-indicator').classList.add('hidden');
            staffCard.querySelector('.assignment-info').classList.add('hidden');
        }
        
        // Update section avatars
        updateSectionAvatars(sectionId);
        updateStats();
        
        // Remove from database
        removeAllocationFromDb(staffId);
    }
}

function updateSectionAvatars(sectionId) {
    const sectionBox = document.querySelector(`[data-section-id="${sectionId}"]`);
    if (!sectionBox) return;
    
    const staffListContainer = sectionBox.querySelector('.section-staff-list');
    const dropHint = sectionBox.querySelector('.drop-hint');
    const sectionSize = sectionBox.dataset.sectionSize;
    
    // Get staff assigned to this section
    const assignedStaff = Object.entries(assignments)
        .filter(([_, sid]) => sid === sectionId)
        .map(([staffId, _]) => {
            const card = document.getElementById(`staff-${staffId}`);
            return {
                id: staffId,
                name: card ? card.dataset.staffName : '',
                initial: card ? card.dataset.staffName.charAt(0).toUpperCase() : '?',
                category: card ? card.dataset.category : ''
            };
        });
    
    // Render staff list with names
    if (assignedStaff.length > 0) {
        let html = '';
        
        if (sectionSize === 'small') {
            // For small sections, show stacked avatars with count
            html = `<div class="flex flex-wrap justify-center items-center gap-0.5 p-1">`;
            assignedStaff.slice(0, 3).forEach((staff, i) => {
                html += `
                    <div class="w-5 h-5 rounded-full bg-white shadow flex items-center justify-center text-xs font-bold text-gray-700" 
                        title="${staff.name}" style="margin-left: ${i > 0 ? '-4px' : '0'}; z-index: ${10-i};">
                        ${staff.initial}
                    </div>`;
            });
            if (assignedStaff.length > 3) {
                html += `<div class="w-5 h-5 rounded-full bg-gray-800 text-white text-xs flex items-center justify-center font-bold" style="margin-left: -4px;">+${assignedStaff.length - 3}</div>`;
            }
            html += `</div>`;
        } else {
            // For medium/large sections, show names
            assignedStaff.forEach(staff => {
                const shortName = staff.name.length > 12 ? staff.name.substring(0, 10) + '..' : staff.name;
                html += `
                    <div class="staff-list-item" data-staff-id="${staff.id}">
                        <div class="avatar">${staff.initial}</div>
                        <span class="name">${shortName}</span>
                        <div class="remove-btn" onclick="event.stopPropagation(); unassignStaff(${staff.id})" title="Remove">×</div>
                    </div>`;
            });
        }
        
        staffListContainer.innerHTML = html;
        if (dropHint) dropHint.style.display = 'none';
    } else {
        staffListContainer.innerHTML = '';
        if (dropHint) dropHint.style.display = '';
    }
}

function updateStats() {
    const totalAssigned = Object.keys(assignments).length;
    document.getElementById('totalAssigned').textContent = totalAssigned;
    
    // Update category counts
    document.querySelectorAll('.assigned-count').forEach(el => {
        const category = el.dataset.category;
        const count = Object.keys(assignments).filter(staffId => {
            const card = document.getElementById(`staff-${staffId}`);
            return card && card.dataset.category === category;
        }).length;
        el.textContent = count;
    });
}

function toggleCategory(category) {
    const content = document.getElementById(`content-${category}`);
    const arrow = document.getElementById(`arrow-${category}`);
    
    if (content.style.display === 'none') {
        content.style.display = '';
        arrow.style.transform = 'rotate(90deg)';
    } else {
        content.style.display = 'none';
        arrow.style.transform = '';
    }
}

function setupSearch() {
    const searchInput = document.getElementById('staffSearch');
    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        
        document.querySelectorAll('.staff-card').forEach(card => {
            const name = card.dataset.staffName.toLowerCase();
            card.style.display = name.includes(term) ? '' : 'none';
        });
    });
}

// Tooltip for small sections
let tooltip = null;

function showTooltip(element, staffList) {
    hideTooltip();
    
    if (staffList.length === 0) return;
    
    tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.innerHTML = `
        <div class="font-semibold mb-1">Assigned Staff (${staffList.length})</div>
        ${staffList.map(s => `<div class="text-sm">• ${s.name}</div>`).join('')}
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + rect.width/2 - tooltip.offsetWidth/2 + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
}

function hideTooltip() {
    if (tooltip) {
        tooltip.remove();
        tooltip = null;
    }
}

// Add hover events for small sections
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        document.querySelectorAll('.section-box[data-section-size="small"]').forEach(section => {
            section.addEventListener('mouseenter', function() {
                const sectionId = this.dataset.sectionId;
                const staffList = Object.entries(assignments)
                    .filter(([_, sid]) => sid === sectionId)
                    .map(([staffId, _]) => {
                        const card = document.getElementById(`staff-${staffId}`);
                        return { name: card ? card.dataset.staffName : 'Unknown' };
                    });
                if (staffList.length > 0) {
                    showTooltip(this, staffList);
                }
            });
            section.addEventListener('mouseleave', hideTooltip);
        });
    }, 500);
});

// Clear all assignments
function clearAllAssignments() {
    if (confirm('Are you sure you want to clear all assignments for this date?')) {
        // Clear from database first
        clearAllocationsFromDb().then(() => {
            const staffIds = Object.keys(assignments);
            staffIds.forEach(staffId => {
                const sectionId = assignments[staffId];
                delete assignments[staffId];
                
                const staffCard = document.getElementById(`staff-${staffId}`);
                if (staffCard) {
                    staffCard.querySelector('.assigned-indicator').classList.add('hidden');
                    staffCard.querySelector('.assignment-info').classList.add('hidden');
                }
            });
            
            // Update all sections
            sections.forEach(section => {
                updateSectionAvatars(section.id);
            });
            updateStats();
            showNotification('All assignments cleared');
        });
    }
}

// Date handling
let currentDate = '{{ date("Y-m-d") }}';

// Booking colors by function type (for badges)
const functionTypeColors = {
    'Wedding': '#e91e63',
    'Birthday': '#9c27b0',
    'Corporate': '#2196f3',
    'Conference': '#00bcd4',
    'Party': '#ff9800',
    'Get Together': '#4caf50',
    'Other': '#607d8b'
};

// Unique colors for each booking (for room outlines)
const bookingOutlineColors = [
    '#ef4444', // Red
    '#22c55e', // Green
    '#3b82f6', // Blue
    '#f59e0b', // Amber
    '#8b5cf6', // Purple
    '#ec4899', // Pink
    '#14b8a6', // Teal
    '#f97316', // Orange
    '#06b6d4', // Cyan
    '#84cc16', // Lime
    '#6366f1', // Indigo
    '#a855f7', // Violet
];

// Get consistent color for a booking based on its ID
function getBookingColor(booking) {
    // Use booking ID to generate a consistent color index
    const bookingId = booking.id || 0;
    const colorIndex = bookingId % bookingOutlineColors.length;
    return bookingOutlineColors[colorIndex];
}

// Load bookings for the selected date
async function loadBookings(date) {
    const bookingsList = document.getElementById('bookingsList');
    bookingsList.innerHTML = `
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p class="text-sm">Loading bookings...</p>
        </div>
    `;

    try {
        const response = await fetch('/bookings');
        const bookings = await response.json();
        
        // Filter bookings for the selected date
        const selectedDate = new Date(date);
        const filteredBookings = bookings.filter(booking => {
            const startDate = new Date(booking.start);
            const endDate = booking.end ? new Date(booking.end) : startDate;
            
            // Check if selected date falls within booking range
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(23, 59, 59, 999);
            selectedDate.setHours(12, 0, 0, 0);
            
            return selectedDate >= startDate && selectedDate <= endDate;
        });

        // Cache bookings for time slider re-rendering
        cachedBookings = filteredBookings;
        
        renderBookings(filteredBookings);
        highlightBookedRooms(filteredBookings);
        renderBookingTimeline(filteredBookings);
        
    } catch (error) {
        console.error('Error loading bookings:', error);
        bookingsList.innerHTML = `
            <div class="text-center py-8 text-red-400">
                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                <p class="text-sm">Error loading bookings</p>
            </div>
        `;
    }
}

function renderBookings(bookings) {
    const bookingsList = document.getElementById('bookingsList');
    
    if (bookings.length === 0) {
        bookingsList.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-calendar-times text-3xl mb-2"></i>
                <p class="text-sm">No bookings for this date</p>
            </div>
        `;
        document.getElementById('totalBookings').textContent = '0';
        document.getElementById('totalRoomsBooked').textContent = '0';
        return;
    }

    // Get viewing time (from slider or current time)
    const viewTime = getViewingTime();
    
    // Separate bookings into active and departed based on viewing time
    const activeBookings = [];
    const departedBookings = [];
    
    bookings.forEach(booking => {
        const checkOut = booking.end ? new Date(booking.end) : null;
        // If checkout time has passed relative to viewing time, it's departed
        if (checkOut && viewTime > checkOut) {
            departedBookings.push(booking);
        } else {
            activeBookings.push(booking);
        }
    });

    let totalRooms = 0;
    let html = '';
    
    // Helper function to render a single booking card
    const renderBookingCard = (booking, index, isDeparted) => {
        const functionColor = functionTypeColors[booking.function_type] || functionTypeColors['Other'];
        const outlineColor = isDeparted ? '#6b7280' : getBookingColor(booking);
        let rooms = [];
        
        try {
            rooms = JSON.parse(booking.room_numbers);
            if (!Array.isArray(rooms)) rooms = [rooms];
        } catch (e) {
            rooms = booking.room_numbers ? [booking.room_numbers] : [];
        }
        
        totalRooms += rooms.length;
        
        // Format dates
        const checkIn = booking.start ? new Date(booking.start) : null;
        const checkOut = booking.end ? new Date(booking.end) : null;
        const formatDateTime = (date) => {
            if (!date) return 'N/A';
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' ' + 
                   date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        };
        
        const opacityClass = isDeparted ? 'opacity-60' : '';
        
        return `
            <div class="booking-card bg-white rounded-lg border-2 shadow-sm overflow-hidden hover:shadow-md transition-shadow ${opacityClass}" style="border-color: ${outlineColor};">
                <div class="px-3 py-2" style="background-color: ${outlineColor}15;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: ${outlineColor};"></div>
                            <span class="font-semibold text-gray-800 text-sm">${booking.function_type || 'Event'}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: ${isDeparted ? '#6b7280' : functionColor};">
                            ${isDeparted ? 'Departed' : (booking.booking_type || booking.function_type || 'Event')}
                        </span>
                    </div>
                    <div class="text-xs text-gray-600 mt-1 font-medium">${booking.name || 'Guest'}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-users mr-1"></i>${booking.guest_count || '0'} guests
                        <span class="mx-2">|</span>
                        <i class="fas fa-phone mr-1"></i>${booking.contact_number || 'N/A'}
                    </div>
                </div>
                <div class="px-3 py-2 border-t border-gray-100">
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-gray-400 block">Check In</span>
                            <span class="text-gray-700 font-medium"><i class="fas fa-sign-in-alt text-green-500 mr-1"></i>${formatDateTime(checkIn)}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Check Out</span>
                            <span class="text-gray-700 font-medium"><i class="fas fa-sign-out-alt text-red-500 mr-1"></i>${formatDateTime(checkOut)}</span>
                        </div>
                    </div>
                </div>
                <div class="px-3 py-2 bg-gray-50">
                    <p class="text-xs text-gray-600 mb-1 font-medium">Rooms:</p>
                    <div class="flex flex-wrap gap-1">
                        ${rooms.map(room => `
                            <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: ${outlineColor};">${room}</span>
                        `).join('')}
                    </div>
                </div>
                <div class="px-3 py-2 border-t border-gray-100">
                    <!-- Assigned Staff Section -->
                    <div class="mb-2">
                        <p class="text-xs text-gray-500 mb-1"><i class="fas fa-user-check mr-1"></i>Assigned Staff:</p>
                        <div class="function-staff-list flex flex-wrap gap-1" id="function-staff-${booking.id}">
                            <span class="text-xs text-gray-400 italic">Drop staff here</span>
                        </div>
                    </div>
                    <button onclick='showBookingDetails(${JSON.stringify(booking).replace(/'/g, "&#39;")}, "${outlineColor}")' 
                        class="w-full text-xs py-1.5 rounded-md font-medium text-white transition-colors hover:opacity-90"
                        style="background-color: ${outlineColor};">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </button>
                </div>
            </div>
        `;
    };
    
    // Render Active Bookings Section
    if (activeBookings.length > 0) {
        html += `
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2 px-1">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-xs font-bold text-green-700 uppercase tracking-wide">Active Bookings (${activeBookings.length})</span>
                </div>
                <div class="space-y-3">
        `;
        activeBookings.forEach((booking, index) => {
            html += renderBookingCard(booking, index, false);
        });
        html += '</div></div>';
    }
    
    // Render Departed Bookings Section
    if (departedBookings.length > 0) {
        html += `
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2 px-1">
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Departed (${departedBookings.length})</span>
                </div>
                <div class="space-y-3">
        `;
        departedBookings.forEach((booking, index) => {
            html += renderBookingCard(booking, index, true);
        });
        html += '</div></div>';
    }
    
    bookingsList.innerHTML = html;
    
    // Setup drop zones for booking cards and load existing assignments
    setupBookingDropZones();
    loadFunctionAssignments();
    
    document.getElementById('totalBookings').textContent = bookings.length;
    document.getElementById('totalRoomsBooked').textContent = totalRooms;
}

function highlightBookedRooms(bookings) {
    // Reset all section highlights including animation
    document.querySelectorAll('.section-box').forEach(section => {
        section.classList.remove('booked-room', 'departed-room', 'turnover-room');
        section.style.boxShadow = '';
        section.style.animation = '';
        section.style.outline = '';
        section.style.outlineOffset = '';
    });
    
    // Get viewing time (from slider or current time)
    const viewTime = getViewingTime();
    
    // Separate bookings into active and departed based on viewing time
    const activeBookings = [];
    const departedBookings = [];
    
    bookings.forEach(booking => {
        const checkOut = booking.end ? new Date(booking.end) : null;
        if (checkOut && viewTime > checkOut) {
            departedBookings.push(booking);
        } else {
            activeBookings.push(booking);
        }
    });
    
    // Track rooms: which are active, which are departed, and which have turnover (departed + upcoming active)
    const activeRoomMap = new Map(); // room -> { color, booking }
    const departedRoomMap = new Map(); // room -> { color, booking }
    const turnoverRooms = new Set(); // rooms that have both departed and active bookings
    
    let hasWedding = false;
    
    // Process active bookings first
    activeBookings.forEach((booking, index) => {
        const color = getBookingColor(booking);
        let rooms = [];
        try {
            rooms = JSON.parse(booking.room_numbers);
            if (!Array.isArray(rooms)) rooms = [rooms];
        } catch (e) {
            rooms = booking.room_numbers ? [booking.room_numbers] : [];
        }
        rooms.forEach(room => {
            const roomName = room.trim();
            activeRoomMap.set(roomName, { color, booking });
        });
        
        const bookingType = (booking.booking_type || booking.function_type || '').toLowerCase();
        if (bookingType.includes('wedding')) {
            hasWedding = true;
        }
    });
    
    // Process departed bookings
    departedBookings.forEach((booking, index) => {
        let rooms = [];
        try {
            rooms = JSON.parse(booking.room_numbers);
            if (!Array.isArray(rooms)) rooms = [rooms];
        } catch (e) {
            rooms = booking.room_numbers ? [booking.room_numbers] : [];
        }
        rooms.forEach(room => {
            const roomName = room.trim();
            // Check if this room also has an active booking (turnover situation)
            if (activeRoomMap.has(roomName)) {
                turnoverRooms.add(roomName);
            } else {
                departedRoomMap.set(roomName, { booking });
            }
        });
    });
    
    // Highlight rooms on the map
    sections.forEach(section => {
        const sectionEl = document.querySelector(`[data-section-id="${section.id}"]`);
        if (!sectionEl) return;
        
        const roomName = section.name;
        
        // Priority: Turnover > Active > Departed
        if (turnoverRooms.has(roomName)) {
            // Turnover room: has departed booking AND new active booking
            // Show with pulsing animation and special indicator
            const activeData = activeRoomMap.get(roomName);
            const color = activeData ? activeData.color : '#22c55e';
            sectionEl.style.boxShadow = `0 0 0 4px ${color}, 0 0 15px ${color}90`;
            sectionEl.style.outline = '3px dashed #f59e0b';
            sectionEl.style.outlineOffset = '6px';
            sectionEl.style.animation = 'pulse-turnover 1.5s infinite';
            sectionEl.classList.add('turnover-room');
        } else if (activeRoomMap.has(roomName)) {
            // Active booking - solid colored border
            const { color } = activeRoomMap.get(roomName);
            sectionEl.style.boxShadow = `0 0 0 4px ${color}, 0 0 15px ${color}90`;
        } else if (departedRoomMap.has(roomName)) {
            // Departed booking - dashed gray border
            sectionEl.style.outline = '3px dashed #6b7280';
            sectionEl.style.outlineOffset = '2px';
            sectionEl.classList.add('departed-room');
        }
    });
    
    // Highlight Banquet Hall if there's a wedding
    if (hasWedding) {
        const banquetEl = document.querySelector('[data-section-id="banquet-hall"]');
        if (banquetEl) {
            banquetEl.style.boxShadow = '0 0 0 4px #ef4444, 0 0 20px #ef444490';
            banquetEl.style.animation = 'pulse-wedding 2s infinite';
        }
    }
}

function handleDateChange(date) {
    currentDate = date;
    // Clear current UI assignments (not database)
    clearAllAssignmentsQuiet();
    
    // Load bookings, allocations, and staff on leave for the new date
    loadAllocations(date);
    loadBookings(date);
    loadStaffOnLeave(date);
    
    // Update Assign Tasks button URL
    const assignTasksBtn = document.getElementById('assignTasksBtn');
    if (assignTasksBtn) {
        assignTasksBtn.href = `{{ route('duty.roster.assign.tasks') }}?date=${date}`;
    }
    
    // Format and display the selected date
    const dateObj = new Date(date);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = dateObj.toLocaleDateString('en-US', options);
    
    // Update bookings sidebar title with date
    const today = new Date().toISOString().split('T')[0];
    const bookingsTitle = document.getElementById('bookingsDateTitle');
    if (bookingsTitle) {
        if (date === today) {
            bookingsTitle.textContent = "Today's Bookings";
        } else {
            const shortDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            bookingsTitle.textContent = `Bookings - ${shortDate}`;
        }
    }
    
    // Refresh Command Center widgets for new date
    loadCommandCenterData();
    loadFraudReport();
    loadArrivalsAndDepartures();
    loadTodayTasks();
    
    // Show notification
    showNotification(`Viewing allocations for: ${formattedDate}`);
}

function goToToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('allocationDate').value = today;
    handleDateChange(today);
}

function goToPreviousDay() {
    const currentDate = document.getElementById('allocationDate').value;
    const prevDate = new Date(currentDate);
    prevDate.setDate(prevDate.getDate() - 1);
    const dateStr = prevDate.toISOString().split('T')[0];
    document.getElementById('allocationDate').value = dateStr;
    handleDateChange(dateStr);
}

function goToTomorrow() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateStr = tomorrow.toISOString().split('T')[0];
    document.getElementById('allocationDate').value = dateStr;
    handleDateChange(dateStr);
}

function goToNextDay() {
    const currentDateStr = document.getElementById('allocationDate').value;
    const currentDate = new Date(currentDateStr);
    currentDate.setDate(currentDate.getDate() + 1);
    const dateStr = currentDate.toISOString().split('T')[0];
    document.getElementById('allocationDate').value = dateStr;
    handleDateChange(dateStr);
}

function clearAllAssignmentsQuiet() {
    const staffIds = Object.keys(assignments);
    staffIds.forEach(staffId => {
        delete assignments[staffId];
        
        const staffCard = document.getElementById(`staff-${staffId}`);
        if (staffCard) {
            staffCard.querySelector('.assigned-indicator').classList.add('hidden');
            staffCard.querySelector('.assignment-info').classList.add('hidden');
        }
    });
    
    sections.forEach(section => {
        updateSectionAvatars(section.id);
    });
    updateStats();
}

function showNotification(message) {
    // Remove existing notification
    const existing = document.querySelector('.date-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = 'date-notification fixed top-20 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in';
    notification.innerHTML = `<i class="fas fa-calendar-check mr-2"></i>${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Booking Details Modal Functions
let currentModalBooking = null; // Store current booking for edit/print actions

function showBookingDetails(booking, color) {
    currentModalBooking = booking; // Store for later use
    
    const modal = document.getElementById('bookingDetailModal');
    const title = document.getElementById('modalBookingTitle');
    const content = document.getElementById('modalBookingContent');
    const header = document.getElementById('modalHeader');
    
    // Set header color
    header.style.background = `linear-gradient(to right, ${color}, ${color}dd)`;
    
    // Set title
    title.textContent = booking.name || 'Booking Details';
    
    // Parse rooms
    let rooms = [];
    try {
        rooms = JSON.parse(booking.room_numbers);
        if (!Array.isArray(rooms)) rooms = [rooms];
    } catch (e) {
        rooms = booking.room_numbers ? [booking.room_numbers] : [];
    }
    
    // Format dates
    const formatFullDateTime = (dateStr) => {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }) + ' at ' + date.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            hour12: true 
        });
    };
    
    // Build content
    content.innerHTML = `
        <div class="space-y-4">
            <!-- Function Type Badge -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Function Type</span>
                <span class="px-3 py-1 rounded-full text-white text-sm font-medium" 
                    style="background-color: ${functionTypeColors[booking.function_type] || functionTypeColors['Other']};">
                    ${booking.function_type || 'Event'}
                </span>
            </div>
            
            <!-- Check In/Out Times -->
            <div class="bg-gray-50 rounded-lg p-3 space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-sign-in-alt text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Check In</p>
                        <p class="text-sm font-semibold text-gray-800">${formatFullDateTime(booking.start)}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-sign-out-alt text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Check Out</p>
                        <p class="text-sm font-semibold text-gray-800">${formatFullDateTime(booking.end)}</p>
                    </div>
                </div>
            </div>
            
            <!-- Guest Info -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">Guest Count</p>
                    <p class="text-lg font-bold text-blue-800"><i class="fas fa-users mr-2"></i>${booking.guest_count || '0'}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xs text-purple-600 uppercase tracking-wide mb-1">Contact</p>
                    <p class="text-sm font-semibold text-purple-800"><i class="fas fa-phone mr-2"></i>${booking.contact_number || 'N/A'}</p>
                </div>
            </div>
            
            <!-- Rooms -->
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Assigned Rooms</p>
                <div class="flex flex-wrap gap-2">
                    ${rooms.map(room => `
                        <span class="px-3 py-1.5 rounded-lg text-white text-sm font-medium" style="background-color: ${color};">${room}</span>
                    `).join('')}
                </div>
            </div>
            
            <!-- Additional Details -->
            ${booking.bites_details ? `
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Bites Details</p>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">${booking.bites_details}</p>
            </div>
            ` : ''}
            
            ${booking.other_details ? `
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Other Details</p>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">${booking.other_details}</p>
            </div>
            ` : ''}
            
            <!-- Payment Info -->
            ${booking.advancePayments && booking.advancePayments.length > 0 ? `
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Payment History</p>
                <div class="space-y-2">
                    ${booking.advancePayments.map(payment => `
                        <div class="flex items-center justify-between bg-green-50 rounded-lg p-2">
                            <div>
                                <span class="text-sm font-semibold text-green-800">Rs. ${parseFloat(payment.amount).toLocaleString()}</span>
                                <span class="text-xs text-green-600 ml-2">${payment.method || ''}</span>
                            </div>
                            <div class="text-xs text-green-600">
                                ${payment.date} | Bill #${payment.billNumber || 'N/A'}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    // Show modal
    modal.classList.remove('hidden');
}

function closeBookingModal() {
    document.getElementById('bookingDetailModal').classList.add('hidden');
    currentModalBooking = null;
}

// Edit current booking - opens the booking edit form
function editCurrentBooking() {
    if (!currentModalBooking || !currentModalBooking.id) {
        showNotification('Unable to edit: Booking ID not found', 'error');
        return;
    }
    
    // Redirect to calendar with the booking date and trigger edit
    const bookingDate = currentModalBooking.start ? new Date(currentModalBooking.start) : new Date();
    const dateStr = bookingDate.toISOString().split('T')[0];
    
    // Open in new tab or redirect to calendar with booking ID
    window.location.href = `/calendar?edit=${currentModalBooking.id}&date=${dateStr}`;
}

// Print booking confirmation
function printBookingConfirmation() {
    if (!currentModalBooking || !currentModalBooking.id) {
        showNotification('Unable to print: Booking ID not found', 'error');
        return;
    }
    
    // Open print confirmation in new window
    window.open(`/bookings/${currentModalBooking.id}/print-confirmation`, '_blank');
}

// Close modal when clicking outside
document.getElementById('bookingDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBookingModal();
    }
});

// ============================================
// Database API Functions for Staff Allocations
// ============================================

// Load allocations from database for a specific date
async function loadAllocations(date) {
    try {
        const response = await fetch(`/api/duty-roster/allocations?date=${date}`);
        const data = await response.json();
        
        if (data.allocations && data.allocations.length > 0) {
            // Apply each saved allocation to the UI
            data.allocations.forEach(allocation => {
                const staffId = allocation.person_id;
                const sectionId = allocation.section_id;
                const sectionName = allocation.section_name;
                
                // Update local state
                assignments[staffId] = sectionId;
                
                // Update staff card UI
                const staffCard = document.getElementById(`staff-${staffId}`);
                if (staffCard) {
                    staffCard.querySelector('.assigned-indicator').classList.remove('hidden');
                    staffCard.querySelector('.assignment-info').classList.remove('hidden');
                    staffCard.querySelector('.location-badge').textContent = sectionName;
                }
                
                // Update section avatars
                updateSectionAvatars(sectionId);
            });
            
            updateStats();
            console.log(`Loaded ${data.allocations.length} allocations for ${date}`);
        }
    } catch (error) {
        console.error('Error loading allocations:', error);
    }
}

// Save allocation to database
async function saveAllocationToDb(staffId, sectionId, sectionName) {
    try {
        const response = await fetch('/api/duty-roster/allocations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                person_id: staffId,
                section_id: sectionId,
                section_name: sectionName,
                allocation_date: currentDate,
            }),
        });
        
        const data = await response.json();
        if (data.success) {
            console.log('Allocation saved:', data.message);
        }
    } catch (error) {
        console.error('Error saving allocation:', error);
    }
}

// Remove allocation from database
async function removeAllocationFromDb(staffId) {
    try {
        const response = await fetch('/api/duty-roster/allocations', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                person_id: staffId,
                allocation_date: currentDate,
            }),
        });
        
        const data = await response.json();
        if (data.success) {
            console.log('Allocation removed:', data.message);
        }
    } catch (error) {
        console.error('Error removing allocation:', error);
    }
}

// Clear all allocations from database for current date
async function clearAllocationsFromDb() {
    try {
        const response = await fetch('/api/duty-roster/allocations/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                allocation_date: currentDate,
            }),
        });
        
        const data = await response.json();
        console.log('Allocations cleared:', data.message);
        return data;
    } catch (error) {
        console.error('Error clearing allocations:', error);
        throw error;
    }
}

// ============================================
// Staff On Leave Functions
// ============================================

// Load staff on leave for a specific date
async function loadStaffOnLeave(date) {
    try {
        const response = await fetch(`/api/duty-roster/staff-on-leave?date=${date}`);
        const data = await response.json();
        
        staffOnLeave = data.staffOnLeave || [];
        
        // Update UI
        updateStaffOnLeaveUI();
        
        // Mark staff cards as on leave
        markStaffOnLeave();
        
        console.log(`Loaded ${staffOnLeave.length} staff on leave for ${date}`);
    } catch (error) {
        console.error('Error loading staff on leave:', error);
    }
}

function updateStaffOnLeaveUI() {
    const section = document.getElementById('staffOnLeaveSection');
    const list = document.getElementById('staffOnLeaveList');
    const countEl = document.getElementById('leaveCount');
    const totalOnLeaveEl = document.getElementById('totalOnLeave');
    
    if (staffOnLeave.length > 0) {
        section.style.display = 'block';
        countEl.textContent = staffOnLeave.length;
        totalOnLeaveEl.textContent = staffOnLeave.length;
        
        list.innerHTML = staffOnLeave.map(staff => `
            <div class="flex items-center justify-between bg-white rounded p-2 border border-red-200">
                <span class="font-medium text-gray-700">${staff.person_name}</span>
                <span class="leave-badge">${staff.leave_type}</span>
            </div>
        `).join('');
    } else {
        section.style.display = 'none';
        countEl.textContent = '0';
        totalOnLeaveEl.textContent = '0';
        list.innerHTML = '';
    }
}

function markStaffOnLeave() {
    // First, remove on-leave class from all staff cards
    document.querySelectorAll('.staff-card').forEach(card => {
        card.classList.remove('on-leave');
        card.draggable = true;
    });
    
    // Mark staff on leave
    staffOnLeave.forEach(staff => {
        const card = document.getElementById(`staff-${staff.person_id}`);
        if (card) {
            card.classList.add('on-leave');
            card.draggable = false; // Disable dragging for staff on leave
        }
    });
}

// ============================================
// Print Roster Function
// ============================================

async function printRoster() {
    const date = document.getElementById('allocationDate').value;
    const dateObj = new Date(date);
    const formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    // Load tasks for this date
    let staffTasks = {};
    try {
        const response = await fetch(`/api/duty-roster/tasks?date=${date}`);
        const data = await response.json();
        data.tasks.forEach(task => {
            if (!staffTasks[task.person_id]) {
                staffTasks[task.person_id] = [];
            }
            staffTasks[task.person_id].push(task.task);
        });
    } catch (error) {
        console.error('Error loading tasks for print:', error);
    }
    
    // Build print content - compact table-based layout
    let printContent = `
        <html>
        <head>
            <title>Duty Roster - ${formattedDate}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 10px; margin: 0; font-size: 11px; }
                .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 8px; margin-bottom: 10px; }
                .header h1 { color: #1e40af; margin: 0; font-size: 16px; }
                .header h2 { color: #666; font-weight: normal; margin: 2px 0 0 0; font-size: 11px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                th { background: #1e40af; color: white; padding: 6px 8px; text-align: left; font-size: 11px; font-weight: 600; }
                td { padding: 4px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
                .section-header { background: #f1f5f9; font-weight: 600; color: #1e40af; }
                .staff-name { font-weight: 500; }
                .tasks { color: #059669; font-size: 10px; }
                .no-tasks { color: #9ca3af; font-style: italic; font-size: 10px; }
                .function-header { background: #059669; color: white; }
                .leave-row { background: #fef2f2; color: #dc2626; }
                .stats { display: flex; justify-content: center; gap: 40px; margin-top: 10px; padding: 8px; background: #f8fafc; border-radius: 4px; }
                .stat-item { text-align: center; }
                .stat-value { font-size: 18px; font-weight: bold; color: #1e40af; }
                .stat-label { color: #6b7280; font-size: 9px; text-transform: uppercase; }
                @media print { 
                    body { padding: 5px; }
                    table { page-break-inside: auto; }
                    tr { page-break-inside: avoid; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🏨 Hotel Soba Lanka - Duty Roster</h1>
                <h2>${formattedDate}</h2>
            </div>
    `;
    
    // Start main table
    printContent += `<table><thead><tr><th style="width:30%">Location</th><th style="width:25%">Staff</th><th>Tasks</th></tr></thead><tbody>`;
    
    // Add staff on leave section
    if (staffOnLeave.length > 0) {
        printContent += `<tr class="leave-row"><td colspan="3"><strong>⚠️ On Leave:</strong> ${staffOnLeave.map(s => s.person_name).join(', ')}</td></tr>`;
    }
    
    // Group assignments by section
    const sectionAssignments = {};
    sections.forEach(section => {
        sectionAssignments[section.id] = {
            name: section.name,
            type: section.type,
            staff: []
        };
    });
    
    Object.entries(assignments).forEach(([staffId, sectionId]) => {
        const card = document.getElementById(`staff-${staffId}`);
        if (card && sectionAssignments[sectionId]) {
            sectionAssignments[sectionId].staff.push({
                id: staffId,
                name: card.dataset.staffName
            });
        }
    });
    
    // Add sections to print content as table rows
    Object.values(sectionAssignments).forEach(section => {
        if (section.staff.length > 0) {
            section.staff.forEach((staff, idx) => {
                const tasks = staffTasks[staff.id] || [];
                const taskText = tasks.length > 0 ? tasks.join('; ') : '<span class="no-tasks">-</span>';
                if (idx === 0) {
                    printContent += `<tr><td rowspan="${section.staff.length}" class="section-header">${section.name}</td><td class="staff-name">${staff.name}</td><td class="tasks">${taskText}</td></tr>`;
                } else {
                    printContent += `<tr><td class="staff-name">${staff.name}</td><td class="tasks">${taskText}</td></tr>`;
                }
            });
        }
    });
    
    // Add function/booking assignments as table rows
    if (Object.keys(functionAssignments).length > 0) {
        printContent += `<tr><th colspan="3" class="function-header">📋 Function/Event Assignments</th></tr>`;
        
        Object.entries(functionAssignments).forEach(([bookingId, staffList]) => {
            if (staffList.length > 0) {
                const staffListEl = document.getElementById(`function-staff-${bookingId}`);
                let bookingName = `Booking #${bookingId}`;
                if (staffListEl) {
                    const card = staffListEl.closest('.booking-card');
                    if (card) {
                        const nameEl = card.querySelector('.font-semibold');
                        if (nameEl) bookingName = nameEl.textContent;
                    }
                }
                
                staffList.forEach((s, idx) => {
                    const tasks = staffTasks[s.person_id] || [];
                    const taskText = tasks.length > 0 ? tasks.join('; ') : '<span class="no-tasks">-</span>';
                    if (idx === 0) {
                        printContent += `<tr><td rowspan="${staffList.length}" class="section-header" style="color:#059669">${bookingName}</td><td class="staff-name">${s.person_name}</td><td class="tasks">${taskText}</td></tr>`;
                    } else {
                        printContent += `<tr><td class="staff-name">${s.person_name}</td><td class="tasks">${taskText}</td></tr>`;
                    }
                });
            }
        });
    }
    
    // Close table
    printContent += `</tbody></table>`;
    
    // Add stats
    const totalAssigned = Object.keys(assignments).length;
    const totalStaff = document.querySelectorAll('.staff-card').length;
    
    printContent += `
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-value">${totalStaff}</div>
                    <div class="stat-label">Total Staff</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${totalAssigned}</div>
                    <div class="stat-label">Assigned</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${staffOnLeave.length}</div>
                    <div class="stat-label">On Leave</div>
                </div>
            </div>
        </body>
        </html>
    `;
    
    // Open print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
    }, 250);
}

// ============================================
// Function/Booking Staff Assignment Functions
// ============================================

// Store for function assignments { bookingId: [{ person_id, person_name, role }] }
let functionAssignments = {};

// Setup booking cards as drop zones
function setupBookingDropZones() {
    document.querySelectorAll('.booking-card').forEach(card => {
        card.addEventListener('dragover', handleBookingDragOver);
        card.addEventListener('dragleave', handleBookingDragLeave);
        card.addEventListener('drop', handleBookingDrop);
    });
}

function handleBookingDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.currentTarget.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
}

function handleBookingDragLeave(e) {
    e.currentTarget.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
}

function handleBookingDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
    
    // Get staff ID from dataTransfer or from the global variable
    let staffId = e.dataTransfer.getData('text/plain') || draggedStaffId;
    
    if (staffId) {
        // Find the booking ID from the staff list container
        const staffListEl = e.currentTarget.querySelector('.function-staff-list');
        if (staffListEl) {
            const bookingId = staffListEl.id.replace('function-staff-', '');
            console.log('Dropping staff', staffId, 'onto booking', bookingId);
            assignStaffToFunction(staffId, bookingId);
        }
    }
}

// Assign staff to a function/booking
async function assignStaffToFunction(staffId, bookingId) {
    const staffCard = document.getElementById(`staff-${staffId}`);
    if (!staffCard) return;
    
    const staffName = staffCard.dataset.staffName;
    
    try {
        const response = await fetch('/api/duty-roster/function-assignment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                booking_id: bookingId,
                person_id: staffId,
            }),
        });
        
        const data = await response.json();
        if (data.success) {
            // Update local state
            if (!functionAssignments[bookingId]) {
                functionAssignments[bookingId] = [];
            }
            
            // Check if already assigned
            if (!functionAssignments[bookingId].find(a => a.person_id == staffId)) {
                functionAssignments[bookingId].push({
                    person_id: staffId,
                    person_name: staffName,
                    role: null
                });
            }
            
            // Update UI
            updateFunctionStaffList(bookingId);
            showNotification(`${staffName} assigned to function`);
        }
    } catch (error) {
        console.error('Error assigning staff to function:', error);
    }
}

// Remove staff from a function/booking
async function removeStaffFromFunction(staffId, bookingId) {
    try {
        const response = await fetch('/api/duty-roster/function-assignment', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                booking_id: bookingId,
                person_id: staffId,
            }),
        });
        
        const data = await response.json();
        if (data.success) {
            // Update local state
            if (functionAssignments[bookingId]) {
                functionAssignments[bookingId] = functionAssignments[bookingId].filter(a => a.person_id != staffId);
            }
            
            // Update UI
            updateFunctionStaffList(bookingId);
            showNotification('Staff removed from function');
        }
    } catch (error) {
        console.error('Error removing staff from function:', error);
    }
}

// Load all function assignments
async function loadFunctionAssignments() {
    try {
        const response = await fetch('/api/duty-roster/function-assignments');
        const data = await response.json();
        
        functionAssignments = {};
        
        // Convert the grouped data to our format
        if (data.assignments) {
            Object.entries(data.assignments).forEach(([bookingId, staffList]) => {
                functionAssignments[bookingId] = staffList;
                updateFunctionStaffList(bookingId);
            });
        }
        
        console.log('Loaded function assignments:', functionAssignments);
    } catch (error) {
        console.error('Error loading function assignments:', error);
    }
}

// Update the staff list UI for a booking
function updateFunctionStaffList(bookingId) {
    const listEl = document.getElementById(`function-staff-${bookingId}`);
    if (!listEl) return;
    
    const staff = functionAssignments[bookingId] || [];
    const parentSection = listEl.closest('.px-3');
    
    if (staff.length === 0) {
        listEl.innerHTML = '<span class="text-xs text-gray-400 italic">Drop staff here</span>';
        if (parentSection) {
            parentSection.classList.remove('bg-green-50', 'border-l-4', 'border-green-500');
        }
    } else {
        listEl.innerHTML = staff.map(s => `
            <span class="inline-flex items-center gap-1 text-sm font-medium bg-green-500 text-white px-3 py-1 rounded-full shadow-sm">
                <i class="fas fa-user-check text-xs"></i>
                ${s.person_name}
                <button onclick="removeStaffFromFunction(${s.person_id}, ${bookingId})" class="text-green-200 hover:text-white ml-1 font-bold" title="Remove">×</button>
            </span>
        `).join('');
        if (parentSection) {
            parentSection.classList.add('bg-green-50', 'border-l-4', 'border-green-500');
        }
    }
}

// ==================== OWNER'S PERSONAL TASKS (MY PRIORITY LIST) ====================

let ownerTaskListVisible = true;

// Load owner tasks on page load
document.addEventListener('DOMContentLoaded', function() {
    loadOwnerTasks();
});

// Toggle owner task list visibility
function toggleOwnerTaskList() {
    ownerTaskListVisible = !ownerTaskListVisible;
    const list = document.getElementById('ownerTaskList');
    const arrow = document.getElementById('ownerTaskArrow');
    
    if (ownerTaskListVisible) {
        list.classList.remove('hidden');
        arrow.style.transform = 'rotate(0deg)';
    } else {
        list.classList.add('hidden');
        arrow.style.transform = 'rotate(-90deg)';
    }
}

// Load owner tasks from API
async function loadOwnerTasks() {
    try {
        const response = await fetch('/api/duty-roster/owner-tasks');
        const data = await response.json();
        
        if (data.success) {
            renderOwnerTasks(data.tasks);
            document.getElementById('ownerTaskCount').textContent = data.stats.pending;
        }
    } catch (error) {
        console.error('Error loading owner tasks:', error);
        document.getElementById('ownerTaskList').innerHTML = `
            <div class="text-center py-2 text-red-500 text-xs">
                <i class="fas fa-exclamation-circle"></i> Failed to load
            </div>
        `;
    }
}

// Render owner tasks list
function renderOwnerTasks(tasks) {
    const container = document.getElementById('ownerTaskList');
    
    if (!tasks || tasks.length === 0) {
        container.innerHTML = `
            <div class="text-center py-3 text-amber-600 text-xs italic">
                <i class="fas fa-clipboard-list"></i> No reminders yet
            </div>
        `;
        return;
    }
    
    // Show pending first, then completed
    const pendingTasks = tasks.filter(t => !t.is_done);
    const completedTasks = tasks.filter(t => t.is_done).slice(0, 3); // Show max 3 completed
    
    let html = '';
    
    // Pending tasks
    pendingTasks.forEach(task => {
        const priorityColor = task.priority === 'High' ? 'text-red-600' : 
                             task.priority === 'Low' ? 'text-gray-500' : 'text-amber-700';
        const overdueClass = task.is_overdue ? 'border-l-2 border-red-500 pl-1' : '';
        
        html += `
            <div class="flex items-start gap-2 p-1.5 bg-white/70 rounded-lg shadow-sm hover:bg-white transition-colors ${overdueClass}" data-task-id="${task.id}">
                <button onclick="toggleOwnerTaskStatus(${task.id})" class="mt-0.5 w-4 h-4 rounded border-2 border-amber-400 hover:border-amber-600 flex-shrink-0 flex items-center justify-center transition-colors">
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-800 leading-tight break-words">${escapeHtml(task.task)}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] ${priorityColor} font-medium">${task.priority}</span>
                        ${task.is_overdue ? '<span class="text-[10px] text-red-500 font-medium">OVERDUE</span>' : ''}
                    </div>
                </div>
                <button onclick="deleteOwnerTask(${task.id})" class="text-gray-400 hover:text-red-500 text-xs flex-shrink-0 opacity-0 hover:opacity-100 transition-opacity" title="Delete">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });
    
    // Completed tasks (collapsed)
    if (completedTasks.length > 0) {
        html += `<div class="border-t border-amber-200 mt-2 pt-1">`;
        completedTasks.forEach(task => {
            html += `
                <div class="flex items-start gap-2 p-1 opacity-60" data-task-id="${task.id}">
                    <button onclick="toggleOwnerTaskStatus(${task.id})" class="mt-0.5 w-4 h-4 rounded border-2 border-green-400 bg-green-400 flex-shrink-0 flex items-center justify-center">
                        <i class="fas fa-check text-white text-[8px]"></i>
                    </button>
                    <p class="text-xs text-gray-500 line-through flex-1 break-words">${escapeHtml(task.task)}</p>
                    <button onclick="deleteOwnerTask(${task.id})" class="text-gray-400 hover:text-red-500 text-xs flex-shrink-0" title="Delete">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });
        html += `</div>`;
    }
    
    container.innerHTML = html;
}

// Add quick task
async function addQuickTask() {
    const input = document.getElementById('quickTaskInput');
    const taskText = input.value.trim();
    
    if (!taskText) {
        input.focus();
        return;
    }
    
    // Disable input while saving
    input.disabled = true;
    
    try {
        const response = await fetch('/api/duty-roster/owner-tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ task: taskText, priority: 'Medium' }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            input.value = '';
            loadOwnerTasks(); // Reload the list
            showNotification('Reminder added!');
        } else {
            showNotification('Failed to add reminder', 'error');
        }
    } catch (error) {
        console.error('Error adding task:', error);
        showNotification('Failed to add reminder', 'error');
    } finally {
        input.disabled = false;
        input.focus();
    }
}

// Toggle task completion status
async function toggleOwnerTaskStatus(taskId) {
    try {
        const response = await fetch(`/api/duty-roster/owner-tasks/${taskId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadOwnerTasks(); // Reload the list
        }
    } catch (error) {
        console.error('Error toggling task:', error);
    }
}

// Delete owner task
async function deleteOwnerTask(taskId) {
    try {
        const response = await fetch(`/api/duty-roster/owner-tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadOwnerTasks(); // Reload the list
            showNotification('Reminder deleted');
        }
    } catch (error) {
        console.error('Error deleting task:', error);
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==================== STAFF OUT (GATE PASS) SUMMARY ====================

async function loadStaffOut() {
    const date = document.getElementById('staffOutDatePicker')?.value || new Date().toISOString().split('T')[0];
    await loadStaffOutForDate(date);
}

async function loadStaffOutForDate(date) {
    try {
        const response = await fetch(`/api/duty-roster/staff-out?date=${date}`);
        const data = await response.json();
        
        if (data.success) {
            // Update stats
            document.getElementById('staffOutTotal').textContent = data.stats.total_today;
            document.getElementById('staffOutCurrently').textContent = data.stats.currently_out;
            document.getElementById('staffOutReturned').textContent = data.stats.returned;
            document.getElementById('staffOutOverdue').textContent = data.stats.overdue;
            
            // Render list
            renderStaffOutList(data.passes);
        }
    } catch (error) {
        console.error('Error loading staff out data:', error);
        document.getElementById('staffOutList').innerHTML = '<div class="text-center text-red-500 text-xs py-2">Error loading data</div>';
    }
}

function renderStaffOutList(passes) {
    const list = document.getElementById('staffOutList');
    
    if (!passes || passes.length === 0) {
        list.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs"><i class="fas fa-check-circle text-green-500 mr-1"></i> No gate passes for this day</div>';
        return;
    }
    
    // Sort: currently out first (with overdue at top), then returned
    const sorted = [...passes].sort((a, b) => {
        if (a.is_out && !b.is_out) return -1;
        if (!a.is_out && b.is_out) return 1;
        if (a.is_overdue && !b.is_overdue) return -1;
        if (!a.is_overdue && b.is_overdue) return 1;
        return 0;
    });
    
    let html = '';
    
    sorted.forEach(pass => {
        let statusBadge = '';
        let borderClass = '';
        let bgClass = 'bg-white';
        
        if (pass.is_out) {
            if (pass.is_overdue) {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 animate-pulse"><i class="fas fa-exclamation-triangle mr-1"></i>OVERDUE</span>';
                borderClass = 'border-l-4 border-red-500';
                bgClass = 'bg-red-50';
            } else {
                statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700"><i class="fas fa-walking mr-1"></i>OUT</span>';
                borderClass = 'border-l-4 border-rose-500';
                bgClass = 'bg-rose-50';
            }
        } else {
            statusBadge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-700"><i class="fas fa-check mr-1"></i>Returned</span>';
            borderClass = '';
            bgClass = 'bg-gray-50';
        }
        
        html += `
            <div class="p-2 rounded-lg ${bgClass} ${borderClass} shadow-sm">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-semibold text-sm text-gray-800">${escapeHtml(pass.staff_name)}</span>
                    ${statusBadge}
                </div>
                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-gray-600">
                    <div><i class="fas fa-tag text-gray-400 mr-1"></i>${escapeHtml(pass.purpose)}</div>
                    <div><i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>${escapeHtml(pass.destination || 'N/A')}</div>
                    <div><i class="fas fa-sign-out-alt text-rose-400 mr-1"></i>Out: ${pass.exit_time || '-'}</div>
                    <div><i class="fas fa-clock text-amber-400 mr-1"></i>Expected: ${pass.expected_return || '-'}</div>
                    ${pass.actual_return ? `<div class="col-span-2"><i class="fas fa-sign-in-alt text-green-400 mr-1"></i>Returned: ${pass.actual_return}</div>` : ''}
                </div>
                ${pass.contact ? `<div class="mt-1 text-xs text-gray-500"><i class="fas fa-phone text-gray-400 mr-1"></i>${pass.contact}</div>` : ''}
            </div>
        `;
    });
    
    list.innerHTML = html;
}

function refreshStaffOut() {
    loadStaffOut();
}

function loadStaffOutForToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('staffOutDatePicker').value = today;
    loadStaffOutForDate(today);
}

function loadStaffOutForYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    document.getElementById('staffOutDatePicker').value = yesterdayStr;
    loadStaffOutForDate(yesterdayStr);
}

// ==================== COMMAND CENTER WIDGETS ====================

// Helper: scroll to a widget by ID
function scrollToWidget(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ===== 1. Command Center Quick Stats =====
async function loadCommandCenterData() {
    try {
        const date = document.getElementById('allocationDate')?.value || new Date().toISOString().split('T')[0];
        const response = await fetch(`/api/duty-roster/command-center?date=${date}`);
        const data = await response.json();
        if (data.success) {
            const s = data.summary;
            document.getElementById('ccArrivals').textContent = s.arrivals;
            document.getElementById('ccDepartures').textContent = s.departures;
            document.getElementById('ccInHouse').textContent = s.in_house;
            document.getElementById('ccDirtyRooms').textContent = s.rooms_dirty;
            document.getElementById('ccTasksDue').textContent = (s.tasks_due_today || 0) + (s.tasks_overdue || 0);
            document.getElementById('ccLowStock').textContent = s.inventory_low + s.inventory_out;
            document.getElementById('ccPendingLeads').textContent = s.leads_pending;
            document.getElementById('ccFeedback').textContent = s.feedback_pending || 0;

            // Pulse animation on critical items
            if (s.rooms_dirty > 0) document.getElementById('ccDirtyRooms').classList.add('animate-pulse');
            if (s.inventory_out > 0) document.getElementById('ccLowStock').classList.add('animate-pulse');
            if (s.leads_overdue > 0) document.getElementById('ccPendingLeads').classList.add('animate-pulse');
            if (s.tasks_overdue > 0) document.getElementById('ccTasksDue').classList.add('animate-pulse');
            if (s.feedback_pending > 0) document.getElementById('ccFeedback').classList.add('animate-pulse');
        }
    } catch (error) {
        console.error('Error loading command center data:', error);
    }
}

// ===== FRAUD ALERT (Security Monitor) =====
async function loadFraudReport() {
    try {
        @if(auth()->user() && auth()->user()->role === 'admin')
        const dateInput = document.getElementById('allocationDate');
        let selectedDate;
        
        if (dateInput && dateInput._flatpickr) {
            // Get from flatpickr if initialized
            selectedDate = dateInput._flatpickr.selectedDates[0];
            if (selectedDate) {
                // Format to YYYY-MM-DD avoiding timezone offset issues
                const year = selectedDate.getFullYear();
                const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const day = String(selectedDate.getDate()).padStart(2, '0');
                selectedDate = `${year}-${month}-${day}`;
            }
        }
        
        // Fallback to value or today
        if (!selectedDate) {
            selectedDate = dateInput?.value || new Date().toISOString().split('T')[0];
        }

        const response = await fetch(`/api/duty-roster/fraud-report?date=${selectedDate}`);
        const data = await response.json();
        
        if (data.success) {
            const count = data.stats.total_suspicious;
            const fraudCountEl = document.getElementById('fraudCount');
            if (fraudCountEl) fraudCountEl.textContent = count;
            
            const widget = document.getElementById('fraudAlertWidget');
            const content = document.getElementById('fraudAlertContent');
            
            if (!widget || !content) return;
            
            if (count === 0) {
                widget.className = 'mb-4 bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl shadow-lg border-2 border-green-300';
                content.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl mb-1"></i>
                        <p class="text-green-800 font-semibold text-sm">✓ All Clear</p>
                        <p class="text-green-600 text-xs">No suspicious activity detected for ${selectedDate}</p>
                    </div>
                `;
            } else {
                widget.className = 'mb-4 bg-gradient-to-r from-red-600 to-orange-600 rounded-xl shadow-lg border-2 border-red-300';
                let html = `
                    <div class="bg-red-50 border-l-4 border-red-600 p-2 mb-2 rounded">
                        <p class="text-red-800 font-bold text-xs">⚠️ ${count} Suspicious Transaction${count > 1 ? 's' : ''} Detected for ${selectedDate}</p>
                        <p class="text-red-600 text-xs">Potential loss: Rs. ${data.stats.potential_loss.toFixed(2)}</p>
                        <p class="text-red-600 text-xs">${data.stats.unique_staff} staff member${data.stats.unique_staff > 1 ? 's' : ''} involved</p>
                    </div>
                    <div class="max-h-48 overflow-y-auto space-y-2">
                `;
                
                data.activities.forEach(activity => {
                    html += `
                        <div class="bg-white border border-red-200 rounded p-2 text-xs">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-bold text-red-700">${activity.user_name}</span>
                                <span class="text-gray-500 text-[10px]">${new Date(activity.timestamp).toLocaleTimeString()}</span>
                            </div>
                            <div class="text-gray-700 space-y-0.5">
                                <p>• Table: <strong>${activity.table}</strong> (Sale #${activity.sale_id})</p>
                                <p>• Bill: Rs. ${parseFloat(activity.bill_amount).toFixed(2)}</p>
                                <p class="text-red-600 font-semibold">• Service Charge: Rs. ${parseFloat(activity.service_charge).toFixed(2)} 
                                   (Expected min: Rs. ${parseFloat(activity.expected_minimum).toFixed(2)})</p>
                            </div>
                        </div>
                    `;
                });
                
                html += `</div>`;
                content.innerHTML = html;
            }
        }
        @endif
    } catch (error) {
        console.error('Error loading fraud report:', error);
        const content = document.getElementById('fraudAlertContent');
        if (content) {
            content.innerHTML = `
                <div class="text-center py-2 text-red-500 text-xs">
                    <i class="fas fa-exclamation-circle"></i> Error loading report
                </div>
            `;
        }
    }
}

// ===== 2. Arrivals & Departures =====
async function loadArrivalsAndDepartures() {
    try {
        const date = document.getElementById('allocationDate')?.value || new Date().toISOString().split('T')[0];
        const response = await fetch(`/api/duty-roster/arrivals-departures?date=${date}`);
        const data = await response.json();
        if (data.success) {
            document.getElementById('arrivalsCount').textContent = data.stats.arrivals_count;
            document.getElementById('departuresCount').textContent = data.stats.departures_count;
            renderArrivalsList(data.arrivals);
            renderDeparturesList(data.departures);
        }
    } catch (error) {
        console.error('Error loading arrivals/departures:', error);
    }
}

function renderArrivalsList(arrivals) {
    const container = document.getElementById('arrivalsList');
    if (arrivals.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-gray-400 text-xs"><i class="fas fa-check-circle text-green-400"></i> No arrivals today</div>';
        return;
    }
    container.innerHTML = arrivals.map(a => `
        <div class="flex items-center gap-2 p-2 bg-emerald-50 rounded-lg mb-1.5 border border-emerald-100 hover:bg-emerald-100 transition cursor-pointer" onclick="window.location='/calendar'">
            <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                ${(a.name || 'G').charAt(0).toUpperCase()}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate">${a.name || 'Guest'}</p>
                <div class="flex items-center gap-2 text-[10px] text-gray-500">
                    <span><i class="fas fa-clock text-emerald-500"></i> ${a.check_in || 'TBD'}</span>
                    <span><i class="fas fa-door-open"></i> ${a.room_count} room(s)</span>
                    ${a.guest_count ? `<span><i class="fas fa-users"></i> ${a.guest_count}</span>` : ''}
                </div>
            </div>
            ${a.function_type ? `<span class="text-[9px] bg-emerald-200 text-emerald-800 px-1.5 py-0.5 rounded font-medium">${a.function_type}</span>` : ''}
        </div>
    `).join('');
}

function renderDeparturesList(departures) {
    const container = document.getElementById('departuresList');
    if (departures.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-gray-400 text-xs"><i class="fas fa-check-circle text-green-400"></i> No departures today</div>';
        return;
    }
    container.innerHTML = departures.map(d => `
        <div class="flex items-center gap-2 p-2 bg-orange-50 rounded-lg mb-1.5 border border-orange-100 hover:bg-orange-100 transition cursor-pointer" onclick="window.location='/calendar'">
            <div class="w-7 h-7 bg-orange-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                ${(d.name || 'G').charAt(0).toUpperCase()}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate">${d.name || 'Guest'}</p>
                <div class="flex items-center gap-2 text-[10px] text-gray-500">
                    <span><i class="fas fa-clock text-orange-500"></i> ${d.check_out || 'TBD'}</span>
                    <span><i class="fas fa-door-closed"></i> ${d.room_count} room(s)</span>
                    ${d.contact_number ? `<span><i class="fas fa-phone"></i> ${d.contact_number}</span>` : ''}
                </div>
            </div>
            ${d.function_type ? `<span class="text-[9px] bg-orange-200 text-orange-800 px-1.5 py-0.5 rounded font-medium">${d.function_type}</span>` : ''}
        </div>
    `).join('');
}

// ===== 3. Housekeeping Status =====
async function loadHousekeepingStatus() {
    try {
        const response = await fetch('/api/duty-roster/housekeeping-status');
        const data = await response.json();
        if (data.success) {
            updateHousekeepingStats(data.stats);
            renderHousekeepingGrid(data.rooms);
        }
    } catch (error) {
        console.error('Error loading housekeeping status:', error);
    }
}

function updateHousekeepingStats(stats) {
    document.getElementById('hkTotal').textContent = stats.total;
    document.getElementById('hkAvailable').textContent = stats.available;
    document.getElementById('hkOccupied').textContent = stats.occupied;
    document.getElementById('hkNeedsCleaning').textContent = stats.needs_cleaning;
}

function getRoomStatusStyle(status) {
    switch (status) {
        case 'available':
            return { bg: 'bg-green-100', text: 'text-green-700', icon: 'fa-check-circle', border: 'border-green-300', label: 'Available' };
        case 'occupied':
            return { bg: 'bg-yellow-100', text: 'text-yellow-700', icon: 'fa-user', border: 'border-yellow-300', label: 'Occupied' };
        case 'needs_cleaning':
            return { bg: 'bg-red-100', text: 'text-red-700', icon: 'fa-broom', border: 'border-red-300', label: 'Needs Cleaning' };
        default:
            return { bg: 'bg-green-100', text: 'text-green-700', icon: 'fa-check-circle', border: 'border-green-300', label: 'Available' };
    }
}

function renderHousekeepingGrid(rooms) {
    const container = document.getElementById('hkRoomGrid');
    if (rooms.length === 0) {
        container.innerHTML = '<div class="text-center py-2 text-gray-400 text-xs w-full">No rooms found</div>';
        return;
    }
    container.innerHTML = rooms.map(room => {
        const s = getRoomStatusStyle(room.status);
        const teamBorderStyle = room.team_color ? `border-l-4` : '';
        const teamBorderColor = room.team_color ? `style="border-left-color: ${room.team_color};"` : '';
        const teamTooltip = room.team_name ? ` | Team: ${room.team_name}` : '';
        return `
            <div class="relative ${s.bg} ${s.text} border ${s.border} ${teamBorderStyle} rounded-md px-2 py-1.5 text-center text-[10px] font-medium min-w-[60px] cursor-pointer select-none hover:opacity-80 active:scale-95 transition-all"
                 ${teamBorderColor}
                 title="${room.name}: ${s.label}${teamTooltip} (click to change)"
                 onclick="cycleRoomStatus(${room.id})"
                 id="hk-room-${room.id}">
                <i class="fas ${s.icon} text-[8px]"></i> ${room.name}
            </div>
        `;
    }).join('');
}

async function cycleRoomStatus(roomId) {
    const el = document.getElementById('hk-room-' + roomId);
    if (!el) return;
    el.style.opacity = '0.5';
    try {
        const response = await fetch('/api/duty-roster/cycle-room-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ room_id: roomId })
        });
        const data = await response.json();
        if (data.success) {
            const s = getRoomStatusStyle(data.new_status);
            el.className = `relative ${s.bg} ${s.text} border ${s.border} rounded-md px-2 py-1.5 text-center text-[10px] font-medium min-w-[60px] cursor-pointer select-none hover:opacity-80 active:scale-95 transition-all`;
            el.title = el.textContent.trim().split(' ').pop() + ': ' + s.label + ' (click to change)';
            const roomName = el.textContent.trim();
            el.innerHTML = `<i class="fas ${s.icon} text-[8px]"></i> ${roomName}`;
            updateHousekeepingStats(data.stats);
        }
    } catch (error) {
        console.error('Error cycling room status:', error);
    }
    el.style.opacity = '1';
}

function refreshHousekeeping() {
    loadHousekeepingStatus();
}

async function showHousekeepingLogs() {
    const modal = new bootstrap.Modal(document.getElementById('housekeepingLogsModal'));
    modal.show();
    
    const container = document.getElementById('hkLogsContainer');
    container.innerHTML = '<div class="text-center py-4 text-gray-500 text-xs"><i class="fas fa-spinner fa-spin mr-2"></i> Loading history...</div>';
    
    try {
        const response = await fetch('/api/duty-roster/housekeeping-logs');
        const data = await response.json();
        
        if (data.success) {
            if (data.logs.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-gray-500 text-xs">No recent status changes found.</div>';
                return;
            }
            
            let html = '<div class="space-y-2">';
            
            data.logs.forEach(log => {
                const oldStyle = getRoomStatusStyle(log.old_status || 'unknown');
                const newStyle = getRoomStatusStyle(log.new_status);
                
                html += `
                    <div class="bg-gray-50 rounded p-2 border border-gray-100">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-bold text-gray-700">${log.room_name}</span>
                            <span class="text-[10px] text-gray-400" title="${log.time}">${log.time_diff}</span>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] mb-1">
                            <span class="${oldStyle.bg} ${oldStyle.text} px-1.5 py-0.5 rounded"><i class="fas ${oldStyle.icon} mr-1"></i>${oldStyle.label}</span>
                            <i class="fas fa-arrow-right text-gray-300"></i>
                            <span class="${newStyle.bg} ${newStyle.text} px-1.5 py-0.5 rounded"><i class="fas ${newStyle.icon} mr-1"></i>${newStyle.label}</span>
                        </div>
                        <div class="text-[10px] text-gray-500">
                            <i class="fas fa-user mr-1"></i> ${log.user_name}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-4 text-red-500 text-xs">Failed to load history</div>';
        }
    } catch (error) {
        console.error('Error loading housekeeping logs:', error);
        container.innerHTML = '<div class="text-center py-4 text-red-500 text-xs"><i class="fas fa-exclamation-triangle"></i> Error loading history</div>';
    }
}

async function showManageRoomsModal() {
    const modal = new bootstrap.Modal(document.getElementById('manageRoomsModal'));
    modal.show();
    await loadRoomsList();
}

async function loadRoomsList() {
    const container = document.getElementById('roomsListContainer');
    container.innerHTML = '<div class="text-center py-4 text-gray-500 text-xs"><i class="fas fa-spinner fa-spin mr-2"></i> Loading rooms...</div>';
    
    try {
        const response = await fetch('/api/duty-roster/rooms');
        const data = await response.json();
        
        if (data.success) {
            if (data.rooms.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-gray-500 text-xs">No rooms found</div>';
                return;
            }
            
            let html = '<div class="divide-y divide-gray-100">';
            
            data.rooms.forEach(room => {
                const statusStyle = getRoomStatusStyle(room.housekeeping_status);
                const isBooked = room.is_booked;
                
                html += `
                    <div class="flex justify-between items-center p-2 hover:bg-gray-50">
                        <div>
                            <h6 class="text-xs font-bold mb-1">${room.name}</h6>
                            <span class="${statusStyle.bg} ${statusStyle.text} text-[10px] px-1.5 py-0.5 rounded">
                                <i class="fas ${statusStyle.icon} mr-1"></i>${statusStyle.label}
                            </span>
                            ${isBooked ? '<span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-1">Booked</span>' : ''}
                        </div>
                        <button 
                            onclick="deleteRoomConfirm(${room.id}, '${room.name}', ${isBooked})" 
                            class="btn btn-sm btn-danger text-xs px-2 py-1"
                            ${isBooked ? 'disabled title="Cannot delete booked room"' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-4 text-red-500 text-xs">Failed to load rooms</div>';
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
        container.innerHTML = '<div class="text-center py-4 text-red-500 text-xs"><i class="fas fa-exclamation-triangle"></i> Error loading rooms</div>';
    }
}

async function addRoom(event) {
    event.preventDefault();
    
    const roomName = document.getElementById('newRoomName').value.trim();
    if (!roomName) return;
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Adding...';
    
    try {
        const response = await fetch('/api/duty-roster/rooms', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name: roomName })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('newRoomName').value = '';
            await loadRoomsList();
            await loadHousekeepingStatus();
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-2 text-xs';
            alert.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            event.target.appendChild(alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error adding room:', error);
        alert('Error adding room. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function deleteRoomConfirm(roomId, roomName, isBooked) {
    if (isBooked) {
        alert('Cannot delete a booked room');
        return;
    }
    
    if (confirm(`Are you sure you want to delete room "${roomName}"?`)) {
        deleteRoom(roomId);
    }
}

async function deleteRoom(roomId) {
    try {
        const response = await fetch(`/api/duty-roster/rooms/${roomId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            await loadRoomsList();
            await loadHousekeepingStatus();
            
            const container = document.getElementById('roomsListContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show m-3 text-xs';
            alert.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            container.insertAdjacentElement('beforebegin', alert);
            setTimeout(() => alert.remove(), 3000);
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error deleting room:', error);
        alert('Error deleting room. Please try again.');
    }
}

// ===== 4. Inventory Warnings =====
async function loadInventoryWarnings() {
    try {
        const response = await fetch('/api/duty-roster/inventory-warnings');
        const data = await response.json();
        if (data.success) {
            document.getElementById('invWarningCount').textContent = data.stats.total_warnings;
            renderInventoryWarnings(data.items, data.stats);
        }
    } catch (error) {
        console.error('Error loading inventory warnings:', error);
    }
}

function renderInventoryWarnings(items, stats) {
    const container = document.getElementById('inventoryBody');
    if (items.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-xs"><i class="fas fa-check-circle text-green-500"></i> <span class="text-green-600 font-medium">All stock levels OK</span></div>';
        return;
    }
    let html = '';
    if (stats.out_of_stock > 0) {
        html += `<div class="text-[10px] font-bold text-red-600 mb-1 flex items-center gap-1"><i class="fas fa-times-circle"></i> OUT OF STOCK (${stats.out_of_stock})</div>`;
    }
    html += items.map(item => {
        const isOut = item.status === 'Out of Stock';
        const bgClass = isOut ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200';
        const iconClass = isOut ? 'text-red-500 fa-times-circle' : 'text-amber-500 fa-exclamation-triangle';
        const stockVal = parseFloat(item.current_stock);
        return `
            <div class="flex items-center gap-2 p-1.5 ${bgClass} border rounded mb-1 cursor-pointer hover:opacity-80" onclick="window.open('/stock','_blank')">
                <i class="fas ${iconClass} text-xs flex-shrink-0"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-gray-800 truncate">${item.name}</p>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="text-[9px] text-gray-400">${item.category}</span>
                        <span class="ml-auto text-[9px] font-bold ${isOut ? 'text-red-600' : 'text-amber-600'}">${stockVal.toFixed(1)} remaining</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    container.innerHTML = html;
}

function refreshInventoryWarnings() {
    loadInventoryWarnings();
}

// ===== 5. Pending CRM Leads =====
async function loadPendingLeads() {
    try {
        const response = await fetch('/api/duty-roster/pending-leads');
        const data = await response.json();
        if (data.success) {
            document.getElementById('crmLeadCount').textContent = data.stats.pending_calls;
            document.getElementById('crmTodayNew').textContent = data.today_new;
            document.getElementById('crmPendingCalls').textContent = data.stats.pending_calls;
            document.getElementById('crmOverdue').textContent = data.stats.overdue;
            document.getElementById('crmConversion').textContent = data.stats.conversion_rate + '%';
            renderPendingLeads(data.leads);
        }
    } catch (error) {
        console.error('Error loading pending leads:', error);
    }
}

function renderPendingLeads(leads) {
    const container = document.getElementById('crmLeadsList');
    if (leads.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-xs"><i class="fas fa-check-circle text-green-500"></i> <span class="text-green-600 font-medium">No pending leads</span></div>';
        return;
    }
    container.innerHTML = leads.map(lead => {
        const overdueClass = lead.is_overdue ? 'border-red-300 bg-red-50' : 'border-blue-100 bg-blue-50';
        const overdueBadge = lead.is_overdue ? '<span class="text-[8px] bg-red-500 text-white px-1 py-0.5 rounded font-bold animate-pulse">OVERDUE</span>' : '';
        return `
            <div class="flex items-center gap-2 p-1.5 ${overdueClass} border rounded mb-1">
                <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0">
                    ${(lead.customer_name || 'L').charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1">
                        <p class="text-[11px] font-semibold text-gray-800 truncate">${lead.customer_name}</p>
                        ${overdueBadge}
                    </div>
                    <div class="flex items-center gap-2 text-[9px] text-gray-500">
                        <span class="badge-${lead.status_color}" style="font-size:9px;padding:1px 4px;border-radius:3px;background:var(--bs-${lead.status_color}, #6c757d);color:white;">${lead.status}</span>
                        ${lead.check_in ? `<span>${lead.check_in} - ${lead.check_out || '?'}</span>` : ''}
                        ${lead.days_since_contact > 0 ? `<span class="text-gray-400">${lead.days_since_contact}d ago</span>` : ''}
                    </div>
                </div>
                ${lead.whatsapp_link ? `<a href="${lead.whatsapp_link}" target="_blank" class="text-green-500 hover:text-green-700 flex-shrink-0" title="WhatsApp"><i class="fab fa-whatsapp text-sm"></i></a>` : ''}
            </div>
        `;
    }).join('');
}

function refreshPendingLeads() {
    loadPendingLeads();
}

// ===== 6. Maintenance Tickets =====
async function loadMaintenanceTickets() {
    try {
        const response = await fetch('/api/duty-roster/maintenance-tickets');
        const data = await response.json();
        if (data.success) {
            const totalCount = data.stats.total_damages + data.stats.pending_tasks;
            document.getElementById('maintenanceCount').textContent = totalCount;
            renderMaintenanceTickets(data.damages, data.tasks);
        }
    } catch (error) {
        console.error('Error loading maintenance tickets:', error);
    }
}

function renderMaintenanceTickets(damages, tasks) {
    const container = document.getElementById('maintenanceBody');
    if (damages.length === 0 && tasks.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-xs"><i class="fas fa-check-circle text-green-500"></i> <span class="text-green-600 font-medium">No pending issues</span></div>';
        return;
    }
    let html = '';

    // Pending maintenance tasks
    if (tasks.length > 0) {
        html += '<div class="text-[10px] font-bold text-rose-700 mb-1 flex items-center gap-1"><i class="fas fa-wrench"></i> PENDING TASKS</div>';
        html += tasks.map(task => {
            const priorityColors = { 'High': 'bg-red-100 text-red-700 border-red-200', 'Medium': 'bg-amber-100 text-amber-700 border-amber-200', 'Low': 'bg-blue-100 text-blue-700 border-blue-200' };
            const pClass = priorityColors[task.priority] || priorityColors['Medium'];
            const overdueIcon = task.is_overdue ? '<i class="fas fa-exclamation-triangle text-red-500 text-[9px] animate-pulse"></i>' : '';
            return `
                <div class="flex items-start gap-2 p-1.5 bg-gray-50 border border-gray-200 rounded mb-1">
                    <span class="text-[8px] px-1 py-0.5 rounded border font-bold mt-0.5 ${pClass}">${task.priority || 'Med'}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-gray-800 leading-tight">${task.task}</p>
                        <div class="flex items-center gap-2 text-[9px] text-gray-400 mt-0.5">
                            <span><i class="fas fa-user"></i> ${task.assigned_to}</span>
                            ${overdueIcon}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Recent damage reports
    if (damages.length > 0) {
        html += '<div class="text-[10px] font-bold text-rose-700 mb-1 mt-2 flex items-center gap-1"><i class="fas fa-hammer"></i> RECENT DAMAGE REPORTS</div>';
        html += damages.map(d => `
            <div class="flex items-start gap-2 p-1.5 bg-rose-50 border border-rose-200 rounded mb-1">
                <i class="fas fa-exclamation-circle text-rose-500 text-xs mt-0.5 flex-shrink-0"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-gray-800">${d.item_name}</p>
                    <div class="flex items-center gap-2 text-[9px] text-gray-500">
                        <span>${d.type || 'Damage'}</span>
                        <span>Qty: ${d.quantity}</span>
                        ${d.total_cost > 0 ? `<span class="text-red-600 font-medium">Rs ${parseFloat(d.total_cost).toLocaleString()}</span>` : ''}
                        <span class="text-gray-400">${d.days_ago === 0 ? 'Today' : d.days_ago + 'd ago'}</span>
                    </div>
                    ${d.notes ? `<p class="text-[9px] text-gray-400 mt-0.5 truncate">${d.notes}</p>` : ''}
                </div>
            </div>
        `).join('');
    }

    container.innerHTML = html;
}

function refreshMaintenanceTickets() {
    loadMaintenanceTickets();
}

// ==================== COLLAPSIBLE WIDGET TOGGLE ====================
function toggleWidgetBody(bodyId) {
    const body = document.getElementById(bodyId);
    const icon = document.getElementById(bodyId + 'Icon');
    if (!body) return;
    if (body.style.display === 'none') {
        body.style.display = '';
        if (icon) icon.style.transform = 'rotate(0deg)';
    } else {
        body.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(-90deg)';
    }
}

// ==================== PENDING CUSTOMER FEEDBACK ====================
async function loadPendingFeedback() {
    try {
        const response = await fetch('/api/duty-roster/pending-feedback');
        const data = await response.json();
        if (data.success) {
            document.getElementById('feedbackCount').textContent = data.stats.pending;
            document.getElementById('fbPending').textContent = data.stats.pending;
            document.getElementById('fbCompletedToday').textContent = data.stats.completed_today;
            renderPendingFeedback(data.feedbacks);
        }
    } catch (error) {
        console.error('Error loading pending feedback:', error);
    }
}

function renderPendingFeedback(feedbacks) {
    const container = document.getElementById('feedbackList');
    if (feedbacks.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-xs"><i class="fas fa-check-circle text-green-500"></i> <span class="text-green-600 font-medium">All feedback collected!</span></div>';
        return;
    }
    container.innerHTML = feedbacks.map(fb => {
        const urgency = fb.days_ago > 3 ? 'border-red-300 bg-red-50' : fb.days_ago > 1 ? 'border-amber-200 bg-amber-50' : 'border-pink-100 bg-pink-50';
        const urgencyBadge = fb.days_ago > 3 ? '<span class="text-[8px] bg-red-500 text-white px-1 py-0.5 rounded font-bold">URGENT</span>' : '';
        return `
            <div class="flex items-center gap-2 p-1.5 ${urgency} border rounded mb-1">
                <div class="w-6 h-6 bg-pink-500 rounded-full flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0">
                    ${(fb.customer_name || 'C').charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1">
                        <p class="text-[11px] font-semibold text-gray-800 truncate">${fb.customer_name}</p>
                        ${urgencyBadge}
                    </div>
                    <div class="flex items-center gap-2 text-[9px] text-gray-500">
                        ${fb.function_type ? `<span>${fb.function_type}</span>` : ''}
                        ${fb.function_date ? `<span>${fb.function_date}</span>` : ''}
                        ${fb.days_ago !== null ? `<span class="text-gray-400">${fb.days_ago === 0 ? 'Today' : fb.days_ago + 'd ago'}</span>` : ''}
                    </div>
                </div>
                ${fb.whatsapp_link ? `<a href="${fb.whatsapp_link}" target="_blank" onclick="event.stopPropagation()" class="text-green-500 hover:text-green-700 flex-shrink-0" title="WhatsApp"><i class="fab fa-whatsapp text-sm"></i></a>` : ''}
            </div>
        `;
    }).join('');
}

function refreshPendingFeedback() {
    loadPendingFeedback();
}

// ==================== TODAY'S TASKS ====================
async function loadTodayTasks() {
    try {
        const date = document.getElementById('allocationDate')?.value || new Date().toISOString().split('T')[0];
        const response = await fetch(`/api/duty-roster/today-tasks?date=${date}`);
        const data = await response.json();
        if (data.success) {
            document.getElementById('tasksDueCount').textContent = data.stats.due_today;
            document.getElementById('tasksOverdueCount').textContent = data.stats.overdue;
            document.getElementById('taskStatDue').textContent = data.stats.due_today;
            document.getElementById('taskStatOverdue').textContent = data.stats.overdue;
            document.getElementById('taskStatCompleted').textContent = data.stats.completed_today;
            document.getElementById('taskStatPending').textContent = data.stats.total_pending;
            renderTodayTasks(data.due_today, data.overdue);
        }
    } catch (error) {
        console.error('Error loading today tasks:', error);
    }
}

function renderTodayTasks(dueToday, overdue) {
    const container = document.getElementById('todayTasksList');
    if (dueToday.length === 0 && overdue.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-xs"><i class="fas fa-check-circle text-green-500"></i> <span class="text-green-600 font-medium">All tasks completed!</span></div>';
        return;
    }
    let html = '';

    if (overdue.length > 0) {
        html += '<div class="text-[10px] font-bold text-red-600 mb-1 flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> OVERDUE</div>';
        html += overdue.map(t => renderTaskCard(t, true)).join('');
    }

    if (dueToday.length > 0) {
        html += '<div class="text-[10px] font-bold text-violet-700 mb-1 mt-1.5 flex items-center gap-1"><i class="fas fa-calendar-day"></i> DUE TODAY</div>';
        html += dueToday.map(t => renderTaskCard(t, false)).join('');
    }

    container.innerHTML = html;
}

function renderTaskCard(task, isOverdue) {
    const priorityColors = { 'High': 'bg-red-100 text-red-700 border-red-200', 'Medium': 'bg-amber-100 text-amber-700 border-amber-200', 'Low': 'bg-blue-100 text-blue-700 border-blue-200' };
    const pClass = priorityColors[task.priority] || priorityColors['Medium'];
    const borderClass = isOverdue ? 'border-red-200 bg-red-50' : 'border-violet-100 bg-violet-50';
    const daysText = task.days_overdue ? `<span class="text-red-500 text-[9px] font-bold">${task.days_overdue}d overdue</span>` : '';
    return `
        <div class="flex items-start gap-2 p-1.5 ${borderClass} border rounded mb-1">
            <span class="text-[8px] px-1 py-0.5 rounded border font-bold mt-0.5 ${pClass} flex-shrink-0">${task.priority || 'Med'}</span>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] text-gray-800 leading-tight">${task.task}</p>
                <div class="flex items-center gap-2 text-[9px] text-gray-400 mt-0.5">
                    <span><i class="fas fa-user"></i> ${task.assigned_to}</span>
                    ${task.category ? `<span class="text-gray-300">|</span><span>${task.category}</span>` : ''}
                    ${daysText}
                </div>
            </div>
        </div>
    `;
}

// ==================== ONLINE USERS (ADMIN ONLY) ====================
async function loadOnlineUsers() {
    try {
        const response = await fetch('/api/duty-roster/online-users');
        const data = await response.json();
        if (data.success) {
            document.getElementById('usOnline').textContent = data.stats.online;
            document.getElementById('usIdle').textContent = data.stats.idle;
            document.getElementById('usOffline').textContent = data.stats.offline;
            document.getElementById('onlineUserCount').textContent = data.stats.online + ' online';
            renderOnlineUsers(data.users);
        }
    } catch (error) {
        console.error('Error loading online users:', error);
    }
}

function renderOnlineUsers(users) {
    const container = document.getElementById('onlineUsersList');
    if (users.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-xs text-gray-400">No users found</div>';
        return;
    }
    container.innerHTML = users.map(user => {
        let statusDot, statusBg, statusText;
        if (user.is_online) {
            statusDot = 'bg-green-500'; statusBg = 'bg-green-50 border-green-200'; statusText = 'Online';
        } else if (user.is_idle) {
            statusDot = 'bg-yellow-500'; statusBg = 'bg-yellow-50 border-yellow-200'; statusText = 'Idle';
        } else {
            statusDot = 'bg-gray-300'; statusBg = 'bg-gray-50 border-gray-200'; statusText = 'Offline';
        }
        const roleBadge = user.role === 'admin'
            ? '<span class="text-[8px] bg-red-100 text-red-600 px-1 py-0.5 rounded font-bold">ADMIN</span>'
            : '<span class="text-[8px] bg-blue-100 text-blue-600 px-1 py-0.5 rounded font-bold">' + (user.role || 'user').toUpperCase() + '</span>';
        const pageName = user.last_page ? '/' + user.last_page : '';
        const pageLabel = pageName.length > 25 ? pageName.substring(0, 25) + '...' : pageName;

        return `
            <div class="flex items-center gap-2 p-1.5 ${statusBg} border rounded mb-1">
                <div class="relative flex-shrink-0">
                    <div class="w-7 h-7 bg-slate-600 rounded-full flex items-center justify-center text-white text-[10px] font-bold">
                        ${user.name.charAt(0).toUpperCase()}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 ${statusDot} rounded-full border-2 border-white"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1">
                        <p class="text-[11px] font-semibold text-gray-800 truncate">${user.name}</p>
                        ${roleBadge}
                    </div>
                    <div class="flex items-center gap-1.5 text-[9px] text-gray-400">
                        <span>${statusText} · ${user.last_seen_human}</span>
                    </div>
                    ${user.is_online && pageLabel ? `<div class="text-[8px] text-gray-400 truncate mt-0.5"><i class="fas fa-eye text-[7px]"></i> ${pageLabel}</div>` : ''}
                </div>
                ${user.last_ip ? `<span class="text-[8px] text-gray-300 flex-shrink-0" title="IP: ${user.last_ip}"><i class="fas fa-wifi"></i></span>` : ''}
            </div>
        `;
    }).join('');
}
</script>
@endpush
