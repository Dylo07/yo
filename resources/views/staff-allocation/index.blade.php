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
                    <div class="px-3 pb-3 space-y-2 category-content" id="content-{{ $category }}">
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

    <!-- Map Area -->
    <div class="flex-1 p-6 overflow-auto" style="background-color: #f8fafc;">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Hotel Floor Plan</h1>
                    <p class="text-sm text-gray-500">Drag and drop staff members to assign them to locations</p>
                </div>
                <!-- Date Selection -->
                <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-sm border">
                    <i class="fas fa-calendar-alt text-blue-500"></i>
                    <button onclick="goToYesterday()" class="text-xs text-gray-600 hover:text-gray-800 font-medium px-2 py-1 rounded hover:bg-gray-100">← Yesterday</button>
                    <input type="date" id="allocationDate" 
                        class="border-0 focus:outline-none focus:ring-0 text-gray-700 font-medium"
                        value="{{ date('Y-m-d') }}"
                        onchange="handleDateChange(this.value)">
                    <button onclick="goToToday()" class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50">Today</button>
                    <button onclick="goToTomorrow()" class="text-xs text-green-600 hover:text-green-800 font-medium px-2 py-1 rounded hover:bg-green-50">Tomorrow</button>
                    <button onclick="goToNextDay()" class="text-xs text-purple-600 hover:text-purple-800 font-medium px-2 py-1 rounded hover:bg-purple-50">Next →</button>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-3 text-xs bg-white px-3 py-2 rounded-lg shadow-sm border">
                    <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-slate-600"></div><span>Rooms</span></div>
                    <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-blue-500"></div><span>Kitchen</span></div>
                    <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-orange-500"></div><span>Dining</span></div>
                    <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-purple-500"></div><span>Hall</span></div>
                    <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-green-500"></div><span>Office</span></div>
                    <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-cyan-400"></div><span>Pool</span></div>
                    <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-lime-500"></div><span>Garden</span></div>
                </div>
                <button onclick="clearAllAssignments()" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg shadow-sm transition-colors flex items-center gap-1 no-print">
                    <i class="fas fa-trash-alt"></i> Clear All
                </button>
                <button onclick="printRoster()" class="px-3 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm rounded-lg shadow-sm transition-colors flex items-center gap-1 no-print">
                    <i class="fas fa-print"></i> Print Roster
                </button>
            </div>
        </div>

        <!-- Map Container -->
        <div class="map-container bg-white rounded-xl shadow-lg border border-gray-200 p-6" id="mapContainer">
            <!-- Sections will be rendered here -->
        </div>
    </div>

    <!-- Bookings Sidebar (Right) -->
    <div class="w-80 bg-white border-l border-gray-200 flex flex-col shadow-lg">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200" style="background: linear-gradient(to right, #059669, #047857);">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-calendar-check"></i> Today's Bookings
            </h2>
            <p class="text-green-100 text-sm mt-1">Rooms with active bookings</p>
        </div>

        <!-- Bookings List -->
        <div class="flex-1 overflow-y-auto sidebar-scroll p-3" id="bookingsList">
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p class="text-sm">Loading bookings...</p>
            </div>
        </div>

        <!-- Footer Stats -->
        <div class="p-4 border-t border-gray-200 bg-gray-50">
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
            <button onclick="closeBookingModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Section data based on hotel layout (room names match /calendar)
const sections = [
    // Top row rooms
    { id: 'ahala', name: 'Ahala', type: 'ROOM', top: 3, left: 17, width: 4, height: 6 },
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
};

// State
let assignments = {}; // { staffId: sectionId }
let draggedStaffId = null;

// State for staff on leave
let staffOnLeave = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    renderSections();
    setupDragAndDrop();
    setupSearch();
    loadBookings(currentDate); // Load bookings for today
    loadAllocations(currentDate); // Load saved allocations for today
    loadStaffOnLeave(currentDate); // Load staff on leave for today
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
    
    // Section drop events
    document.querySelectorAll('.section-box').forEach(section => {
        section.addEventListener('dragover', handleDragOver);
        section.addEventListener('dragleave', handleDragLeave);
        section.addEventListener('drop', handleDrop);
    });
}

