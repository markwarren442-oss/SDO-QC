@extends('layouts.app')

@section('title', 'User Manual | SDO QC')

@section('styles')
    <style>
        .manual-wrapper {
            max-width: 100%;
            transition: all 0.3s;
        }

        /* Sticky Header Navigation */
        .sticky-top-nav {
            position: sticky;
            top: 20px;
            z-index: 100;
            background: #f4f7f6; /* match body background */
            padding-bottom: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid transparent;
            transition: all 0.3s;
        }
        
        /* Add slight shadow when scrolled */
        .sticky-top-nav.scrolled {
            background: rgba(244, 247, 246, 0.95);
            backdrop-filter: blur(10px);
            border-bottom-color: #e2e8f0;
            box-shadow: 0 10px 15px -10px rgba(0,0,0,0.05);
        }

        .manual-header {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            border-left: 5px solid #3b82f6;
        }

        .manual-title {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 800;
            color: #1e293b;
        }

        .manual-subtitle {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 1.1rem;
        }

        .manual-header-icon {
            font-size: 3.5rem;
            color: #3b82f6;
            background: #eff6ff;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
        }

        .manual-section {
            background: white;
            border-radius: 20px;
            padding: 50px;
            margin-bottom: 30px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.04);
            border: 1px solid #f1f5f9;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .manual-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tabs Styles */
        .manual-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .manual-tab {
            padding: 18px 30px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }
        .manual-tab:hover {
            border-color: #cbd5e1;
            color: #1e293b;
            transform: translateY(-2px);
        }
        .manual-tab.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(59,130,246,0.3);
        }

        .section-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 25px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-icon {
            background: #eff6ff;
            color: #3b82f6;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 25px;
        }

        .feature-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 25px;
            transition: all 0.2s;
        }

        .feature-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }

        .feature-card h4 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feature-card h4 i {
            color: #3b82f6;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-list li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 14px;
            font-size: 1.05rem;
            color: #475569;
            line-height: 1.6;
        }

        .feature-list li::before {
            content: '\f054'; /* FontAwesome chevron-right */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 4px;
            font-size: 0.8rem;
            color: #3b82f6;
        }

        .highlight-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 5px;
        }

        /* Search bar styles */
        .search-container {
            position: relative;
            max-width: 100%;
            margin-bottom: 20px;
        }
        .search-input {
            width: 100%;
            padding: 16px 20px 16px 55px;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            outline: none;
            transition: all 0.3s;
            font-family: inherit;
        }
        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(59,130,246,0.1);
        }
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.4rem;
        }

        /* AI Chat Styles */
        .ai-float-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: white;
            font-size: 1.8rem;
            border: none;
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 1000;
        }
        .ai-float-btn:hover {
            transform: scale(1.1);
        }
        .ai-chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 380px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            display: none;
            flex-direction: column;
            z-index: 1000;
            transition: opacity 0.3s, transform 0.3s;
            opacity: 0;
            transform: translateY(20px);
        }
        .ai-chat-window.show {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }
        .ai-chat-header {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: white;
            padding: 18px 20px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.1rem;
        }
        .ai-close-btn {
            background: none;
            border: none;
            color: rgba(255,255,255,0.8);
            cursor: pointer;
            font-size: 1.2rem;
        }
        .ai-close-btn:hover { color: white; }
        .ai-chat-body {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ai-msg {
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 0.95rem;
            line-height: 1.5;
            word-wrap: break-word;
        }
        .ai-msg b {
            font-weight: 800;
        }
        .bot-msg {
            background: white;
            color: #1e293b;
            align-self: flex-start;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .user-msg {
            background: #3b82f6;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 5px rgba(59,130,246,0.2);
        }
        .ai-chat-footer {
            padding: 15px;
            background: white;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
        }
        .ai-chat-footer input {
            flex: 1;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 15px;
            outline: none;
            font-size: 0.95rem;
            font-family: inherit;
        }
        .ai-chat-footer input:focus {
            border-color: #8b5cf6;
        }
        .ai-chat-footer button {
            background: #8b5cf6;
            color: white;
            border: none;
            width: 48px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: 0.2s;
        }
        .ai-chat-footer button:hover {
            background: #7c3aed;
        }

        /* Responsive Mobile Adjustments */
        @media (max-width: 768px) {
            .manual-wrapper {
                margin-left: 0;
                padding: 0 5px;
            }
            .sticky-top-nav {
                top: 5px;
            }
            .sticky-top-nav.scrolled {
                margin-left: 0;
                padding-left: 0;
            }
            .manual-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
                padding: 20px;
                border-left: none;
                border-top: 5px solid #3b82f6;
            }
            .manual-header-icon {
                width: 60px;
                height: 60px;
                font-size: 2.5rem;
            }
            .manual-title {
                font-size: 1.8rem;
            }
            .manual-tab {
                padding: 12px 20px;
                font-size: 1rem;
                flex: 1 1 calc(50% - 15px);
                justify-content: center;
            }
            .manual-section {
                padding: 20px;
            }
            .feature-grid {
                grid-template-columns: 1fr;
            }
            .feature-card {
                padding: 15px;
            }
            .ai-chat-window {
                width: calc(100vw - 40px);
                right: 20px;
                bottom: 85px;
            }
            .ai-float-btn {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                bottom: 20px;
                right: 20px;
            }
        }
        
    </style>
@endsection

@section('content')
<div class="manual-wrapper">

    <div class="sticky-top-nav" id="stickyNav">
        <div class="manual-header">
            <div class="manual-header-icon">
                <i class="fas fa-book-reader"></i>
            </div>
            <div>
                <h1 class="manual-title">System User Manual</h1>
                <p class="manual-subtitle">A quick and easy guide to understanding and using the SDO QC Personnel System as an Administrator.</p>
            </div>
        </div>

        <!-- Interactive Search Bar -->
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="manualSearch" class="search-input" placeholder="Search the manual for topics (e.g. 'Excel', 'Absence', 'Print')...">
        </div>

        <!-- Navigation Tabs -->
        <div class="manual-tabs">
            <button class="manual-tab active" data-target="sec-welcome"><i class="fas fa-compass"></i> Navigation Guide</button>
            <button class="manual-tab" data-target="sec-employees"><i class="fas fa-users"></i> Employees</button>
            <button class="manual-tab" data-target="sec-attendance"><i class="fas fa-calendar-alt"></i> Attendance</button>
            <button class="manual-tab" data-target="sec-absences"><i class="fas fa-user-xmark"></i> Absences</button>
            <button class="manual-tab" data-target="sec-system"><i class="fas fa-cog"></i> System Tools</button>
        </div>
    </div>

    <!-- Section: Welcome & Navigation Guide (NEW) -->
    <div class="manual-section active" id="sec-welcome">
        <h2 class="section-title"><div class="section-icon"><i class="fas fa-compass"></i></div> Getting Around the System</h2>
        
        <div style="background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 20px; padding: 25px; margin-bottom: 25px; text-align: center;">
            <img src="{{ asset('images/app_navigation_guide.png') }}" alt="Navigation Guide" style="max-width: 100%; max-height: 480px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <h4><i class="fas fa-bars"></i> The Left Sidebar</h4>
                <ul class="feature-list">
                    <li>Look at the dark panel on the left side of your screen. This is your <b>Main Menu</b>!</li>
                    <li>Clicking any of the links (like Employees, Attendance, etc.) will take you directly to that page.</li>
                    <li>You can hide the sidebar by clicking the <b>three lines</b> button at the top to save space.</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <h4><i class="fas fa-user-circle"></i> Your Profile Menu</h4>
                <ul class="feature-list">
                    <li>At the very bottom left, you will see your name and picture.</li>
                    <li>Click the <b>Red Door Icon</b> right next to your name when you are ready to log out securely.</li>
                    <li>To update your picture, click the <b>Profile</b> tab in the menu above it.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Section: Employees -->
    <div class="manual-section" id="sec-employees">
        <h2 class="section-title"><div class="section-icon"><i class="fas fa-users"></i></div> Managing Employees</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h4><i class="fas fa-user-plus"></i> Adding & Editing Employees</h4>
                <ul class="feature-list">
                    <li><strong>Add Employee Button:</strong> Opens a modal where you type the Employee ID, First/Last Name, and Station. Hit Save to add them instantly.</li>
                    <li><strong>Blue Pencil Icon:</strong> Opens the Edit Employee modal to correct typos or update their active status.</li>
                    <li><strong>Red Trash Can Icon:</strong> Prompts a confirmation modal. If confirmed, permanently deletes the employee and their specific attendance records.</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <h4><i class="fas fa-file-excel"></i> Mass Import via Excel</h4>
                <ul class="feature-list">
                    <li><strong>Download Template Button:</strong> Generates a blank `.xlsx` file formatted perfectly for the system to understand.</li>
                    <li><strong>Import Excel Button:</strong> Opens a file upload box. After dragging and dropping your filled Excel file, it opens a Preview Modal showing you which rows will be imported and highlights any errors before finalizing!</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4><i class="fas fa-tasks"></i> Bulk Control Buttons</h4>
                <ul class="feature-list">
                    <li><strong>Bulk Actions -> Change Selected Status:</strong> After ticking checkboxes next to multiple names, this opens a modal to change them all to "Active", "Inactive", or "On Leave" in one click.</li>
                    <li><strong>Bulk Actions -> Clear All:</strong> Opens a massive red warning confirmation modal. If you type "CONFIRM", it completely deletes EVERY employee from the system for a fresh start. Use with extreme caution!</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Section: Attendance -->
    <div class="manual-section" id="sec-attendance">
        <h2 class="section-title"><div class="section-icon"><i class="fas fa-calendar-alt"></i></div> Attendance & Calendar</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h4><i class="fas fa-list"></i> The Batch Grid View Buttons</h4>
                <ul class="feature-list">
                    <li><strong>View Toggle Switch:</strong> Use this to flip between "Batch View" (typing codes like A, L, P for everyone at once) and "Individual View" (clicking one employee to see deep data).</li>
                    <li><strong>Save Batch / Changes Button:</strong> While the system auto-saves when you click out of a cell, pressing this forces a deep manual save of everything currently in the grid.</li>
                    <li><strong>Merge Selected Button:</strong> Click and drag your mouse across multiple days, then click this to open the Merge Modal. You can pick "Vacation Leave" or "Sick", and it turns the days into one big solid block! Double-click a merged block to <strong>Unmerge</strong> it.</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4><i class="fas fa-calendar-plus"></i> Month Management Modals</h4>
                <ul class="feature-list">
                    <li><strong>Next/Prev Month Arrows:</strong> Found at the top center, let you navigate forward and backward in time.</li>
                    <li><strong>Add Holiday Button:</strong> Opens a modal where you type the holiday name and pick the day. It instantly shades that day completely red (HOL) for everyone so you don't have to input it manually.</li>
                    <li><strong>Clear Month Button:</strong> Opens a confirmation modal. Erases all typed attendance and merges strictly for the month you are viewing.</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4><i class="fas fa-user-clock"></i> Individual View Shortcuts</h4>
                <ul class="feature-list">
                    <li><strong>Mark All Present Button:</strong> Found in Individual View. Instantly fills every blank day of the month for that person as Present (/) so you only log exceptions.</li>
                    <li><strong>Quick Mark Absent:</strong> Found in Batch View (Yellow Button). Opens a quick modal to set a specific day for a specific person to Absent automatically.</li>
                    <li><strong>Print Form Button:</strong> Hides all menus, cleans up the grid borders, and opens your browser's print dialog so you can print a perfect DTR sheet for a specific Station.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Section: Absences -->
    <div class="manual-section" id="sec-absences">
        <h2 class="section-title"><div class="section-icon"><i class="fas fa-user-xmark"></i></div> Absences Tracker</h2>
        <div class="feature-grid">
            <div class="feature-card" style="grid-column: 1 / -1;">
                <h4><i class="fas fa-clipboard-list"></i> Tagging the Reason for Absence</h4>
                <ul class="feature-list">
                    <li>When you tag someone "Absent" on the calendar, head over to the <strong>Absences</strong> tab to give it a context.</li>
                    <li>If you see a yellow <span style="background:#fef08a; color:#854d0e; padding:2px 6px; border-radius:4px; font-size:0.8rem; font-weight:bold;">Needs Reason</span> label, click the blue <strong>"Add Reason"</strong> button next to it.</li>
                    <li><strong>Add Reason Modal:</strong> This popup lets you select the Absence Type (e.g., Sick Leave, Vacation Leave, Without Pay) from a dropdown. It also features a "Custom Details" text box where you can type an exact reason (e.g., "Doctor Appointment").</li>
                    <li><strong>Report Toggles (Top Left):</strong> Switch between "Monthly View" and "Yearly Report" buttons at the top right to filter the absence tags across different time spans.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Section: Forms & System -->
    <div class="manual-section" id="sec-system">
        <h2 class="section-title"><div class="section-icon"><i class="fas fa-cog"></i></div> System Settings & Logs</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h4><i class="fas fa-link"></i> Adding Form Links</h4>
                <ul class="feature-list">
                    <li><strong>Add Link Button (Top Right):</strong> Opens the "Add Document Link" modal. Type a title and paste an external link (like Google Forms). This makes the form globally accessible for staff clicking the eye icon to view and fill it.</li>
                    <li><strong>Kebab Menu (Three Dots):</strong> Appears on every form card. Opens a sub-menu to Edit/View the link inside a framed <strong>Embed View Modal</strong>, or Delete the link entirely.</li>
                </ul>
            </div>

            <div class="feature-card">
                <h4><i class="fas fa-shield-alt"></i> The Security Audit Logs</h4>
                <ul class="feature-list">
                    <li>This automatic grid tracks who pushed what buttons. It records the action (e.g., "Delete Employee"), the targeted element, the exact Date/Time, the Username of the admin who did it, and their IP address.</li>
                    <li><strong>Filter & Search Box:</strong> Type a username or action (like "Clear Month") to instantly sort through thousands of logs.</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <h4><i class="fas fa-id-badge"></i> Profile & User Management Buttons</h4>
                <ul class="feature-list">
                    <li><strong>Upload Photo Area:</strong> Click your current profile circle to open a tiny file selector. Upload an image, and it will auto-crop it to your profile.</li>
                    <li><strong>Change Password Section:</strong> Enter your old password and new password to securely update your credentials.</li>
                    <li><strong>Add System User Modal:</strong> If you are the Main Admin, you can click "Add User" to make accounts for assistant HR staff. The modal asks for their Username, Name, and Role (Admin/Staff) before saving them to the system table below.</li>
                </ul>
            </div>
        </div>
    </div>

    </div>

    <!-- AI Chatbot UI -->
    <button class="ai-float-btn" onclick="toggleAIChat()">
        <i class="fas fa-sparkles"></i>
    </button>
    
    <div id="aiChatWindow" class="ai-chat-window">
        <div class="ai-chat-header">
            <div><i class="fas fa-robot"></i> SDO-QC AI Guide</div>
            <button class="ai-close-btn" onclick="toggleAIChat()"><i class="fas fa-times"></i></button>
        </div>
        <div class="ai-chat-body" id="aiChatMessages">
            <div class="ai-msg bot-msg">Hi there! 👋 I am your Gemini-powered AI Guide. Can't find what you're looking for in the manual? Ask me anything about how to use the system!</div>
        </div>
        <div class="ai-chat-footer">
            <input type="text" id="aiInput" placeholder="Type your question..." onkeypress="if(event.key === 'Enter') sendAIMessage()">
            <button onclick="sendAIMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Sticky Header Scroll Effect
    window.addEventListener('scroll', function() {
        const stickyNav = document.getElementById('stickyNav');
        if (window.scrollY > 20) {
            stickyNav.classList.add('scrolled');
        } else {
            stickyNav.classList.remove('scrolled');
        }
    });

    // Tab Navigation Logic
    const tabs = document.querySelectorAll('.manual-tab');
    const sections = document.querySelectorAll('.manual-section');
    const tabsContainer = document.querySelector('.manual-tabs');
    const cards = document.querySelectorAll('.feature-card');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            
            tab.classList.add('active');
            const target = tab.getAttribute('data-target');
            document.getElementById(target).classList.add('active');
        });
    });

    // Live Search Functionality
    document.getElementById('manualSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        
        if (term === '') {
            // Restore Tab View
            tabsContainer.style.display = 'flex';
            cards.forEach(c => c.style.display = 'block');
            sections.forEach(sec => sec.classList.remove('active'));
            const activeTab = document.querySelector('.manual-tab.active');
            if (activeTab) {
                document.getElementById(activeTab.getAttribute('data-target')).classList.add('active');
            }
            return;
        }

        // Search Mode (Hide Tabs, show matching cards)
        tabsContainer.style.display = 'none';

        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(term) ? 'block' : 'none';
        });

        // Show sections that contain matching cards
        sections.forEach(sec => {
            sec.classList.remove('active');
            const visibleCards = sec.querySelectorAll('.feature-card[style="display: block;"]');
            if(visibleCards.length > 0) {
                sec.classList.add('active');
            }
        });
    });

    // Gemini AI Chatbot Functionality
    const chatWindow = document.getElementById('aiChatWindow');
    const messagesBox = document.getElementById('aiChatMessages');
    const aiInput = document.getElementById('aiInput');

    function toggleAIChat() {
        if(chatWindow.classList.contains('show')) {
            chatWindow.classList.remove('show');
            setTimeout(() => chatWindow.style.display = 'none', 300);
        } else {
            chatWindow.style.display = 'flex';
            setTimeout(() => chatWindow.classList.add('show'), 10);
            aiInput.focus();
        }
    }

    async function sendAIMessage() {
        const msg = aiInput.value.trim();
        if(!msg) return;

        aiInput.value = '';
        appendMessage('user', msg);
        
        const loaderId = appendMessage('bot', '<i class="fas fa-circle-notch fa-spin" style="color:#8b5cf6;"></i> Thinking...');

        try {
            const contextMsg = "You are an AI assistant specifically designed to help users with the SDO QC Personnel System. Summarize answers confidently based on general web application standards (adding employees, setting absences, printing). Be extremely concise, use short sentences, and wrap keywords in HTML <b> tags.";
            
            const response = await fetch('/api/ai_query', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    message: msg,
                    context: contextMsg
                })
            });
            
            const data = await response.json();
            
            // Convert simple markdown-like **bold** to HTML
            let formattedReply = data.reply.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
            // Convert newlines to breaks
            formattedReply = formattedReply.replace(/\n/g, '<br>');
            
            document.getElementById(loaderId).innerHTML = formattedReply;
        } catch(e) {
            document.getElementById(loaderId).innerHTML = "<span style='color:red;'>Could not connect to Gemini AI. Check your API key.</span>";
        }
        
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    let msgCounter = 0;
    function appendMessage(sender, text) {
        msgCounter++;
        const id = 'msg-' + msgCounter;
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg ${sender}-msg`;
        msgDiv.id = id;
        msgDiv.innerHTML = text;
        messagesBox.appendChild(msgDiv);
        messagesBox.scrollTop = messagesBox.scrollHeight;
        return id;
    }
</script>
@endsection
