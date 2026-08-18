@php
    $features = collect($features ?? []);
    $audiences = collect($audiences ?? []);
    $pricingItems = collect($pricingItems ?? []);
    $sections = collect($sections ?? []);

    $sectionValue = function (string $key, string $field, $default = null) use ($sections) {
        return data_get($sections->get($key), $field, $default);
    };

    $resolveUrl = function (?string $url): string {
        $url = trim((string) $url);

        if ($url === '') {
            return route('register');
        }

        return $url;
    };

    $sectionSettings = function (string $key, array $default = []) use ($sections) {
        $settings = data_get($sections->get($key), 'settings', []);

        return is_array($settings) ? array_replace_recursive($default, $settings) : $default;
    };

    $highlightCodeLine = function (?string $line): string {
        $line = (string) $line;

        if (trim($line) === '') {
            return '&nbsp;';
        }

        $escaped = e($line);

        if (preg_match('/^\s*\/\//', $line)) {
            return '<span class="cm">' . $escaped . '</span>';
        }

        $strings = [];

        $escaped = preg_replace_callback('/(&#039;[^&#039;]*&#039;|&quot;[^&quot;]*&quot;)/', function ($matches) use (&$strings) {
            $token = '__STR_' . count($strings) . '__';
            $strings[$token] = '<span class="str">' . $matches[1] . '</span>';

            return $token;
        }, $escaped);

        $escaped = preg_replace('/\b(console\.log|function|return|const|let|var|if|else|true|false|null)\b/', '<span class="kw">$1</span>', $escaped);
        $escaped = preg_replace('/\b(room|mode|pointer|permissions|teacher|student|visible)\b/', '<span class="fn">$1</span>', $escaped);
        $escaped = preg_replace('/\b(\d+)\b/', '<span class="num">$1</span>', $escaped);

        return strtr($escaped, $strings);
    };

    if ($audiences->isEmpty()) {
        $audiences = collect([
            ['title' => 'Schools', 'description' => 'Keep teachers, learners, parents, live sessions, and reports in one protected workspace.'],
            ['title' => 'Private tutors', 'description' => 'Run one-to-one lessons without creating a full school structure first.'],
            ['title' => 'Online tutors', 'description' => 'Teach remotely with live pointers, chat, and guided corrections.'],
            ['title' => 'Coding academies', 'description' => 'Teach programming with a shared editor and live preview.'],
            ['title' => 'Homeschool teachers', 'description' => 'Support one child or a family with a private online classroom.'],
            ['title' => 'After-school teachers', 'description' => 'Offer focused support in a calm workspace after class.'],
        ]);
    }

    if ($features->isEmpty()) {
        $features = collect([
            ['title' => 'Live Interactive Classroom', 'description' => 'The main room where teacher and student work together in real time.'],
            ['title' => 'Shared Whiteboard', 'description' => 'Draw, annotate, and solve together on the same canvas.'],
            ['title' => 'Shared Coding Studio', 'description' => 'Code side by side with live preview and visible edits.'],
            ['title' => 'Teacher and Student Pointer', 'description' => 'Both cursors remain visible in the protected workspace.'],
            ['title' => 'Shared Text Pad', 'description' => 'Write, correct, and review text live without leaving class.'],
            ['title' => 'AI Lesson Helper', 'description' => 'Generate lesson support without replacing the teacher.'],
        ]);
    }

    if ($pricingItems->isEmpty()) {
        $pricingItems = collect([
            ['name' => 'Private Tutor', 'description' => 'For one tutor running a private teaching business.', 'price_text' => 'From $19/mo', 'features' => ['Live sessions', 'Students', 'Homework', 'Reports']],
            ['name' => 'Small Tutoring Team', 'description' => 'For a small team that needs shared tools.', 'price_text' => 'From $79/mo', 'features' => ['Shared classroom tools', 'Parent reporting', 'Lesson replay', 'AI support'], 'is_popular' => true],
            ['name' => 'School / Academy', 'description' => 'For schools, centers, and academies.', 'price_text' => 'From $199/mo', 'features' => ['Teachers and classes', 'Students and parents', 'Subscriptions', 'Reports']],
            ['name' => 'Enterprise', 'description' => 'For larger organizations that need custom rollout.', 'price_text' => 'Custom', 'features' => ['White label', 'Training support', 'Governance', 'Custom setup']],
        ]);
    }

    $headerSettings = $sectionSettings('site_header', [
        'nav_links' => [
            ['label' => 'Live classroom', 'url' => '#demo'],
            ['label' => 'How it works', 'url' => '#how'],
            ['label' => 'Pricing', 'url' => '#pricing'],
            ['label' => 'Request demo', 'url' => '#request-demo'],
        ],
    ]);

    $heroSettings = $sectionSettings('hero', [
        'eyebrow' => 'Live interactive learning',
        'chips' => [],
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
    ]);

    $demoPreviewSettings = $sectionSettings('demo_preview', [
        'label' => 'See it in action',
        'video_label' => 'Platform walkthrough - 2 min',
    ]);

    $howSettings = $sectionSettings('how_it_works', [
        'label' => 'How it works',
        'steps' => [
            ['title' => 'Teacher creates the lesson', 'copy' => 'Open one protected room and choose the mode for the session.'],
            ['title' => 'Student joins by code or link', 'copy' => 'The learner enters the same classroom with no remote desktop risk.'],
            ['title' => 'Both work in the same classroom', 'copy' => 'Teacher and student write, draw, code, and explain together.'],
            ['title' => 'Teacher guides and parents can follow', 'copy' => 'Use pointers, notes, chat, and reports without leaving the lesson.'],
        ],
    ]);

    $socialSettings = $sectionSettings('social_proof', [
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
    ]);

    $requestDemoSettings = $sectionSettings('request_demo', [
        'label' => 'Request demo',
        'badge_text' => 'Protected workspace',
        'form_title' => 'Tell us about your teaching setup.',
        'form_subtitle' => 'We will follow up with a demo for your school, tutoring center, private teaching business, homeschool setup, or coding academy.',
    ]);

    $footerSettings = $sectionSettings('site_footer', [
        'links' => [
            ['label' => 'Features', 'url' => '#features'],
            ['label' => 'How it works', 'url' => '#how'],
            ['label' => 'Pricing', 'url' => '#pricing'],
            ['label' => 'Request demo', 'url' => '#request-demo'],
        ],
    ]);

    $pricingPreviewSettings = $sectionSettings('pricing_preview', [
        'label' => 'Pricing preview',
    ]);

    $heroTitle = $sectionValue('hero', 'title', 'Teach online like you are sitting beside the child -');
    $heroAccent = $sectionValue('hero', 'content', 'without remote access risk.');
    $heroSubtitle = $sectionValue('hero', 'subtitle', 'ClassBridge AI gives schools, tutors, and online teachers a protected live classroom where teacher and student can write, draw, code, point, explain, and learn together in real time.');

    $workflow = collect(data_get($howSettings, 'steps', []));
    $stats = collect(data_get($socialSettings, 'stats', []));
    $testimonials = collect(data_get($socialSettings, 'testimonials', []));
    $footerLinks = collect(data_get($footerSettings, 'links', []));
    $heroCodeLines = collect(data_get($heroSettings, 'code_lines', []));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>ClassBridge AI - Live teaching without remote access risk</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --navy: #0A0F1E;
    --navy-card: #111827;
    --navy-mid: #1A2744;
    --blue: #3B82F6;
    --blue-dim: #1E3A5F;
    --mint: #6EE7B7;
    --mint-dim: #065F46;
    --white: #F8FAFC;
    --slate: #94A3B8;
    --border: rgba(255,255,255,0.07);
  }

  html { scroll-behavior: smooth; }

  body {
    background: var(--navy);
    color: var(--white);
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    line-height: 1.7;
    overflow-x: hidden;
  }

  a { color: inherit; }

  nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5vw;
    height: 68px;
    background: rgba(10,15,30,0.85);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
  }

  .logo {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--white);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .logo-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--mint);
    display: inline-block;
  }

  .nav-links {
    display: flex;
    align-items: center;
    gap: 2rem;
    list-style: none;
  }

  .nav-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .nav-links a {
    color: var(--slate);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color .2s;
  }

  .nav-links a:hover { color: var(--white); }

  .btn-login {
    background: transparent;
    border: 1.5px solid rgba(110,231,183,0.5);
    color: var(--mint);
    padding: 0.45rem 1.3rem;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s, border-color .2s;
    text-decoration: none;
    display: inline-block;
  }

  .btn-login:hover {
    background: rgba(110,231,183,0.1);
    border-color: var(--mint);
  }

  .btn-primary {
    background: var(--blue);
    color: #fff;
    border: none;
    padding: 0.8rem 1.8rem;
    border-radius: 10px;
    font-family: 'Inter', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
    text-decoration: none;
    display: inline-block;
  }

  .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

  .btn-ghost {
    background: transparent;
    color: var(--white);
    border: 1.5px solid var(--border);
    padding: 0.8rem 1.8rem;
    border-radius: 10px;
    font-family: 'Inter', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-ghost:hover {
    border-color: rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.04);
  }

  .hero {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 3rem;
    padding: 100px 5vw 60px;
    max-width: 1200px;
    margin: 0 auto;
  }

  .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.78rem;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--mint);
    background: rgba(110,231,183,0.08);
    border: 1px solid rgba(110,231,183,0.2);
    padding: 5px 14px;
    border-radius: 100px;
    margin-bottom: 1.5rem;
  }

  .hero-eyebrow span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--mint);
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%,100% { opacity: 1; }
    50% { opacity: 0.3; }
  }

  .hero-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: clamp(2.4rem, 4.5vw, 3.7rem);
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 1.25rem;
  }

  .hero-title .accent { color: var(--mint); }
  .hero-title .accent-blue { color: var(--blue); }

  .hero-sub {
    font-size: 1.05rem;
    color: var(--slate);
    max-width: 520px;
    margin-bottom: 2.2rem;
    line-height: 1.75;
  }

  .hero-ctas {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .hero-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.4rem;
  }

  .hero-chips span,
  .mini-pill {
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.04);
    color: var(--slate);
    border-radius: 999px;
    padding: 0.48rem 0.9rem;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .hero-visual { position: relative; }

  .code-card {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.8rem;
    box-shadow: 0 32px 64px rgba(0,0,0,0.5);
  }

  .code-topbar {
    background: rgba(255,255,255,0.04);
    border-bottom: 1px solid var(--border);
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .dot { width: 10px; height: 10px; border-radius: 50%; }
  .dot-r { background: #FF5F56; }
  .dot-y { background: #FFBD2E; }
  .dot-g { background: #27C93F; }

  .tab-label {
    margin-left: auto;
    color: var(--slate);
    font-size: 0.75rem;
  }

  .code-body {
    padding: 1.2rem 1.5rem;
    line-height: 1.75;
  }

  .code-line {
    display: flex;
    align-items: flex-start;
    gap: 0;
    white-space: pre;
  }

  .code-text {
    display: inline;
    white-space: pre;
  }

  .ln { color: #4B5563; margin-right: 1.2rem; user-select: none; }
  .kw { color: #C084FC; }
  .fn { color: #60A5FA; }
  .str { color: #86EFAC; }
  .cm { color: #4B5563; font-style: italic; }
  .num { color: #F9A8D4; }

  .cursor {
    display: inline-block;
    width: 2px;
    height: 14px;
    background: var(--mint);
    vertical-align: middle;
    animation: blink 1s step-end infinite;
  }

  @keyframes blink {
    0%,100% { opacity: 1; }
    50% { opacity: 0; }
  }

  .badge-float {
    position: absolute;
    background: var(--navy-mid);
    border: 1px solid rgba(59,130,246,0.35);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.78rem;
    font-family: 'Inter', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    white-space: nowrap;
  }

  .badge-float .bicon { font-size: 1rem; }
  .badge-1 { bottom: -20px; left: -30px; }
  .badge-2 { top: -18px; right: -18px; }
  .badge-online { width: 8px; height: 8px; border-radius: 50%; background: #22C55E; }

  .section-label {
    font-size: 0.78rem;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--mint);
    margin-bottom: 0.75rem;
  }

  .section-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .section-sub {
    color: var(--slate);
    max-width: 560px;
    margin: 0 auto 2.5rem;
  }

  .video-section {
    max-width: 900px;
    margin: 0 auto;
    padding: 60px 5vw 80px;
    text-align: center;
  }

  .video-wrapper {
    position: relative;
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    aspect-ratio: 16/9;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }

  .video-thumb {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #0A1628 0%, #1A2744 50%, #0D1B2A 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 1.5rem;
  }

  .video-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 10px;
    width: 80%;
    opacity: 0.3;
  }

  .vg-block {
    background: var(--navy-mid);
    border: 1px solid var(--border);
    border-radius: 8px;
    height: 70px;
  }

  .play-btn {
    position: absolute;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: var(--blue);
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 0 0 16px rgba(59,130,246,0.12);
  }

  .play-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 0 0 20px rgba(59,130,246,0.16);
  }

  .play-btn svg { width: 28px; height: 28px; fill: #fff; margin-left: 5px; }

  .video-label {
    position: absolute;
    bottom: 20px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 0.85rem;
    color: var(--slate);
  }

  .features {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 5vw 80px;
  }

  .features-header {
    text-align: center;
    margin-bottom: 3.5rem;
  }

  .features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }

  .feat-card {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.8rem 1.6rem;
    transition: border-color .25s, transform .2s;
  }

  .feat-card:hover {
    border-color: rgba(59,130,246,0.35);
    transform: translateY(-3px);
  }

  .feat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: rgba(59,130,246,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.1rem;
    font-size: 1.3rem;
  }

  .feat-icon.mint { background: rgba(110,231,183,0.1); }

  .feat-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  .feat-desc {
    color: var(--slate);
    font-size: 0.9rem;
    line-height: 1.65;
  }

  .how {
    background: linear-gradient(180deg, transparent, rgba(26,39,68,0.4) 50%, transparent);
    padding: 80px 5vw;
  }

  .how-inner {
    max-width: 900px;
    margin: 0 auto;
  }

  .how-header {
    text-align: center;
    margin-bottom: 3.5rem;
  }

  .steps {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .step {
    display: grid;
    grid-template-columns: 56px 1fr;
    gap: 1.5rem;
    align-items: flex-start;
  }

  .step-num {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    border: 1.5px solid rgba(110,231,183,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--mint);
    flex-shrink: 0;
    background: rgba(110,231,183,0.06);
  }

  .step-content h3 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 0.3rem;
  }

  .step-content p {
    color: var(--slate);
    font-size: 0.9rem;
  }

  .social {
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 5vw 80px;
  }

  .social-header {
    text-align: center;
    margin-bottom: 3rem;
  }

  .stats-row {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 1rem;
    margin-bottom: 3.5rem;
  }

  .stat-card {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.4rem;
    text-align: center;
  }

  .stat-num {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 0.2rem;
  }

  .stat-num .unit { color: var(--mint); }
  .stat-label { color: var(--slate); font-size: 0.85rem; }

  .testimonials {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 1.25rem;
  }

  .testi {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.4rem;
  }

  .testi-text {
    font-size: 0.9rem;
    color: var(--slate);
    margin-bottom: 1rem;
    line-height: 1.7;
    font-style: italic;
  }

  .testi-author {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    font-weight: 600;
    flex-shrink: 0;
  }

  .av-blue { background: rgba(59,130,246,0.2); color: #60A5FA; }
  .av-mint { background: rgba(110,231,183,0.2); color: #6EE7B7; }
  .av-purple { background: rgba(167,139,250,0.2); color: #A78BFA; }

  .testi-name { font-size: 0.85rem; font-weight: 500; }
  .testi-role { font-size: 0.75rem; color: var(--slate); }

  .pricing {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 5vw 80px;
  }

  .pricing-header {
    text-align: center;
    margin-bottom: 3rem;
  }

  .pricing-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 1.25rem;
  }

  .plan {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 1.5rem;
    transition: transform .2s, border-color .2s;
  }

  .plan:hover {
    transform: translateY(-3px);
    border-color: rgba(110,231,183,0.35);
  }

  .plan.featured {
    border-color: rgba(59,130,246,0.5);
    box-shadow: 0 20px 40px rgba(59,130,246,0.08);
  }

  .plan-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .plan-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.05rem;
    font-weight: 600;
  }

  .plan-price {
    margin-top: 0.9rem;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.9rem;
    font-weight: 700;
  }

  .plan-desc {
    color: var(--slate);
    margin-top: 0.35rem;
    font-size: 0.9rem;
  }

  .plan-list {
    margin-top: 1rem;
    list-style: none;
    display: grid;
    gap: 0.55rem;
  }

  .plan-list li {
    display: flex;
    gap: 0.5rem;
    color: #CBD5E1;
    font-size: 0.88rem;
  }

  .plan-list li::before {
    content: '•';
    color: var(--mint);
  }

  .cta-section {
    text-align: center;
    padding: 80px 5vw 100px;
  }

  .cta-inner {
    max-width: 1160px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    align-items: stretch;
  }

  .cta-left {
    position: relative;
    overflow: hidden;
    background: var(--navy-card);
    border: 1px solid rgba(59,130,246,0.25);
    border-radius: 24px;
    padding: 3rem 2rem;
    text-align: left;
  }

  .cta-glow {
    position: absolute;
    top: -80px;
    left: 50%;
    transform: translateX(-50%);
    width: 300px;
    height: 200px;
    background: radial-gradient(ellipse, rgba(59,130,246,0.18) 0%, transparent 70%);
    pointer-events: none;
  }

  .cta-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
  }

  .cta-sub {
    color: var(--slate);
    margin-bottom: 1.8rem;
    max-width: 34rem;
  }

  .cta-btns {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .cta-note {
    margin-top: 1.5rem;
    color: #CBD5E1;
    max-width: 34rem;
    font-size: 0.92rem;
  }

  .cta-form {
    background: #fff;
    border: 1px solid rgba(15,23,42,0.08);
    border-radius: 24px;
    padding: 2rem;
    text-align: left;
    color: #0f172a;
    box-shadow: 0 18px 40px rgba(15,23,42,0.12);
  }

  .cta-form h3 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.55rem;
    margin-bottom: 0.5rem;
  }

  .cta-form p {
    color: #475569;
    font-size: 0.95rem;
    margin-bottom: 1rem;
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 0.9rem;
  }

  .field {
    display: grid;
    gap: 0.45rem;
  }

  .field.full { grid-column: 1 / -1; }

  .field label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #94A3B8;
  }

  .field input,
  .field select,
  .field textarea {
    width: 100%;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 0.95rem 1rem;
    font-family: inherit;
    font-size: 0.95rem;
    color: #0f172a;
    background: #fff;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }

  .field input:focus,
  .field select:focus,
  .field textarea:focus {
    border-color: #10B981;
    box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
  }

  .field textarea {
    resize: vertical;
    min-height: 120px;
  }

  .submit-btn {
    width: 100%;
    margin-top: 1rem;
    border: none;
    border-radius: 999px;
    background: #0f172a;
    color: #fff;
    padding: 0.95rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform .15s, opacity .2s;
  }

  .submit-btn:hover {
    opacity: 0.92;
    transform: translateY(-1px);
  }

  .flash-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 86px 5vw 0;
  }

  .flash {
    border: 1px solid rgba(110,231,183,0.25);
    background: rgba(6,95,70,0.25);
    color: #DCFCE7;
    border-radius: 16px;
    padding: 0.85rem 1rem;
    margin-top: 1rem;
  }

  .flash.error {
    border-color: rgba(248,113,113,0.25);
    background: rgba(127,29,29,0.3);
    color: #FEE2E2;
  }

  footer {
    border-top: 1px solid var(--border);
    padding: 2rem 5vw;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    color: var(--slate);
    font-size: 0.85rem;
  }

  footer a {
    color: var(--slate);
    text-decoration: none;
  }

  footer a:hover { color: var(--white); }

  .footer-links { display: flex; gap: 1.5rem; }

  .demo-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    border: 1px solid rgba(110,231,183,0.2);
    background: rgba(110,231,183,0.08);
    color: var(--mint);
    padding: 0.45rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .demo-pill span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--mint);
  }

  @media (max-width: 1080px) {
    .pricing-grid { grid-template-columns: repeat(2,1fr); }
    .cta-inner { grid-template-columns: 1fr; }
  }

  @media (max-width: 768px) {
    .hero { grid-template-columns: 1fr; padding-top: 90px; }
    .hero-visual { display: none; }
    .features-grid { grid-template-columns: 1fr; }
    .stats-row { grid-template-columns: repeat(2,1fr); }
    .testimonials { grid-template-columns: 1fr; }
    .nav-links { display: none; }
    .form-grid { grid-template-columns: 1fr; }
    .cta-left { padding: 2rem 1.4rem; }
  }
</style>
</head>
<body>
<nav>
  <div style="display:flex;flex-direction:column;gap:2px;">
    <a class="logo" href="{{ route('home') }}">
      <span class="logo-dot"></span>
      {{ $sectionValue('site_header', 'title', 'ClassBridge AI') }}
    </a>
    <span style="font-size:0.72rem;color:var(--slate);font-family:'Inter',sans-serif;">{{ $sectionValue('site_header', 'subtitle', 'Safe live teaching workspace') }}</span>
  </div>
  <div class="nav-actions">
    <a href="{{ $resolveUrl($sectionValue('site_header', 'secondary_button_url', route('login'))) }}" class="btn-login">{{ $sectionValue('site_header', 'secondary_button_text', 'Login') }}</a>
    <a href="{{ $resolveUrl($sectionValue('site_header', 'button_url', route('register'))) }}" class="btn-primary">{{ $sectionValue('site_header', 'button_text', 'Start Free Trial') }}</a>
  </div>
</nav>

<div class="flash-wrap">
  @if (session('success'))
    <div class="flash">{{ session('success') }}</div>
  @endif

  @if (session('error'))
    <div class="flash error">{{ session('error') }}</div>
  @endif
</div>

<section class="hero">
  <div class="hero-left">
    <div class="hero-eyebrow"><span></span>{{ data_get($heroSettings, 'eyebrow', 'Live interactive learning') }}</div>
    <h1 class="hero-title">
      {{ $heroTitle }}
      <span class="accent">{{ $heroAccent }}</span>
    </h1>
    <p class="hero-sub">{{ $heroSubtitle }}</p>
    <div class="hero-ctas">
      <a href="{{ $resolveUrl($sectionValue('hero', 'primary_button_url', route('register'))) }}" class="btn-primary">
        {{ $sectionValue('hero', 'primary_button_text', 'Start Free Trial') }}
      </a>
      <a href="{{ $resolveUrl($sectionValue('hero', 'secondary_button_url', route('demo.live-classroom'))) }}" class="btn-ghost">
        <span class="play-icon">
          <svg viewBox="0 0 10 12"><path d="M1 1l8 5-8 5V1z"/></svg>
        </span>
        {{ $sectionValue('hero', 'secondary_button_text', 'Try Demo Classroom') }}
      </a>
      <a href="{{ $resolveUrl($sectionValue('request_demo', 'button_url', '#request-demo')) }}" class="btn-ghost">
        {{ $sectionValue('request_demo', 'button_text', 'Request Demo') }}
      </a>
      <a href="{{ $resolveUrl($sectionValue('site_header', 'secondary_button_url', route('login'))) }}" class="btn-login">{{ $sectionValue('site_header', 'secondary_button_text', 'Login') }}</a>
    </div>
  </div>

  <div class="hero-visual">
    <div style="position:relative;">
      <div class="code-card">
        <div class="code-topbar">
          <div class="dot dot-r"></div>
          <div class="dot dot-y"></div>
          <div class="dot dot-g"></div>
          <span class="tab-label">live-session.blade.php - {{ data_get($heroSettings, 'status_label', 'Protected room') }}</span>
        </div>
        <div class="code-body">
          @foreach ($heroCodeLines as $index => $line)
            <div class="code-line"><span class="ln">{{ $index + 1 }}</span><span class="code-text">{!! $highlightCodeLine($line) !!}</span></div>
          @endforeach
        </div>
      </div>

      <div class="badge-float badge-1">
        <span class="badge-online"></span>
        <strong style="color:#fff;font-family:'Inter',sans-serif;font-size:0.8rem;">{{ data_get($heroSettings, 'badge_one_text', '3 learners online') }}</strong>
      </div>
      <div class="badge-float badge-2">
        <span class="bicon">Board</span>
        <span style="color:#94A3B8;font-family:'Inter',sans-serif;font-size:0.78rem;">{{ data_get($heroSettings, 'badge_two_text', 'Whiteboard active') }}</span>
      </div>
    </div>
  </div>
</section>

<section class="video-section" id="demo">
  <div class="section-label">{{ data_get($demoPreviewSettings, 'label', 'See it in action') }}</div>
  <h2 class="section-title">{{ $sectionValue('demo_preview', 'title', 'A full learning environment in your browser') }}</h2>
  <p class="section-sub">{{ $sectionValue('demo_preview', 'subtitle', 'Watch how a teacher leads a live lesson - sharing code, drawing on the whiteboard, and answering questions in real time.') }}</p>

  <div class="video-wrapper" id="videoWrapper" onclick="playVideo(this)">
    <div class="video-thumb">
      <div class="video-grid">
        <div class="vg-block"></div>
        <div class="vg-block" style="background:rgba(59,130,246,0.08)"></div>
        <div class="vg-block"></div>
        <div class="vg-block" style="background:rgba(110,231,183,0.05)"></div>
        <div class="vg-block"></div>
        <div class="vg-block"></div>
      </div>
      <button class="play-btn" aria-label="Open demo classroom">
        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
      </button>
      <div class="video-label">{{ data_get($demoPreviewSettings, 'video_label', 'Platform walkthrough - 2 min') }}</div>
    </div>
  </div>
</section>

<section class="features" id="features">
  <div class="features-header">
    <div class="section-label">{{ $sectionValue('features', 'settings.label', 'Everything you need') }}</div>
    <h2 class="section-title">{{ $sectionValue('features', 'title', 'Built for serious teaching') }}</h2>
    <p class="section-sub" style="max-width:500px;margin:0 auto;">{{ $sectionValue('features', 'subtitle', 'Every tool a tutor needs to deliver a live session - no tab switching, no plugins.') }}</p>
  </div>

  <div class="features-grid">
    @foreach ($features->take(6) as $feature)
      @php $isCore = data_get($feature, 'title', data_get($feature, 'name')) === 'Live Interactive Classroom'; @endphp
      <div class="feat-card">
        <div class="feat-icon {{ $loop->index % 2 ? 'mint' : '' }}">
          @if ($loop->index === 0)
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#3B82F6" stroke-width="1.8"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 9l3 3-3 3M13 15h3"/></svg>
          @elseif ($loop->index === 1)
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#6EE7B7" stroke-width="1.8"><rect x="2" y="3" width="20" height="16" rx="2"/><path d="M8 21h8M12 19v2M7 9l3 3M10 12l-3 3"/></svg>
          @elseif ($loop->index === 2)
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#3B82F6" stroke-width="1.8"><path d="M15 10l4.55-2.27a1 1 0 011.45.9V15.4a1 1 0 01-1.45.89L15 14"/><rect x="2" y="7" width="13" height="10" rx="2"/></svg>
          @elseif ($loop->index === 3)
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#6EE7B7" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6"/></svg>
          @elseif ($loop->index === 4)
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#3B82F6" stroke-width="1.8"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          @else
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#6EE7B7" stroke-width="1.8"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          @endif
        </div>
        <div class="feat-title">
          {{ data_get($feature, 'title', data_get($feature, 'name')) }}
        </div>
        <div class="feat-desc">
          {{ data_get($feature, 'description', data_get($feature, 'copy')) }}
        </div>
        @if ($isCore)
          <div class="mini-pill" style="margin-top:1rem;"><span></span>Core classroom</div>
        @endif
      </div>
    @endforeach
  </div>
</section>

<section class="how" id="how">
  <div class="how-inner">
    <div class="how-header">
      <div class="section-label">{{ $sectionValue('how_it_works', 'settings.label', 'How it works') }}</div>
      <h2 class="section-title">{{ $sectionValue('how_it_works', 'title', 'From sign-up to first lesson in minutes') }}</h2>
      <p class="section-sub">{{ $sectionValue('how_it_works', 'subtitle', 'A simple flow that parents understand quickly.') }}</p>
    </div>

    <div class="steps">
      @foreach ($workflow as $item)
        <div class="step">
          <div class="step-num">{{ sprintf('%02d', $loop->iteration) }}</div>
          <div class="step-content">
            <h3>{{ data_get($item, 'title') }}</h3>
            <p>{{ data_get($item, 'copy') }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section class="social">
  <div class="social-header">
    <div class="section-label">{{ $sectionValue('social_proof', 'settings.label', 'Trust matters') }}</div>
    <h2 class="section-title">{{ $sectionValue('social_proof', 'title', 'Numbers that make the room easy to trust') }}</h2>
    <p class="section-sub">{{ $sectionValue('social_proof', 'subtitle', 'Parents, tutors, and school owners see exactly what the platform protects and supports.') }}</p>
  </div>

  <div class="stats-row">
    @foreach ($stats as $stat)
      <div class="stat-card">
        <div class="stat-num">{{ data_get($stat, 'value') }}<span class="unit">+</span></div>
        <div class="stat-label">{{ data_get($stat, 'label') }}</div>
      </div>
    @endforeach
  </div>

  <div class="testimonials">
    @foreach ($testimonials as $testimonial)
      <div class="testi">
        <p class="testi-text">"{{ data_get($testimonial, 'quote') }}"</p>
        <div class="testi-author">
          <div class="avatar {{ $loop->index === 0 ? 'av-blue' : ($loop->index === 1 ? 'av-mint' : 'av-purple') }}">
            {{ strtoupper(substr((string) data_get($testimonial, 'name'), 0, 2)) }}
          </div>
          <div>
            <div class="testi-name">{{ data_get($testimonial, 'name') }}</div>
            <div class="testi-role">{{ data_get($testimonial, 'role') }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</section>

<section id="pricing" class="pricing">
  <div class="pricing-header">
    <div class="section-label">{{ $sectionValue('pricing_preview', 'settings.label', 'Pricing preview') }}</div>
    <h2 class="section-title">{{ $sectionValue('pricing_preview', 'title', 'Plans shaped for tutors, teams, and organizations') }}</h2>
    <p class="section-sub">{{ $sectionValue('pricing_preview', 'subtitle', 'Choose the size that fits your teaching business, then grow later.') }}</p>
  </div>

  <div class="pricing-grid">
    @foreach ($pricingItems as $plan)
      <div class="plan {{ data_get($plan, 'is_popular') ? 'featured' : '' }}">
        <div class="plan-top">
          <div>
            <div class="plan-title">{{ data_get($plan, 'name') }}</div>
            <div class="plan-desc">{{ data_get($plan, 'description') }}</div>
          </div>
          @if (data_get($plan, 'is_popular'))
            <span class="mini-pill" style="background:rgba(15,23,42,0.9);color:#fff;border-color:rgba(255,255,255,0.1);">Popular</span>
          @endif
        </div>
        <div class="plan-price">{{ data_get($plan, 'price_text', 'Custom') }}</div>
        <ul class="plan-list">
          @foreach ((array) data_get($plan, 'features', []) as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </div>
</section>

<section id="request-demo" class="cta-section">
  <div class="cta-inner">
    <div class="cta-left">
      <div class="cta-glow"></div>
      <div class="demo-pill"><span></span>{{ data_get($requestDemoSettings, 'badge_text', 'Protected workspace') }}</div>
      <h2 class="cta-title">{{ $sectionValue('request_demo', 'title', 'Start teaching interactively today.') }}</h2>
      <p class="cta-sub">{{ $sectionValue('request_demo', 'subtitle', 'Build your live classroom around shared teaching, not remote desktop access.') }}</p>

      <div class="cta-btns">
        <a href="{{ $resolveUrl($sectionValue('site_header', 'button_url', route('register'))) }}" class="btn-primary">{{ $sectionValue('site_header', 'button_text', 'Start Free Trial') }}</a>
        <a href="{{ $resolveUrl($sectionValue('hero', 'secondary_button_url', route('demo.live-classroom'))) }}" class="btn-ghost">{{ $sectionValue('hero', 'secondary_button_text', 'Try Demo Classroom') }}</a>
        <a href="{{ $resolveUrl($sectionValue('site_header', 'secondary_button_url', route('login'))) }}" class="btn-ghost">{{ $sectionValue('site_header', 'secondary_button_text', 'Login') }}</a>
      </div>

      <p class="cta-note">
        {{ $sectionValue('request_demo', 'content', 'The protected classroom keeps teacher and student inside the same workspace while parents, schools, and tutors stay informed through reports and replays.') }}
      </p>
    </div>

    <div class="cta-form">
      <p class="text-xs font-black uppercase tracking-[0.28em] text-slate-400">{{ $sectionValue('request_demo', 'settings.label', 'Request demo') }}</p>
      <h3>{{ data_get($requestDemoSettings ?? [], 'form_title', $sectionValue('request_demo', 'settings.form_title', 'Tell us about your teaching setup.')) }}</h3>
      <p>{{ data_get($requestDemoSettings ?? [], 'form_subtitle', $sectionValue('request_demo', 'settings.form_subtitle', 'We will follow up with a demo for your school, tutoring center, private teaching business, homeschool setup, or coding academy.')) }}</p>

      <form method="POST" action="{{ route('demo.request') }}">
        @csrf

        <div class="form-grid">
          <div class="field">
            <label>Name</label>
            <input name="name" value="{{ old('name') }}" required placeholder="Your name">
            @error('name')<div style="color:#dc2626;font-size:0.78rem;">{{ $message }}</div>@enderror
          </div>

          <div class="field">
            <label>Email</label>
            <input name="email" type="email" value="{{ old('email') }}" required placeholder="Email address">
            @error('email')<div style="color:#dc2626;font-size:0.78rem;">{{ $message }}</div>@enderror
          </div>

          <div class="field">
            <label>Phone</label>
            <input name="phone" value="{{ old('phone') }}" placeholder="Phone optional">
          </div>

          <div class="field">
            <label>I am a</label>
            <select name="role_type">
              <option value="">Select type</option>
              @foreach ([
                  'school' => 'School',
                  'tutoring_center' => 'Tutoring center',
                  'coding_academy' => 'Coding academy',
                  'private_tutor' => 'Private tutor',
                  'homeschool_tutor' => 'Homeschool tutor',
                  'online_academy' => 'Online lesson business',
                  'parent' => 'Parent',
                  'other' => 'Other',
              ] as $value => $label)
                <option value="{{ $value }}" @selected(old('role_type') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div class="field full">
            <label>Organization</label>
            <input name="organization" value="{{ old('organization') }}" placeholder="Organization or tutor name optional">
          </div>

          <div class="field full">
            <label>Message</label>
            <textarea name="message" rows="4" placeholder="What do you want to teach online?">{{ old('message') }}</textarea>
          </div>
        </div>

        <button class="submit-btn" type="submit">Request demo</button>
      </form>
    </div>
  </div>
</section>

<footer>
  <div style="display:flex;flex-direction:column;gap:4px;max-width:340px;">
    <a class="logo" href="{{ route('home') }}">
      <span class="logo-dot"></span>
      {{ $sectionValue('site_footer', 'title', 'ClassBridge AI') }}
    </a>
    <span style="color:var(--slate);font-size:0.82rem;">{{ $sectionValue('site_footer', 'subtitle', 'Safe live teaching workspace.') }}</span>
    <span style="color:var(--slate);font-size:0.78rem;">{{ $sectionValue('site_footer', 'content', 'Teach online like you are beside the learner, without remote access risk.') }}</span>
  </div>
  <div class="footer-links">
    @foreach ($footerLinks as $link)
      <a href="{{ $resolveUrl(data_get($link, 'url', '#features')) }}">{{ data_get($link, 'label', 'Link') }}</a>
    @endforeach
  </div>
  <span>&copy; {{ date('Y') }} {{ $sectionValue('site_footer', 'title', 'ClassBridge AI') }}. All rights reserved.</span>
</footer>

<script>
function playVideo(el) {
  el.innerHTML = `
    <div style="width:100%;height:100%;background:#000;display:flex;align-items:center;justify-content:center;color:#94A3B8;font-family:Inter,sans-serif;font-size:0.95rem;">
      <div style="text-align:center;">
        <div style="font-size:2.5rem;margin-bottom:1rem;">&#9654;</div>
        <div>Your demo classroom walkthrough would play here.</div>
        <div style="font-size:0.8rem;margin-top:0.4rem;color:#4B5563;">Replace this with a video embed or route to the demo classroom page.</div>
      </div>
    </div>`;
}
</script>
</body>
</html>
