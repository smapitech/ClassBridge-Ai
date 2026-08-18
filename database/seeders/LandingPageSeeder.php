<?php

namespace Database\Seeders;

use App\Models\LandingPageAudience;
use App\Models\LandingPageFeature;
use App\Models\LandingPagePricingItem;
use App\Models\LandingPageSection;
use App\Models\LandingPageSlide;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'label' => 'Live teaching workspace',
                'headline' => 'Teach online like you are sitting beside the child.',
                'subtitle' => 'Whiteboard, code editor, text pad, pointer, and chat in one safe classroom.',
                'primary_button_text' => 'Try demo classroom',
                'primary_button_url' => '/live-classroom-demo',
                'secondary_button_text' => 'Request demo',
                'secondary_button_url' => '#request-demo',
                'background_style' => 'teal',
                'sort_order' => 1,
            ],
            [
                'label' => 'Private tutors',
                'headline' => 'Run a real teaching business without a school setup first.',
                'subtitle' => 'Add students, link parents, schedule live sessions, and keep everything inside the classroom.',
                'primary_button_text' => 'Start free trial',
                'primary_button_url' => '/register',
                'secondary_button_text' => 'See how it works',
                'secondary_button_url' => '#how-it-works',
                'background_style' => 'sky',
                'sort_order' => 2,
            ],
            [
                'label' => 'Schools and academies',
                'headline' => 'One workspace for live lessons, homework, and progress reports.',
                'subtitle' => 'Built for schools, tutoring centers, coding academies, homeschool tutors, and after-school teachers.',
                'primary_button_text' => 'View pricing',
                'primary_button_url' => '#pricing',
                'secondary_button_text' => 'Login',
                'secondary_button_url' => '/login',
                'background_style' => 'slate',
                'sort_order' => 3,
            ],
        ];

        foreach ($slides as $slide) {
            LandingPageSlide::updateOrCreate(
                ['headline' => $slide['headline']],
                $slide
            );
        }

        $features = [
            ['title' => 'Live Interactive Classroom', 'description' => 'The protected room where teaching happens in real time.', 'icon' => 'classroom', 'feature_group' => 'core', 'sort_order' => 1],
            ['title' => 'Shared Whiteboard', 'description' => 'Draw, explain, and solve together on the same board.', 'icon' => 'whiteboard', 'feature_group' => 'core', 'sort_order' => 2],
            ['title' => 'Shared Coding Studio', 'description' => 'Teach code with tabs, syntax highlighting, and preview.', 'icon' => 'code', 'feature_group' => 'core', 'sort_order' => 3],
            ['title' => 'Teacher and Student Pointer', 'description' => 'Both cursors stay visible while the lesson moves.', 'icon' => 'pointer', 'feature_group' => 'core', 'sort_order' => 4],
            ['title' => 'Shared Text Pad', 'description' => 'Write, correct, and review text side by side.', 'icon' => 'text', 'feature_group' => 'core', 'sort_order' => 5],
            ['title' => 'AI Lesson Builder', 'description' => 'Draft lessons, examples, and practice tasks faster.', 'icon' => 'ai', 'feature_group' => 'ai', 'sort_order' => 6],
            ['title' => 'AI Curriculum Generator', 'description' => 'Plan a learning path across topics and weeks.', 'icon' => 'curriculum', 'feature_group' => 'ai', 'sort_order' => 7],
            ['title' => 'Homework and Quiz Tools', 'description' => 'Set work, check answers, and keep the flow simple.', 'icon' => 'tasks', 'feature_group' => 'learning', 'sort_order' => 8],
            ['title' => 'Parent Progress Portal', 'description' => 'Share progress without exposing the student device.', 'icon' => 'parent', 'feature_group' => 'family', 'sort_order' => 9],
            ['title' => 'Smart Lesson Replay', 'description' => 'Review what happened in the room later.', 'icon' => 'replay', 'feature_group' => 'learning', 'sort_order' => 10],
            ['title' => 'Certificates', 'description' => 'Mark completion and keep motivation visible.', 'icon' => 'certificate', 'feature_group' => 'learning', 'sort_order' => 11],
            ['title' => 'Tutor and School Accounts', 'description' => 'Supports private tutors, teams, centers, and schools.', 'icon' => 'accounts', 'feature_group' => 'business', 'sort_order' => 12],
        ];

        foreach ($features as $feature) {
            LandingPageFeature::updateOrCreate(
                ['title' => $feature['title']],
                $feature
            );
        }

        $audiences = [
            ['title' => 'Schools', 'description' => 'Manage teachers, classes, learners, parents, live sessions, and reports.', 'icon' => 'school', 'sort_order' => 1],
            ['title' => 'Private tutors', 'description' => 'Teach one-to-one and run a clean online lesson business.', 'icon' => 'tutor', 'sort_order' => 2],
            ['title' => 'Online tutors', 'description' => 'Guide learners in real time with no remote desktop access.', 'icon' => 'online', 'sort_order' => 3],
            ['title' => 'Coding academies', 'description' => 'Use the coding studio to teach projects and concepts live.', 'icon' => 'code', 'sort_order' => 4],
            ['title' => 'Homeschool teachers', 'description' => 'Support learning in a safe room for one child or a family.', 'icon' => 'home', 'sort_order' => 5],
            ['title' => 'After-school lesson teachers', 'description' => 'Offer focused support, homework help, and progress updates.', 'icon' => 'after-school', 'sort_order' => 6],
        ];

        foreach ($audiences as $audience) {
            LandingPageAudience::updateOrCreate(
                ['title' => $audience['title']],
                $audience
            );
        }

        $pricingItems = [
            [
                'name' => 'Private Tutor',
                'description' => 'For one tutor running a private online teaching business.',
                'price_text' => 'From $19/mo',
                'features' => ['Live sessions', 'Add students', 'Homework', 'Reports'],
                'button_text' => 'Start free',
                'button_url' => '/register',
                'sort_order' => 1,
            ],
            [
                'name' => 'Small Tutoring Team',
                'description' => 'For tutors with a small teaching team and shared learners.',
                'price_text' => 'From $79/mo',
                'features' => ['Shared classroom tools', 'Parent reporting', 'Lesson replay', 'AI support'],
                'button_text' => 'Request demo',
                'button_url' => '#request-demo',
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'School / Academy',
                'description' => 'For schools, centers, and academies with more structure.',
                'price_text' => 'From $199/mo',
                'features' => ['Teachers and classes', 'Students and parents', 'Subscriptions', 'Reports'],
                'button_text' => 'Talk to sales',
                'button_url' => '#request-demo',
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'For larger organizations that need a custom rollout.',
                'price_text' => 'Custom',
                'features' => ['White label', 'Training support', 'Advanced governance', 'Custom setup'],
                'button_text' => 'Request demo',
                'button_url' => '#request-demo',
                'sort_order' => 4,
            ],
        ];

        foreach ($pricingItems as $pricingItem) {
            LandingPagePricingItem::updateOrCreate(
                ['name' => $pricingItem['name']],
                $pricingItem
            );
        }

        $sections = [
            [
                'section_key' => 'site_header',
                'title' => 'ClassBridge AI',
                'subtitle' => 'Safe live teaching workspace',
                'button_text' => 'Start Free Trial',
                'button_url' => '/register',
                'secondary_button_text' => 'Login',
                'secondary_button_url' => '/login',
                'sort_order' => 1,
                'settings' => [
                    'nav_links' => [
                        ['label' => 'Live classroom', 'url' => '#demo'],
                        ['label' => 'Who it helps', 'url' => '#for-who'],
                        ['label' => 'Safety', 'url' => '#safety'],
                        ['label' => 'Pricing', 'url' => '#pricing'],
                        ['label' => 'Request demo', 'url' => '#request-demo'],
                    ],
                ],
            ],
            [
                'section_key' => 'hero',
                'title' => 'Teach online like you are sitting beside the child -',
                'subtitle' => 'ClassBridge AI gives schools, tutors, and online teachers a protected live classroom where teacher and student can write, draw, code, point, explain, and learn together in real time.',
                'content' => 'without remote access risk.',
                'button_text' => 'Start Free Trial',
                'button_url' => '/register',
                'secondary_button_text' => 'Try Demo Classroom',
                'secondary_button_url' => '/live-classroom-demo',
                'sort_order' => 2,
                'settings' => [
                    'eyebrow' => 'Live interactive learning',
                    'chips' => ['Schools', 'Private tutors', 'Online tutors', 'Coding academies', 'Homeschool teachers', 'After-school teachers'],
                    'room_code' => 'CB-2147',
                    'mode_label' => 'Coding Mode',
                    'status_label' => 'Protected room',
                    'badge_one_text' => '3 learners online',
                    'badge_two_text' => 'Whiteboard active',
                    'code_lines' => [
                        '// Teacher guides the learner live',
                        "room = 'CB-2147'",
                        "mode = 'Coding Mode'",
                        "pointer = visible('teacher')",
                        "permissions = 'chat, draw, type'",
                        '',
                        '// Student sees the change instantly',
                        "console.log('I can follow this lesson.')",
                    ],
                ],
            ],
            [
                'section_key' => 'demo_preview',
                'title' => 'A full learning environment in your browser',
                'subtitle' => 'Watch how a teacher leads a live lesson - sharing code, drawing on the whiteboard, and answering questions in real time.',
                'button_text' => 'Try demo classroom',
                'button_url' => '/live-classroom-demo',
                'sort_order' => 3,
                'settings' => [
                    'label' => 'See it in action',
                    'video_label' => 'Platform walkthrough - 2 min',
                ],
            ],
            [
                'section_key' => 'features',
                'title' => 'Built for serious teaching',
                'subtitle' => 'Every tool a tutor needs to deliver a live session - no tab switching, no plugins.',
                'sort_order' => 4,
                'settings' => ['label' => 'Everything you need'],
            ],
            [
                'section_key' => 'how_it_works',
                'title' => 'From sign-up to first lesson in minutes',
                'subtitle' => 'A simple flow that parents understand quickly.',
                'content' => 'Create a session, share the link, work live in the room, then send progress notes home.',
                'sort_order' => 5,
                'settings' => [
                    'label' => 'How it works',
                    'steps' => [
                        ['title' => 'Teacher creates the lesson', 'copy' => 'Open one protected room and choose the mode for the session.'],
                        ['title' => 'Student joins by code or link', 'copy' => 'The learner enters the same classroom with no remote desktop risk.'],
                        ['title' => 'Both work in the same classroom', 'copy' => 'Teacher and student write, draw, code, and explain together.'],
                        ['title' => 'Teacher guides and parents can follow', 'copy' => 'Use pointers, notes, chat, and reports without leaving the lesson.'],
                    ],
                ],
            ],
            [
                'section_key' => 'social_proof',
                'title' => 'Numbers that make the room easy to trust',
                'subtitle' => 'Parents, tutors, and school owners see exactly what the platform protects and supports.',
                'sort_order' => 6,
                'settings' => [
                    'label' => 'Trust matters',
                    'stats' => [
                        ['value' => '1', 'label' => 'Protected room per lesson'],
                        ['value' => '5', 'label' => 'Teaching modes'],
                        ['value' => '2', 'label' => 'Visible pointers'],
                        ['value' => '0', 'label' => 'Remote desktop access'],
                    ],
                    'testimonials' => [
                        ['quote' => 'It feels calm and personal. I can draw, type, and explain without leaving the lesson.', 'name' => 'Private tutor', 'role' => 'One-to-one teaching'],
                        ['quote' => 'Parents understand what happened in class without needing a long meeting.', 'name' => 'School owner', 'role' => 'Organization workspace'],
                        ['quote' => 'My child learns in one place, and I still trust the setup.', 'name' => 'Parent', 'role' => 'Progress and reports'],
                    ],
                ],
            ],
            [
                'section_key' => 'classroom_preview',
                'title' => 'Live classroom preview',
                'subtitle' => 'A calm, practical room where the work is visible.',
                'content' => 'Teacher and student stay inside one protected workspace while they draw, code, type, ask, and correct together.',
                'button_text' => 'Try demo classroom',
                'button_url' => '/live-classroom-demo',
                'sort_order' => 7,
                'settings' => ['accent' => 'teal'],
            ],
            [
                'section_key' => 'who_it_helps',
                'title' => 'Who it helps',
                'subtitle' => 'Built for both schools and private teaching businesses.',
                'content' => 'Use one platform for tutoring, homeschool support, coding lessons, and school sessions.',
                'sort_order' => 8,
            ],
            [
                'section_key' => 'safety',
                'title' => 'Safety first',
                'subtitle' => 'No remote desktop access, ever.',
                'content' => 'Teachers guide the lesson only inside ClassBridge AI. They cannot open the student computer, files, browser history, or other apps.',
                'sort_order' => 9,
            ],
            [
                'section_key' => 'ai_teacher_helper',
                'title' => 'AI teacher helper',
                'subtitle' => 'Support for lesson planning, not a replacement for the teacher.',
                'content' => 'Generate lesson ideas, corrections, simpler explanations, and practice tasks without losing the human side of teaching.',
                'button_text' => 'See AI tools',
                'button_url' => '#pricing',
                'sort_order' => 10,
            ],
            [
                'section_key' => 'pricing_preview',
                'title' => 'Pricing preview',
                'subtitle' => 'A plan for every size of teaching business.',
                'content' => 'Start small, then grow into a team, center, academy, or school setup.',
                'sort_order' => 11,
                'settings' => ['label' => 'Pricing preview'],
            ],
            [
                'section_key' => 'request_demo',
                'title' => 'Request a demo',
                'subtitle' => 'Show us how you teach and we will walk you through the workspace.',
                'content' => 'We will tailor the preview for schools, tutors, homeschool teachers, or coding programs.',
                'button_text' => 'Request demo',
                'button_url' => '#request-demo',
                'sort_order' => 12,
                'settings' => [
                    'label' => 'Request demo',
                    'badge_text' => 'Protected workspace',
                    'form_title' => 'Tell us about your teaching setup.',
                    'form_subtitle' => 'We will follow up with a demo for your school, tutoring center, private teaching business, homeschool setup, or coding academy.',
                ],
            ],
            [
                'section_key' => 'site_footer',
                'title' => 'ClassBridge AI',
                'subtitle' => 'Safe live teaching workspace.',
                'content' => 'Teach online like you are beside the learner, without remote access risk.',
                'sort_order' => 13,
                'settings' => [
                    'links' => [
                        ['label' => 'Features', 'url' => '#features'],
                        ['label' => 'How it works', 'url' => '#how'],
                        ['label' => 'Pricing', 'url' => '#pricing'],
                        ['label' => 'Request demo', 'url' => '#request-demo'],
                    ],
                ],
            ],
        ];

        foreach ($sections as $section) {
            LandingPageSection::updateOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }
    }
}
