@extends('layouts.app')

@section('title', 'Forms Management | SDO QC')

@section('styles')
    <style>
        .main-container {
            display: flex;
            flex-direction: column;
            gap: 0;
            height: calc(100vh - 52px);
            overflow: hidden;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }

        .sticky-top-section {
            flex-shrink: 0;
            background: rgba(224, 242, 254, 0.4);
            backdrop-filter: blur(8px);
            padding: 8px 16px 12px;
            z-index: 100;
            position: sticky;
            top: 0;
            margin-bottom: 8px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            background: white;
            padding: 14px 24px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
            margin-bottom: 0;
        }

        .page-header h1 {
            font-size: 1.4rem;
            font-weight: 850;
            color: #1e293b;
            margin: 0;
        }

        /* ── Breadcrumbs ── */
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            background: #f8fafc;
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-item:hover {
            color: #3b82f6;
        }

        .breadcrumb-item.active {
            color: #0f172a;
            cursor: default;
        }

        .breadcrumb-sep {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .forms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 26px;
            padding: 10px 2px 40px;
        }

        /* ── Cards (Folders & Files) ── */
        .manager-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1.5px solid #e2e8f0;
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 12px;
            cursor: pointer;
            text-decoration: none !important;
        }

        .manager-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Folder styling */
        .manager-card.folder {
            background: #fffdf5;
            border-color: #fde68a;
        }
        .manager-card.folder:hover {
            border-color: #f59e0b;
        }
        .manager-card.folder .card-icon {
            background: #fef3c7;
            color: #d97706;
        }

        /* File/Link styling */
        .manager-card.file .card-icon {
            background: #f0f9ff;
            color: #0ea5e9;
        }
        .manager-card.link .card-icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-meta {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .card-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 5;
        }

        .kebab-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: #94a3b8;
            transition: color 0.2s;
            font-size: 0.9rem;
        }

        .kebab-btn:hover {
            color: #475569;
        }

        .dropdown-menu {
            position: absolute;
            top: 40px;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            border: 1px solid #e2e8f0;
            z-index: 100;
            width: 160px;
            display: none;
            overflow: hidden;
            animation: fadeIn 0.15s ease-out;
            text-align: left;
        }

        .dropdown-item {
            padding: 10px 16px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s;
            text-decoration: none !important;
            border: none;
            background: none;
            width: 100%;
            cursor: pointer;
            text-align: left;
        }

        .dropdown-item:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .dropdown-item.delete { color: #ef4444; }
        .dropdown-item.delete:hover { background: #fef2f2; }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-pill {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .btn-secondary:hover {
            background: #dcfce7;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #94a3b8;
            background: #f8fafc;
            border-radius: 24px;
            border: 2px dashed #e2e8f0;
            margin-top: 20px;
        }
    </style>
@endsection

@section('content')
<div class="main-container">
    <div class="sticky-top-section animate-fade">
        <div class="page-header">
            <div>
                <h1>Forms Management</h1>
                <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0; font-weight: 500;">
                    Organize your files and links into folders
                </p>
            </div>

            <div style="display: flex; gap: 10px;">
                <button class="btn btn-primary" style="background: #3b82f6; border: none; border-radius: 50px; padding: 10px 24px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;" onclick="openCreateFolderModal()">
                    <i class="fas fa-folder-plus"></i> New Folder
                </button>
                <button class="btn btn-success" style="background: #10b981; border: none; border-radius: 50px; padding: 10px 24px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;" onclick="openUploadModal()">
                    <i class="fas fa-link"></i> Add Link
                </button>
            </div>
        </div>

        @if($folderId)
            <div class="breadcrumbs" style="background: white; margin-top: 12px; padding: 12px 24px; border-radius: 50px; border: 1.5px solid #edf2f7;">
                <a href="{{ route('admin.forms') }}" class="breadcrumb-item {{ !$folderId ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Root
                </a>
                @foreach($breadcrumbs as $bc)
                    <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                    <a href="{{ route('admin.forms', ['folder_id' => $bc->id]) }}" class="breadcrumb-item {{ $bc->id == $folderId ? 'active' : '' }}">
                        {{ $bc->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if(count($folders) > 0 || count($forms) > 0)
            <div class="forms-grid">
                
                <!-- 1. Folders -->
                @foreach($folders as $f)
                    <a href="{{ route('admin.forms', ['folder_id' => $f->id]) }}" class="manager-card folder">
                        <div class="card-actions">
                            <button class="kebab-btn" onclick="toggleMenu(event, 'folder-{{ $f->id }}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                             <div id="dropdown-folder-{{ $f->id }}" class="dropdown-menu">
                                 <button class="dropdown-item delete" onclick="deleteFolder('{{ $f->id }}', '{{ addslashes($f->name) }}')">
                                     <i class="fas fa-trash"></i> Delete
                                 </button>
                             </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="card-title" title="{{ $f->name }}">{{ $f->name }}</div>
                        <div class="card-meta">Folder</div>
                    </a>
                @endforeach

                <!-- 2. Files -->
                @foreach($forms as $form)
                    <div class="manager-card file {{ $form->embed_url ? 'link' : '' }}" 
                         onclick="openEmbedModal('{{ addslashes($form->title) }}', '{{ $form->embed_url ?: asset('storage/' . $form->file_path) }}')">
                        
                        <div class="card-actions">
                            <button class="kebab-btn" onclick="toggleMenu(event, 'file-{{ $form->id }}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="dropdown-file-{{ $form->id }}" class="dropdown-menu">
                                @if(!$form->embed_url)
                                    <a href="{{ route('admin.forms.download', ['id' => $form->id]) }}" class="dropdown-item" onclick="event.stopPropagation()">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                @endif
                                <button class="dropdown-item" onclick="event.stopPropagation(); openMoveModal({{ $form->id }}, '{{ addslashes($form->title) }}')">
                                    <i class="fas fa-file-export"></i> Move to Folder
                                </button>
                                <button class="dropdown-item delete" onclick="event.stopPropagation(); deleteForm({{ $form->id }}, '{{ addslashes($form->title) }}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>

                        <div class="card-icon">
                            <i class="fas {{ $form->embed_url ? 'fa-link' : 'fa-file-alt' }}"></i>
                        </div>
                        <div class="card-title" title="{{ $form->title }}">{{ $form->title }}</div>
                        <div class="card-meta">
                            @if($form->embed_url)
                                Link • {{ parse_url($form->embed_url, PHP_URL_HOST) }}
                            @else
                                {{ $form->filesize }} • {{ date('M d', strtotime($form->uploaded_at)) }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-folder-open" style="display:block; font-size: 3.5rem; margin-bottom: 20px; opacity: 0.2;"></i>
                <p style="font-weight:800;font-size:1.15rem;margin-bottom:8px; color: #475569;">Empty Directory</p>
                <p style="font-size:0.88rem; max-width: 300px; margin: 0 auto;">Start organizing by creating folders or adding new document links.</p>
            </div>
        @endif
    </div>

    <!-- NEW FOLDER MODAL -->
    <div id="folderModal" class="custom-overlay">
        <div class="custom-box" style="max-width: 400px; text-align: left;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Create Folder</h2>
                <button onclick="closeModal('folderModal')" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">&times;</button>
            </div>
            <form id="folderForm" onsubmit="submitFolder(event)">
                <input type="hidden" name="parent_id" value="{{ $folderId }}">
                <div style="margin-bottom: 24px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; font-size:0.8rem; color:#64748b; text-transform:uppercase;">Folder Name</label>
                    <input type="text" name="name" required placeholder="Enter folder name..."
                        style="width:100%; padding:12px; border-radius:10px; border:1.5px solid #cbd5e1; outline:none; font-weight:600;">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:12px;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:#475569; padding:10px 20px; border-radius:10px; font-weight:700;" onclick="closeModal('folderModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding:10px 24px; border-radius:10px; font-weight:700;">Create Folder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MOVE FORM MODAL -->
    <div id="moveModal" class="custom-overlay">
        <div class="custom-box" style="max-width: 450px; text-align: left;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Move to Folder</h2>
                <button onclick="closeModal('moveModal')" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">&times;</button>
            </div>
            <p id="moveTargetLabel" style="font-size:0.85rem; color:#64748b; margin-bottom:16px; font-weight:600;"></p>
            <div id="folderList" style="max-height: 300px; overflow-y: auto; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; padding: 10px;">
                <!-- Folder items injected here -->
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn" style="background:#f1f5f9; color:#475569; padding:10px 20px; border-radius:10px; font-weight:700;" onclick="closeModal('moveModal')">Cancel</button>
                <button id="moveConfirmBtn" type="button" class="btn btn-primary" style="padding:10px 24px; border-radius:10px; font-weight:700;" disabled>Move Here</button>
            </div>
        </div>
    </div>

    <!-- ADD LINK MODAL (Updated) -->
    <div id="uploadModal" class="custom-overlay">
        <div class="custom-box" style="max-width: 450px; text-align: left;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Add Document Link</h2>
                <button onclick="closeModal('uploadModal')" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">&times;</button>
            </div>
            <form id="uploadForm" onsubmit="submitForm(event, '{{ route('admin.forms.upload') }}', 'uploadModal')">
                <input type="hidden" name="folder_id" value="{{ $folderId }}">
                <div style="margin-bottom: 16px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; font-size:0.8rem; color:#64748b; text-transform:uppercase;">Document Title</label>
                    <input type="text" name="title" required placeholder="e.g. Leave Application Form"
                        style="width:100%; padding:12px; border-radius:10px; border:1.5px solid #cbd5e1; outline:none; font-weight:600;">
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="display:block; margin-bottom:8px; font-weight:700; font-size:0.8rem; color:#64748b; text-transform:uppercase;">URL</label>
                    <input type="url" name="link_url" placeholder="https://docs.google.com/..." required
                        style="width:100%; padding:12px; border-radius:10px; border:1.5px solid #cbd5e1; outline:none; font-weight:600;">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:12px;">
                    <button type="button" class="btn" style="background:#f1f5f9; color:#475569; padding:10px 20px; border-radius:10px; font-weight:700;" onclick="closeModal('uploadModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding:10px 24px; border-radius:10px; font-weight:700;">Save Document</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Embed View Modal -->
    <div id="embedModal" class="custom-overlay" style="display: none; z-index: 1000;">
        <div class="custom-box" style="max-width: 1200px; width: 98%; height: 90vh; text-align: left; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; background: white;">
                <h2 id="embedModalTitle" style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;">View Document</h2>
                <button onclick="closeModal('embedModal')" style="background: none; border: none; font-size: 1.8rem; color: #94a3b8; cursor: pointer;">&times;</button>
            </div>
            <div style="flex: 1; background: #f8fafc; position: relative;">
                <div id="embedLoading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; gap: 12px; color: #64748b;">
                    <i class="fas fa-circle-notch fa-spin" style="font-size: 2rem; color: #3b82f6;"></i>
                    <span style="font-weight: 700; font-size: 0.9rem;">Crunching document...</span>
                </div>
                <iframe id="embedFrame" src="" style="width: 100%; height: 100%; border: none; position: relative; z-index: 1;" onload="document.getElementById('embedLoading').style.display='none'"></iframe>
            </div>
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background: white; display: flex; justify-content: flex-end; gap: 12px;">
                <a id="embedOpenNewTab" href="#" target="_blank" class="btn" style="background: #f1f5f9; color: #475569; text-decoration: none; padding: 10px 20px; border-radius: 10px; font-weight: 700;">
                    <i class="fas fa-external-link-alt"></i> External View
                </a>
                <button type="button" class="btn btn-primary" style="padding: 10px 24px; border-radius: 10px; font-weight: 700;" onclick="closeModal('embedModal')">Close</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleMenu(event, id) {
            event.stopPropagation();
            const dropdown = document.getElementById(`dropdown-${id}`);
            const isOpen = dropdown.style.display === 'block';

            document.querySelectorAll('.dropdown-menu').forEach(el => el.style.display = 'none');
            if (!isOpen) dropdown.style.display = 'block';
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu').forEach(el => el.style.display = 'none');
        });

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        /* ── Modals ── */
        function openUploadModal() {
            document.getElementById('uploadForm').reset();
            document.getElementById('uploadModal').style.display = 'flex';
        }

        function openCreateFolderModal() {
            document.getElementById('folderForm').reset();
            document.getElementById('folderModal').style.display = 'flex';
        }

        let currentMoveFormId = null;
        let selectedMoveFolderId = null;

        async function openMoveModal(formId, title) {
            currentMoveFormId = formId;
            document.getElementById('moveTargetLabel').textContent = `Moving: ${title}`;
            document.getElementById('moveModal').style.display = 'flex';
            document.getElementById('moveConfirmBtn').disabled = true;

            const folderListDiv = document.getElementById('folderList');
            folderListDiv.innerHTML = '<div style="text-align:center; padding:20px;"><i class="fas fa-circle-notch fa-spin"></i> Loading...</div>';

            try {
                const response = await fetch('{{ route('admin.forms.folders.list') }}');
                const folders = await response.json();
                
                // Build a simple root + folder tree
                let html = `
                    <div class="move-folder-item" onclick="selectMoveFolder(null, this)" style="padding: 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 700; color: #1e293b;">
                        <i class="fas fa-home" style="color: #64748b;"></i> Root Directory
                    </div>
                `;

                folders.forEach(f => {
                    html += `
                        <div class="move-folder-item" onclick="selectMoveFolder(${f.id}, this)" style="padding: 10px 10px 10px 30px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; color: #475569;">
                            <i class="fas fa-folder" style="color: #f59e0b;"></i> ${f.name}
                        </div>
                    `;
                });

                folderListDiv.innerHTML = html;
            } catch (error) { folderListDiv.innerHTML = '<p style="color:red; padding:10px;">Failed to load folders.</p>'; }
        }

        function selectMoveFolder(id, el) {
            selectedMoveFolderId = id;
            document.querySelectorAll('.move-folder-item').forEach(item => {
                item.style.background = 'transparent';
                item.style.color = '#475569';
                if(item.querySelector('.fa-home')) item.style.color = '#1e293b';
            });
            el.style.background = '#3b82f6';
            el.style.color = 'white';
            el.querySelectorAll('i').forEach(i => i.style.color = 'white');
            document.getElementById('moveConfirmBtn').disabled = false;
        }

        document.getElementById('moveConfirmBtn').addEventListener('click', async () => {
            const btn = document.getElementById('moveConfirmBtn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Moving...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('form_id', currentMoveFormId);
            if(selectedMoveFolderId !== null) formData.append('folder_id', selectedMoveFolderId);

            try {
                const response = await fetch('{{ route('admin.forms.move') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: formData
                });
                const result = await response.json();
                if (result.success) window.location.reload();
                else alert('Error: ' + result.message);
            } catch (error) { alert('Move failed: ' + error); }
        });

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'embedModal') document.getElementById('embedFrame').src = '';
        }

        function openEmbedModal(title, url) {
            // Check if document was just deleted by menu
            document.getElementById('embedModalTitle').textContent = title;
            let embedUrl = url;
            if (embedUrl.includes('docs.google.com/forms') && !embedUrl.includes('embedded=true')) {
                embedUrl += (embedUrl.includes('?') ? '&' : '?') + 'embedded=true';
            }
            document.getElementById('embedLoading').style.display = 'flex';
            document.getElementById('embedFrame').src = embedUrl;
            document.getElementById('embedOpenNewTab').href = url;
            document.getElementById('embedModal').style.display = 'flex';
        }

        /* ── Core Actions ── */
        async function submitFolder(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            try {
                const response = await fetch('{{ route('admin.forms.folder.create') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: formData
                });
                const result = await response.json();
                if (result.success) window.location.reload();
                else alert('Error: ' + result.message);
            } catch (error) { alert('Request failed: ' + error); }
        }

        async function submitForm(event, url, modalId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const btn = form.querySelector('button[type="submit"]');
            const oldText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Saving...';
            btn.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: formData
                });
                const result = await response.json();
                if (result.success) window.location.reload();
                else alert('Error: ' + result.message);
            } catch (error) { alert('Request failed: ' + error); }
            finally {
                btn.innerHTML = oldText;
                btn.disabled = false;
            }
        }

        function deleteForm(id, title) {
            if (confirm(`Are you sure you want to delete the document "${title}"?`)) {
                sendDelete('{{ route('admin.forms.delete') }}', id);
            }
        }

        function deleteFolder(id, name) {
            if (confirm(`Delete folder "${name}"? All files inside will also be removed.`)) {
                sendDelete('{{ route('admin.forms.folder.delete') }}', id);
            }
        }

        async function sendDelete(url, id) {
            const formData = new FormData();
            formData.append('id', id);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                    body: formData
                });
                const result = await response.json();
                if (result.success) window.location.reload();
                else alert('Error: ' + result.message);
            } catch (error) { alert('Request failed: ' + error); }
        }
    </script>
@endsection