@extends('layouts.app')

@section('title', 'SDO QC | Reports')

@section('styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade { animation: fadeInUp 0.45s ease both; }

    /* ── Layout ── */
    .rp-page {
        padding: 28px 36px;
        max-width: 1400px;
    }

    /* ── Page Header ── */
    .rp-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding-bottom: 24px;
        border-bottom: 1.5px solid #f1f5f9;
        margin-bottom: 30px;
    }
    .rp-header h1 {
        font-size: 1.75rem;
        font-weight: 850;
        color: #0f172a;
        margin: 0 0 4px;
    }
    .rp-header p {
        color: #64748b;
        font-size: 0.84rem;
        margin: 0;
        font-weight: 500;
    }

    /* ── Section Labels ── */
    .rp-section-label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .rp-section-label h2 {
        font-size: 1rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .rp-section-label p {
        font-size: 0.78rem;
        color: #94a3b8;
        margin: 0;
        font-weight: 500;
    }
    .rp-section-pill {
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f1f5f9;
        color: #475569;
    }

    /* ── Grid ── */
    .rp-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 18px;
        margin-bottom: 36px;
    }

    /* ── Card ── */
    .rp-card {
        background: white;
        border-radius: 22px;
        border: 1.5px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.22s, box-shadow 0.22s;
        position: relative;
    }
    .rp-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.08);
    }

    /* Top accent stripe */
    .rp-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 3px 3px 0 0;
    }
    .stripe-blue::before   { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .stripe-orange::before { background: linear-gradient(90deg, #f97316, #fb923c); }
    .stripe-green::before  { background: linear-gradient(90deg, #10b981, #34d399); }
    .stripe-red::before    { background: linear-gradient(90deg, #ef4444, #f87171); }
    .stripe-amber::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .stripe-violet::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

    .rp-card-body {
        padding: 22px 22px 16px;
        flex: 1;
    }

    .rp-icon {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 14px;
    }
    .ic-blue   { background: #eff6ff; color: #3b82f6; }
    .ic-orange { background: #fff7ed; color: #f97316; }
    .ic-green  { background: #f0fdf4; color: #10b981; }
    .ic-red    { background: #fef2f2; color: #ef4444; }
    .ic-amber  { background: #fffbeb; color: #d97706; }
    .ic-violet { background: #f5f3ff; color: #7c3aed; }

    .rp-card-title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 6px;
    }
    .rp-card-desc {
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.55;
        margin: 0;
    }

    /* ── Format Chooser Footer ── */
    .rp-card-footer {
        padding: 14px 22px 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rp-format-label {
        font-size: 0.62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .rp-format-btns {
        display: flex;
        gap: 8px;
    }

    .rp-fmt-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 14px;
        border-radius: 11px;
        border: 1.5px solid transparent;
        font-size: 0.76rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s;
        font-family: inherit;
    }
    .rp-fmt-btn i { font-size: 0.9rem; }

    /* Excel button */
    .rp-fmt-excel {
        background: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }
    .rp-fmt-excel:hover {
        background: #16a34a;
        color: white;
        border-color: #16a34a;
        box-shadow: 0 4px 14px rgba(22,163,74,0.25);
        transform: translateY(-1px);
    }

    /* PDF button */
    .rp-fmt-pdf {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }
    .rp-fmt-pdf:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
        box-shadow: 0 4px 14px rgba(220,38,38,0.25);
        transform: translateY(-1px);
    }

    /* Preview link */
    .rp-preview-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s;
        font-family: inherit;
        width: 100%;
    }
    .rp-preview-btn:hover {
        background: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
    }

    /* ── Config Modal ── */
    .rc-modal-content {
        border-radius: 28px !important;
        border: none !important;
        box-shadow: 0 40px 80px -20px rgba(0,0,0,0.3) !important;
        overflow: hidden;
    }
    .rc-modal-header {
        padding: 24px 32px 16px;
        border-bottom: 1.5px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .rc-modal-body {
        padding: 20px 32px 0;
    }
    .rc-modal-footer {
        padding: 16px 32px 24px;
        background: white;
        border-top: 1.5px solid #f1f5f9;
    }

    /* Format badge shown in modal header */
    .rc-format-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 50px;
        font-size: 0.72rem;
        font-weight: 800;
        margin-top: 6px;
    }
    .badge-excel { background: #dcfce7; color: #166534; }
    .badge-pdf   { background: #fee2e2; color: #991b1b; }
    .badge-preview { background: #e0f2fe; color: #075985; }

    /* Signatory boxes */
    .sig-box {
        padding: 16px;
        border-radius: 16px;
        height: 100%;
    }
    .sig-box-prep { background: #f8fafc; border: 1.5px solid #e2e8f0; }
    .sig-box-cert { background: #f0fdf4; border: 1.5px solid #dcfce7; }
    .sig-box-ver  { background: #eff6ff; border: 1.5px solid #dbeafe; }
    .sig-box h6 {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 12px;
    }
    .sig-box-prep h6 { color: #475569; }
    .sig-box-cert h6 { color: #166534; }
    .sig-box-ver  h6 { color: #1e40af; }

    .sig-input {
        width: 100%;
        padding: 7px 12px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        font-size: 0.8rem;
        font-weight: 600;
        font-family: inherit;
        outline: none;
        transition: border-color 0.18s, box-shadow 0.18s;
        color: #1e293b;
        margin-bottom: 8px;
        background: white;
    }
    .sig-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .sig-input::placeholder { font-weight: 500; color: #94a3b8; }
    .sig-input.uppercase { text-transform: uppercase; }

    .sig-lbl {
        font-size: 0.62rem;
        font-weight: 700;
        color: #94a3b8;
        display: block;
        margin-bottom: 3px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    /* Generate button */
    .rc-gen-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 28px;
        border-radius: 13px;
        border: none;
        font-size: 0.85rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }
    .rc-gen-btn-excel {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        box-shadow: 0 4px 14px rgba(16,185,129,0.3);
    }
    .rc-gen-btn-pdf {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 14px rgba(239,68,68,0.3);
    }
    .rc-gen-btn-preview {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        box-shadow: 0 4px 14px rgba(14,165,233,0.3);
    }
    .rc-gen-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
    }
</style>
@endsection

@section('content')
<div class="rp-page">

    {{-- ── Page Header ── --}}
    <div class="rp-header animate-fade" style="animation-delay:0s;">
        <div>
            <h1><i class="fas fa-chart-bar" style="color:#3b82f6;margin-right:10px;font-size:1.4rem;"></i>System Reports</h1>
            <p>Generate and download attendance reports — choose Excel or PDF for each report type.</p>
        </div>
    </div>

    {{-- ══ GENERAL SUMMARIES ══ --}}
    <div class="rp-section-label animate-fade" style="animation-delay:0.05s;">
        <div>
            <h2>General Summaries</h2>
            <p>Full overview reports for all employees across different time spans.</p>
        </div>
        <span class="rp-section-pill">Monthly · Weekly · Yearly</span>
    </div>

    <div class="rp-grid">

        {{-- Monthly --}}
        <div class="rp-card stripe-blue animate-fade" style="animation-delay:0.1s;">
            <div class="rp-card-body">
                <div class="rp-icon ic-blue"><i class="fas fa-calendar-check"></i></div>
                <h3 class="rp-card-title">Monthly Attendance</h3>
                <p class="rp-card-desc">Detailed daily attendance for all employees in a selected month — includes counts, tardiness, absences, and metrics.</p>
            </div>
            <div class="rp-card-footer">
                <div class="rp-format-label">Choose Format</div>
                <div class="rp-format-btns">
                    <button class="rp-fmt-btn rp-fmt-excel" onclick="openReportConfig('monthly','ALL','excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="rp-fmt-btn rp-fmt-pdf" onclick="openReportConfig('monthly','ALL','pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
                <button class="rp-preview-btn" onclick="openReportConfig('monthly','ALL','preview')">
                    <i class="fas fa-eye"></i> Preview in Browser
                </button>
            </div>
        </div>

        {{-- Weekly --}}
        <div class="rp-card stripe-orange animate-fade" style="animation-delay:0.15s;">
            <div class="rp-card-body">
                <div class="rp-icon ic-orange"><i class="fas fa-calendar-week"></i></div>
                <h3 class="rp-card-title">Weekly Attendance</h3>
                <p class="rp-card-desc">Lightweight weekly summary showing present/absent tallies for all employees across the selected week.</p>
            </div>
            <div class="rp-card-footer">
                <div class="rp-format-label">Choose Format</div>
                <div class="rp-format-btns">
                    <button class="rp-fmt-btn rp-fmt-excel" onclick="openReportConfig('weekly','ALL','excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="rp-fmt-btn rp-fmt-pdf" onclick="openReportConfig('weekly','ALL','pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
                <button class="rp-preview-btn" onclick="openReportConfig('weekly','ALL','preview')">
                    <i class="fas fa-eye"></i> Preview in Browser
                </button>
            </div>
        </div>

        {{-- Yearly --}}
        <div class="rp-card stripe-green animate-fade" style="animation-delay:0.2s;">
            <div class="rp-card-body">
                <div class="rp-icon ic-green"><i class="fas fa-calendar-alt"></i></div>
                <h3 class="rp-card-title">Yearly Attendance</h3>
                <p class="rp-card-desc">Annual summary listing total presence, lates, undertimes, and absences per employee for the full year.</p>
            </div>
            <div class="rp-card-footer">
                <div class="rp-format-label">Choose Format</div>
                <div class="rp-format-btns">
                    <button class="rp-fmt-btn rp-fmt-excel" onclick="openReportConfig('yearly','ALL','excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="rp-fmt-btn rp-fmt-pdf" onclick="openReportConfig('yearly','ALL','pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
                <button class="rp-preview-btn" onclick="openReportConfig('yearly','ALL','preview')">
                    <i class="fas fa-eye"></i> Preview in Browser
                </button>
            </div>
        </div>

    </div>

    {{-- ══ STATUS SPOTLIGHT ══ --}}
    <div class="rp-section-label animate-fade" style="animation-delay:0.25s;">
        <div>
            <h2>Status Spotlight Reports</h2>
            <p>Filtered reports focused on specific attendance categories.</p>
        </div>
        <span class="rp-section-pill">Filtered</span>
    </div>

    <div class="rp-grid">

        {{-- Present --}}
        <div class="rp-card stripe-green animate-fade" style="animation-delay:0.3s;">
            <div class="rp-card-body">
                <div class="rp-icon ic-green"><i class="fas fa-check-circle"></i></div>
                <h3 class="rp-card-title">Present Employees</h3>
                <p class="rp-card-desc">Lists only employees who were present during the selected period — ideal for active verification and counting.</p>
            </div>
            <div class="rp-card-footer">
                <div class="rp-format-label">Choose Format</div>
                <div class="rp-format-btns">
                    <button class="rp-fmt-btn rp-fmt-excel" onclick="openReportConfig('monthly','present','excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="rp-fmt-btn rp-fmt-pdf" onclick="openReportConfig('monthly','present','pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
                <button class="rp-preview-btn" onclick="openReportConfig('monthly','present','preview')">
                    <i class="fas fa-eye"></i> Preview in Browser
                </button>
            </div>
        </div>

        {{-- Absent --}}
        <div class="rp-card stripe-red animate-fade" style="animation-delay:0.35s;">
            <div class="rp-card-body">
                <div class="rp-icon ic-red"><i class="fas fa-times-circle"></i></div>
                <h3 class="rp-card-title">Absence Records</h3>
                <p class="rp-card-desc">Detailed logs of employees who missed work — includes absence counts and specific dates where applicable.</p>
            </div>
            <div class="rp-card-footer">
                <div class="rp-format-label">Choose Format</div>
                <div class="rp-format-btns">
                    <button class="rp-fmt-btn rp-fmt-excel" onclick="openReportConfig('monthly','absent','excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="rp-fmt-btn rp-fmt-pdf" onclick="openReportConfig('monthly','absent','pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
                <button class="rp-preview-btn" onclick="openReportConfig('monthly','absent','preview')">
                    <i class="fas fa-eye"></i> Preview in Browser
                </button>
            </div>
        </div>

        {{-- Late --}}
        <div class="rp-card stripe-amber animate-fade" style="animation-delay:0.4s;">
            <div class="rp-card-body">
                <div class="rp-icon ic-amber"><i class="fas fa-clock"></i></div>
                <h3 class="rp-card-title">Tardiness Reports</h3>
                <p class="rp-card-desc">Focuses on employees with late arrivals — tracks total lates and minutes delayed for performance reviews.</p>
            </div>
            <div class="rp-card-footer">
                <div class="rp-format-label">Choose Format</div>
                <div class="rp-format-btns">
                    <button class="rp-fmt-btn rp-fmt-excel" onclick="openReportConfig('monthly','late','excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="rp-fmt-btn rp-fmt-pdf" onclick="openReportConfig('monthly','late','pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
                <button class="rp-preview-btn" onclick="openReportConfig('monthly','late','preview')">
                    <i class="fas fa-eye"></i> Preview in Browser
                </button>
            </div>
        </div>

        {{-- Undertime --}}
        <div class="rp-card stripe-violet animate-fade" style="animation-delay:0.45s;">
            <div class="rp-card-body">
                <div class="rp-icon ic-violet"><i class="fas fa-hourglass-half"></i></div>
                <h3 class="rp-card-title">Undertime Logs</h3>
                <p class="rp-card-desc">Monitors employees who left before their shift ended — summarizes undertime instances for compensation tracking.</p>
            </div>
            <div class="rp-card-footer">
                <div class="rp-format-label">Choose Format</div>
                <div class="rp-format-btns">
                    <button class="rp-fmt-btn rp-fmt-excel" onclick="openReportConfig('monthly','undertime','excel')">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="rp-fmt-btn rp-fmt-pdf" onclick="openReportConfig('monthly','undertime','pdf')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
                <button class="rp-preview-btn" onclick="openReportConfig('monthly','undertime','preview')">
                    <i class="fas fa-eye"></i> Preview in Browser
                </button>
            </div>
        </div>

    </div>
</div>

{{-- ══ CONFIG MODAL ══ --}}
<div class="modal fade" id="reportConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rc-modal-content">

            {{-- Header --}}
            <div class="rc-modal-header">
                <div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:44px;height:44px;background:#ededff;border-radius:13px;display:flex;align-items:center;justify-content:center;color:#4f46e5;font-size:1.2rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <h5 style="font-weight:850;color:#0f172a;font-size:1.3rem;margin:0;">Generate Report</h5>
                            <div id="rc_format_badge" class="rc-format-badge badge-excel" style="margin-top:4px;">
                                <i class="fas fa-file-excel"></i> Excel (.xlsx)
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"
                    style="background-color:#f1f5f9;border-radius:50%;padding:10px;opacity:1;"></button>
            </div>

            {{-- Body --}}
            <div class="rc-modal-body">
                <input type="hidden" id="report_type">
                <input type="hidden" id="report_status">
                <input type="hidden" id="report_format">

                {{-- Context tag --}}
                <div style="background:#f8fafc;padding:10px 18px;border-radius:13px;border:1.5px solid #edf2f7;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
                    <i class="fas fa-filter" style="color:#6366f1;"></i>
                    <span style="font-size:0.82rem;font-weight:700;color:#475569;">
                        Report Scope: <span id="label_report_context" style="color:#6366f1;">ALL EMPLOYEES</span>
                    </span>
                </div>

                {{-- Date Range --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="sig-lbl">Start Date</label>
                        <input type="date" id="input_start_date" value="{{ date('Y-m-01') }}"
                            style="width:100%;padding:11px 16px;border-radius:13px;border:2px solid #e2e8f0;font-weight:600;font-size:0.88rem;color:#0f172a;outline:none;background:white;" class="sig-input">
                    </div>
                    <div class="col-md-6">
                        <label class="sig-lbl">End Date</label>
                        <input type="date" id="input_end_date" value="{{ date('Y-m-t') }}"
                            style="width:100%;padding:11px 16px;border-radius:13px;border:2px solid #e2e8f0;font-weight:600;font-size:0.88rem;color:#0f172a;outline:none;background:white;" class="sig-input">
                    </div>
                </div>

                {{-- Signatories --}}
                <div style="font-size:0.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px;">Signatories</div>
                <div class="row g-3 mb-1">
                    {{-- Prepared By --}}
                    <div class="col-lg-4">
                        <div class="sig-box sig-box-prep">
                            <h6>Prepared By</h6>
                            <label class="sig-lbl">Full Name</label>
                            <input class="sig-input uppercase" id="prep_name" placeholder="CHRISTINE JOY C. MAAPOY">
                            <label class="sig-lbl">Position Line 1</label>
                            <input class="sig-input" id="prep_pos" placeholder="Administrative Assistant III">
                            <label class="sig-lbl">Position Line 2</label>
                            <input class="sig-input" id="prep_pos2" placeholder="E-Form7 In-Charge">
                            <label class="sig-lbl">Position Line 3</label>
                            <input class="sig-input" id="prep_pos3" placeholder="...">
                        </div>
                    </div>
                    {{-- Certified Correct By --}}
                    <div class="col-lg-4">
                        <div class="sig-box sig-box-cert">
                            <h6>Certified Correct By</h6>
                            <label class="sig-lbl">Full Name</label>
                            <input class="sig-input uppercase" id="cert_name" placeholder="MICHELLE A. MAL-IN">
                            <label class="sig-lbl">Position</label>
                            <input class="sig-input" id="cert_pos" placeholder="HRMO II">
                        </div>
                    </div>
                    {{-- Verified Correct By --}}
                    <div class="col-lg-4">
                        <div class="sig-box sig-box-ver">
                            <h6>Verified Correct By</h6>
                            <label class="sig-lbl">Full Name</label>
                            <input class="sig-input uppercase" id="ver_name" placeholder="ROSELYN B. SENCIL">
                            <label class="sig-lbl">Position</label>
                            <input class="sig-input" id="ver_pos" placeholder="HRMO V">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="rc-modal-footer" style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn px-4 py-2" data-bs-dismiss="modal"
                    style="border-radius:12px;font-weight:700;color:#64748b;background:#f1f5f9;border:none;font-size:0.85rem;">
                    Cancel
                </button>
                <button id="rc_gen_btn" type="button" onclick="executeReportGeneration()"
                    class="rc-gen-btn rc-gen-btn-excel">
                    <i class="fas fa-file-excel"></i> Generate Excel
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══ PREVIEW MODAL ══ --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:90%;width:1100px;">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
            <div class="modal-header bg-light" style="border-bottom:1px solid #f1f5f9;">
                <h5 class="modal-title" style="font-weight:700;color:#1e293b;">
                    <i class="fas fa-eye" style="color:#0ea5e9;margin-right:8px;"></i> Document Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 position-relative" style="height:75vh;background:#e2e8f0;">
                <div id="previewSpinner" class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center"
                    style="background:rgba(255,255,255,0.85);z-index:10;">
                    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <iframe id="previewFrame" style="width:100%;height:100%;border:none;"
                    onload="document.getElementById('previewSpinner').style.display='none';"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let configModal;

    document.addEventListener('DOMContentLoaded', function () {
        configModal = new bootstrap.Modal(document.getElementById('reportConfigModal'));
    });

    const formatMeta = {
        excel:   { badge: 'badge-excel', badgeHtml: '<i class="fas fa-file-excel"></i> Excel (.xlsx)',   btnClass: 'rc-gen-btn-excel',   btnHtml: '<i class="fas fa-file-excel"></i> Generate Excel' },
        pdf:     { badge: 'badge-pdf',   badgeHtml: '<i class="fas fa-file-pdf"></i> PDF Document',     btnClass: 'rc-gen-btn-pdf',     btnHtml: '<i class="fas fa-file-pdf"></i> Generate PDF' },
        preview: { badge: 'badge-preview',badgeHtml: '<i class="fas fa-eye"></i> Browser Preview',      btnClass: 'rc-gen-btn-preview', btnHtml: '<i class="fas fa-eye"></i> Open Preview' },
    };

    const contextMap = {
        all:       'ALL EMPLOYEES',
        present:   'PRESENT EMPLOYEES ONLY',
        absent:    'ABSENCE RECORDS ONLY',
        late:      'LATE RECORDS ONLY',
        undertime: 'UNDERTIME LOGS ONLY',
    };

    function openReportConfig(type, status, format) {
        document.getElementById('report_type').value   = type;
        document.getElementById('report_status').value = status;
        document.getElementById('report_format').value = format;

        // Context label
        document.getElementById('label_report_context').innerText =
            contextMap[status.toLowerCase()] || 'ALL EMPLOYEES';

        // Badge
        const meta = formatMeta[format] || formatMeta.excel;
        const badge = document.getElementById('rc_format_badge');
        badge.className = 'rc-format-badge ' + meta.badge;
        badge.innerHTML = meta.badgeHtml;

        // Generate button
        const btn = document.getElementById('rc_gen_btn');
        btn.className = 'rc-gen-btn ' + meta.btnClass;
        btn.innerHTML = meta.btnHtml;

        configModal.show();
    }

    function executeReportGeneration() {
        const type   = document.getElementById('report_type').value;
        const status = document.getElementById('report_status').value;
        const format = document.getElementById('report_format').value;

        const signatories = {
            prep_name:  document.getElementById('prep_name').value  || document.getElementById('prep_name').placeholder,
            prep_pos:   document.getElementById('prep_pos').value   || document.getElementById('prep_pos').placeholder,
            prep_pos2:  document.getElementById('prep_pos2').value  || document.getElementById('prep_pos2').placeholder,
            prep_pos3:  document.getElementById('prep_pos3').value  || document.getElementById('prep_pos3').placeholder,
            cert_name:  document.getElementById('cert_name').value  || document.getElementById('cert_name').placeholder,
            cert_pos:   document.getElementById('cert_pos').value   || document.getElementById('cert_pos').placeholder,
            ver_name:   document.getElementById('ver_name').value   || document.getElementById('ver_name').placeholder,
            ver_pos:    document.getElementById('ver_pos').value    || document.getElementById('ver_pos').placeholder,
        };

        let baseUrl = '';
        if (type === 'monthly') baseUrl = "{{ route('admin.export.attendance') }}";
        else if (type === 'weekly')  baseUrl = "{{ route('admin.export.weekly') }}";
        else if (type === 'yearly')  baseUrl = "{{ route('admin.export.yearly') }}";

        const startVal = document.getElementById('input_start_date').value;
        const [y, m]   = startVal.split('-');

        const timeline = {};
        if      (type === 'monthly') { timeline.month = m; timeline.year = y; }
        else if (type === 'yearly')  { timeline.year = y; }
        else if (type === 'weekly')  { timeline.date = startVal; }

        const params   = new URLSearchParams({ status, format, ...timeline, ...signatories });
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
        const frame = document.getElementById('previewFrame');
        frame.src = 'about:blank';
        setTimeout(() => { frame.src = url; }, 10);
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }
</script>
@endsection
