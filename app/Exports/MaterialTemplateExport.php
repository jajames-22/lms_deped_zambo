<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaterialTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'section_type',  // 'lesson' or 'exam'
            'section_title', // Name of the Lesson/Exam
            'type',          // 'content', 'mcq', 'true_false', 'checkbox', or 'text'
            'content_text',  // The lesson text or the question text
            'media_url',     // Optional URL to image, video, or audio
            'option_1',
            'option_2',
            'option_3',
            'option_4',
            'correct_answer' // e.g., 'Option 1', 'Option 2', or 'Option 1, Option 3'
        ];
    }

    public function array(): array
    {
        return [
            // --- LESSON 1: Content & Standard Multiple Choice ---
            ['lesson', 'Chapter 1: Getting Started', 'content', 'Please watch the attached introductory video before proceeding to the practice quiz.', 'https://example.com/intro.mp4', '', '', '', '', ''],
            ['lesson', 'Chapter 1: Getting Started', 'mcq', 'What is the primary language used for web styling?', '', 'HTML', 'CSS', 'JavaScript', 'PHP', 'Option 2'],

            // --- LESSON 2: True/False & Checkboxes (Multiple Answers) ---
            ['lesson', 'Chapter 2: Data Structures', 'true_false', 'Graphs are non-linear data structures consisting of nodes and edges.', '', 'True', 'False', '', '', 'Option 1'],
            ['lesson', 'Chapter 2: Data Structures', 'checkbox', 'Which of the following are backend programming languages? (Select all that apply)', '', 'Python', 'HTML', 'PHP', 'CSS', 'Option 1, Option 3'],

            // --- LESSON 3: Fill-in-the-Blank / Short Text ---
            // Note: For 'text' types, place acceptable string answers in the option columns and mark them all as correct.
            ['lesson', 'Chapter 3: Networking', 'text', 'What is the default port number used for secure web traffic (HTTPS)?', '', '443', 'four hundred forty three', '', '', 'Option 1, Option 2'],

            // --- FINAL EXAM: Strictly Graded Questions ---
            ['exam', 'Final Examination', 'true_false', 'Laravel is built on top of JavaScript.', '', 'True', 'False', '', '', 'Option 2'],
            ['exam', 'Final Examination', 'mcq', 'Which data structure uses LIFO (Last In First Out)?', '', 'Queue', 'Array', 'Stack', 'Tree', 'Option 3'],
        ];
    }
}