@extends('layouts.app')

@section('title', 'User Profile | SDO QC')

@section('styles')
    <style>
        .profile-page-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 40px);
            overflow: hidden;
        }

        .profile-container {
            display: flex;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            min-height: 400px;
            flex-shrink: 0;
            margin-bottom: 24px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .users-table-scroll-area {
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
            padding-bottom: 20px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .users-table-scroll-area::-webkit-scrollbar {
            display: none;
        }

        /* ── Sidebar ── */
        .profile-sidebar {
            width: 220px;
            padding: 24px 12px;
            border-right: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f8fafc;
            flex-shrink: 0;
        }

        .avatar-container {
            position: relative;
            margin-bottom: 16px;
        }

        .avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
            border: 3px solid white;
        }

        .edit-avatar {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: #3b82f6;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2.5px solid white;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.35);
            transition: transform 0.2s;
        }

        .edit-avatar:hover {
            transform: scale(1.1);
        }

        .profile-sidebar h3 {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            text-align: center;
        }

        .profile-sidebar .role-badge {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .profile-nav {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .profile-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 12px;
            color: #64748b;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            font-size: 0.88rem;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .profile-nav-item i {
            font-size: 0.95rem;
            width: 18px;
            text-align: center;
        }

        .profile-nav-item.active {
            background: #eff6ff;
            color: #2563eb;
        }

        .profile-nav-item:hover:not(.active) {
            background: #f1f5f9;
            color: #1e293b;
        }

        /* ── Content ── */
        .profile-content {
            flex: 1;
            padding: 24px 30px;
            background: #ffffff;
        }

        .profile-content h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .section-desc {
            font-size: 0.82rem;
            color: #94a3b8;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            font-size: 0.78rem;
            color: #64748b;
            margin-bottom: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.2s;
            outline: none;
            font-weight: 500;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group input[readonly] {
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon .icon-right {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-discard {
            flex: 1;
            padding: 10px;
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #64748b;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-discard:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-save-changes {
            flex: 1;
            padding: 10px;
            border: none;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-save-changes:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        }

        /* ── Tab System ── */
        .tab-content {
            display: none;
            animation: fadeInUp 0.35s ease;
        }

        .tab-content.active {
            display: block;
        }

        /* ── Divider ── */
        .section-divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 28px 0;
        }

        /* ── Users Table ── */
        .users-table-container {
            background: white;
            border-radius: 20px;
            padding: 30px 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
        }

        .users-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .users-table-header h2 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        table.prof-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.prof-table th {
            text-align: left;
            color: #94a3b8;
            font-size: 0.72rem;
            text-transform: uppercase;
            padding: 12px 16px;
            border-bottom: 1.5px solid #f1f5f9;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        table.prof-table td {
            padding: 14px 16px;
            font-size: 0.88rem;
            color: #334155;
            border-bottom: 1px solid #f8fafc;
        }

        table.prof-table tr:last-child td {
            border-bottom: none;
        }

        .u-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .role-tag {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-block;
        }

        .btn-add-user {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s;
            font-family: inherit;
            box-shadow: 0 3px 10px rgba(59, 130, 246, 0.25);
        }

        .btn-add-user:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);
        }

        /* ── Success banner ── */
        .alert-success-bar {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #15803d;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .role-tag-admin {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .role-tag-staff {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .user-card {
            background: white;
            border-radius: 20px;
            padding: 30px 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            transition: all 0.3s ease;
            text-align: center;
        }

        .user-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.1);
        }

        .btn-delete-user {
            background: #fee2e2;
            color: #ef4444;
            border: none;
            cursor: pointer;
            font-size: 0.88rem;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        .btn-delete-user:hover {
            background: #fca5a5;
            color: white;
        }

        .users-table-container-transparent {
            background: transparent;
            padding: 0;
            box-shadow: none;
            border: none;
        }

        .users-table-header-custom {
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
        }

        .users-grid-inner {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .u-avatar-lg {
            width: 80px;
            height: 80px;
            font-size: 2rem;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
            border: 3px solid white;
        }

        .user-card-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .role-tag-item {
            margin-bottom: 18px;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .user-joined-date {
            font-size: 0.82rem;
            color: #94a3b8;
            font-weight: 500;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .user-card-footer {
            margin-top: auto;
            width: 100%;
            border-top: 1px dashed #e2e8f0;
            padding-top: 20px;
            display: flex;
            justify-content: center;
        }

        .active-user-badge {
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.88rem;
            font-weight: 700;
            padding: 12px 0;
            border-radius: 12px;
            width: 100%;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .profile-container {
                flex-direction: column;
            }

            .profile-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #f1f5f9;
            }

            .form-row {
                flex-direction: column;
                gap: 14px;
            }

            .profile-content {
                padding: 28px 20px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="profile-page-wrapper">
        <div class="profile-container">

        {{-- ── Sidebar ── --}}
        <div class="profile-sidebar">
            <div class="avatar-container">
                @if($currentUser->photo ?? null)
                    <img src="{{ asset($currentUser->photo) }}" class="avatar" alt="Profile">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->username) }}&background=3b82f6&color=fff&size=150"
                        class="avatar" alt="Profile">
                @endif
                <div class="edit-avatar" onclick="document.getElementById('hidden_photo_input').click()">
                    <i class="fas fa-pencil-alt" style="font-size:0.75rem;"></i>
                </div>
            </div>

            <h3>{{ $currentUser->username }}</h3>
            <span class="role-badge">{{ $currentUser->role }}</span>

            <div class="profile-nav">
                <div class="profile-nav-item active" id="nav-personal" onclick="switchTab('personal', this)">
                    <i class="fas fa-user"></i> Personal Info
                </div>
                <div class="profile-nav-item" id="nav-login" onclick="switchTab('login', this)">
                    <i class="fas fa-lock"></i> Login & Password
                </div>
                <a href="{{ route('logout') }}" onclick="confirmLogout(event)" class="profile-nav-item"
                    style="color:#ef4444;">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </a>
            </div>
        </div>

        {{-- ── Content Area ── --}}
        <div class="profile-content">
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="action" value="update_self">
                <input type="file" name="self_photo" id="hidden_photo_input" style="display:none;" accept="image/*"
                    onchange="previewAvatar(this); submitFormOnChange(this);">

                @if(session('success'))
                    <div class="alert-success-bar">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                {{-- ── Personal Info Tab ── --}}
                <div id="tab-personal" class="tab-content active">
                    <h2>Personal Information</h2>
                    <p class="section-desc">Update your display name and account email.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="{{ $currentUser->username }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="{{ $currentUser->role }}" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="{{ $currentUser->email ?? '' }}"
                                placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-discard"
                            onclick="event.preventDefault(); window.location.reload();">Discard</button>
                        <button type="submit" class="btn-save-changes">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>

                {{-- ── Login & Password Tab ── --}}
                <div id="tab-login" class="tab-content">
                    <h2>Login & Password</h2>
                    <p class="section-desc">Change your account password. Leave blank to keep the current one.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Username</label>
                            <input type="text" value="{{ $currentUser->username }}" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group input-with-icon">
                            <label>New Password</label>
                            <input type="password" name="self_password" id="newPasswordInput"
                                placeholder="Enter new password…">
                            <i class="fas fa-eye icon-right" onclick="togglePassword(this)"></i>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-discard"
                            onclick="switchTab('personal', document.getElementById('nav-personal'))">Cancel</button>
                        <button type="submit" class="btn-save-changes">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- ── System Users (Admin only) ── --}}
    @if(session('role') === 'Admin')
        <div class="users-table-scroll-area">
            <div class="users-table-container users-table-container-transparent">
            <div class="users-table-header users-table-header-custom">
                <h2><i class="fas fa-users" style="color:#3b82f6;margin-right:8px;font-size:1.1rem;"></i>System Users</h2>
                <button class="btn-add-user" onclick="openAddUserModal()">
                    <i class="fas fa-plus"></i> Add User
                </button>
            </div>
            
            <div class="users-grid users-grid-inner">
                @foreach($allUsers as $u)
                    <div class="user-card">
                        
                        <div class="u-avatar u-avatar-lg">
                            {{ strtoupper(substr($u->username, 0, 1)) }}
                         </div>
                        
                        <h4 class="user-card-title">{{ $u->username }}</h4>
                        
                        <span class="role-tag role-tag-item {{ $u->role === 'Admin' ? 'role-tag-admin' : 'role-tag-staff' }}">
                            <i class="fas {{ $u->role === 'Admin' ? 'fa-user-shield' : 'fa-user-tie' }}"></i> {{ $u->role }} Position
                        </span>
                        
                        <div class="user-joined-date">
                            <i class="fas fa-calendar-alt"></i> Joined: {{ isset($u->created_at) ? date('M d, Y', strtotime($u->created_at)) : '—' }}
                        </div>

                        <div class="user-card-footer">
                            @if($u->id !== $currentUser->id)
                                <form method="POST" action="{{ route('admin.user.delete') }}" style="display:inline; width: 100%;" data-name="{{ $u->username }}" onsubmit="return confirm('Are you sure you want to delete user ' + this.getAttribute('data-name') + '?')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $u->id }}">
                                    <button type="submit" class="btn-delete-user">
                                        <i class="fas fa-trash-alt"></i> Delete Account
                                    </button>
                                </form>
                            @else
                                <div class="active-user-badge">
                                    <i class="fas fa-check-circle" style="color:#10b981;"></i> Current Active User
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        </div>
    @endif
    </div>

    {{-- ── Add User Modal ── --}}
    <div id="addUserModal" class="custom-overlay">
        <div class="custom-box" style="max-width:420px;text-align:left;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                <h2 style="font-size:1.3rem;color:#0f172a;font-weight:800;margin:0;">
                    <i class="fas fa-user-plus" style="color:#3b82f6;margin-right:8px;"></i>Add System User
                </h2>
                <i class="fas fa-times" style="color:#94a3b8;font-size:1.1rem;cursor:pointer;"
                    onclick="document.getElementById('addUserModal').style.display='none'"></i>
            </div>

            <form method="POST" action="{{ route('admin.user.add') }}">
                @csrf
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>
                <div class="form-group" style="margin-bottom:26px;">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="Staff">Staff</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div style="display:flex;gap:12px;">
                    <button type="button" class="btn-discard" style="flex:1;"
                        onclick="document.getElementById('addUserModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn-save-changes" style="flex:1;">
                        <i class="fas fa-plus"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'flex';
        }

        function switchTab(tabId, navEl) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.profile-nav-item').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            if (navEl) navEl.classList.add('active');
        }

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => document.querySelector('.avatar').src = e.target.result;
                reader.readAsDataURL(input.files[0]);
            }
        }

        function submitFormOnChange(input) {
            if (input.files && input.files[0]) {
                input.closest('form').submit();
            }
        }

        function togglePassword(icon) {
            const input = document.getElementById('newPasswordInput');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
@endsection