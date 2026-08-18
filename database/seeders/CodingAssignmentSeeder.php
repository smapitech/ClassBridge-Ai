<?php

namespace Database\Seeders;

use App\Models\CodingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CodingAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get the demo school's teacher
        $teacher = User::where('email', 'teacher@demoacademy.com')->first();
        if (!$teacher) {
            $this->command?->warn('Demo teacher not found. Skipping coding assignment seeder.');
            return;
        }

        $schoolId = $teacher->school_id;

        // Sample Assignment 1: My First Webpage
        CodingAssignment::firstOrCreate(
            ['school_id' => $schoolId, 'title' => 'My First Webpage', 'teacher_id' => $teacher->id],
            [
                'school_id' => $schoolId,
                'teacher_id' => $teacher->id,
                'title' => 'My First Webpage',
                'description' => 'Learn to create your first webpage using HTML, CSS, and JavaScript.',
                'instructions' => "Create a webpage with:\n1. Your name displayed as a heading\n2. Your school name\n3. Your favorite color mentioned\n4. A button that shows an alert when clicked\n\nUse all three files: HTML, CSS, and JavaScript.",
                'starter_html' => "<h1>My First Webpage</h1>\n<p>My school is: <strong>Demo Academy</strong></p>\n<p>My favorite color is: <span id='favColor'>blue</span></p>\n<button onclick='sayHello()'>Click Me</button>",
                'starter_css' => "body {\n    font-family: Arial, sans-serif;\n    text-align: center;\n    margin-top: 50px;\n    background-color: #f0f4ff;\n}\nh1 {\n    color: #4f46e5;\n}\nbutton {\n    background-color: #4f46e5;\n    color: white;\n    border: none;\n    padding: 10px 20px;\n    font-size: 16px;\n    border-radius: 8px;\n    cursor: pointer;\n}\nbutton:hover {\n    background-color: #3730a3;\n}",
                'starter_js' => "function sayHello() {\n    alert('Hello! Welcome to my webpage!');\n}",
                'status' => 'published',
            ]
        );

        // Sample Assignment 2: Build a Calculator
        CodingAssignment::firstOrCreate(
            ['school_id' => $schoolId, 'title' => 'Build a Simple Calculator', 'teacher_id' => $teacher->id],
            [
                'school_id' => $schoolId,
                'teacher_id' => $teacher->id,
                'title' => 'Build a Simple Calculator',
                'description' => 'Create a working calculator with HTML buttons and JavaScript logic.',
                'instructions' => "Build a calculator that can:\n1. Add, subtract, multiply, and divide two numbers\n2. Have a clear button\n3. Show the result on screen\n4. Style it with CSS to look nice",
                'starter_html' => "<div class='calculator'>\n    <input type='text' id='display' readonly placeholder='0'>\n    <div class='buttons'>\n        <button onclick='appendNumber(7)'>7</button>\n        <button onclick='appendNumber(8)'>8</button>\n        <button onclick='appendNumber(9)'>9</button>\n        <button onclick='setOperator(\"/\")' class='op'>/</button>\n        <button onclick='appendNumber(4)'>4</button>\n        <button onclick='appendNumber(5)'>5</button>\n        <button onclick='appendNumber(6)'>6</button>\n        <button onclick='setOperator(\"*\")' class='op'>*</button>\n        <button onclick='appendNumber(1)'>1</button>\n        <button onclick='appendNumber(2)'>2</button>\n        <button onclick='appendNumber(3)'>3</button>\n        <button onclick='setOperator(\"-\")' class='op'>-</button>\n        <button onclick='appendNumber(0)'>0</button>\n        <button onclick='clearDisplay()'>C</button>\n        <button onclick='calculate()' class='equals'>=</button>\n        <button onclick='setOperator(\"+\")' class='op'>+</button>\n    </div>\n</div>",
                'starter_css' => ".calculator {\n    width: 220px;\n    margin: 50px auto;\n    padding: 15px;\n    background: #1f2937;\n    border-radius: 12px;\n}\n#display {\n    width: 100%;\n    padding: 10px;\n    font-size: 20px;\n    text-align: right;\n    border: none;\n    border-radius: 6px;\n    margin-bottom: 10px;\n}\n.buttons {\n    display: grid;\n    grid-template-columns: repeat(4, 1fr);\n    gap: 5px;\n}\nbutton {\n    padding: 12px;\n    font-size: 16px;\n    border: none;\n    border-radius: 6px;\n    cursor: pointer;\n    background: #374151;\n    color: white;\n}\nbutton:hover { background: #4b5563; }\n.op { background: #f59e0b; }\n.op:hover { background: #d97706; }\n.equals { background: #10b981; }\n.equals:hover { background: #059669; }",
                'starter_js' => "let currentInput = '';\nlet previousInput = '';\nlet operator = null;\n\nfunction appendNumber(num) {\n    currentInput += num;\n    updateDisplay(currentInput);\n}\n\nfunction setOperator(op) {\n    if (currentInput === '') return;\n    if (previousInput !== '') calculate();\n    operator = op;\n    previousInput = currentInput;\n    currentInput = '';\n}\n\nfunction calculate() {\n    let result;\n    const prev = parseFloat(previousInput);\n    const curr = parseFloat(currentInput);\n    if (isNaN(prev) || isNaN(curr)) return;\n    switch(operator) {\n        case '+': result = prev + curr; break;\n        case '-': result = prev - curr; break;\n        case '*': result = prev * curr; break;\n        case '/': result = prev / curr; break;\n        default: return;\n    }\n    currentInput = result.toString();\n    operator = null;\n    previousInput = '';\n    updateDisplay(currentInput);\n}\n\nfunction clearDisplay() {\n    currentInput = '';\n    previousInput = '';\n    operator = null;\n    updateDisplay('');\n}\n\nfunction updateDisplay(value) {\n    document.getElementById('display').value = value;\n}",
                'status' => 'published',
            ]
        );

        $this->command?->info('Coding assignments seeded!');
    }
}