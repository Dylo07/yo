<style>
/* Card and Header Styles */
.card-header {
    background: linear-gradient(45deg, #1a237e, #283593);
}

/* Badge Styles */
.badge-base {
    color: white;
    border-radius: 15px;
    font-size: 0.9rem;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.checkin-badge {
    composes: badge-base;
    background: linear-gradient(45deg, #6c757d, #495057);
    padding: 6px 12px;
}

.checkout-badge {
    composes: badge-base;
    background: linear-gradient(45deg, #20c997, #28a745);
    padding: 8px 12px;
    min-width: 200px;
}

.pool-badge {
    composes: badge-base;
    background: linear-gradient(45deg, #0dcaf0, #0d6efd);
    padding: 6px 12px;
}

.room-badge {
    composes: badge-base;
    background: linear-gradient(45deg, #17a2b8, #138496);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85rem;
    margin: 2px;
}

.checkout-status-badge {
    composes: badge-base;
    background: linear-gradient(45deg, #28a745, #20c997);
    padding: 8px 12px;
    border-radius: 20px;
}

.temp-badge {
    composes: badge-base;
    background: linear-gradient(45deg, #17a2b8, #0dcaf0);
    padding: 6px 12px;
    min-width: 200px;
}

/* Team Styles */
.team-badge {
    padding: 6px 12px;
    border-radius: 15px;
    font-weight: 500;
    font-size: 0.9rem;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Team Colors */
.team-Team1 { background: linear-gradient(to bottom, #e6e6e6, #deb5f7) !important; border: 1px solid #b3b3b3; }
.team-Team2 { background: linear-gradient(to bottom, #b3ffb3, #80ff80) !important; border: 1px solid #4dff4d; }
.team-Team3 { background: linear-gradient(to bottom, #b3d9ff, #80bfff) !important; border: 1px solid #4da6ff; }
.team-Team4 { background: linear-gradient(to bottom, #ffd9b3, #ffbf80) !important; border: 1px solid #ffa64d; }
.team-Team5 { background: linear-gradient(to bottom, #ffb3ff, #ff80ff) !important; border: 1px solid #ff4dff; }
.team-Team6 { background: linear-gradient(to bottom, #b3fff9, #80fff5) !important; border: 1px solid #4dfff2; }
.team-Team7 { background: linear-gradient(to bottom, #ffcccc, #ff9999) !important; border: 1px solid #ff6666; }
.team-Team8 { background: linear-gradient(to bottom, #d9ffcc, #b3ff99) !important; border: 1px solid #8cff66; }
.team-Team9 { background: linear-gradient(to bottom, #ccd9ff, #99b3ff) !important; border: 1px solid #668cff; }
.team-Team10 { background: linear-gradient(to bottom, #fff2cc, #ffe699) !important; border: 1px solid #ffd966; }

/* Temp Status Styles */
.temp-status-base {
    display: block;
    font-size: 0.8rem;
    margin-top: 2px;
}

.temp-history {
    composes: temp-status-base;
    opacity: 0.8;
    margin-top: 4px;
    color: #666;
}

.temp-in-time {
    composes: temp-status-base;
    opacity: 0.9;
}

/* Button Styles */
.btn-base {
    border: none;
    color: white;
    transition: all 0.3s ease;
}

.main-checkout-btn {
    composes: btn-base;
    background: linear-gradient(45deg, #dc3545, #c82333);
    padding: 6px 12px;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.main-checkout-btn:hover {
    background: linear-gradient(45deg, #c82333, #bd2130);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(220, 53, 69, 0.4);
}

.main-checkout-btn:focus {
    color: white;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.temp-button {
    composes: btn-base;
    background: linear-gradient(45deg, #17a2b8, #0dcaf0);
}

.temp-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Form Elements */
.room-checkboxes {
    max-height: 150px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    background: #fff;
}

.custom-checkbox {
    margin: 5px 10px;
}

.custom-switch {
    padding-left: 2.25rem;
}

.form-control:focus {
    border-color: #4d5aad;
    box-shadow: 0 0 0 0.2rem rgba(29, 35, 126, 0.25);
}

/* Table Styles */
.table td { 
    vertical-align: middle !important;
}

.table tbody tr:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Animations */
@keyframes highlight {
    0% { background-color: rgba(255, 255, 0, 0.5); }
    100% { background-color: transparent; }
}

.highlight {
    animation: highlight 1s ease-out;
}

/* Additional Utility Classes */
.mr-1 { margin-right: 0.25rem !important; }
.ml-1 { margin-left: 0.25rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
</style>