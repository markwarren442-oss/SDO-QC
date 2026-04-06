@extends('layouts.app')

@section('title', 'SDO QC | Reports')

@section('styles')
<style>
    /* ── Animations ── */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade {
        animation: fadeInUp 0.5s ease backwards;
    }

    /* ── Header ── */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding: 0 0 20px 0;
    }
    .page-header h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .page-header p {
        color: #64748b;
        font-size: 0.85rem;
        margin: 4px 0 0 0;
        font-weight: 500;
    }

    /* ── Cards ── */
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-top: 10px;
    }

    .report-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.06);
    }

    .report-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 20px;
    }

    .icon-blue { background: #eff6ff; color: #3b82f6; }
    .icon-green { background: #f0fdf4; color: #10b981; }
    .icon-orange { background: #fff7ed; color: #f97316; }

    .report-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 8px 0;
    }

    .report-desc {
        font-size: 0.82rem;
        color: #64748b;
        line-height: 1.5;
        flex: 1;
        margin-bottom: 24px;
    }

    .report-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
        width: 100%;
        text-align: center;
    }

    .report-btn:hover {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    /* ── Spotlight Cards Premium ── */
    .spotlight-card {
        border: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .spotlight-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .spotlight-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 4px;
        opacity: 0.8;
    }
    .spotlight-present::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .spotlight-absent::before { background: linear-gradient(90deg, #ef4444, #f87171); }
    .spotlight-late::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .spotlight-undertime::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

    .spotlight-icon-wrap {
        width: 54px; height: 54px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }
    .spotlight-icon-wrap::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        filter: blur(12px);
        opacity: 0.4;
        z-index: -1;
    }
    .spotlight-present .spotlight-icon-wrap { background: #ecfdf5; color: #059669; }
    .spotlight-present .spotlight-icon-wrap::after { background: #10b981; }
    .spotlight-absent .spotlight-icon-wrap { background: #fef2f2; color: #dc2626; }
    .spotlight-absent .spotlight-icon-wrap::after { background: #ef4444; }
    .spotlight-late .spotlight-icon-wrap { background: #fffbeb; color: #d97706; }
    .spotlight-late .spotlight-icon-wrap::after { background: #f59e0b; }
    .spotlight-undertime .spotlight-icon-wrap { background: #f5f3ff; color: #7c3aed; }
    .spotlight-undertime .spotlight-icon-wrap::after { background: #8b5cf6; }

    /* ── Decorative Layout ── */
    .dashboard-layout {
        padding: 28px 36px;
    }
</style>
@endsection

@section('content')
<div class="dashboard-layout">
    <div class="page-header animate-fade" style="animation-delay: 0s;">
        <div>
            <h1>System Reports</h1>
            <p>Generate, download, and analyze administrative insights</p>
        </div>
    </div>

    <div style="margin-bottom: 25px;">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 5px;">General Summaries</h2>
        <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 15px;">Full overview reports containing all attendance metrics.</p>
    </div>

    <div class="reports-grid">
        <!-- Report 1: Attendance Log -->
        <div class="report-card animate-fade" style="animation-delay: 0.1s;">
            <div class="report-icon icon-blue">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3 class="report-title">Monthly Attendance Summary</h3>
            <p class="report-desc">Comprehensive spreadsheet containing detailed daily attendance counts, total tardiness, absences, and log metrics for all registered employees.</p>
            <div class="d-flex gap-2">
                <button onclick="openReportConfig('monthly', 'ALL', 'preview')" class="report-btn" style="flex: 1;">
                    <i class="fas fa-eye text-info"></i> Preview
                </button>
                <div class="dropdown" style="flex: 1;">
                    <button class="report-btn dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu w-100 border-0 shadow-sm" style="border-radius:12px; padding:8px;">
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('monthly', 'ALL', 'excel')" style="padding:8px 16px;"><i class="fas fa-file-excel text-success" style="width:20px;"></i> Excel (.xlsx)</a></li>
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('monthly', 'ALL', 'pdf')" style="padding:8px 16px;"><i class="fas fa-file-pdf text-danger" style="width:20px;"></i> PDF Document</a></li>
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('monthly', 'ALL', 'docx')" style="padding:8px 16px;"><i class="fas fa-file-word text-primary" style="width:20px;"></i> Word (.doc)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Report 2: Weekly Attendance Log -->
        <div class="report-card animate-fade" style="animation-delay: 0.2s;">
            <div class="report-icon icon-orange">
                <i class="fas fa-calendar-week"></i>
            </div>
            <h3 class="report-title">Weekly Attendance Summary</h3>
            <p class="report-desc">Generates a quick, lightweight CSV spreadsheet showing present and absent day tallies across the current week.</p>
            <div class="d-flex gap-2">
                <button onclick="openReportConfig('weekly', 'ALL', 'preview')" class="report-btn" style="flex: 1;">
                    <i class="fas fa-eye text-info"></i> Preview
                </button>
                <div class="dropdown" style="flex: 1;">
                    <button class="report-btn dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu w-100 border-0 shadow-sm" style="border-radius:12px; padding:8px;">
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('weekly', 'ALL', 'excel')" style="padding:8px 16px;"><i class="fas fa-file-excel text-success" style="width:20px;"></i> Excel (.xlsx)</a></li>
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('weekly', 'ALL', 'pdf')" style="padding:8px 16px;"><i class="fas fa-file-pdf text-danger" style="width:20px;"></i> PDF Document</a></li>
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('weekly', 'ALL', 'docx')" style="padding:8px 16px;"><i class="fas fa-file-word text-primary" style="width:20px;"></i> Word (.doc)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Report 3: Yearly Attendance Log -->
        <div class="report-card animate-fade" style="animation-delay: 0.3s;">
            <div class="report-icon icon-green">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3 class="report-title">Yearly Attendance Summary</h3>
            <p class="report-desc">Comprehensive annual CSV listing total presence, lateness, undertimes, and absences for the entire year per employee.</p>
            <div class="d-flex gap-2">
                <button onclick="openReportConfig('yearly', 'ALL', 'preview')" class="report-btn" style="flex: 1;">
                    <i class="fas fa-eye text-info"></i> Preview
                </button>
                <div class="dropdown" style="flex: 1;">
                    <button class="report-btn dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu w-100 border-0 shadow-sm" style="border-radius:12px; padding:8px;">
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('yearly', 'ALL', 'excel')" style="padding:8px 16px;"><i class="fas fa-file-excel text-success" style="width:20px;"></i> Excel (.xlsx)</a></li>
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('yearly', 'ALL', 'pdf')" style="padding:8px 16px;"><i class="fas fa-file-pdf text-danger" style="width:20px;"></i> PDF Document</a></li>
                        <li><a class="dropdown-item rounded" href="javascript:void(0)" onclick="openReportConfig('yearly', 'ALL', 'docx')" style="padding:8px 16px;"><i class="fas fa-file-word text-primary" style="width:20px;"></i> Word (.doc)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Status-Specific Reports Section -->
    <div style="margin: 40px 0 25px;">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 5px;">Status Spotlight Reports</h2>
        <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 15px;">Filtered reports focusing on specific attendance categories.</p>
    </div>

    <div class="reports-grid">
        <!-- Present Report -->
        <div class="report-card spotlight-card spotlight-present animate-fade" style="animation-delay: 0.4s;">
            <div class="spotlight-icon-wrap">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="report-title">Present Employees</h3>
            <p class="report-desc">Lists only employees who were present during the selected period. Ideal for verification and basic active counting.</p>
            @include('admin.partials.report_dropdown', ['status' => 'present'])
        </div>

        <!-- Absent Report -->
        <div class="report-card spotlight-card spotlight-absent animate-fade" style="animation-delay: 0.5s;">
            <div class="spotlight-icon-wrap">
                <i class="fas fa-times-circle"></i>
            </div>
            <h3 class="report-title">Absence Records</h3>
            <p class="report-desc">Detailed logs of employees who missed work, including counts and specific absence dates where applicable.</p>
            @include('admin.partials.report_dropdown', ['status' => 'absent'])
        </div>

        <!-- Late Report -->
        <div class="report-card spotlight-card spotlight-late animate-fade" style="animation-delay: 0.6s;">
            <div class="spotlight-icon-wrap">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="report-title">Tardiness Reports</h3>
            <p class="report-desc">Focuses on employees with late arrivals. Tracks total lates and minutes delayed for performance reviews.</p>
            @include('admin.partials.report_dropdown', ['status' => 'late'])
        </div>

        <!-- Undertime Report -->
        <div class="report-card spotlight-card spotlight-undertime animate-fade" style="animation-delay: 0.7s;">
            <div class="spotlight-icon-wrap">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <h3 class="report-title">Undertime Logs</h3>
            <p class="report-desc">Monitors employees who left before their shift ended. Summarizes undertime instances for compensation tracking.</p>
            @include('admin.partials.report_dropdown', ['status' => 'undertime'])
        </div>
    </div>
</div>

<!-- Signatory Configuration Modal -->
<div class="modal fade" id="reportConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 90%; width: 1100px;">
        <div class="modal-content" style="border-radius: 35px; border: none; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.35); overflow: hidden;">
            <div class="modal-header border-0 pb-0" style="padding: 24px 35px 10px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; background: #eeefff; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #4f46e5; font-size: 1.4rem;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5 class="modal-title" style="font-family: 'Outfit', sans-serif; font-weight: 850; color: #0f172a; font-size: 1.6rem;">
                        Select Date Span
                    </h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="background-color: #f1f5f9; border-radius: 50%; padding: 10px; opacity: 1;"></button>
            </div>
            <div class="modal-body" style="padding: 20px 35px 0;">
                <form id="signatoryForm">
                    <input type="hidden" id="report_type">
                    <input type="hidden" id="report_status">
                    <input type="hidden" id="report_format">

                    <!-- Date Span Selection (Calendar Popup Style) -->
                    <div id="timeline_section" class="mb-4">
                        <div style="background: #f8fafc; padding: 12px 20px; border-radius: 16px; border: 1.5px solid #edf2f7; margin-bottom: 20px;" id="div_employee_context">
                            <i class="fas fa-filter text-primary me-2"></i>
                            <span style="font-size: 0.85rem; font-weight: 700; color: #475569;">Report Filter: <span id="label_report_context" style="color: #6366f1;">ALL EMPLOYEES</span></span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase mb-2" style="font-size: 0.68rem; font-weight: 850; color: #475569; letter-spacing: 0.8px;">Start Date</label>
                                <div class="position-relative">
                                    <input type="date" id="input_start_date" class="form-control" value="{{ date('Y-m-01') }}" style="border-radius: 16px; padding: 12px 18px; font-weight: 600; border: 2px solid #e2e8f0; color: #1e1b4b; background: white;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase mb-2" style="font-size: 0.68rem; font-weight: 850; color: #475569; letter-spacing: 0.8px;">End Date</label>
                                <div class="position-relative">
                                    <input type="date" id="input_end_date" class="form-control" value="{{ date('Y-m-t') }}" style="border-radius: 16px; padding: 12px 18px; font-weight: 600; border: 2px solid #e2e8f0; color: #1e1b4b; background: white;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 align-items-stretch">
                        <!-- Prepared By -->
                        <div class="col-lg-4">
                            <div style="background: #f8fafc; padding: 18px; border-radius: 18px; border: 1px solid #f1f5f9; height: 100%;">
                                <h6 style="font-weight: 800; color: #475569; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 12px;">Prepared By</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Full Name</label>
                                    <input type="text" id="prep_name" class="form-control form-control-sm" placeholder="CHRISTINE JOY C. MAAPOY" style="border-radius: 10px; font-weight: 600; text-transform: uppercase; font-size: 0.8rem;">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Position Line 1</label>
                                    <input type="text" id="prep_pos" class="form-control form-control-sm" placeholder="Administrative Assistant III" style="border-radius: 10px; font-weight: 500; font-size: 0.8rem;">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Position Line 2</label>
                                    <input type="text" id="prep_pos2" class="form-control form-control-sm" placeholder="E-Form7 In-Charge" style="border-radius: 10px; font-weight: 500; font-size: 0.8rem;">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Position Line 3</label>
                                    <input type="text" id="prep_pos3" class="form-control form-control-sm" placeholder="..." style="border-radius: 10px; font-weight: 500; font-size: 0.8rem;">
                                </div>
                            </div>
                        </div>

                        <!-- Certified Correct By -->
                        <div class="col-lg-4">
                            <div style="background: #f0fdf4; padding: 18px; border-radius: 18px; border: 1.5px solid #dcfce7; height: 100%;">
                                <h6 style="font-weight: 800; color: #166534; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 12px;">Certified Correct By</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Full Name</label>
                                    <input type="text" id="cert_name" class="form-control form-control-sm" placeholder="MICHELLE A. MAL-IN" style="border-radius: 10px; font-weight: 600; text-transform: uppercase; font-size: 0.8rem;">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Position</label>
                                    <input type="text" id="cert_pos" class="form-control form-control-sm" placeholder="HRMO II" style="border-radius: 10px; font-weight: 500; font-size: 0.8rem;">
                                </div>
                            </div>
                        </div>

                        <!-- Verified Correct By -->
                        <div class="col-lg-4">
                            <div style="background: #eff6ff; padding: 18px; border-radius: 18px; border: 1.5px solid #dbeafe; height: 100%;">
                                <h6 style="font-weight: 800; color: #1e40af; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 12px;">Verified Correct By</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Full Name</label>
                                    <input type="text" id="ver_name" class="form-control form-control-sm" placeholder="ROSELYN B. SENCIL" style="border-radius: 10px; font-weight: 600; text-transform: uppercase; font-size: 0.8rem;">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted" style="font-size: 0.65rem; font-weight: 700; margin-bottom: 2px;">Position</label>
                                    <input type="text" id="ver_pos" class="form-control form-control-sm" placeholder="HRMO V" style="border-radius: 10px; font-weight: 500; font-size: 0.8rem;">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0" style="padding: 15px 40px 25px; background: white;">
                <button type="button" class="btn px-4 py-2" data-bs-dismiss="modal" style="border-radius: 14px; font-weight: 750; color: #64748b; background: #f1f5f9; border: none; font-size: 0.9rem;">Cancel</button>
                <button type="button" onclick="executeReportGeneration()" class="btn btn-primary px-5 py-2 shadow-lg" style="border-radius: 14px; font-weight: 750; background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-eye"></i> Preview Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 90%; width: 1100px;">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="modal-header bg-light" style="border-bottom: 1px solid #f1f5f9;">
                <h5 class="modal-title" style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #1e293b;"><i class="fas fa-eye text-info"></i> Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 position-relative" style="height: 75vh; background: #e2e8f0;">
                <div id="previewSpinner" class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 10;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <iframe id="previewFrame" style="width: 100%; height: 100%; border: none;" onload="document.getElementById('previewSpinner').style.display='none';"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let configModal;

    document.addEventListener('DOMContentLoaded', function() {
        configModal = new bootstrap.Modal(document.getElementById('reportConfigModal'));
    });

    function openReportConfig(type, status, format) {
        document.getElementById('report_type').value = type;
        document.getElementById('report_status').value = status;
        document.getElementById('report_format').value = format;

        // Update context label
        const contextMap = { 'all': 'ALL EMPLOYEES', 'present': 'PRESENT EMPLOYEES ONLY', 'absent': 'ABSENCE RECORDS ONLY', 'late': 'LATE RECORDS ONLY', 'undertime': 'UNDERTIME LOGS ONLY' };
        document.getElementById('label_report_context').innerText = contextMap[status.toLowerCase()] || 'ALL EMPLOYEES';

        configModal.show();
    }


    function executeReportGeneration() {
        const type = document.getElementById('report_type').value;
        const status = document.getElementById('report_status').value;
        const format = document.getElementById('report_format').value;

        // Collect signatories
        const signatories = {
            prep_name: document.getElementById('prep_name').value || document.getElementById('prep_name').placeholder,
            prep_pos: document.getElementById('prep_pos').value || document.getElementById('prep_pos').placeholder,
            prep_pos2: document.getElementById('prep_pos2').value || document.getElementById('prep_pos2').placeholder,
            prep_pos3: document.getElementById('prep_pos3').value || document.getElementById('prep_pos3').placeholder,
            cert_name: document.getElementById('cert_name').value || document.getElementById('cert_name').placeholder,
            cert_pos: document.getElementById('cert_pos').value || document.getElementById('cert_pos').placeholder,
            ver_name: document.getElementById('ver_name').value || document.getElementById('ver_name').placeholder,
            ver_pos: document.getElementById('ver_pos').value || document.getElementById('ver_pos').placeholder,
        };

        // Determine route
        let baseUrl = '';
        if (type === 'monthly') baseUrl = "{{ route('admin.export.attendance') }}";
        else if (type === 'weekly') baseUrl = "{{ route('admin.export.weekly') }}";
        else if (type === 'yearly') baseUrl = "{{ route('admin.export.yearly') }}";

        // Collect timeline data
        const startVal = document.getElementById('input_start_date').value;
        const [y, m, d] = startVal.split('-');
        
        const timeline = {};
        if (type === 'monthly') {
            timeline.month = m;
            timeline.year = y;
        } else if (type === 'yearly') {
            timeline.year = y;
        } else if (type === 'weekly') {
            timeline.date = startVal;
        }

        // Build URL
        const params = new URLSearchParams({
            status: status,
            format: format,
            ...timeline,
            ...signatories
        });
        const finalUrl = baseUrl + '?' + params.toString();

        configModal.hide();

        if (format === 'preview') {
            openPreview(finalUrl);
        } else {
            window.location.href = finalUrl;
        }
    }

    function openPreview(url) {
        document.getElementById('previewSpinner').style.display = 'flex';
        var frame = document.getElementById('previewFrame');
        
        // Reset the iframe src to force a proper reload cycle
        frame.src = 'about:blank';
        setTimeout(() => {
            frame.src = url;
        }, 10);
        
        var myModal = new bootstrap.Modal(document.getElementById('previewModal'));
        myModal.show();
    }
</script>
@endsection
