# ClassBridge AI One-Page Architecture Blueprint

## Product Rule
ClassBridge AI has one main job:

Teacher and student work together in one protected live classroom.

Everything else should support that one experience.

## Canonical Flow
1. User logs in.
2. User sees one role-based dashboard.
3. Dashboard shows one clear primary action.
4. Primary action opens the live classroom.
5. Teacher switches modes without creating a new session.
6. Room code, chat, participants, pointers, and permissions stay the same.

## Roles And Main CTA
- Super Admin -> Manage organizations
- Organization Owner/Admin -> Start Live Classroom
- Teacher/Tutor -> Start Live Classroom
- Student/Learner -> Join Live Class
- Parent -> View Child Progress

## Dashboard Rule
Every dashboard should show:
- page title
- main CTA
- 4 to 6 key stats
- quick actions
- recent activity
- empty states

## Sidebar Rule
- Desktop sidebar stays visible by default
- Mobile sidebar opens with a hamburger button
- Active menu item is highlighted
- One primary CTA per role
- Keep labels short and clear

## Unified Live Classroom
One room should contain:
- Whiteboard
- Code editor
- Text pad
- Chat
- Teacher pointer
- Student pointer
- Participant list
- Teacher controls
- Student permissions
- Session status
- Room code or join link

## Classroom Modes
Use only these modes:
- Whiteboard
- Coding
- Text / English
- Math
- Presentation

## Keep These Data Areas
Preserve the current structure for now:
- `school_id` ownership
- schools, users, classes, subjects, teachers, students, parents
- classroom sessions
- coding sessions
- homework, quizzes, reports, lesson replay, AI usage

## Main Conflicts To Simplify Later
- Classroom create/join flow vs coding join flow
- Separate room codes for classroom and coding
- Whiteboard, Coding Studio, and Text Pad shown as separate primary modules
- Duplicate landing page content
- Too many sidebar links leading to the same lesson room

## Recommended Implementation Order
1. Make Live Interactive Classroom the canonical teaching entry point.
2. Point dashboards and sidebar actions to that one room.
3. Redirect old classroom and coding links to the canonical room.
4. Merge realtime state, permissions, and participant logic.
5. Only then consider database consolidation.

## Public Page Order
Hero -> live classroom preview -> how it works -> who it helps -> safety -> AI helper -> pricing -> demo request -> footer

## Private Tutor Flow
1. Set tutor profile
2. Add first student
3. Link parent optional
4. Create first live session
5. Open interactive classroom
6. Generate first AI lesson

## School Flow
1. Complete organization profile
2. Add teachers
3. Add classes
4. Add students
5. Link parents
6. Create live classroom
7. Generate AI lesson
8. Publish homework

## UI Tone
Keep the app:
- clean
- warm
- premium
- simple
- human
- easy to scan

Avoid:
- long paragraphs
- repetitive cards
- generic SaaS gradients
- school-only language everywhere
- empty dashboards

## Copyable Short Version
```text
ClassBridge AI = public site + role dashboards + protected live classroom.

Primary rule:
Teacher and student work together in one workspace.

Roles:
Super Admin, Organization Owner/Admin, Teacher/Tutor, Student/Learner, Parent.

Main action by role:
Super Admin -> Manage organizations
Organization Owner/Admin -> Start Live Classroom
Teacher/Tutor -> Start Live Classroom
Student/Learner -> Join Live Class
Parent -> View Child Progress

Main classroom tools:
Whiteboard, Code Editor, Text Pad, Chat, Pointers, Participants, Modes, Permissions.
```