function handleDragStart(e) {
    draggedStaffId = e.target.dataset.staffId;
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
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

        renderBookings(filteredBookings);
        highlightBookedRooms(filteredBookings);
        
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

    let totalRooms = 0;
    let html = '<div class="space-y-3">';
    
    bookings.forEach((booking, index) => {
        const functionColor = functionTypeColors[booking.function_type] || functionTypeColors['Other'];
        const outlineColor = bookingOutlineColors[index % bookingOutlineColors.length];
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
        
        html += `
            <div class="booking-card bg-white rounded-lg border-2 shadow-sm overflow-hidden hover:shadow-md transition-shadow" style="border-color: ${outlineColor};">
                <div class="px-3 py-2" style="background-color: ${outlineColor}15;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: ${outlineColor};"></div>
                            <span class="font-semibold text-gray-800 text-sm">${booking.name || 'Guest'}</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: ${functionColor};">
                            ${booking.function_type || 'Event'}
                        </span>
                    </div>
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
                    <button onclick='showBookingDetails(${JSON.stringify(booking).replace(/'/g, "&#39;")}, "${outlineColor}")' 
                        class="w-full text-xs py-1.5 rounded-md font-medium text-white transition-colors hover:opacity-90"
                        style="background-color: ${outlineColor};">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    bookingsList.innerHTML = html;
    
    document.getElementById('totalBookings').textContent = bookings.length;
    document.getElementById('totalRoomsBooked').textContent = totalRooms;
}

function highlightBookedRooms(bookings) {
    // Reset all section highlights
    document.querySelectorAll('.section-box').forEach(section => {
        section.classList.remove('booked-room');
        section.style.boxShadow = '';
    });
    
    // Create a map of room -> unique booking color (each booking gets a unique color)
    const roomColorMap = new Map();
    
    bookings.forEach((booking, index) => {
        // Each booking gets a unique color from the array
        const color = bookingOutlineColors[index % bookingOutlineColors.length];
        let rooms = [];
        try {
            rooms = JSON.parse(booking.room_numbers);
            if (!Array.isArray(rooms)) rooms = [rooms];
        } catch (e) {
            rooms = booking.room_numbers ? [booking.room_numbers] : [];
        }
        rooms.forEach(room => {
            roomColorMap.set(room.trim(), color);
        });
    });
    
    // Highlight booked rooms on the map with their unique booking color
    sections.forEach(section => {
        if (roomColorMap.has(section.name)) {
            const color = roomColorMap.get(section.name);
            const sectionEl = document.querySelector(`[data-section-id="${section.id}"]`);
            if (sectionEl) {
                sectionEl.style.boxShadow = `0 0 0 4px ${color}, 0 0 15px ${color}90`;
            }
        }
    });
}

function handleDateChange(date) {
    currentDate = date;
    // Clear current UI assignments (not database)
    clearAllAssignmentsQuiet();
    
    // Load bookings, allocations, and staff on leave for the new date
    loadBookings(date);
    loadAllocations(date);
    loadStaffOnLeave(date);
    
    // Format and display the selected date
    const dateObj = new Date(date);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = dateObj.toLocaleDateString('en-US', options);
    
    // Show notification
    showNotification(`Viewing allocations for: ${formattedDate}`);
}

function goToToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('allocationDate').value = today;
    handleDateChange(today);
}

function goToYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const dateStr = yesterday.toISOString().split('T')[0];
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
function showBookingDetails(booking, color) {
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

function printRoster() {
    const date = document.getElementById('allocationDate').value;
    const dateObj = new Date(date);
    const formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    // Build print content
    let printContent = `
        <html>
        <head>
            <title>Duty Roster - ${formattedDate}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1 { text-align: center; color: #1e40af; margin-bottom: 5px; }
                h2 { text-align: center; color: #6b7280; font-weight: normal; margin-top: 0; }
                .section { margin-bottom: 20px; page-break-inside: avoid; }
                .section-title { background: #1e40af; color: white; padding: 8px 15px; border-radius: 5px; margin-bottom: 10px; }
                .staff-list { padding-left: 20px; }
                .staff-item { padding: 5px 0; border-bottom: 1px solid #e5e7eb; }
                .no-staff { color: #9ca3af; font-style: italic; }
                .leave-section { background: #fef2f2; border: 1px solid #fca5a5; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .leave-title { color: #dc2626; font-weight: bold; margin-bottom: 10px; }
                .stats { display: flex; justify-content: space-around; margin-top: 30px; padding: 15px; background: #f3f4f6; border-radius: 5px; }
                .stat-item { text-align: center; }
                .stat-value { font-size: 24px; font-weight: bold; color: #1e40af; }
                .stat-label { color: #6b7280; font-size: 12px; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <h1>🏨 Hotel Duty Roster</h1>
            <h2>${formattedDate}</h2>
    `;
    
    // Add staff on leave section
    if (staffOnLeave.length > 0) {
        printContent += `
            <div class="leave-section">
                <div class="leave-title">⚠️ Staff On Leave (${staffOnLeave.length})</div>
                <div class="staff-list">
                    ${staffOnLeave.map(s => `<div class="staff-item">${s.person_name} - ${s.leave_type}</div>`).join('')}
                </div>
            </div>
        `;
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
            sectionAssignments[sectionId].staff.push(card.dataset.staffName);
        }
    });
    
    // Add sections to print content
    Object.values(sectionAssignments).forEach(section => {
        if (section.staff.length > 0) {
            printContent += `
                <div class="section">
                    <div class="section-title">${section.name} (${section.type})</div>
                    <div class="staff-list">
                        ${section.staff.map(name => `<div class="staff-item">• ${name}</div>`).join('')}
                    </div>
                </div>
            `;
        }
    });
    
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
</script>
@endpush
