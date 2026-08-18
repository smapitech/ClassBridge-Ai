@extends('layouts.dashboard')
@section('title', $classroom->title . ' - Live Classroom')

@php
    $modeLabels = [
        'whiteboard' => 'Whiteboard Mode',
        'coding' => 'Coding Mode',
        'text' => 'Text / English Mode',
        'english' => 'Text / English Mode',
        'mathematics' => 'Mathematics Mode',
        'math' => 'Mathematics Mode',
        'presentation' => 'Presentation Mode',
    ];

    $shareLink = $roomJoinLink ?? route('join.room', ['roomCode' => $classroom->room_code]);
    $roomPermissions = [
        'draw' => data_get($session?->metadata, 'student_permissions.draw', data_get($classroom->settings, 'allow_student_draw', true)),
        'type' => data_get($session?->metadata, 'student_permissions.type', data_get($classroom->settings, 'allow_student_type', true)),
        'chat' => data_get($session?->metadata, 'student_permissions.chat', data_get($classroom->settings, 'allow_student_chat', true)),
        'pointer' => data_get($session?->metadata, 'student_permissions.pointer', data_get($classroom->settings, 'show_pointer', true)),
        'code' => data_get($session?->metadata, 'student_permissions.code', data_get($classroom->settings, 'allow_student_code', true)),
        'download' => data_get($session?->metadata, 'student_permissions.download', data_get($classroom->settings, 'allow_resource_download', false)),
    ];

    $canTypeTextPad = $isTeacher || ($myParticipant?->permissions['type'] ?? false);
    $canUseCodeEditor = $isTeacher || ($myParticipant?->permissions['code'] ?? ($myParticipant?->permissions['type'] ?? false));
    $canUseWhiteboard = $isTeacher || ($myParticipant?->permissions['draw'] ?? false);
    $canUsePointer = $isTeacher || ($myParticipant?->permissions['pointer'] ?? false);
    $requestedMode = request()->query('mode');
    $currentMode = $requestedMode
        ? \App\Enums\LiveLessonMode::normalize($requestedMode)
        : ($session?->active_mode ?? $classroom->classroom_mode ?? 'whiteboard');
    $codeWorkspace = data_get($session?->metadata, 'code_workspace', []);
    $currentCode = data_get($session?->metadata, 'code_draft', '');
    $currentLanguage = data_get($session?->metadata, 'code_language', 'plaintext');
    $codeFiles = collect(data_get($codeWorkspace, 'files', [
        'html' => [
            'filename' => 'index.html',
            'language' => 'html',
            'content' => data_get($session?->metadata, 'code_draft', ''),
            'label' => 'HTML',
        ],
        'css' => [
            'filename' => 'styles.css',
            'language' => 'css',
            'content' => '',
            'label' => 'CSS',
        ],
        'js' => [
            'filename' => 'script.js',
            'language' => 'javascript',
            'content' => '',
            'label' => 'JavaScript',
        ],
    ]));
    $codeActiveFileKey = data_get($codeWorkspace, 'active_file_key', data_get($session?->metadata, 'code_active_file_key', 'html'));
    $textPadContent = (string) data_get($session, 'textpad_snapshot', '');
    $textPadComments = collect(data_get($session?->metadata, 'textpad_comments', []))->values();
    $sessionNotes = data_get($session?->metadata, 'session_notes', '');
    $sessionResources = collect(data_get($session?->metadata, 'resources', []));
    $whiteboardState = data_get($session?->metadata, 'whiteboard_state', [
        'active_page' => 'page-1',
        'zoom' => 100,
        'viewport' => ['x' => 0, 'y' => 0],
        'settings' => [
            'board_locked' => false,
            'follow_teacher_page' => true,
            'follow_teacher_viewport' => false,
            'presentation_mode' => false,
            'allow_learner_page_switch' => true,
            'allow_learner_page_create' => false,
            'allow_learner_object_move' => true,
            'allow_learner_draw' => true,
            'allow_learner_erase' => false,
            'allow_learner_comments' => true,
            'allow_learner_images' => false,
        ],
        'pages' => [
            [
                'key' => 'page-1',
                'title' => 'Page 1',
                'page_number' => 1,
                'background_type' => 'plain_white',
                'background_value' => '#ffffff',
                'thumbnail_path' => null,
                'is_locked' => false,
                'settings' => [],
                'sort_order' => 0,
            ],
        ],
    ]);
@endphp

@push('head')
<meta name="classroom-session-id" content="{{ $session?->id }}">
<meta name="classroom-user-id" content="{{ Auth::id() }}">
<meta name="classroom-is-teacher" content="{{ $isTeacher ? '1' : '0' }}">
<meta name="classroom-permissions" content='@json($myParticipant?->permissions ?? $roomPermissions)'>
<meta name="classroom-room-permissions" content='@json($roomPermissions)'>
<meta name="classroom-mode" content="{{ $currentMode }}">
<meta name="classroom-room-code" content="{{ $classroom->room_code }}">
<meta name="classroom-join-link" content="{{ $shareLink }}">
<meta name="classroom-code-draft" content='@json($currentCode)'>
<meta name="classroom-code-language" content="{{ $currentLanguage }}">
<meta name="classroom-code-workspace" content='@json($codeWorkspace)'>
<meta name="classroom-code-files" content='@json($codeFiles->all())'>
<meta name="classroom-code-active-file" content="{{ $codeActiveFileKey }}">
<meta name="classroom-textpad-content" content='@json($textPadContent)'>
<meta name="classroom-textpad-comments" content='@json($textPadComments->all())'>
<meta name="classroom-session-notes" content='@json($sessionNotes)'>
<meta name="classroom-session-resources" content='@json($sessionResources->values()->all())'>
<meta name="classroom-whiteboard-state" content='@json($whiteboardState)'>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@include('classrooms.partials.unified-workspace')
@endsection
