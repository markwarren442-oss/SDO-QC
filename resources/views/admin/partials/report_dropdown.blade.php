<div class="btn-group w-100">
    <button class="report-btn w-100" type="button" @if(isset($status)) onclick="openReportConfig('monthly', '{{ $status }}', 'excel')" @else onclick="openReportConfig('monthly', 'ALL', 'excel')" @endif style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
        <i class="fas fa-file-export"></i> Generate Report
    </button>
    <button type="button" class="report-btn dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" style="width: 45px; border-top-left-radius: 0; border-bottom-left-radius: 0;">
        <span class="visually-hidden">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu w-100 border-0 shadow-lg p-2" style="border-radius: 16px; margin-top: 5px;">
        <li class="dropdown-header text-uppercase pb-1" style="font-size: 0.65rem; font-weight: 800; color: #94a3b8;">Select Interval</li>
        
        <!-- Weekly -->
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between rounded p-2" href="javascript:void(0)" onclick="openReportConfig('weekly', '{{ $status }}', 'excel')">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-week text-primary"></i>
                    <span>Weekly Summary</span>
                </div>
                <i class="fas fa-arrow-right opacity-25" style="font-size: 0.7rem;"></i>
            </a>
        </li>

        <!-- Monthly -->
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between rounded p-2" href="javascript:void(0)" onclick="openReportConfig('monthly', '{{ $status }}', 'excel')">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-check text-success"></i>
                    <span>Monthly Detailed</span>
                </div>
                <i class="fas fa-arrow-right opacity-25" style="font-size: 0.7rem;"></i>
            </a>
        </li>

        <!-- Yearly -->
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between rounded p-2" href="javascript:void(0)" onclick="openReportConfig('yearly', '{{ $status }}', 'excel')">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-alt text-warning"></i>
                    <span>Yearly Review</span>
                </div>
                <i class="fas fa-arrow-right opacity-25" style="font-size: 0.7rem;"></i>
            </a>
        </li>

        <li class="dropdown-divider opacity-50"></li>
        
        <li class="px-2 pb-1">
            <button onclick="openReportConfig('monthly', '{{ $status }}', 'preview')" class="btn btn-sm btn-light w-100 rounded-pill" style="font-size: 0.7rem; font-weight: 700;">
                <i class="fas fa-eye me-1"></i> Quick Preview
            </button>
        </li>
    </ul>
</div>
